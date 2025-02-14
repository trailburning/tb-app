define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailUploadPhotoView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailUploadPhotoViewTemplate').text());
      
      this.photoData = null;
      this.bMultiUpload = false;
    },            
    multiUpload: function(){
      return this.bMultiUpload;
    },
    render: function(){
      var self = this;

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      $('#photofileupload').change(function(evt){
        $('#uploadPhotoprogress_view').show();      	
        self.upload();
      }); 
            
      return this;
    },    
    upload: function(){
      var bValid = true;
      var self = this;
                        
      var strURL = TB_RESTAPI_BASEURL + '/v1/route/'+this.model.get('id')+'/medias/add';      
        
      $.fn.upload = function(remote,successFn,progressFn) {
        return this.each(function() {    
          var formData = new FormData();
      	  var arrFiles = $('input[type="file"]', this)[0].files;
      	  
      	  // multi upload?
      	  if (arrFiles.length > 1) {
      	  	self.bMultiUpload = true;
      	  }
      	  // fire event
          app.dispatcher.trigger("TrailUploadPhotoView:upload", self);                
          
          // add files
          for (var nFile=0; nFile < arrFiles.length; nFile++) {
            formData.append('medias[]', arrFiles[nFile]);          	
          }          
  
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
              if(bValid && successFn) successFn(res);
            },
            success: function(data) {
              self.photoData = data;

        	  $('#uploadPhotoprogress_view').hide();      	
        	  // fire event
        	  app.dispatcher.trigger("TrailUploadPhotoView:uploaded", self);                
            },
            error: function(data) {
//              console.log('error');
              bValid = false;

              var errObj = null;              
              if (data.responseText) {
              	errObj = jQuery.parseJSON(data.responseText);
              }
              self.errObj = errObj;
              
        	  $('#uploadPhotoprogress_view').hide();      	
        	  // fire event
        	  app.dispatcher.trigger("TrailUploadPhotoView:error", self);                
            }
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
