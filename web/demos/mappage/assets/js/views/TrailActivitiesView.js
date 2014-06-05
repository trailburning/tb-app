define([
  'underscore', 
  'backbone',
  'views/TrailActivityView',  
], function(_, Backbone, TrailActivityView){

  var TrailActivitiesView = Backbone.View.extend({
    defaults: {
  	  bReadonly: false
	},  	
    initialize: function(){
      this.options = _.extend({}, this.defaults, this.options);
    	
      var self = this;
    	
      app.dispatcher.on("TrailActivityView:add", self.onTrailActivityViewAdd, this);
      app.dispatcher.on("TrailActivityView:remove", self.onTrailActivityViewRemove, this);
    	
      this.bRendered = false;
    },
    render: function(){
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
  
  	  var self = this;
  	                            
      // get trail activities
      var strURL = TB_RESTAPI_BASEURL + '/v1/attribute/activity/list';      
      $.ajax({
        type: "GET",
        dataType: "json",
        url: strURL,
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
          // populate list
	      $.each(data.value.attributes, function(key, attribute) {
	      	var model = new Backbone.Model(attribute);
	      	var view = new TrailActivityView({ model: model, bReadonly: self.options.bReadonly });
	      	$(self.el).append(view.render().el);
	      });
	      // apply selections
		  if (self.model.get('value').route.attributes != undefined) {
	        $.each(self.model.get('value').route.attributes, function(key, attribute) {
	      	  $('.activity[data-id='+attribute.id+']').addClass('active');
            });
         }
        }
      });                  
      return this;
    },
    onTrailActivityViewAdd: function(trailActivityView){    
      if (this.options.bReadonly) {
      	return;
      }
      
      var strURL = TB_RESTAPI_BASEURL + '/v1/route/' + this.model.id + '/attribute/' + trailActivityView.model.id;
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
        error: function(data) {
//          console.log('error:'+data.responseText);      
//          console.log(data);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
        }
      });        
    },
    onTrailActivityViewRemove: function(trailActivityView){    
      if (this.options.bReadonly) {
      	return;
      }
    	
      var strURL = TB_RESTAPI_BASEURL + '/v1/route/' + this.model.id + '/attribute/' + trailActivityView.model.id;
      $.ajax({
        type: "DELETE",
        dataType: "json",
        url: strURL,
        error: function(data) {
//          console.log('error:'+data.responseText);      
//          console.log(data);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
        }
      });        
    }
        
  });

  return TrailActivitiesView;
});
