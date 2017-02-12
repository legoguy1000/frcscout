<?php
include('includes.php');

$authToken = checkToken(true, true);
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$teamAccount = $teamInfo['team_number'];


if(!isset($_GET['year']) || $_GET['year'] == '' || !is_numeric($_GET['year']))
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid year.')));
}
if(!isset($_GET['team']) || $_GET['team'] == '' || !is_numeric($_GET['team']))
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Not a valid team number.')));
}

$data = array();
$year = $_GET['year'];
$team = $_GET['team'];
$data = getRobotOptionsByYear($year);
$query = 'select * from robot_data where team_account = "'.$teamAccount.'" AND year="'.$year.'" AND team_number="'.$team.'"';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	while($row = $result->fetch_assoc())
	{
		$attr = $row['attribute'];
		$value = $row['value'];
		$data[$attr] = $value;
	}
}
die(json_encode(array('type'=>'success', 'msg'=>'', 'data'=>$data), JSON_NUMERIC_CHECK));
?>