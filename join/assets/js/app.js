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
    $('#search_form').submit(function(evt) {
      $('#search_field').val('not just yet...');
      event.preventDefault();
    });    

    $('#signup_form').submit(function (evt) {
      signup();
      return false; 
    });

    var imgLoad = imagesLoaded('.scale');
    imgLoad.on('always', function(instance) {
      for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
        $(imgLoad.images[i].img).addClass('scale_image_ready');
      }
      // update pos
      $("img.scale_image_ready").imageScale();
      // fade in - delay adding class to ensure image is ready  
      $('.fade_on_load').addClass('tb-fade-in');
      $('.image_container').css('opacity', 1);
    });
        
    function handleResize() {
      $("img.scale_image_ready").imageScale();
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
        $.post("server/mailerproxy.php", $('#signup_form').serialize()).success(function(data) {});
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
