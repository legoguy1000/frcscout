<?php
use \Firebase\JWT\JWT;

$app->group('/teams', function () use ($app) {
	$app->post('/updateMemberPrivs', function ($request, $response, $args) {
		$db = db_connect();
		$formData = $request->getParsedBody();
		$authToken = $request->getAttribute("jwt");
		if(!isset($formData['id']) || $formData['id'] == '') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid user ID.'));
		}
		if(!isset($formData['privs']) || $formData['privs'] == '') {
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Privs Level.'));
		}
		verifyTeamPrivs($authToken->data->id, 'admin', $die = true);
		$query = 'UPDATE team_memberships SET privs='.db_quote($formData['privs']).' WHERE user_id='.db_quote($formData['id']).'';
		$result = db_query($query);
		if(!$result) {
			return $response->withJson(errorHandle(mysqli_error($db), $query));
		}
		return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Team Privs updated for '.$formData['userInfo']['full_name']));
	});
	$app->group('/membership', function () use ($app) {
		$app->post('/request', function ($request, $response, $args) {
			$db = db_connect();
			$formData = $request->getParsedBody();
			$authToken = $request->getAttribute("jwt");
			if(!isset($formData['user_id']) || $formData['user_id'] == '')
			{
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid user ID.  Reload the page and try again.'));
			}
			if(!isset($formData['team_number']) || $formData['team_number'] == '')
			{
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid Team Number.  Reload the page and try again.'));
			}
			verifyUser($formData['user_id'], $authToken->data->id, $die = true);
			$id = uniqid();
			$query = 'insert into team_memberships (id, team_number, user_id, status) VALUES
													('.db_quote($id).',
													'.db_quote($formData['team_number']).',
													'.db_quote($formData['user_id']).',
													"pending")';
			$result = db_query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			$name = $authToken->data->full_name;
			$email = $authToken->data->email;
			$admins = getTeamMembership($formData['team_number'], array('privs'=>array('admin')));
			$msg_data = array(
				'users' => $admins,
				'push' => array(
					'subject' => 'Team Membership Request',
					'message' => $name.' ('.$email.') has requested to join Team '.$formData['team_number'].'.'
				)
			);
			newMessageToQueue('user_notification', $msg_data);
			$teamInfo = getTeamInfoByUser($formData['user_id']);
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Your Request to join Team '.$formData['team_number'].' has been submitted.', 'teamInfo'=>$teamInfo));
		});
		$app->post('/approve', function ($request, $response, $args) {
			$db = db_connect();
			$formData = $request->getParsedBody();
			$authToken = $request->getAttribute("jwt");
			if(!isset($formData['id']) || $formData['id'] == '')
			{
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid user ID.'));
			}
			verifyTeamPrivs($authToken->data->id, 'admin', $die = true);

			$userId = $authToken->data->id;
			$teamInfo = getTeamInfoByUser($userId);
			$team = $teamInfo['team_number'];

			$query = 'UPDATE team_memberships SET status="joined", privs="read" WHERE user_id='.db_quote($formData['id']).' AND team_number='.db_quote($team).'';
			$result = db_query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			$msg_data = array(
				'users' => array($formData['id']),
				'push' => array(
					'subject' => 'Team Membership',
					'message' => 'Team '.$team.' has approved your membership request.'
				)
			);
			newMessageToQueue('user_notification', $msg_data);
			$newMembership = getTeamMembership($team);
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Team membership confirmed for '.$formData['userInfo']['full_name'], 'membership'=>$newMembership));
		});
		$app->post('/deny', function ($request, $response, $args) {
			$db = db_connect();
			$formData = $request->getParsedBody();
			$authToken = $request->getAttribute("jwt");
			if(!isset($formData['id']) || $formData['id'] == '')
			{
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid user ID.'));
			}
			verifyTeamPrivs($authToken->data->id, 'admin', $die = true);

			$userId = $authToken->data->id;
			$teamInfo = getTeamInfoByUser($userId);
			$team = $teamInfo['team_number'];

			removeTeamMembership($formData['id'], $team);
			$newMembership = getTeamMembership($team);
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Team membership denied for '.$formData['userInfo']['full_name'], 'membership'=>$newMembership));
		});
		$app->post('/remove', function ($request, $response, $args) {
			$db = db_connect();
			$formData = $request->getParsedBody();
			$authToken = $request->getAttribute("jwt");
			if(!isset($formData['id']) || $formData['id'] == '')
			{
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid user ID.'));
			}
			verifyTeamPrivs($authToken->data->id, 'admin', $die = true);

			$userId = $formData['id'];
			$teamInfo = getTeamInfoByUser($userId);
			$team = $teamInfo['team_number'];

			removeTeamMembership($formData['id'], $team);
			$newMembership = getTeamMembership($team);
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>$formData['userInfo']['full_name'].' removed from team', 'membership'=>$newMembership));
		});
	});
	$app->group('/account', function () use ($app) {
		$app->post('/register', function ($request, $response, $args) {
			$db = db_connect();
			$formData = $request->getParsedBody();
			$authToken = $request->getAttribute("jwt");

			if(!isset($formData['user_id']) || $formData['user_id'] == '') {
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid user ID.  Reload the page and try again.'));
			}
			if(!isset($formData['team_number']) || $formData['team_number'] == '') {
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid Team Number.  Reload the page and try again.'));
			}
			verifyUser($formData['user_id'], $authToken->data->id, $die = true);

			$query = 'insert into team_accounts (`team_number`, `contact_id`, `current_event`, `logo`, `background_header`, `background_body`, `font_color_header`, `font_color_body`) VALUES ('.db_quote($formData['team_number']).', "", NULL, "", "", "", "", "")';
			$result = $db->query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			$id = uniqid();
			$query = 'insert into team_memberships (id, team_number, user_id, privs, status) VALUES
													('.db_quote($id).',
													'.db_quote($formData['team_number']).',
													'.db_quote($formData['user_id']).',
													"admin",
													"joined")';
			$result = $db->query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			$teamInfo = getTeamInfoByUser($formData['user_id']);
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'You have successfully registered Team '.$formData['team_number'].'.', 'teamInfo'=>$teamInfo));
		});
		$app->post('/updateInfo', function ($request, $response, $args) {
			$db = db_connect();
			$formData = $request->getParsedBody();
			$authToken = $request->getAttribute("jwt");

			if(!isset($formData['team_number']) || $formData['team_number'] == '') {
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Team Number.'));
			}
			$logo = isset($formData['logo']) ? $formData['logo']:'';
			$h_fc = isset($formData['font_color_header']) ? $formData['font_color_header']:'';
			$h_bg = isset($formData['background_header']) ? $formData['background_header']:'';
			$b_fc = isset($formData['font_color_body']) ? $formData['font_color_body']:'';
			$b_bg = isset($formData['background_body']) ? $formData['background_body']:'';
			$current_event = isset($formData['current_event']) ? $formData['current_event']:'';
			verifyTeamPrivs($authToken->data->id, 'admin', $die = true);
			$currentTeamInfo = getTeamInfoByUser($authToken->data->id);
			if($currentTeamInfo['current_event'] != $current_event && $current_event != '') {
				$eventInfo = getEventInfo($current_event);
				$users = getTeamMembership($formData['team_number'], array('status'=>array('joined'), 'not_user'=>array($authToken->data->id)));
				$msg_data = array(
					'users' => $users,
					'push' => array(
						'subject' => 'Team '.$formData['team_number'].' current event changed.',
						'message' => 'The Current Event is the '.$eventInfo['name'].' from '.date('l M d, Y',strtotime($eventInfo['start_date'])).' to '.date('l M d, Y',strtotime($eventInfo['end_date'])),
						'tag' => ''
					)
				);
				newMessageToQueue('user_notification', $msg_data);
			}
			if($current_event == '') {
				$current_event = 'NULL';
			} else {
				$current_event = db_quote($current_event);
			}
			$query = 'UPDATE team_accounts SET logo='.db_quote($logo).',
											   font_color_header='.db_quote($h_fc).',
											   background_header='.db_quote($h_bg).',
											   font_color_body='.db_quote($b_fc).',
											   background_body='.db_quote($b_bg).',
											   current_event='.$current_event.'
								WHERE team_number='.db_quote($formData['team_number']).'';
			$result = $db_query($query);
			if(!$result) {
				return $response->withJson($db_error($query));
			}
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Team Information updated for Team '.$formData['team_number']));
		});
	});
	$app->get('/search[/{search}]', function ($request, $response, $args) {
		$db = db_connect();
		$authToken = $request->getAttribute("jwt");

		$data = array();
		$search = $request->getAttribute("search");
		if(isset($search) && $search != '' && $search != 'undefined') {
			$query = 'select * from teams where team_number LIKE "%'.$search.'%" OR nickname LIKE "%'.$search.'%" ORDER BY team_number ASC';
			$result = $db->query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			if($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$temp = $row;
					$temp['account_status'] = teamAccountStatus($row['team_number']);
					$data[] = $temp;
				}
			}
		}
		return $response->withJson($data);
	});
	$app->group('/team/{team:[0-9]+}', function () use ($app) {
		$app->get('', function ($request, $response, $args) {
			$db = db_connect();
			$authToken = $request->getAttribute("jwt");
			$team = $request->getAttribute("team");

			if($team == '') {
				$data = array('status'=>true, 'msg'=>'Invalid Team Number');
				$response->withJson($data);
			} elseif($team == 0) {
				$userId = $authToken->data->id;
				$teamInfo = getTeamInfoByUser($userId);
				$team = $teamInfo['team_number'];
			}
			$query = 'select teams.*, team_accounts.* from teams INNER JOIN team_accounts ON teams.team_number=team_accounts.team_number WHERE teams.team_number="'.$team.'"';
			$result = $db->query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			if($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$data = $row;
				$data['membership'] = getTeamMembership($team);
			}
			return $response->withJson($data);
		});
		$app->get('/checkAccount', function ($request, $response, $args) {
			$db = db_connect();
			$authToken = $request->getAttribute("jwt");
			$team = $request->getAttribute("team");
			$data = array('status'=>false, 'active'=>false);
			if($team == '') {
				$response->withJson($data);
			}
			$query = 'select * from teams INNER JOIN team_accounts ON teams.team_number=team_accounts.team_number WHERE teams.team_number="'.$team.'"';
			$result = $db->query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			if($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$data = array('status'=>true, 'msg'=>$row, 'active'=>true);
			}
			return $response->withJson($data);
		});
	});
	$app->get('/multipleTeamInfo/{teams}', function ($request, $response, $args) {
		$db = db_connect();
		$authToken = $request->getAttribute("jwt");
		$teams = $request->getAttribute("teams");

		if($teams == '') {
			$data = array('status'=>true, 'msg'=>'Invalid Request');
			$response->withJson($data);
		}
		$teamsArr = explode(',',$teams);
		foreach($teamsArr as $team)
		{
			$query = 'select * from teams WHERE team_number = "'.$team.'"';
			$result = $db->query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			if($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$data[] = $row;
			}
		}
		return $response->withJson($data);
	});
});

?>
