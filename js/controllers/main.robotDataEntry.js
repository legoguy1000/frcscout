'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.robotDataEntry-ctrl', function($scope,$log, $sce, $state, $stateParams, toastr, teams, robots, teamInfo) {
	
	$scope.robotDataEntry = {
		'selectTeam':teamInfo,
	}
	
	$scope.robotData = {};
	
	$scope.searchTeams = function($select) {
		teams.searchTeams($select.search).then(function(response){ 
			$scope.searchTeamRes = response;
		});
	}
	
	
	$scope.getRobotInfo = function() {
		if($scope.robotDataEntry.selectTeam)
		{
			var year = $scope.season;
			var team = $scope.robotDataEntry.selectTeam.team_number;
			robots.getRobotsByTeamNumberAndYear(team, year).then(function(response){ 
				if(response.data != null && response.data.length!=0)
				{
					$scope.robotData = response.data;
				}
			});
		}
	}
	$scope.getRobotInfo();
	
	$scope.updateRobotInfo = function() {
		var year = $scope.season;
		var team = $scope.robotDataEntry.selectTeam.team_number;
		var data = {
			'year':year,
			'team':team,
			'data':$scope.robotData
		}
		robots.updateRobotByTeamNumberAndYear(data).then(function(response){ 
			toastr[response.type](response.msg, 'Robot Data');
		});
	}
	
	$scope.driveTrainOptions = [
		'',
		'Tank',
		'Swerve',
		'Mecanum',
		'Slide (H)',
		'Holonomic',
		'Other',
	];
	
	$scope.capabilityTimesOptions = [
		'',
		'Autonomous',
		'Teleoperated',
		'Both',
	];
	
	$scope.yesNoOptions = [
		'',
		'Yes',
		'No',
	];
})
