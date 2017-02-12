'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.home-ctrl', function($rootScope, $scope,$log, $sce, $state, toastr, auth, events, general) {
	$scope.eventData = [];
	$scope.stats = {};

	$scope.loading = {
		currentEvents: true,
	}
	
	var loadEvents = function() {
		events.getCurrentEvents().then(function(response) {
			$scope.eventData = response;
			$scope.loading.currentEvents = false;
		});
	}
	var loadStats = function() {
		general.getFrontPageStats().then(function(response) {
			$scope.stats = response;
		});
	}
	loadEvents();
	loadStats();
	
	$rootScope.$on('afterLoginAction', function(msg, data) {
		loadEvents();
	});
	
	$rootScope.$on('logOutAction', function(msg, data) {
		loadEvents();
	});
});
