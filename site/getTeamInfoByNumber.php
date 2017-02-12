<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken();
//die(json_encode($authToken));
/* $json = file_get_contents('php://input'); 
$formData = json_decode($json,true); */

$team = '';
$data = array();
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];
if(isset($_GET['team']) && $_GET['team']!='' && $_GET['team']!='undefined')
{
	$team = $_GET['team'];
}
$query = 'select teams.*, team_accounts.* from teams INNER JOIN team_accounts ON teams.team_number=team_accounts.team_number WHERE teams.team_number="'.$team.'"';
$result = $db->query($query) or die(errorHandle(mysqli_error($db), $query));
if($result->num_rows > 0)
{		
	$row = $result->fetch_assoc();
	$data = $row;
	$data['membership'] = getTeamMembership($team);
}
die(json_encode($data));




?>