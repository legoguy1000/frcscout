<?php
include('includes.php');

$authToken = checkToken(true, true);
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$team = $teamInfo['team_number'];

$data = array();
$event_key = $_GET['event'];
$match = $_GET['match'];
if(!isset($_GET['event']) || !isset($_GET['match']) || $_GET['match']==''  || $_GET['match']<1 || $_GET['match']=='')
{
	/* $data[] = ;
	$data['ready_to_start'] = false; */
}
$match_key = $event_key.'_qm'.$match;
$data = tbaApiCallMatch($match_key);
die(json_encode($data, JSON_NUMERIC_CHECK ));

?>