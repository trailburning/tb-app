var app = app || {};

define([
  'underscore',
  'modernizr',
  'backbone',
  'models/TrailModel',
  'views/AppView'
], function(_, Modernizr, Backbone, TrailModel, AppView){
  app.dispatcher = _.clone(Backbone.Events);
	
  var initialize = function() {
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

    this.trailModel = new TrailModel();

    this.appView = new AppView({ model: this.trailModel });            
  };
    
  return { 
    initialize: initialize
  };   
});  
