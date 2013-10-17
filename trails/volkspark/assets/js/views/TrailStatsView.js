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
      function formatAltitude(nStr){
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
          x1 = x1.replace(rgx, '$1' + '’' + '$2');
        }
        return x1 + x2;
      }
      
      if (!this.model) {
        return;
      }

      var jsonRoute = this.model.get('value').route;

      if (!this.bRendered) {
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        
        var elTrailLength = $('.length', $(this.el));
        if (elTrailLength.length) {
          if (elTrailLength.html() == '') {
            elTrailLength.html('<h1>'+Math.floor(jsonRoute.length/1000)+' km</h1><h2>Length</h2>');
          }
        }
        
        var elTrailTerrain = $('.terrain', $(this.el));
        if (elTrailTerrain.length) {
          if (elTrailTerrain.html() == '') {
            elTrailTerrain.html('<h1>'+formatAltitude(Math.floor(jsonRoute.tags.ascent))+' m</h1><h2>D+ / '+formatAltitude(Math.floor(jsonRoute.tags.descent))+'m D-</h2>');
          }
        }
      }                             
      return this;
    }    
  });

  return TrailStatsView;
});
