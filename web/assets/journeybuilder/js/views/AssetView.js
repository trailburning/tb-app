define([
  'underscore', 
  'backbone',
  'jqueryui',
  'bootstrap',
  'views/AssetMediaView'
], function(_, Backbone, jqueryui, bootstrap, AssetMediaView){

  var AssetView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#assetViewTemplate').text());

      app.dispatcher.on("AssetMediaView:deleteClick", this.onMediaDelete, this);

      this.eventModel = null;
      this.assetModel = null;
    },

    hide: function(){
      if (this.assetMediaView) {
        this.assetMediaView.hide();
      }
      $(this.el).hide();
    },

    show: function(){
      $(this.el).show();
    },

    mediaUploaded: function(){
      $('#modalUploadProgress').modal('hide');
      this.getAssetMediaAndRender();
    },

    uploadMedia: function(){
      var bValid = true;
      var self = this;

      $('#modalUploadProgress').modal('show');
      $('.progressbar', self.el).progressbar({value: 0});
      $('.percent', self.el).html(0);

      var strURL = TB_RESTAPI_BASEURL + '/assets/' + this.assetModel.get('id') + '/media';

      $.fn.upload = function(remote,successFn,progressFn) {
        return this.each(function() {
          var formData = new FormData();
          formData.append('media', $('input[type="file"]', this)[0].files[0]);
          $.ajax({
            url: remote,
            type: 'POST',
            dataType: 'json',
            xhr: function() {
              var myXhr = $.ajaxSettings.xhr();
              if(myXhr.upload && progressFn) {
                myXhr.upload.addEventListener('progress',progressFn, false);
              }
              return myXhr;
            },
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            complete : function(res) {
              console.log('complete');
              if(bValid && successFn) successFn(res);
            },
            success: function(data) {
              console.log(data);
            },
            error: function(data) {
              $('#modalUploadProgress').modal('hide');

              bValid = false;
              
              var errObj = null;
              if (data.responseText) {
                errObj = jQuery.parseJSON(data.responseText);
              }
              // fire event
              app.dispatcher.trigger("AssetView:mediauploadError", errObj);
            }
          });
        });
      };   
      
      $('#uploadMediaForm').upload(strURL, function(res) {
        console.log(res);
        self.mediaUploaded();
      },function(data) {
        var nProgress = parseInt(data.loaded / data.total * 100, 10);
        console.log(nProgress);

        $('.progressbar', self.el).progressbar({value: nProgress});
        $('.percent', self.el).html(nProgress);
      });
    },

    removeAsset: function(){
      if (this.assetModel.get('id')) {
        var strURL = TB_RESTAPI_BASEURL + '/assets/' + this.assetModel.get('id');
        $.ajax({
          type: "DELETE",
          dataType: "json",
          url: strURL,
          error: function(data) {
            console.log('error');
            console.log(data);
          },
          success: function(data) {
            console.log('success');
            // fire event
            app.dispatcher.trigger("AssetView:removed");
          }
        }); 
      }
    },

    updateAsset: function(elForm){
      var self = this;

//      var $btn = $('#save-asset-btn').button('loading')

      // replace newline
      var strAbout = $('#form_about', elForm).val().replace(/(?:\n)/g, '\r')

      var json = {'category': $('#form_category:checked', elForm).val(),
                  'name': $('#form_name', elForm).val(),
                  'credit': $('#form_credit', elForm).val(),
                  'about': strAbout};

      if (this.assetModel.get('id')) {
        var strURL = TB_RESTAPI_BASEURL + '/assets/' + this.assetModel.get('id');
        $.ajax({
          type: "PUT",
          dataType: "json",
          url: strURL,
          data: json,
          error: function(data) {
//            console.log('error');
//            console.log(data);
          },
          success: function(data) {
//            console.log('success');
//            $btn.button('reset');
          }
        }); 
      }
    },

    getAssetAndRender: function(journeyModel, eventModel, assetID){
      this.journeyModel = journeyModel;
      this.eventModel = eventModel;

      $(this.el).html('');

      var self = this;

      $('.asset-view', $(this.el)).hide();

      if (assetID) {
        var url = TB_RESTAPI_BASEURL + '/assets/' + assetID;
        $.getJSON(url, function(result){
          var jsonAsset = result.body.assets[0];
          self.assetModel = new Backbone.Model(jsonAsset);
          self.render();
          self.getAssetMediaAndRender();
        });
      }
      else {
        self.assetModel = new Backbone.Model({category: {name: 'expedition'}});
        self.render();
      }

      $('.media-collection-view').hide();
    },

    getAssetMediaAndRender: function(){
      var self = this;

      var url = TB_RESTAPI_BASEURL + '/assets/' + this.assetModel.get('id');
      $.getJSON(url, function(result){
        var jsonAsset = result.body.assets[0];
        self.assetModel = new Backbone.Model(jsonAsset);

        $('.media-collection-view', $(self.el)).show();
        self.assetMediaView.render(self.assetModel);
      });
    },

    render: function(){
      var self = this;

      this.journeyModel.set('asset', this.assetModel.toJSON());

      var attribs = this.journeyModel.toJSON();
      $(this.el).html(this.template(attribs));

      this.assetMediaView = new AssetMediaView({ el: '#media-view' });

      $('.remove-btn', this.el).click(function(evt){
        $('#modalConfirmAssetDelete').modal('show');
      });

      $('.confirm-delete', this.el).click(function(evt){
        $('#modalConfirmAssetDelete').modal('hide');
        self.removeAsset();
      });

      $("#assetForm").submit(function(evt){
        evt.preventDefault();

        self.updateAsset(this);
      });

      $('#mediafileupload').change(function(){
        self.uploadMedia();
      }); 

      $('.back-btn', this.el).click(function(evt){
        // fire event
        app.dispatcher.trigger("AssetView:backClick");
      });

      return this;
    },

    onMediaDelete: function(mediaID) {
      var self = this;

      var assetID = this.assetModel.get('id');

      var strURL = TB_RESTAPI_BASEURL + '/assets/' + assetID + '/media/' + mediaID;
      
      $.ajax({
        type: "DELETE",
        url: strURL,
        error: function(data) {
          console.log('error');
          console.log(data);
        },
        success: function(data) {
          console.log('success');

          self.getAssetMediaAndRender();
        }
      });
    }
    
  });

  return AssetView;
});
