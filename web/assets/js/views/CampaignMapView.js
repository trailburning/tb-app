define([
  'underscore', 
  'backbone',
  'views/TrailMapMediaMarkerView'  
], function(_, Backbone, TrailMapMediaMarkerView){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var CampaignMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMapViewTemplate').text());        
            
      this.elCntrls = this.options.elCntrls;            
      this.bRendered = false;
      this.map = null;
      this.polyline = null;
      this.arrLineCordinates = [];
      this.arrMapMediaViews = [];
      this.currMapMediaView = null;
      this.nMapView = MAP_STREET_VIEW;
      
      var self = this;
      
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      this.locationIcon = new LocationIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/location.png'});      
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
//      if (Modernizr.touch) {
//        $('.btn-tb', $(this.elCntrls)).addClass('touch_btn');
//        $('.btn-tb', $(this.elCntrls)).addClass('btn-tb-mega');
//      }      

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
        this.map.panTo(this.currMapMediaView.marker.getLatLng(), {animate: true, duration: 2});
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
    render: function(){
      var latlng = new L.LatLng(51.507351, -0.127758);
    	
      // already rendered?  Just update
      if (this.bRendered) {
        this.map.invalidateSize();
        this.map.setView(latlng, 12);	
        this.map.zoomOut(1, {animate: false});      	
        return;         
      }        
                
      var self = this;
                
      $(this.el).html(this.template());
                        
      this.map = L.mapbox.map('map_large', null, {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:true, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.8d4ad8ec');
      this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
      this.map.addLayer(this.layer_street);

      this.map.setView(latlng, 12);	

      this.buildBtns();           
                        
      this.bRendered = true;
                        
      return this;
    }    
  });

  return CampaignMapView;
});
