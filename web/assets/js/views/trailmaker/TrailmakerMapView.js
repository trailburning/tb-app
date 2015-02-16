define([
  'underscore', 
  'backbone',
  'views/trailmaker/TrailmakerMapMediaMarkerView'    
], function(_, Backbone, TrailMapMediaMarkerView){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var TrailMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMapViewTemplate').text());        

      var self = this;
      
      app.dispatcher.on("TrailMapMediaMarkerView:mediaclick", self.onTrailMapMediaMarkerClick, this);
      app.dispatcher.on("TrailMapMediaMarkerView:removemedia", self.onTrailMapMediaMarkerRemove, this);
      app.dispatcher.on("TrailMapMediaMarkerView:mediamoved", self.onTrailMapMediaMarkerMoved, this);
      app.dispatcher.on("TrailMapMediaMarkerView:starmedia", self.onTrailMapMediaMarkerStarClick, this);
      
      this.elCntrls = this.options.elCntrls;            
      this.bRendered = false;
      this.map = null;
      this.polyline = null;
      this.arrLineCordinates = [];      
      this.arrMapMediaViews = [];      
      this.currMapMediaMarkerView = null;
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
    addMarker: function(jsonMedia, bPlaceOnTrail){
      var trailMapMediaMarkerView = new TrailMapMediaMarkerView({ model: jsonMedia, trailModel: this.model, map: this.map, arrLine: this.arrLineCordinates, timezoneData: this.timezoneData, placeOnTrail: bPlaceOnTrail });

      trailMapMediaMarkerView.render();
      this.arrMapMediaViews.push(trailMapMediaMarkerView);        
    },
    selectMarker: function(id){
      var self = this;
      // find marker
      $.each(this.arrMapMediaViews, function(index, trailMapMediaMarkerView) {
      	if (trailMapMediaMarkerView.model.id == id) {
		  self.focusMarker(trailMapMediaMarkerView);
		  trailMapMediaMarkerView.showPopup();
		  return false;
      	}
      });      	
    },
    focusMarker: function(mapMediaMarkerView){
      if (this.currMapMediaMarkerView) {
        this.currMapMediaMarkerView.setActive(false);
      }
      mapMediaMarkerView.setActive(true);
      this.currMapMediaMarkerView = mapMediaMarkerView;
	},    
    render: function(){
      var self = this;
                  
      // first time
      if (!this.bRendered) {
        $(this.el).html(this.template());
  
        this.map = L.mapbox.map('trail_map', null, {dragging: true, touchZoom: false, scrollWheelZoom: false, doubleClickZoom: false, boxZoom: false, tap: false, zoomControl:false, zoomAnimation:true, attributionControl: false, center: [52.512303, 13.408813], zoom: 3});
        this.layer_street = L.mapbox.tileLayer('mallbeury.8d4ad8ec');
        this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
        this.map.addLayer(this.layer_street);
        
        this.buildBtns();           
      }

      if (this.model.get('id')) {
        this.map.invalidateSize();
      	
        var self = this;
        var data = this.model.get('value');      
        $.each(data.route.route_points, function(key, point) {
          self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);
        });

        var polyline_options = {
          color: '#ed1c24',
          opacity: 1,
          weight: 4,
          clickable: false,
          distanceMarkers: { lazy: true }
        };         

        this.polyline = L.polyline(self.arrLineCordinates, polyline_options).addTo(this.map);

	    var marker = L.marker(this.arrLineCordinates[0]).addTo(this.map);			        
        marker.setIcon(L.divIcon({className: 'tb-map-location-marker', html: '<div class="marker"></div>', iconSize: [22, 30], iconAnchor: [11, 30],}));
	    $(marker._icon).addClass('selected');
                  
        this.map.fitBounds(self.polyline.getBounds(), {padding: [30, 30]});
      }
      
      this.bRendered = true;
    },
    onTrailMapMediaMarkerClick: function(mapMediaMarkerView){
      this.focusMarker(mapMediaMarkerView);
      // fire event
      app.dispatcher.trigger("TrailMapView:mediaclick", mapMediaMarkerView.model.id);                        		       
    },
    onTrailMapMediaMarkerRemove: function(mapMediaMarkerView){
      var self = this;
  
      // fire event
      app.dispatcher.trigger("TrailMapView:removemedia", mapMediaMarkerView.model.id);                        		       
      
      // find point in arr      
      $.each(this.arrMapMediaViews, function(key, currMapMediaMarkerView) {
      	if (currMapMediaMarkerView.model.id == mapMediaMarkerView.model.id) {
      	  // remove
      	  self.arrMapMediaViews.splice(key, 1);
      	  return false;
      	}
      });    	    	
    },
    onTrailMapMediaMarkerMoved: function(mapMediaMarkerView){
      // fire event
      app.dispatcher.trigger("TrailMapView:movedmedia", mapMediaMarkerView.model.id);                        		       
    },    
    onTrailMapMediaMarkerStarClick: function(mapMediaMarkerView){
      // fire event
      app.dispatcher.trigger("TrailMapView:starmedia", mapMediaMarkerView.model.id);                        		       
    }    
            
  });

  return TrailMapView;
});
