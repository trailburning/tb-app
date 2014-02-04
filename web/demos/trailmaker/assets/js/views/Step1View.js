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

      $('#form_name').val('TB Crew');
	  $('#form_email').val('hello@trailburning.com');
      $('#form_event').val('Grunewald');
      $('#form_trail').val('Berlin');
        
      // validate form  
      $('form', $(this.el)).validationEngine();
        
      $('.submit', $(this.el)).click(function(evt) {
        if ($('form', $(self.el)).validationEngine('validate')) {
          // store form        
          self.model.set('name', $('#form_name').val());
          self.model.set('email', $('#form_email').val());
          self.model.set('event_name', $('#form_event').val());
          self.model.set('trail_name', $('#form_trail').val());
          self.model.set('trail_notes', $('#form_notes').val());
          // fire event
          app.dispatcher.trigger("Step1View:submitclick", self);                        
        }
      });
        
      return this;
    }
  });

  return Step1View;
});
