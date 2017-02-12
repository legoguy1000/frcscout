<?php
include('includes.php');

//$authToken = checkToken();
$data = array('status'=>false);
$team = $_GET['team'];
if($team == '')
{
	die(json_encode($data));
}
$query = 'select * from teams INNER JOIN team_accounts ON teams.team_number=team_accounts.team_number WHERE teams.team_number="'.$team.'"';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	$row = $result->fetch_assoc();
	$data = array('status'=>true, 'msg'=>$row, 'active'=>true);
}
else
{
	$data = array('status'=>false, 'active'=>false);
}
die(json_encode($data));

?>