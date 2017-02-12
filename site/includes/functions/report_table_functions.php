<?php

function getEventRankings($event_key)
{
	$tbaRankings = tbaApiCallEventRankings($event_key);
	$data = array();
	$headers = array();
	if($tbaRankings !== false)
	{
		foreach($tbaRankings as $i=>$row)
		{
			if($i == 0)
			{
				$headers = $row;
			}
			else
			{
				$team = $row[1];
				$data[$team] = array(
					'rank' => $row[0],
					'played' => $row[2],
					'seeding_score' => $row[3]
				);
			}
		}
	}
	return $data;
}
function get2017ReportTableData($event, $allTeams, $teamAccount)
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
	
	$totalPoints = array();
	$autoPoints = array();
	$telePoints = array();
	$highGoals = array();
	$lowGoals = array();
	$gearsDelivered = array();
	$foulCounts = array();
	$techFoulCounts = array();


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
			
			if(!isset($totalPoints[$team_number][$match]))
			{
				$totalPoints[$team_number][$match] = 0;
			}
			if(!isset($autoPoints[$team_number][$match]))
			{
				$autoPoints[$team_number][$match] = 0;
			}
			if(!isset($telePoints[$team_number][$match]))
			{
				$telePoints[$team_number][$match] = 0;
			}
			if(!isset($highGoals[$team_number][$match]))
			{
				$highGoals[$team_number][$match] = 0;
			}
			if(!isset($lowGoals[$team_number][$match]))
			{
				$lowGoals[$team_number][$match] = 0;
			}
			if(!isset($gearsDelivered[$team_number][$match]))
			{
				$gearsDelivered[$team_number][$match] = 0;
			}
			if(!isset($foulCounts[$team_number][$match]))
			{
				$foulCounts[$team_number][$match] = 0;
			}
			if(!isset($techFoulCounts[$team_number][$match]))
			{
				$techFoulCounts[$team_number][$match] = 0;
			}
					
			
			if($action == 'high_goal' && $duringAuto)
			{
				$totalPoints[$team_number][$match] += $pointValues['auto_high_goal']*$multiplier;
				$autoPoints[$team_number][$match] += $pointValues['auto_high_goal']*$multiplier;
				$highGoals[$team_number][$match] += 1*$multiplier;
			}
			elseif($action == 'high_goal' && !$duringAuto)
			{
				$totalPoints[$team_number][$match] += $pointValues['tele_high_goal']*$multiplier;
				$telePoints[$team_number][$match] += $pointValues['tele_high_goal']*$multiplier;
				$highGoals[$team_number][$match] += 1*$multiplier;
				
			}
			elseif($action == 'low_goal' && $duringAuto)
			{
				$totalPoints[$team_number][$match] += $pointValues['auto_low_goal']*$multiplier;
				$autoPoints[$team_number][$match] += $pointValues['auto_low_goal']*$multiplier;
				$lowGoals[$team_number][$match] += 1*$multiplier;
			}
			elseif($action == 'low_goal' && !$duringAuto)
			{
				$totalPoints[$team_number][$match] += $pointValues['tele_low_goal']*$multiplier;
				$telePoints[$team_number][$match] += $pointValues['tele_low_goal']*$multiplier;
				$lowGoals[$team_number][$match] += 1*$multiplier;
			}
			elseif($action == 'ready_takeoff')
			{
				$totalPoints[$team_number][$match] += $pointValues['ready_takeoff'];
				$telePoints[$team_number][$match] += $pointValues['ready_takeoff'];
			}
			elseif($action == 'cross_baseline' && $duringAuto)
			{
				$totalPoints[$team_number][$match] += $pointValues['auto_cross_baseline'];
				$autoPoints[$team_number][$match] += $pointValues['auto_cross_baseline'];
			}
			elseif($action == 'deliver_gear')
			{
				$gearsDelivered[$team_number][$match] += 1;
			}		
			elseif($action == 'foul')
			{
				$foulCounts[$team_number][$match] += 1;
			}
			elseif($action == 'tech_foul')
			{
				$techFoulCounts[$team_number][$match] += 1;
			}
		}
	}
	$rankings = getEventRankings($event);	
	$teamList = sort($allTeams);
	$numMatches = array();
	$allData = array();
	$graphData = array();
	$i = 0;
	foreach($allTeams as $team)
	{
		//Average/Total Points
		$graphData[$team] = array();
		$allData[$i]['team_number'] = $team;
		$allData[$i]['totalPointsAvg'] = 0;
		$allData[$i]['totalPointsMax'] = 0;
		$graphData[$team]['totalPointsAvg'] = array();
		$allData[$i]['autoPointsAvg'] = 0;
		$allData[$i]['autoPointsMax'] = 0;
		$allData[$i]['telePointsAvg'] = 0;
		$allData[$i]['telePointsMax'] = 0;
		$allData[$i]['highGoalsAvg'] = 0;
		$allData[$i]['highGoalsMax'] = 0;
		$allData[$i]['lowGoalsAvg'] = 0;
		$allData[$i]['lowGoalsMax'] = 0;
		$allData[$i]['gearsDeliveredAvg'] = 0;
		$allData[$i]['gearsDeliveredMax'] = 0;
		$allData[$i]['ranking'] = 0;
		if(array_key_exists($team,$rankings))
		{
			$allData[$i]['ranking'] = $rankings[$team]['rank'];
		}
		if(isset($totalPoints[$team]) && array_key_exists($team,$totalPoints))
		{
			$scores = $totalPoints[$team];
			$allData[$i]['totalPointsAvg'] = array_sum($scores) / count($scores);
			$allData[$i]['totalPointsMax'] = max($scores);
			$graphData[$team]['totalPointsAvg']['scores'] = array(array_values($scores));
			$graphData[$team]['totalPointsAvg']['xLabels'] = range(1,count($scores));
			$graphData[$team]['totalPointsAvg']['legend'] = array('Points Scored per Match');
		}
		if(isset($autoPoints[$team]) && array_key_exists($team,$autoPoints))
		{
			$scores = $autoPoints[$team];
			$allData[$i]['autoPointsAvg'] = array_sum($scores) / count($scores);
			$allData[$i]['autoPointsMax'] = max($scores);
			$graphData[$team]['autoPointsAvg']['scores'] = array(array_values($scores));
			$graphData[$team]['autoPointsAvg']['xLabels'] = range(1,count($scores));
			$graphData[$team]['autoPointsAvg']['legend'] = array('Points Scored During Autonomous per Match');
		}
		if(isset($telePoints[$team]) && array_key_exists($team,$telePoints))
		{
			$scores = $telePoints[$team];
			$allData[$i]['telePointsAvg'] = array_sum($scores) / count($scores);
			$allData[$i]['telePointsMax'] = max($scores);
			$graphData[$team]['telePointsAvg']['scores'] = array(array_values($scores));
			$graphData[$team]['telePointsAvg']['xLabels'] = range(1,count($scores));
			$graphData[$team]['telePointsAvg']['legend'] = array('Points Scored During Teleoperated per Match');
		}
		if(isset($highGoals[$team]) && array_key_exists($team,$highGoals))
		{
			$scores = $highGoals[$team];
			$allData[$i]['highGoalsAvg'] = array_sum($scores) / count($scores);
			$allData[$i]['highGoalsMax'] = max($scores);
			$graphData[$team]['highGoalsAvg']['scores'] = array(array_values($scores));
			$graphData[$team]['highGoalsAvg']['xLabels'] = range(1,count($scores));
			$graphData[$team]['highGoalsAvg']['legend'] = array('Number of High Goals Scored per Match');
		}
		if(isset($lowGoals[$team]) && array_key_exists($team,$lowGoals))
		{
			$scores = $lowGoals[$team];
			$allData[$i]['lowGoalsAvg'] = array_sum($scores) / count($scores);
			$allData[$i]['lowGoalsMax'] = max($scores);
			$graphData[$team]['lowGoalsAvg']['scores'] = array(array_values($scores));
			$graphData[$team]['lowGoalsAvg']['xLabels'] = range(1,count($scores));
			$graphData[$team]['lowGoalsAvg']['legend'] = array('Number of High Goals Scored per Match');
		}
		if(isset($gearsDelivered[$team]) && array_key_exists($team,$gearsDelivered))
		{
			$scores = $gearsDelivered[$team];
			$allData[$i]['gearsDeliveredAvg'] = array_sum($scores) / count($scores);
			$allData[$i]['gearsDeliveredMax'] = max($scores);
			$graphData[$team]['gearsDeliveredAvg']['scores'] = array(array_values($scores));
			$graphData[$team]['gearsDeliveredAvg']['xLabels'] = range(1,count($scores));
			$graphData[$team]['gearsDeliveredAvg']['legend'] = array('Number of Gears Delivered per Match');
		}
		$i++;
	}
	
	$data = array(
		'allData' => $allData,
		'graphData' => $graphData,
		'query' => $query
	);
	return $data;
}

?>