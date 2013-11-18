define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var StepWelcomeView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#stepWelcomeViewTemplate').text());        
      
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

  return StepWelcomeView;
});
