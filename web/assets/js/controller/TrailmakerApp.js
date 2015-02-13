var app = app || {};

var nTrail = 0;

define([
  'underscore', 
  'modernizr',
  'backbone',
  'models/TrailModel',
  'views/trailmaker/TrailmakerView',
  'views/SearchView'    
], function(_, Modernizr, Backbone, TrailModel, AppView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';
  	
    this.trailModel = new TrailModel();
                        
    this.appView = new AppView({ el: '#appview', model: this.trailModel });
	this.searchView = new SearchView({ el: '#searchview' });
  };
    
  return { 
    initialize: initialize
  };   
});  
