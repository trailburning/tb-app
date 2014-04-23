define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var EventsEventCardView = Backbone.View.extend({
  	className: "panel",
    initialize: function(){
      this.template = _.template($('#eventsEventCardViewTemplate').text());        
            
      this.bRendered = false;
    },            
    render: function(){
      var self = this;

      if (!this.bRendered) {
      	if (this.model) {
      	  // format title
      	  var strTitle = this.model.get('title');
      	  if (this.model.get('title2')) {
      	  	strTitle += ' ' + this.model.get('title2');
      	  }
      	  this.model.set('title_text', strTitle);      	  
      	  // format date
	 	  var strDate = $.format.date(this.model.get('date') + ' 00:00:00', 'dd MMM yyyy');
	 	  if (this.model.get('date_to')) {
	 	  	strDate = $.format.date(this.model.get('date') + ' 00:00:00', 'dd MMM') + ' to ' + $.format.date(this.model.get('date_to') + ' 00:00:00', 'dd MMM yyyy');
	 	  }
      	  this.model.set('date_text', strDate);
      	}
      	      	
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        $(this.el).addClass('event_card_panel');
      
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
	  }
      this.bRendered = true;
                       
      return this;
    }    
  });

  return EventsEventCardView;
});
