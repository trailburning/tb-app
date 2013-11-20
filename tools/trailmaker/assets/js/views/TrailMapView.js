define([
  'underscore', 
  'backbone',
  'views/TrailMapMediaMarkerView'    
], function(_, Backbone, TrailMapMediaMarkerView){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var TrailMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMapViewTemplate').text());        
      
      this.elCntrls = this.options.elCntrls;            
      this.bRendered = false;
      this.map = null;
      this.polyline = null;
      this.arrLineCordinates = [];      
      this.arrMapMediaViews = [];
      this.nMapView = MAP_STREET_VIEW;      
      this.timezoneData = null;      
    },         
    buildBtns: function(){
      var self = this;

      // make btns more touch friendly
      if (Modernizr.touch) {
        $('.btn-tb', $(this.elCntrls)).addClass('touch_btn');
        $('.btn-tb', $(this.elCntrls)).addClass('btn-tb-mega');
      }      

      $('.zoomin_btn', $(this.elCntrls)).click(function(evt){
        if(self.map.getZoom() < self.map.getMaxZoom()) {
          self.map.zoomIn();                  
          $('.zoomout_btn', $(self.elCntrls)).attr('disabled', false);
          // fire event
          app.dispatcher.trigger("TrailMapView:zoominclick", self);                
        }
        
        if(self.map.getZoom() >= self.map.getMaxZoom()-1) {
          $('.zoomin_btn', $(self.elCntrls)).attr('disabled', true);
        }
      });

      $('.zoomout_btn', $(self.elCntrls)).click(function(evt){
        if(self.map.getZoom() > self.map.getMinZoom()+2) {
          self.map.zoomOut();
          $('.zoomin_btn', $(self.elCntrls)).attr('disabled', false);
          // fire event
          app.dispatcher.trigger("TrailMapView:zoomoutclick", self);                
        }
        
        if(self.map.getZoom() <= self.map.getMinZoom()+3) {
          $('.zoomout_btn', $(self.elCntrls)).attr('disabled', true);
        }
      });
      
      $('.view_btn', $(this.elCntrls)).click(function(evt){
        switch (self.nMapView) {
          case MAP_SAT_VIEW:
            self.nMapView = MAP_STREET_VIEW;
            
            self.map.removeLayer(self.layer_sat);        
            self.map.addLayer(self.layer_street);                
            self.layer_street.redraw();
            
            $(this).text('Satellite');
            break;
            
          case MAP_STREET_VIEW:
            self.nMapView = MAP_SAT_VIEW;
          
            self.map.removeLayer(self.layer_street);        
            self.map.addLayer(self.layer_sat);  
            self.layer_sat.redraw();

            $(this).text('Map');
            break;          
        }
      });
    },
    setTimeZoneData: function(timezoneData){
      this.timezoneData = timezoneData;
    },
    render: function(){
      var self = this;
                  
      // first time
      if (!this.bRendered) {
        $(this.el).html(this.template());
  
        this.map = L.mapbox.map('trail_map', null, {dragging: true, touchZoom: false, scrollWheelZoom: false, doubleClickZoom: false, boxZoom: false, tap: false, zoomControl:false, zoomAnimation:true, attributionControl: false, center: [52.512303, 13.408813], zoom: 3});
        this.layer_street = L.mapbox.tileLayer('mallbeury.map-omeomj70');
        this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
        this.map.addLayer(this.layer_street);
        
        this.buildBtns();           
      }
      
      if (this.model.get('id')) {
        var self = this;
        var data = this.model.get('value');      
        $.each(data.route.route_points, function(key, point) {
          self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
        });
  
        var polyline_options = {
          color: '#44B6FC',
          opacity: 1,
          weight: 4,
          clickable: true
        };         
        this.polyline = L.polyline(self.arrLineCordinates, polyline_options).on('click', onClickTrail).addTo(this.map);          
        this.map.fitBounds(self.polyline.getBounds(), {padding: [30, 30]});
        
        function onClickTrail(e) {        
          var trailMapMediaMarkerView = new TrailMapMediaMarkerView({ model: self.model, map: self.map, latlng: e.latlng, timezoneData: self.timezoneData });
          trailMapMediaMarkerView.render();
          self.arrMapMediaViews.push(trailMapMediaMarkerView);        
        }
      }
      this.bRendered = true;
    }    
  });

  return TrailMapView;
});
