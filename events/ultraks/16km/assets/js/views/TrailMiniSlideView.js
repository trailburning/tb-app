define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMiniSlideView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMiniSlideViewTemplate').text());
      
      this.bRendered = false;        
    },            
    show: function(){
      $(this.el).show();
    },
    hide: function(){
      $(this.el).hide();
    },
    render: function(){
      console.log('TrailMiniSlideView:render');
        
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
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      $('.btn', $(this.el)).click(function(evt){
        // fire event
        app.dispatcher.trigger("TrailMiniSlideView:viewbtnclick", self);                
      });

      this.bRendered = true;

      return this;
    }    
  });

  return TrailMiniSlideView;
});
