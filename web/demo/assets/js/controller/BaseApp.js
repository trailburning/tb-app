var app = app || {};

var BASE_URL = 'http://www.trailburning.com/api';
var FEED_URL = 'http://www.eggontop.com/live/trailburning/tb-campaignviewer/server/feed_cache.php';

//var BASE_URL = 'http://localhost:8888/trailburning_api/app_dev.php';
//var FEED_URL = 'http://localhost:8888/projects/Trailburning/tb-campaignviewer/server/feed_cache.php';

//var BASE_URL = 'http://10.0.1.5:8888/trailburning_api/app_dev.php';
//var FEED_URL = 'http://10.0.1.5:8888/projects/Trailburning/tb-campaignviewer/server/feed_cache.php';

var HEIGHT_WIDE_ASPECT_PERCENT = 56;
var MAP_VIEW = 0;
var MAP_ASSET_VIEW = 1;

define([
  'underscore', 
  'backbone',
  'imageScale',
  'lazyload',
  'views/AssetsView'
], function(_, Backbone, imageScale, lazyload, AssetsView){

  app.dispatcher = _.clone(Backbone.Events);
  
  var jsonFeed = {};

  var initialize = function() {
    function getJourney(nJourneyID) {
      var url = BASE_URL + "/v1/route/" + nJourneyID;

      $.getJSON(url, function(result){
        journeyModel = new Backbone.Model(result.value.route);
        getJourneyMedia(nJourneyID);
      });
    }

    function getJourneyMedia(nJourneyID) {
      var url = BASE_URL + "/v1/route/" + nJourneyID + "/medias";

      $.getJSON(url, function(result){
        buildPage(result.value, jsonFeed);
      });
    }

    function getFeed(strHashTag) {
      if (strHashTag != '') {
        var url = FEED_URL + '?tag=' + strHashTag;

        $.getJSON(url, function(result){
          if(!result || !result.data || !result.data.length){
            return;
          }  
          jsonFeed = result.data;
          getJourney(TB_TRAIL);
        });
      }
      else {
        getJourney(TB_TRAIL);
      }
    }

    function buildPage(jsonAssets, jsonFeed) {
      this.assetsView = new AssetsView({ el: '#assets-View', model: journeyModel, jsonAssets: jsonAssets, jsonFeed: jsonFeed });
      this.assetsView.render();

      $('.lazy').lazy();

      resize();
    }

    function resize() {
      if (this.assetsView) this.assetsView.resize();
    }
    $(window).resize(function() {
      resize();
    });

    getFeed(TB_HASHTAG);
  };

  return {
    initialize: initialize
  };
});
