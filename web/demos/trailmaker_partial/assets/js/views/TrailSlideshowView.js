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
	      var versions = media.get('versions');
		  $(self.el).append('<div class="slide" data-id="'+media.id+' data-datetime='+media.get('tags').datetime+'"><img src="http://app.resrc.it/O=80/http://s3-eu-west-1.amazonaws.com/'+versions[0].path+'" width="50" class="resrc"></div>');
		});
	  }
      this.bRendered = true;

      return this;
    },
    remove: function(id){
	  $('.slide', this.el).each(function(index) {
	  	if ($(this).attr('data-id') == id) {
	  	  $(this).remove();
	  	}
	  });
	}
    
  });

  return TrailSlideshowView;
});
