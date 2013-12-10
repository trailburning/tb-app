var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/ProfileMapView'
], function(_, Modernizr, Backbone, ProfileMapView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    this.profileMapView = new ProfileMapView({ el: '#profile_map_view' });
    this.profileMapView.render();
        
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
  };
    
  return { 
    initialize: initialize
  };   
});  
