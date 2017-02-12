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
		$data['auto_cross_baseline'] = 5;
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
	return $data;
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
			'fuel_capacity' =>  ''
		);
	}
	return $data;
}
?>