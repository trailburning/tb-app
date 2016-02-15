var MAX_ASSETS = 3;

define([
  'underscore', 
  'backbone',
  'turf',
  'mapbox',
  'views/MapView',
  'views/DistanceMarkerView'
], function(_, Backbone, turf, mapbox, MapView, DistanceMarkerView){

  var AssetsView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#assetsViewTemplate').text());

      var self = this;

      this.jsonRoute = {
        "type": "Feature",
        "properties": {
        "name": this.model.get('name'),
        "color": "#000"
        },
        "geometry": {
          "type": "LineString",
          "coordinates": []
        }
      };

      this.markerLayer = L.layerGroup();

      var nMaxAssets = MAX_ASSETS;
      this.jsonBlocks = {blocks: []};
      var jsonBlock = {assets: []};

      function writeBlock() {
        // push curr block
        if (jsonBlock.assets.length) {
          self.jsonBlocks.blocks.push(jsonBlock);
          jsonBlock = null;
        }
      }

      function createBlock() {
        // create block for this asset and push
        jsonBlock = {assets: []};
      }

      function pushAsset(jsonAsset) {
        jsonBlock.assets.push(jsonAsset);
      }

      function processAsset(jsonAsset) {
        if (jsonAsset.about != undefined) {
          writeBlock();

          createBlock();
          pushAsset(jsonAsset);
          writeBlock();

          createBlock();
        }
        else if (jsonBlock.assets.length == nMaxAssets) {
          writeBlock();

          createBlock();
          pushAsset(jsonAsset);

          if (nMaxAssets == MAX_ASSETS) {
            nMaxAssets = MAX_ASSETS - 1;
          }
          else {
            nMaxAssets = MAX_ASSETS;
          }
        }
        else {
          pushAsset(jsonAsset);
        }
      }

      // setup blocks
      $.each(this.options.jsonAssets, function(index, jsonAsset) {
        // mla - test text
        var fRnd = Math.floor(Math.random() * (4 - 0 + 1)) + 0;
        if (fRnd == 4) {
//        if (index == 0) {
          jsonAsset.about = 'This is example text used to describe this piece of media.';
        }

        if (jsonBlock.assets.length == (nMaxAssets - 1)) {
          var fRnd = Math.floor(Math.random() * (4 - 0 + 1)) + 0;
          if (fRnd == 4) {
            // add placeholder
            var jsonAdAsset = {type: 'advert', filename: 'http://tbassets2.imgix.net/images/competition/_0001_sky.jpg', tags: {height: 806, width: 806}};
            processAsset(jsonAdAsset);
          }
        }

        processAsset(jsonAsset);
      });
      //one left to push
      if (jsonBlock) {
        self.jsonBlocks.blocks.push(jsonBlock);
      }
    },
    
    resize: function(nHeightWideAspectPercent){
      $('.scale-container', $(this.el)).each(function(){
        var nHeight = ($(this).width() * nHeightWideAspectPercent) / 100;
        $(this).height(nHeight);
      });
    },

    render: function(){
      var self = this;

      this.model.set('assetBlocks', this.jsonBlocks);

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      // build geoJSON route
      $.each(this.model.get('route_points'), function(index) {
        self.jsonRoute.geometry.coordinates.push(this.coords);
      });

      L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';
      this.map = L.mapbox.map('mapbox-view', 'mallbeury.8d4ad8ec', {dragging: true, touchZoom: false, scrollWheelZoom: false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:true, markerZoomAnimation:true, attributionControl:false, minZoom: 2, maxZoom: 17})
      .setView([self.jsonRoute.geometry.coordinates[0][1], self.jsonRoute.geometry.coordinates[0][0]], 12);

      this.map.featureLayer.setGeoJSON(this.jsonRoute);

      this.map.invalidateSize(false);
      this.map.fitBounds(this.map.featureLayer.getBounds(), {padding: [100, 100], reset: true});

      this.mapView = new MapView({ map: this.map, elCntrls: '#view_map_btns', jsonMedia: this.options.jsonAssets });
      this.mapView.render();

      this.addDistanceMarkers();
      this.map.addLayer(this.markerLayer);

      return this;
    },

    addDistanceMarker: function(nKM) {
      var along = turf.along(this.jsonRoute, nKM, 'kilometers');
      var modelDistance = new Backbone.Model({lat: along.geometry.coordinates[1], lng: along.geometry.coordinates[0], distance: nKM});     
      var distanceMarkerView = new DistanceMarkerView({model: modelDistance, layer: this.markerLayer, map: this.map});
      distanceMarkerView.render();
    },

    addDistanceMarkers: function() {
      var length = turf.lineDistance(this.jsonRoute, 'kilometers');
      var nInc = 5;
      var nMarkers = Math.floor(length / nInc);
      var nCurrMarker = 0;

      for (var nMarker=0; nMarker <= nMarkers; nMarker += 1) {
        nCurrMarker = nInc * nMarker;
        if (nCurrMarker) {
          this.addDistanceMarker(nCurrMarker);
        }
      }
    }

  });

  return AssetsView;
});
