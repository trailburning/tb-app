define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailsTrailCardView = Backbone.View.extend({
  	className: "panel",
    initialize: function(){
      this.template = _.template($('#trailsTrailCardViewTemplate').text());        
            
      this.bRendered = false;
    },            
    render: function(){
      var self = this;

      if (!this.bRendered) {
      	if (this.model) {
          var versions = this.model.get('media').versions;
      	  this.model.set('versionLargePath', versions[0].path);
		  if (this.model.get('category') == undefined) {
		  	this.model.set('category', '');
      	  }
      	}

        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        $(this.el).addClass('trail_card_panel');
        $(this.el).attr('data-id', this.model.cid);
      
        var imgLoad = imagesLoaded($('.scale', $(this.el)));
        imgLoad.on('always', function(instance) {
          for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
            $(imgLoad.images[i].img).addClass('scale_image_ready');
            // update pos
            $(imgLoad.images[i].img).imageScale();
          }
          // fade in - delay adding class to ensure image is ready  
          $('.fade_on_load', $(self.el)).addClass('tb-fade-in');
          $('.image_container', $(self.el)).css('opacity', 1);
          // force update to fix blurry bug
	      resrc.resrcAll();
        });
        
        // add to map
        function onClick(e) {
		  // fire event
          app.dispatcher.trigger("TrailCardView:markerclick", self);                
	  	}
        
	    this.marker = L.marker(new L.LatLng(this.model.get('centroid')[1], this.model.get('centroid')[0])).on('click', onClick);			  
		this.marker.setIcon(L.divIcon({className: 'tb-map-marker', html: '<div class="marker"></div>', iconSize: [20, 20]}));
		this.options.mapCluster.addLayer(this.marker);
	  }
      this.bRendered = true;
                       
      return this;
    },
	selected: function(bSelected){
	  if (bSelected) {
        this.marker.setIcon(L.divIcon({className: 'tb-map-marker selected', html: '<div class="marker"></div>', iconSize: [20, 20]}));	  	
	  }
	  else {
        this.marker.setIcon(L.divIcon({className: 'tb-map-marker', html: '<div class="marker"></div>', iconSize: [20, 20]}));	  		  	
	  }		
	}

  });

  return TrailsTrailCardView;
});
