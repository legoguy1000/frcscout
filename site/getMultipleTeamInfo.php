<?php
include('includes.php');

//$authToken = checkToken();
$data = array();
$teams = $_GET['teams'];
if($teams == '')
{
	die(json_encode($data));
}
$teamsArr = explode(',',$teams);
foreach($teamsArr as $team)
{
	$query = 'select * from teams WHERE team_number = "'.$team.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$data[] = $row;
	}
}
die(json_encode($data));

?>