<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken($die=true,$die401=true);
//die(json_encode($authToken));
$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['team_number']) || $formData['team_number'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Team Number.')));
}
$logo = isset($formData['logo']) ? $formData['logo']:'';
$h_fc = isset($formData['font_color_header']) ? $formData['font_color_header']:'';
$h_bg = isset($formData['background_header']) ? $formData['background_header']:'';
$b_fc = isset($formData['font_color_body']) ? $formData['font_color_body']:'';
$b_bg = isset($formData['background_body']) ? $formData['background_body']:'';
$current_event = isset($formData['current_event']) ? $formData['current_event']:'';
verifyTeamPrivs($authToken['data']['id'], 'admin', $die = true);


$currentTeamInfo = getTeamInfoByUser($authToken['data']['id']);
if($currentTeamInfo['current_event'] != $current_event)
{
	$eventInfo = getEventInfo($current_event);
	$users = getTeamMembership($formData['team_number'], array('status'=>array('joined'), 'not_user'=>array($authToken['data']['id'])));
	$msg_data = array(
		'users' => $users,
		'push' => array(
			'subject' => 'Team '.$formData['team_number'].' current event changed.',
			'message' => 'The Current Event is the '.$eventInfo['name'].' from '.date('l M d, Y',strtotime($eventInfo['start_date'])).' to '.date('l M d, Y',strtotime($eventInfo['end_date'])),
			'tag' => ''
		)
	);
	newMessageToQueue('user_notification', $msg_data);
}
$query = 'UPDATE team_accounts SET logo="'.mysqli_real_escape_string($db, $logo).'",
								   font_color_header="'.mysqli_real_escape_string($db, $h_fc).'",
								   background_header="'.mysqli_real_escape_string($db, $h_bg).'",
								   font_color_body="'.mysqli_real_escape_string($db, $b_fc).'",
								   background_body="'.mysqli_real_escape_string($db, $b_bg).'",
								   current_event="'.mysqli_real_escape_string($db, $current_event).'"
					WHERE team_number="'.mysqli_real_escape_string($db, $formData['team_number']).'"';
$result = $db->query($query) or die(mysqli_error($db));

die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Team Information updated for Team '.$formData['team_number'])));



?>