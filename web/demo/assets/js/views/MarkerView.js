define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  
  var MarkerView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;
      
      this.bSelected = false;
	  },
    render: function(){
      var self = this;

      function onClick(evt) {
        // fire event
        app.dispatcher.trigger("MarkerView:click", self);
      }
      function onMouseOver(evt){
        self.onMouseOver(evt);
      }
      function onMouseOut(evt){
        self.onMouseOut(evt);
      }

      this.marker = L.marker([this.options.jsonMedia.coords.lat, this.options.jsonMedia.coords.long]).on('click', onClick).on('mouseover', onMouseOver).on('mouseout', onMouseOut);
      var strImage = 'http://tbmedia2.imgix.net/'+this.options.jsonMedia.versions[0].path+'?fm=jpg&q=80&w=128&h=128&fit=crop';
      this.marker.setIcon(L.divIcon({className: 'tb-map-media-marker', html: '<div class="icon"><div class="marker"><div class="avatar"><img src="'+strImage+'"></div><div class="overlay"></div></div></div><div class="pointer"></div>', iconSize: [60, 69], iconAnchor: [30, 69]}));
      this.options.mapCluster.addLayer(this.marker);

      return this;
    },
    focus: function(){
      this.bSelected = true;

      $(this.marker._icon).addClass('focus');
      this.marker.setZIndexOffset(2000);
    },
    blur: function(){ 
      this.bSelected = false;

      $(this.marker._icon).removeClass('focus');
      this.marker.setZIndexOffset(0);
    },    
    onMouseOver: function(evt){
      var self = this;

      if (!this.bSelected) {
        this.marker.setZIndexOffset(1000);
        $(this.marker._icon).addClass('focus');  
      }
    },
    onMouseOut: function(evt){
      var self = this;

      if (!this.bSelected) {
        this.marker.setZIndexOffset(0);
        $(this.marker._icon).removeClass('focus');
      }
    }
	
  });

  return MarkerView;
});
