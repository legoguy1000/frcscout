'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.season2017-ctrl', function($scope,$log, $sce, $state, toastr, seasonData) {
	$scope.season = 2017;
	$scope.seasonData = seasonData;
	$scope.videoUrl = $sce.trustAsResourceUrl('https://www.youtube.com/embed/'+seasonData.youtube_id);
	
})
.controller('main.season2017.info-ctrl', function($scope,$log, $sce, $state) {

})
.controller('main.season2017.matchDataEntry-ctrl', function($scope,$log, $sce, $state, $timeout, $auth, toastr, matches) {
	
	$scope.fuelNumbers = [1,10,20,30,40,50,60,70,80,90,100];
	
	$scope.goalCounts = {
		'high': "1",
		'low': "1",
	}
	
	$scope.dropdownDesc = 'Select the number of goals if scored in bulk.  You may add goals one at a time or use the dropdown to increase the number of balls scored at a single time.';
})
.controller('main.season2017.matchDataView-ctrl', function($scope,$log, $sce, $state, $timeout, $auth, $uibModal, toastr, matches) {
	
})
.controller('main.season2017.robotDataEntry-ctrl', function($scope,$log, $sce, $state, $stateParams, toastr, teams, robots) {
	
	$scope.$parent.robotData = {
		'drive_train':'',
		"low_goal": "",
		"high_goal": "",
		"deliver_gears": "",
		"climb_rope": "",
		"fuel_hopper": "",
		"loading_station": "",
		"fuel_capacity": "",
	};
	var robotDataOrig = angular.copy($scope.robotData);
})
.controller('main.season2017.reportsGraphs-ctrl', function($scope,$log, $sce, $state, toastr, teams, reports) {
	
})
.controller('main.season2017.reportsTable-ctrl', function($scope,$log, $sce, $state, toastr, teams, reports, NgTableParams) {
	
	
});