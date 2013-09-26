var app = app || {};

define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    function handleResize() {
      $('.image').resizeToParent();      
    }
  };
    
  return { 
    initialize: initialize
  };   
});  
