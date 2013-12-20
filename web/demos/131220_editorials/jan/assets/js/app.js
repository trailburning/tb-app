var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';

define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  app.dispatcher = _.clone(Backbone.Events);
  
  var STATE_BIG_SPONSOR = 1;
  var STATE_SMALL_SPONSOR = 2;
  
  var initialize = function() {
    var self = this;
    var nSponsorState = STATE_BIG_SPONSOR;
    var nPrevScrollY = 0;
    
	$(window).scroll(function () {
      handleScroll(); 
    });    
    
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

	function handleScroll() {
	  var nTopY = 63;
	  var nTransitionOffY = 35;
	  var nTransitionOnY = 100;
	  var nScrollY = $(window).scrollTop();	  
	  var nFactorY = 2;
	  var bScrollUp = false;

	  // which direction are we scrolling?	  
	  if (nScrollY > nPrevScrollY) {
	  	bScrollUp = true;	  	
	  }
	  // move bug bar
	  $('#big_sponsor_bar').css('top', nTopY - (nScrollY * nFactorY));

  	  switch (nSponsorState) {
  	    case STATE_BIG_SPONSOR:
  	      if ((nScrollY > nTransitionOffY) && bScrollUp) {
	  	    nSponsorState = STATE_SMALL_SPONSOR;
	  		$('#small_sponsor_bar').css('top', nTopY);
  	      }
	  	  break;
	  	  
  	    case STATE_SMALL_SPONSOR:
  	      if ((nScrollY < nTransitionOnY) && !bScrollUp) {
	  	    nSponsorState = STATE_BIG_SPONSOR;
	  		$('#small_sponsor_bar').css('top', 0);
	  	  }
	  	  break;
	  }
	  nPrevScrollY = nScrollY;
	}	
  };
    
  return { 
    initialize: initialize
  };   
});  
