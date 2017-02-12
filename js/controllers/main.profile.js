'use strict';
/**
 * @ngdoc function
 * @name frcScout.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the frcScout
 */
angular.module('frcScout')
.controller('main.profile-ctrl', function($scope,$log, $sce, $state, toastr, teams, users, teamInfoSrv, $auth) {
	
	$scope.saving = {
		personal: false,
		service: false,
		location: false,
	};
	
	$scope.personalInfo = {
		'fname':$scope.globalInfo.userInfo.fname,
		'lname':$scope.globalInfo.userInfo.lname,
		'email':$scope.globalInfo.userInfo.email,
		'phone':$scope.globalInfo.userInfo.phone,
	}
	
	$scope.notificationPreferences = $scope.globalInfo.userInfo.notification_preferences;
	
	$scope.updatePersonalInfo = function()
	{
		$scope.saving.personal = true;
		var data = {'id':$scope.userInfo.id, 'data':$scope.personalInfo};
		users.updateUserPersonalInfoById(data).then(function(response){
			toastr[response.type](response.msg, 'Personal Information');
			$scope.saving.personal = false;
			$scope.globalInfo.userInfo = $auth.getPayload().data;

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
		//	$scope.activeTeamAccount = response.active;
			toastr[response.type](response.msg, 'Team Join');
			if(response.status)
			{
				$scope.updateTeamInfo(response.teamInfo);
			}
			
		});
	}

	$scope.registerTeam = function()
	{
		var data = {'user_id':$auth.getPayload().data.id, 'team_number':$scope.selectTeam.team.team_number};
		teams.registerTeam(data).then(function(response){ 
		//	$scope.activeTeamAccount = response.active;
			toastr[response.type](response.msg, 'Team Register');
			if(response.status)
			{
				$scope.updateTeamInfo(response.teamInfo);
			}
			
		});
	}
	
	
	
	$scope.enablePush = {
		status:false,
		disabled:true,
		subscription: null,
		endpoint: null,
	};

	var initServiceWorkerState = function() {  
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
					$scope.$apply( function () {
						$scope.enablePush.status = false; 
						$scope.enablePush.disabled = false;
					});
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
				console.log(data);

				$scope.$apply( function () {
					$scope.enablePush.subscription = subscription;
					$scope.enablePush.status = true; 
					$scope.enablePush.disabled = false;
					$scope.enablePush.endpoint = endpoint;
				});
				return true;
			})  
			.catch(function(err) {  
				console.warn('Error during getSubscription()', err);  
			});  
		});  
	}
	initServiceWorkerState();

	$scope.subscribePush = function() {  
	  // Disable the button so it can't be changed while  
	  // we process the permission request   
	  $scope.enablePush.disabled = true;

	  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {  
		serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true})  
		  .then(function(subscription) {  
			// The subscription was successful 
			
			// TODO: Send the subscription.endpoint to your server  
			// and save it to send a push message at a later date
		  
	//	  return sendSubscriptionToServer(subscription);  
			var rawKey = subscription.getKey ? subscription.getKey('p256dh') : '';
			var key = rawKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawKey))) : '';
			var rawAuthSecret = subscription.getKey ? subscription.getKey('auth') : '';
			var authSecret = rawAuthSecret ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawAuthSecret))) : '';
			var endpoint = subscription.endpoint;
			var data = {'endpoint':endpoint, 'key':key, 'authSecret':authSecret};
			users.deviceNotificationSubscribe(data).then(function(response){
				toastr[response.type](response.msg, 'Notifications');
				$scope.saving.personal = false;
			});
			console.log(data);
			$scope.$apply( function () {
				$scope.enablePush.subscription = subscription;
				$scope.enablePush.status = true; 
				$scope.enablePush.disabled = false;
				$scope.enablePush.endpoint = endpoint;
			});
		  })  
		  .catch(function(e) {  
			if (Notification.permission === 'denied') {  
			  // The user denied the notification permission which  
			  // means we failed to subscribe and the user will need  
			  // to manually change the notification permission to  
			  // subscribe to push messages  
			  console.warn('Permission for Notifications was denied');  
			  $scope.enablePush.disabled = true;  
			} else {  
			  // A problem occurred with the subscription; common reasons  
			  // include network errors, and lacking gcm_sender_id and/or  
			  // gcm_user_visible_only in the manifest.  
			  console.error('Unable to subscribe to push.', e);  
			  $scope.enablePush.disabled = false;
			}  
		  });  
	  });  
	}

	$scope.unsubscribePush = function() {
		$scope.enablePush.disabled = true;
		if($scope.enablePush.status && $scope.enablePush.subscription)
		{
			$scope.enablePush.subscription.unsubscribe().then(function(event) {
				console.log('Unsubscribed!', event);
				var data = {'endpoint':$scope.enablePush.endpoint};
				users.deviceNotificationUnsubscribe(data).then(function(response){
					toastr[response.type](response.msg, 'Notifications');
					$scope.saving.personal = false;
				});
				$scope.$apply( function () {
					$scope.enablePush.status = false; 
					$scope.enablePush.disabled = false;
				});
			}).catch(function(error) {
				console.log('Error unsubscribing', error);
			});
		}
	}
});
