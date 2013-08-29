define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMediaUploadView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMediaUploadViewTemplate').text());
    },            
    render: function(){
      console.log('TrailMediaUploadView:render');
        
      if (!this.model) {
        return;
      }
        
      var self = this;

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      
      $(":file", $('#myMediaForm')).filestyle({classButton: "btn btn-primary"});      
      $(".upload", $('#myMediaForm')).on('click', function () {
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
              console.log(data);
              console.log('msg:'+data.message);
            },
          });
        });
      };      
      
      $('#myMediaForm').upload(strURL, function(res) {
        console.log("done",res);
        // fire event
        app.dispatcher.trigger("TrailMediaUploadView:uploaded", self);                
      },function(data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        // fire event
        app.dispatcher.trigger("TrailMediaUploadView:uploadProgress", progress);                
      });                        
    }        
  });

  return TrailMediaUploadView;
});
