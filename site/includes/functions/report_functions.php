<?php
function get2016ReportData($event, $allTeams, $teamAccount)
{
	global $db;
	
	$year = 2016;
	
	$data = array();
	$teamsQuery = array();
	$legend = array();
	$teamsQueryString = '';	
	if(isset($allTeams))
	{
		foreach($allTeams as $team)
		{
			$teamsQuery[] = 'team_number="'.mysqli_real_escape_string($db,$team).'"';
			$legend[] = $team;
		}
		if(!empty($teamsQuery))
		{
			$teamsQueryString = 'AND ('.implode(' OR ',$teamsQuery).')';
		}
	}
	
	$allindividualScores = array();
	$highGoalTimes = array();
	$lowGoalTimes = array();
	$foulCounts = array('foul'=>array(), 'tech_foul'=>array());
	$auto_goals = array('high_goal'=>array(), 'low_goal'=>array());
	$tele_goals = array('high_goal'=>array(), 'low_goal'=>array());
	$defenseCrossings = array();
	$pointValues = getPointValuesByYear($year);
	$query = 'select match_data.*, SUBSTRING_INDEX(`match_key`, "_", 1) as event_key FROM match_data WHERE team_account="'.$teamAccount.'" '.$teamsQueryString.' HAVING event_key = "'.$event.'" ORDER BY timestamp ASC';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	if($result->num_rows > 0)
	{
		while($row=$result->fetch_assoc())
		{
			$team_number = $row['team_number'];
			$match = $row['match_key'];
			$matchStartArr = getMatchData_start($match, $teamAccount);
			$matchStart = $matchStartArr['match_start'];
			$action = $row['action'];
			$timestamp = $row['timestamp'];
			
			$duringAuto = $timestamp-$matchStart <= 15;
			
			if(!isset($allindividualScores[$team_number][$match]))
			{
				$allindividualScores[$team_number][$match] = 0;
			}
			if(!isset($foulCounts['foul'][$team_number][$match]))
			{
				$foulCounts['foul'][$team_number][$match] = 0;
			}
			if(!isset($foulCounts['tech_foul'][$team_number][$match]))
			{
				$foulCounts['tech_foul'][$team_number][$match] = 0;
			}
			if(!isset($auto_goals['high_goal'][$team_number][$match]))
			{
				$auto_goals['high_goal'][$team_number][$match] = 0;
			}
			if(!isset($auto_goals['low_goal'][$team_number][$match]))
			{
				$auto_goals['low_goal'][$team_number][$match] = 0;
			}
			if(!isset($tele_goals['high_goal'][$team_number][$match]))
			{
				$tele_goals['high_goal'][$team_number][$match] = 0;
			}
			if(!isset($tele_goals['low_goal'][$team_number][$match]))
			{
				$tele_goals['low_goal'][$team_number][$match] = 0;
			}
			if(!isset($highGoalTimes[$team_number][$match]))
			{
				$highGoalTimes[$team_number][$match] = array($matchStart);
			}	
			if(!isset($lowGoalTimes[$team_number][$match]))
			{
				$lowGoalTimes[$team_number][$match] = array($matchStart);
			}			
			
			if($action == 'high_goal' && $duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['auto_high_goal'];
				$auto_goals['high_goal'][$team_number][$match] += 1;
			}
			elseif($action == 'high_goal' && !$duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['tele_high_goal'];
				$tele_goals['high_goal'][$team_number][$match] += 1;
				$highGoalTimes[$team_number][$match][] = $timestamp;
			}
			elseif($action == 'low_goal' && $duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['auto_low_goal'];
				$auto_goals['low_goal'][$team_number][$match] += 1;
			}
			elseif($action == 'low_goal' && !$duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['tele_low_goal'];
				$tele_goals['low_goal'][$team_number][$match] += 1;
				$lowGoalTimes[$team_number][$match][] = $timestamp;
			}
			elseif($action == 'challenge_tower')
			{
				$allindividualScores[$team_number][$match] += $pointValues['challenge_tower'];
			}
			elseif($action == 'scale_tower')
			{
				$allindividualScores[$team_number][$match] += $pointValues['scale_tower'];
			}
			elseif($action == 'reach_defense' && $duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['auto_reach_defense'];
			}		
			elseif($action == 'foul')
			{
				$foulCounts['foul'][$team_number][$match] += 1;
			}
			elseif($action == 'tech_foul')
			{
				$foulCounts['tech_foul'][$team_number][$match] += 1;
			}
			elseif($action == 'crossing_defense')
			{
				$defense = $row['attr_1'];
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
				}	
			}
		}
	}
	
	$defenseCrossTimes = array();
	$defenseCrossAtttemps = array('success'=>array(), 'all'=>array());
	foreach($defenseCrossings as $team=>$data)
	{
		foreach($data as $match=>$data2)
		{
			foreach($data2 as $defense=>$crossings)
			{
				foreach($crossings as $cross)
				{
					if(!isset($defenseCrossAtttemps['all'][$defense][$team]))
					{
						$defenseCrossAtttemps['all'][$defense][$team] = 0;
					}
					$defenseCrossAtttemps['all'][$defense][$team] += 1;
					
					if($cross['status'] == 'success')
					{
						$score = $cross['auto'] ? 10:5;
						$allindividualScores[$team][$match] += $score;
						
						if(!isset($defenseCrossTimes[$defense][$team]))
						{
							$defenseCrossTimes[$defense][$team] = array();
						}
						$defenseCrossTimes[$defense][$team][] = $cross['time'];
						
						if(!isset($defenseCrossAtttemps['success'][$defense][$team]))
						{
							$defenseCrossAtttemps['success'][$defense][$team] = 0;
						}
						$defenseCrossAtttemps['success'][$defense][$team] += 1;
					}
				}
			}
		}
	}
	
	$teamList = sort($allTeams);
	$numMatches = array();
	$xLabels = array();
	$individualScores = array();
	ksort($allindividualScores);
	foreach($allindividualScores as $team=>$scores)
	{
		$individualScores[] = array_values($scores);
		$numMatches[] = count($scores);
	}
	$maxNumMatches = max($numMatches);

	for($i=1;$i<=$maxNumMatches;$i++)
	{
		$xLabels[] = $i;
	}
	sort($legend, SORT_NUMERIC );
	$individualScoresArr = array(
		'legend' => $legend,
		'allScores' => $allindividualScores,
		'scores' => $individualScores,
		'xLabels' => $xLabels,
	);

	
	

	$defTimeAvg = array();
	
	$i = 0;
	foreach($defenseCrossTimes as $defense=>$data)
	{
		$k = 0;
		foreach($allTeams as $team)
		{
			$defTimeAvg[$i][$k] = 0;
			if(isset($data[$team]))
			{
				$times = $data[$team];
				if(!empty($times))
				{
					$defTimeAvg[$i][$k] = round(array_sum($times) / count($times),2);
				}
			}
			$k++;
		}
		$i++;
	}

	$defTimesArr = array(
		'scores' => $defTimeAvg,
		'xLabels' => $legend,
		'legend' => array_keys($defenseCrossTimes)
	);
	
	$defAttemps = array();
	$i = 0;
	foreach($defenseCrossAtttemps['all'] as $defense=>$data)
	{
		$k = 0;
		foreach($allTeams as $team)
		{
			$defAttemps[$i][$k] = 0;
			if(isset($data[$team]) && array_key_exists($team,$data))
			{
				$allAttempts = $data[$team];
				$defAttemps[$i][$k] = $defenseCrossAtttemps['success'][$defense][$team] / $allAttempts;
			}
			$k++;
		}
		$i++;
	}
	
	$defAttemptsArr = array(
		'scores' => $defAttemps,
		'xLabels' => $legend,
		'legend' => array_keys($defenseCrossAtttemps['all'])
	);
	
	// Average/Total Points
	$averagePoints = array();
	$sumPoints = array();
	$teamPoints = array();
	// Total/Average Tele points
	$tele_high_sum = array();
	$tele_high_avg = array();
	$tele_low_sum = array();
	$tele_low_avg = array();
	// Total/Average Auto points
	$auto_high_sum = array();
	$auto_high_avg = array();
	$auto_low_sum = array();
	$auto_low_avg = array();
	// Fouls
	$regFoulsSum = array();
	$regFoulsAvg = array();
	$techFoulsSum = array();
	$techFoulsAvg = array();
	
	$i = 0;
	foreach($allTeams as $team)
	{
		//Average/Total Points
		$averagePoints[$i] = 0;
		$sumPoints[$i] = 0;
		if(isset($allindividualScores[$team]) && array_key_exists($team,$allindividualScores))
		{
			$scores = $allindividualScores[$team];
			$averagePoints[$i] = array_sum($scores) / count($scores);
			$sumPoints[$i] = array_sum($scores);
		}
		// Total/Average Tele points
		$tele_high_avg[$i] = 0;
		$tele_high_sum[$i] = 0;
		$tele_low_avg[$i] = 0;
		$tele_low_sum[$i] = 0;
		if(isset($tele_goals['high_goal'][$team]) && array_key_exists($team,$tele_goals['high_goal']))
		{
			$goals = $tele_goals['high_goal'][$team];
			$tele_high_avg[$i] = array_sum($goals) / count($goals);
			$tele_high_sum[$i] = array_sum($goals);
		}
		if(isset($tele_goals['low_goal'][$team]) && array_key_exists($team,$tele_goals['low_goal']))
		{
			$goals = $tele_goals['low_goal'][$team];
			$tele_low_avg[$i] = array_sum($goals) / count($goals);
			$tele_low_sum[$i] = array_sum($goals);
		}
		// Total/Average Auto points
		$auto_high_avg[$i] = 0;
		$auto_high_sum[$i] = 0;
		$auto_low_avg[$i] = 0;
		$auto_low_sum[$i] = 0;
		if(isset($auto_goals['high_goal'][$team]) && array_key_exists($team,$auto_goals['high_goal']))
		{
			$goals = $auto_goals['high_goal'][$team];
			$auto_high_avg[$i] = array_sum($goals) / count($goals);
			$auto_high_sum[$i] = array_sum($goals);
		}
		if(isset($auto_goals['low_goal'][$team]) && array_key_exists($team,$auto_goals['low_goal']))
		{
			$goals = $auto_goals['low_goal'][$team];
			$auto_low_avg[$i] = array_sum($goals) / count($goals);
			$auto_low_sum[$i] = array_sum($goals);
		}		
		//Fouls
		$regFoulsSum[] = 0;
		$regFoulsAvg[] = 0;
		$techFoulsSum[] = 0;
		$techFoulsAvg[] = 0;
		if(isset($foulCounts['foul'][$team]) && array_key_exists($team,$foulCounts['foul']))
		{
			$fouls = $foulCounts['foul'][$team];
			$regFoulsAvg[$i] = array_sum($fouls) / count($fouls);
			$regFoulsSum[$i] = array_sum($fouls);
		}
		if(isset($foulCounts['tech_foul'][$team]) && array_key_exists($team,$foulCounts['tech_foul']))
		{
			$goals = $foulCounts['tech_foul'][$team];
			$techFoulsAvg[$i] = array_sum($fouls) / count($fouls);
			$techFoulsSum[$i] = array_sum($fouls);
		}
		
		$i++;
	}

	$totalScoresArr = array(
		'scores' => array($averagePoints,$sumPoints),
		'xLabels' => $legend,
		'legend' => array('Average Points Per Match','Total Points Scored')
	);
	$autoGoalsArr = array(
		'scores' => array($auto_high_sum,$auto_low_sum,$auto_high_avg,$auto_low_avg),
		'xLabels' => $legend,
		'legend' => array('Total High Goals Scored','Total Low Goals Scored','Avg. High Goals Scored per Match','Avg. low Goals Scored per Match')
	);
	$teleGoalsArr = array(
		'scores' => array($tele_high_sum,$tele_low_sum,$tele_high_avg,$tele_low_avg),
		'xLabels' => $legend,
		'legend' => array('Total High Goals Scored','Total Low Goals Scored','Avg. High Goals Scored per Match','Avg. low Goals Scored per Match')
	);
	$foulsArr = array(
		'scores' => array($regFoulsSum,$techFoulsSum,$regFoulsAvg,$techFoulsAvg),
		'xLabels' => $legend,
		'legend' => array('Total Fouls','Total Tech Fouls','Avg. Fouls per Match','Avg. Tech Fouls per Match')
	);
	
	
	
	$highGoalDiff = array();
	$lowGoalDiff = array();
	foreach($highGoalTimes as $team=>$data)
	{
		foreach($data as $match=>$timeArr)
		{
			foreach($timeArr as $i=>$time)
			{
				if($i != 0)
				{
					$temp = $time - $timeArr[$i-1];
					$highGoalDiff[$team][] = $temp;
				}
			}
		}
	}
	foreach($lowGoalTimes as $team=>$data)
	{
		foreach($data as $match=>$timeArr)
		{
			foreach($timeArr as $i=>$time)
			{
				if($i != 0)
				{
					$temp = $time - $timeArr[$i-1];
					$lowGoalDiff[$team][] = $temp;
				}
			}
		}
	}
	$highGoalTimesAvg = array();
	$lowGoalTimesAvg = array();
	foreach($highGoalDiff as $team=>$times)
	{
		$highGoalTimesAvg[$team] = array_sum($times) / count($times);
	}
	foreach($lowGoalDiff as $team=>$times)
	{
		$lowGoalTimesAvg[$team] = array_sum($times) / count($times);
	}
	$highGoalsTimesArr = array(
		'scores' => array_values($highGoalTimesAvg),
		'xLabels' => array_keys($highGoalTimesAvg),
		'legend' => array('Average Time for High Goal')
	);
	$lowGoalsTimesArr = array(
		'scores' => array_values($lowGoalTimesAvg),
		'xLabels' => array_keys($lowGoalTimesAvg),
		'legend' => array('Average Time for High Goal')
	);
	
	
	
	
	$data = array(
		'individualScores'=>$individualScoresArr,
		'averageScores'=>$totalScoresArr,
		'fouls'=>$foulsArr,
		'autoGoals'=>$autoGoalsArr,
		'teleGoals'=>$teleGoalsArr,
	//	'defenseCrossingsRaw'=>$defenseCrossings,
		'defenseCrossingsTimes'=>$defTimesArr,
		'defenseAttempts'=>$defAttemptsArr,
		'defenseAttempts1'=>$defenseCrossAtttemps['all'],
		'defenseAttempts2'=>$defenseCrossAtttemps['success'],
		'highGoalsTimes'=>$highGoalsTimesArr,
		'lowGoalsTimes'=>$lowGoalsTimesArr,
		'query' => $query
	);
	return $data;
}

function get2017ReportData($event, $allTeams, $teamAccount)
{
	global $db;
	
	$year = 2017;
	
	$data = array();
	$teamsQuery = array();
	$legend = array();
	$teamsQueryString = '';	
	if(isset($allTeams))
	{
		foreach($allTeams as $team)
		{
			$teamsQuery[] = 'team_number="'.mysqli_real_escape_string($db,$team).'"';
			$legend[] = $team;
		}
		if(!empty($teamsQuery))
		{
			$teamsQueryString = 'AND ('.implode(' OR ',$teamsQuery).')';
		}
	}
	
	$allindividualScores = array();
	$autoPoints = array();
	$telePoints = array();
	$foulCounts = array('foul'=>array(), 'tech_foul'=>array());
	$auto_goals = array('high_goal'=>array(), 'low_goal'=>array());
	$tele_goals = array('high_goal'=>array(), 'low_goal'=>array());
	$num_goals = array('high_goal'=>array(), 'low_goal'=>array());
	$gearsDelivered = array();


	$pointValues = getPointValuesByYear($year);
	$current_match = $event.'_qm1';
	$matchStartArr = getMatchData_start($current_match, $teamAccount);
	$matchStart = $matchStartArr['match_start'];
	$query = 'select match_data.*, SUBSTRING_INDEX(`match_key`, "_", 1) as event_key FROM match_data WHERE team_account="'.$teamAccount.'" '.$teamsQueryString.' HAVING event_key = "'.$event.'" ORDER BY timestamp ASC';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	if($result->num_rows > 0)
	{
		while($row=$result->fetch_assoc())
		{
			$team_number = $row['team_number'];
			$match = $row['match_key'];
			if($match != $current_match)
			{
				$matchStartArr = getMatchData_start($match, $teamAccount);
				$matchStart = $matchStartArr['match_start'];
				$current_match = $match;
			}
			$action = $row['action'];
			$timestamp = $row['timestamp'];
			$attr_1 = $row['attr_1'];
			$multiplier = isset($attr_1) && $attr_1!=0 ? (int)$attr_1:1;

			$duringAuto = $timestamp-$matchStart <= 15;
			
			if(!isset($allindividualScores[$team_number][$match]))
			{
				$allindividualScores[$team_number][$match] = 0;
			}
			if(!isset($foulCounts['foul'][$team_number][$match]))
			{
				$foulCounts['foul'][$team_number][$match] = 0;
			}
			if(!isset($foulCounts['tech_foul'][$team_number][$match]))
			{
				$foulCounts['tech_foul'][$team_number][$match] = 0;
			}
			if(!isset($auto_goals['high_goal'][$team_number][$match]))
			{
				$auto_goals['high_goal'][$team_number][$match] = 0;
			}
			if(!isset($auto_goals['low_goal'][$team_number][$match]))
			{
				$auto_goals['low_goal'][$team_number][$match] = 0;
			}
			if(!isset($tele_goals['high_goal'][$team_number][$match]))
			{
				$tele_goals['high_goal'][$team_number][$match] = 0;
			}
			if(!isset($tele_goals['low_goal'][$team_number][$match]))
			{
				$tele_goals['low_goal'][$team_number][$match] = 0;
			}
			if(!isset($num_goals['high_goal'][$team_number][$match]))
			{
				$num_goals['high_goal'][$team_number][$match] = 0;
			}
			if(!isset($num_goals['low_goal'][$team_number][$match]))
			{
				$num_goals['low_goal'][$team_number][$match] = 0;
			}
			if(!isset($autoPoints[$team_number][$match]))
			{
				$autoPoints[$team_number][$match] = 0;
			}
			if(!isset($telePoints[$team_number][$match]))
			{
				$telePoints[$team_number][$match] = 0;
			}
			if(!isset($gearsDelivered[$team_number][$match]))
			{
				$gearsDelivered[$team_number][$match] = 0;
			}			
			
			if($action == 'high_goal' && $duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['auto_high_goal']*$multiplier;
				$autoPoints[$team_number][$match] += $pointValues['auto_high_goal']*$multiplier;
				$auto_goals['high_goal'][$team_number][$match] += 1*$multiplier;
				$num_goals['high_goal'][$team_number][$match] += 1*$multiplier;
			}
			elseif($action == 'high_goal' && !$duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['tele_high_goal']*$multiplier;
				$telePoints[$team_number][$match] += $pointValues['tele_high_goal']*$multiplier;
				$tele_goals['high_goal'][$team_number][$match] += 1*$multiplier;
				$num_goals['high_goal'][$team_number][$match] += 1*$multiplier;
				
			}
			elseif($action == 'low_goal' && $duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['auto_low_goal']*$multiplier;
				$autoPoints[$team_number][$match] += $pointValues['auto_low_goal']*$multiplier;
				$auto_goals['low_goal'][$team_number][$match] += 1*$multiplier;
				$num_goals['low_goal'][$team_number][$match] += 1*$multiplier;
			}
			elseif($action == 'low_goal' && !$duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['tele_low_goal']*$multiplier;
				$telePoints[$team_number][$match] += $pointValues['tele_low_goal']*$multiplier;
				$tele_goals['low_goal'][$team_number][$match] += 1*$multiplier;
				$num_goals['low_goal'][$team_number][$match] += 1*$multiplier;
			}
			elseif($action == 'ready_takeoff')
			{
				$allindividualScores[$team_number][$match] += $pointValues['ready_takeoff'];
				$telePoints[$team_number][$match] += $pointValues['ready_takeoff'];
			}
			elseif($action == 'cross_baseline' && $duringAuto)
			{
				$allindividualScores[$team_number][$match] += $pointValues['auto_cross_baseline'];
				$autoPoints[$team_number][$match] += $pointValues['auto_cross_baseline'];
			}
			elseif($action == 'deliver_gear')
			{
				$gearsDelivered[$team_number][$match] += 1;
			}		
			elseif($action == 'foul')
			{
				$foulCounts['foul'][$team_number][$match] += 1;
			}
			elseif($action == 'tech_foul')
			{
				$foulCounts['tech_foul'][$team_number][$match] += 1;
			}
		}
	}
		
	$teamList = sort($allTeams);
	$numMatches = array();
	$xLabels = array();
	$individualScores = array();
	ksort($allindividualScores);
	foreach($allindividualScores as $team=>$scores)
	{
		$individualScores[] = array_values($scores);
		$numMatches[] = count($scores);
	}
	$maxNumMatches = max($numMatches);

	for($i=1;$i<=$maxNumMatches;$i++)
	{
		$xLabels[] = $i;
	}
	sort($legend, SORT_NUMERIC );
	$individualScoresArr = array(
		'legend' => $legend,
		'allScores' => $allindividualScores,
		'scores' => $individualScores,
		'xLabels' => $xLabels,
	);
	
	// Average/Total Points
	$averagePoints = array();
	$sumPoints = array();
	$teamPoints = array();
	// Average/Total Auto Points
	$auto_points_sum = array();
	$auto_points_avg = array();
	// Average/Total Tele Points
	$tele_points_sum = array();
	$tele_points_avg = array();
	// Total/Average Tele Goals
	$tele_high_sum = array();
	$tele_high_avg = array();
	$tele_low_sum = array();
	$tele_low_avg = array();
	// Total/Average Auto Goals
	$auto_high_sum = array();
	$auto_high_avg = array();
	$auto_low_sum = array();
	$auto_low_avg = array();
	// Total/Average Goals
	$high_sum = array();
	$high_avg = array();
	$low_sum = array();
	$low_avg = array();
	// Fouls
	$regFoulsSum = array();
	$regFoulsAvg = array();
	$techFoulsSum = array();
	$techFoulsAvg = array();
	// Gears Delivered
	$gears_delivered_sum = array();
	$gears_delivered_avg = array();
	
	$i = 0;
	foreach($allTeams as $team)
	{
		//Average/Total Points
		$averagePoints[$i] = 0;
		$sumPoints[$i] = 0;
		if(isset($allindividualScores[$team]) && array_key_exists($team,$allindividualScores))
		{
			$scores = $allindividualScores[$team];
			$averagePoints[$i] = array_sum($scores) / count($scores);
			$sumPoints[$i] = array_sum($scores);
		}
		//Average/Total Auto Points
		$auto_points_sum[$i] = 0;
		$auto_points_avg[$i] = 0;
		if(isset($autoPoints[$team]) && array_key_exists($team,$autoPoints))
		{
			$scores = $autoPoints[$team];
			$auto_points_avg[$i] = array_sum($scores) / count($scores);
			$auto_points_sum[$i] = array_sum($scores);
		}
		//Average/Total Tele Points
		$tele_points_sum[$i] = 0;
		$tele_points_avg[$i] = 0;
		if(isset($telePoints[$team]) && array_key_exists($team,$telePoints))
		{
			$scores = $telePoints[$team];
			$tele_points_avg[$i] = array_sum($scores) / count($scores);
			$tele_points_sum[$i] = array_sum($scores);
		}
		// Total/Average Tele points
		$tele_high_avg[$i] = 0;
		$tele_high_sum[$i] = 0;
		$tele_low_avg[$i] = 0;
		$tele_low_sum[$i] = 0;
		if(isset($tele_goals['high_goal'][$team]) && array_key_exists($team,$tele_goals['high_goal']))
		{
			$goals = $tele_goals['high_goal'][$team];
			$tele_high_avg[$i] = array_sum($goals) / count($goals);
			$tele_high_sum[$i] = array_sum($goals);
		}
		if(isset($tele_goals['low_goal'][$team]) && array_key_exists($team,$tele_goals['low_goal']))
		{
			$goals = $tele_goals['low_goal'][$team];
			$tele_low_avg[$i] = array_sum($goals) / count($goals);
			$tele_low_sum[$i] = array_sum($goals);
		}
		// Total/Average Auto points
		$auto_high_avg[$i] = 0;
		$auto_high_sum[$i] = 0;
		$auto_low_avg[$i] = 0;
		$auto_low_sum[$i] = 0;
		if(isset($auto_goals['high_goal'][$team]) && array_key_exists($team,$auto_goals['high_goal']))
		{
			$goals = $auto_goals['high_goal'][$team];
			$auto_high_avg[$i] = array_sum($goals) / count($goals);
			$auto_high_sum[$i] = array_sum($goals);
		}
		if(isset($auto_goals['low_goal'][$team]) && array_key_exists($team,$auto_goals['low_goal']))
		{
			$goals = $auto_goals['low_goal'][$team];
			$auto_low_avg[$i] = array_sum($goals) / count($goals);
			$auto_low_sum[$i] = array_sum($goals);
		}
		// Total/Average Auto points
		$high_avg[$i] = 0;
		$high_sum[$i] = 0;
		$low_avg[$i] = 0;
		$low_sum[$i] = 0;
		if(isset($num_goals['high_goal'][$team]) && array_key_exists($team,$num_goals['high_goal']))
		{
			$goals = $num_goals['high_goal'][$team];
			$high_avg[$i] = array_sum($goals) / count($goals);
			$high_sum[$i] = array_sum($goals);
		}
		if(isset($num_goals['low_goal'][$team]) && array_key_exists($team,$num_goals['low_goal']))
		{
			$goals = $num_goals['low_goal'][$team];
			$low_avg[$i] = array_sum($goals) / count($goals);
			$low_sum[$i] = array_sum($goals);
		}		
		//Fouls
		$regFoulsSum[] = 0;
		$regFoulsAvg[] = 0;
		$techFoulsSum[] = 0;
		$techFoulsAvg[] = 0;
		if(isset($foulCounts['foul'][$team]) && array_key_exists($team,$foulCounts['foul']))
		{
			$fouls = $foulCounts['foul'][$team];
			$regFoulsAvg[$i] = array_sum($fouls) / count($fouls);
			$regFoulsSum[$i] = array_sum($fouls);
		}
		if(isset($foulCounts['tech_foul'][$team]) && array_key_exists($team,$foulCounts['tech_foul']))
		{
			$goals = $foulCounts['tech_foul'][$team];
			$techFoulsAvg[$i] = array_sum($fouls) / count($fouls);
			$techFoulsSum[$i] = array_sum($fouls);
		}
		//Gears Delivered
		$gears_delivered_sum[] = 0;
		$gears_delivered_avg[] = 0;
		if(isset($gearsDelivered[$team]) && array_key_exists($team,$gearsDelivered))
		{
			$gears = $gearsDelivered[$team];
			$gears_delivered_avg[$i] = array_sum($gears) / count($gears);
			$gears_delivered_sum[$i] = array_sum($gears);
		}
		
		$i++;
	}

	$totalScoresArr = array(
		'scores' => array($averagePoints,$sumPoints),
		'xLabels' => $legend,
		'legend' => array('Average Points Per Match','Total Points Scored')
	);
	$autoPointsArr = array(
		'scores' => array($auto_points_avg,$auto_points_sum),
		'xLabels' => $legend,
		'legend' => array('Average Points Per Match','Total Points Scored')
	);
	$telePointsArr = array(
		'scores' => array($tele_points_avg,$tele_points_sum),
		'xLabels' => $legend,
		'legend' => array('Average Points Per Match','Total Points Scored')
	);
	$autoGoalsArr = array(
		'scores' => array($auto_high_sum,$auto_low_sum,$auto_high_avg,$auto_low_avg),
		'xLabels' => $legend,
		'legend' => array('Total High Goals Scored','Total Low Goals Scored','Avg. High Goals Scored per Match','Avg. low Goals Scored per Match')
	);
	$teleGoalsArr = array(
		'scores' => array($tele_high_sum,$tele_low_sum,$tele_high_avg,$tele_low_avg),
		'xLabels' => $legend,
		'legend' => array('Total High Goals Scored','Total Low Goals Scored','Avg. High Goals Scored per Match','Avg. low Goals Scored per Match')
	);
	$numGoalsArr = array(
		'scores' => array($high_sum,$low_sum,$high_avg,$low_avg),
		'xLabels' => $legend,
		'legend' => array('Total High Goals Scored','Total Low Goals Scored','Avg. High Goals Scored per Match','Avg. low Goals Scored per Match')
	);
	$foulsArr = array(
		'scores' => array($regFoulsSum,$techFoulsSum,$regFoulsAvg,$techFoulsAvg),
		'xLabels' => $legend,
		'legend' => array('Total Fouls','Total Tech Fouls','Avg. Fouls per Match','Avg. Tech Fouls per Match')
	);
	$gearsDeliveredArr = array(
		'scores' => array($gears_delivered_sum,$gears_delivered_avg),
		'xLabels' => $legend,
		'legend' => array('Total Gears Delivered','Avg. Gears Delivered per Match')
	);
	
		$data = array(
		'individualScores'=>$individualScoresArr,
		'autoPoints'=>$autoPointsArr,
		'telePoints'=>$telePointsArr,
		'averageScores'=>$totalScoresArr,
		'fouls'=>$foulsArr,
		'autoGoals'=>$autoGoalsArr,
		'teleGoals'=>$teleGoalsArr,
		'numGoals'=>$numGoalsArr,
		'gearsDelivered'=>$gearsDeliveredArr,
		'query' => $query
	);
	return $data;
}

?>