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
              
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
        
      return this;
    }
  });

  return Step1View;
});
