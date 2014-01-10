define([
  'underscore', 
  'backbone',
  'views/TrailMapMediaMarkerView'  
], function(_, Backbone, TrailMapMediaMarkerView){

  var EditorialMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#editorialMapViewTemplate').text());        
            
      this.bRendered = false;
      this.arrLineCordinates = [];
      
      var self = this;
    },            
    show: function(){
      $(this.el).show();
    },
    hide: function(){
      $(this.el).hide();
    },
    render: function(){
      // already rendered?  Just update
      if (this.bRendered) {
        this.map.invalidateSize();
        this.map.fitBounds(this.polyline.getBounds(), {paddingTopLeft: [20, 30], paddingBottomRight: [30, 20]});
        return;         
      }        

      var self = this;
                
      $(this.el).html(this.template());
            
      this.map = L.mapbox.map('map', null, {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.map-kply0zpa');
      this.map.addLayer(this.layer_street);
          
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      var locationIcon = new LocationIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/location.png'});
                
      var arrMarkers = [];

      function onClick(e) {
      }

      arrMarkers.push([46.623579575, 8.04374652]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: locationIcon, zIndexOffset: 1000}).on('click', onClick).addTo(this.map);
                    
      arrMarkers.push([46.021073, 7.747937]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: locationIcon, zIndexOffset: 1000}).on('click', onClick).addTo(this.map);

      arrMarkers.push([45.923697, 6.869433]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: locationIcon, zIndexOffset: 1000}).on('click', onClick).addTo(this.map);

      var bounds = new L.LatLngBounds(arrMarkers);
      bounds = bounds.pad(0.05);
      this.map.fitBounds(bounds);

      this.bRendered = true;

      return this;
    }    
  });

  return EditorialMapView;
});
