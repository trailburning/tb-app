define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailmakerDeleteTrailView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailmakerDeleteTrailViewTemplate').text());
      
      this.bRendered = false;
    },            
    render: function(nProgress){
      var self = this;
      
      if (!this.bRendered) {                
        var attribs = this.model.toJSON();
        
        $(this.el).html(this.template(attribs));

        $('.proceed', $(this.el)).click(function(evt) {
          $('.confirm_action', self.el).hide();
          $('.action_confirmed', self.el).show();
          // fire event
          app.dispatcher.trigger("TrailmakerDeleteTrailView:proceed", self);                          
        });    	
        
        $('.cancel', $(this.el)).click(function(evt) {
          // fire event
          app.dispatcher.trigger("TrailmakerDeleteTrailView:close", self);                          
        });    	
        
        $('.complete', $(this.el)).click(function(evt) {
          // fire event
          app.dispatcher.trigger("TrailmakerDeleteTrailView:close", self);                          
        });    	
      }
      
      this.bRendered = true;
      
      return this;
    }
  });

  return TrailmakerDeleteTrailView;
});
