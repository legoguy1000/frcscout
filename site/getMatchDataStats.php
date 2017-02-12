<?php
include('includes.php');

$authToken = checkToken(true, true);
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$teamAccount = $teamInfo['team_number'];


$event_key = $_GET['event'];
$match = $_GET['match'];
if(!isset($_GET['event']) || !isset($_GET['match']) || $_GET['match']==''  || $_GET['match']<1 || $_GET['match']=='')
{
	
}
$match_key = $event_key.'_qm'.$match;
$data = getMatchDataStats($match_key, $teamAccount);
die(json_encode($data, JSON_NUMERIC_CHECK ));

?>