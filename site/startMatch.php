<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken($die=true,$die401=true);
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
$startArr = getMatchData_start($match_key, $team);
if($startArr !== false)
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Match already started.')));
}
verifyTeamPrivs($authToken['data']['id'], 'write', $die = true);
$id = uniqid();
$time = microtime(true);

$query = 'INSERT INTO match_data (`id`, `team_account`, `user_id`, `match_key`, `action`, `timestamp`) VALUES 
								("'.$id.'",
								 "'.$team.'",
								 "'.$userId.'",
								 "'.mysqli_real_escape_string($db, $match_key).'",
								 "match_start",
								 "'.round($time,4).'")';
$result = $db->query($query) or die(mysqli_error($db));
$dataToWS = array(
	'type' => 'match_start',
	'team' => $team,
	'match_key' => $match_key
);
newMessageToWS($dataToWS);

$msg = 'Match '.$formData['match_number'].' started.';
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));



?>