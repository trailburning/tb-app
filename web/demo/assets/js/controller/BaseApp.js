var app = app || {};

//var BASE_URL = 'http://www.trailburning.com/api';
var BASE_URL = 'http://localhost:8888/trailburning_api/app_dev.php';

var HEIGHT_WIDE_ASPECT_PERCENT = 56;

define([
  'underscore', 
  'backbone',
  'imageScale',
  'views/AssetsView'
], function(_, Backbone, imageScale, AssetsView){
  app.dispatcher = _.clone(Backbone.Events);
    
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
        buildPage(result.value);
      });
    }

    function buildPage(jsonAssets) {
      var self = this;

      this.assetsView = new AssetsView({ el: '#assets-View', model: journeyModel, jsonAssets: jsonAssets });
      this.assetsView.render();

      resize();
    }

    function resize() {
      if (this.assetsView) this.assetsView.resize(HEIGHT_WIDE_ASPECT_PERCENT);
    }
    $(window).resize(function() {
      resize();
    });

//    getJourney(53);
    getJourney(425);
  };

  return {
    initialize: initialize
  };
});
