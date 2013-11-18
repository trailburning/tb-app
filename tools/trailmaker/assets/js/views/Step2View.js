define([
  'underscore', 
  'backbone',
  'views/TrailMapView'  
], function(_, Backbone, TrailMapView){

  var Step2View = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#step2ViewTemplate').text());        
      
      this.timezoneData = null;      
      this.bRendered = false;
    },
    getTimeZone: function(){
      var self = this;    
      
      var data = this.model.get('value');      
      var firstPoint = data.route.route_points[0];
      var nTimestamp = firstPoint.tags.datetime;
      
      var strURL = 'https://maps.googleapis.com/maps/api/timezone/json?location='+Number(firstPoint.coords[1])+','+Number(firstPoint.coords[0])+'&timestamp='+nTimestamp+'&sensor=false'; 
      
      $.ajax({
        url: strURL,
        type: 'GET',            
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
          self.timezoneData = data; 
          self.trailMapView = new TrailMapView({ el: '#trail_map_view', model: self.model, timezoneData: self.timezoneData });
          self.trailMapView.render();          
        },
      });
    },
    render: function(){
      console.log('R');
      
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
              
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
        
      var self = this;    
      // get trail    
      this.model.fetch({
        success: function () {
          self.getTimeZone();
        }      
      });        
        
      return this;
    }
  });

  return Step2View;
});
