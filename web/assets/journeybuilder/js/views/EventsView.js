define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var EventsView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#eventsViewTemplate').text());

      this.eventsCollection = null;
    },

    hide: function(){
      $(this.el).hide();
    },

    show: function(){
      $(this.el).show();
    },

    findAssetCategory: function(assets, strCategory) {
      return assets.filter(function(asset, index){
        return asset.category.name == strCategory;
      });
    },

    getEventsAndRender: function(journeyModel){
      this.journeyModel = journeyModel;

      var self = this;

      $(this.el).html('');

      var url = TB_RESTAPI_BASEURL + '/journeys/' + journeyModel.get('id') + "/events";
      $.getJSON(url, function(result){
        var jsonEvents = result.body.events;

        $.each(jsonEvents, function(index){
          this.step = index + 1;
          this.assetTypes = [];
          if (self.findAssetCategory(this.assets, 'fauna').length) {
            this.assetTypes.push({'shortname': 'fauna', 'name': 'fauna'});
          }
          if (self.findAssetCategory(this.assets, 'flora').length) {
            this.assetTypes.push({'shortname': 'flora', 'name': 'flora'});
          }
          if (self.findAssetCategory(this.assets, 'mountain').length) {
            this.assetTypes.push({'shortname': 'mountain', 'name': 'mountain'});
          }
          if (self.findAssetCategory(this.assets, 'timecapsule').length) {
            this.assetTypes.push({'shortname': 'timecapsule', 'name': 'time capsule'});
          }
          if (self.findAssetCategory(this.assets, 'climatechange').length) {
            this.assetTypes.push({'shortname': 'climatechange', 'name': 'climate change'});
          }
        });

        self.eventsCollection = new Backbone.Collection(jsonEvents);
        self.render();
      });
    },

    render: function(){
      var self = this;
      
      this.journeyModel.set('events', this.eventsCollection.toJSON());

      var attribs = this.journeyModel.toJSON();
      $(this.el).html(CRtoBR(this.template(attribs)));

      $('.back-btn', this.el).click(function(evt){
        // fire event
        app.dispatcher.trigger("EventsView:backClick");
      });

      $('.event', this.el).click(function(evt){
        var eventModel = self.eventsCollection.get($(this).attr('id'));
        // fire event
        app.dispatcher.trigger("EventsView:eventSelect", eventModel);
      });
      return this;
    }
    
  });

  return EventsView;
});
