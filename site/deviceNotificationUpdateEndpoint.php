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
$query = 'select * from notification_endpoints WHERE endpoint="'.mysqli_real_escape_string($db, $formData['endpoint']).'"';
$result = $db->query($query) or die(errorHandle(mysqli_error($db), $query));
if($result->num_rows > 0)
{
	$row = $result->fetch_assoc();
	if($userId != $row['user_id'])
	{
		$query = 'UPDATE notification_endpoints SET user_id="'.$userId.'", auth_secret="'.mysqli_real_escape_string($db, $formData['authSecret']).'", public_key="'.mysqli_real_escape_string($db, $formData['key']).'" WHERE endpoint="'.mysqli_real_escape_string($db, $formData['endpoint']).'"';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db), $query));
	}
}
else
{
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
}
$msg = 'Device Subscription Endpoint Updated';
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));



?>