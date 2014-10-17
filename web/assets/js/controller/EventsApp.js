var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/EventsView',
  'views/SearchView'  
], function(_, Modernizr, Backbone, AppView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
    
  var initialize = function() {
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

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
