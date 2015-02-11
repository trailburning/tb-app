define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailDetailView = Backbone.View.extend({
    initialize: function(){
    },            
    render: function(){
      var jsonRoute = this.model.get('value').route;
      
      var elTrailLength = $('.length .marker', $(this.el));
      if (elTrailLength.length) {
        elTrailLength.html(Math.ceil(jsonRoute.length/1000));
      }
        
      var elTrailTerrain = $('.ascent .marker');
      if (elTrailTerrain.length) {
        elTrailTerrain.html(formatAltitude(Math.floor(jsonRoute.tags.ascent)));
      }

      var elTrailTerrain = $('.descent .marker');
      if (elTrailTerrain.length) {
        elTrailTerrain.html(formatAltitude(Math.floor(jsonRoute.tags.descent)));
      }
                 
      return this;
	}    
    
  });

  return TrailDetailView;
});
