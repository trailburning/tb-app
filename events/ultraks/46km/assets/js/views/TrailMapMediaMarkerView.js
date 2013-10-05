define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMapMediaMarkerView = Backbone.View.extend({
    initialize: function(){
      
      this.map = this.options.map;
      this.marker = null;
      
      var MediaIcon = L.Icon.extend({
          options: {
              iconSize:     [23, 24],
              iconAnchor:   [11, 11],
              popupAnchor:  [11, 11]
          }
      });      
      this.mediaInactiveIcon = new MediaIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/marker_inactive.png'});
      this.mediaActiveIcon = new MediaIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/marker_active.png'});            
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
      
      this.marker = L.marker([this.model.get('coords').lat, this.model.get('coords').long], {icon: this.mediaInactiveIcon}).on('click', onClick).addTo(this.map);;

      function onClick(e) {       
        // fire event
        app.dispatcher.trigger("TrailMapMediaMarkerView:mediaclick", self);                        
      }
      return this;
    }    
  });

  return TrailMapMediaMarkerView;
});
