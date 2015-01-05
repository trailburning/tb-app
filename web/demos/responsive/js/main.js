require.config({
  paths: {
	modernizr: 'libs/modernizr.custom.76241',  	
    underscore: 'libs/underscore-min',
    backbone: 'libs/backbone-min'
  }
});
require(['controller/app'], function(App){
  App.initialize();
});
