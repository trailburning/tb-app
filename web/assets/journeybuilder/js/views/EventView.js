define([
  'underscore', 
  'backbone',
  'jqueryui'
], function(_, Backbone, jqueryui){

  var EventView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#eventViewTemplate').text());

      this.nHeightWideAspectPercent = 0;
      this.nHeightSquareAspectPercent = 0;
      this.eventModel = null;
    },

    hide: function(){
      $(this.el).hide();
    },

    show: function(){
      $(this.el).show();
    },

    updateMediaHeight: function(){
      var self = this;
      $('.scale-container', $(this.el)).each(function(){
        var nHeight = ($(this).width() * self.nHeightWideAspectPercent) / 100;
        $(this).height(nHeight);
      });
    },

    resize: function(nHeightWideAspectPercent, nHeightSquareAspectPercent){
      this.nHeightWideAspectPercent = nHeightWideAspectPercent;
      this.nHeightSquareAspectPercent = nHeightSquareAspectPercent;
      this.updateMediaHeight();
    },

    updatePosition: function(assetID, nPosition){
      var self = this;

      console.log('U:'+nPosition);

      var json = {'position': nPosition};

      var strURL = TB_RESTAPI_BASEURL + '/assets/' + assetID;
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
        data: json,
        error: function(data) {
          console.log('error');
          console.log(data);
        },
        success: function(data) {
          console.log('success');
        }
      }); 
    },
    updateSortOrder: function(){
      var self = this;

      var arrSortedIDs = $(".sortable", this.el).sortable("toArray");
      $.each(arrSortedIDs, function(index, id) {
        var nPos = index + 1;
        console.log(nPos+' : '+id);
        self.updatePosition(id, nPos);
      });
    },

    getEventAndRender: function(journeyModel, eventModel){
      this.journeyModel = journeyModel;
      
      var self = this;

      $(this.el).html('');

      var url = TB_RESTAPI_BASEURL + '/events/' + eventModel.get('id');
      $.getJSON(url, function(result){
        self.eventModel = new Backbone.Model(result.body.events[0]);

        self.render();
      });
    },

    render: function(){
      var self = this;
      
      this.journeyModel.set('event', this.eventModel.toJSON());

      var attribs = this.journeyModel.toJSON();
      $(this.el).html(CRtoBR(this.template(attribs)));

      this.updateMediaHeight();

      $('.create-btn', this.el).click(function(evt){
        // fire event
        app.dispatcher.trigger("EventView:assetCreate");
      });

      $('.asset', this.el).click(function(evt){
        // fire event
        app.dispatcher.trigger("EventView:assetClick", $(this).attr('id'));
      });

      $('.back-btn', this.el).click(function(evt){
        // fire event
        app.dispatcher.trigger("EventView:backClick");
      });

      $('.update-btn', this.el).click(function(evt){
        $('.update-btn', self.el).hide();
        self.updateSortOrder();
      });

      $('.sortable', this.el).sortable({axis: "y", change: function( event, ui ) {
        $('.update-btn', self.el).show();
      }});
      $('.sortable', this.el).disableSelection();

      return this;
    }
    
  });

  return EventView;
});
