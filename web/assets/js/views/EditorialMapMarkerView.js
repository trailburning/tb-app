define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var EditorialMapMarkerView = Backbone.View.extend({
    initialize: function(){
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      this.locationIcon = new LocationIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/location.png'});      
    },            
    render: function(){
      var self = this;

      this.marker = L.marker([this.options.lat, this.options.lng], {icon: self.locationIcon, zIndexOffset: 1000}).on('click', onClick).addTo(this.options.map);
      function onClick(e) {       
        // fire event
        app.dispatcher.trigger("EditorialMapMarkerView:markerclick", self);                        
      }

      return this;
    }    
  });

  return EditorialMapMarkerView;
});
