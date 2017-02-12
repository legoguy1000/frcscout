<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken();
//die(json_encode($authToken));
$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['id']) || $formData['id'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid user ID.')));
}
verifyTeamPrivs($authToken['data']['id'], 'admin', $die = true);

$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];

removeTeamMembership($formData['id'], $team);
$newMembership = getTeamMembership($team);
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Team membership denied for '.$formData['userInfo']['full_name'], 'membership'=>$newMembership)));



?>