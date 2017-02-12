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

$query = 'insert into team_accounts (`team_number`, `contact_id`, `current_event`, `logo`, `background_header`, `background_body`, `font_color_header`, `font_color_body`) VALUES ("'.mysqli_real_escape_string($db, $formData['team_number']).'", "", NULL, "", "", "", "", "")';

$result = $db->query($query) or die(json_encode(errorHandle(mysqli_error($db), $query)));

$id = uniqid();
$query = 'insert into team_memberships (id, team_number, user_id, privs, status) VALUES
										("'.mysqli_real_escape_string($db, $id).'", 
										"'.mysqli_real_escape_string($db, $formData['team_number']).'",
										"'.mysqli_real_escape_string($db, $formData['user_id']).'",
										"admin",
										"joined")';
$result = $db->query($query) or die(json_encode(errorHandle(mysqli_error($db), $query)));

$teamInfo = getTeamInfoByUser($formData['user_id']);
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'You have successfully registered Team '.$formData['team_number'].'.', 'teamInfo'=>$teamInfo)));



?>