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

      $(this.el).html(this.template());

      this.options.collection.forEach(function(media, nIndex){
        var versions = media.get('versions');
		$(self.el).append('<img src="http://app.resrc.it/O=80/http://s3-eu-west-1.amazonaws.com/'+versions[0].path+'" width="50" class="resrc">');
	  });

      return this;
    }        
  });

  return TrailSlideshowView;
});
