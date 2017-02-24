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
.module('frcScout')
.controller('main-ctrl',function($rootScope, $scope, $state, $location, $window, general, $auth, $uibModal, $log, toastr, authed, users, seasons, teams, teamInfoSrv) {
	$scope.isCollapsed = true;
	$scope.$on('$stateChangeStart', function () {
		$scope.isCollapsed = true;
		/* if(!$auth.isAuthenticated())
		{
			$scope.logout();
		} */
	});
	
	$scope.headerSeasonsMenu = seasons;
	$scope.globalInfo = {
		'userInfo':{},
		'teamInfo':{},
		'searchAllTeams':'',
		'serverTime': 0,
	}
	
	$scope.searchTeams = function($select) {
		teams.searchTeams($select.search).then(function(response){ 
			$scope.searchTeamRes = response;
		});
	}
	
	$scope.goToTeamPage = function($select) {
		$state.go('main.teams',{'team_number':$scope.globalInfo.searchAllTeams.team_number});
	}
	
	//Chat

	$scope.chatVariables = {
		expandOnNew: false,
		minimize: true,
		hide: false,
		writingMessage: '',
		messages: [],
		sending: false,
		focus: true,
		glued: false,
		lastId: '',
		loadingMore: false,
		noMoreMessages: false,
		newChatCount: 0,
		statusClass: {
			'connecting':$scope.chatWS && $scope.chatWS.readyState == 0, 
			'open':$scope.chatWS && $scope.chatWS.readyState == 1, 
			'closed':$scope.chatWS && $scope.chatWS.readyState == 3, 
		}
	}
	
	//Start Chat
	$scope.startChat = function()
	{
		if($scope.chatWS)
		{
			$scope.chatWS.close();
		}
		if($scope.globalInfo.teamInfo != null && $scope.globalInfo.teamInfo.status=='joined')
		{	
			$scope.chatWS = new WebSocket('wss://ws.frcscout.resnick-tech.com:443/ws/chat?token='+$auth.getToken());
			$scope.chatWS.onopen = function()
			{
				// Web Socket is connected, send data using send()
				//$scope.chatWS.send("Message to send");
				console.log("Chat Web Soccket Connection is open..."); 
			}
			$scope.chatWS.onmessage = function (e) 
			{ 
				$scope.$apply(function () {
					var messageData = JSON.parse(e.data);
					if(messageData.initial) {
						$scope.chatVariables.messages = messageData.messages;		
						console.log("Initial Chats");						
					}
					else {
						$scope.chatVariables.glued = true;
						$scope.chatVariables.messages.push(messageData);
						if($scope.chatVariables.minimize == true && messageData.type=='message')
						{
							$scope.chatVariables.newChatCount = $scope.chatVariables.newChatCount + 1;
						}
					}
					
				});
			};

			$scope.chatWS.onclose = function()
			{ 
				//$scope.startChat();
				// websocket is closed.
				console.log("Chat Web Soccket Connection is closed..."); 
			};
		}
		
	}
	
	$scope.chatVariables.sendMessage = function() {
		if($scope.chatVariables.writingMessage && $scope.chatVariables.writingMessage !== '') {
			$scope.chatVariables.glued = true;
			$scope.chatWS.send($scope.chatVariables.writingMessage);
			$scope.chatVariables.writingMessage = '';
		}
		$scope.$broadcast('chatVariables.isOpen');
	};
	
	//Start Server Time
	$scope.startServerTime = function() {
		if($scope.serverTimeWS) {
			$scope.serverTimeWS.close();
		}	
		$scope.serverTimeWS = new WebSocket('wss://ws.frcscout.resnick-tech.com:443/ws/time');
		$scope.serverTimeWS.onopen = function(){
			// Web Socket is connected, send data using send()
			console.log("Server Time Web Soccket Connection is open..."); 
		}
		$scope.serverTimeWS.onmessage = function (e) { 
			$scope.$apply(function () {
				var messageData = JSON.parse(e.data);
				$scope.globalInfo.serverTime = messageData.server_time;		
				$rootScope.$broadcast('serverTimeUpdate');
			});
		};

		$scope.serverTimeWS.onclose = function() { 
			// websocket is closed.
			console.log("Chat Web Soccket Connection is closed..."); 
		};
	}
	//$scope.startServerTime();
	
	$scope.minimizeChatBox = function()
	{
		if($scope.chatVariables.minimize == true)
		{
			$scope.chatVariables.minimize = false;
			$scope.chatVariables.newChatCount = 0;
		}
		else if($scope.chatVariables.minimize == false)
		{
			$scope.chatVariables.minimize = true;
			$scope.chatVariables.newChatCount = 0;
		}
	}

	$scope.initServiceWorkerState = function() {  
		console.log('Initializing');
		// Are Notifications supported in the service worker?  
		if (!('showNotification' in ServiceWorkerRegistration.prototype)) {  
			console.warn('Notifications aren\'t supported.');  
			return false;
		}

		// Check the current Notification permission.  
		// If its denied, it's a permanent block until the  
		// user changes the permission  
		if (Notification.permission === 'denied') {  
			console.warn('The user has blocked notifications.');  
			return false;  
		}

		// Check if push messaging is supported  
		if (!('PushManager' in window)) {  
			console.warn('Push messaging isn\'t supported.');  
			return false; 
		}

		// We need the service worker registration to check for a subscription  
		navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) { 
			console.log('Service Worker Ready');
			// Do we already have a push message subscription?  
			serviceWorkerRegistration.pushManager.getSubscription()  
			.then(function(subscription) {  
				console.log('Checkig Subscription');
				// Enable any UI which subscribes / unsubscribes from  
				// push messages.  
			//	var pushButton = document.querySelector('.js-push-button');  
			//	pushButton.disabled = false;

				if (!subscription) {  
					console.log('Not Scubscribed');
					// We aren't subscribed to push, so set UI  
					// to allow the user to enable push  
					return false;  
				}
				//console.log(subscription);
				// Keep your server in sync with the latest subscriptionId
				// sendSubscriptionToServer(subscription);
				var rawKey = subscription.getKey ? subscription.getKey('p256dh') : '';
				var key = rawKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawKey))) : '';
				var rawAuthSecret = subscription.getKey ? subscription.getKey('auth') : '';
				var authSecret = rawAuthSecret ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawAuthSecret))) : '';
				var endpoint = subscription.endpoint;
				var data = {'endpoint':endpoint, 'key':key, 'authSecret':authSecret};
				users.deviceNotificationUpdateEndpoint(data).then(function(response){
					//toastr[response.type](response.msg, 'Notifications');
					//$scope.saving.personal = false;
					console.log('Endpoint Updated');
				});
				console.log(data);
				return true;
			})  
			.catch(function(err) {  
				console.warn('Error during getSubscription()', err);  
			});  
		});  
	}



	$scope.checkServiceWorker = function()
	{
		console.log('Check Service Worker');
		if ('serviceWorker' in navigator) {  
			navigator.serviceWorker.register('/sw.js').then($scope.initServiceWorkerState);  
		} else {  
			console.warn('Service workers aren\'t supported in this browser.');  
		}
	}
	
	var afterLoginAction = function()
	{
		$scope.isAuthed = $auth.isAuthenticated();
		$scope.userInfo = $auth.getPayload().data;
		$scope.globalInfo.userInfo = $auth.getPayload().data;
		$scope.globalInfo.teamInfo = teamInfoSrv.getTeamInfo();
		$scope.startChat();
		$scope.checkServiceWorker();
	//	alert(JSON.stringify(teamInfoSrv.getTeamInfo(), null, 4));
	}
	
	var logOutAction = function()
	{
		$scope.isAuthed = false;
		$scope.userInfo = null;
		$scope.globalInfo.userInfo = null;
		$scope.globalInfo.teamInfo = null;
		teamInfoSrv.deleteTeamInfo();
		$scope.chatWS.close();
		$scope.chatVariables.messages = [];
	}
	
	$scope.updateTeamInfo = function(teamInfo)
	{
		if(teamInfo)
		{
			teamInfoSrv.saveTeamInfo(teamInfo);
		}
		$scope.globalInfo.teamInfo = teamInfoSrv.getTeamInfo();
	}
	$scope.masterBreadcrumbs = [];
	var path = window.location.pathname;
	var host = window.location.hostname;
	var host1 = host.split('.');
	$scope.isAuthed = authed;
	
	
	
	
	
	if($scope.isAuthed)
	{
		afterLoginAction();
	}

	$scope.logout = function()
	{
		$rootScope.$broadcast('logOutAction');
		//logOutAction();
		if($state.current.authenticate == true)
		{
			$state.go('main.home');
		}
		$auth.logout();
	}
	
	
	$scope.openLoginModal = function () {
		var modalInstance = $uibModal.open({
			animation: true,
			templateUrl: './views/modals/loginModal.html',
			controller: 'loginModal-ctrl',
			resolve: {
				title:function () {
				  return '';
				},
			}
		});
		$scope.isCollapsed = true;
		modalInstance.result.then(function (data) {
				$scope.isAuthed = data.auth;
				if(data.auth)
				{
					$rootScope.$broadcast('afterLoginAction');
					//afterLoginAction();
					/* $scope.userInfo = $auth.getPayload().data;
					$scope.chatVariables.expandOnNew = true;
				//	$scope.chatVariables.minimize = true;
					$scope.startChat(); */
				}
				
			}, function () {
				$log.info('Modal dismissed at: ' + new Date());
		});
	};
		
	$rootScope.$on('afterLoginAction', function(msg, data) {
		console.info('Login Initiated');
		afterLoginAction();
	});
	$rootScope.$on('logOutAction', function(msg, data) {
		console.info('LogOut Initiated');
		logOutAction();
	});
	$rootScope.$on('checkAuth', function(msg, data) {
		console.info('LogOut Initiated');
		if($scope.isAuthed && !$auth.isAuthenticated())
		{
			logOutAction();
		}
	});
	
	/* $scope.$on('$viewContentLoaded', function() {  
		console.log('loaded');
		 
	}); */
})
.controller('loginModal-ctrl', function($scope,$log, $sce, $state, $timeout, $auth, $uibModalInstance, toastr, teamInfoSrv, title) {
	var nextState = 'main.home';
	$scope.loginForm = {};
	$scope.title = title;
	$scope.authenticate = function(provider) {
		$auth.authenticate(provider).then(function(response){ 
			toastr[response.data.type](response.data.msg, 'Login');

			var authed = $auth.isAuthenticated();
			if(authed)
			{ 
			//	alert(JSON.stringify(response.teamInfo, null, 4));
				teamInfoSrv.saveTeamInfo(response.data.teamInfo);
				var data = {
					'auth': true,
				}
				$uibModalInstance.close(data);
			}
		});
    };
	
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
})
.controller('500ErrorModal-ctrl', function($scope,$log, $sce, $state, $timeout, $auth, $uibModalInstance, toastr, teamInfoSrv, data) {
	$scope.data = data;

	
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});