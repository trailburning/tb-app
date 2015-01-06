define([
  'underscore', 
  'backbone',
  'views/TrailMapMediaMarkerView'  
], function(_, Backbone, TrailMapMediaMarkerView){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var TrailMapView = Backbone.View.extend({
    initialize: function(){
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
    gotoMedia: function(nMedia){
      // restore previous
      if (this.currMapMediaView) {
        this.currMapMediaView.setActive(false);
      }
      
      if (this.arrMapMediaViews.length) {
        this.currMapMediaView = this.arrMapMediaViews[nMedia];
        this.currMapMediaView.setActive(true);
        // centre on active marker
        this.map.panTo(this.currMapMediaView.marker.getLatLng(), {animate: true, duration: 1});
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
      
      var trailMapMediaView = null;
      for (var nMedia=0; nMedia < this.arrMapMediaViews.length; nMedia++) {
        trailMapMediaView = this.arrMapMediaViews[nMedia];
        trailMapMediaView.render();
      }
      
	  var marker = L.marker(this.arrLineCordinates[0]).addTo(this.map);			        
      marker.setIcon(L.divIcon({className: 'tb-map-location-marker', html: '<div class="marker"></div>', iconSize: [22, 30], iconAnchor: [11, 30],}));
    },        
    render: function(){
      if (!this.model) {
        return;
      }

      if (!this.model.get('id')) {
        return;
      }
       
      // already rendered?  Just update
      if (this.bRendered) {
        this.map.invalidateSize();
        if (this.bFullView) {
          this.map.fitBounds(this.red_polyline.getBounds(), {padding: [30, 30], animate: false});
        }
        else {
          this.map.fitBounds(this.white_polyline.getBounds(), {padding: [30, 30], animate: false});        	
        }
        return;         
      }        
                
      var self = this;
                        
      this.map = L.mapbox.map(this.el.id, null, {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:true, attributionControl:false});
      this.layer_mini_street = L.mapbox.tileLayer('mallbeury.8f5ac718');     
      this.layer_full_street = L.mapbox.tileLayer('mallbeury.8d4ad8ec');
      this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
      this.map.addLayer(this.layer_mini_street);

	  this.map.on('click', function() {
        for (var nMedia=0; nMedia < self.arrMapMediaViews.length; nMedia++) {
          self.arrMapMediaViews[nMedia].hidePopup();
        }
	  });

      var data = this.model.get('value');      
      $.each(data.route.route_points, function(key, point) {
        self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      });

      var white_polyline_options = {
        color: '#FFF',
        opacity: 1,
        weight: 4,
        clickable: false,
    	distanceMarkers: { lazy: true }
      };         
      this.white_polyline = L.polyline(self.arrLineCordinates, white_polyline_options).addTo(this.map);

      var red_polyline_options = {
        color: '#ed1c24',
        opacity: 1,
        weight: 4,
        clickable: false,
    	distanceMarkers: { lazy: true }
      };         
      this.red_polyline = L.polyline(self.arrLineCordinates, red_polyline_options);
  
      this.map.fitBounds(self.white_polyline.getBounds(), {padding: [30, 30]});
               
      this.buildBtns();           
      
      this.renderMarkers();                        
                        
      this.bRendered = true;
                        
      return this;
    },
    setView: function(bFull){    	
      if (bFull == this.bFullView) {
      	console.log('RET');
      	return;
      }
      
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
    }
    
  });

  return TrailMapView;
});
