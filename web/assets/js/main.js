if (typeof TB_APP != 'undefined') {
    require.config({
    	paths: {
    		inherit: 'libs/jquery.inherit-1.3.2',
    		modernizr: 'libs/modernizr.custom.76241',
    		underscore: 'libs/underscore-min',
    		backbone: 'libs/backbone-min',
            async: 'libs/async',
            royalslider: 'libs/jquery.royalslider.custom.min'
    	}
    });
    // Load our app module and pass it to our definition function
    require(['controller/' + TB_APP], function(App){
    	App.initialize();
    })

    define('gmaps', ['async!https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places'], function() {
        return google.maps;
    });
}
