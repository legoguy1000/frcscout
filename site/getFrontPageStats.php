<?php
include('includes.php');

//$authToken = checkToken();

$data = array(
	'users' => 0,
	'teams' => 0,
	'events' => 0,
	'matches' => 0
);

$query = 'select * from users';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	$data['users'] = $result->num_rows;
}
$query = 'select * from team_accounts';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	$data['teams'] = $result->num_rows;
}
$query = 'SELECT DISTINCT match_info.event_key FROM `match_data` JOIN match_info ON match_info.match_key=match_data.match_key';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	$data['events'] = $result->num_rows;
}
$query = 'select DISTINCT match_key from match_data';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	$data['matches'] = $result->num_rows;
}
die(json_encode($data));
?>