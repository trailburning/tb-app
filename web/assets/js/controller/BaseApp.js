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

    $('#search_field').focus(function(evt) {
      $('#search_field').val('not just yet...');
      event.preventDefault();
    });

    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }
    
    $('#footerview').show();
       
  };
    
  return { 
    initialize: initialize
  };   
});  
