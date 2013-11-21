var ASSETS_BASEURL = 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/';
var DEF_ICONS = 0;
var SMALL_ICONS = 1;

define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMapMediaMarkerView = Backbone.View.extend({
    
    initialize: function(){
      this.point = null;
      this.latlng = this.options.latlng;
      this.map = this.options.map;
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
    render: function(){
      var self = this;
      
      this.marker = L.marker([this.latlng.lat, this.latlng.lng], {icon: this.mediaInactiveIcon, draggable:'true'}).on('click', onClick).addTo(this.map);   
      this.marker.bindPopup('<div class="trail_media_popup"><h4 class="tb">Picture name:</h4><div class="form-group"><input type="text" name="form_media_name" id="form_media_name" class="form-control"></div><div><span class="btn btn-tb-action btn-tb-large">Save</span></div><a href="">delete pin</a></div>');
      
      function onClick(e) {
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:mediaclick", self);                        
      }
      this.marker.on('dragend', function(event){
        self.placeMarker();
      });
        
      return this;
    },
    placeMarker: function(){
      var self = this;
      
      // look for closest point      
      var nClosestDistance = 0, nDistance = 0;
      var data = this.model.get('value');      
      $.each(data.route.route_points, function(key, point) {
        nDistance = self.marker.getLatLng().distanceTo([Number(point.coords[1]), Number(point.coords[0])]);
        if (nDistance < nClosestDistance || !nClosestDistance) {
          nClosestDistance = nDistance;       
          self.point = point;    
        }        
      });
      // position on closest point      
      this.marker.setLatLng([Number(this.point.coords[1]), Number(this.point.coords[0])]);      
      
      var dtDate = new Date(this.point.tags.datetime*1000); // unix timestamp to timestamp      
      // adjust based on timezone of 1st point
      dtDate.setSeconds(dtDate.getSeconds() + this.options.timezoneData.dstOffset);
      // adjust to UTC
      console.log('date:'+dtDate.toUTCString());      
    }
    
  });

  return TrailMapMediaMarkerView;
});
