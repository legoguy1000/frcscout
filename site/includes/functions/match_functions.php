<?php

function insertMatch($matchData, $multiple = false)
{
	$db = db_connect();
	if(!empty($matchData))
	{
		$event_key = '';
		if($multiple == false)
		{
			$valuesArr = insertMatchMysqlValue($matchData);
			$event_key = $matchData['event_key'];
		}
		elseif($multiple == true)
		{
			$valuesArr = array();
			foreach($matchData as $match)
			{

				$event_key = $match['event_key'];
				$key = $match['key'];
	//			error_log($match['key'], 0);
				if($match['comp_level'] == 'qm')
				{
					$query = 'select * from match_info where match_key = '.db_quote($key);
					$match_query = db_select_single($query);
//					error_log('match: '.$match_query, 0);
					if(is_null($match_query))
					{
	//					error_log(json_encode($match), 0);
						$valuesArr[] = insertMatchMysqlValue($match);
					}
					else
					{
						updateMatchInfo($match);
					}
				}
			}
		}
		$values = '';
		if(is_array($valuesArr))
		{
			$values = implode(',',array_filter($valuesArr));
		}
		else
		{
			$values = $valuesArr;
		}
		if($values != '')
		{
			insertNewEventFromBA($event_key);
			$query = 'INSERT INTO match_info (id,event_key,match_level,match_num,match_key,red_1,red_2,red_3,blue_1,blue_2,blue_3,red_score,blue_score,status) VALUES '.$values;
			$result = db_query($query);
		}
	}
}

function insertMatchMysqlValue($matchData)
{
	//error_log(json_encode($matchData), 0);
	$year = getYearByEventKey($matchData['event_key']);
	$event_code = getEventByEventKey($matchData['event_key']);
	$key = $matchData['key'];


	$red1 = convertBaTeamNumber($matchData['alliances']['red']['teams'][0]);
	$red2 = convertBaTeamNumber($matchData['alliances']['red']['teams'][1]);
	$red3 = convertBaTeamNumber($matchData['alliances']['red']['teams'][2]);
	$blue1 = convertBaTeamNumber($matchData['alliances']['blue']['teams'][0]);
	$blue2 = convertBaTeamNumber($matchData['alliances']['blue']['teams'][1]);
	$blue3 = convertBaTeamNumber($matchData['alliances']['blue']['teams'][2]);

	$status = checkMatchStatus($matchData['alliances']['red']['score'], $matchData['alliances']['blue']['score']);
	$string = '('.db_quote(uniqid()).',
							'.db_quote($matchData['event_key']).',
							'.db_quote($matchData['comp_level']).',
							'.db_escape($matchData['match_number']).',
							'.db_quote($key).',
							'.db_quote($red1).',
							'.db_quote($red2).',
							'.db_quote($red3).',
							'.db_quote($blue1).',
							'.db_quote($blue2).',
							'.db_quote($blue3).',
							'.db_quote($matchData['alliances']['red']['score']).',
							'.db_quote($matchData['alliances']['blue']['score']).',
							'.db_quote($status).')';
	if($key != '') {
		return $string;
	}
	else  {
		return false;
	}
}

function getYearByEventKey($event_key)
{
	return (int)trim(filter_var(substr($event_key, 0, 4), FILTER_SANITIZE_NUMBER_INT));
}

function getEventByEventKey($event_key)
{
	return trim(substr($event_key, 4));
}

function getEventMatchByMatchKey($match_key)
{
	$matchKeyArr = explode('_',$match_key);
	$event_key = $matchKeyArr[0];
	$year = (int)getYearByEventKey($event_key);
	$match_num = (int)trim(filter_var($matchKeyArr[1], FILTER_SANITIZE_NUMBER_INT));
	$event = getEventByEventKey($event_key);
	return array(
		'match_key' => $match_key,
		'event_key' => $event_key,
		'event' => $event,
		'year' => $year,
		'match_num' => $match_num,
	);
}

/* function matchInfoLogsInster($matchData)
{
	$db = db_connect();
	$query = 'INSERT INTO match_info_logs (id,match_key,red_score,blue_score) VALUES ("'.uniqid().'", "'.$matchData['key'].'", "'.$matchData['alliances']['red']['score'].'", "'.$matchData['alliances']['blue']['score'].'")';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
} */

function checkEventInfo($event_key)
{
	$db = db_connect();
	$data = false;
	$query = 'select * from events where event_key = '.db_quote($event_key);
	$event = db_select_single($query);
	if(!is_null($event))
	{
		$data = true;
	}
	return $data;
}

function checkEventMatches($event_key)
{
	$db = db_connect();
	$data = false;
	$query = 'select * from match_info where event_key = '.db_quote($event_key);
	$matches = db_select($query);
	if(count($matches) > 0)
	{
		$data = true;
	}
	return $data;
}

function getEventInfo($event_key)
{
	$db = db_connect();
	$query = 'select * from events where event_key = '.db_quote($event_key);
	$event = db_select_single($query);
	return $event;
}

function insertNewEvent($eventData)
{
	$db = db_connect();
	$official = $eventData['official'] == true ? 1:0;
	$query = 'INSERT INTO events (id,event_key,year,event_code,name,address,location,start_date,end_date,website,timezone,official) VALUES
								 ('.db_quote(uniqid()).',
								 	'.db_quote($eventData['key']).',
									'.db_escape($eventData['year']).',
									'.db_quote($eventData['event_code']).',
									'.db_quote($eventData['name']).',
								  '.db_quote($eventData['venue_address']).',
									'.db_quote($eventData['location']).',
									'.db_quote($eventData['start_date']).',
									'.db_quote($eventData['end_date']).',
									'.db_quote($eventData['website']).',
									'.db_quote($eventData['timezone']).',
									'.db_quote($official).')';
	$result = db_query($query);
}

function insertNewEventFromBA($event_key)
{
	$db = db_connect();
	$query = 'select * from events where event_key = "'.$event_key.'"';
	$event = db_select($query);
	if(!is_null($event))
	{
		$baApiCall = tbaApiCallEvent($event_key);
		if($baApiCall !== FALSE)
		{
			$eventArr = json_decode($baApiCall, true);
			if(!array_key_exists('404',$eventArr) && !array_key_exists('Errors',$eventArr))
			{
				insertNewEvent($eventArr);
			}
		}
	}
}

function updateMatchInfo($matchData)
{
	$db = db_connect();
	$key = db_quote($matchData['key']);
	$red_1 = db_quote(convertBaTeamNumber($matchData['alliances']['red']['teams'][0]));
	$red_2 = db_quote(convertBaTeamNumber($matchData['alliances']['red']['teams'][1]));
	$red_3 = db_quote(convertBaTeamNumber($matchData['alliances']['red']['teams'][2]));
	$blue_1 = db_quote(convertBaTeamNumber($matchData['alliances']['blue']['teams'][0]));
	$blue_2 = db_quote(convertBaTeamNumber($matchData['alliances']['blue']['teams'][1]));
	$blue_3 = db_quote(convertBaTeamNumber($matchData['alliances']['blue']['teams'][2]));

	$red_score = db_quote($matchData['alliances']['red']['score']);
	$blue_score = db_quote($matchData['alliances']['blue']['score']);
	$status = '';
	if(checkMatchStatus($red_score, $blue_score) == 'complete')
	{
		$status = ', status="complete"';
	}

	$query = 'UPDATE match_info SET red_1='.$red_1.',
																	red_2='.$red_2.',
																	red_3='.$red_3.',
																	blue_1='.$blue_1.',
																	blue_2='.$blue_2.',
																	blue_3='.$blue_3.',
																	red_score='.$red_score.',
																	blue_score='.$blue_score.''.$status.'
						WHERE match_key='.$key;
	$result = db_query($query);
}

function setUpcomingMatch($matchData)
{
	$db = db_connect();
	$key = $matchData['match_key'];
	$query = 'UPDATE match_info SET status="upcoming" WHERE match_key="'.$key.'"';
	$result = db_query($query);
}

function convertBaTeamNumber($team)
{
	$newTeam = trim(filter_var($team, FILTER_SANITIZE_NUMBER_INT));
	return $newTeam;
}

function getMatchesByEventKey($event_key)
{
	$data = array();
	$db = db_connect();
	$query = 'select * from match_info where event_key = "'.$event_key.'" ORDER BY match_num ASC';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		while($row = $result->fetch_assoc())
		{
			$temp = $row;
			$temp['alliances'] = generateAlliances($row);
			$data[$row['match_key']] = $temp;
		}
	}
	return $data;
}


function checkEventComplete($event_key)
{
	$db = db_connect();
	$return = true;
	$query = 'select * from match_info where event_key = "'.$event_key.'" AND status <> "complete" ORDER BY match_num ASC';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		$return = false;
	}
	return $return;
}

function getEventsByYear($year)
{
	$db = db_connect();
	$data = array();
	$query = 'select * from events where year = "'.$year.'" ORDER BY name ASC';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		while($row = $result->fetch_assoc())
		{
			$data[$row['event_key']] = $row;
		}
	}
	return $data;
}

function checkMatchStatus($red_score, $blue_score)
{
	if($red_score == '-1' && $blue_score == '-1')
	{
		$status = 'pending';
	}
	else
	{
		$status = 'complete';
	}
	return $status;
}

function getMatchByMatchKey($matchKey)
{
	$db = db_connect();
	$data = array();
	$final_data = array();
	$query = 'select * from match_info WHERE match_key = "'.$matchKey.'"';
	$match = db_select_single($query);
	if(!is_null($match))
	{
		$data = $match;
		$final_data = $data;
	}
	else
	{
		$data = array(
			'blue_1' => '',
			'blue_2' => '',
			'blue_3' => '',
			'red_1' => '',
			'red_2' => '',
			'red_3' => '',
			'status'=>'',
		);
	}
	$final_data['last_match'] = isLastMatchByMatchKey($matchKey);
	$final_data['alliances'] = generateAlliances($data);
	$final_data['completed'] = $data['status']=='complete';
	$final_data['ready_to_start'] = matchReadyToStart($data);
	return $final_data;
}

function generateAlliances($matchData)
{
	$data = array(
		'blue' => array(
			getTeamInfoFromNumber($matchData['blue_1']),
			getTeamInfoFromNumber($matchData['blue_2']),
			getTeamInfoFromNumber($matchData['blue_3'])
		),
		'red' => array(
			getTeamInfoFromNumber($matchData['red_1']),
			getTeamInfoFromNumber($matchData['red_2']),
			getTeamInfoFromNumber($matchData['red_3'])
		)
	);
	return $data;
}

function matchReadyToStart($matchData)
{
	$return = false;
	if($matchData['blue_1']!='' && $matchData['blue_2']!='' && $matchData['blue_3']!='' && $matchData['red_1']!='' && $matchData['red_2']!='' && $matchData['red_3']!='')
	{
		$return = true;
	}
	return $return;
}


function checkMatchKeyFormat($match_key)
{
	$return = true;
	if(preg_match("/\b[0-9]{4}[a-z]+_qm[0-9]+\b/", $match_key, $output_array) === false) {
		$return = false;
	}
	return $return;
}

function getLastMatchByEvent($event)
{
	$db = db_connect();
	$data = null;
	if(isset($event) && $event!='')
	{
		$query = 'select * from match_info WHERE event_key = "'.$event.'" ORDER BY match_num DESC';
		$match = db_select_single($query);
		if(!is_null($match))
		{
			$data = $match['match_num'];
		}
	}
	return $data;
}

function isLastMatch($match, $event)
{
	$return = false;
	if(isset($match) && isset($event) && $match!='' && $event!='')
	{
		$last_match = getLastMatchByEvent($event);
		$return = $match == $last_match;
	}
	return $return;
}

function isLastMatchByMatchKey($match_key = null)
{
		$matchKeyArr = getEventMatchByMatchKey($match_key);
		$event_key = $matchKeyArr['event_key'];
		$match_num = $matchKeyArr['match_num'];
		return isLastMatch($match_num, $event_key);
}

function getTeamInfoFromNumber($team)
{
	$db = db_connect();
	$data = array();
	if(isset($team) && $team!='')
	{
		$query = 'select * from teams WHERE team_number = "'.$team.'"';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			$data = $row;
		}
	}
	return $data;
}

function getMatchData_start($match_key, $team)
{
	$db = db_connect();
	$data = false;
	$query = 'select * from match_data WHERE match_key = "'.$match_key.'" AND team_account = "'.$team.'" AND action = "match_start" AND timestamp <= '.(microtime(true)+2);
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$data = array();
		$data['match_start'] = $row['timestamp'];
		$data['raw'] = $row;
	}
	return $data;
}

function getMatchData($match_key, $team)
{
	$db = db_connect();
	$data = array();
	$data['match_started'] = false;
	$data['match_start'] = '';
	$data['team_data'] = new stdClass();
	$data['team_data'] = array();
	if(isset($match_key) && isset($team) && $match_key!='' && $team!='')
	{
		$startArr = getMatchData_start($match_key, $team);
		if($startArr !== false)
		{
			$data['match_start'] = $startArr['match_start'];
			$data['match_started'] = true;

			$query = 'select * from match_data WHERE match_key = "'.$match_key.'" AND team_account = "'.$team.'" AND team_number is not NULL';
			$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
			if($result->num_rows > 0)
			{
				$data['team_data'] = array();
				while($row = $result->fetch_assoc())
				{
					$team = $row['team_number'];
					$action = $row['action'];
					$timestamp = $row['timestamp'];
					$gameTime = $timestamp - $data['match_start'];
					$data['team_data'][(string)$team][$action][] = array(
						'timestamp' => $timestamp,
						'game_time' => $gameTime,
						'raw' => $row
					);
				}
			}
		}
	}
	return $data;
}

function getOneTimeMatchActionByYear($year, $action)
{
	$db = db_connect();
	$return = false;
	$actions = array();
	if($year == 2016)
	{
		$actions[] = 'challenge_tower';
		$actions[] = 'scale_tower';
	}
	elseif($year == 2017)
	{
		$actions[] = 'cross_baseline';
		$actions[] = 'ready_takeoff';
	}
	if(in_array($action,$actions))
	{
		$return = true;
	}
	return $return;
}

function oneTimeActionComplete($match_key, $team, $team_account, $action)
{
	$db = db_connect();
	$year = (int)substr($match_key, 0, 4);
	$isOT = getOneTimeMatchActionByYear($year, $action);
	$return = false;
	if($isOT)
	{
		$query = 'SELECT * FROM match_data WHERE team_account="'.$team_account.'" AND team_number="'.$team.'" AND match_key="'.$match_key.'" AND action="'.$action.'"';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$return = true;
		}
	}
	return $return;
}


?>
