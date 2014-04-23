var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/EventsView'  
], function(_, Modernizr, Backbone, AppView){
  app.dispatcher = _.clone(Backbone.Events);
    
  var initialize = function() {
    var self = this;
              
    this.appView = new AppView({ });
    this.appView.getResults();
    
  	$('#footerview').show();  	
  };
    
  return { 
    initialize: initialize
  };   
});  
