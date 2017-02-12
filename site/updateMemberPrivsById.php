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
if(!isset($formData['privs']) || $formData['privs'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Privs Level.')));
}
verifyTeamPrivs($authToken['data']['id'], 'admin', $die = true);

$query = 'UPDATE team_memberships SET privs="'.mysqli_real_escape_string($db, $formData['privs']).'" WHERE user_id="'.mysqli_real_escape_string($db, $formData['id']).'"';
$result = $db->query($query) or die(mysqli_error($db));

die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Team Privs updated for '.$formData['userInfo']['full_name'])));



?>