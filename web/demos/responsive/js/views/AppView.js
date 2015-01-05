define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var AppView = Backbone.View.extend({
    initialize: function(){
	  var self = this;
	  
	  $('.royalSlider').show();

  	  $(".royalSlider").royalSlider({
  	  	imageScaleMode: 'fill',
  	  	controlNavigation: 'none',
  	  	slidesSpacing: 0,
  	  	loop: true,
//  	  	transitionType: 'fade',
        keyboardNavEnabled: true,
        autoScaleSlider: false,
    	fullscreen: {
    		enabled: true,
    		nativeFS: false
    	}
      });  	
      
	  var slider = $(".royalSlider").data('royalSlider');
//      slider.enterFullscreen();

	  slider.ev.on('rsAfterSlideChange', function(event) {
	    // triggers after slide change
	    
	    console.log(slider.currSlide);
	  });
  	  
      function buildMap() {
        var fLat = 59.312483;
        var fLng = 18.071243;
        console.log(fLat);
        console.log(fLng);
      
        var map = L.mapbox.map('location_map', 'mallbeury.map-kply0zpa', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});

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
        map.setView(latlng, 12);
      }                    
      buildMap();  	  
	}
	
  });

  return AppView;
});
