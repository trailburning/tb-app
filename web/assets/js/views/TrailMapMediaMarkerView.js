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
      this.popupOverlay = null;      
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
      this.popupOverlay.show();    	

      var imgLoad = imagesLoaded('.trail_media_popup .scale');
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
          // update pos
          $(imgLoad.images[i].img).imageScale();
        }
        // update pos
        $('.trail_media_popup img.scale_image_ready').imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.trail_media_popup .fade_on_load').addClass('tb-fade-in');
        $('.trail_media_popup .image_container').css('opacity', 1);
      });
    },
    hidePopup: function(){
	  if (this.popupOverlay) {
		this.popupOverlay.hide();
      }    	
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
      
      this.marker = L.marker([this.model.get('coords').lat, this.model.get('coords').long], {icon: this.mediaInactiveIcon, zIndexOffset: 100}).on('click', onClick).addTo(this.map);;
      function onClick(e) {       
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:mediaclick", self);                        
      }

      // build popup      
      var versions = this.model.get('versions');
      
	  var MyCustomLayer = L.Class.extend({

        initialize: function (latlng) {
          // save position of the layer or any options from the constructor
          this._latlng = latlng;
    	},

    	onAdd: function (map) {
          this._map = map;

          // create a DOM element and put it into one of the map panes
          this._el = L.DomUtil.create('div', 'trail_media_popup_overlay leaflet-zoom-hide');
          $(this._el).append('<div class="leaflet-popup-content-wrapper"><div class="leaflet-popup-content" style="width: 125px;"><div class="trail_media_popup"><div class="image_container fade_on_load tb-fade"><img src="http://app.resrc.it/o=80/http://s3-eu-west-1.amazonaws.com/'+versions[0].path+'" class="resrc scale photo_btn" border="0"></div></div></div></div><div class="leaflet-popup-tip-container"><div class="leaflet-popup-tip"></div></div>');        
          map.getPanes().markerPane.appendChild(this._el);
          
          // add a viewreset event listener for updating layer's position, do the latter
          map.on('viewreset', this._reset, this);
          this._reset();
    	},

    	onRemove: function (map) {
          // remove layer's DOM elements and listeners
          map.getPanes().overlayPane.removeChild(this._el);
          map.off('viewreset', this._reset, this);
    	},

    	show: function () {
    	  $(this._el).fadeIn();
		},

    	hide: function () {    	
    	  $(this._el).fadeOut();
		},

    	_reset: function () {
          // update layer's position
          var pos = this._map.latLngToLayerPoint(this._latlng);
          L.DomUtil.setPosition(this._el, pos);
    	}
	  });   
	  this.popupOverlay = new MyCustomLayer(this.marker.getLatLng());
	  this.map.addLayer(this.popupOverlay);
	  $(this.popupOverlay._el).on('click', '.photo_btn', function(evt) {
      	evt.stopPropagation();
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:photoclick", self);                              	  	
	  });
      
      return this;
    }    
  });

  return TrailMapMediaMarkerView;
});
