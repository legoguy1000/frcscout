<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken(true,true);
//die(json_encode($authToken));
$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['endpoint']) || $formData['endpoint'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}


$userId = $authToken['data']['id'];

$query = 'DELETE FROM notification_endpoints WHERE endpoint="'.$formData['endpoint'].'" AND user_id="'.$userId.'"';
$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
$msg = 'Device Subscription Removed';
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));



?>