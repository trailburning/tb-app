define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailSlideView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlideViewTemplate').text());        
            
      this.bRendered = false;
      this.map = null;
      this.polyline = null;
      this.arrLineCordinates = [];
    },            
    show: function(){
      $(this.el).show();
    },
    hide: function(){
      $(this.el).hide();
    },
    render: function(){
      console.log('TrailSlideView:render');
        
      if (!this.model) {
        return;
      }

      if (!this.model.get('id')) {
        return;
      }
       
      // already rendered?  Just update
      if (this.bRendered) {
        return;         
      }        
                
      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
                        
      this.bRendered = true;
                        
      return this;
    }    
  });

  return TrailSlideView;
});
