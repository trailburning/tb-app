define([
  'underscore', 
  'backbone',
  'views/TrailSlideshowSlideView'
], function(_, Backbone, TrailSlideshowSlideView){

  var TrailSlideshowView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#slideshowViewTemplate').text());
      
      app.dispatcher.on("TrailSlideshowSlideView:click", this.onTrailSlideshowSlideViewClick, this);
      
      this.nActiveID = 0;
	  this.nActiveSlide = 0;
	  this.bRendered = false;
    },            
    render: function(){
      var self = this;

      // first time
      if (!this.bRendered) {
      	$(this.el).html(this.template());
      	
      	$('#slideshow_nav .prev').click(function(evt){
      		var nSlide = self.collection.length - 1;
      		if (self.nActiveSlide - 1 >= 0) {
      		  nSlide = self.nActiveSlide - 1;
      		}
      		var model = self.collection.at(nSlide)
      		self.selectSlide(model.id);
      	});

      	$('#slideshow_nav .next').click(function(evt){
      		var nSlide = 0;
      		if (self.nActiveSlide + 1 < self.collection.length) {
      		  nSlide = self.nActiveSlide + 1;
      		}
      		var model = self.collection.at(nSlide)
      		self.selectSlide(model.id);
      	});
      	
	    this.options.collection.forEach(function(media, nIndex){
	      self.appendMedia(media);
		});
	  }
	  else {
	    this.options.collection.forEach(function(media, nIndex){
		  if (!$('.slide[data-id='+media.id+']', self.el).length) {
		  	self.appendMedia(media);
		  }
		});	  
	  }
	  // update sort order
	  self.sort();
	  
      this.bRendered = true;

      return this;
    },
    checkSlideshow: function(){
	  if (this.options.collection.length) {
	  	// remove def photo
	    this.remove(-1);
	  }
	  else {
	    // append def photo
        var slide = new TrailSlideshowSlideView();
        slide.render();
        $(this.el).append(slide.el);      
	  }
    },
    appendMedia: function(media){
      var versions = media.get('versions');
      
      var slide = new TrailSlideshowSlideView({model: media});
      slide.render();
      $(this.el).append(slide.el);      
	},    
    remove: function(id){
      var bFound = false;
      
      var self = this;
    	
	  $('.slide', this.el).each(function(index) {
	  	if ($(this).attr('data-id') == id) {
	  	  $(this).remove();
	  	  bFound = true;
	  	}
	  });
	  if (bFound) {
	    // if not removing def then check we have a slide
	    if (id != -1) {
	      this.checkSlideshow();	  	
	    }
        // fire event
        app.dispatcher.trigger("TrailSlideshowView:mediaremove");                          		  	  
	  }
	},
    starSlide: function(mediaID){
	  // hide all    	
      $('.star_marker', $(this.el)).hide();
      // find the one to show
      var elSlide = $('.slide[data-id='+mediaID+']', $(this.el)); 
	  if (elSlide.length) {	  
      	$('.star_marker', elSlide).show();
	  }    	
    },	
    selectSlide: function(mediaID){
      this.gotoSlide(mediaID);
      // fire event
      app.dispatcher.trigger("TrailSlideshowView:mediaclick", mediaID);
    },           
    selectDefSlide: function(){
	  // select 1st element
	  $('.slide:first', this.el).each(function(index) {
	    self.gotoSlide($(this).attr('data-id'));
        // fire event
        app.dispatcher.trigger("TrailSlideshowView:mediaclick", $(this).attr('data-id'));
	  });
    },                       		
    gotoSlide: function(mediaID){
      var self = this, bAnimate = true;
                	
      // has the active slide changed?
	  if (this.nActiveID == mediaID) {
	    bAnimate = false;
	  }
    	
	  var elSlides = $('.slide', this.el);
      this.nActiveSlide = 0;
	  $('.photo', $(this.el)).removeClass('active');
	  elSlides.each(function(nSlide) {
	  	if ($(this).attr('data-id') == mediaID) {
	  	  self.nActiveSlide = nSlide;
	  	  $('.photo', this).addClass('active');
	  	}
	  });
	  // update nav
	  $('#slideshow_nav .curr').html(this.nActiveSlide+1);
	  $('#slideshow_nav .total').html(elSlides.length);
	  $('#slideshow_nav').show();
	  
      this.moveSlides(this.nActiveSlide, bAnimate);
      
      this.nActiveID = mediaID;          	
    },
    moveSlides: function(nActiveSlide, bAnimate){
	  var nWidth = 203, nX = 0;

	  var elSlides = $('.slide', this.el);
	  // position slides
	  elSlides.each(function(nSlide) {
	  	nX = 0;
	  	if (nSlide < nActiveSlide) {
	  	  nX = -(nWidth * Math.abs(nActiveSlide - nSlide));
	  	}
	  	else if (nSlide > nActiveSlide) {
	  	  nX = (nWidth * Math.abs(nActiveSlide - nSlide));
	  	}
	  	
	  	if (bAnimate) {
	  	  $(this).addClass('tb-move');	  	
	  	}
	  	else {
	  	  $(this).removeClass('tb-move');
	  	}
	  	
	  	$(this).css('left', nX);
	  });
    },    
    sort: function(){
      this.options.collection.sort();      
	  // update datetime attribs    	
      this.options.collection.forEach(function(media, nIndex){
	  	$('.slide[data-id='+media.id+']', this.el).attr('data-datetime', media.get('tags').datetime);
	  });
	  
	  var elSlides = $('.slide', this.el);
	  // sort    	    		  	 
	  elSlides.tsort({attr:'data-datetime'});	  
	  
	  var nActiveSlide = Math.floor(elSlides.length / 2);
	  
	  this.moveSlides(nActiveSlide, false);
	  
	  this.checkSlideshow();	  
      // fire event
      app.dispatcher.trigger("TrailSlideshowView:mediaupdate");                          		  
	},
    onTrailSlideshowSlideViewClick: function(trailGallerySlideView){
      this.selectSlide(trailGallerySlideView.model.id);
	}	
    
  });

  return TrailSlideshowView;
});
