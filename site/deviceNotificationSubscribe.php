<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken(true,true);
//die(json_encode($authToken));
$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['endpoint']) || $formData['endpoint'] == '' || !isset($formData['key']) || $formData['key'] == '' || !isset($formData['authSecret']) || $formData['authSecret'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}


$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];

$id = uniqid();
$query = 'INSERT INTO notification_endpoints (`id`, `user_id`, `endpoint`, `auth_secret`, `public_key`) VALUES 
								("'.$id.'",
								 "'.$userId.'",
								 "'.mysqli_real_escape_string($db, $formData['endpoint']).'",
								 "'.mysqli_real_escape_string($db, $formData['authSecret']).'",
								 "'.mysqli_real_escape_string($db, $formData['key']).'")';
$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
$msg = 'Device Subscription Added';
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));



?>