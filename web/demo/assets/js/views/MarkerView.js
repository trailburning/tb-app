define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  
  var MarkerView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;
      
      this.ID = this.options.jsonMedia.id;
      this.parentID = this.options.parentID;
      this.pos = this.options.pos;
      this.jsonMedia = this.options.jsonMedia;
      this.bSelected = false;
      this.marker = null;
	  },
    render: function(){
      if (!this.options.jsonMedia.coords) {
        return;
      }
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
//      var strImage = 'http://tbmedia2.imgix.net/'+this.options.jsonMedia.versions[0].path+'?fm=jpg&q=80&w=128&h=128&fit=crop';
      var strImage = this.options.jsonMedia.thumb_res;

      if (this.options.bLarge) {
        this.marker.setIcon(L.divIcon({className: 'tb-map-media-marker large', html: '<div class="icon"><div class="marker"><div class="avatar"><img src="'+strImage+'"></div><div class="overlay"></div></div></div><div class="pointer"></div>', iconSize: [100, 109], iconAnchor: [50, 109]}));
      }
      else {
        this.marker.setIcon(L.divIcon({className: 'tb-map-media-marker', html: '<div class="icon"><div class="marker"><div class="avatar"><img src="'+strImage+'"></div><div class="overlay"></div></div></div><div class="pointer"></div>', iconSize: [60, 69], iconAnchor: [30, 69]}));
      }

      this.options.mapLayer.addLayer(this.marker);

      return this;
    },
    destroy: function(){
      if (this.marker) {
        this.options.mapLayer.removeLayer(this.marker);
      }
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
