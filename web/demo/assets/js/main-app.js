require.config({
  paths: {
    jquery: 'libs/jquery-2.1.4.min',
    modernizr: 'libs/modernizr-custom',
    underscore: 'libs/underscore-min',
    backbone: 'libs/backbone-min',
    async: 'libs/async',
    bootstrap: '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min',
    mapbox: 'http://api.tiles.mapbox.com/mapbox.js/v2.1.5/mapbox',
    turf: 'http://api.tiles.mapbox.com/mapbox.js/plugins/turf/v1.4.0/turf.min',
    markercluster: 'http://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/leaflet.markercluster',
    videojs: '//vjs.zencdn.net/4.12/video',
    imageScale: 'libs/image-scale.min'
  },
  shim: {
    'modernizr': {
      exports: 'Modernizr'
    },
    'imageScale': {
      deps: ['jquery'],
      exports: 'imageScale'
    },
    'bootstrap': {
      deps: ['jquery']
    },
    'markercluster': {
      deps: ['mapbox']
    }
  }
});

function CRtoBR(str) {
  return str.replace(/(?:\n)/g, '<br />')
}

// Load our app module and pass it to our definition function
require(['controller/BaseApp'], function(App){
  App.initialize();
})
