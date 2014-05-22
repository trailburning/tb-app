define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var MapTrailCardView = Backbone.View.extend({
  	className: "panel",
    initialize: function(){
      this.template = _.template($('#mapTrailCardViewTemplate').text());        
                        
      this.bRendered = false;
	  this.mapRouteMarkerView = null;
    },            
    render: function(){
      var self = this;

      if (!this.bRendered) {
      	// mla - all avatar urls should be http based
      	if (this.model.get('user').avatar.substr(0, 4) != 'http') {
      	  this.model.get('user').avatar = 'http://s3-eu-west-1.amazonaws.com/trailburning-assets/images/default/' + this.model.get('user').avatar;
      	}
      		
        var versions = this.model.get('media').versions;
      	this.model.set('versionLargePath', versions[0].path);
		if (this.model.get('category') == undefined) {
		  this.model.set('category', '');
      	}
      	        	  
        var versions = this.model.get('media').versions;
      	this.model.set('versionLargePath', versions[0].path);
		if (this.model.get('category') == undefined) {
		  this.model.set('category', '');
      	}
      
        // add to map
        function onClick(e) {
		  // fire event
          app.dispatcher.trigger("MapTrailCardView:markerclick", self);                
	    }
        
	    this.marker = L.marker(new L.LatLng(this.model.get('centroid')[1], this.model.get('centroid')[0])).on('click', onClick);			  
	    this.marker.setIcon(L.divIcon({className: 'tb-map-marker', html: '<div class="marker"></div>', iconSize: [20, 20]}));      	  
		this.options.mapCluster.addLayer(this.marker);      	  

        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        $(this.el).addClass('trail_card_panel');
        $(this.el).attr('data-id', this.model.cid);
      
        var nRating = this.model.get('rating');
        $.each($('.star', $(this.el)), function(index, value){
          switch (index) {
          	case 0:
          	  if (nRating > 0) {
          	    $(this).addClass('star_full');          	  	
          	  }
          	  break;
          	case 1:
          	  if (nRating > 1) {
          	    $(this).addClass('star_full');          	  	
          	  }
          	  break;
          	case 2:
          	  if (nRating > 2) {
          	    $(this).addClass('star_full');          	  	
          	  }
          	  break;
          	case 3:
          	  if (nRating > 3) {
          	    $(this).addClass('star_full');          	  	
          	  }
          	  break;
          	case 4:
          	  if (nRating > 4) {
          	    $(this).addClass('star_full');          	  	
          	  }
          	  break;
          }
        });
      
      	$('.location', this.el).click(function(evt){
		  // fire event
          app.dispatcher.trigger("MapTrailCardView:cardmarkerclick", self);                
      	});
      	      
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
        });        
		// invoke resrc      
	    resrc.resrc($('.scale', $(this.el)));                
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

  return MapTrailCardView;
});
