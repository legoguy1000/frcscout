<?php
include('includes.php');

//$authToken = checkToken();
$teamArr = array();
$data = array();
$team = $_GET['team'];
$baApiCall = file_get_contents('https://www.thebluealliance.com/api/v2/team/frc'.$team.'?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
if($baApiCall !== FALSE)
{
	$teamArr = json_decode($baApiCall, true);
	if(array_key_exists('404',$teamArr))
	{
		$data = array('status'=>false, 'msg'=>$teamArr['404']);
	}
	if(array_key_exists('Errors',$teamArr))
	{
		$data = array('status'=>false, 'msg'=>$teamArr['Errors'][0]['team_id']);
	}
	else
	{
		$data = array('status'=>true, 'msg'=>'', 'data'=>$teamArr);
	}
}
else
{
	$data = array('status'=>false, 'msg'=>'Error fetching teams from The Blue Alliance');
}

die(json_encode($data));

?>