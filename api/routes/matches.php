<?php
use \Firebase\JWT\JWT;

$app->group('/matches', function () use ($app) {
	$app->group('/matchData', function () use ($app) {
		$app->group('/{match_key}', function () use ($app) {
			$app->get('', function ($request, $response, $args) {
				global $db;
				$authToken = $request->getAttribute("jwt");
				$match_key = $request->getAttribute("match_key");
				
				$matchKeyArr = getEventMatchByMatchKey($match_key);
				$event_key = $matchKeyArr['event_key'];
				$match_num = $matchKeyArr['match_num'];
				
				$userId = $authToken->data->id;
				$teamInfo = getTeamInfoByUser($userId);
				$team = $teamInfo['team_number'];
				$data = array();
				$data = getMatchByMatchKey($match_key);
				$data['last_match'] = isLastMatch($match_num, $event_key);
				$data['match_data'] = getMatchData($match_key, $team);
				if(checkMatchKeyFormat($match_key)) {
					$data['msg'] = 'Incorect match key format.  Default values used.';
					$data['status'] = false;
					$data['type'] = 'warning';
				}
				return $response->withJson($data, null, JSON_NUMERIC_CHECK);
			});
			$app->get('/official', function ($request, $response, $args) {
				global $db;
				$authToken = $request->getAttribute("jwt");
				$match_key = $request->getAttribute("match_key");	
				$data = array();
				$data = tbaApiCallMatch($match_key);
				if(checkMatchKeyFormat($match_key)) {
					$data['msg'] = 'Incorect match key format.  Default values used.';
					$data['status'] = false;
					$data['type'] = 'warning';
				}
				return $response->withJson($data, null, JSON_NUMERIC_CHECK);
			});
			$app->get('/stats', function ($request, $response, $args) {
				global $db;
				$authToken = $request->getAttribute("jwt");
				$match_key = $request->getAttribute("match_key");	
				
				$userId = $authToken->data->id;
				$teamInfo = getTeamInfoByUser($userId);
				$team = $teamInfo['team_number'];
				$data = array();
				$data = getMatchDataStats($match_key, $team);
				if(checkMatchKeyFormat($match_key)) {
					$data['msg'] = 'Incorect match key format.  Default values used.';
					$data['status'] = false;
					$data['type'] = 'warning';
				}
				return $response->withJson($data);
			});
		});
	});
	$app->get('/pointsByYear[/{year:[0-9]{4}}]', function ($request, $response, $args) {
		global $db;
		//$authToken = $request->getAttribute("jwt");
		$year = date('Y');
		if($request->getAttribute("year") != null && $request->getAttribute("year")!='' && $request->getAttribute("year")!='undefined') {
			$year = $request->getAttribute("year");
		}
		$pointValues = getPointValuesByYear($year);
		return $response->withJson($pointValues, null, JSON_NUMERIC_CHECK);
	});
	$app->post('/insertMatchData', function ($request, $response, $args) {
		global $db;
		$formData = $request->getParsedBody();
		$authToken = $request->getAttribute("jwt");
		if(!isset($formData['event']) || $formData['event'] == '') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Event Key.'));
		}
		if(!isset($formData['match_number']) || $formData['match_number'] == '' || $formData['match_number'] < 1) {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Match Number.'));
		}

		$match_key = $formData['event'].'_qm'.$formData['match_number'];
		$userId = $authToken->data->id;
		$teamInfo = getTeamInfoByUser($userId);
		$team = $teamInfo['team_number'];
		$time = microtime(true);
		$start = getMatchData_start($match_key, $team);
		if($start === false) {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Match has not started yet.'));
		}
		elseif($time - $start['match_start'] > 150 && $time - $start['match_start'] <155) {
			$timeInsrtstr = '"'.round($start['match_start']+150,4).'"';
		}
		elseif($time - $start['match_start'] >= 155) {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Match is over.'));
		}
		else {
			$timeInsrtstr = '"'.round($time,4).'"';
		}

		if(!isset($formData['team_number']) || $formData['team_number'] == '') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Team Number.'));
		}
		if(!isset($formData['data']) || !isset($formData['data']['action']) || $formData['data']['action']=='') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Data.'));
		}
		if(oneTimeActionComplete($match_key, $formData['team_number'], $team, $formData['data']['action'])) {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Action can only be completed once per team.'));
		}
		$attr_1 = isset($formData['data']['attr_1']) ? $formData['data']['attr_1']:'';
		$attr_2 = isset($formData['data']['attr_2']) ? $formData['data']['attr_2']:'';
		$comment = isset($formData['data']['comment']) ? $formData['data']['comment']:'';

		verifyTeamPrivs($authToken->data->id, 'write', $die = true);
		$id = uniqid();
		$query = 'INSERT INTO match_data (`id`, `team_account`, `user_id`, `team_number`, `match_key`, `action`, `attr_1`, `attr_2`, `comment`, `timestamp`) VALUES 
										("'.$id.'",
										 "'.$team.'",
										 "'.$userId.'",
										 "'.mysqli_real_escape_string($db, $formData['team_number']).'",
										 "'.mysqli_real_escape_string($db, $match_key).'",
										 "'.mysqli_real_escape_string($db, $formData['data']['action']).'",
										 "'.mysqli_real_escape_string($db, $attr_1).'",
										 "'.mysqli_real_escape_string($db, $attr_2).'",
										 "'.mysqli_real_escape_string($db, $comment).'",
										 '.$timeInsrtstr.')';

		$result = $db->query($query);
		if(!$result) {
			return $response->withJson(errorHandle(mysqli_error($db), $query));
		}
		$dataToWS = array(
			'type' => 'match_data',
			'team' => $team,
			'team_number' => $formData['team_number'],
			'match_key' => $match_key,
			'action' => $formData['data']['action'],
			'attr_1' => $attr_1,
			'attr_2' => $attr_2,
			'comment' => $comment
		);
		newMessageToWS($dataToWS);
		$attr1_msg = $attr_1!='' ? $attr_1:'';
		$attr2_msg = $attr_2!='' ? $attr_2:'';
		$msg = ucwords(implode(' ',explode('_',$formData['data']['action']))).' '.$attr1_msg.' '.$attr2_msg.' recorded for Team '.$formData['team_number'];
		return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>$msg));

	});
	$app->post('/startMatch', function ($request, $response, $args) {
		global $db;
		$formData = $request->getParsedBody();
		$authToken = $request->getAttribute("jwt");

		if(!isset($formData['event']) || $formData['event'] == '') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Event Key.'));
		}
		if(!isset($formData['match_number']) || $formData['match_number'] == '' || $formData['match_number'] < 1) {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Match Number.'));
		}

		$match_key = $formData['event'].'_qm'.$formData['match_number'];
		$userId = $authToken->data->id;
		$teamInfo = getTeamInfoByUser($userId);
		$team = $teamInfo['team_number'];
		$startArr = getMatchData_start($match_key, $team);
		if($startArr !== false) {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Match already started.'));
		}
		verifyTeamPrivs($authToken->data->id, 'write', $die = true);
		$id = uniqid();
		$time = microtime(true);
		$query = 'INSERT INTO match_data (`id`, `team_account`, `user_id`, `match_key`, `action`, `timestamp`) VALUES 
										("'.$id.'",
										 "'.$team.'",
										 "'.$userId.'",
										 "'.mysqli_real_escape_string($db, $match_key).'",
										 "match_start",
										 "'.round($time,4).'")';
		$result = $db->query($query);
		if(!$result) {
			return $response->withJson(errorHandle(mysqli_error($db), $query));
		}
		$dataToWS = array(
			'type' => 'match_start',
			'team' => $team,
			'match_key' => $match_key
		);
		newMessageToWS($dataToWS);

		$msg = 'Match '.$formData['match_number'].' started.';
		return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>$msg));
	});
	$app->post('/updateMatchScore', function ($request, $response, $args) {
		global $db;
		$formData = $request->getParsedBody();
		$authToken = $request->getAttribute("jwt");
		if(!isset($formData['match_key']) || $formData['match_key'] == '') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Match Key.'));
		}
		$match_info = getMatchByMatchKey($formData['match_key']);
		if($match_info['completed'] == true) {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Scores cannot be updated.  Match is complete.'));
		}
		$queryArr = array();
		if(isset($formData['red_score']) && $formData['red_score'] != '' && $formData['red_score'] != 'null' && $formData['red_score'] != '-1') {
			$queryArr[] = 'red_score="'.mysqli_real_escape_string($db, $formData['red_score']).'"';
		}
		if(isset($formData['blue_score']) && $formData['blue_score'] != '' && $formData['blue_score'] != 'null' && $formData['blue_score'] != '-1') {
			$queryArr[] = 'blue_score="'.mysqli_real_escape_string($db, $formData['blue_score']).'"';
		}
		$queryStr = '';
		if(!empty($queryArr)) {
			$queryStr = implode(', ',$queryArr);
			$query = 'UPDATE match_info SET '.$queryStr.' WHERE match_key="'.mysqli_real_escape_string($db, $formData['match_key']).'"';
			$result = $db->query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			$dataToWS = array(
				'type' => 'match_info',
				'match_key' => $formData['match_key']
			);
			newMessageToWS($dataToWS);
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Match Info Updated.'));
		}
		return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'No data.'));
	});
});

?>