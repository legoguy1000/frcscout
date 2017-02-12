'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.season2016-ctrl', function($scope,$log, $sce, $state, toastr, seasonData) {
	$scope.season = 2016;
	$scope.seasonData = seasonData;
	$scope.videoUrl = $sce.trustAsResourceUrl('https://www.youtube.com/embed/'+seasonData.youtube_id);
	
})
.controller('main.season2016.info-ctrl', function($scope,$log, $sce, $state) {

})
.controller('main.season2016.matchDataEntry-ctrl', function($scope, $rootScope, $log, $sce, $state, $timeout, $auth, $uibModal, toastr, matches) {
	$scope.defensesCrossed = {'defense':'', 'start':'', 'end':''};
	var defensesCrossedOrig = {};
	angular.copy($scope.defensesCrossed, defensesCrossedOrig);
	$scope.crossingDefense = false;
	$scope.currentDefense= '';
		
	$rootScope.$on('matchStopTimer', function(msg, data) {
		$scope.crossingDefense = false;
		$scope.currentDefense= '';
		console.log('Stop Timer');
	});
	
	$scope.defenses = {
		'Category A': ['Portcullis','Cheval De Frise'],
		'Category B': ['Moat','Ramparts'],
		'Category C': ['Drawbridge','Sally Port'],
		'Category D': ['Rock Wall','Rough Terrain'],
		'Default': ['Low Bar'],
	}
	
	$scope.crossedDefense = function(defense)
	{
		if($scope.matchDataEntry.matchStarted==true && $scope.gameOver==false && $scope.matchDataEntry.team_number != '')
		{
			if($scope.crossingDefense == false)
			{
				// alert(defense);
				$scope.insertMatchData('crossing_defense', defense, 'start', '');
				
				$scope.defensesCrossed = {'defense':defense, 'start':$scope.timer, 'end':''};
				$scope.crossingDefense = true;
				$scope.currentDefense= defense;
			}
			else if($scope.crossingDefense == true && $scope.currentDefense == defense)
			{
				$scope.insertMatchData('crossing_defense', defense, 'end', '');
				$scope.defensesCrossed.end=$scope.timer;
				$scope.crossingDefense = false;
				$scope.currentDefense= "";
			}
		}
	}
	
	$scope.crossFail = function()
	{
		if($scope.matchDataEntry.matchStarted==true && $scope.gameOver==false && $scope.matchDataEntry.team_number != '')
		{
			if($scope.crossingDefense == true)
			{
				$scope.insertMatchData('crossing_defense', $scope.currentDefense, 'fail', '');
				$scope.crossingDefense = false;
				$scope.currentDefense= "";
			}
		}
	}
})
.controller('main.season2016.matchDataView-ctrl', function($scope,$log, $sce, $state, $timeout, $auth, $uibModal, toastr, matches) {
	$scope.defenses = {
		'Category A': ['Portcullis','Cheval De Frise'],
		'Category B': ['Moat','Ramparts'],
		'Category C': ['Drawbridge','Sally Port'],
		'Category D': ['Rock Wall','Rough Terrain'],
		'Default': ['Low Bar'],
	}
})
.controller('main.season2016.robotDataEntry-ctrl', function($scope,$log, $sce, $state, $stateParams, toastr, teams, robots) {
	
	$scope.$parent.robotData = {
		'drive_train':'',
		"portcullis": "",
		"cheval_de_frise": "",
		"ramparts": "",
		"drawbridge": "",
		"sally_port": "",
		"moat": "",
		"rock_wall": "",
		"rough_terrain": "",
		"low_bar": "",
		"low_goal": "",
		"high_goal": "",
		"climb_tower": "",
		"block": "",
	};
	var robotDataOrig = angular.copy($scope.robotData);

})
.controller('main.season2016.reportsGraphs-ctrl', function($scope,$log, $sce, $state, toastr, teams, reports) {
	
	

});