var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView'  
], function(_, Backbone, ActivityFeedView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
        
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
    
  	$('#footerview').show();
    
    if (typeof TB_USER_ID != 'undefined') {
  	  this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
  	  this.activityFeedView.render();
  	  this.activityFeedView.getActivity();	  	
    }
    
    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }
    
    function builtEventMap() {
      var fLat = TB_EVENT_LAT;
      var fLng = TB_EVENT_LONG;
      
      var map = L.mapbox.map('event_location_map', 'mallbeury.map-kply0zpa', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});

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
      map.setView(latlng, TB_EVENT_MAP_ZOOM);
    }
                    
    builtEventMap();
  };
    
  return { 
    initialize: initialize
  };   
});  
