<?php
include('includes.php');

//$authToken = checkToken();
$data = array();
if(!isset($_GET['team']) || $_GET['team'] == '' || $_GET['team'] == 'undefined')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Team Number cannot be blank.')));
}
if(!isset($_GET['year']) || $_GET['year'] == '' || $_GET['year'] == 'undefined')
{
	$year = date('Y');
}
else
{
	$year = $_GET['year'];
}
$team = $_GET['team'];


$comLevelArr = array('f'=>'finals', 'sf'=>'semifinals', 'qf'=>'quarterfinals', 'qm'=>'qualifications');
$team_info = tbaApiCallTeamInfo($team);
if($team_info !== false)
{
	$data['team_info'] = $team_info;
	$events = tbaApiCallTeamEvents($team, $year);
	if($events !== false)
	{
		$i = 0;
		foreach($events as $event)
		{
			$eventKey = $event['key'];
			$data['events'][$i] = $event;
			$matches = tbaApiCallTeamEventMatches($team, $eventKey);
			if($matches !== false)
			{
				$matchArr = array('qualifications'=>array(), 'quarterfinals'=>array(), 'semifinals'=>array(), 'finals'=>array());
				foreach($matches as $match)
				{
					$level = $comLevelArr[$match['comp_level']];
					$matchArr[$level][] = $match;
				}
				$data['events'][$i]['matches'] = $matchArr;
			}
			$i++;
		}
	}
}




die(json_encode($data));

?>