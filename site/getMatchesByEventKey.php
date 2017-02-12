<?php
include('includes.php');

//$authToken = checkToken();
$data = array();
$event_key = $_GET['event_key'];
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