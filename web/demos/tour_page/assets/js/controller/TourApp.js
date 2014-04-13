var app = app || {};

define([
  'underscore', 
  'libs/modernizr.custom.68191',
  'backbone',
  'views/TourView'  
], function(_, Modernizr, Backbone, AppView){
  app.dispatcher = _.clone(Backbone.Events);
    
  var initialize = function() {
    var self = this;
              
    this.appView = new AppView({ });
    
  	$('#footerview').show();  	
  };
    
  return { 
    initialize: initialize
  };   
});  
