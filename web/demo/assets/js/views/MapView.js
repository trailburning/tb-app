define([
  'underscore', 
  'backbone',
  'turf',
  'mapbox',
  'markercluster',
  'views/MarkerView',
  'views/DistanceMarkerView'
], function(_, Backbone, turf, mapbox, markercluster, MarkerView, DistanceMarkerView){
  
  var MapView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      var self = this;

      app.dispatcher.on("MarkerView:click", self.onMarkerViewClick, this);

      this.bRendered = false;
      this.currMarkerView = null;

      L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

      this.map = L.mapbox.map('mapbox-view', 'mallbeury.8d4ad8ec', {dragging: true, touchZoom: false, scrollWheelZoom: false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:true, zoomAnimation:true, markerZoomAnimation:true, attributionControl:false, minZoom: 2});

      this.markerLayer = L.layerGroup();

      this.markerCluster = new L.MarkerClusterGroup({ showCoverageOnHover: false, spiderfyOnMaxZoom: true,
        iconCreateFunction: function(cluster) {
          var strClass = 'tb-map-cluster small';
          if (cluster._childCount > 9) {
            strClass = 'tb-map-cluster medium';
          }         
          if (cluster._childCount > 99) {
            strClass = 'tb-map-cluster large';
          } 

          // look for 1st marker and use image
          var markers = cluster.getAllChildMarkers();
          var firstMarker = _.find(markers, function(marker) {
            if ($(".avatar img", marker.options.icon.options.html).attr('src')) {
              return marker;
            }
          });

          var strImage = $(".avatar img", firstMarker.options.icon.options.html).attr('src');
          return new L.DivIcon({ className: strClass, html: '<div class="icon"><div class="avatar"><img src="'+strImage+'"></div><div class="overlay"></div></div><div class="cluster-counter"><div class="counter">' + cluster.getChildCount() + '</div></div>', iconSize: [60, 60], iconAnchor: [30, 30] });          
        }
      });

      this.markerCluster.on('animationend', function(evt){
        if (self.currMarkerView) {
          self.currMarkerOrCluster = self.markerCluster.getVisibleParent(self.currMarkerView.marker);
          if (self.currMarkerOrCluster) {
            $(self.currMarkerOrCluster._icon).addClass('selected');

            if (self.currMarkerView) {
              self.currMarkerView.focus();
            }
          }
        }
      }, this);

      this.markerCluster.on('clustermouseover', function (evt) {
        $(evt.layer._icon).addClass('focus');
      });

      this.markerCluster.on('clustermouseout', function (evt) {
        $(evt.layer._icon).removeClass('focus');
      });
	  },

    setSelected: function(nSelected){
      var self = this;

      var modelPost = this.options.collectionPosts.at(nSelected);
      if (!modelPost) {
        return false;
      }

      if (this.currMarkerOrCluster) {
        $(this.currMarkerOrCluster._icon).removeClass('selected');
        this.currMarkerOrCluster = null;
      }

      var markerView = modelPost.markerView;

      if (this.currMarkerView) {
        this.currMarkerView.blur();  
      }
      this.currMarkerView = markerView;

      this.currMarkerOrCluster = this.markerCluster.getVisibleParent(this.currMarkerView.marker);
      if (this.currMarkerOrCluster) {
        $(this.currMarkerOrCluster._icon).addClass('selected');
      }             

      self.currMarkerView.focus();
      this.options.map.panTo(new L.LatLng(modelPost.get("lat"), modelPost.get("lng")), {animate: true});
    },
    
    render: function(){
      var self = this;

      // already rendered?  Just update
      if (this.bRendered) {
        return;
      }

      this.map.setView([this.options.jsonRoute.geometry.coordinates[0][1], this.options.jsonRoute.geometry.coordinates[0][0]], 12);

      this.map.featureLayer.setGeoJSON(this.options.jsonRoute);

      this.map.invalidateSize(false);
      this.map.fitBounds(this.map.featureLayer.getBounds(), {padding: [100, 100], reset: true});

      function onLayerAdd() {
        // the focussed marker/cluster may not be available due to out of bounds layers not be rendered.
        // so when a layer is added force a re-focus.
        if (self.currMarkerView) {
          self.currMarkerOrCluster = self.markerCluster.getVisibleParent(self.currMarkerView.marker);
          if (self.currMarkerOrCluster) {
            $(self.currMarkerOrCluster._icon).addClass('selected');
            self.currMarkerView.focus();  
          }
        }
      }
      this.map.on("layeradd", onLayerAdd);

      this.addDistanceMarkers();
      this.map.addLayer(this.markerLayer);

      $.each(this.options.jsonMedia, function(index, jsonMedia) {
        var markerView = new MarkerView({parentID: MAP_VIEW, pos: index, jsonMedia: jsonMedia, map: self.options.map, mapLayer: self.markerCluster});
        markerView.render();
      });
      this.map.addLayer(this.markerCluster);

      this.bRendered = true;

      return this;
    },

    addDistanceMarker: function(nKM) {
      var along = turf.along(this.options.jsonRoute, nKM, 'kilometers');
      var modelDistance = new Backbone.Model({lat: along.geometry.coordinates[1], lng: along.geometry.coordinates[0], distance: nKM});
      var distanceMarkerView = new DistanceMarkerView({model: modelDistance, layer: this.markerLayer, map: this.map});
      distanceMarkerView.render();
    },

    addDistanceMarkers: function() {
      var length = turf.lineDistance(this.options.jsonRoute, 'kilometers');
      var nInc = 1;
      if (length > 10) {
        nInc = 2;
      }
      if (length > 20) {
        nInc = 5;
      }
      var nMarkers = Math.floor(length / nInc);
      var nCurrMarker = 0;

      for (var nMarker=0; nMarker <= nMarkers; nMarker += 1) {
        nCurrMarker = nInc * nMarker;
        if (nCurrMarker) {
          this.addDistanceMarker(nCurrMarker);
        }
      }
    },

    onMarkerViewClick: function(markerView){
      // fire event
      app.dispatcher.trigger("MapView:click", markerView.options.model);
    }
	
  });

  return MapView;
});
