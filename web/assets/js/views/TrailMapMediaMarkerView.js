var ASSETS_BASEURL = 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/';
var DEF_ICONS = 0;
var SMALL_ICONS = 1;

define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMapMediaMarkerView = Backbone.View.extend({
    initialize: function(){
      this.map = this.options.map;
      this.marker = null;
      this.nSize = DEF_ICONS;
      this.bEnablePopup = false;
      this.popup = null;
      this.bActive = false;
      if (this.options.size) {
        this.nSize = this.options.size;
      }

      var MediaIcon = null;
      switch (this.nSize) {
        case DEF_ICONS:
          MediaIcon = L.Icon.extend({
            options: {
                iconSize:     [23, 24],
                iconAnchor:   [11, 11],
                popupAnchor:  [0, 0]
            }
          });              
          this.mediaInactiveIcon = new MediaIcon({iconUrl: ASSETS_BASEURL + 'images/icons/marker_inactive.png'});
          this.mediaActiveIcon = new MediaIcon({iconUrl: ASSETS_BASEURL + 'images/icons/marker_active.png'});            
          break;
          
        case SMALL_ICONS:
          MediaIcon = L.Icon.extend({
            options: {
                iconSize:     [18, 18],
                iconAnchor:   [8, 8],
                popupAnchor:  [0, 0]
            }
          });      
          this.mediaInactiveIcon = new MediaIcon({iconUrl: ASSETS_BASEURL + 'images/icons/sm_marker_inactive.png'});
          this.mediaActiveIcon = new MediaIcon({iconUrl: ASSETS_BASEURL + 'images/icons/sm_marker_active.png'});            
          break;
      }
    },            
    show: function(){
	  if (this.popup) {
	    this.popup.update();    	
	  }    	
      this.marker.setOpacity(1);
    },
    hide: function(){
      this.marker.setOpacity(0);
    },    
    enablePopup: function(bEnable){
      this.bEnablePopup = bEnable;
      if (this.bEnablePopup && this.bActive) {
        this.showPopup();      
      }
      else {
        this.hidePopup();
      }
    },
    showPopup: function(){
      this.popup.openOn(this.map);
      var imgLoad = imagesLoaded('.trail_media_popup .scale');
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        // update pos
        $('.trail_media_popup img.scale_image_ready').imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.trail_media_popup .fade_on_load').addClass('tb-fade-in');
        $('.trail_media_popup .image_container').css('opacity', 1);
      });
    },
    hidePopup: function(){
      this.map.closePopup();
      $('.trail_media_popup .image_container').css('opacity', 0);
    },
    setActive: function(bActive){
      this.bActive = bActive;
      if (bActive) {
        this.marker.setIcon(this.mediaActiveIcon);
        this.marker.setZIndexOffset(200);        
        if (this.bEnablePopup) {        
          this.showPopup();
        }
      }
      else {
        this.marker.setIcon(this.mediaInactiveIcon);
        this.marker.setZIndexOffset(100);
        if (this.bEnablePopup) {        
          this.hidePopup();          
        }
      }
    },    
    render: function(){
      var self = this;
      
      this.marker = L.marker([this.model.get('coords').lat, this.model.get('coords').long], {icon: this.mediaInactiveIcon}).on('click', onClick).addTo(this.map);;
      function onClick(e) {       
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:mediaclick", self);                        
      }

      // build popup      
      var versions = this.model.get('versions');
      
      // Create an element to hold all your text and markup
      var container = $('<div class="trail_media_popup" />');      
      // Delegate all event handling for the container itself and its contents to the container
      container.on('click', '.photo_btn', function() {
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:photoclick", self);                        
      });
      container.on('mouseover', '.photo_btn', function() {
      	$(this).css('cursor', 'pointer');
      });
      
      container.html('<div class="image_container fade_on_load tb-fade"><img src="http://app.resrc.it/o=80/http://s3-eu-west-1.amazonaws.com/'+versions[0].path+'" class="resrc scale photo_btn" border="0"></div>');
      
      this.popup = L.popup({'closeButton': false})
      .setLatLng([this.model.get('coords').lat, this.model.get('coords').long])
      .setContent(container[0]);
      
      return this;
    }    
  });

  return TrailMapMediaMarkerView;
});
