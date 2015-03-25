define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  var MapView = Backbone.View.extend({
    initialize: function(){
      this.bAttractor = false;

      this.map = L.mapbox.map($('.map', $(this.el))[0], 'mallbeury.e2be4b9a',{tileSize: 5120, zoomControl: false, attributionControl: false});      

      this.geojsonFeature = {
        "type": "LineString",
        "coordinates": []
      };
    
      this.myStyle = {
        "color": "#0000ff",
        "weight": 4,
        "opacity": 1
      };

      var getPxBounds = this.map.getPixelBounds;
      this.map.getPixelBounds = function () {
          var bounds = getPxBounds.call(this);
          bounds.min.x=bounds.min.x-1000;
          bounds.min.y=bounds.min.y-1000;
          bounds.max.x=bounds.max.x+1000;
          bounds.max.y=bounds.max.y+1000;
          return bounds;
      };
      this.map.scrollWheelZoom.disable();

      //map rotation
      $('body').on('mousedown','.map-rotate.tilted',
        function(e){
//              xpos=e.pageX;
//              window.isDown = true;
          })
          .on('mousemove','.map-rotate.tilted', function(e){
/*            
            if(isDown){
              xdrag =(xpos-e.pageX)/4;
              $('#map').attr('style', '-webkit-transform:rotateZ('+(angle+xdrag)%360+'deg)');
              $('.pointmarker').attr('style', '-webkit-transform:rotateX(90deg) rotateY('+(angle+xdrag)*(-1)%360+'deg)')
            }
*/            
          })
        .on('mouseup','.map-rotate.tilted',function(){
//          isDown=false;
//          angle = angle+xdrag;
        });   
    },    
    render: function(){
      this.renderLocations();
      this.setMapView();
      this.toggleView();
    },
    playAttractor: function(nBearing){
      var strBearing = String(nBearing+'_short');

      TweenMax.to($('.pivotmarker', $(this.el)), 2, {rotationY: strBearing, ease:Sine.easeInOut});
      TweenMax.to($('.rotating', $(this.el)), 2, {rotationZ: strBearing, ease:Sine.easeInOut});
    },
    toggleView: function(){
      $('.rotating', $(this.el)).addClass('transform-tween');
      $('.map-rotate', $(this.el)).addClass('tilted');

      //disable panning
      this.map.dragging.disable();
    },
    moveMap: function(latLng, nBearing){
      $('.map-rotate', $(this.el)).addClass('transform-tween');

      this.map.setViewFIXED(latLng, this.options.nZoom, {animate: true, duration: 2});
      this.playAttractor(nBearing);
    },  
    setMapView: function(){
      this.map.fitBounds(this.options.arrPoints, {paddingTopLeft:[50,50],paddingBottomRight:[50,50], animate: false});
      this.map.setView(this.options.arrPoints[0], this.options.nZoom, {animate: false});
    },
    renderLocations: function(){
      var nMarkerWidth = 26;
      var nMargin = Math.round((this.options.nLabelWidth - nMarkerWidth) / 2);

      for (var nMarker=0; nMarker < this.options.arrMarkers.length; nMarker++) {
        var objMarker = this.options.arrMarkers[nMarker];

        var iconLocation = L.divIcon({
            className:'',
            html:'<div class="locationmarker"><div class="pivotmarker"><img src="http://assets.trailburning.com/images/icons/sm_tb_location.png" class="icon" style="margin-left:'+nMargin+'"/><div class="locationlabel" style="width:'+this.options.nLabelWidth+'">'+objMarker.getTitle()+'</div></div></div>',
            iconSize: [this.options.nLabelWidth, 34], iconAnchor: [this.options.nLabelWidth/2, 34]
        });

        var iconTweet = L.divIcon({
            className:'',
            html:'<div class="locationmarker"><div class="pivotmarker"><img src="http://assets.trailburning.com/images/icons/tweet.png" class="icon" style="margin-left:'+nMargin+'"/><div class="locationlabel" style="width:'+this.options.nLabelWidth+'">'+objMarker.getTitle()+'</div></div></div>',
            iconSize: [this.options.nLabelWidth, 38], iconAnchor: [this.options.nLabelWidth/2, 38]
        });

//        switch (nPoint) {
//          case 0:
//          case this.options.arrPoints.length-1:
            L.marker([this.options.arrMarkers[nMarker].getLat(), this.options.arrMarkers[nMarker].getLong()],{icon:iconLocation}).addTo(this.map);
//            break;
//          default:
//            L.marker([this.options.arrPoints[nPoint].lat, this.options.arrPoints[nPoint].lng],{icon:iconTweet}).addTo(this.map);
//            break;
//        }        
      }
    }      

  });

  return MapView;
});
