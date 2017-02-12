'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.season-ctrl', function($scope,$log, $sce, $state, $stateParams, seasonData) {
	$scope.season = $stateParams.season;
	$scope.seasonData = seasonData;
	$scope.seasonData.youtube_id_safe = $sce.trustAsResourceUrl(seasonData.youtube_id);
	
	$scope.trustAsHtml = function(value) {
	  return $sce.trustAsHtml(value);
	};

});
