define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var StepPublishedView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#stepPublishedViewTemplate').text());        
      
      this.bRendered = false;
    },
    render: function(){
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
              
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
        
      return this;
    }
  });

  return StepPublishedView;
});
