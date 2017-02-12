'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.reportsTable-ctrl', function($scope,$log, $sce, $state, $uibModal, toastr, teams, reports, NgTableParams, eventList, eventInit, teamsInit) {
	$scope.reportInfo = {
		'selectRegional': eventInit,
		'teams': teamsInit
	};
	
	$scope.compareTeams = [];
	
	$scope.eventList = eventList;
	$scope.searchTeams = function($select) {
		teams.searchTeams($select.search).then(function(response){ 
			$scope.searchTeamRes = response;
		});
	}
	$scope.createEventGroups = function(event){
		return  event.status;
	};
		
	reports.getReportsTableByYear($scope.reportInfo.selectRegional).then(function(response){
		$scope.data = response.data.allData;
		$scope.graphData = response.data.graphData;
		$scope.tableParams = new NgTableParams({
			filter: { 
				 
			},
		},{
			page: 1,            // show first page
			count: 20,
			dataset: $scope.data
		});		
	});	
		
	$scope.compareTeamsFunct = function(team) {
		var i = $scope.compareTeams.indexOf(team);
		if(i == -1)
		{
			$scope.compareTeams.push(team);
		}
		else
		{
			$scope.compareTeams.splice(i,1);
		}
	}	
		
	$scope.openGraphModal = function(team,graph) {
		var modalInstance = $uibModal.open({
			animation: true,
			templateUrl: './views/modals/reportTableGraphModal.html',
			controller: 'reportTableGraphModal-ctrl',
			size: 'lg',
			resolve: {
				graph:function () {
				  return graph;
				},
				team:function () {
				  return team;
				},
				data:function () {
				  return $scope.graphData[team][graph];
				},
				
			}
		});
		modalInstance.result.then(function (data) {
			}, function () {
				$log.info('Modal dismissed at: ' + new Date());
		});
	};
})
.controller('reportTableGraphModal-ctrl', function($scope,$log, $sce, $state, $timeout, $auth, $uibModalInstance, toastr, team, graph, data) {
	$scope.graph = graph;
	$scope.team = team;
	$scope.data = data;
		
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});