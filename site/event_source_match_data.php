<?php
include('includes.php');
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

//sendMessage('', 'Connection Established');
if(isset($_GET['token']))
{
	$token = $_GET['token'];
}
else
{
	header("HTTP/1.1 403 Forbidden");
	//header( 'HTTP/1.1 400 BAD REQUEST' );
	exit;
}
if(!isset($_GET['event']) || !isset($_GET['match']))
{
	header( 'HTTP/1.1 400 BAD REQUEST' );
	exit;
}

$event = $_GET['event'];
$match = $_GET['match'];
$match_key = $event.'_qm'.$match;

$authToken = checkTokenManually($token,true,true);
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];

$lastId = isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? $_SERVER["HTTP_LAST_EVENT_ID"]:'';
if (isset($lastId) && !empty($lastId) && is_numeric($lastId)) {
	$lastId = intval($lastId);
	$lastId++;
}
if(isset($match_key) && $match_key!='')
{
//2016-08-07 22:12:35
	$startTime = microtime(true);
	$startTime2 = microtime(true);
	$scriptStart = $startTime;
	$send=false;
	$checkStart=true;
	$matchStarted=false;
	$masterArr = array();
	$sleepTime = 1;
	while (true) {
		
		/* if($checkStart)
		{	
			$sleepTime = .25;
			$start = getMatchData_start($match_key, $team);
			if($start !== false)
			{
				$match_start_time = $start['match_start'];
				$matchStarted=true;
				$checkStart=false;
				$sleepTime = 1;
				$start['msg'] = 'Match '.$match.' started.';
				sendMessage($lastId, $match_key.'_start', json_encode($start));
				$lastId++;
			}
		} */
		if($matchStarted==true)
		{
			//This will not occur until match started
			/* $query = 'select * from match_data where match_key = "'.$match_key.'" AND team_account = "'.$team.'" AND timestamp > "'.$startTime.'" AND team_number is not NULL ORDER BY timestamp ASC';
			$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
			if($result->num_rows > 0)
			{
				$startTime = microtime(true);
				$data = array();
				while($row = $result->fetch_assoc())
				{
					$timestamp = strtotime($row['timestamp']);
					$data = $row;
					$data['query'] = $query;
					sendMessage($lastId, $match_key.'_data', json_encode($data));
					$lastId++;
					
					//die(json_encode($data));			
				}
				
			} */
			/* $query = 'select * from match_info where match_key = "'.$match_key.'" ORDER BY match_num ASC';
			$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
			if($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$timestamp = strtotime($row['timestamp']);
				if($timestamp >= $startTime2)
				{
					$startTime2 = microtime(true);
					sendMessage($lastId, $match_key.'_info', json_encode($row));
					$lastId++;
				}
			} */	
		}
		sleep($sleepTime);
		
	}
}
function sendMessage($id, $match_key, $data) {
	echo "event: $match_key\n";
	echo "id: $id\n";
	echo "data: $data\n\n";
	ob_flush();
	flush();
}


?>
