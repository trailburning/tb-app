define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var MapTrailMarkerPopup = Backbone.View.extend({
    initialize: function(){
	  this.popup = null;
    },            
    show: function(fLat, fLng){    
      var popup_options = {
        autoPan: true,
        closeButton: true,
        maxWidth: 460,
        autoPanPadding: [30, 30],
        offset: [0, -24]
      };                
        
      this.popup = L.popup(popup_options)
      .setLatLng([this.model.get('start')[1], this.model.get('start')[0]])
      .setContent(this.popupContainer[0])
      .openOn(this.options.map);  
      
  	  // reset      
      $('.tb-trailpopup.fade_on_load').removeClass('tb-fade-in');
      $('.tb-trailpopup.image_container').css('opacity', 0);
        
	  if (!this.model.get('user').is_ambassador) {
        $('.tb-trailpopup .card_flag').hide();
      }
            
      var nRating = this.model.get('rating');
      if (!nRating) {
        // do not show no stars
       	$('.tb-trailpopup .stars').hide();
      }
      
      // mla - this should be an id
	  if (this.model.get('category').name == 'Mountains') {
	  	$('.tb-trailpopup h3').addClass('reduce');
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
    hide: function(){
      if (this.popup) {
     	this.options.map.closePopup(this.popup);
      }
    },
    render: function(){
      var self = this;

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
	    this.model.set('ascent_m', formatAltitude(Math.round(this.model.get('tags').ascent)));
	    this.model.set('descent_m', formatAltitude(Math.round(this.model.get('tags').descent)));	  	
	  }

	  var bEvent = false;
      switch (this.model.get('slug')) {
        case '16km':
        case '30km':
        case '46km':
          bEvent = true;	          	
          this.model.set('sponsorURL', 'event/ultraks');
          break;	         
      	case 'pitschen-16k-st-moritz':        	  
      	case 'grand-30k-st-moritz':
      	case 'grand-46k-st-moritz':
      	  bEvent = true;	          	
      	  model.set('sponsorURL', 'event/ultraksengadin');
      	  break;	          	             	  
        case 'e16':
        case 'e51':
          bEvent = true;	          	
        case 'e101':
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

	  var strPath = '/images/default/example_trailcard.jpg';
	  if (this.model.get('media')) {
	    var versions = this.model.get('media').versions;
	    var strPath = versions[0].path;      	
	  }
	  this.model.set('versionLargePath', strPath);      	
	
	  if (this.model.get('category') == undefined) {
		this.model.set('category', '');
	  }
	
	  if (this.model.get('length')) {
		this.model.set('length_km', Math.ceil(this.model.get('length') / 1000));
		this.model.set('ascent_m', Math.round(this.model.get('tags').ascent));
		this.model.set('descent_m', Math.round(this.model.get('tags').descent));	  	
 	  }

	  var attribs = this.model.toJSON();
	    
      this.template = _.template($('#trailMarkerPopupViewTemplate').text());
	  if (bEvent) {
	    this.template = _.template($('#trailEventMarkerPopupViewTemplate').text());	
	  }
	    
      this.popupContainer.html(this.template(attribs));
                       
      return this;
    }
  });

  return MapTrailMarkerPopup;
});
