define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var Step1View = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#step1ViewTemplate').text());        
      
      this.bRendered = false;
    },
    render: function(){
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
              
      var self = this;              
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
        
      $('.btn', $(this.el)).click(function(evt) {
        // fire event
        app.dispatcher.trigger("Step1View:submitclick", self);                        
      });
        
      return this;
    }
  });

  return Step1View;
});
