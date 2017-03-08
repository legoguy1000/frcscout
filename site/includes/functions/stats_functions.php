<?php
function getPointValuesByYear($year)
{
	$data = array();
	if($year == 2016)
	{
		$data['auto_high_goal'] = 10;
		$data['auto_low_goal'] = 5;
		$data['tele_high_goal'] = 5;
		$data['tele_low_goal'] = 2;
		$data['challenge_tower'] = 5;
		$data['scale_tower'] = 15;
		$data['auto_reach_defense'] = 2;
	}
	if($year == 2017)
	{
		$data['auto_high_goal'] = 1;
		$data['auto_low_goal'] = 1/3;
		$data['tele_high_goal'] = 1/3;
		$data['tele_low_goal'] = 1/9;
		$data['cross_baseline'] = 5;
		$data['ready_takeoff'] = 50;
	}
	return $data;
}

function getMatchStatsCategoriesByYear($year)
{
	$data = array();
	if($year == 2016)
	{
		$data = array(
			'auto_high_goal_count' => 0,
			'auto_low_goal_count' => 0,
			'auto_goal_points' => 0,
			'tele_high_goal_count' => 0,
			'tele_low_goal_count' => 0,
			'tele_goal_points' => 0,
			'challenge_tower_points' => 0,
			'scale_tower_points' => 0,
			'auto_reach_defense_points' => 0,
			'foul_count' => 0,
			'tech_foul_count' => 0,
			'auto_points' => 0,
			'tele_points' => 0
		);
	}
	elseif($year == 2017)
	{
		$data = array(
			'auto_high_goal_count' => 0,
			'auto_low_goal_count' => 0,
			'auto_goal_points' => 0,
			'tele_high_goal_count' => 0,
			'tele_low_goal_count' => 0,
			'tele_goal_points' => 0,
			'auto_cross_baseline_points' => 0,
			'ready_takeoff_points' => 0,
			'foul_count' => 0,
			'tech_foul_count' => 0,
			'auto_points' => 0,
			'tele_points' => 0
		);
	}
	return $data;
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
	$dataRows = db_select($query)
	if(count($dataRows) > 0)
	{
		if($year == 2016)
		{
			foreach($dataRows as $row)
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
				}
			}
		}
		elseif($year == 2017)
		{
			foreach($dataRows as $row)
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
				elseif($action == 'ready_takeoff')
				{
					$data[$alli]['ready_takeoff_points'] += $pointValues['ready_takeoff'];
					$data[$alli]['tele_points'] += $pointValues['ready_takeoff'];
				}
				elseif($action == 'cross_baseline' && $duringAuto)
				{
					$data[$alli]['auto_cross_baseline_points'] += $pointValues['cross_baseline'];
					$data[$alli]['auto_points'] += $pointValues['cross_baseline'];
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

function getRobotOptionsByYear($year)
{
	$data = array();
	if($year == 2016)
	{
		$data = array(
			'drive_train' => '',
			'portcullis' =>  '',
			'cheval_de_frise' =>  '',
			'ramparts' =>  '',
			'drawbridge' =>  '',
			'sally_port' =>  '',
			'moat' =>  '',
			'rock_wall' =>  '',
			'rough_terrain' =>  '',
			'low_bar' =>  '',
			'low_goal' =>  '',
			'high_goal' =>  '',
			'climb_tower' =>  '',
			'block' =>  ''
		);
	}
	elseif($year == 2017)
	{
		$data = array(
			'drive_train' => '',
			'low_goal' =>  '',
			'high_goal' =>  '',
			'deliver_gears' =>  '',
			'climb_rope' =>  '',
			'fuel_hopper' =>  '',
			'loading_station' =>  '',
			'floor_pickup' =>  '',
			'fuel_capacity' =>  ''
		);
	}
	return $data;
}
?>
