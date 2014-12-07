define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/BrandPlayerView'  
], function(_, Backbone, ActivityFeedView, BrandPlayerView){
  
  var BrandView = Backbone.View.extend({
    initialize: function(){
      var self = this;

	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
      this.playerView = new BrandPlayerView({ model: this.model, mediaCollection: this.mediaCollection, mediaModel: this.mediaModel });            
      
      this.playerView.updatePlayerHeight();
      
      $(window).resize(function() {
        self.handleResize();
      });    
  
      $('#campaignplayer').show();
      $('.panel_container').show();
      
  	  this.playerView.render();

      $('#content_view').show();
      $('#footerview').show();
      
	  if ($('.brand_location_panel').length) {
        this.builtBrandMap();    		
	  }                                       
      
      this.playerView.handleMedia();
      this.handleResize();
      
      // keyboard control
      $(document).keydown(function(e){
      	switch (e.keyCode) {
      	  case 13: // toggle overlay
            e.preventDefault();
            self.playerView.togglePlayer();
      	    break;
      	  case 86: // toggle view
			self.playerView.toggleView();      	  
      	    break;      	    
      	}
      });      
    },   
    builtBrandMap: function(){
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
    },   
    handleResize: function(){
      this.playerView.handleResize();
    }
    
  });

  return BrandView;
});
