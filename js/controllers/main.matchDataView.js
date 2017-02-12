'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.matchDataView-ctrl', function($scope,$log, $sce, $state, $timeout, $auth, $uibModal, toastr, matches, eventList, eventInit, matchInit, matchStats) {
	$scope.matchDataEntry = {
		'selectRegional': eventInit,
		'matchNumber': matchInit.match_num,
	};
	
	$scope.loadingData = false;
	$scope.eventList = eventList;
	$scope.matchStats = matchStats;
	
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
		if(event != undefined && event != '')
		{
			$scope.loadingData = true;
			$scope.matchDataEntry.data = {};
			$scope.matchDataEntry.team_number = '';
			matches.getMatchDataStats(event, match).then(function(response) {
				$scope.matchStats = response;
				$scope.loadingData = false;
			});
		}
	}
})
