var app = app || {};

var VIEW_JOURNEYS = 0;
var VIEW_EVENTS = 1;
var VIEW_EVENT = 2;
var VIEW_ASSET = 3;

var TB_RESTAPI_BASEURL = 'https://api.trailburning.com/v2';

var HEIGHT_WIDE_ASPECT_PERCENT = 56;
var HEIGHT_SQUARE_ASPECT_PERCENT = 90;

define([
  'underscore', 
  'backbone',
  'views/JourneysView',
  'views/EventsView',
  'views/EventView',
  'views/AssetView'
], function(_, Backbone, JourneysView, EventsView, EventView, AssetView){
  app.dispatcher = _.clone(Backbone.Events);

  var initialize = function() {
    app.dispatcher.on("JourneysView:journeySelect", onJourneySelect, this);
    app.dispatcher.on("EventsView:eventSelect", onEventSelect, this);
    app.dispatcher.on("EventsView:backClick", onGoBack, this);
    app.dispatcher.on("EventView:assetCreate", onAssetCreate, this);
    app.dispatcher.on("EventView:assetClick", onAssetSelect, this);
    app.dispatcher.on("EventView:backClick", onGoBack, this);
    app.dispatcher.on("AssetView:removed", onAssetRemoved, this);
    app.dispatcher.on("AssetView:backClick", onGoBack, this);
    app.dispatcher.on("AssetView:mediauploadError", onShowError, this);

    var self = this;
    var journeyModel = null; eventModel = null, assetID = null;
    var eventsView = null, eventView = null, assetView = null;
    var nCurrentView = VIEW_JOURNEYS;
    var nPrevView = VIEW_JOURNEYS;

    function getJourneys() {
      journeysView = new JourneysView({ el: '#journeys-view' });
      eventsView = new EventsView({ el: '#events-view' });
      eventView = new EventView({ el: '#event-view' });
      assetView = new AssetView({ el: '#asset-view' });

      journeysView.getJourneysAndRender();
      resize();
    }

    function resize() {
      if (eventView) eventView.resize(HEIGHT_WIDE_ASPECT_PERCENT, HEIGHT_SQUARE_ASPECT_PERCENT);
    }
    $(window).resize(function() {
      resize();
    });

    function goBack() {
      switch (nCurrentView) {
        case VIEW_EVENTS:
          changeView(VIEW_JOURNEYS);
          break;

        case VIEW_EVENT:
          changeView(VIEW_EVENTS);
          break;

        case VIEW_ASSET:
          changeView(VIEW_EVENT);
          break;
      }
    }

    function changeView(nNewView) {
      switch (nNewView) {
        case VIEW_JOURNEYS:
          switch (nCurrentView) {
            case VIEW_EVENTS:
              eventsView.hide();
              journeysView.getJourneysAndRender();
              journeysView.show();
              break;
          }
          break;

        case VIEW_EVENTS:
          switch (nCurrentView) {
            case VIEW_JOURNEYS:
              journeysView.hide();
              eventsView.getEventsAndRender(journeyModel);
              eventsView.show();
              break;

            case VIEW_EVENT:
              eventView.hide();
              eventsView.getEventsAndRender(journeyModel);
              eventsView.show();
              break;
          }
          break;

        case VIEW_EVENT:
          switch (nCurrentView) {
            case VIEW_EVENTS:
              eventsView.hide();
              eventView.getEventAndRender(journeyModel, eventModel);
              eventView.show();
              break;

            case VIEW_ASSET:
              assetView.hide();
              eventView.getEventAndRender(journeyModel, eventModel);
              eventView.show();
              break;
          }
          break;

        case VIEW_ASSET:
          switch (nCurrentView) {
            case VIEW_EVENT:
              eventView.hide();
              assetView.getAssetAndRender(journeyModel, eventModel, assetID);
              assetView.show();
              break;
          }
          break;
      }

      resize();
      nPrevView = nCurrentView;
      nCurrentView = nNewView;
    }

    function onGoBack() {
      goBack();
    }

    function onAssetRemoved() {
      goBack();
    }

    function onJourneySelect(selJourneyModel) {
      journeyModel = selJourneyModel;

      changeView(VIEW_EVENTS);
    }

    function onEventSelect(selEventModel) {
      eventModel = selEventModel;

      changeView(VIEW_EVENT);
    }

    function onAssetCreate() {
      var json = {'category': 1,
                  'name': 'Asset Name',
                  'credit': 'Asset Credit',
                  'about': 'About the Asset'};

      var strURL = TB_RESTAPI_BASEURL + '/events/' + eventModel.get('id') + '/assets';
      
      $.ajax({
        type: "POST",
        dataType: "json",
        url: strURL,
        data: json,
        error: function(data) {
          console.log('error');
          console.log(data);
        },
        success: function(data) {
          console.log('success');
          console.log(data);

          eventView.getEventAndRender(eventModel);
        }
      }); 
    }

    function onAssetSelect(selAssetID) {
      assetID = selAssetID;

      changeView(VIEW_ASSET);
    }

    function onShowError(errObj) {
      if (errObj) {
        $('.modal-body', $('#modalError')).html(errObj.messages[0]);
      }
      else {
        $('.modal-body', $('#modalError')).html('Your file is bigger than the 20Mb maximum.  Please reduce the size and try again.');
      }
      $('#modalError').modal('show');
    }

    getJourneys();
  };

  return { 
    initialize: initialize
  };   
});  
