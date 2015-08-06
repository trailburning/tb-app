/**
 * @jsx React.DOM
 */
/*jshint quotmark:false */
/*jshint white:false */
/*jshint trailing:false */
/*jshint newcap:false */
/*global React, Backbone */
var app = app || {};

(function () {
	'use strict';

  app.dispatcher = _.clone(Backbone.Events);

  app.dispatcher.on("MapView:click", onMapMarkerViewClick, this);

  L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';
  var map = L.mapbox.map('map_view', 'mallbeury.8d4ad8ec', {dragging: true, touchZoom: false, scrollWheelZoom: false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false, minZoom: 2, maxZoom: 17});

  var routeLine = {
    "type": "Feature",
    "properties": {},
    "geometry": {
      "type": "LineString",
      "coordinates": []
    }
  };

  var collectionPosts = new Backbone.Collection();
  var modelPost = null;

  var SlideList, slideList, DialogDetail, dialogDetail;
  var mapView, feed, nCurrSlideFocus = -1;    
  var elPhotoFeedContainer = $('#slideList-mount-point').get(0);
  var elDialogDetailContainer = $('#postDetail-mount-point').get(0);

  var strGPX = TB_DATA + "/trail_ultraks.gpx";
//  var strGPX = "data/trail_tblw.gpx";
//  var strGPX = "data/trail_test1.gpx";
//  var strGPX = "data/trail_test2.gpx";
  addTrail();
//  getFeed();

  $('#footerview').show();

  var searchView = new SearchView({ el: '#searchview' });
  if (typeof TB_USER_ID != 'undefined') {
    var activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
    activityFeedView.render();
    activityFeedView.getActivity();      
  }

  // keyboard control
  $(document).keydown(function(e){
    switch (e.keyCode) {
      case 13: // detail dialog
        if (nCurrSlideFocus != -1) {
          $("#postDetail").modal();  
        }        
        break;
      case 37: // previous
        slideList.prevSlide();
        break;
      case 39: // next
        slideList.nextSlide();
        break;
    }
  });

  function getFeed() {
    var url = "http://www.eggontop.com/live/trailburning/tb-campaignviewer/server/feed_ultraks.php";
//    var url = "http://localhost:8888/projects/Trailburning/tb-campaignviewer/server/feed_ultraks.php";
    
    var strInstagramURL = "http://www.instagram.com/";

    $.getJSON(url, function(result){
      if(!result || !result.data || !result.data.length){
        return;
      }  

      feed = result.data;
       
      $.each(result.data, function(index, item) {        
        if (item.location) {
          if (item.location.latitude && item.location.longitude) {
            var date = new Date(parseInt(item.created_time) * 1000);
            var strCaption = "";
            if (item.caption) {
              strCaption = item.caption.text;
            }

//            if (routeLine.geometry.coordinates.length) {
            if (1 == 2) {
              // look for point on line
              var pt = {
                "type": "Feature",
                "properties": {},
                "geometry": {
                  "type": "Point",
                  "coordinates": [item.location.latitude, item.location.longitude]
                }
              }

              var snapped = turf.pointOnLine(routeLine, pt);
              // images < 1 km from trail
              if (snapped.properties.dist < 1) {
                modelPost = new Backbone.Model({
                  id: item.id, 
                  image_low_res: item.images.low_resolution.url, 
                  image_standard_res: item.images.standard_resolution.url, 
                  caption: strCaption,
                  lat: item.location.latitude, 
                  lng: item.location.longitude,
                  link_url: item.link,
                  username: item.user.username,
                  user_url: strInstagramURL + item.user.username,
                  user_avatar: item.user.profile_picture,
                  created_time: date.getTime()
                });
                collectionPosts.add(modelPost);
              }
            }
            else {
              modelPost = new Backbone.Model({
                id: item.id, 
                image_low_res: item.images.low_resolution.url, 
                image_standard_res: item.images.standard_resolution.url, 
                caption: strCaption,
                lat: item.location.latitude, 
                lng: item.location.longitude,
                link_url: item.link,
                username: item.user.username,
                user_url: strInstagramURL + item.user.username,
                user_avatar: item.user.profile_picture,
                created_time: date.getTime()
              });
              collectionPosts.add(modelPost);
            }                        
          }          
        }
      });      

      var nInitialSlide = 0;

      mapView = new MapView({ map: map, elCntrls: '#view_map_btns', collectionPosts: collectionPosts });            
      mapView.render();

      SlideList = app.SlideList;   
      DialogDetail = app.DialogDetail;   

      renderSlideList(nInitialSlide);    
      renderDistanceMarkers();  

      $("#slideList-mount-point").swipe( { allowPageScroll:"vertical",
          //Generic swipe handler for all directions
          swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
            if (direction == "left") {
              slideList.prevSlide();
            }
            if (direction == "right") {
              slideList.nextSlide();              
            }
          }
        });      
      });
  }

  function addDistanceMarker(nKM) {
    var along = turf.along(routeLine, nKM, 'kilometers');
//    console.log(JSON.stringify(along));
    var modelDistance = new Backbone.Model({lat: along.geometry.coordinates[0], lng: along.geometry.coordinates[1], distance: nKM}); 

    var distanceMarkerView = new DistanceMarkerView({model: modelDistance, map: map});
    distanceMarkerView.render();
  }

  function renderDistanceMarkers() {
    var length = turf.lineDistance(routeLine, 'kilometers');    
    var nInc = 5;
    var nMarkers = Math.floor(length / nInc);
    var nCurrMarker = 0;

    for (var nMarker=0; nMarker <= nMarkers; nMarker += 1) {
      nCurrMarker = nInc * nMarker;
      if (nCurrMarker) {
        addDistanceMarker(nCurrMarker);  
      }      
    }
  }

  function renderSlideList(nSelected) {
    slideList = React.render(<SlideList collection={ collectionPosts.models } selected={ nSelected } onSlideFocus={ onSlideFocus } onSlideClick={ onSlideClick } />, elPhotoFeedContainer);
  }

  function renderDialogDetail(nSelected) {
    var modelPost = collectionPosts.at(nSelected);    
    dialogDetail = React.render(<DialogDetail link_url={ modelPost.get("link_url") } user_url={ modelPost.get("user_url") } username={ modelPost.get("username") } user_avatar={ modelPost.get("user_avatar") } created_time={ modelPost.get("created_time") } caption={ modelPost.get("caption") } image_standard_res={ modelPost.get("image_standard_res") } onPrevClick={ onPrevClick } onNextClick={ onNextClick } />, elDialogDetailContainer);
  }

  function addTrail() {
    var customLayer = L.geoJson(null, {
      // http://leafletjs.com/reference.html#geojson-style
      style: function(feature) {
        return { color: "#000000", weight: 4, opacity: 0.8};
      }
    });
    
    var trailLayer = omnivore.gpx(strGPX, null, customLayer)
    .on('ready', function() {
        map.fitBounds(trailLayer.getBounds());

        trailLayer.eachLayer(function (layer) {
          var arrCoords = new Array;
          var arrLatLngs = layer.getLatLngs();

          for (var n=0; n < arrLatLngs.length; n++) {
            arrCoords.push([arrLatLngs[n].lat, arrLatLngs[n].lng]);
          }
          routeLine.geometry.coordinates = arrCoords;
//          console.log(JSON.stringify(routeLine));
        });

        getFeed();
    });

    trailLayer.addTo(map); 
  }

  function onSlideFocus(nSelected) {
    nCurrSlideFocus = nSelected;
    mapView.setSelected(nSelected); 
    renderDialogDetail(nSelected);
  }

  function onSlideClick(nSelected) {
    renderDialogDetail(nSelected);
    $("#postDetail").modal();
  }

  function onPrevClick() {
    slideList.prevSlide();
  }

  function onNextClick() {
    slideList.nextSlide();
  }

  function onMapMarkerViewClick(modelPost) {
    var currModelPost = collectionPosts.get(modelPost.id);

    var nSelected = collectionPosts.indexOf(currModelPost);

    mapView.setSelected(nSelected); 

    renderSlideList(nSelected);
  }

})();
