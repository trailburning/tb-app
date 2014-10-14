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
    this.appView = new AppView({ el: '#appview' });
	this.searchView = new SearchView({ el: '#searchview' });        
  };
    
  return { 
    initialize: initialize
  };   
});  
