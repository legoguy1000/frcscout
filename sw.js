var CACHE_NAME = 'my-site-cache-v1';
var urlsToCache = [
  '/',
  '/css/main.css',
  '/js/app.js',
  '/js/controllers/main.js',
  '/js/controllers/main.home.js',
  '/views/main.html',
  '/views/main.home.html',
];



self.addEventListener('install', function(event) {
  // Perform install steps
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('activate', function(event) {

  var cacheWhitelist = ['my-site-cache-v1'];

  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('push', function(event) {  
  console.log('Received a push message', event);

	var title = 'Yay a message.';  
	var body = 'We have received a push message.';  
	var icon = '/favicons/android-chrome-144x144.png?v=wAvOLR9ye8';  
	var tag = 'simple-push-demo-notification-tag';
	var data =  {'title':title, 'body':body, 'tag':tag};
	if(event.data != undefined)
	{
		data = event.data.json();
	}
	console.log(data);
	
  event.waitUntil(  
    self.registration.showNotification(data.title, {  
      body: data.body,  
      icon: icon, 
	  tag: data.tag
    })  
  );  
});

self.addEventListener('notificationclick', function(event) {
    console.log('Notification click: tag ', event.notification.tag);
    event.notification.close();
    var url = 'https://www.frcscout.resnick-tech.com';
    event.waitUntil(
        clients.matchAll({includeUncontrolled: true, type: 'window'})
        .then(function(windowClients) {
			console.log(windowClients.length);
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
				console.log(client.url);
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
					console.log('focus');
                }
            }
            if (clients.openWindow) {
				console.log('new window');
                return clients.openWindow(url);
            }
        })
    );
});