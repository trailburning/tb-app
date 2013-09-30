var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';

define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    // register for image ready      
    $('.tb-fade img', this.el).load(function() {
      $(this).parent().css({ opacity: 1 });
    });
    // force ie to run the load function if the image is cached
    if ($('.tb-fade img', this.el).get(0).complete) {
      $('.tb-fade img', this.el).trigger('load');
    }
    
    function handleResize() {
      $('.image').resizeToParent();      
    }
    
    function builtEventMap() {  
      var fLat = 46.021073;
      var fLng = 7.747937;
      
      var map = L.mapbox.map('event_location_map', 'mallbeury.map-kply0zpa', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});

      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      var startIcon = new LocationIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/location.png'});
      L.marker([fLat, fLng], {icon: startIcon}).addTo(map);      

      var latlng = new L.LatLng(fLat, fLng);
      map.setView(latlng, 7);
    }
                    
    builtEventMap();
  };
    
  return { 
    initialize: initialize
  };   
});  