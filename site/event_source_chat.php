<?php
/* include('includes.php');
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

if(isset($_GET['token']))
{
	$token = $_GET['token'];
}
else
{
	die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error.  Please try logging in again.')));
}

$lastId = isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? $_SERVER["HTTP_LAST_EVENT_ID"] : null;
$timestamp = date('Y-m-d H:i:s');
$idQuery = '';
if (isset($lastId) && !empty($lastId) && is_numeric($lastId)) {
	$lastId = intval($lastId);
	$idQuery = ' AND id > '.$lastId;
}
else
{
	$idQuery = 'AND `timestamp` > "'.$timestamp.'"';
}

$authToken = checkTokenManually($token,true,true);
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];
$masterArr = array();
while (true) {
	$query = 'select * from chat_app WHERE team_number = "'.$team.'" '.$idQuery.' ORDER BY id DESC';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		while($row = $result->fetch_assoc())
		{
			$userInfo = getUserDataFromParam('id', $row['user_id']);
			$data = $row;
			$data['timestamp_epoch'] = strtotime($row['timestamp']);
			$data['today'] = date('Ymd') == date('Ymd', $data['timestamp_epoch']);
		//	$data['query'] = $query;
		//	$data['lastId'] = $lastId;
			$data['user_info'] = $userInfo;
			$data['sent'] = $row['user_id'] == $authToken['data']['id'];
			sendMessage($row['id'], json_encode($data));
			$timestamp = date('Y-m-d H:i:s');
			$idQuery = 'AND `timestamp` > "'.$timestamp.'"';
			//$masterArr[] = $row['id'];
		}
	}
	sleep(1.5);
}
function sendMessage($id, $data) {
	echo "id: $id\n";
	echo "data: $data\n\n";
	ob_flush();
	flush();
} */


?>
