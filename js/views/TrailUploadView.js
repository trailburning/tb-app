define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailUploadView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailUploadViewTemplate').text());
    },            
    render: function(){
      console.log('TrailUploadView:render');
        
      if (!this.model) {
        return;
      }
        
      var self = this;

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      
      $(":file", $('#myForm')).filestyle({classButton: "btn btn-primary"});      
      $(".upload", $('#myForm')).on('click', function () {
        self.upload();      
      });
            
      return this;
    },
    upload: function(){
      console.log('TrailUploadView:upload');
        
      var self = this;
        
      var strURL = RESTAPI_BASEURL + 'v1/import/gpx';      
        
      $.fn.upload = function(remote,successFn,progressFn) {
        return this.each(function() {    
          var formData = new FormData();
          formData.append('gpxfile', $('input[type="file"]', this)[0].files[0]);  
  
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
              if (data.value.route_ids.length) {
                self.model.set('id', data.value.route_ids[0]);  
              }                            
            },
          });
        });
      };   
      
      $('#myForm').upload(strURL, function(res) {
        console.log("done",res);
        // fire event
        app.dispatcher.trigger("TrailUploadView:uploaded", self);                
      },function(data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);          
        // fire event
        app.dispatcher.trigger("TrailUploadView:uploadProgress", progress);                
      });                        
    }        
  });

  return TrailUploadView;
});
