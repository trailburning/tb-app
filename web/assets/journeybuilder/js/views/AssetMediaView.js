define([
  'underscore', 
  'backbone',
  'videojs'
], function(_, Backbone, videojs){

  var AssetMediaView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#assetMediaViewTemplate').text());

      this.arrAssetMediaView = new Array();
      this.mediaID = null;
    },

    hide: function(){
      this.destroyMedia();
      $(this.el).hide();
    },

    show: function(){
      $(this.el).show();
    },

    destroyMedia: function(){
      $.each(this.arrAssetMediaView, function(index, objMedia) {
        objMedia.pause();
        objMedia.dispose();
      });
      while (this.arrAssetMediaView.length) { 
        this.arrAssetMediaView.pop(); 
      }
    },

    render: function(assetModel){
      this.model = assetModel;

      var self = this;

      this.destroyMedia();

console.log(this.model);

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      $('.delete-btn', this.el).click(function(evt){
        self.mediaID = $(this).closest('.media-view').attr('data-id')
        $('#modalConfirmMediaDelete').modal('show');
      });

      $('.confirm-delete', this.el).click(function(evt){
        $('#modalConfirmMediaDelete').modal('hide');
        // fire event
        app.dispatcher.trigger("AssetMediaView:deleteClick", self.mediaID);
      });

      $('.media-player', $(this.el)).each(function(){
        var objMedia = videojs($(this).attr('id'));
        objMedia.src($(this).attr('url'));
        objMedia.load();

        self.arrAssetMediaView.push(objMedia);
      });

      return this;
    }

  });

  return AssetMediaView;
});
