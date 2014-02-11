define([
  'underscore', 
  'backbone',
  'views/TrailSlideshowSlideView'
], function(_, Backbone, TrailSlideshowSlideView){

  var TrailSlideshowView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#slideshowViewTemplate').text());
    },            
    render: function(){
      var self = this;
	  this.bRendered = false;

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
	  $('.slide', this.el).each(function(index) {
	  	if ($(this).attr('data-id') == id) {
	  	  $(this).remove();
	  	}
	  });
	},
    sort: function(){
	  // update datetime attribs    	
      this.options.collection.forEach(function(media, nIndex){
	  	$('.slide[data-id='+media.id+']', this.el).attr('data-datetime', media.get('tags').datetime);
	  });
	  // sort    	    		  	 
	  $('.slide', this.el).tsort({attr:'data-datetime'});	  
	}	
    
  });

  return TrailSlideshowView;
});
