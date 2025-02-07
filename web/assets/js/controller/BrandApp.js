var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/SearchView'
], function(_, Backbone, ActivityFeedView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
	$('.scale').imagesLoaded()
  	  .progress( function(instance, image) {
  	  	$(image.img).addClass('scale_image_ready');
        // update pos
        $(image.img).imageScale();
  	  	
    	var elContainer = $(image.img).parent();
    	if (elContainer.hasClass('fade_on_load')) {
          // fade in - delay adding class to ensure image is ready  
          elContainer.addClass('tb-fade-in');
		  var nRnd = 100 * (Math.floor(Math.random() * 6) + 1);
		  setTimeout(function(){
		  	elContainer.css('opacity', 1);
		  }, nRnd);    		
    	}
		// invoke resrc      
	    resrc.resrc($(image.img));        
  	});    
    
  	$('#footerview').show();
  	    
	this.searchView = new SearchView({ el: '#searchview' });
    if (typeof TB_USER_ID != 'undefined') {
  	  this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
  	  this.activityFeedView.render();
  	  this.activityFeedView.getActivity();	  	
    }
  	    
    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }
    
    function builtBrandMap() {
      var fLat = TB_BRAND_LAT;
      var fLng = TB_BRAND_LONG;
      
      var map = L.mapbox.map('brand_location_map', 'mallbeury.map-kply0zpa', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});

      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      var startIcon = new LocationIcon({iconUrl: 'http://assets.trailburning.com/images/icons/location.png'});
      L.marker([fLat, fLng], {icon: startIcon}).addTo(map);      

      var latlng = new L.LatLng(fLat, fLng);
      
      // mla temp
      if (TB_BRAND_ID == 220) {
        map.setView(latlng, 8);	
      }
      else {
        map.setView(latlng, 7);	
      }      
    }
                   
	if ($('.brand_location_panel').length) {
      builtBrandMap();    		
	}                                       
  };
    
  return { 
    initialize: initialize
  };   
});  