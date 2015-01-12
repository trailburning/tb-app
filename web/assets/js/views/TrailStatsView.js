define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailStatsView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailStatsViewTemplate').text());        
            
      this.bRendered = false;      
    },            
    render: function(){
      if (!this.model) {
        return;
      }
      
      if (!this.model.get('id')) {
        return;
      }
      
      if (!this.model) {
        return;
      }

	  var self = this;
      var jsonRoute = this.model.get('value').route;

      if (!this.bRendered) {
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        
        $('.play_btn', $(this.el)).click(function(evt){
          // fire event
          app.dispatcher.trigger("TrailStatsView:clickplay", self);                
	    });

        $('.play_btn', $(this.el)).mouseover(function(evt){
          $(evt.currentTarget).css('cursor','pointer');      
	    });

        $('.pause_btn', $(this.el)).click(function(evt){
          // fire event
          app.dispatcher.trigger("TrailStatsView:clickpause", self);                
	    });

        $('.pause_btn', $(this.el)).mouseover(function(evt){
          $(evt.currentTarget).css('cursor','pointer');      
	    });
      }                  
      this.bRendered = true;
                 
      return this;
    },
    setCurrSlide: function(nSlide){
      $('.slides .current', $(this.el)).html(nSlide);
    },
    setTotalSlides: function(nSlides){
      $('.slides .total', $(this.el)).html(nSlides);
    },    
    playerPlaying: function(){
      $('.play_btn', $(this.el)).hide();
      $('.pause_btn', $(this.el)).show();
	},    
    playerStopped: function(){
      $('.play_btn', $(this.el)).show();
      $('.pause_btn', $(this.el)).hide();
	}    
    
  });

  return TrailStatsView;
});
