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
            elTrailLength.html('<h2 class="tb">'+Math.round(jsonRoute.length/1000)+' km</h2><div class="desc">Length</div>');
          }
        }
        
        var elTrailTerrain = $('.terrain', $(this.el));
        if (elTrailTerrain.length) {
          if (elTrailTerrain.html() == '') {
            elTrailTerrain.html('<h3 class="tb">'+formatAltitude(Math.floor(jsonRoute.tags.ascent))+' m</h3><div class="desc">D+ / '+formatAltitude(Math.floor(jsonRoute.tags.descent))+'m D-</div>');
          }
        }
      }                             
      return this;
    }    
  });

  return TrailStatsView;
});
