var ASSETS_BASEURL = 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/';
var DEF_ICONS = 0;
var SMALL_ICONS = 1;

define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMapMediaMarkerView = Backbone.View.extend({
    options: {placeOnTrail: true},
    initialize: function(){
      this.model = this.options.model;
      this.trailModel = this.options.trailModel;
//      this.jsonMedia = this.options.jsonMedia;
      this.point = null;
      this.map = this.options.map;
      this.popup = null;
      this.marker = null;
      this.nSize = DEF_ICONS;
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
    setActive: function(bActive){
      if (bActive) {
        this.marker.setIcon(this.mediaActiveIcon);
        this.marker.setZIndexOffset(200);
      }
      else {
        this.marker.setIcon(this.mediaInactiveIcon);
        this.marker.setZIndexOffset(100);
      }
    },    
    showPopup: function(){
      var popup_options = {
        autoPan: true,
        closeButton: false
      };                
        
      this.popup = L.popup(popup_options)
      .setLatLng([this.marker.getLatLng().lat, this.marker.getLatLng().lng])
      .setContent(this.popupContainer[0])
      .openOn(this.map);  
                  
      // force resrc
      resrc.resrcAll();      
    },    
    hidePopup: function(){
      if (this.popup) {
     	this.map.closePopup(this.popup);
      }
    },
    render: function(){
      var self = this;

      var versions = this.model.get('versions');
      // Create an element to hold all your text and markup
      this.popupContainer = $('<div />');      
      // Delegate all event handling for the container itself and its contents to the container
      this.popupContainer.on('click', '.deletepin_btn', function() {
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:removemedia", self);                        
        // goodbye pin
        self.map.closePopup(self.popup);
        self.map.removeLayer(self.marker);
      });
      this.popupContainer.on('click', '.save_btn', function() {
      	self.hidePopup();
      });
      this.popupContainer.html('<div class="trail_media_popup"><img src="http://app.resrc.it/O=80/http://s3-eu-west-1.amazonaws.com/'+versions[0].path+'" width="240" class="resrc"><span class="btn btn-tb-action btn-tb-large save_btn">Save</span> <a href="javascript:void(0)" class="deletepin_btn">delete pin</a></div>');

      function onClick(e) {
      	self.showPopup();      	
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:mediaclick", self);                        
      }
      this.marker = L.marker([this.model.get('coords').lat, this.model.get('coords').long], {icon: this.mediaInactiveIcon, draggable:'true'}).on('click', onClick).addTo(this.map);
      
      this.marker.on('dragstart', function(event){
      	self.hidePopup();
      });
      this.marker.on('dragend', function(event){
        self.placeMarker();
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:mediamoved", self);                        
      });
            
      // locate initial point
      if (this.options.placeOnTrail) {
        this.placeMarker();
      }

      return this;
    },
    placeMarker: function(){
      var self = this;
      
      // look for closest point      
      var nClosestDistance = 0, nDistance = 0, nDistanceToMarker = 0, nLength = 0, latlng = null, prevLatLng = null;;
      var data = this.trailModel.get('value');
      
      $.each(data.route.route_points, function(key, point) {
        nDistance = self.marker.getLatLng().distanceTo([Number(point.coords[1]), Number(point.coords[0])]);

        latlng = L.latLng(Number(point.coords[1]), Number(point.coords[0]));
        if (prevLatLng) {
          nLength += latlng.distanceTo(prevLatLng);
        }        
        prevLatLng = latlng;
        
        if (nDistance < nClosestDistance || !nClosestDistance) {
          nClosestDistance = nDistance;       
          self.point = point;    
          nDistanceToMarker = nLength;
        }        
      });
      
      // position on closest point      
      this.marker.setLatLng([Number(this.point.coords[1]), Number(this.point.coords[0])]);            
      // adjust to UTC            
      this.model.get('tags').datetime = this.point.tags.datetime;
      this.model.get('tags').altitude = this.point.tags.altitude;
      this.model.get('coords').lat = Number(this.point.coords[1]);
      this.model.get('coords').long = Number(this.point.coords[0]);
    }
    
  });

  return TrailMapMediaMarkerView;
});