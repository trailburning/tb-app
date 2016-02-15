define([
  'underscore', 
  'backbone',
  'mapbox',
  'markercluster',
  'views/MarkerView'
], function(_, Backbone, mapbox, markercluster, MarkerView){
  
  var MapView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      var self = this;

      app.dispatcher.on("MarkerView:click", self.onMarkerViewClick, this);

      this.bRendered = false;
      this.currMarkerView = null;

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
        self.updateZoomCtrls();
      }, this);

      this.markerCluster.on('clustermouseover', function (evt) {
        $(evt.layer._icon).addClass('focus');
      });

      this.markerCluster.on('clustermouseout', function (evt) {
        $(evt.layer._icon).removeClass('focus');
      });

      this.buildBtns();
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
    updateZoomCtrls: function(){
      if(this.options.map.getZoom() > this.options.map.getMinZoom()) {
        $('.zoomout_btn', $(this.options.elCntrls)).attr('disabled', false);
      }
      else {
        $('.zoomout_btn', $(this.options.elCntrls)).attr('disabled', true);     
      }     
    
      if(this.options.map.getZoom() < this.options.map.getMaxZoom()) {
        $('.zoomin_btn', $(this.options.elCntrls)).attr('disabled', false);
      }
      else {
        $('.zoomin_btn', $(this.options.elCntrls)).attr('disabled', true);
      }
    },
    buildBtns: function(){
      var self = this;

      $('.centre_btn', $(this.options.elCntrls)).click(function(evt){       
        // fire event
        app.dispatcher.trigger("MapView:centreclick", self);                
      });

      $('.zoomin_btn', $(this.options.elCntrls)).click(function(evt){       
        if (!$(this).attr('disabled')) {
          $('.view_btn', $(self.options.elCntrls)).attr('disabled', false);              
          self.options.map.zoomIn();      
          // fire event
          app.dispatcher.trigger("MapView:zoominclick", self);                
        }
      });

      $('.zoomout_btn', $(this.options.elCntrls)).click(function(evt){
        if (!$(this).attr('disabled')) {
          $('.view_btn', $(self.options.elCntrls)).attr('disabled', false);              
          self.options.map.zoomOut();
          // fire event
          app.dispatcher.trigger("MapView:zoomoutclick", self);                
        }
      });
      
      $('.view_btn', $(this.options.elCntrls)).click(function(evt){
        if (!$(this).attr('disabled')) {
          $(this).attr('disabled', true);
        }
      });
    },
    
    render: function(){
      var self = this;

      // already rendered?  Just update
      if (this.bRendered) {
        return;
      }

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
      this.options.map.on("layeradd", onLayerAdd);

      $.each(this.options.jsonMedia, function(index, jsonMedia) {
        var markerView = new MarkerView({jsonMedia: jsonMedia, map: self.options.map, mapLayer: self.markerCluster});
        markerView.render();
      });
      this.options.map.addLayer(this.markerCluster);

      this.updateZoomCtrls(); 

      $('.view_btn', $(this.options.elCntrls)).attr('disabled', true);
      $(this.options.elCntrls).show();

      this.bRendered = true;

      return this;
    },
    onMarkerViewClick: function(markerView){
      // fire event
      app.dispatcher.trigger("MapView:click", markerView.options.model);
    }
	
  });

  return MapView;
});
