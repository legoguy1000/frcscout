<?php
include('includes.php');

$authToken = checkToken(true, true);
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$teamAccount = $teamInfo['team_number'];
//$teamAccount = 2;

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['event']) || $formData['event'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Event Key.', 'data'=>$formData)));
}
/* if(!isset($formData['teams']) || empty($formData['teams']))
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Must select at least 1 team.')));
} */


$event = $formData['event'];
$year = (int)substr($formData['event'], 0, 4);

$allReportData = array();
$currentData = array();

$xLabels = array();
$allianceScores = array();
$IndividualScores = array();
$legend = array();
$matchScores = array();

// Alliance Scores
$teamsQuery = array();
$teamsQueryString = '';
$allTeams = array();
$allTeamsQuery = array();
$populateTeams = true;
if(isset($formData["teams"]) && $formData["teams"]!='' && !empty($formData["teams"]))
{
	$populateTeams = false;
	foreach($formData["teams"] as $team)
	{
		$teamNum = $team['team_number'];
		$allTeams[] = $teamNum;
		$allTeamsQuery[] = mysqli_real_escape_string($db,$teamNum);
		$legend[] = $teamNum;
	}
	if(!empty($allTeamsQuery))
	{
		$teamArrStr = implode(', ',$allTeamsQuery);
		$teamsQueryString = 'AND (red_1 IN ('.$teamArrStr.') OR red_2 IN ('.$teamArrStr.') OR red_3 IN ('.$teamArrStr.') OR blue_1 IN ('.$teamArrStr.') OR blue_2 IN ('.$teamArrStr.') OR blue_3 IN ('.$teamArrStr.'))';
	}
}

$allianceScores = array();
$allAllianceScores = array();
$allianceMem = array('red_1','red_2','red_3','blue_1','blue_2','blue_3');
$query = 'select * from match_info WHERE event_key= "'.$event.'" '.$teamsQueryString.' AND status="complete" ORDER BY match_num ASC';
$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
if($result->num_rows > 0)
{
	while($row=$result->fetch_assoc())
	{
		$temp = array();
		$match = $row['match_key'];
			
		foreach($allianceMem as $alli)
		{
			$team = $row[$alli];
			if($populateTeams)
			{
				if(!in_array($team,$allTeams))
				{
					$allTeams[] = $team;
				}
				if(!in_array($team,$legend))
				{
					$legend[] = $team;
				}
			}
			if(in_array($team,$allTeams))
			{
				$alliance = explode('_',$alli)[0];
				$score = (int)$row[$alliance.'_score'];
				$allAllianceScores[$team][$match] = $score;
			}
		}
		
	}
}
$numMatches = array();
ksort($allAllianceScores);
foreach($allAllianceScores as $team=>$scores)
{
	/* $temp = array();
	$i=1;
	foreach($scores as $score)
	{
		$temp[] = array('x'=>$i, 'y'=>$score);
		$i++;
	}
	$newData[] = array(
		'key' => $team,
		'values' => $temp,
	); */
	$allianceScores[] = array_values($scores);
	$numMatches[] = count($scores);
}
$maxNumMatches = max($numMatches);

for($i=1;$i<=$maxNumMatches;$i++)
{
	$xLabels[] = $i;
}
sort($legend, SORT_NUMERIC );
$allianceScoresArr = array(
	'legend' => $legend,
	'allScores' => $allAllianceScores,
	'scores' => $allianceScores,
	'xLabels' => $xLabels,
	'query' => $query
	//'newData' => $newData
);

//Individual Scores -- Year Specific

$yearSpecific = array();
if($year == 2016)
{
	$yearSpecific = get2016ReportData($event, $allTeams, $teamAccount);
}
if($year == 2017)
{
	$yearSpecific = get2017ReportData($event, $allTeams, $teamAccount);
}

$data = array(
	'allianceScores' => $allianceScoresArr,
	'yearSpecific' => $yearSpecific,
	'populateTeams' => $populateTeams,
	'allTeams' => $allTeams
);

die(json_encode(array('type'=>'success', 'msg'=>'', 'data'=>$data)));
?>