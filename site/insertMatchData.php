<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken(true,true);
//die(json_encode($authToken));
$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['event']) || $formData['event'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Event Key.')));
}
if(!isset($formData['match_number']) || $formData['match_number'] == '' || $formData['match_number'] < 1)
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Match Number.')));
}

$match_key = $formData['event'].'_qm'.$formData['match_number'];
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];
$time = microtime(true);
$start = getMatchData_start($match_key, $team);
if($start === false)
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Match has not started yet.')));
}
elseif($time - $start['match_start'] > 150 && $time - $start['match_start'] <155)
{
	$timeInsrtstr = '"'.round($start['match_start']+150,4).'"';
}
elseif($time - $start['match_start'] >= 155)
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Match is over.')));
}
else
{
	$timeInsrtstr = '"'.round($time,4).'"';
}

if(!isset($formData['team_number']) || $formData['team_number'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Team Number.')));
}
if(!isset($formData['data']) || !isset($formData['data']['action']) || $formData['data']['action']=='')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Data.')));
}

if(oneTimeActionComplete($match_key, $formData['team_number'], $team, $formData['data']['action']))
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Action can only be completed once per team.')));
}


$attr_1 = isset($formData['data']['attr_1']) ? $formData['data']['attr_1']:'';
$attr_2 = isset($formData['data']['attr_2']) ? $formData['data']['attr_2']:'';
$comment = isset($formData['data']['comment']) ? $formData['data']['comment']:'';


verifyTeamPrivs($authToken['data']['id'], 'write', $die = true);
$id = uniqid();
$query = 'INSERT INTO match_data (`id`, `team_account`, `user_id`, `team_number`, `match_key`, `action`, `attr_1`, `attr_2`, `comment`, `timestamp`) VALUES 
								("'.$id.'",
								 "'.$team.'",
								 "'.$userId.'",
								 "'.mysqli_real_escape_string($db, $formData['team_number']).'",
								 "'.mysqli_real_escape_string($db, $match_key).'",
								 "'.mysqli_real_escape_string($db, $formData['data']['action']).'",
								 "'.mysqli_real_escape_string($db, $attr_1).'",
								 "'.mysqli_real_escape_string($db, $attr_2).'",
								 "'.mysqli_real_escape_string($db, $comment).'",
								 '.$timeInsrtstr.')';

$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
$dataToWS = array(
	'type' => 'match_data',
	'team' => $team,
	'team_number' => $formData['team_number'],
	'match_key' => $match_key,
	'action' => $formData['data']['action'],
	'attr_1' => $attr_1,
	'attr_2' => $attr_2,
	'comment' => $comment
);
newMessageToWS($dataToWS);
$attr1_msg = $attr_1!='' ? $attr_1:'';
$attr2_msg = $attr_2!='' ? $attr_2:'';
$msg = ucwords(implode(' ',explode('_',$formData['data']['action']))).' '.$attr1_msg.' '.$attr2_msg.' recorded for Team '.$formData['team_number'];
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));



?>