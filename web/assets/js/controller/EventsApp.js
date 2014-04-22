var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone'
], function(_, Modernizr, Backbone){
  app.dispatcher = _.clone(Backbone.Events);
    
  var initialize = function() {
    var self = this;        
    
  	$('#footerview').show();  	
  };
    
  return { 
    initialize: initialize
  };   
});  
