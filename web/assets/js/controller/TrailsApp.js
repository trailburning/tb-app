var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/TrailsView'  
], function(_, Modernizr, Backbone, TrailsView){
  app.dispatcher = _.clone(Backbone.Events);
    
  var initialize = function() {
    var self = this;
              
    this.trailsView = new TrailsView({ });
    this.trailsView.getResults();
    
  	$('#footerview').show();  	
  };
    
  return { 
    initialize: initialize
  };   
});  
