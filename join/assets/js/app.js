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
    
    $('#signup_form').submit(function (evt) {
      signup();
      return false; 
    });

    $('#submit').click(function (evt) {
      return false;
    });
    
    // register for image ready      
    $('.tb-fade img', this.el).load(function() {
      $(this).parent().css({ opacity: 1 });
    });
    // force ie to run the load function if the image is cached
    if ($('.tb-fade img', this.el).get(0).complete) {
      $('.tb-fade img', this.el).trigger('load');
    }
        
    function handleResize() {
      $('.image').resizeToParent();      
    }
    
    function signup() {

    }    
  };
    
  return { 
    initialize: initialize
  };   
});  
