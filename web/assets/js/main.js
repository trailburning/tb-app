if (typeof TB_APP != 'undefined') {
	require.config({
		paths: {
			inherit: 'libs/jquery.inherit-1.3.2',
			modernizr: 'libs/modernizr.custom.15357',
			underscore: 'libs/underscore-min',
			backbone: 'libs/backbone-min'
		}
	});
	require([
		// Load our app module and pass it to our definition function
		TB_APP,
	], function(App){
		App.initialize();
	});
}
