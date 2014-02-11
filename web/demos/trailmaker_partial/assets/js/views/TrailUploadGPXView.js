define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailUploadGPXView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailUploadGPXViewTemplate').text());
    },            
    render: function(){
      var self = this;

      $(this.el).html(this.template());

	  // mla test
        // fire event
        app.dispatcher.trigger("TrailUploadGPXView:uploaded", self);                
		return;

	  // mla test
      $('#btntest').click(function(evt){
      	evt.stopPropagation();
        // fire event
        app.dispatcher.trigger("TrailUploadGPXView:uploaded", self);                
      });

      $('#gpxfileupload').change(function(){
        $('#uploadGPX_view').hide();
        $('#uploadGPXprogress_view').show();
        self.upload();
      }); 
                            
      return this;
    },
    upload: function(){
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
      
      $('#uploadGPXForm').upload(strURL, function(res) {
        // fire event
        app.dispatcher.trigger("TrailUploadGPXView:uploaded", self);                
      },function(data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);          
        // fire event
        app.dispatcher.trigger("TrailUploadGPXView:uploadProgress", progress);                
      });                        
    }        
  });

  return TrailUploadGPXView;
});
