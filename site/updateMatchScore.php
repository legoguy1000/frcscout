<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken();

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['match_key']) || $formData['match_key'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Match Key.')));
}
if(!isset($formData['match_key']) || $formData['match_key'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Match Key.')));
}

$match_info = getMatchByMatchKey($formData['match_key']);
if($match_info['completed'] == true)
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Scores cannot be updated.  Match is complete.')));
}

$queryArr = array();
if(isset($formData['red_score']) && $formData['red_score'] != '' && $formData['red_score'] != 'null' && $formData['red_score'] != '-1')
{
	$queryArr[] = 'red_score="'.mysqli_real_escape_string($db, $formData['red_score']).'"';
}
if(isset($formData['blue_score']) && $formData['blue_score'] != '' && $formData['blue_score'] != 'null' && $formData['blue_score'] != '-1')
{
	$queryArr[] = 'blue_score="'.mysqli_real_escape_string($db, $formData['blue_score']).'"';
}
$queryStr = '';
if(!empty($queryArr))
{
	$queryStr = implode(', ',$queryArr);
	$query = 'UPDATE match_info SET '.$queryStr.' WHERE match_key="'.mysqli_real_escape_string($db, $formData['match_key']).'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db), $query));
	$dataToWS = array(
		'type' => 'match_info',
		'match_key' => $formData['match_key']
	);
	newMessageToWS($dataToWS);
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Match Info Updated.')));
}

die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'No data.')));


?>