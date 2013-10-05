define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMediaMarkerView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMediaMarkerViewTemplate').text());
      
      this.pos = this.options.pos;
    },            
    setActive: function(bActive){
      if (bActive) {
        $('.marker', this.el).addClass('marker_active');        
        $('.marker', this.el).css('zIndex', 200);
      }
      else {
        $('.marker', this.el).removeClass('marker_active');        
        $('.marker', this.el).css('zIndex', 100);
      }
    },    
    render: function(){
      var self = this;
      
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      var elMarker = $('.marker', this.el); 

      elMarker.mouseover(function(evt){              
        $(evt.currentTarget).css('cursor','pointer');      
      });    

      elMarker.click(function(evt){
        // fire event
        app.dispatcher.trigger("TrailMediaMarkerView:mediaclick", self);                
      });
      
      return this;
    }    
  });

  return TrailMediaMarkerView;
});
