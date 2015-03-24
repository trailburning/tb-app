define([
  'underscore', 
  'backbone',
  'views/trailplayertour/MapView',
  'views/trailplayertour/StoryView',
  'views/trailplayertour/MarkerView'  
], function(_, Backbone, MapView, StoryView, MarkerView){
  var JourneyView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      this.bLocked = true;

      this.nZoom = this.options.nZoom;      

      this.nCurrPoint = 0;
      this.nNextPoint = 0;

      this.arrPoints = [];
      this.arrMarkers = [];
      this.arrLineCordinates = [];
      this.arrNormalizedLineCordinates = [];

      this.routeLine = {
        "type": "Feature",
        "properties": {},
        "geometry": {
          "type": "LineString",
          "coordinates": []
        }
      };
      this.routeLine.geometry.coordinates = this.arrLineCordinates;

      this.normalizedRouteLine = {
        "type": "Feature",
        "properties": {},
        "geometry": {
          "type": "LineString",
          "coordinates": []
        }
      };
      this.normalizedRouteLine.geometry.coordinates = this.arrNormalizedLineCordinates;

      this.mapView = new MapView({ el: $('.map-view', $(this.el)), arrPoints: this.arrPoints, arrMarkers: this.arrMarkers, nZoom: this.nZoom, nLabelWidth: this.options.nLabelWidth });      
      this.storyView = new StoryView({ el: $('.story-view .story-items', $(this.el)) });  

      $('.next-btn', $(this.el)).click(function(evt){
        self.nextPoint();
      });            

      $('.story-items', $(this.el)).click(function(evt){
        if ($('.fullscreen').length) {
          self.fullscreenClose();
        }
        else {
          self.fullscreenOpen();  
        }
      });            

      $('.fullscreen-btn', $(this.el)).click(function(evt){
        if ($('.rsDefault.rsFullscreen').length) {
          self.fullscreenClose();
        }
        else {
          self.fullscreenOpen();  
        }        
      });            

      // keyboard control
      $(document).keydown(function(e){
        switch (e.keyCode) {
          case 27: // close fullscreen
            e.preventDefault();
            self.fullscreenClose();
            break;
        }
      });      
    },
    render: function(){      
      if (this.options.strType == 'drive') {
        this.testHarnessJourney();
      }
      else {
        this.testHarnessTBTrail('mt_buller');
//        this.testHarnessTBTrail('park');
//        this.testHarness25Zero();
      }

      $('.story-view .title-item', $(this.el)).css('opacity', 1);

      this.bLocked = false;

    },
    testAddPoint: function(fLat, fLong, strTitle, strImage){      
      var latLng = new L.LatLng(fLat, fLong);
      this.arrPoints.push(latLng);
      this.arrMarkers.push(new MarkerView({lat: latLng.lat, long: latLng.lng, title: strTitle}));

      $("<div class='story-item fade-in'><div class='image-container fade-on-load'><img src='"+strImage+"' class='scale'></div></div>").appendTo($('.story-items', $(this.el)));
    },
    testHarnessJourney: function(){            
      this.testAddPoint(-37.75194, 144.91955, 'Melbourne Docklands', 'http://tbassets.imgix.net//assets.trailburning.com/images/tour/trailplayer/WP1 - MelbourneDocklandsTwilight_wikicommons.jpg?fm=jpg&q=80');
      this.testAddPoint(-37.87763, 145.32616, 'Ferny Creek', 'http://tbassets.imgix.net//assets.trailburning.com/images/tour/trailplayer/WP2_FernyCreek.jpg?fm=jpg&q=80');
      this.testAddPoint(-37.86184, 145.35719, 'Sassasfras', 'http://tbassets.imgix.net//assets.trailburning.com/images/tour/trailplayer/WP3_sassasfras.jpg?fm=jpg&q=80');
      this.testAddPoint(-37.72891, 145.37779, 'Yarra Valley', 'http://tbassets.imgix.net//assets.trailburning.com/images/tour/trailplayer/WP4 - Yarra_Valley,_vineyards_at_Yarra_Yering.jpg?fm=jpg&q=80');
      this.testAddPoint(-37.65311, 145.51924, 'Healesville', 'http://tbassets.imgix.net//assets.trailburning.com/images/tour/trailplayer/WP5 - Healesville_Hotel.JPG?fm=jpg&q=80');
      this.testAddPoint(-37.21242, 145.42568, 'Yea Wetlands', 'http://tbassets.imgix.net//assets.trailburning.com/images/tour/trailplayer/WP6-Yea_wetlands_1.JPG?fm=jpg&q=80');
      this.testAddPoint(-37.02884, 145.87020, 'Goulburn River', 'http://tbassets.imgix.net//assets.trailburning.com/images/tour/trailplayer/WP7 - Goulburn_River_High_Country_Rail_Trail_bridge_at_Bonnie_Doon copy.jpg?fm=jpg&q=80');
      this.testAddPoint(-37.13255, 146.45420, 'Mount Buller', 'http://tbassets.imgix.net//assets.trailburning.com/images/profile/mtbuller/brand_hero3.jpg?fm=jpg&q=80');

/*
      this.testAddPoint(50.07554, 14.43780, 'Prague', 'assets/img/prague.jpg');
      this.testAddPoint(52.52001, 13.40495, 'Berlin', 'assets/img/berlin.jpg');
      this.testAddPoint(55.67610, 12.56834, 'Copenhagen', 'assets/img/copenhagen.jpg');
      this.testAddPoint(51.50735, -0.12776, 'London', 'assets/img/london.jpg');
*/
      this.getDrivingRoute();

      this.storyView.render();
      this.mapView.render();
    },
    testHarnessTBTrail: function(strName){      
      this.getTBMedia(strName);
    },
    testHarness25Zero: function(){      
      this.testAddPoint(4.89056, -75.32373, 'Nevado del Ruiz', 'assets/img/mountains/Nevado-del-Ruiz66.jpg');
      this.testAddPoint(4.67000, -75.33000, 'Nevado del Tolima', 'assets/img/mountains/Nevado_del_Tolima.jpg');
      this.testAddPoint(4.81667, -75.36667, 'Nevado de Santa Isabel', 'assets/img/mountains/Nevado de Santa Isabel.jpg');
      this.testAddPoint(4.71276, -75.39975, 'Nevado del Quindío', 'assets/img/mountains/Quindio.jpg');
      this.testAddPoint(2.93000, -76.03000, 'Nevado del Huila', 'assets/img/mountains/Huila.jpg');
      this.testAddPoint(-1.46930, -78.81694, 'Chimborazo', 'assets/img/mountains/Chimborazo-volcano-2.jpg');
      this.testAddPoint(-0.433333, -78.483333, 'Pasachoa', 'assets/img/mountains/pasochoa.jpg');
      this.testAddPoint(-0.360833, -78.349167, 'Cotaccachi', 'assets/img/mountains/cotaccachi.jpg');
      this.testAddPoint(1.47, -78.4447, 'Tungurahua', 'assets/img/mountains/mountain-2-Tungurahua-3.jpg');
      this.testAddPoint(-0.680556, -78.437778, 'Cotapaxi', 'assets/img/mountains/Cotapaxi-volcano.jpg');

      this.storyView.render();
      this.mapView.render();
    },
    buildRoute: function(distance){
      var route = this.routeLine.geometry;

      var sampleQuant = Math.min(distance/50, this.options.nRoutePoints);
      var incrementDist = distance/sampleQuant;

      var linestring = turf.linestring(route.coordinates);

      var myIcon=L.divIcon({className: '', html: '<div class="pointmarker"><div class="point"></div></div>'});
      var fLat, fLng, along;
      for (var i=0;i<sampleQuant; i++){
        along = turf.along(linestring, incrementDist*i*0.001, 'kilometers');
        fLat = along['geometry']['coordinates'][1];
        fLng = along['geometry']['coordinates'][0];
        L.marker([fLat, fLng],{icon: myIcon}).addTo(this.mapView.map)
        this.arrNormalizedLineCordinates.push([fLat, fLng]);
      }
    },
    getDrivingRoute: function(){
      var self = this;

      var strPoints = '';
      for (var nPoint=0; nPoint < this.arrPoints.length; nPoint++) {
        strPoints += this.arrPoints[nPoint].lng + ',';
        strPoints += this.arrPoints[nPoint].lat;
        if (nPoint < this.arrPoints.length - 1) {
          strPoints += ';';
        }
      }

      var directionsURL = 'https://api.tiles.mapbox.com/v4/directions/mapbox.driving/'+ strPoints +'.json?access_token=pk.eyJ1IjoiZHVuY2FuZ3JhaGFtIiwiYSI6IlJJcWdFczQifQ.9HUpTV1es8IjaGAf_s64VQ';
      $.get(directionsURL, function(data){
        $.each(data.routes[0].geometry.coordinates, function(key, val) {
          self.arrLineCordinates.push([Number(val[0]), Number(val[1])]);
        });        
        self.buildRoute(data.routes[0].distance);
      });
    },  
    getTBRoute: function(strName){
      var self = this;

      var strPoints = '';
      for (var nPoint=0; nPoint < this.arrPoints.length; nPoint++) {
        strPoints += this.arrPoints[nPoint].lng + ',';
        strPoints += this.arrPoints[nPoint].lat;
        if (nPoint < this.arrPoints.length - 1) {
          strPoints += ';';
        }
      }

      $.getJSON(TB_DATA+'/'+strName+"_route.json", function(data) {
        var jsonRoute = data.value.route;
        var jsonRoutePoints = jsonRoute.route_points;

        $.each(jsonRoutePoints, function(key, val) {
          self.arrLineCordinates.push([Number(val.coords[0]), Number(val.coords[1])]);
        });        
        self.buildRoute(jsonRoute.length);
      });
    },       
    getTBMedia: function(strName){
      var self = this;

      $.getJSON(TB_DATA+'/'+strName+"_media.json", function(data) {
        var jsonMedia = data.value
        $.each( jsonMedia, function( key, val ) {
          self.arrPoints.push(new L.LatLng(val.coords.lat, val.coords.long));
          self.arrMarkers.push(new MarkerView({lat: val.coords.lat, long: val.coords.long, title: 'elevation ' + Math.round(val.tags.altitude) + 'm'}));
          $("<div class='story-item fade-in'><div class='image-container fade-on-load'><img src='http://tbmedia.imgix.net//media.trailburning.com"+val.versions[0].path+"?fm=jpg&q=80&w=1024&fit=fill' class='scale'></div></div>").appendTo($('.story-items', $(self.el)));
        });

        self.storyView.render();
        self.mapView.render();
        self.getTBRoute(strName);
      });
    },
    fullscreenOpen: function(){
      $('.rsDefault', $(this.el)).addClass('rsFullscreen');
      $('.story-view', $(this.el)).addClass('fullscreen');
      $('.scale-image-ready').each(function(index) {
          // update pos
          $(this).imageScale();
      });
    },
    fullscreenClose: function(){
      $('.rsDefault', $(this.el)).removeClass('rsFullscreen');
      $('.story-view', $(this.el)).removeClass('fullscreen');
      $('.scale-image-ready').each(function(index) {
          // update pos
          $(this).imageScale();
      });
    },
    nextPoint: function(){
      if (this.bLocked) {
        return;
      }
      this.bLocked = true;

      var self = this;

      if (this.nCurrPoint != this.nNextPoint) {
        this.storyView.hide(this.nCurrPoint);        
      }    
      else {
        $('.story-view .title-item', $(this.el)).css('opacity', 0);
      }

      this.nCurrPoint = this.nNextPoint;

      $('.locationlabel', $(this.el)).hide();
      var elMarker = $('.locationmarker:eq('+this.nCurrPoint+')', $(this.el));
      if (elMarker.length) {
        $('.locationlabel', elMarker).show();
      }
      this.storyView.show(this.nCurrPoint);      

      this.nNextPoint = this.nCurrPoint + 1;
      if (this.nNextPoint >= this.arrPoints.length) {
        this.nNextPoint = 0;
      }

      var currLatLng = this.arrPoints[this.nCurrPoint];
      var nextLatLng = this.arrPoints[this.nNextPoint];

      // look up closest point on line
      if (this.arrNormalizedLineCordinates.length) {
        var pt = {
          "type": "Feature",
          "properties": {},
          "geometry": {
            "type": "Point",
            "coordinates": [currLatLng.lat, currLatLng.lng]
          }
        };

        // retrieve snapped point   
        var snapped = turf.pointOnLine(this.normalizedRouteLine, pt);
        var nIndex = snapped.properties.index+2;
        if (nIndex >= self.arrNormalizedLineCordinates.length) {
          nIndex = 0;
        }
        var point = self.arrNormalizedLineCordinates[nIndex]; 
        nextLatLng = new L.LatLng(point[0], point[1]); 
      }

      var point1 = {
        "type": "Feature",
        "geometry": {
          "type": "Point",
          "coordinates": [currLatLng.lng, currLatLng.lat]
        }
      };

      var point2 = {
        "type": "Feature",
        "geometry": {
          "type": "Point",
          "coordinates": [nextLatLng.lng, nextLatLng.lat]
        }
      };

      var nBearing = Math.round(turf.bearing(point1, point2));

      nBearing = -nBearing;
      this.mapView.moveMap(currLatLng, nBearing);

      setTimeout(function(){ 
        self.bLocked = false;
      }, 500);      
    }

  });

  return JourneyView;
});
