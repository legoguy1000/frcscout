'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.matchDataEntry-ctrl', function($scope,$log, $sce, $state, $timeout, $auth, $uibModal, toastr, matches, robots, eventList, eventInit, matchInit, matchInfo) {
	$scope.$on('$stateChangeStart', function () {
		$scope.stopTimer();
	});

	$scope.loadingData = false;

	$scope.matchDataEntry = {
		'selectRegional': eventInit,
		'matchNumber': matchInit.match_num,
		'team_number': '',
		'matchStarted': matchInfo.match_data.match_started,
		'match_start_time': matchInfo.match_data.match_start,
		'data': matchInfo.match_data.team_data,
		'completed': matchInfo.completed,
		'ready_to_start': matchInfo.ready_to_start,
	};
	$scope.serverTime = matchInfo.server_time;
	var timeDiff = $scope.serverTime - (Date.now() / 1000);
	console.log("timeDiff: "+timeDiff);
	console.log("Server Time: "+$scope.serverTime);
	/*$scope.serverTimeWS = new WebSocket('wss://ws.frcscout.resnick-tech.com:443/ws/time');
	$scope.serverTimeWS.onopen = function(){
		// Web Socket is connected, send data using send()
		console.log("Server Time Web Soccket Connection is open...");
	}
	$scope.serverTimeWS.onmessage = function (e) {
		$scope.$apply(function () {
			var messageData = JSON.parse(e.data);
			$scope.serverTime = messageData.server_time;
		});
	};
	$scope.serverTimeWS.onclose = function() {
		// websocket is closed.
		console.log("Chat Web Soccket Connection is closed...");
	};
	$scope.serverTimeWS.onclose = function() {
		// websocket is closed.
		console.log("Chat Web Soccket Connection is closed...");
	};*/

	$scope.eventList = eventList;
	$scope.matchInfo = matchInfo;
	$scope.pointValues = {};
	$scope.robotData = {};

	$scope.getMatchInfo = function()
	{
		getMatchInfo();
	}

	$scope.increaseMatch = function(event){
		$scope.matchDataEntry.matchNumber = $scope.matchDataEntry.matchNumber+1;
		getMatchInfo();
	};
	$scope.decreaseMatch = function(event){
		if($scope.matchDataEntry.matchNumber>1)
		{
			$scope.matchDataEntry.matchNumber = $scope.matchDataEntry.matchNumber-1;
			getMatchInfo();
		}
	};

	$scope.createEventGroups = function(event){
		return  event.status;
	};

	function getMatchInfo()
	{

		var event = $scope.matchDataEntry.selectRegional;
		var match = $scope.matchDataEntry.matchNumber;
		closeMatchWS();
		if(event != undefined && event != '')
		{
			$scope.loadingData = true;
			$scope.matchDataEntry.data = {};
			$scope.matchDataEntry.team_number = '';
			matches.getMatchByEventNumber(event, match).then(function(response) {
				$scope.matchInfo = response;
				$scope.loadingData = false;
				$scope.matchDataEntry.matchStarted = response.match_data.match_started;
				$scope.matchDataEntry.match_start_time = response.match_data.match_start;
				$scope.matchDataEntry.data = response.match_data.team_data;
				$scope.matchDataEntry.completed = response.completed;
				$scope.matchDataEntry.ready_to_start = response.ready_to_start;
				$scope.serverTime = response.server_time;
				var timeDiff = $scope.serverTime - (Date.now() / 1000);
				console.log("timeDiff: "+timeDiff);
				console.log("Server Time: "+$scope.serverTime);
				startmatchDataWS();
				setStartTime();
			});
		}
	}

	function getPointsValues()
	{
		matches.getPointsByYear($scope.season).then(function(response) {
			$scope.pointValues = response;
		});
	}

	$scope.selectTeam = function(team)
	{
		if($scope.matchDataEntry.team_number == team)
		{
			$scope.matchDataEntry.team_number = '';
			$scope.robotData = {};
		}
		else
		{
			$scope.matchDataEntry.team_number = team;
			if($scope.matchDataEntry.team_number)
			{
				var year = $scope.season;
				var team = $scope.matchDataEntry.team_number;
				robots.getRobotsByTeamNumberAndYear(team, year).then(function(response){
					if(response.data != null && response.data.length!=0)
					{
						$scope.robotData = response.data;
					}
				});
			}
		}
	}

	$scope.insertMatchData = function(action, attr1, attr2, comment)
	{
		var data = {
			'event': $scope.matchDataEntry.selectRegional,
			'match_number': $scope.matchDataEntry.matchNumber,
			'team_number': $scope.matchDataEntry.team_number,
			'data': {
				'action':action,
				'attr_1':attr1,
				'attr_2':attr2,
				'comment':comment,
			}
		}
		matches.insertMatchData(data).then(function(response) {
			toastr[response.type](response.msg, 'Match Data');
		});
	}

	$scope.startMatch = function()
	{
		var data = {
			'event': $scope.matchDataEntry.selectRegional,
			'match_number': $scope.matchDataEntry.matchNumber,
		}
		console.log('Start Match unix: '+$scope.serverTime);
		matches.startMatch(data).then(function(response) {
			if(response.status == false)
			{
				toastr[response.type](response.msg, 'Match Data');
			}
		});
	}


	var timer;
	var timeCounter;
	$scope.gameMode = '';
	$scope.timer = 0;
	$scope.stopTimer = function()
	{
		$timeout.cancel(timer);
		console.log('Stop Timer');
		timeCounter = 0;
		$scope.showTimer = false;
		$scope.gameOver = false;
		$scope.gameMode = '';
		$scope.timer = 0;

		if($scope.MatchDataWS != undefined || $scope.serverTimeWS != undefined)
		{
			closeMatchWS();
		}
		$scope.$broadcast('matchStopTimer');
	}


	var setStartTime = function()
	{
		if($scope.matchDataEntry.matchStarted == true)
		{
			$scope.gameOver = false;
			var timer = ((Date.now() / 1000) + timeDiff) - $scope.matchDataEntry.match_start_time;
			if(timer >= 0) { $scope.timer = timer; }
			console.log('Start unix: '+$scope.serverTime);
			console.log('SFASD: '+$scope.matchDataEntry.match_start_time);
			console.log($scope.timer);
			timerCount();
		}
	}
	function timerCount()
	{
		if ($scope.timer >= 151)
		{
			$scope.timer = 150;
		}

		if ($scope.timer < 16)
		{
			$scope.gameMode = 'Autonomous';
		}
		else if ($scope.timer < 150)
		{
			$scope.gameMode = 'Teleoperated';
		}
		else
		{
			$timeout.cancel(timerCount);
			$scope.gameOver = true;

		}

		if ($scope.timer < 150)
		{
			console.log($scope.timer);
			timer = $timeout(timerCount, 100);
			$scope.timer = ((Date.now() / 1000) + timeDiff) - $scope.matchDataEntry.match_start_time;
		}
	}



	var startmatchDataWS = function()
	{
		console.log("startmatchDataWS Function initiated for "+$scope.matchDataEntry.selectRegional+"_qm"+$scope.matchDataEntry.matchNumber);
		closeMatchWS();//
		if($scope.matchDataEntry.completed==false && $scope.matchDataEntry.selectRegional!='' && $scope.matchDataEntry.matchNumber && $scope.matchDataEntry.ready_to_start)
		{
			console.log("Match Data WS Starting for "+$scope.matchDataEntry.selectRegional+"_qm"+$scope.matchDataEntry.matchNumber);
			$scope.matchDataWS = new WebSocket('wss://ws.frcscout.resnick-tech.com:443/ws/match?token='+$auth.getToken()+'&event='+$scope.matchDataEntry.selectRegional+'&match='+$scope.matchDataEntry.matchNumber);
			var match_key = $scope.matchDataEntry.selectRegional+'_qm'+$scope.matchDataEntry.matchNumber;
			$scope.matchDataWS.onopen = function() {
				// Web Socket is connected, send data using send()
				console.log("Match Web Soccket Connection is open...");
			}
			$scope.matchDataWS.onclose = function() {
				// websocket is closed.
				console.log("Match Web Soccket Connection is closed...");
			};
			$scope.matchDataWS.onmessage = function (e)
			{
				$scope.$apply(function () {
					var mdwsData = JSON.parse(e.data);
					if(mdwsData.type == match_key+'_data')
					{
						var MdesData = JSON.parse(e.data);
						if(!$scope.matchDataEntry.data[MdesData.team_number])
						{
							$scope.matchDataEntry.data[MdesData.team_number] = {};
						}
						if(!$scope.matchDataEntry.data[MdesData.team_number][MdesData.action])
						{
							$scope.matchDataEntry.data[MdesData.team_number][MdesData.action] = [];
						}
						if(MdesData.action=='crossing_defense')
						{
							if(!$scope.matchDataEntry.data[MdesData.team_number].crossing_defense[MdesData.attr_1])
							{
								$scope.matchDataEntry.data[MdesData.team_number].crossing_defense[MdesData.attr_1] = [];
							}
							if(MdesData.attr_2 == 'start')
							{
								$scope.matchDataEntry.data[MdesData.team_number].crossing_defense[MdesData.attr_1].push({'start':MdesData.time, 'end':'', 'time':''});
							}
							else
							{
								var le = $scope.matchDataEntry.data[MdesData.team_number].crossing_defense[MdesData.attr_1].length-1;
								var el = $scope.matchDataEntry.data[MdesData.team_number].crossing_defense[MdesData.attr_1][le];
								el.end = MdesData.time;
								el.time = el.end - el.start;
							}
						}
						else
						{
							$scope.matchDataEntry.data[MdesData.team_number][MdesData.action].push(MdesData);
						}
						console.log(JSON.stringify(MdesData, null, 4));
					}
					else if(mdwsData.type == match_key+'_info')
					{
						var MdesInfo = JSON.parse(e.data);
						$scope.matchInfo.red_score = MdesInfo.red_score;
						$scope.matchInfo.blue_score = MdesInfo.blue_score;
						toastr['success'](MdesInfo.msg, 'Match Data');
						if(MdesInfo.status == 'complete')
						{
							closeMatchWS();
						}
						console.log(JSON.stringify(MdesInfo, null, 4));
					}
					else if(mdwsData.type == match_key+'_start')
					{
						if($scope.matchDataEntry.matchStarted == false)
						{
							console.log('Receive ES unix: '+$scope.serverTime);
							var MdesStart = JSON.parse(e.data);
							$scope.matchDataEntry.matchStarted = true;
							$scope.matchDataEntry.match_start_time = MdesStart.match_start;
							toastr['success'](MdesStart.msg, 'Match Data');
							setStartTime();
							console.log(JSON.stringify(MdesStart, null, 4));
						}
					}
				});
			};
		}
		else {
			console.log("startmatchDataWS Function canceled for "+$scope.matchDataEntry.selectRegional+"_qm"+$scope.matchDataEntry.matchNumber);
			console.log($scope.matchDataEntry);
		}
	}
	var closeMatchWS = function() {
		if($scope.matchDataWS != undefined)
		{
			$scope.matchDataWS.close();
		}
		$scope.MatchDataWS = undefined;
		if($scope.serverTimeWS != undefined)
		{
			$scope.serverTimeWS.close();
		}
		$scope.serverTimeWS = undefined;
	}



	$scope.stopTimer();
	startmatchDataWS();
	setStartTime();
	//getPointsValues();

	$scope.openScoreModal = function()
	{
		var modalInstance = $uibModal.open({
			animation: true,
			templateUrl: './views/updateMatchScoreModal.html',
			controller: 'updateMatchScoreModal-ctrl',
			resolve: {
				matchInfo:function () {
				  return $scope.matchInfo;
				},
			}
		});
	}
})
.controller('updateMatchScoreModal-ctrl', function($scope,$log, $sce, $state, $timeout, $auth, $uibModalInstance, matches, toastr, matchInfo) {
	$scope.matchInfo = matchInfo;
	$scope.matchData = {
		'red_score':$scope.matchInfo.red_score,
		'blue_score':$scope.matchInfo.blue_score,
		'match_key':$scope.matchInfo.match_key,
	}
	$scope.updateScores = function()
	{
		matches.updateMatchScore($scope.matchData).then(function(response) {
			if(response.status == true)
			{
				toastr[response.type](response.msg, 'Match Data');
			}
		});
	}

	$scope.close = function () {
		$uibModalInstance.dismiss('cancel');
	};
})
