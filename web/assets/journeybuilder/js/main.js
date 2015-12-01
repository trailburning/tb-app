require.config({
  paths: {
    jquery: 'libs/jquery-2.1.4.min',
    jqueryui: 'libs/jquery-ui',
    modernizr: 'libs/modernizr-custom',
    underscore: 'libs/underscore-min',
    backbone: 'libs/backbone-min',
    async: 'libs/async',
    bootstrap: '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min',
    videojs: '//vjs.zencdn.net/4.12/video',
    imageScale: 'libs/image-scale.min'
  },
  shim: {
    'bootstrap' : { 
      deps: ['jquery'] 
    },
    'modernizr': {
      exports: 'Modernizr'
    },
    'imageScale': {
      deps: ['jquery'],
      exports: 'imageScale'
    }
  }
});

function CRtoBR(str) {
  return str.replace(/(?:\r)/g, '<br />')
}

function formatAltitude(nStr){
  nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
      x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

// Load our app module and pass it to our definition function
require(['controller/BaseApp'], function(App){
  App.initialize();
})
