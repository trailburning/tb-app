define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailUploadPhotoView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailUploadPhotoViewTemplate').text());
      
      this.photoData = null;
    },            
    render: function(){
      var self = this;

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      
      $('#photofileupload').change(function(){
        $('#uploadPhoto_view').hide();
        $('#uploadPhotoprogress_view').show();      	
        self.upload();
      }); 
      
      $(".upload", $('#uploadPhotoForm')).on('click', function () {
        self.upload();      
      });
            
      return this;
    },    
    upload: function(){
      console.log('TrailMediaUploadView:upload');
        
      var self = this;
        
      var strURL = RESTAPI_BASEURL + 'v1/route/'+this.model.get('id')+'/medias/add';      
        
      $.fn.upload = function(remote,successFn,progressFn) {
        return this.each(function() {    
          var formData = new FormData();
          formData.append('medias[]', $('input[type="file"]', this)[0].files[0]);
  
          $.ajax({
            url: remote,
            type: 'POST',            
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
              if(successFn) successFn(res);
            },
            success: function(data) {
              self.photoData = data;

        	  $('#uploadPhotoprogress_view').hide();      	
        	  $('#uploadPhoto_view').show();
        	  // fire event
        	  app.dispatcher.trigger("TrailUploadPhotoView:uploaded", self);                
            },
          });
        });
      };      
      
      $('#uploadPhotoForm').upload(strURL, function(res) {
      },function(data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        // fire event
        app.dispatcher.trigger("TrailUploadPhotoView:uploadProgress", progress);                
      });                        
    }        
        
  });

  return TrailUploadPhotoView;
});
