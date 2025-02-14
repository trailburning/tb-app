var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/MapView',
  'views/SearchView'
], function(_, Modernizr, Backbone, AppView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

    this.appView = new AppView({ el: '#appview' });
	this.searchView = new SearchView({ el: '#searchview' });        
  };
    
  return { 
    initialize: initialize
  };   
});  
