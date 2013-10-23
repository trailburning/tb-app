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
      var bRet = false;
          
      var strEmail = $('#form_email').val();
      var atpos = strEmail.indexOf("@");
      var dotpos = strEmail.lastIndexOf(".");
      if (atpos<1 || dotpos<atpos+2 || dotpos+2>=strEmail.length) {
        $('#signup_form .success').hide();
        $('#signup_form .fail').show();
      }
      else {
        $.post("http://www.trailburning.com/server/mailerproxy.php", $('#signup_form').serialize()).success(function(data) {});
        $('#signup_form .fail').hide();
        $('#signup_form .success').show();
      }
      return false;
    }    
  };
    
  return { 
    initialize: initialize
  };   
});  
