define([
  'underscore', 
  'backbone',
  'piste',
  'views/Expedition3DView'
], function(_, Backbone, piste, Expedition3DView){

  var ExpeditionView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#expeditionViewTemplate').text());

      app.dispatcher.on("Expedition3DView:mediaSelect", this.on3DMediaSelect, this);

      this.expedition3DView = new Expedition3DView({ collection: this.collection });

      this.eventModel = null;
    },

    hide: function(){
      $(this.el).hide();
      this.expedition3DView.hide();
    },

    show: function(){
      $(this.el).show();
      this.expedition3DView.show();
    },

    findAssetCategory: function(assets, strCategory){
      return assets.filter(function(asset, index){
        return asset.category == strCategory;
      });
    },

    render: function(){
      var self = this;

      this.collection.each(function(model){
        model.set('assetTypes', []);
        if (self.findAssetCategory(model.get('assets'), 'fauna').length) {
          model.get('assetTypes').push({'name': 'fauna'});
        }
        if (self.findAssetCategory(model.get('assets'), 'flora').length) {
          model.get('assetTypes').push({'name': 'flora'});
        }
        if (self.findAssetCategory(model.get('assets'), 'mountain').length) {
          model.get('assetTypes').push({'name': 'mountain'});
        }
        if (self.findAssetCategory(model.get('assets'), 'timecapsule').length) {
          model.get('assetTypes').push({'name': 'timecapsule'});
        }
      });

      this.model.set('journeyEvents', this.collection.toJSON());
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      this.expedition3DView.render();

      var myContainer = $('.events-container', this.el);

      $('.event', this.el).click(function(evt){
        if ($(this).hasClass('active')) {
          // fire event
          app.dispatcher.trigger("ExpeditionView:eventSelect", $(this).attr('id'), 'expedition');
        }
        else {
          self.eventModel = self.collection.get($(this).attr('id'));

          $('.event').removeClass('active');
          $(this).addClass('active');

          self.expedition3DView.gotoPoint($(this).attr('id'));
        }
      });

      $('.category-btn', this.el).click(function(evt){
        evt.stopPropagation();

        var elEvent = $(this).closest('.event');

        self.eventModel = self.collection.get(elEvent.attr('id'));

        $('.event').removeClass('active');
        elEvent.addClass('active');

        self.expedition3DView.gotoPoint(elEvent.attr('id'));

        // fire event
        app.dispatcher.trigger("ExpeditionView:eventSelect", elEvent.attr('id'), $(this).attr('id'));
      });

      function sync() {
        var elEvent = $('.event[id='+self.eventModel.get('id')+']', myContainer);

        $('.event').removeClass('active');
        elEvent.addClass('active');

        myContainer.animate({
          scrollTop: elEvent.offset().top - myContainer.offset().top + myContainer.scrollTop()
        });

        self.expedition3DView.gotoPoint(self.eventModel.get('id'));
      }

      $('.prev-btn', this.el).click(function(evt){
        if (!self.eventModel) {
          // initial
          self.eventModel = self.collection.at(self.collection.length - 1);
        }
        else {
          var nIndex = self.collection.indexOf(self.eventModel) - 1;
          if (nIndex < 0) {
            nIndex = self.collection.length - 1;
          }
          self.eventModel = self.collection.at(nIndex);
        }
        sync();
      });

      $('.next-btn', this.el).click(function(evt){
        if (!self.eventModel) {
          // initial
          self.eventModel = self.collection.at(0);
        }
        else {
          var nIndex = self.collection.indexOf(self.eventModel) + 1;
          if (nIndex >= self.collection.length) {
            nIndex = 0;
          }
          self.eventModel = self.collection.at(nIndex);
        }
        sync();
      });

      return this;
    },

    on3DMediaSelect: function(strID) {
      // fire event
      app.dispatcher.trigger("ExpeditionView:eventSelect", strID, 'expedition');
    }

  });

  return ExpeditionView;
});
