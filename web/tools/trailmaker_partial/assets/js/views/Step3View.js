define([
  'underscore', 
  'backbone',
  'views/TrailMapView'  
], function(_, Backbone, TrailMapView){

  var Step3View = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#step3ViewTemplate').text());        
      
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

  return Step3View;
});
