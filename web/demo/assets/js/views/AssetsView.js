define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var AssetsView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#assetsViewTemplate').text());

      $.each(this.options.jsonAssets, function(index, jsonAsset) {
        // mla - test text
        var fRnd = Math.floor(Math.random() * (1 - 0 + 1)) + 0;
        if (fRnd == 1) {
          jsonAsset.about = 'This is example text used to describe this piece of media.';
        }
      });

    },
    
    resize: function(nHeightWideAspectPercent){
      $('.scale-container', $(this.el)).each(function(){
        var nHeight = ($(this).width() * nHeightWideAspectPercent) / 100;
        $(this).height(nHeight);
      });
    },

    render: function(){
      var self = this;

      this.model.set('assets', this.options.jsonAssets);

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      return this;
    }
    
  });

  return AssetsView;
});
