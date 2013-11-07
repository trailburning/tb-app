require.config({
  paths: {
    inherit: 'libs/jquery.inherit-1.3.2',
    modernizr: 'libs/modernizr.custom.68191',
    underscore: 'libs/underscore-min',
    backbone: 'libs/backbone-min'
  }
});

require([
  // Load our app module and pass it to our definition function
  'app',
], function(App){
  App.initialize();
});
