var ASSETS_BASEURL = 'http://assets.trailburning.com/';
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
      this.bActive = false;
    },            
    setActive: function(bActive){
      this.bActive = bActive;
      if (bActive) {
        $(this.marker._icon).addClass('focus');
        this.marker.setZIndexOffset(200);        
      }
      else {
        $(this.marker._icon).removeClass('focus');
        this.marker.setZIndexOffset(100);
      }
    },    
    render: function(){
      var self = this;
      
      this.marker = L.marker([this.model.get('coords').lat, this.model.get('coords').long]).on('click', onClick);      
      this.marker.setIcon(L.divIcon({className: 'tb-map-marker', html: '<div class="marker"></div>', iconSize: [14, 14], iconAnchor: [9, 9]}));      	  
      this.marker.addTo(this.map);
      
      function onClick(e) {       
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:mediaclick", self);                        
      }
      
      return this;
    }    
  });

  return TrailMapMediaMarkerView;
});
