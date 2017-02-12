<?php
use \Firebase\JWT\JWT;

$app->group('/events', function () use ($app) {
	$app->group('/{year:[0-9]{4}}', function () use ($app) {
		$app->get('', function ($request, $response, $args) {
			global $db;
			$authToken = $request->getAttribute("jwt");
			$year = $request->getAttribute("year");	
			$data = getEventsByYear($year);
			return $response->withJson($data);
		});
		$app->group('/{event:[a-z]+}', function () use ($app) {
			$app->get('', function ($request, $response, $args) {
				global $db;
				$authToken = $request->getAttribute("jwt");
				$year = $request->getAttribute("year");	
				$event = $request->getAttribute("event");
				$event_key = $year.$event;
				$data = getEventInfo($event_key);
				return $response->withJson($data);
			});
			$app->get('/matches', function ($request, $response, $args) {
				global $db;
				$authToken = $request->getAttribute("jwt");
				$year = $request->getAttribute("year");	
				$event = $request->getAttribute("event");
				$event_key = $year.$event;
				$complete = checkEventComplete($event_key);
				$matches = getMatchesByEventKey($event_key);
				$data = array();
				if($complete == true) {
					$data = array('complete'=>true,'data'=>$matches);
				}
				else {
					$data = array('complete'=>false, 'data'=>$matches);
				}		
				return $response->withJson($data);
			});
		});
	});
	$app->group('/{event_key:[0-9]{4}[a-z]+}', 	function () use ($app) {
		$app->get('', function ($request, $response, $args) {
				global $db;
				$authToken = $request->getAttribute("jwt");
				$event_key = $request->getAttribute("event_key");
				$data = getEventInfo($event_key);
				return $response->withJson($data);
			});
		$app->get('/matches', function ($request, $response, $args) {
			global $db;
			$authToken = $request->getAttribute("jwt");
			$event_key = $request->getAttribute("event_key");	
			$complete = checkEventComplete($event_key);
			$matches = getMatchesByEventKey($event_key);
			$data = array();
			if($complete == true) {
				$data = array('complete'=>true,'data'=>$matches);
			}
			else {
				$data = array('complete'=>false, 'data'=>$matches);
			}		
			return $response->withJson($data);
		});
		$app->get('/upcomingMatch', function ($request, $response, $args) {
			global $db;
			$authToken = $request->getAttribute("jwt");
			$event_key = $request->getAttribute("event_key");	
			
			$data = array('match_num'=>1);
			$query = 'select * from match_info WHERE event_key = "'.$event_key.'" AND status="upcoming" ORDER BY match_num ASC LIMIT 1';
			$result = $db->query($query) or die(errorHandle(mysqli_error($db), $query));
			if($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$data = $row;
			}
			return $response->withJson($data, null, JSON_NUMERIC_CHECK);
		});
	});
	$app->get('/current', function ($request, $response, $args) {
		global $db;
		$authToken = $request->getAttribute("jwt");
		$events = getCurrentEvents();
		$data = array(
			'events' => $events,
			'count' => count($events)
		);
		return $response->withJson($data);
	});
	$app->get('/dataEntry[/{year:[0-9]{4}}]', function ($request, $response, $args) {
		global $db;
		$authToken = $request->getAttribute("jwt");
		$year = $request->getAttribute("year");	
		
		$userId = $authToken->data->id;
		$teamInfo = getTeamInfoByUser($userId);
		$team = $teamInfo['team_number'];
		if(!isset($year) || $year == '')
		{
			$year = date('Y');
		}

		$data = array();
		$current_event = '';
		$query = 'select * from team_accounts WHERE team_number = "'.$team.'"';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			if($row['current_event'] != null && strpos($row['current_event'], $year) !== false)
			{
				$current_event = $row['current_event'];
			}
		}
		$currentWeek = date('W',time());
		$query = 'select * from events WHERE year = "'.$year.'" ORDER BY name';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$active = array();
			$current = array();
			$other = array();
			$all = array();
			while($row = $result->fetch_assoc())
			{
				$event_week = date('W',strtotime($row['start_date']));
				$temp = $row;
				
				if($row['event_key'] == $current_event)
				{
					$temp['status'] = 'Team Active';
					$data['team_active'] = $temp;
					$active = $temp;
				}
				if($currentWeek == $event_week)
				{
					$temp['status'] = 'Current Week';
					$data['current_week'][] = $temp;
					$current[] = $temp;
				}		
				else
				{
					$temp['status'] = 'Other';
					$data['other'][] = $temp;
					$other[] = $temp;
				}
				//$data['all'][] =  $temp;
			}
			$all[] = $active;
			$data['all'] = array_merge($all, $current, $other);
		}
		return $response->withJson($data);
	});
});

?>