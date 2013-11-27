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

      var self = this;
      
      app.dispatcher.on("TrailMapMediaMarkerView:mediaclick", self.onTrailMapMediaMarkerClick, this);
      app.dispatcher.on("TrailMapMediaMarkerView:removemedia", self.onTrailMapMediaMarkerRemove, this);
      
      this.elCntrls = this.options.elCntrls;            
      this.bRendered = false;
      this.map = null;
      this.polyline = null;
      this.arrLineCordinates = [];      
      this.arrMapMediaViews = [];      
      this.collectionMedia = new Backbone.Collection();      
      this.currMapMediaMarkerView = null;
      this.nMapView = MAP_STREET_VIEW;      
      this.timezoneData = null;      
      
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      this.locationIcon = new LocationIcon({iconUrl: ASSETS_BASEURL + 'images/icons/location.png'});      
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
    addMarker: function(latlng, bPlaceOnTrail, strName){
      var model = new Backbone.Model();
      model.set('name', strName);
      this.collectionMedia.add(model);
                      
      var trailMapMediaMarkerView = new TrailMapMediaMarkerView({ model: model, trailModel: this.model, map: this.map, latlng: latlng, timezoneData: this.timezoneData, placeOnTrail: bPlaceOnTrail });
      trailMapMediaMarkerView.render();
      this.arrMapMediaViews.push(trailMapMediaMarkerView);        
    },
    render: function(){
      var self = this;
                  
      // first time
      if (!this.bRendered) {
        $(this.el).html(this.template());
  
        this.map = L.mapbox.map('trail_map', null, {dragging: true, touchZoom: false, scrollWheelZoom: false, doubleClickZoom: false, boxZoom: false, tap: false, zoomControl:false, zoomAnimation:true, attributionControl: false, center: [52.512303, 13.408813], zoom: 3});
        this.layer_street = L.mapbox.tileLayer('mallbeury.gchl1fm0');
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
        
        function onClickTrail(e) {
          self.addMarker(e.latlng, true, '');
        }
        this.polyline = L.polyline(self.arrLineCordinates, polyline_options).on('click', onClickTrail).addTo(this.map);
        
        L.marker(this.arrLineCordinates[0], {icon: this.locationIcon}).addTo(this.map);            
                  
        this.map.fitBounds(self.polyline.getBounds(), {padding: [30, 30]});
        
      }
      
      // mla test markers
/*      
      this.addMarker(L.latLng(46.622476896924, 8.0464107859594), false, '1');
      this.addMarker(L.latLng(46.658620747696, 8.0984256320652), false, '2');
      this.addMarker(L.latLng(46.663585093091, 8.0955462694098), false, '3');
      this.addMarker(L.latLng(46.668922259321, 8.0240646224825), false, '4');
      this.addMarker(L.latLng(46.674453837122, 7.9973328393055), false, '5');
      this.addMarker(L.latLng(46.673117319214, 7.9947052562556), false, '6');
      this.addMarker(L.latLng(46.670469233088, 7.9842234778627), false, '7');
      this.addMarker(L.latLng(46.66925091139, 7.9561153326779), false, '8');
      this.addMarker(L.latLng(46.661542917811, 7.9188035988499), false, '9');
      this.addMarker(L.latLng(46.655495656473, 7.9128708609304), false, '10');
      this.addMarker(L.latLng(46.617159377251, 7.9373395232013), false, '11');
      this.addMarker(L.latLng(46.581087491473, 7.9572291714001), false, '12');
*/
      this.bRendered = true;
    },
    onTrailMapMediaMarkerClick: function(mapMediaMarkerView){
      if (this.currMapMediaMarkerView) {
        this.currMapMediaMarkerView.setActive(false);
      }
      mapMediaMarkerView.setActive(true);
      
      this.currMapMediaMarkerView = mapMediaMarkerView;
    },
    onTrailMapMediaMarkerRemove: function(mapMediaMarkerView){
      // remove from collection      
      this.collectionMedia.remove(mapMediaMarkerView.model);      
    }        
  });

  return TrailMapView;
});
