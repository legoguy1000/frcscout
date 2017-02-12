'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.test-ctrl', function($scope,$log, $sce, $state, $http, matches, currentYear, allYears, currentEvents) {

	// the last received msg
	$scope.matchData = {};
	$scope.event = '';
	$scope.allEvents = currentEvents;
	$scope.year = currentYear;
	$scope.allYears = allYears;
	$scope.matchES = undefined;
	
	$scope.closeSource = function() {
		if($scope.matchES != undefined)
		{
			$scope.matchES.close();
			console.log("Connection Closed");
		}
		$scope.matchES = undefined;
	}
	
	$scope.getMatchData = function()
	{
		$scope.closeSource();
		$scope.matchData = {};
		var event = $scope.event;
		matches.getMatchesByEventKey(event.event_key).then(function(response){
			if(response.complete == true)
			{
				$scope.matchData = response.data;
			}
			else
			{
				openSource();
			}
			//toastr[response.type](response.msg, type.substr(0,1).toUpperCase() + type.substr(1)+' Information');
			
		});
	}
	
	$scope.getEvents = function()
	{
		//$scope.close$scope.matchES();
		var year = $scope.year;
		matches.getEventsByYear(year).then(function(response){
			$scope.allEvents = response;
			//toastr[response.type](response.msg, type.substr(0,1).toUpperCase() + type.substr(1)+' Information');
		});
	}
	
	function openSource() {
		$scope.matchES = new EventSource('./site/event_source_match_info.php?event='+$scope.event.event_key);
		$scope.matchES.addEventListener('message', function (e) {
			$scope.$apply(function () {
				$scope.matchData = JSON.parse(e.data);
				console.log(e.data);
			});
		}, false);
		$scope.matchES.addEventListener("open", function(e) {
			console.log("Connection was opened.");
		}, false);
		$scope.matchES.addEventListener("error", function(e) {
			console.log("Error - connection was lost.");
		}, false);
	}
	

	$scope.scheduleUpdate = function()
	{
		var formData = {
			"message_data": {
				"event_key": "2016ohcl"
			},
			"message_type": "schedule_updated"
		}
		$http({
			url: './site/blue_alliance_webhook.php',
			method: "POST",
			data: formData,
			headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Tba-Checksum':'asdfasdfgsadfgwergsdfwe'}
		})
		.then(function(response) {
				// success
			}, 
			function(response) { // optional
				// failed
			}
		);
	}
});
