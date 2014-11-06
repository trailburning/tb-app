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
        
/*        
        var elTrailLength = $('.length', $(this.el));
        if (elTrailLength.length) {
          if (elTrailLength.html() == '') {
            elTrailLength.html('<h2 class="tb">'+Math.ceil(jsonRoute.length/1000)+' km</h2><div class="desc">Length</div>');
          }
        }
        
        var elTrailTerrain = $('.terrain', $(this.el));
        if (elTrailTerrain.length) {
          if (elTrailTerrain.html() == '') {
            elTrailTerrain.html('<h3 class="tb">'+formatAltitude(Math.floor(jsonRoute.tags.ascent))+' m</h3><div class="desc">D+ / '+formatAltitude(Math.floor(jsonRoute.tags.descent))+'m D-</div>');
          }
        }
*/        
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
