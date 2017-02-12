'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.teams-ctrl', function($scope,$log, $sce, $state, toastr, $stateParams, teams) {
	$scope.team_number = $stateParams.team_number;
	$scope.teamInfo = {};
	teams.getTbaCompleteTeamInfo($stateParams.team_number, $stateParams.season).then(function(response){ 
		$scope.teamInfo = response;
	});
	
});
