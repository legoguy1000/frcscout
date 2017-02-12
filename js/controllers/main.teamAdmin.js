'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.teamAdmin-ctrl', function($scope,$log, $sce, $state, toastr, teams, users, teamInfo, eventList, NgTableParams) {	
	$scope.teamInfo = teamInfo;
	$scope.eventList = eventList;
	
	$scope.saving = {
		teamInfo: false,
	};
	
	var createUserTable = function()
	{
		$scope.membersTable = new NgTableParams({
			
		}, { 
			dataset: $scope.teamInfo.membership
		});
	}
	
	$scope.createEventGroups = function(event){
		return  event.status;
	};
	
	createUserTable();
	$scope.memberPrivs = {};
	
	$scope.updateTeamInfo = function()
	{
		$scope.saving.teamInfo = true;
		var data = $scope.teamInfo;
		teams.updateTeamInfo(data).then(function(response){ 
			toastr[response.type](response.msg, 'Team Info');
			$scope.saving.teamInfo = false;
		});
	}
	
	$scope.updateMemberPrivs = function(user)
	{
		var data = {'id':user.id, 'privs':$scope.memberPrivs[user.id], 'userInfo':user};
		users.updateMemberPrivsById(data).then(function(response){ 
			//$scope.teamSearchRes = response;
			toastr[response.type](response.msg, 'Team Privs');
		});
	}
	
	$scope.approveTeamMember = function(user)
	{
		var data = {'id':user.id, 'userInfo':user};
		users.approveTeamMemberById(data).then(function(response){ 
			$scope.teamInfo.membership = response.membership;
			toastr[response.type](response.msg, 'Team Membership');
			createUserTable();
		});
	}
	
	$scope.rejectTeamMember = function(user)
	{
		var data = {'id':user.id, 'userInfo':user};
		users.denyTeamMemberById(data).then(function(response){ 
			$scope.teamInfo.membership = response.membership;
			toastr[response.type](response.msg, 'Team Membership');
			createUserTable();
		});
	}
	
	$scope.removeTeamMembership = function(user)
	{
		var data = {'id':user.id, 'userInfo':user};
		users.removeTeamMembershipById(data).then(function(response){ 
			$scope.teamInfo.membership = response.membership;
			toastr[response.type](response.msg, 'Team Membership');
			createUserTable();
		});
	}
	
	$scope.selectTeam = {
		'team': undefined
	};
	$scope.searchTeams = function($select) {
		teams.searchTeams($select.search).then(function(response){ 
			$scope.teamSearchRes = response;
		});
	}
	
	$scope.trustAsHtml = function(value) {
	  return $sce.trustAsHtml(value);
	};
	
	
	
	$scope.requestTeamJoin = function()
	{
		var data = {'user_id':$auth.getPayload().data.id, 'team_number':$scope.selectTeam.team.team_number};
		users.requestTeamJoin(data).then(function(response){ 
			$scope.activeTeamAccount = response.active;
		});
	}
	
	$scope.options = {
		round: false,
		format: 'hex',
		hue: true,
		alpha: true,
		swatch: true,
		swatchPos: 'left',
		swatchBootstrap: true,
		swatchOnly: false,
		pos: 'bottom left',
		case: 'upper',
		inline: false,
		placeholder: '',
	};
});
