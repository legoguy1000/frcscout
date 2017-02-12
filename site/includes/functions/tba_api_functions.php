<?php

function tbaApiCallEventMatches($event_key)
{
	$baApiCall = file_get_contents('https://www.thebluealliance.com/api/v2/event/'.$event_key.'/matches?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
	return $baApiCall;
}

function tbaApiCallEvent($event_key)
{
	$baApiCall = file_get_contents('https://www.thebluealliance.com/api/v2/event/'.$event_key.'?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
	return $baApiCall;
}

function tbaApiCallEventRankings($event_key)
{
	if(getEventByEventKey($event_key) == 'test')
	{
		return array();
	}
	$baApiCall = file_get_contents('https://www.thebluealliance.com/api/v2/event/'.$event_key.'/rankings?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
	if($baApiCall !== FALSE)
	{
		return json_decode($baApiCall,true);
	}
	else
	{
		return false;
	}
}

function tbaApiCallMatch($match_key)
{
	$return = false;
	if(checkMatchKeyFormat($match_key)) {
		$matchArr = getEventMatchByMatchKey($match_key);
		if( $matchArr['event'] != 'test')
		{
			$baApiCall = file_get_contents('https://www.thebluealliance.com/api/v2/match/'.$match_key.'?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
			if($baApiCall !== FALSE)
			{
				$return = json_decode($baApiCall,true);
			}
		}
	}
	return $return;
}

function tbaApiCallEventsYear($year=null)
{
	if($year == null)
	{
		$year = date('Y');
	}
	$baApiCall = file_get_contents('https://www.thebluealliance.com/api/v2/events/'.$year.'?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
	return $baApiCall;
}

function tbaApiCallTeamsPage($page=null)
{
	if($page == null)
	{
		$page = 0;
	}
	$baApiCall = file_get_contents('http://www.thebluealliance.com/api/v2/teams/'.$page.'?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
	return $baApiCall;
}

function tbaApiCallTeamInfo($team)
{
	$baApiCall = file_get_contents('http://www.thebluealliance.com/api/v2/team/frc'.$team.'?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
	if($baApiCall !== FALSE)
	{
		return json_decode($baApiCall,true);
	}
	else
	{
		return false;
	}
}

function tbaApiCallTeamEvents($team, $year)
{
	if($year == null)
	{
		$year = date('Y');
	}
	$baApiCall = file_get_contents('http://www.thebluealliance.com/api/v2/team/frc'.$team.'/'.$year.'/events?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
	if($baApiCall !== FALSE)
	{
		return json_decode($baApiCall,true);
	}
	else
	{
		return false;
	}
}

function tbaApiCallTeamEventMatches($team, $event)
{
	$baApiCall = file_get_contents('http://www.thebluealliance.com/api/v2/team/frc'.$team.'/event/'.$event.'/matches?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
	if($baApiCall !== FALSE)
	{
		return json_decode($baApiCall,true);
	}
	else
	{
		return false;
	}
}

function tbaApiCallTeamYearsParticipated($team)
{
	$baApiCall = file_get_contents('http://www.thebluealliance.com/api/v2/team/frc'.$team.'/years_participated?X-TBA-App-Id=Resnick-Tech:FRCSCOUT:v01');
	if($baApiCall !== FALSE)
	{
		return json_decode($baApiCall,true);
	}
	else
	{
		return false;
	}
}
?>