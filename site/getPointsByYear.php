<?php
include('includes.php');

$authToken = checkToken(true, true);
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];

$data = array();
$year = $_GET['year'];
if(!isset($_GET['year']) || $_GET['year']=='' || $_GET['year']=='undefined')
{
	$year = date('Y');
}
$pointValues = getPointValuesByYear($year);

die(json_encode($pointValues, JSON_NUMERIC_CHECK ));

?>