<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken();
//die(json_encode($authToken));
$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];
if($team == '' || $userId == '')
{
	die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Error reading Authorization Token, please log out and log back in')));
}
$query = 'INSERT INTO chat_app (team_number, user_id, message) VALUES ("'.mysqli_real_escape_string($db, $team).'", "'.mysqli_real_escape_string($db, $userId).'", "'.mysqli_real_escape_string($db, $formData['message']).'")';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
$users = getTeamMembership($team, array('status'=>array('joined'), 'not_user'=>array($userId)));
/* foreach($users as $user)
{
	sendPushNotificationByUser($user['user_id'], '', 'New messsage from '.$authToken['data']['full_name'], 'chat-message');
} */
$msg_data = array(
	'users' => $users,
	'push' => array(
		'subject' => 'New messsage from '.$authToken['data']['full_name'],
		'message' => $formData['message'],
		'tag' => 'chat-message'
	)
);
newMessageToQueue('user_notification', $msg_data);
die(json_encode(array('status'=>true, 'type'=>array('toast'=>'success', 'alert'=>'success'), 'msg'=>'Message from '.$userId.' to Team '.$team.' sent')));



?>