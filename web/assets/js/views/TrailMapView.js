define([
  'underscore', 
  'backbone',
  'views/TrailMapMediaMarkerView'  
], function(_, Backbone, TrailMapMediaMarkerView){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var TrailMapView = Backbone.View.extend({
    initialize: function(){
    	
		L.LatLng.prototype.bearingTo = function(other) {
		    var d2r  = L.LatLng.DEG_TO_RAD;
		    var r2d  = L.LatLng.RAD_TO_DEG;
		    var lat1 = this.lat * d2r;
		    var lat2 = other.lat * d2r;
		    var dLon = (other.lng-this.lng) * d2r;
		    var y    = Math.sin(dLon) * Math.cos(lat2);
		    var x    = Math.cos(lat1)*Math.sin(lat2) - Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
		    var brng = Math.atan2(y, x);
		    brng = parseInt( brng * r2d );
		    brng = (brng + 360) % 360;
		    return brng;
		};
    	
      this.elCntrls = this.options.elCntrls;            
      this.bRendered = false;
      this.bFullView = false;
      this.map = null;
      this.polyline = null;
      this.arrLineCordinates = [];
      this.arrMapMediaViews = [];
      this.currMapMediaView = null;
      this.nMapView = MAP_STREET_VIEW;
      
      var self = this;
    },            
    show: function(){
      $(this.el).fadeIn();      
      this.showDetail(true);      
    },
    hide: function(){
      $(this.el).fadeOut();
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
                
        if(self.map.getZoom() > 8) {
          self.showDetail(true);
        }
      });

      $('.zoomout_btn', $(this.elCntrls)).click(function(evt){
        if(self.map.getZoom() > self.map.getMinZoom()+3) {
          self.map.zoomOut();                  
          $('.zoomin_btn', $(self.elCntrls)).attr('disabled', false);
          // fire event
          app.dispatcher.trigger("TrailMapView:zoomoutclick", self);                
        }
        
        if(self.map.getZoom() <= self.map.getMinZoom()+4) {
          $('.zoomout_btn', $(self.elCntrls)).attr('disabled', true);
        }
        
        if(self.map.getZoom() <= 10) {
          self.showDetail(false);
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
    reset: function(){
      if (this.currMapMediaView) {
        this.currMapMediaView.setActive(false);
        
        var fLatLng;
        if (this.bFullView) {
//          fLatLng = this.red_polyline.getBounds().getCenter();
        }
        else {
//          fLatLng = this.white_polyline.getBounds().getCenter();
        }
//		this.map.panTo(fLatLng, {animate: true, duration: 1});
      }
    },    
    gotoMedia: function(nMedia){
      // restore previous
      if (this.currMapMediaView) {
        this.currMapMediaView.setActive(false);
      }
      
      if (this.arrMapMediaViews.length) {
        this.currMapMediaView = this.arrMapMediaViews[nMedia];
        if (this.currMapMediaView) {
          this.currMapMediaView.setActive(true);
          
          // get next point for bearing
          var nNextMedia = nMedia + 1;
          if (nNextMedia >= this.arrMapMediaViews.length) {
            nNextMedia = 0;
          }
          var nextMapMediaView = this.arrMapMediaViews[nNextMedia];
		  
		  var currLatLng = L.latLng(this.currMapMediaView.model.get('coords').lat, this.currMapMediaView.model.get('coords').long);
		  var nextLatLng = L.latLng(nextMapMediaView.model.get('coords').lat, nextMapMediaView.model.get('coords').long);
 
 		  var nBearing = currLatLng.bearingTo(nextLatLng);
 
          // centre on active marker
          this.map.stop();
//          this.map.flyTo([this.currMapMediaView.model.get('coords').lat, this.currMapMediaView.model.get('coords').long], 15, nBearing, {speed: 0.4, curve: 0.4});          
          this.map.easeTo([this.currMapMediaView.model.get('coords').lat, this.currMapMediaView.model.get('coords').long], 16, nBearing, {duration: 2000});          
        }
      }
    },
    addMedia: function(mediaModel){
      var trailMapMediaMarkerView = new TrailMapMediaMarkerView({ map: this.map, model: mediaModel });
      this.arrMapMediaViews.push(trailMapMediaMarkerView);
    },
    showDetail: function(bShow){
      if (!this.arrMapMediaViews.length) {
        return;
      }

      if (bShow) {
        $('.leaflet-overlay-pane').show();
      }
      else {
        $('.leaflet-overlay-pane').hide();        
      }

      var trailMapMediaView = null;
      for (var nMedia=0; nMedia < this.arrMapMediaViews.length; nMedia++) {
        trailMapMediaView = this.arrMapMediaViews[nMedia];
        if (bShow) {
          trailMapMediaView.show();
        }
        else {
          trailMapMediaView.hide();          
        }
      }
    },    
    enablePopups: function(bEnable){
      var trailMapMediaView = null;
      for (var nMedia=0; nMedia < this.arrMapMediaViews.length; nMedia++) {
        trailMapMediaView = this.arrMapMediaViews[nMedia];
        trailMapMediaView.enablePopup(bEnable);
      }
    },    
    renderMarkers: function(){
      if (!this.arrMapMediaViews.length) {
        return;
      }      	        
      
      var self = this;
      
		mapboxgl.util.getJSON('https://www.mapbox.com/mapbox-gl-styles/styles/outdoors-v6.json', function (err, style) {
		  if (err) throw err;
		  		
		  style.layers.push({
		    "id": "route",
		    "type": "line",
		    "source": "route",
		    "layout": {
		      "line-join": "round",
		      "line-cap": "round"
		    },
		    "paint": {
		      "line-color": "#44B6F7",
		      "line-width": 3
		    }
		  });

		  style.layers.push({
		    "id": "markers",
		    "type": "symbol",
		    "source": "markers",
		    "layout": {
		      "icon-image": "{marker-symbol}-12",
		      "text-field": "{title}",
		      "text-font": "Open Sans Semibold, Arial Unicode MS Bold",
		      "text-offset": [0, 0.6],
		      "text-anchor": "top"
		    },
		    "paint": {
		      "text-size": 16
		    }
		  });

		  self.map = new mapboxgl.Map({
			  container: self.el.id,
			  style: style,
			  center: [40, -74.50]
			});

		 var geoJSON = {
		    "type": "Feature",
		    "properties": {},
		    "geometry": {
		      "type": "LineString",
		      "coordinates": []
		    }
		  };		
		  var route = new mapboxgl.GeoJSONSource({ data: geoJSON });
		  
	      var data = self.model.get('value');      
	      $.each(data.route.route_points, function(key, point) {
	        self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
		    route._data.geometry.coordinates.push([Number(point.coords[0]), Number(point.coords[1])]);
	      });
		  		  		  
		 var geoJSON = {
		    "type": "FeatureCollection",
		    "features": []};		
		  var markers = new mapboxgl.GeoJSONSource({ data: geoJSON });
	  	  
	      var trailMapMediaView = null;
	      for (var nMedia=0; nMedia < self.arrMapMediaViews.length; nMedia++) {
	        trailMapMediaView = self.arrMapMediaViews[nMedia];
	        trailMapMediaView.render(markers);
	      }
	      
		  self.map.addSource('route', route);
	  	  self.map.addSource('markers', markers);      
	  	  
	  	  self.map.setView([self.arrLineCordinates[0][0], self.arrLineCordinates[0][1]], 13, 0);	  	  
      });
    },        
    render: function(){
      if (!this.model) {
        return;
      }

      if (!this.model.get('id')) {
        return;
      }

      var contentWidth = $(document).width() / 2;
       
      // already rendered?  Just update
      if (this.bRendered) {
//        this.map.invalidateSize();
//        this.map.fitBounds(this.red_polyline.getBounds(), {padding: [30, 30], animate: false});
//        this.map.fitBounds(this.red_polyline.getBounds(), {paddingTopLeft: [30, 30], paddingBottomRight: [contentWidth/2, 30], animate: false});
        return;         
      }        
                
      var self = this;
                        
//      this.map = L.mapbox.map(this.el.id, null, {dragging: true, touchZoom: false, scrollWheelZoom:true, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:true, attributionControl:false});

               
      this.buildBtns();           
                              
      this.bRendered = true;
                        
      return this;
    },
    setView: function(bFull){    	
      if (bFull == this.bFullView) {
      	return;
      }
/*      
      if (bFull) {
      	this.bFullView = true;
      	
      	this.red_polyline.addTo(this.map);
      	this.map.removeLayer(this.white_polyline);
      	
        this.map.removeLayer(this.layer_mini_street);
        this.map.addLayer(this.layer_full_street);  
        this.layer_full_street.redraw();
      }
      else {
      	this.bFullView = false;
      	
      	this.white_polyline.addTo(this.map);
      	this.map.removeLayer(this.red_polyline);
      	
        this.map.removeLayer(this.layer_full_street);
        this.map.addLayer(this.layer_mini_street);  
        this.layer_mini_street.redraw();      	
      }
      this.render();
*/      
    }
    
  });

  return TrailMapView;
});
