<?php
include('includes.php');

$authToken = checkToken(true, true);
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$teamAccount = $teamInfo['team_number'];

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['year']) || $formData['year'] == '' || !is_numeric($formData['year']))
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid year.')));
}
if(!isset($formData['team']) || $formData['team'] == '' || !is_numeric($formData['team']))
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Not a valid team number.')));
}


$year = $formData['year'];
$team = $formData['team'];

$curData = array();
$query = 'select * from robot_data where team_account = "'.$teamAccount.'" AND year="'.$year.'" AND team_number="'.$team.'"';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	while($row = $result->fetch_assoc())
	{
		$attr = $row['attribute'];
		$value = $row['value'];
		$curData[$attr] = $value;
	}
}

$query = null;
$data = $formData['data'];
foreach($data as $item=>$val)
{
	if(array_key_exists($item,$curData))
	{
		$query = 'UPDATE robot_data SET value="'.$val.'" WHERE attribute="'.$item.'" AND team_account = "'.$teamAccount.'" AND year="'.$year.'" AND team_number="'.$team.'"';
	//	die($query);
	}
	else
	{
		$id = uniqid();
		$query = 'INSERT INTO `robot_data` (`id`,`team_account`,`user_id`,`team_number`,`year`,`attribute`,`value`,`comment`) 
									VALUES ("'.$id.'","'.$teamAccount.'","'.$userId.'","'.$team.'","'.$year.'","'.$item.'","'.$val.'","")';
	}
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
}

die(json_encode(array('status'=>true,'type'=>'success', 'msg'=>'Robot data submitted', 'data'=>$data)));
?>