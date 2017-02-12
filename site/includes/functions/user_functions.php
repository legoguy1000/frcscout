<?php

function getUserDataFromParam($param, $value)
{
	global $db;
	$data = array();
	$query = 'select users.* from users WHERE users.'.mysqli_real_escape_string($db, $param).'="'.mysqli_real_escape_string($db, $value).'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	if($result->num_rows > 0)
	{		
		$row = $result->fetch_assoc();
		$data = $row;
		$data['full_name'] = $row['fname'].' '.$row['lname'];
		//$data['team_info'] = getTeamMembershipByUser($row['id']);
		$query2 = 'select notification_preferences.* from notification_preferences WHERE notification_preferences.user_id="'.mysqli_real_escape_string($db, $row['id']).'"';
		$result2 = $db->query($query2) or die(errorHandle(mysqli_error($db),$query));
		$row2 = $result2->fetch_assoc();
		$data['notification_preferences'] = convertNotificationPreferencesToBool($row2);
	}
	else
	{
		$data = false;
	}
	return $data;
}

function checkUserLogin($userData)
{
	global $db;
	$user = getUserDataFromParam('email', $userData['email']);
	if($user != false)
	{
		$data = $user;
	}
	elseif($user == false)
	{
		$id = uniqid();
		$date = date('Y-n-d');
		$query = 'insert into users (id, email, fname, lname, creation)
										values ("'.mysqli_real_escape_string($db, $id).'", 
												"'.mysqli_real_escape_string($db, $userData['email']).'",
												"'.mysqli_real_escape_string($db, $userData['fname']).'",
												"'.mysqli_real_escape_string($db, $userData['lname']).'",
												"'.mysqli_real_escape_string($db, $date).'")';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
		initialUserNotificationPreferences($id);
		$data = getUserDataFromParam('id', $id);
	}
	return $data;
}

function initialUserNotificationPreferences($user_id)
{
	global $db;
	$query = 'insert into notification_preferences (user_id) values ("'.mysqli_real_escape_string($db, $user_id).'")';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
}

function verifyUser($formId, $tokenId, $die = true)
{
	if($formId != $tokenId)
	{
		if($die)
		{
			die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Unauthorized Action')));
		}
		else
		{
			return false;
		}
	}
	else
	{
		return true;
	}
}

function verifyTeamPrivs($userId, $requiredPrivs, $die = true)
{
	global $db;
	$dbPrivs = null;
	$query = 'SELECT team_memberships.* FROM team_memberships WHERE user_id="'.$userId.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	if($result->num_rows > 0)
	{		
		$row = $result->fetch_assoc();
		$dbPrivs = $row['privs'];
	}
	
	$privsArr = array(
		'admin'=>array('admin'),
		'write'=>array('admin','write'),
		'read'=>array('admin','write','read')
	);
	
	if(!in_array($dbPrivs,$privsArr[$requiredPrivs]))
	{
		if($die)
		{
			die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Unauthorized Action')));
		}
		else
		{
			return false;
		}
	}
	else
	{
		return true;
	}
}

function getTeamMembershipByUser($userId)
{
	global $db;
	$data = null;
	$query = 'select * from team_memberships WHERE user_id="'.$userId.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	if($result->num_rows > 0)
	{		
		$row = $result->fetch_assoc();
		$data = $row;
	}
	return $data;
}

function getTeamInfoByUser($userId)
{
	global $db;
	$data = null;
	$query = 'select team_memberships.*, teams.*, team_accounts.* FROM team_memberships INNER JOIN teams ON team_memberships.team_number=teams.team_number INNER JOIN team_accounts ON team_memberships.team_number=team_accounts.team_number WHERE team_memberships.user_id="'.$userId.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	if($result->num_rows > 0)
	{		
		$row = $result->fetch_assoc();
		$data = $row;
	}
	return $data;
}

function getTeamMembership($team, $options = null)
{
	global $db;
	$data = null;
	$optionsArr = array();
	$optionsStr = '';
	if(isset($options) && is_array($options) && !empty($options))
	{
		if(isset($options['privs']) && !empty($options['privs']))
		{
			$temp = array();
			foreach($options['privs'] as $privs)
			{
				$temp[] = 'team_memberships.privs="'.$privs.'"';
			}
			if(!empty($temp))
			{
				$optionsArr[] = '('.implode(' OR ',$temp).')';		
			}
		}
		if(isset($options['status']) && !empty($options['status']))
		{
			$temp = array();
			foreach($options['status'] as $status)
			{
				$temp[] = 'team_memberships.status="'.$status.'"';
			}
			if(!empty($temp))
			{
				$optionsArr[] = '('.implode(' OR ',$temp).')';		
			}
		}
		if(isset($options['not_user']) && !empty($options['not_user']))
		{
			$temp = array();
			foreach($options['not_user'] as $user)
			{
				$temp[] = 'team_memberships.user_id != "'.$user.'"';
			}
			if(!empty($temp))
			{
				$optionsArr[] = '('.implode(' AND ',$temp).')';		
			}
		}
		if(!empty($optionsArr))
		{
			$optionsStr = ' AND '.implode(' AND ',$optionsArr);		
		}
	}
	$query = 'select team_memberships.*, users.* from team_memberships INNER JOIN users ON team_memberships.user_id=users.id WHERE team_memberships.team_number="'.$team.'"'.$optionsStr;
	$result = $db->query($query) or die(errorHandle(mysqli_error($db), $query));
	if($result->num_rows > 0)
	{		
		while($row = $result->fetch_assoc())
		{
			$user = $row;
			$user['full_name'] = $row['fname'].' '.$row['lname'];
			$data[] = $user;
		}
		
	}
	return $data;
}

function removeTeamMembership($userId, $team)
{
	global $db;
	$query = 'DELETE FROM team_memberships WHERE user_id="'.mysqli_real_escape_string($db, $userId).'" AND team_number="'.mysqli_real_escape_string($db, $team).'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	return true;
}

function getGcmKey()
{
	$key = '';
	$ini = parse_ini_file('./includes/config.ini');
	if(isset($ini['gcm_key']))
	{
		$key = $ini['gcm_key'];
	}
	return $key;
}
use Minishlink\WebPush\WebPush;
function sendPushNotificationByUser($user, $title='', $body='', $tag='')
{
	global $db;

	$ti = '';
	$tagInit = uniqid();
	if(isset($title) && $title!='')
	{
		$ti = ' | '.$title;
	}
	if(isset($tag) && $tag!='')
	{
		$tagInit = $tag;
	}
	$apiKeys = array(
		'GCM' => getIniProp('gcm_key'),
	);
	$webPush = new WebPush($apiKeys);
	$query = 'select * from notification_endpoints where user_id="'.$user.'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
	if($result->num_rows > 0)
	{
		$payload = array(
			'title'=>'FRC Scout'.$ti,
			'body'=>$body,
			'tag'=>$tagInit,
		);
		while($row = $result->fetch_assoc())
		{
			$notification = array(
				'endpoint' => $row['endpoint'],
				'userPublicKey' => $row['public_key'],
				'userAuthToken' => $row['auth_secret'],
				'payload' => json_encode($payload)
			);
			$webPush->sendNotification(
				$notification['endpoint'],
				$notification['payload'], // optional (defaults null)
				$notification['userPublicKey'], // optional (defaults null)
				$notification['userAuthToken'], // optional (defaults null)
				true
			);
		}
	}
}

function convertNotificationPreferencesToBool($data)
{
	$newData = array();
	foreach($data as $i=>$val)
	{
		if($i != 'user_id')
		{
			$newData[$i] = $val == 1 ? true:false;
		}
	}
	return $newData;
}

function checkNotificationPreference($user_id, $type)
{
	global $db;
	$data = array(
		'email' => true,
		'push' => true
	);
	$query = 'select notification_preferences.* from notification_preferences WHERE notification_preferences.user_id="'.mysqli_real_escape_string($db, $user_id).'"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	if($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$data['email'] = $row[$type.'-email'] == 1 ? true:false;
		$data['push'] = $row[$type.'-push'] == 1 ? true:false;
	}
	return $data;
}

function sendUserNotification($user_id, $type, $msgData)
{
	global $db;
	
	$preferences = checkNotificationPreference($user_id, $type);
	
	if($preferences['email'] == true)
	{
		
	}
	if($preferences['push'] == true)
	{
		$msg = $msgData['push'];
		$title = $msg['title'];
		$body = $msg['body'];
		$tag = '';
		sendPushNotificationByUser($user_id, $title, $body, $tag);
	}
}
?>