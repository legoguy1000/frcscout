angular.module('frcScout')
.service('general', function ($http) {
	return {
		getGameSeasons: function () {
			return $http.get('api/v1/general/seasonInfo/all')
			.then(function(response) {
				return response.data;
			});
		},
		getSeasonInfoByYear: function (year) {
			return $http.get('api/v1/general/seasonInfo/'+year)
			.then(function(response) {
				return response.data;
			});
		},
		getFrontPageStats: function () {
			return $http.get('api/v1/general/frontPageStats')
			.then(function(response) {
				return response.data;
			});
		},
	};
})
.service('auth', function ($window) {
	return {
		parseJwt: function(token) {
			if(token != '' && token != undefined)
			{
				var base64Url = token.split('.')[1];
				var base64 = base64Url.replace('-', '+').replace('_', '/');
				return JSON.parse($window.atob(base64));
			}
			else
			{
				return false;
			}
		},
		saveToken: function(token) {
			$window.localStorage['satellizer_token'] = token;
		},
		getToken: function() {
			return $window.localStorage['satellizer_token'];
		},
		isAuthed: function() {
			var token = this.getToken();
			if(token) {
				var params = this.parseJwt(token);
				return Math.round(new Date().getTime() / 1000) <= params.exp;
			} 
			else {
				return false;
			}
		},
		logout: function() {
			$window.localStorage.removeItem('satellizer_token');
		}
	};
})
.service('teamInfoSrv', function ($window) {
	return {
		saveTeamInfo: function(teamInfo) {
			$window.localStorage['teamInfo'] = JSON.stringify(teamInfo);
		},
		getTeamInfo: function() {
			var data = {};
			if($window.localStorage['teamInfo'] && $window.localStorage['teamInfo']!=null)
			{
				data = JSON.parse($window.localStorage['teamInfo']);
			}
			return data;
		},
		onTeam: function() {
			var teamInfo = this.getTeamInfo();
			if(teamInfo && teamInfo.team_number!='') {
				return true;
			} 
			else {
				return false;
			}
		},
		deleteTeamInfo: function() {
			$window.localStorage.removeItem('teamInfo');
		}
	};
})
.service('users', function ($http) {
	return {
		getAllUsers: function () {
			return $http.get('site/getAllUsers.php')
			.then(function(response) {
				return response.data;
			});
		},
		getUserById: function (id) {
			return $http.get('api/v1/users/'+id)
			.then(function(response) {
				return response.data;
			});
		},
		updateUserPersonalInfoById: function (formData) {
			return $http.post('api/v1/users/updatePersonalInfo',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateMemberPrivsById: function (formData) {
			return $http.post('api/v1/teams/updateMemberPrivs',formData)
			.then(function(response) {
				return response.data;
			});
		},
		requestTeamJoin: function (formData) {
			return $http.post('api/v1/teams/membership/request',formData)
			.then(function(response) {
				return response.data;
			});
		},
		approveTeamMemberById: function (formData) {
			return $http.post('api/v1/teams/membership/approve',formData)
			.then(function(response) {
				return response.data;
			});
		},
		denyTeamMemberById: function (formData) {
			return $http.post('api/v1/teams/membership/deny',formData)
			.then(function(response) {
				return response.data;
			});
		},
		removeTeamMembershipById: function (formData) {
			return $http.post('api/v1/teams/membership/remove',formData)
			.then(function(response) {
				return response.data;
			});
		},
		getTeamMembershipByUser: function (id) {
			return $http.get('api/v1/users/'+id+'/team/membership')
			.then(function(response) {
				return response.data;
			});
		},
		getTeamInfoByUser: function (id) {
			return $http.get('api/v1/users/'+id+'/team/info')
			.then(function(response) {
				return response.data;
			});
		},
		deviceNotificationSubscribe: function (formData) {
			return $http.post('api/v1/users/pushNotification/subscribe',formData)
			.then(function(response) {
				return response.data;
			});
		},
		deviceNotificationUnsubscribe: function (formData) {
			return $http.post('api/v1/users/pushNotification/unsubscribe',formData)
			.then(function(response) {
				return response.data;
			});
		},
		deviceNotificationUpdateEndpoint: function (formData) {
			return $http.post('api/v1/users/pushNotification/endpointUpdate',formData)
			.then(function(response) {
				return response.data;
			});
		},
	};
})
.service('matches', function ($http) {
	return {
		getMatchesByEventKey: function (event_key) {
			return $http.get('api/v1/events/'+event_key+'/matches')
			.then(function(response) {
					return response.data;
				});
		},
		getMatchesByEventYear: function (event, year) {
			return $http.get('api/v1/events/'+year+'/'+event+'/matches')
			.then(function(response) {
					return response.data;
				});
		},
		getEventsByYear: function (year) {
			return $http.get('api/v1/events/'+year)
			.then(function(response) {
					return response.data;
				});
		},
		getMatchByEventNumber: function (event, match) {
		//	return $http.get('site/getMatchByEventNumber.php?event='+event+'&match='+match)
			return $http.get('api/v1/matches/matchData/'+event+'_qm'+match+'')
			.then(function(response) {
					return response.data;
				});
		},
		getOfficialMatchData: function (event, match) {
			return $http.get('api/v1/matches/matchData/'+event+'_qm'+match+'/official')
			.then(function(response) {
					return response.data;
				});
		},
		getMatchDataStats: function (event, match) {
			return $http.get('api/v1/matches/matchData/'+event+'_qm'+match+'/stats')
			.then(function(response) {
					return response.data;
				});
		},
		getUpcomingMatchByEvent: function (event) {
			return $http.get('api/v1/events/'+event+'/upcomingMatch')
			.then(function(response) {
					return response.data;
				});
		},
		insertMatchData: function (formData) {
			return $http.post('api/v1/matches/insertMatchData',formData)
			.then(function(response) {
				return response.data;
			});
		},
		startMatch: function (formData) {
			return $http.post('api/v1/matches/startMatch',formData)
			.then(function(response) {
				return response.data;
			});
		},
		updateMatchScore: function (formData) {
			return $http.post('api/v1/matches/updateMatchScore',formData)
			.then(function(response) {
				return response.data;
			});
		},
		getPointsByYear: function (year) {
			var yearStr = '';
			if(year != undefined && year != '')
			{
				yearStr = '/'+year;
			}
			return $http.get('api/v1/matches/pointsByYear'+yearStr)
			.then(function(response) {
					return response.data;
				});
		},
		
	};
})
.service('teams', function ($http) {
	return {
		searchTeamsBA: function (team) {
			return $http.get('site/searchTeamsBA.php?team='+team)
			.then(function(response) {
					return response.data;
				});
		},
		searchTeams: function (search) {
			var searchStr = '';
			if(search != undefined && search != '')
			{
				searchStr = '/'+search;
			}
			return $http.get('/api/v1/teams/search'+searchStr)
			.then(function(response) {
					return response.data;
				});
		},
		checkTeamAccount: function (team) {
			return $http.get('/api/v1/teams/team/'+team+'/checkAccount')
			.then(function(response) {
					return response.data;
				});
		},
		getTeamInfoByNumber: function (team) {
			return $http.get('/api/v1/teams/team/'+team)
			.then(function(response) {
					return response.data;
				});
		},
		getMultipleTeamInfo: function (teams) {
			return $http.get('/api/v1/teams/multipleTeamInfo/'+teams)
			.then(function(response) {
					return response.data;
				});
		},
		getTbaCompleteTeamInfo: function (team, year) {
			return $http.get('site/getTbaCompleteTeamInfo.php?team='+team+'&year='+year)
			.then(function(response) {
					return response.data;
				});
		},
		updateTeamInfo: function (formData) {
			return $http.post('/api/v1/teams/account/updateInfo',formData)
			.then(function(response) {
					return response.data;
				});
		},
		registerTeam: function (formData) {
			return $http.post('/api/v1/teams/account/register',formData)
			.then(function(response) {
					return response.data;
				});
		},
	};
})
.service('events', function ($http) {
	return {
		getEventsForDataEntry: function (year) {
			var yearStr = '';
			if(year != undefined && year != '')
			{
				yearStr = '/'+year;
			}
			return $http.get('/api/v1/events/dataEntry'+yearStr)
			.then(function(response) {
				return response.data;
			});
		},
		getCurrentEvents: function () {
			return $http.get('/api/v1/events/current')
			.then(function(response) {
				return response.data;
			});
		},
	};
})
.service('reports', function ($http) {
	return {
		getReportsByYear: function (formData) {
			return $http.post('site/getReportsByYear.php',formData)
			.then(function(response) {
					return response.data;
				});
		},
		getReportsTableByYear: function (event) {
			return $http.get('site/getReportsTableByYear.php?event='+event)
			.then(function(response) {
					return response.data;
				});
		},
	};
})
.service('robots', function ($http) {
	return {
		getRobotsByTeamNumberAndYear: function (team, year) {
			return $http.get('site/getRobotsByTeamNumberAndYear.php?team='+team+'&year='+year)
			.then(function(response) {
					return response.data;
				});
		},
		updateRobotByTeamNumberAndYear: function (formData) {
			return $http.post('site/updateRobotByTeamNumberAndYear.php',formData)
			.then(function(response) {
					return response.data;
				});
		},
	};
});