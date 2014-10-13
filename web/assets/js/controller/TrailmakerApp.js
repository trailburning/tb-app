var app = app || {};

var nTrail = 0;

define([
  'underscore', 
  'modernizr',
  'backbone',
  'models/TrailModel',
  'views/TrailmakerView',
  'views/SearchView'    
], function(_, Modernizr, Backbone, TrailModel, AppView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    this.trailModel = new TrailModel();
                        
    this.appView = new AppView({ el: '#appview', model: this.trailModel });
	this.searchView = new SearchView({ el: '#searchview' });
  };
    
  return { 
    initialize: initialize
  };   
});  
