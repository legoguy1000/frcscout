<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken(true,true);

$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];
$year = date('Y');
if(isset($_GET['year']) && $_GET['year'] != 'undefined' && $_GET['year'] != '')
{
	$year =  $_GET['year'];
}

$data = array();
$current_event = '';
$query = 'select * from team_accounts WHERE team_number = "'.$team.'"';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	$row = $result->fetch_assoc();
	if($row['current_event'] != null && strpos($row['current_event'], $year) !== false)
	{
		$current_event = $row['current_event'];
	}
}
$currentWeek = date('W',time());
$query = 'select * from events WHERE year = "'.$year.'" ORDER BY name';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	$active = array();
	$current = array();
	$other = array();
	$all = array();
	while($row = $result->fetch_assoc())
	{
		$event_week = date('W',strtotime($row['start_date']));
		$temp = $row;
		
		if($row['event_key'] == $current_event)
		{
			$temp['status'] = 'Team Active';
			$data['team_active'] = $temp;
			$active = $temp;
		}
		if($currentWeek == $event_week)
		{
			$temp['status'] = 'Current Week';
			$data['current_week'][] = $temp;
			$current[] = $temp;
		}		
		else
		{
			$temp['status'] = 'Other';
			$data['other'][] = $temp;
			$other[] = $temp;
		}
		//$data['all'][] =  $temp;
	}
	$all[] = $active;
	$data['all'] = array_merge($all, $current, $other);
}
die(json_encode($data));



?>