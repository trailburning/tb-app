var app = app || {};

define([
  'underscore', 
  'libs/modernizr.custom.68191',
  'backbone',
  'views/TourView',  
  'views/SearchView'    
], function(_, Modernizr, Backbone, AppView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
    
  var initialize = function() {
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

    var self = this;
              
    this.appView = new AppView({ });
	this.searchView = new SearchView({ el: '#searchview' });
    
  	$('#footerview').show();  	
  };
    
  return { 
    initialize: initialize
  };   
});  
