<?php

function insertMatch($matchData, $multiple = false)
{
	global $db;
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
				if($match['comp_level'] == 'qm')
				{				
					$query = 'select * from match_info where match_key = "'.$key.'"';
					$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
					if($result->num_rows == 0)
					{
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
			$values = implode(',',$valuesArr);
		}
		else
		{
			$values = $valuesArr;
		}
		if($values != '')
		{
			insertNewEventFromBA($event_key);
			$query = 'INSERT INTO match_info (id,event_key,match_level,match_num,match_key,red_1,red_2,red_3,blue_1,blue_2,blue_3,red_score,blue_score,status) VALUES '.$values;
			$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
		}
	}
}

function insertMatchMysqlValue($matchData)
{
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
	$string = '("'.uniqid().'","'.$matchData['event_key'].'","'.$matchData['comp_level'].'","'.$matchData['match_number'].'",
						 "'.$key.'","'.$red1.'","'.$red2.'","'.$red3.'","'.$blue1.'","'.$blue2.'","'.$blue3.'",
						 "'.$matchData['alliances']['red']['score'].'","'.$matchData['alliances']['blue']['score'].'","'.$status.'")';
	return $string;
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
	global $db;
	$query = 'INSERT INTO match_info_logs (id,match_key,red_score,blue_score) VALUES ("'.uniqid().'", "'.$matchData['key'].'", "'.$matchData['alliances']['red']['score'].'", "'.$matchData['alliances']['blue']['score'].'")';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
} */

function checkEventInfo($event_key)
{
	global $db;
	$data = false;
	$query = 'select * from events where event_key = "'.$event_key.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		$data = true;
	}
	return $data;
}

function getEventInfo($event_key)
{
	global $db;
	$data = false;
	$query = 'select * from events where event_key = "'.$event_key.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$data = $row;
	}
	return $data;
}

function insertNewEvent($eventData)
{
	global $db;
	$official = $eventData['official'] == true ? 1:0;
	$query = 'INSERT INTO events (id,event_key,year,event_code,name,address,location,start_date,end_date,website,timezone,official) VALUES 
								 ("'.uniqid().'", "'.mysqli_real_escape_string($db, $eventData['key']).'", "'.mysqli_real_escape_string($db, $eventData['year']).'", "'.mysqli_real_escape_string($db, $eventData['event_code']).'", "'.mysqli_real_escape_string($db, $eventData['name']).'", 
								  "'.mysqli_real_escape_string($db, $eventData['venue_address']).'", "'.mysqli_real_escape_string($db, $eventData['location']).'", "'.mysqli_real_escape_string($db, $eventData['start_date']).'", "'.mysqli_real_escape_string($db, $eventData['end_date']).'", "'.mysqli_real_escape_string($db, $eventData['website']).'", "'.mysqli_real_escape_string($db, $eventData['timezone']).'", "'.$official.'")';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
}

function insertNewEventFromBA($event_key)
{
	global $db;
	$query = 'select * from events where event_key = "'.$event_key.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows == 0)
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
	global $db;
	$key = $matchData['key'];
	$red_1 = convertBaTeamNumber($matchData['alliances']['red']['teams'][0]);
	$red_2 = convertBaTeamNumber($matchData['alliances']['red']['teams'][1]);
	$red_3 = convertBaTeamNumber($matchData['alliances']['red']['teams'][2]);
	$blue_1 = convertBaTeamNumber($matchData['alliances']['blue']['teams'][0]);
	$blue_2 = convertBaTeamNumber($matchData['alliances']['blue']['teams'][1]);
	$blue_3 = convertBaTeamNumber($matchData['alliances']['blue']['teams'][2]);
	
	$red_score = $matchData['alliances']['red']['score'];
	$blue_score = $matchData['alliances']['blue']['score'];
	$status = '';
	if(checkMatchStatus($red_score, $blue_score) == 'complete')
	{
		$status = ', status="complete"';
	}
	
	$query = 'UPDATE match_info SET red_1="'.$red_1.'", red_2="'.$red_2.'", red_3="'.$red_3.'", blue_1="'.$blue_1.'", blue_2="'.$blue_2.'", blue_3="'.$blue_3.'", red_score="'.$red_score.'", blue_score="'.$blue_score.'"'.$status.' WHERE match_key="'.$key.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
}

function setUpcomingMatch($matchData)
{
	global $db;
	$key = $matchData['match_key'];
	$query = 'UPDATE match_info SET status="upcoming" WHERE match_key="'.$key.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
}

function convertBaTeamNumber($team)
{
	$newTeam = trim(filter_var($team, FILTER_SANITIZE_NUMBER_INT));
	return $newTeam;
}

function getMatchesByEventKey($event_key)
{
	$data = array();
	global $db;
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
	global $db;
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
	global $db;
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
	global $db;
	$data = array();
	$final_data = array();
	$query = 'select * from match_info WHERE match_key = "'.$matchKey.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$data = $row;
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
	global $db;
	$data = null;
	if(isset($event) && $event=='')
	{
		$query = 'select * from match_info WHERE event_key = "'.$event.'" ORDER BY match_num DESC';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			$data = $row['match_num'];
		}
	}
	return $data;
}

function isLastMatch($match, $event)
{
	$return = false;
	if(isset($match) && isset($event) && $match=='' && $event=='')
	{
		$last_match = getLastMatchByEvent($event);
		$return = $match == $last_match;
	}
	return $return;
}

function getTeamInfoFromNumber($team)
{
	global $db;
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
	global $db;
	$data = false;
	$query = 'select * from match_data WHERE match_key = "'.$match_key.'" AND team_account = "'.$team.'" AND action = "match_start" AND timestamp <= '.microtime(true);
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
	global $db;
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
	global $db;
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
	global $db;
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

function getMatchDataStats($match_key, $teamAccount)
{
	global $db;
	$match_info = getMatchByMatchKey($match_key);

	$alliances = array(
		$match_info['blue_1'] => 'blue_1',
		$match_info['blue_2'] => 'blue_2',
		$match_info['blue_3'] => 'blue_3',
		$match_info['red_1'] => 'red_1',
		$match_info['red_2'] => 'red_2',
		$match_info['red_3'] => 'red_3'
	);
	$matchKeyArr = getEventMatchByMatchKey($match_key);
	$event_key = $matchKeyArr['event_key'];
	$year = $matchKeyArr['year'];
	
	$initData = getMatchStatsCategoriesByYear($year);
	$data = array(
		'blue_1' => $initData,
		'blue_2' => $initData,
		'blue_3' => $initData,
		'red_1' => $initData,
		'red_2' => $initData,
		'red_3' => $initData
	);

	$pointValues = getPointValuesByYear($year);
	$matchStartArr = getMatchData_start($match_key, $teamAccount);
	$matchStart = $matchStartArr['match_start'];

	$query = 'select match_data.*, SUBSTRING_INDEX(`match_key`, "_", 1) as event_key FROM match_data WHERE match_key = "'.$match_key.'" AND team_account="'.$teamAccount.'" ORDER BY timestamp ASC';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	if($result->num_rows > 0)
	{
		if($year == 2016)
		{
			while($row=$result->fetch_assoc())
			{
				$team_number = $row['team_number'];
				$match = $row['match_key'];
				$action = $row['action'];
				$timestamp = $row['timestamp'];
				$alli = $alliances[$team_number];
				
				$duringAuto = $timestamp-$matchStart <= 15;
				
				if($action == 'high_goal' && $duringAuto)
				{
					$data[$alli]['auto_high_goal_count'] += 1;
					$data[$alli]['auto_goal_points'] += $pointValues['auto_high_goal'];
					$data[$alli]['auto_points'] += $pointValues['auto_high_goal'];
				}
				elseif($action == 'high_goal' && !$duringAuto)
				{
					$data[$alli]['tele_high_goal_count'] += 1;
					$data[$alli]['tele_goal_points'] += $pointValues['tele_high_goal'];
					$data[$alli]['tele_points'] += $pointValues['tele_high_goal'];
				}
				elseif($action == 'low_goal' && $duringAuto)
				{
					$data[$alli]['auto_low_goal_count'] += 1;
					$data[$alli]['auto_goal_points'] += $pointValues['auto_low_goal'];
					$data[$alli]['auto_points'] += $pointValues['auto_low_goal'];
				}
				elseif($action == 'low_goal' && !$duringAuto)
				{
					$data[$alli]['tele_low_goal_count'] += 1;
					$data[$alli]['tele_goal_points'] += $pointValues['tele_low_goal'];
					$data[$alli]['tele_points'] += $pointValues['tele_low_goal'];
				}
				elseif($action == 'challenge_tower')
				{
					$data[$alli]['challenge_tower_points'] += $pointValues['challenge_tower'];
					$data[$alli]['tele_points'] += $pointValues['challenge_tower'];
				}
				elseif($action == 'scale_tower')
				{
					$data[$alli]['scale_tower_points'] += $pointValues['scale_tower'];
					$data[$alli]['tele_points'] += $pointValues['scale_tower'];
				}
				elseif($action == 'reach_defense' && $duringAuto)
				{
					$data[$alli]['auto_reach_defense_points'] += $pointValues['auto_reach_defense'];
					$data[$alli]['auto_points'] += $pointValues['auto_reach_defense'];
				}		
				elseif($action == 'foul')
				{
					$data[$alli]['foul_count'] += 1;
				}
				elseif($action == 'tech_foul')
				{
					$data[$alli]['tech_foul_count'] += 1;
				}
				elseif($action == 'crossing_defense')
				{
					/* $defense = $row['attr_1'];
					$type = $row['attr_2'];
					if(!isset($defenseCrossings[$team_number][$match][$defense]))
					{
						$defenseCrossings[$team_number][$match][$defense] = array();
					}
					if($type == 'start')
					{
						$defenseCrossings[$team_number][$match][$defense][] = array('start'=>$timestamp, 'end'=>'', 'time'=>'', 'status'=>'', 'auto'=>false);
					}
					elseif($type == 'end')
					{
						$i = count( $defenseCrossings[$team_number][$match][$defense] ) - 1;
						$defenseCrossings[$team_number][$match][$defense][$i]['end'] = $timestamp;
						$defenseCrossings[$team_number][$match][$defense][$i]['time'] = $defenseCrossings[$team_number][$match][$defense][$i]['end'] - $defenseCrossings[$team_number][$match][$defense][$i]['start'];
						$defenseCrossings[$team_number][$match][$defense][$i]['status'] = 'success';
						if($defenseCrossings[$team_number][$match][$defense][$i]['time'] <= 15)
						{
							$defenseCrossings[$team_number][$match][$defense][$i]['auto'] = true;
						}
					}
					elseif($type == 'fail')
					{
						$i = count( $defenseCrossings[$team_number][$match][$defense] ) - 1;
						$defenseCrossings[$team_number][$match][$defense][$i]['end'] = $timestamp;
						$defenseCrossings[$team_number][$match][$defense][$i]['status'] = 'fail';
					}	 */
				}
			}
		}
	}

	$allData = array(
		'matchData' => $data,
		'matchInfo' => $match_info,
		'official_data' => tbaApiCallMatch($match_key)
	);
	
	return $allData;
}
?>