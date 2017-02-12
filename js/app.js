'use strict';
/**
 * @ngdoc overview
 * @name frcScout
 * @description
 * # frcScout
 * alert(JSON.stringify(YOUR_OBJECT_HERE, null, 4));
 * Main module of the application.
 */
angular
.module('frcScout', [
	/* 'oc.lazyLoad', */
	'satellizer',
	'ui.router',
	'ui.bootstrap',
	'ngAnimate',
	'ngSanitize',
	'toastr',
	'ui.select',
	'angular-loading-bar',
	'ngTouch',
	'ngTable',
	/* 'textAngular', */
	/* 'growlNotifications', */
	/* 'angular-web-notification', */
	'luegg.directives',
	'zInfiniteScroll',
	'angularMoment',
	'color.picker',
	'ui.router.default',
	'chart.js',
	'angulartics', 
	'angulartics.google.analytics',
	'cp.ngConfirm',
	'angular-smilies',
	'monospaced.elastic',
	'nvd3',
])
.config(['$stateProvider','$urlRouterProvider','$locationProvider', '$httpProvider',function ($stateProvider,$urlRouterProvider,$locationProvider,$httpProvider) { 
   
	$locationProvider.html5Mode(true);
    $urlRouterProvider.otherwise('/app/home');

    $stateProvider
	.state('main',{
		url:'/app',
		controller: 'main-ctrl',
		templateUrl:'./views/main.html',
		resolve: {
			authed: function($auth) {
				return $auth.isAuthenticated();
			},
			seasons: function(general) {
				return general.getGameSeasons();
			},
		},
	})
	.state('main.test',{
		url:'/test',
		controller: 'main.test-ctrl',
		templateUrl:'./views/main.test.html',
		authenticate: true,
		resolve: {
		  currentYear: function() {
			  return new Date().getFullYear().toString();
		  },
	 	  allYears: function(currentYear) {
			  var data = [];
			  for(var i = 2016; i<=currentYear; i++)
			  {
				  data.push(i);
			  }
			  return data;
		  },
		  currentEvents: function(currentYear, matches) {
			  return matches.getEventsByYear(currentYear);
		  }
		},
	})
	.state('main.home',{
		url:'/home',
		controller: 'main.home-ctrl',
		templateUrl:'./views/main.home.html',
		resolve: {
		},
	})
	.state('main.help',{
		url:'/help',
		controller: 'main.help-ctrl',
		templateUrl:'./views/main.help.html',
		resolve: {
		},
	})
	.state('main.about',{
		url:'/about',
		controller: 'main.about-ctrl',
		templateUrl:'./views/main.about.html',
		resolve: {
		},
	})
	.state('main.profile',{
		url:'/profile',
		controller: 'main.profile-ctrl',
		templateUrl:'./views/main.profile.html',
		authenticate: true,
		resolve: {
		}
	})
	.state('main.teamAdmin',{
		url:'/team/admin',
		controller: 'main.teamAdmin-ctrl',
		templateUrl:'./views/main.teamAdmin.html',
		authenticate: true,
		teamPrivs: 'admin',
		resolve: {
			teamInfo: function($auth, teams)
			{
				//var team_number = $auth.getPayload().data.team_info.team_number;
				return teams.getTeamInfoByNumber(0);
			},
			eventList: function($auth, events) {
				return events.getEventsForDataEntry();
			},
		}
	})
	.state('main.teams',{
		url:'/teams/:team_number?season',
		controller: 'main.teams-ctrl',
		templateUrl:'./views/main.teams.html',
		authenticate: true,
		resolve: {
		}
	})
	.state('main.season2016',{
		abstract: '.info',
		url:'/season/2016',
		controller: 'main.season2016-ctrl',
		templateUrl:'./views/seasons/main.season.html',
		resolve: {
			seasonData: function(general) {
				return general.getSeasonInfoByYear(2016);
			},
		},
	})
	.state('main.season2016.info',{
		url:'/info',
		controller: 'main.season2016.info-ctrl',
		templateUrl:'./views/seasons/main.season.info.html',
		resolve: {
		},
	})
	.state('main.season2016.matchDataEntry',{
		url:'/match/entry?event&match',
		controller: 'main.matchDataEntry-ctrl',
		templateUrl:'./views/seasons/main.season2016.matchEntry.html',
		authenticate: true,
		teamPrivs: 'write',
		resolve: {
			eventList: function($auth, events) {
				return events.getEventsForDataEntry(2016);
			},
		 	eventInit: function($stateParams, eventList) {
				var data = '';
				if($stateParams.event)
				{
					data = $stateParams.event;
				}
				else if(eventList.team_active != undefined)
				{
					data = eventList.team_active.event_key;
				}
				return data;
			},
			matchInit: function($stateParams, eventInit, matches) {
				var data = {'match_num':1};
				if($stateParams.match)
				{
					data = {'match_num':parseInt($stateParams.match)};
				}
				else if(eventInit != '')
				{
					data = matches.getUpcomingMatchByEvent(eventInit);
				}
				else
				{
					data = {'match_num':1};
				}
				return data;
			},
			matchInfo: function(matches, eventInit, matchInit) {
				return matches.getMatchByEventNumber(eventInit, matchInit.match_num);
			},
		},
	})
	.state('main.season2016.matchDataView',{
		url:'/match/view?event&match',
		controller: 'main.matchDataView-ctrl',
		templateUrl:'./views/seasons/main.season2016.matchDataView.html',
		authenticate: true,
		teamPrivs: 'read',
		resolve: {
			eventList: function($auth, events) {
				return events.getEventsForDataEntry(2016);
			},
		 	eventInit: function($stateParams, eventList) {
				var data = '';
				if($stateParams.event)
				{
					data = $stateParams.event;
				}
				else if(eventList.team_active != undefined)
				{
					data = eventList.team_active.event_key;
				}
				return data;
			},
			matchInit: function($stateParams, eventInit, matches) {
				var data = {'match_num':1};
				if($stateParams.match)
				{
					data = {'match_num':parseInt($stateParams.match)};
				}
				else if(eventInit != '')
				{
					data = matches.getUpcomingMatchByEvent(eventInit);
				}
				else
				{
					data = {'match_num':1};
				}
				return data;
			},
			matchStats: function(matches, eventInit, matchInit) {
				return matches.getMatchDataStats(eventInit, matchInit.match_num);
			},
		},
	})
	.state('main.season2016.robotDataEntry',{
		url:'/robot/entry/:team_number',
		controller: 'main.robotDataEntry-ctrl',
		templateUrl:'./views/seasons/main.season2016.robotDataEntry.html',
		authenticate: true,
		teamPrivs: 'write',
		resolve: {
			teamInfo: function(teams, $stateParams) {
				var team_number = $stateParams.team_number;
				if(team_number)
				{
					return teams.getTeamInfoByNumber(team_number);
				}
				else
				{
					return null;
				}
				//
			},
		},
	})
	.state('main.season2016.reportsGraphs',{
		url:'/reports/graphs?event&teams',
		controller: 'main.reportsGraphs-ctrl',
		templateUrl:'./views/seasons/main.season2016.reportsGraphs.html',
		authenticate: true,
		teamPrivs: 'read',
		resolve: {
			eventList: function($auth, events) {
				return events.getEventsForDataEntry(2016);
			},
		 	eventInit: function($stateParams, eventList) {
				var data = '';
				if($stateParams.event)
				{
					data = $stateParams.event;
				}
				else if(eventList.team_active != undefined)
				{
					data = eventList.team_active.event_key;
				}
				return data;
			},
			teamsInit: function($stateParams, teams) {
				if($stateParams.teams)
				{
					return teams.getMultipleTeamInfo($stateParams.teams);
				}
				else
				{
					return '';
				}
			},
		},
	})
	.state('main.season2017',{
		abstract: '.info',
		url:'/season/2017',
		controller: 'main.season2017-ctrl',
		templateUrl:'./views/seasons/main.season.html',
		resolve: {
			seasonData: function(general) {
				return general.getSeasonInfoByYear(2017);
			},
		},
	})
	.state('main.season2017.info',{
		url:'/info',
		controller: 'main.season2017.info-ctrl',
		templateUrl:'./views/seasons/main.season.info.html',
		resolve: {
		},
	})
	.state('main.season2017.matchDataEntry',{
		url:'/match/entry?event&match',
		controller: 'main.matchDataEntry-ctrl',
		templateUrl:'./views/seasons/main.season2017.matchEntry.html',
		authenticate: true,
		teamPrivs: 'write',
		resolve: {
			eventList: function($auth, events) {
				return events.getEventsForDataEntry(2017);
			},
		 	eventInit: function($stateParams, eventList) {
				var data = '';
				if($stateParams.event)
				{
					data = $stateParams.event;
				}
				else if(eventList.team_active != undefined)
				{
					data = eventList.team_active.event_key;
				}
				return data;
			},
			matchInit: function($stateParams, eventInit, matches) {
				var data = {'match_num':1};
				if($stateParams.match)
				{
					data = {'match_num':parseInt($stateParams.match)};
				}
				else if(eventInit != '')
				{
					data = matches.getUpcomingMatchByEvent(eventInit);
				}
				else
				{
					data = {'match_num':1};
				}
				return data;
			},
			matchInfo: function(matches, eventInit, matchInit) {
				return matches.getMatchByEventNumber(eventInit, matchInit.match_num);
			},
		},
	})
	.state('main.season2017.matchDataView',{
		url:'/match/view?event&match',
		controller: 'main.matchDataView-ctrl',
		templateUrl:'./views/seasons/main.season2017.matchDataView.html',
		authenticate: true,
		teamPrivs: 'read',
		resolve: {
			eventList: function($auth, events) {
				return events.getEventsForDataEntry(2017);
			},
		 	eventInit: function($stateParams, eventList) {
				var data = '';
				if($stateParams.event)
				{
					data = $stateParams.event;
				}
				else if(eventList.team_active != undefined)
				{
					data = eventList.team_active.event_key;
				}
				return data;
			},
			matchInit: function($stateParams, eventInit, matches) {
				var data = {'match_num':1};
				if($stateParams.match)
				{
					data = {'match_num':parseInt($stateParams.match)};
				}
				else if(eventInit != '')
				{
					data = matches.getUpcomingMatchByEvent(eventInit);
				}
				else
				{
					data = {'match_num':1};
				}
				return data;
			},
			matchStats: function(matches, eventInit, matchInit) {
				return matches.getMatchDataStats(eventInit, matchInit.match_num);
			},
		},
	})
	.state('main.season2017.robotDataEntry',{
		url:'/robot/entry/:team_number',
		controller: 'main.robotDataEntry-ctrl',
		templateUrl:'./views/seasons/main.season2017.robotDataEntry.html',
		authenticate: true,
		teamPrivs: 'write',
		resolve: {
			teamInfo: function(teams, $stateParams) {
				var team_number = $stateParams.team_number;
				if(team_number)
				{
					return teams.getTeamInfoByNumber(team_number);
				}
				else
				{
					return null;
				}
				//
			},
		},
	})
	.state('main.season2017.reportsGraphs',{
		url:'/reports/graphs?event&teams',
		controller: 'main.reportsGraphs-ctrl',
		templateUrl:'./views/seasons/main.season2017.reportsGraphs.html',
		authenticate: true,
		teamPrivs: 'read',
		resolve: {
			eventList: function($auth, events) {
				return events.getEventsForDataEntry(2017);
			},
		 	eventInit: function($stateParams, eventList) {
				var data = '';
				if($stateParams.event)
				{
					data = $stateParams.event;
				}
				else if(eventList.team_active != undefined)
				{
					data = eventList.team_active.event_key;
				}
				return data;
			},
			teamsInit: function($stateParams, teams) {
				if($stateParams.teams)
				{
					return teams.getMultipleTeamInfo($stateParams.teams);
				}
				else
				{
					return '';
				}
			},
		},
	})
	.state('main.season2017.reportsTable',{
		url:'/reports/table?event&teams',
		controller: 'main.reportsTable-ctrl',
		templateUrl:'./views/seasons/main.season2017.reportsTable.html',
		authenticate: true,
		teamPrivs: 'read',
		resolve: {
			eventList: function($auth, events) {
				return events.getEventsForDataEntry(2017);
			},
		 	eventInit: function($stateParams, eventList) {
				var data = '';
				if($stateParams.event)
				{
					data = $stateParams.event;
				}
				else if(eventList.team_active != undefined)
				{
					data = eventList.team_active.event_key;
				}
				return data;
			},
			teamsInit: function($stateParams, teams) {
				if($stateParams.teams)
				{
					return teams.getMultipleTeamInfo($stateParams.teams);
				}
				else
				{
					return '';
				}
			},
		},
	});
/* 	.state('main.adminTeamAdmin',{
		url:'/team/admin/:team',
		controller: 'main.teamAdmin-ctrl',
		templateUrl:'./views/main.teamAdmin.html',
		resolve: {
			loadMyFiles:function($ocLazyLoad) {
				return $ocLazyLoad.load({
					name:'frcScout',
					files:[
						'/js/controllers/main.teamAdmin.js',
					]
				})
			},
			authed: function(auth) {
				return auth.isAuthed();
			},
			teamNumber = function($stateParams)
			{
				return $stateParams.team;
			}
		}
	}); */
}])
.config(function($httpProvider) {
	$httpProvider.interceptors.push(function authInterceptor($q, $injector, auth) {
		return {
			// automatically attach Authorization header
			/* request: function(config) {
				var token = auth.getToken();
				if(token) {
					config.headers.Authorization = 'Bearer ' + token;
				}
				return config;
			}, */
			// If a token was sent back, save it
			response: function(res) {
				if(res.data.token) {
					auth.saveToken(res.data.token);
				}
				return res;
			},
			responseError: function(rejection) {
				if (rejection.status === 401) {
					// Return a new promise
					var $uibModal = $injector.get('$uibModal');
					var $auth = $injector.get('$auth');
					var $rootScope = $injector.get('$rootScope');
					//var $scope = $injector.get('$scope');
					
					var openLoginModal = function () {
						var modalInstance = $uibModal.open({
							animation: true,
							templateUrl: './views/modals/loginModal.html',
							controller: 'loginModal-ctrl',
							resolve: {
								'title':function () {
								  return 'Authentication issue. Please re-authenticate.';
								},
							}
						});
						modalInstance.result.then(function (data) {
								if(data.auth)
								{
									$rootScope.$broadcast('afterLoginAction');
									return $injector.get('$http')(rejection.config);
								}
							}, function () {
								//$log.info('Modal dismissed at: ' + new Date());
						});
					};
					openLoginModal();
				}
				else if (rejection.status === 500) {
					// Return a new promise
					var $uibModal = $injector.get('$uibModal');
					var $auth = $injector.get('$auth');
					var $rootScope = $injector.get('$rootScope');
					//var $scope = $injector.get('$scope');
					
					var openLoginModal = function () {
						var modalInstance = $uibModal.open({
							animation: true,
							templateUrl: './views/modals/500ErrorModal.html',
							controller: '500ErrorModal-ctrl',
							resolve: {
								'data':rejection.data
							}
						});
						modalInstance.result.then(function (data) {
							}, function () {
								//$log.info('Modal dismissed at: ' + new Date());
						});
					};
					openLoginModal();
				}
				/* If not a 401, do nothing with this error.
				* This is necessary to make a `responseError`
				* interceptor a no-op. */
				return $q.reject(rejection);
			}
		}
	});
})
/* .config(function($provide) {
	$provide.decorator('ColorPickerOptions', function($delegate) {
		var options = angular.copy($delegate);
		options.round = true;
		options.alpha = false;
		options.format = 'hex';
		return options;
	});
}) */
.run(function ($rootScope, $state, $auth, $uibModal, $log, teamInfoSrv) {
  $rootScope.$on("$stateChangeStart", function(event, toState, toParams, fromState, fromParams){
	/* if(!$auth.isAuthenticated())
	{
		event.preventDefault(); 
		$rootScope.$broadcast('checkAuth');
		$state.go(toState.name, toParams);
	} */
    if (toState.authenticate && !$auth.isAuthenticated()){
		event.preventDefault(); 
		//alert('Need logged in');
		//alert(JSON.stringify(fromState, null, 4));
		var modalInstance = $uibModal.open({
			animation: true,
			templateUrl: './views/modals/loginModal.html',
			controller: 'loginModal-ctrl',
			resolve: {
				'title':function () {
				  return 'Authentication Required to Access this Resource';
				},
			}
		});
		modalInstance.result.then(function (data) {
				if(data.auth)
				{
					$rootScope.$broadcast('afterLoginAction');
					$log.info('Logged in');
					$state.go(toState.name, toParams);
				}
				else if(fromState.name == '')
				{
					$state.go('main.home');
				}
			}, function () {
				$log.info('Modal dismissed at: ' + new Date());
				$log.error('Authentication Required');
				if(fromState.name == '')
				{
					$state.go('main.home');
				}
		});
    }
	else if (toState.teamPrivs){
		var noTeam = teamInfoSrv.getTeamInfo() == null || teamInfoSrv.getTeamInfo().status != 'joined';
		var admin = toState.teamPrivs=='admin' && !noTeam && teamInfoSrv.getTeamInfo().privs !='admin';
		var write = toState.teamPrivs=='write' && !noTeam && teamInfoSrv.getTeamInfo().privs !='write' && teamInfoSrv.getTeamInfo().privs !='admin';
		var read = toState.teamPrivs=='read' && !noTeam && teamInfoSrv.getTeamInfo().privs !='read' && teamInfoSrv.getTeamInfo().privs !='write' && teamInfoSrv.getTeamInfo().privs !='admin';
		if(noTeam || admin || write || read)
		{
			event.preventDefault(); 
			if(noTeam)
			{
				alert('You must have confirmed team membership to access this resource.');
			}
			else if(admin || write || read)
			{
				alert('You are not authrozied to access this resource.');
			}
			if(fromState.name == '')
			{
				$state.go('main.home');
			}
		}
    }
  });
})
.config(function(toastrConfig) {
  angular.extend(toastrConfig, {
    autoDismiss: true,
    containerId: 'toast-container',
    maxOpened: 6,    
    newestOnTop: true,
    positionClass: 'toast-top-right',
    preventDuplicates: false,
    preventOpenDuplicates: false,
    target: 'body'
  });
})
.config(function(toastrConfig) {
  angular.extend(toastrConfig, {
    timeOut: 5000,
  });
})
.config(function($authProvider) {
	$authProvider.google({
		clientId: '104136500318-ovfi8uh672h6cvq0tcnin6f65iq0nab8.apps.googleusercontent.com',
	//	url: '/site/auth_google.php',
		url: '/api/v1/login/google',
		authorizationEndpoint: 'https://accounts.google.com/o/oauth2/auth',
		redirectUri: window.location.origin,
		requiredUrlParams: ['scope'],
		optionalUrlParams: ['display'],
		scope: ['profile', 'email'],
		scopePrefix: 'openid',
		scopeDelimiter: ' ',
		display: 'popup',
		type: '2.0',
		popupOptions: { width: 452, height: 633 }
	});
	$authProvider.facebook({
		clientId: '157827901294347',
		name: 'facebook',
	//	url: '/site/auth_facebook.php',
		url: '/api/v1/login/facebook',
		authorizationEndpoint: 'https://www.facebook.com/v2.5/dialog/oauth',
		redirectUri: window.location.origin+'/',
		requiredUrlParams: ['display', 'scope'],
		scope: ['email'],
		scopeDelimiter: ',',
		display: 'popup',
		type: '2.0',
		popupOptions: { width: 580, height: 400 }
	});
	$authProvider.live({
	//	url: '/site/auth_live.php',
		url: '/api/v1/login/live',
		clientId: '9324cca8-b26c-463c-8714-abdd0fff5f2d',
		authorizationEndpoint: 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
		redirectUri: window.location.origin,
		requiredUrlParams: ['scope', 'response_mode', 'nonce'],
		scope: ['openid','email','profile'],
		scopeDelimiter: ' ',
		//display: 'popup',
		responseType: 'id_token+code',
		responseMode: 'fragment',
		nonce: '678910',
		type: '2.0',
		popupOptions: { width: 500, height: 560 }
	});
	$authProvider.linkedin({
		url: '/site/auth_linkedin.php',
		clientId: '778o827lbrsltx',
		authorizationEndpoint: 'https://www.linkedin.com/uas/oauth2/authorization',
		redirectUri: window.location.origin,
		requiredUrlParams: ['state'],
		scope: ['r_emailaddress', 'r_basicprofile'],
		scopeDelimiter: ' ',
		state: 'STATE',
		type: '2.0',
		popupOptions: { width: 527, height: 582 }
	});
	$authProvider.yahoo({
		url: '/site/auth_yahoo.php',
		authorizationEndpoint: 'https://api.login.yahoo.com/oauth2/request_auth',
		redirectUri: window.location.origin,
		scope: [],
		scopeDelimiter: ',',
		type: '2.0',
		popupOptions: { width: 559, height: 519 }
	});

	
	
	$authProvider.httpInterceptor = function() { return true; },
	$authProvider.withCredentials = true;
	$authProvider.tokenRoot = null;
	$authProvider.baseUrl = '/';
	$authProvider.loginUrl = '/site/auth/login';
	$authProvider.signupUrl = '/site/auth/signup';
	$authProvider.unlinkUrl = '/site/auth_unlink.php';
	$authProvider.tokenName = 'token';
	$authProvider.tokenPrefix = 'satellizer';
	$authProvider.authHeader = 'Authorization';
	$authProvider.authToken = 'Bearer';
	$authProvider.storageType = 'localStorage';
});