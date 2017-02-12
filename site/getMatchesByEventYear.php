<?php
include('includes.php');

//$authToken = checkToken();
$data = array();
$event = $_GET['event'];
$year = $_GET['year'];
$event_key = $year.$event;

$complete = checkEventComplete($event_key);
$matches = getMatchesByEventKey($event_key);
if($complete == true)
{
	$data = array('complete'=>true,'data'=>$matches);
}
else
{
	$data = array('complete'=>false, 'data'=>$matches);
}
die(json_encode($data));

?>