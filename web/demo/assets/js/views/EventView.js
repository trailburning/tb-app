define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var EventView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#eventViewTemplate').text());
    },

    hide: function(){
      $(this.el).hide();
    },

    show: function(){
      $(this.el).show();
    },
    resize: function(nHeightAspectPercent){
      $('.scale-container', $(this.el)).each(function(){
        var nHeight = ($(this).width() * nHeightAspectPercent) / 100;
        $(this).height(nHeight);
      });
    },
    render: function(eventModel){
      var self = this;
      
      this.model = eventModel;

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      $('.category-btn', this.el).click(function(evt){
        // fire event
        app.dispatcher.trigger("EventView:categorySelect", self.model.get('id'), $(this).attr('id'));
      });

      $('.scale-container', this.el).click(function(evt){
        // fire event
        app.dispatcher.trigger("EventView:assetSelect", $(this).attr('id'));
      });

      return this;
    }
    
  });

  return EventView;
});
