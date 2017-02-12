<?php
include('includes.php');

$authToken = checkToken(true, true);
$userId = $authToken['data']['id'];
$teamInfo = getTeamInfoByUser($userId);
$teamAccount = $teamInfo['team_number'];
//$teamAccount = 2;



if(!isset($_GET['event']) || $_GET['event'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Event Key.')));
}
$event = $_GET['event'];
$year = (int)substr($_GET['event'], 0, 4);

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
$teamInfoArr = array();

$allData = array();
$alliance_scores_avg = array();
$alliance_scores_max = array();


$allianceMem = array('red_1','red_2','red_3','blue_1','blue_2','blue_3');
$query = 'select * from match_info WHERE event_key= "'.$event.'" AND status="complete" ORDER BY match_num ASC';
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
			if(!in_array($team,$allTeams))
			{
				$allTeams[] = $team;
			}
			if(!array_key_exists($team,$teamInfoArr))
			{
				$teamInfoArr[$team] = getTeamInfoFromNumber($team);
			}
			if(in_array($team,$allTeams))
			{
				$alliance = explode('_',$alli)[0];
				$score = (int)$row[$alliance.'_score'];
				$alliance_scores_max[$team][$match] = $score;
				$alliance_scores_avg[$team][$match] = $score;
			}
		}
	}
}



//Individual Scores -- Year Specific

$yearSpecific = array();
if($year == 2016)
{
	$yearSpecific = get2016ReportData($event, $allTeams, $teamAccount);
}
if($year == 2017)
{
	$yearSpecific = get2017ReportTableData($event, $allTeams, $teamAccount);
}

$allDataOrig = $yearSpecific['allData'];
$allData = $yearSpecific['allData'];
foreach($allDataOrig as $i=>$teamData)
{
	//Average/Total Points
	$team = $teamData['team_number'];
	$allData[$i]['teamInfo'] = $teamInfoArr[$team];
	$allData[$i]['allianceScoresAvg'] = 0;
	$allData[$i]['allianceScoresMax'] = 0;
	if(isset($alliance_scores_max[$team]) && array_key_exists($team,$alliance_scores_max))
	{
		$scores = $alliance_scores_max[$team];
		$allData[$i]['allianceScoresAvg'] = array_sum($scores) / count($scores);
		$allData[$i]['allianceScoresMax'] = max($scores);
	}
	$allData[$i]['matchesScouted'] = 0;
	$query = 'SELECT DISTINCT `match_key` FROM `match_data` WHERE `team_account`="'.$teamAccount.'" AND team_number="'.$team.'" AND `match_key` LIKE "'.$event.'%"';
	$result = $db->query($query) or die(errorHandle(mysqli_error($db),$query));
	if($result->num_rows > 0)
	{
		$allData[$i]['matchesScouted'] = $result->num_rows;
	}
}

$data = array(
	'allData' => $allData,
	'yearSpecific' => $yearSpecific['allData'],
	'graphData' => $yearSpecific['graphData'],
	'allTeams' => $allTeams
);

die(json_encode(array('type'=>'success', 'msg'=>'', 'data'=>$data), JSON_NUMERIC_CHECK));
?>