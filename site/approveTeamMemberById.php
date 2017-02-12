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

$query = 'UPDATE team_memberships SET status="joined", privs="read" WHERE user_id="'.mysqli_real_escape_string($db, $formData['id']).'" AND team_number="'.mysqli_real_escape_string($db, $team).'"';
$result = $db->query($query) or die(mysqli_error($db));

$msg_data = array(
	'users' => array($formData['id']),
	'push' => array(
		'subject' => 'Team Membership',
		'message' => 'Team '.$team.' has approved your membership request.'
	)
);
newMessageToQueue('user_notification', $msg_data);

$newMembership = getTeamMembership($team);
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Team membership confirmed for '.$formData['userInfo']['full_name'], 'membership'=>$newMembership)));



?>