require.config({
	paths: {
		modernizr: 'libs/modernizr.custom.15357',
		underscore: 'libs/underscore-min',
		backbone: 'libs/backbone-min',
        async: 'libs/async'
	}
});
require(['controller/MapApp'], function(App){
	App.initialize();
})
define('gmaps', ['async!https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places'], function() {
    return google.maps;
});
