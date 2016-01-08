define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var JourneysView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#journeysViewTemplate').text());
    },

    hide: function(){
      $(this.el).hide();
    },

    show: function(){
      $(this.el).show();
    },

    getJourneysAndRender: function(){
      var self = this;

      $(this.el).html('');

      var url = TB_RESTAPI_BASEURL + '/journeys/user/' + TB_USER;
      $.getJSON(url, function(result){
        self.model = new Backbone.Model(result.body);

        self.journeysCollection = new Backbone.Collection(result.body.journeys);
        self.render();
      });
    },

    render: function(){
      var self = this;
      
      var attribs = this.model.toJSON();
      $(this.el).html(CRtoBR(this.template(attribs)));

      $('.journey', this.el).click(function(evt){
        var journeyModel = self.journeysCollection.get($(this).attr('id'));
        // fire event
        app.dispatcher.trigger("JourneysView:journeySelect", journeyModel);
      });
      return this;
    }
    
  });

  return JourneysView;
});
