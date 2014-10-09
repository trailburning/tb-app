var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/TrailsView',  
  'views/SearchView'    
], function(_, Modernizr, Backbone, AppView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
    
  var initialize = function() {
    var self = this;
              
    this.appView = new AppView({ });
    this.appView.getResults();
	this.searchView = new SearchView({ el: '#searchview' });
    
  	$('#footerview').show();  	
  };
    
  return { 
    initialize: initialize
  };   
});  
