'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.reportsGraphs-ctrl', function($scope,$log, $sce, $state, toastr, teams, reports, eventList, eventInit, teamsInit) {
	$scope.reportInfo = {
		'selectRegional': eventInit,
		'teams': teamsInit
	};
	
	$scope.fullscreen = {
		'allianceScores': false,
		'individualScores': false,
	};
	$scope.makeFullscreen = function(report) {
		if(!(report in $scope.fullscreen))
		{
			$scope.fullscreen[report] = false;
		}
		var re = $scope.fullscreen[report];
		if(re == true)
		{
			$scope.fullscreen[report] = false;
		}
		else if(re == false)
		{
			$scope.fullscreen[report] = true;
		}
	}
	
	$scope.reportData = {
		'allianceScores':{
			'xLabels':[],
			'legend':[],
			'scores':[],
		},
		'individualScores': {
			'xLabels':[],
			'legend':[],
			'scores':[],
		},
	};
	
	
	$scope.eventList = eventList;
	$scope.searchTeams = function($select) {
		teams.searchTeams($select.search).then(function(response){ 
			$scope.searchTeamRes = response;
		});
	}
	$scope.createEventGroups = function(event){
		return  event.status;
	};
	
	var getReportDataInit = function() {
		if($scope.reportInfo.teams.length != 0)
		{
			$scope.getReportData();
		}
	}
	$scope.getReportData = function() {
		if($scope.reportInfo.selectRegional != '') 
		{
			var data = {
				'event':$scope.reportInfo.selectRegional,
				'teams':$scope.reportInfo.teams
			};
			reports.getReportsByYear(data).then(function(response){ 
				if(response.status == false)
				{
					toastr[response.type](response.msg, 'Reports');
				}
				$scope.reportData = response.data;
			});
		}
	}
	getReportDataInit();
	var legendOptions = {
		display: true,
		position: 'bottom',
	};
	var scalesOptions = {
		'yaxis1': {
			id: 'y-axis-1',
			type: 'linear',
			display: true,
			position: 'left',
			ticks: {
				min: 0,
			}
		},
		'yaxis2':	{
			id: 'y-axis-2',
			type: 'linear',
			display: true,
			position: 'right',
			ticks: {
				min: 0,
			}
		}
	};
	$scope.chartOptions = {
		'singleAxis': {
			legend: legendOptions,
			scales: {
				'yAxes':[scalesOptions.yaxis1]
			},
		},
		'doubleAxis': {
			legend: legendOptions,
			scales: {
				'yAxes':[scalesOptions.yaxis1,scalesOptions.yaxis2]
			},
		},
	};
	
	$scope.datasetOverride = {
		'averageScores': [{ yAxisID: 'y-axis-1' }, { yAxisID: 'y-axis-2' }],
		'fouls': [{ yAxisID: 'y-axis-1' },{ yAxisID: 'y-axis-1' }, { yAxisID: 'y-axis-2' }, { yAxisID: 'y-axis-2' },],
		'autoGoals': [{ yAxisID: 'y-axis-1' },{ yAxisID: 'y-axis-1' }, { yAxisID: 'y-axis-2' }, { yAxisID: 'y-axis-2' },],
		'teleGoals': [{ yAxisID: 'y-axis-1' },{ yAxisID: 'y-axis-1' }, { yAxisID: 'y-axis-2' }, { yAxisID: 'y-axis-2' },],
	};
	/************************************************************************/
/* 	$scope.options = {};
	$scope.options.allianceScores = {
		chart: {
			type: 'lineChart',
			height: 450,
			margin : {
				top: 20,
				right: 20,
				bottom: 40,
				left: 55
			},
			x: function(d){ return d.x; },
			y: function(d){ return d.y; },
			useInteractiveGuideline: true,
			dispatch: {
				stateChange: function(e){ console.log("stateChange"); },
				changeState: function(e){ console.log("changeState"); },
				tooltipShow: function(e){ console.log("tooltipShow"); },
				tooltipHide: function(e){ console.log("tooltipHide"); }
			},
			xAxis: {
				axisLabel: 'Match'
			},
			yAxis: {
				axisLabel: 'Score'
			},
			callback: function(chart){
				console.log("!!! lineChart callback !!!");
			},
		},
		title: {
			enable: true,
			text: 'Alliance Scores'
		},
		subtitle: {
			enable: true,
			text: 'Alliance Scores per Match',
			css: {
				'text-align': 'center',
				'margin': '10px 13px 0px 7px'
			}
		},
	}; */

});