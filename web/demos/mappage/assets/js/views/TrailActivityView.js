define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailActivityView = Backbone.View.extend({
    defaults: {
  	  bReadonly: false
	},  	
  	className: "activity",
    initialize: function(){
      this.options = _.extend({}, this.defaults, this.options);
      this.template = _.template($('#trailActivityViewTemplate').text());
      
      this.bRendered = false;
    },
    render: function(){
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
                            
	  var self = this;                            
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      $(this.el).addClass('tb-avatar');
      $(this.el).addClass('tb-avatar-highlight');
      $(this.el).attr('data-id', this.model.id);

	  if (!this.options.bReadonly) {
	    $(this.el).click(function(evt){
	  	  if ($(this).hasClass('active')) {
	  	    $(this).removeClass('active');
            // fire event
            app.dispatcher.trigger("TrailActivityView:remove", self);                        	  	  
	  	  }
	  	  else {
	  	    $(this).addClass('active');
            // fire event
            app.dispatcher.trigger("TrailActivityView:add", self);                        	  	 
	  	  }
	    });
	  }	  
      return this;
    }
        
  });

  return TrailActivityView;
});
