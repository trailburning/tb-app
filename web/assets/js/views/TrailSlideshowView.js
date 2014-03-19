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
	  this.bRendered = false;
    },            
    render: function(){
      var self = this;

      // first time
      if (!this.bRendered) {
      	$(this.el).html(this.template());
      	
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
    appendMedia: function(media){
      var versions = media.get('versions');
      
      var slide = new TrailSlideshowSlideView({model: media});
      slide.render();
      $(this.el).append(slide.el);      
	},    
    remove: function(id){
      var self = this;
    	
	  $('.slide', this.el).each(function(index) {
	  	if ($(this).attr('data-id') == id) {
	  	  $(this).remove();
	  	}
	  });
	  // select 1st element
	  $('.slide:first', this.el).each(function(index) {
	    self.gotoSlide($(this).attr('data-id'));
        // fire event
        app.dispatcher.trigger("TrailSlideshowView:mediaclick", $(this).attr('data-id'));
	  });
      // fire event
      app.dispatcher.trigger("TrailSlideshowView:mediaupdate");                          		  	  
	},
    gotoSlide: function(mediaID){
      bAnimate = true;          	
      // has the active slide changed?
	  if (this.nActiveID == mediaID) {
	    bAnimate = false;
	  }
    	
	  var elSlides = $('.slide', this.el);
	  var nActiveSlide = 0;

	  $('.photo', $(this.el)).removeClass('active');
	  elSlides.each(function(nSlide) {
	  	if ($(this).attr('data-id') == mediaID) {
	  	  nActiveSlide = nSlide;
	  	  $('.photo', this).addClass('active');
	  	}
	  });
	  
      this.moveSlides(nActiveSlide, bAnimate);
      
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
	  
      // fire event
      app.dispatcher.trigger("TrailSlideshowView:mediaupdate");                          		  
	},
    onTrailSlideshowSlideViewClick: function(trailGallerySlideView){
      this.gotoSlide(trailGallerySlideView.model.id);
      // fire event
      app.dispatcher.trigger("TrailSlideshowView:mediaclick", trailGallerySlideView.model.id);                          	
	}	
    
  });

  return TrailSlideshowView;
});
