<?php
use \Firebase\JWT\JWT;


function getTokenFromHeaders()
{
	$return = false;
	$headers = apache_request_headers();
	if(isset($headers['Authorization']))
	{
		$jwt = str_replace('Bearer ','',$headers['Authorization']);
		if($jwt != '')
		{
			$return = $jwt;
		}
	}
	return $return;
}
function checkToken($die=true,$die401=false)
{
	$data = false;
	$jwt = null;
	if(substr(php_sapi_name(), 0, 3) == 'cli') {
		global $AUTH_TOKEN;
		if(isset($AUTH_TOKEN) && $AUTH_TOKEN != null) {
			$jwt = $AUTH_TOKEN;
		}
	}
	elseif(substr(PHP_SAPI, 0, 6) == 'apache') {
		$jwt = getTokenFromHeaders();
	}
	$data = checkTokenManually($jwt,$die,$die401);
	return $data;
}

function checkTokenManually($token,$die=true,$die401=false)
{
	$data = array();
	if(isset($token) && $token != '' && $token != false && $token != null)
	{
		$jwt = $token;
		$key = getIniProp('jwt_key');
		$decoded = JWT::decode($jwt, $key, array('HS256'));
		$decoded_array = json_encode($decoded);
		$data = json_decode($decoded_array,true);
		return $data;
	}
	else
	{
		if($die401)
		{
			header("HTTP/1.1 401 Unauthorized");
			exit;
		}
		elseif($die)
		{
			die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error.  Please try logging in again.')));
		}
		else
		{
			return false;
		}
	}
}

function verifyToken($token,$die=true,$die401=false)
{
	global $app;
	$data = array();
	if(isset($token) && $token != '' && $token != false && $token != null)
	{
		$jwt = $token;
		$key = getIniProp('jwt_key');
		$decoded = JWT::decode($jwt, $key, array('HS256'));
		$decoded_array = json_encode($decoded);
		$data = json_decode($decoded_array,true);
		return $data;
	}
	else
	{
		if($die401)
		{
			header("HTTP/1.1 401 Unauthorized");
			exit;
		}
		elseif($die)
		{
			die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error.  Please try logging in again.')));
		}
		else
		{
			return false;
		}
	}
}

function getRealIpAddr()
{
	$ip = '';
	if(substr(php_sapi_name(), 0, 3) == 'cli') {
		global $WEBSOCKET_IP;
		$ip=$WEBSOCKET_IP;
	}
	elseif(substr(PHP_SAPI, 0, 6) == 'apache') {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
	}
    return $ip;
}

function insertLogs($userId, $type, $status, $msg)
{
	$db = db_connect();
	$id = uniqid();
	$ip = getRealIpAddr();
	$user_id = 'NUll';
	if($userId != '' && $userId != 'NULL') {
		$user_id = ''.db_quote($userId).'"';
	}
	$query = 'INSERT INTO logs (id, user_id, type, status, msg, remote_ip) VALUES ('.db_quote($id).', '.$user_id.', '.db_quote($type).', '.db_quote($status).', '.db_quote($msg).', '.db_quote($ip).')';
	$result = db_query($query);
	return $id;
}

function teamAccountStatus($team)
{
	$db = db_connect();
	$data = false;
	$query = 'select * from team_accounts where team_number ='.db_quote($team);
	$teams = db_select($query);
	if(count($teams) > 0) {
		$data = true;
	}
	return $data;
}

function getCurrentEvents()
{
	$db = db_connect();
	$authToken = checkToken(false, false);
	$current_event = '';
	if($authToken != false) {
		$userId = $authToken['data']['id'];
		$teamInfo = getTeamInfoByUser($userId);
		$team = $teamInfo['team_number'];
		$query = 'select * from team_accounts WHERE team_number='.db_quote($team);
		$team = db_select_single($query);
			if($team['current_event'] != null) {
				$current_event = $team['current_event'];
			}
	}
	$data = array();
	$currentWeek = date('W',time());
	$query = 'select * from events WHERE year = '.db_quote(date('Y')).' ORDER BY name';
	$events = db_select($query);
	$active = array();
	$current = array();
	$other = array();
	$all = array();
	foreach($events as $event)
	{
		$event_week = date('W',strtotime($event['start_date']));
		$temp = $event;
		if($currentWeek == $event_week)
		{
			$temp['start_date_unix'] = strtotime($event['start_date']);
			$temp['end_date_unix'] = strtotime($event['end_date']);
			$temp['team_active'] = false;
			if($event['event_key'] == $current_event)
			{
				$temp['team_active'] = true;
			}
			$data[] = $temp;
		}
	}
	return $data;
}

?>
