define([
  'underscore', 
  'backbone',
  'piste'
], function(_, Backbone, piste){

  var Expedition3DView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;
    },

    hide: function(){
    },

    show: function(){
      // ensure view updates
      window.dispatchEvent(new Event('resize'));
    },

    gotoPoint: function(strID){
      Piste.panToMediaPoint( Number(strID) );
      Piste.selectMediaPoints( [Number(strID)] );
/*
      $('.mediapoint i', $('#piste-view')).each (function(index) {
        $(this).css('background-image', 'http://tbmedia2.imgix.net/test-assets/poster-video.jpg?fm=jpg&q=80');
      });
*/
    },

    render: function(){
      var self = this;

      // When Piste API has loaded and can be used, onReady will be called
      Piste.onReady = function () {
        var container = $('#piste-view')[0];
        Piste.init( container );

//        var url = 'http://www.eggontop.com/live/trailburning/tb-campaignviewer/server/25zero.php';
        var url = 'test-assets/chimborazo_route.json';
        $.getJSON(url, function(result){
          var json, strIconClass;
          self.collection.each(function(event) {
            /* mla - temp until we can define in geoJSON */
            strIconClass = 'fa';
            if (event.get('id') == 1) {
              strIconClass = 'fa fa-video';
            }
            if (event.get('id') == 10) {
              strIconClass = 'fa fa-image';
            }
            json = {"id": event.get('id'),"type": "Feature","properties": {"tags": {"color": "#000","icon": strIconClass},"display": "mediapoint"},"geometry": {"type": "Point","coordinates": [event.get('coords')[0],event.get('coords')[1]]}};
            result.features.push(json);
          });

          // Load in a location by name
          Piste.selectLocation( 'chimborazo' );
//          Piste.selectLocation( 'mount-stanley' );

          // When location data is loaded
          Piste.onLocationLoaded = function () {
            Piste.setFeatures( result );
          }
        });
      }

      // Register event handler for when media point is clicked
      Piste.mediaPointClicked = function ( id ) {
        // When clicked, pan to media point
        Piste.panToMediaPoint( id );

        // Also select clicked point (note multiple selection also supported)
        Piste.selectMediaPoints( [id] );

        // fire event
        app.dispatcher.trigger("Expedition3DView:mediaSelect", id);
      };

      return this;
    }

  });

  return Expedition3DView;
});
