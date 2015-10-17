var app = app || {};

var VIEW_EXPEDITION = 0;
var VIEW_EVENT = 1;
var VIEW_EVENT_SLIDESHOW = 2;
var VIEW_EVENT_CATEGORY_SLIDESHOW = 3;

//var HEIGHT_ASPECT_PERCENT = 56.25;
var HEIGHT_ASPECT_PERCENT = 56;

define([
  'underscore', 
  'backbone',
  'views/ExpeditionView',
  'views/EventView',
  'views/EventSlideshowView'
], function(_, Backbone, ExpeditionView, EventView, EventSlideshowView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    app.dispatcher.on("ExpeditionView:eventSelect", onEventSelect, this);

    app.dispatcher.on("EventView:assetSelect", onAssetSelect, this);
    app.dispatcher.on("EventView:categorySelect", onEventCategorySelect, this);

    var self = this;

    var expeditionModel = new Backbone.Model();
    var collectionEvents = null;
    var eventModel = null, collectionAssets = null, assetModel = null, strEventCategory = '';

    var expeditionView = null;
    var eventView = null;
    var eventSlideshowView = null;

    var nCurrentView = VIEW_EXPEDITION;
    var nPrevView = VIEW_EXPEDITION;

    getJourney();

    $('.back-btn', this.el).click(function(evt){
      goBack();
    });

    function buildJourney(jsonJourney) {
      collectionEvents = new Backbone.Collection(jsonJourney.events);
      expeditionView = new ExpeditionView({ el: '#expedition-view', model: expeditionModel, collection: collectionEvents });
      eventView = new EventView({ el: '#event-view' });
      eventSlideshowView = new EventSlideshowView({ el: '#event-slideshow-view' });

      expeditionView.render();
      resize();
    }

    function getJourney() {
      var url = "test-assets/journey.json";
      $.getJSON(url, function(result){
        buildJourney(result.body.journeys[0]);
      });
    }

    function resize() {
      eventView.resize(HEIGHT_ASPECT_PERCENT);
      eventSlideshowView.resize(HEIGHT_ASPECT_PERCENT);
    }
    $(window).resize(function() {
      resize();
    });

    function goBack() {
      switch (nCurrentView) {
        case VIEW_EVENT_SLIDESHOW:
          changeView(VIEW_EVENT);
          break;

        case VIEW_EVENT_CATEGORY_SLIDESHOW:
          switch (nPrevView) {
            case VIEW_EVENT:
              changeView(VIEW_EVENT);
              break;

            default:
              changeView(VIEW_EXPEDITION);
              break;
          }
          break;

        case VIEW_EVENT:
          changeView(VIEW_EXPEDITION);
          break;
      }
    }

    function changeView(nNewView) {
      $('.nav-toggle-btn').hide();
      $('.back-btn-container.nav-toggle-btn').show();

      switch (nNewView) {
        case VIEW_EXPEDITION:
          switch (nCurrentView) {
            case VIEW_EVENT:
              eventView.hide();
              expeditionView.show();
              $('.nav-toggle-btn').hide();
              $('.main-btn-container.nav-toggle-btn').show();
              break;

            case VIEW_EVENT_CATEGORY_SLIDESHOW:
              eventSlideshowView.hide();
              expeditionView.show();
              $('.nav-toggle-btn').hide();
              $('.main-btn-container.nav-toggle-btn').show();
              break;
          }
          break;

        case VIEW_EVENT:
          switch (nCurrentView) {
            case VIEW_EXPEDITION:
              expeditionView.hide();
              eventView.render(eventModel);
              eventView.show();
              break;

            case VIEW_EVENT_SLIDESHOW:
              eventSlideshowView.hide();
              eventView.show();
              break;

            case VIEW_EVENT_CATEGORY_SLIDESHOW:
              eventSlideshowView.hide();
              eventView.show();
              break;
          }
          break;

        case VIEW_EVENT_SLIDESHOW:
          switch (nCurrentView) {
            case VIEW_EVENT:
              eventView.hide();
              eventSlideshowView.render(assetModel, collectionAssets, strEventCategory);
              eventSlideshowView.show();
              break;
          }
          break;

        case VIEW_EVENT_CATEGORY_SLIDESHOW:
          switch (nCurrentView) {
            case VIEW_EXPEDITION:
              expeditionView.hide();
              eventSlideshowView.render(assetModel, collectionAssets, strEventCategory);
              eventSlideshowView.show();
              break;

            case VIEW_EVENT:
              eventView.hide();
              eventSlideshowView.render(assetModel, collectionAssets, strEventCategory);
              eventSlideshowView.show();
              break;
          }
          break;
      }
      resize(HEIGHT_ASPECT_PERCENT);

      nPrevView = nCurrentView;
      nCurrentView = nNewView;
    }

    function onAssetSelect(strID) {
      collectionAssets = new Backbone.Collection(eventModel.get('assets').filter(function(asset) {
        return (asset.category == 'expedition');
      }));

      assetModel = collectionAssets.get(strID);
      changeView(VIEW_EVENT_SLIDESHOW);
    }

    function onEventSelect(strID, strCategory) {
      strEventCategory = strCategory;

      eventModel = collectionEvents.get(strID);

      if (strCategory == 'expedition') {
        changeView(VIEW_EVENT);
      }
      else {
        collectionAssets = new Backbone.Collection(eventModel.get('assets').filter(function(asset) {
          return (asset.category == strCategory);
        }));
        assetModel = collectionAssets.at(0);
        changeView(VIEW_EVENT_CATEGORY_SLIDESHOW);
      }
    }

    function onEventCategorySelect(strID, strCategory) {
      collectionAssets = new Backbone.Collection(eventModel.get('assets').filter(function(asset) {
        return (asset.category == strCategory);
      }));
      assetModel = collectionAssets.at(0);
      changeView(VIEW_EVENT_CATEGORY_SLIDESHOW);
    }

  };

  return { 
    initialize: initialize
  };   
});  
