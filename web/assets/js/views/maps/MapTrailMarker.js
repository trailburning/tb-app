define([
  'underscore', 
  'backbone',
  'models/TrailModel'
], function(_, Backbone, TrailModel){

  var MapTrailMarker = Backbone.View.extend({
    initialize: function(){
      this.trailEvents = this.trailEvents || {};    	
    	
  	  this.trailEvents.dispatcher = _.clone(Backbone.Events);
    	
	  var self = this;
	  this.popup = null;
	      	
      this.trailEvents.dispatcher.on("DistanceMarkers:click", function(evt){
      	self.showPopup();
		// fire event
        app.dispatcher.trigger("MapTrailMarker:click", self);                     	  	
      }, this);
      this.trailEvents.dispatcher.on("DistanceMarkers:mouseover", function(evt){
	    self.onMouseOver(evt);      	  	
      }, this);
    	
      this.trailModel = new TrailModel();    	
      this.bRendered = false;
      this.bTrailRendered = false;
      this.bTrailVisible = false;
      this.bSelected = false;
      this.polyline = null;
      this.hoverPolyline = null;
      this.arrLineCordinates = [];
    },            
    showTrail: function(){
      if (this.polyline) {
        this.polyline.addTo(this.options.map);
		this.hoverPolyline.addTo(this.options.map);
		if (this.bSelected) {
		  this.selected(true);
		}
		this.bTrailVisible = true;
      }
    },
    hideTrail: function(){
      if (this.polyline) {
        this.options.map.removeLayer(this.polyline);
        this.options.map.removeLayer(this.hoverPolyline);
      } 
      this.bTrailVisible = false;   
    },
    showPopup: function(){
      var popup_options = {
        autoPan: true,
        closeButton: true,
        maxWidth: 500,
        offset: [0, -15]
      };                
        
      this.popup = L.popup(popup_options)
      .setLatLng([this.marker.getLatLng().lat, this.marker.getLatLng().lng])
      .setContent(this.popupContainer[0])
      .openOn(this.options.map);  
      
  	  // reset      
      $('.tb-trailpopup.fade_on_load').removeClass('tb-fade-in');
      $('.tb-trailpopup.image_container').css('opacity', 0);
            
      var nRating = this.model.get('rating');
      if (!nRating) {
        // do not show no stars
       	$('.tb-trailpopup .stars').hide();
      }
      
      $.each($('.tb-trailpopup .star'), function(index, value){
		switch (index) {
          case 0:
            if (nRating > 0) {
          	  $(this).addClass('star_full');          	  	
          	}
          	break;
          case 1:
          	if (nRating >= 2) {
          	  $(this).addClass('star_full');          	  	          	  
          	}
          	else if (nRating >= 1.5) {
          	  $(this).addClass('star_half');          	  	          	  
          	}
          	else {
          	  $(this).addClass('star');          	  	          	  
          	}
          	break;
          case 2:
          	if (nRating >= 3) {
          	  $(this).addClass('star_full');          	  	          	  
          	}
          	else if (nRating >= 2.5) {
          	  $(this).addClass('star_half');          	  	          	  
          	}
          	else {
          	  $(this).addClass('star');          	  	          	  
          	}
          	break;
          case 3:
          	if (nRating >= 4) {
          	  $(this).addClass('star_full');          	  	          	  
          	}
          	else if (nRating >= 3.5) {
          	  $(this).addClass('star_half');          	  	          	  
          	}
          	else {
          	  $(this).addClass('star');          	  	          	  
          	}
          	break;
          case 4:
          	if (nRating >= 5) {
          	  $(this).addClass('star_full');          	  	          	  
          	}
          	else if (nRating >= 4.5) {
          	  $(this).addClass('star_half');          	  	          	  
          	}
          	else {
          	  $(this).addClass('star');          	  	          	  
          	}
          	break;
          }        	
      });      
      
	  // scale images when loaded
	  var elImages = $('.tb-trailpopup .scale');
	  var imgLoad = imagesLoaded(elImages);
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        // update pos
        $('.tb-trailpopup img.scale_image_ready').imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.tb-trailpopup .fade_on_load').addClass('tb-fade-in');
        $('.tb-trailpopup .image_container').css('opacity', 1);
      });
	  // invoke resrc      
      resrc.resrc($('.tb-trailpopup .scale'));                      
    },    
    hidePopup: function(){
      if (this.popup) {
     	this.options.map.closePopup(this.popup);
      }
    },
    render: function(){
      var self = this;

      if (!this.bRendered) {
        // add to map
        function onClick(evt) {
		  self.showPopup();        	
		  // fire event
          app.dispatcher.trigger("MapTrailMarker:click", self);                
	    }
	    function onMouseOver(evt){
	  	  self.onMouseOver(evt);
	    }
	    function onMouseOut(evt){
	  	  self.onMouseOut(evt);
	    }	    
	    
	    this.marker = L.marker(new L.LatLng(this.model.get('start')[1], this.model.get('start')[0])).on('click', onClick).on('mouseover', onMouseOver).on('mouseout', onMouseOut);			  
	    this.marker.setIcon(L.divIcon({className: 'tb-map-location-marker', html: '<div class="marker"></div>', iconSize: [18, 25], iconAnchor: [9, 25]}));      	  
		this.options.mapCluster.addLayer(this.marker);

        this.popupContainer = $('<div />');      
        this.popupContainer.on('click', '.btnView', function(evt) {
          // fire event          
          app.dispatcher.trigger("MapTrailDetail:click", this);                        
        });
        
	    if (this.model.get('category') == undefined) {
		  this.model.set('category', '');
        }

	    if (this.model.get('length')) {
	      this.model.set('length_km', Math.ceil(this.model.get('length') / 1000));
	      this.model.set('ascent_m', Math.round(this.model.get('tags').ascent));
	      this.model.set('descent_m', Math.round(this.model.get('tags').descent));	  	
	    }

	  	var bEvent = false;
      	switch (this.model.get('slug')) {
          case '16km':
          case '30km':
          case '46km':
            bEvent = true;	          	
            this.model.set('sponsorURL', 'event/ultraks');
            break;	          	  
          case 'e16':
          case 'e51':
          case 'e101':
            bEvent = true;	          	
            this.model.set('sponsorURL', 'event/eiger');
            break;	          	  
          case 'ttm':
            bEvent = true;	          	
            this.model.set('sponsorURL', 'event/tfor');
            break;	          	  
          case 'marathon':
            bEvent = true;	          	
            this.model.set('sponsorURL', 'event/aom');
            break;	          	  
          case 'ultramarathon':
            bEvent = true;	          	
            this.model.set('sponsorURL', 'event/laugavegur');
            break;	          	  
      	  case 'lantau-vertical-hong-kong':
      	    bEvent = true;	          	
      	    this.model.set('sponsorURL', 'event/lantauvertical');
      	    break;	          	  	          	  
      	  case 'heysen-105-south-australia':
      	    bEvent = true;	          	
      	    this.model.set('sponsorURL', 'event/heysen105');
      	    break;	          
      	  case 'scenic-trail-24k-d-2200m-lugano-capriasca-switzerland':
      	  case 'scenic-trail-54k-d-3900m-lugano-capriasca-switzerland':
      	    bEvent = true;	          	
      	    this.model.set('sponsorURL', 'event/luganoscenictrail');
        }

        if (this.model.get('user').type == 'brand') {
	      this.model.set('sponsorURL', 'profile/' + this.model.get('user').name);
  	      bEvent = true;
        }

	    var strPopup = '<div class="tb-trailpopup"><div class="card"><div class="image_container fade_on_load"><img data-src="http://app.resrc.it/O=80/http://media.trailburning.com'+this.model.get('media').versions[0].path+'" class="resrc scale"></div><div class="card_title"><h1>'+this.model.get('name')+'</h1><br/><h2>'+this.model.get('region')+'</h2><br/><h2><span class="stars"><span class="star"></span><span class="star"></span><span class="star"></span><span class="star"></span><span class="star"></span></span></h2></div></div><div class="card_avatar"><div class="tb-avatar"><div class="photo"><a href="'+TB_BASEURL+'/profile/'+this.model.get('user').name+'"><img src="'+this.model.get('user').avatar+'"></a></div></div></div><div class="detail_container"><h3 class="tb">'+this.model.get('category').name+'</h3><div class="summary"><div class="length">'+this.model.get('length_km')+' km</div><div class="altitude">'+this.model.get('ascent_m')+' D+<br/>'+this.model.get('descent_m')+' D-</div></div><div class="btns"><span data-url="'+TB_PATH+'/trail/'+this.model.get('slug')+'" data-id="'+this.model.id+'" class="btn btn-tb-action btnView">View the Trail</span></div></div></div>';              
	    if (bEvent) {
	      strPopup = '<div class="tb-trailpopup"><div class="card"><div class="image_container fade_on_load"><img data-src="http://app.resrc.it/O=80/http://media.trailburning.com'+this.model.get('media').versions[0].path+'" class="resrc scale"></div><div class="card_title"><h1>'+this.model.get('name')+'</h1><br/><h2>'+this.model.get('region')+'</h2><br/><h2><span class="stars"><span class="star"></span><span class="star"></span><span class="star"></span><span class="star"></span><span class="star"></span></span></h2></div></div><div class="card_avatar"><a href="'+TB_BASEURL+'/'+this.model.get('sponsorURL')+'"><img src="http://assets.trailburning.com/images/'+this.model.get('sponsorURL')+'/card_trail_sticker_logo.png"></a></div><div class="detail_container"><h3 class="tb">'+this.model.get('category').name+'</h3><div class="summary"><div class="length">'+this.model.get('length_km')+' km</div><div class="altitude">'+this.model.get('ascent_m')+' D+<br/>'+this.model.get('descent_m')+' D-</div></div><div class="btns"><span data-url="'+TB_PATH+'/trail/'+this.model.get('slug')+'" data-id="'+this.model.id+'" class="btn btn-tb-action btnView">View the Trail</span></div></div></div>';              
	    }
      	this.popupContainer.html(strPopup);      	
	  }
      this.bRendered = true;
                       
      return this;
    },
	renderTrail: function(){
      if (this.bTrailRendered) {
      	this.showTrail();
      	return;
      }
		
	  var self = this;
		
	  function onClick(evt){
	  	self.showPopup();
	    // fire event
        app.dispatcher.trigger("MapTrailMarker:click", self);                
	  }
	  function onMouseOver(evt){
	  	self.onMouseOver(evt);
	  }
	  function onMouseOut(evt){
	  	self.onMouseOut(evt);
	  }
		
      // get trail    
      this.trailModel.set('id', this.model.id);             
      this.trailModel.fetch({
        success: function () {                
	      var nDistanceOffsetMetres = 1000;
	      // over 10k reduce markers
	      if (self.trailModel.get('value').route.length > 10000) {
	        nDistanceOffsetMetres = 2000;
		  }
	      
	      self.blur_polyline_options = {
	        color: '#1f1f1f',
	        opacity: 0.5,
	        weight: 4,
	        clickable: false,
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id, strClassName: 'dist-marker' }
	      };         
		  
	      self.focus_polyline_options = {
	        color: '#1f1f1f',
	        opacity: 1,
	        weight: 4,
	        clickable: false,
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id, strClassName: 'dist-marker' }
	      };               
	
	      self.select_polyline_options = {
	        color: '#ed1c24',
	        opacity: 1,
	        weight: 4,
	        clickable: false,
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id, strClassName: 'dist-marker' }
	      };                       	
        	
      	  var data = self.trailModel.get('value');      
      	  $.each(data.route.route_points, function(key, point) {
        	self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      	  });
      	  self.polyline = L.polyline(self.arrLineCordinates, self.blur_polyline_options);
      	  
          var hover_polyline_options = {
        	color: '#000000',
        	opacity: 0,
        	weight: 20,
        	clickable: true,
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id, strClassName: 'dist-marker-active' }
      	  };               	  
      	  self.hoverPolyline = L.polyline(self.arrLineCordinates, hover_polyline_options).on('click', onClick).on('mousemove', function(evt){
	  	    self.onMouseOver(evt);      	  	
      	  }).on('mouseout', function(evt){
            self.onMouseOut(evt);
      	  });
      	  self.showTrail();
        }      
      });            
	  this.bTrailRendered = true;      
  	},
	select: function(){
	  // fire event
      app.dispatcher.trigger("MapTrailMarker:click", this);                
	},		
	focus: function(){	
	  $(this.marker._icon).addClass('selected');	
	  this.marker.setZIndexOffset(2);

	  if (this.polyline) {
        this.polyline.setStyle(this.focus_polyline_options);
	  	this.polyline.addDistanceMarkers();
	  	if (this.bTrailVisible) {
          this.polyline.bringToFront();
          this.hoverPolyline.bringToFront();	  		
	  	}
	  }
	},
	blur: function(){	
  	  $(this.marker._icon).removeClass('selected');  	
	  this.marker.setZIndexOffset(0);
  	
      if (this.polyline) {
        this.polyline.setStyle(this.blur_polyline_options);
        this.polyline.removeDistanceMarkers();
      }
	},
	selected: function(bSelected){
	  this.bSelected = bSelected;
	  if (bSelected) {
	  	this.focus();
	  	if (this.polyline) {
          this.hoverPolyline.addDistanceMarkers();
          this.polyline.setStyle(this.select_polyline_options);
        }
	  }
	  else {
	  	this.blur();
	  	if (this.polyline) {	
          this.hoverPolyline.removeDistanceMarkers();
        }
	  }		
	},
	onMouseOver: function(evt){	
  	  if (!this.bSelected) {
  	  	this.focus();
  	  }
    },
	onMouseOut: function(evt){	
  	  if (!this.bSelected) {  	 
  	  	this.blur(); 	
	  }
  	}
  });

  return MapTrailMarker;
});
