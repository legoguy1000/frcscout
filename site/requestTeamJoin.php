<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken();
//die(json_encode($authToken));
$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['user_id']) || $formData['user_id'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid user ID.  Reload the page and try again.')));
}
if(!isset($formData['team_number']) || $formData['team_number'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid Team Number.  Reload the page and try again.')));
}
verifyUser($formData['user_id'], $authToken['data']['id'], $die = true);
$id = uniqid();
$query = 'insert into team_memberships (id, team_number, user_id, status) VALUES
										("'.mysqli_real_escape_string($db, $id).'", 
										"'.mysqli_real_escape_string($db, $formData['team_number']).'",
										"'.mysqli_real_escape_string($db, $formData['user_id']).'",
										"pending")';
$result = $db->query($query) or die(json_encode(errorHandle(mysqli_error($db), $query)));
$name = $authToken['data']['full_name'];
$email = $authToken['data']['email'];
$admins = getTeamMembership($formData['team_number'], array('privs'=>array('admin')));
/* foreach($admins as $admin)
{
	sendPushNotificationByUser($admin['user_id'], 'Team Membership Request', $name.' ('.$email.') has requested to join Team '.$formData['team_number'].'.');
} */
$msg_data = array(
	'users' => $admins,
	'push' => array(
		'subject' => 'Team Membership Request',
		'message' => $name.' ('.$email.') has requested to join Team '.$formData['team_number'].'.'
	)
);
newMessageToQueue('user_notification', $msg_data);
$teamInfo = getTeamInfoByUser($formData['user_id']);
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Your Request to join Team '.$formData['team_number'].' has been submitted.', 'teamInfo'=>$teamInfo)));



?>