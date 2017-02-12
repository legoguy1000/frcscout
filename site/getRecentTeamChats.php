<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken();
//die(json_encode($authToken));
/* $json = file_get_contents('php://input'); 
$formData = json_decode($json,true); */

$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];
$data = array();

if(isset($_GET['last_id']) && $_GET['last_id']!=''  && $_GET['last_id']!='undefined' && $_GET['last_id']!='null')
{
	$last_id = $_GET['last_id'];
	if($last_id == 'initial')
	{
		$start = '';
	}
	else
	{
		$start = ' AND id < "'.$last_id.'"';
	}
}
else
{
	die(json_encode(array('status'=>false, 'type'=>'error')));
}

$query = 'select * from chat_app where team_number = "'.$team.'"'.$start.' ORDER BY id DESC LIMIT 15';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	while($row = $result->fetch_assoc())
	{
		$id = $row['id'];
		$temp = array();
		$temp = $row;
		$userInfo = getUserDataFromParam('id', $row['user_id']);
		$temp['timestamp_epoch'] = strtotime($row['timestamp']);
		$temp['today'] = date('Ymd') == date('Ymd', $temp['timestamp_epoch']);
		$temp['user_info'] = $userInfo;
		$temp['sent'] = $row['user_id'] == $authToken['data']['id'];
		$data[] = $temp;
	}
	$data = array_reverse($data);
	die(json_encode(array('status'=>true, 'type'=>'success', 'data'=>$data, 'lastId'=>$id, 'data_count'=>count($data))));
}
else
{
	die(json_encode(array('status'=>false, 'type'=>'error', 'data_count'=>0)));
}




?>