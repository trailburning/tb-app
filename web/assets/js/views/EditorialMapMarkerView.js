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
      this.locationIcon = new LocationIcon({iconUrl: 'http://assets.trailburning.com/images/icons/location.png'});      
    },            
    render: function(){
      var self = this;

      this.marker = L.marker([this.options.trail.lat, this.options.trail.long], {icon: self.locationIcon, zIndexOffset: 1000}).on('click', onClick).on('mouseover', onMouseOver).addTo(this.options.map);
      function onClick(e) {       
        // fire event
        app.dispatcher.trigger("EditorialMapMarkerView:markerclick", self);                        
      }
      function onMouseOver(e) {
	    self.popup.openOn(self.options.map);
	  }

      // Create an element to hold all your text and markup
      var container = $('<div class="trail_location_popup clearfix" />');      
      container.html('<div class="icon"><img src="http://assets.trailburning.com/images/icons/mini_trailcard.png"></div><div class="detail">'+this.options.trail.short_name+'<br/>'+this.options.trail.region+'</div>');

      this.popup = L.popup({'offset': L.point(0, -24), 'autoPan': false, 'closeButton': false})
      .setLatLng([this.options.trail.lat, this.options.trail.long])
      .setContent(container[0]);

      return this;
    }    
  });

  return EditorialMapMarkerView;
});
