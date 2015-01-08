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
      	$('.tb-map-marker', this.el).addClass('focus');
        $('.tb-map-marker', this.el).css('zIndex', 200);      	
//        $('.marker', this.el).addClass('marker_active');        
//        $('.marker', this.el).css('zIndex', 200);

        $('.alt', this.el).show();
        $('.alt', this.el).css({ opacity: 1 });
      }
      else {
      	$('.tb-map-marker', this.el).removeClass('focus');
        $('.tb-map-marker', this.el).css('zIndex', 100);      	
//        $('.marker', this.el).removeClass('marker_active');        
//        $('.marker', this.el).css('zIndex', 100);
        
        $('.alt', this.el).css({ opacity: 0 });
      }
    },    
    render: function(){
      var self = this;
      
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      var elAlt = $('.alt', this.el);
      elAlt.html(Math.round(this.model.get('tags').altitude) + ' m');
      elAlt.addClass('tb-fade');
      
      var elMarker = $('.tb-map-marker', this.el); 

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
