define([
  'underscore', 
  'backbone'
], function(_, Backbone){

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
		  	console.log('not found');
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
	  $(this.el).append('<div class="slide" data-id="'+media.id+'" data-datetime="'+media.get('tags').datetime+'"><img src="http://app.resrc.it/O=80/http://s3-eu-west-1.amazonaws.com/'+versions[0].path+'" class="resrc"></div>');
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
