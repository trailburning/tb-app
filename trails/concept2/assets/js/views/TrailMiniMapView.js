define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMiniMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMiniMapViewTemplate').text());        
            
      this.bRendered = false;
      this.polyline = null;
      this.arrLineCordinates = [];
      this.arrMediaPoints = [];
      this.arrMarkers = [];
      this.currMarker = null;
      
      var self = this;
      
      var MediaIcon = L.Icon.extend({
          options: {
              iconSize:     [18, 18],
              iconAnchor:   [8, 8],
              popupAnchor:  [8, 8]
          }
      });      
      this.mediaInactiveIcon = new MediaIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/sm_marker_inactive.png'});
      this.mediaActiveIcon = new MediaIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/sm_marker_active.png'});
            
      var LocationIcon = L.Icon.extend({
          options: {
            iconSize:     [27, 35],
            iconAnchor:   [12, 33],
            popupAnchor:  [12, 33]
          }
      });      
      this.locationIcon = new LocationIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/sm_location.png'});
            
      $(window).resize(function() {
        self.render();        
      });                
    },            
    show: function(){
      $(this.el).show();
    },
    hide: function(){
      $(this.el).hide();
    },
    gotoMedia: function(nMedia){
      var marker;
      // retore previous
      if (this.currMarker) {
        this.currMarker.setIcon(this.mediaInactiveIcon);
        this.currMarker.setZIndexOffset(100);
      }
      marker = this.arrMarkers[nMedia];
      marker.setIcon(this.mediaActiveIcon);
      marker.setZIndexOffset(200);
      
      this.currMarker = marker;
    },
    addMedia: function(mediaModel){
      this.arrMediaPoints.push(mediaModel);
    },
    renderMarkers: function(){
      if (!this.arrMediaPoints.length) {
        return;
      }

      var self = this;
      var mediaPoint = null;
      var marker = null;

      for (var nMedia=0; nMedia < this.arrMediaPoints.length; nMedia++) {
        mediaPoint = this.arrMediaPoints[nMedia];
        marker = L.marker([mediaPoint.get('coords').lat, mediaPoint.get('coords').long], {icon: self.mediaInactiveIcon}).on('click', onClick).addTo(self.map);;
        this.arrMarkers.push(marker);
      }
      L.marker(this.arrLineCordinates[0], {icon: this.locationIcon}).addTo(this.map);            
      
      function onClick(e) {
        // fire event
        app.dispatcher.trigger("TrailMiniMapView:mediaclick", self);                        
      }         
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
        this.map.fitBounds(this.polyline.getBounds(), {paddingTopLeft: [20, 30], paddingBottomRight: [30, 20]});
        return;         
      }        

      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      
      $('.btn', $(this.el)).click(function(evt){
        // fire event
        app.dispatcher.trigger("TrailMiniMapView:viewbtnclick", self);                
      });
      
      this.map = L.mapbox.map('minimap', null, {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.map-omeomj70');
      this.map.addLayer(this.layer_street);
                
      // remove previous points
      while (this.arrLineCordinates.length > 0) {
        this.arrLineCordinates.pop();
      }
                
      var data = this.model.get('value');      
      $.each(data.route.route_points, function(key, point) {
        self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      });

      var polyline_options = {
        color: '#44B6FC',
        opacity: 1,
        weight: 4,
        clickable: false
      };         
      this.polyline = L.polyline(this.arrLineCordinates, polyline_options).addTo(this.map);          
      this.map.fitBounds(this.polyline.getBounds(), {paddingTopLeft: [20, 30], paddingBottomRight: [30, 20]});         

      this.renderMarkers();
                      
      // show btn                      
      $('.trailview_toggle', $(this.el)).show();

      this.bRendered = true;

      return this;
    }    
  });

  return TrailMiniMapView;
});
