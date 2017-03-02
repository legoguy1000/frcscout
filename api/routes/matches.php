<?php
use \Firebase\JWT\JWT;

$app->group('/matches', function () use ($app) {
	$app->group('/matchData', function () use ($app) {
		$app->group('/{match_key}', function () use ($app) {
			$app->get('', function ($request, $response, $args) {
				$db = db_connect();
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
				$data['server_time'] = time();
				if(!checkMatchKeyFormat($match_key)) {
					$data['msg'] = 'Incorect match key format.  Default values used.';
					$data['status'] = false;
					$data['type'] = 'warning';
				}
				return $response->withJson($data, null, JSON_NUMERIC_CHECK);
			});
			$app->get('/official', function ($request, $response, $args) {
				$db = db_connect();
				$authToken = $request->getAttribute("jwt");
				$match_key = $request->getAttribute("match_key");
				$data = array();
				$data = tbaApiCallMatch($match_key);
				if(!checkMatchKeyFormat($match_key)) {
					$data['msg'] = 'Incorect match key format.  Default values used.';
					$data['status'] = false;
					$data['type'] = 'warning';
				}
				return $response->withJson($data, null, JSON_NUMERIC_CHECK);
			});
			$app->get('/stats', function ($request, $response, $args) {
				$db = db_connect();
				$authToken = $request->getAttribute("jwt");
				$match_key = $request->getAttribute("match_key");

				$userId = $authToken->data->id;
				$teamInfo = getTeamInfoByUser($userId);
				$team = $teamInfo['team_number'];
				$data = array();
				$data = getMatchDataStats($match_key, $team);
				if(!checkMatchKeyFormat($match_key)) {
					$data['msg'] = 'Incorect match key format.  Default values used.';
					$data['status'] = false;
					$data['type'] = 'warning';
				}
				return $response->withJson($data);
			});
		});
	});
	$app->get('/pointsByYear[/{year:[0-9]{4}}]', function ($request, $response, $args) {
		$db = db_connect();
		//$authToken = $request->getAttribute("jwt");
		$year = date('Y');
		if($request->getAttribute("year") != null && $request->getAttribute("year")!='' && $request->getAttribute("year")!='undefined') {
			$year = $request->getAttribute("year");
		}
		$pointValues = getPointValuesByYear($year);
		return $response->withJson($pointValues, null, JSON_NUMERIC_CHECK);
	});
	$app->post('/insertMatchData', function ($request, $response, $args) {
		$db = db_connect();
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
			$timeInsrtstr = db_quote(round($start['match_start']+150,4));
		}
		elseif($time - $start['match_start'] >= 155) {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Match is over.'));
		}
		else {
			$timeInsrtstr = db_quote(round($time,4));
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
										('.db_quote($id).',
										('.db_quote($team).',
										('.db_quote($userId).',
										 '.db_quote($formData['team_number']).',
										 '.db_quote($match_key).',
										 '.db_quote($formData['data']['action']).',
										 '.db_quote($attr_1).',
										 '.db_quote($attr_2).',
										 '.db_quote($comment).',
										 '.db_quote($timeInsrtstr).')';

		$result = db_query($query);
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
			'comment' => $comment,
			"timestamp" => $timeInsrtstr,
			"id" => $id
		);
		newMessageToWS($dataToWS);
		$attr1_msg = $attr_1!='' ? $attr_1:'';
		$attr2_msg = $attr_2!='' ? $attr_2:'';
		$msg = ucwords(implode(' ',explode('_',$formData['data']['action']))).' '.$attr1_msg.' '.$attr2_msg.' recorded for Team '.$formData['team_number'];
		return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>$msg));

	});
	$app->post('/undoMatchData', function ($request, $response, $args) {
		$db = db_connect();
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

		if(!isset($formData['team_number']) || $formData['team_number'] == '') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Team Number.'));
		}
		if(!isset($formData['data']) || !isset($formData['data']['action']) || $formData['data']['action']=='') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Data.'));
		}
		if(!isset($formData['data']) || !isset($formData['data']['id']) || $formData['data']['id']=='') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Data.'));
		}


		verifyTeamPrivs($authToken->data->id, 'write', $die = true);
		$id = uniqid();
		$query = 'DELETE FROM match_data WHERE team_account="'.$team.'" AND id='.db_quote($formData['data']['id']).'';

		$result = db_query($query);
		if(!$result) {
			return $response->withJson(errorHandle(mysqli_error($db), $query));
		}
		$dataToWS = array(
			'type' => 'match_data_undo',
			'team' => $team,
			'team_number' => $formData['team_number'],
			'match_key' => $match_key,
			'action' => $formData['data']['action'],
			'id' => $formData['data']['id'],
			"id" => $id
		);
		newMessageToWS($dataToWS);
		$msg = ucwords(implode(' ',explode('_',$formData['data']['action']))).' removed for Team '.$formData['team_number'];
		return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>$msg));

	});
	$app->post('/startMatch', function ($request, $response, $args) {
		$db = db_connect();
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
										('.db_quote($id).',
										 '.db_quote($team).',
										 '.db_quote($userId).',
										 '.db_quote($match_key).',
										 '.db_quote('match_start').',
										 '.db_quote(round($time,4)).')';
		$result = db_query($query);
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
		$db = db_connect();
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
			$queryArr[] = 'red_score="'.db_quote($formData['red_score']).'';
		}
		if(isset($formData['blue_score']) && $formData['blue_score'] != '' && $formData['blue_score'] != 'null' && $formData['blue_score'] != '-1') {
			$queryArr[] = 'blue_score="'.db_quote($formData['blue_score']).'';
		}
		$queryStr = '';
		if(!empty($queryArr)) {
			$queryStr = implode(', ',$queryArr);
			$query = 'UPDATE match_info SET '.$queryStr.' WHERE match_key='.db_quote($formData['match_key']).'';
			$result = db_query($query);
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
