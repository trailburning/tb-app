var MAX_ASSETS = 3;

define([
  'underscore', 
  'backbone',
  'views/MapAssetView',
  'views/MapView'
], function(_, Backbone, MapAssetView, MapView, DistanceMarkerView){

  var AssetsView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#assetsViewTemplate').text());

      app.dispatcher.on("MarkerView:click", this.onMarkerSelect, this);

      var self = this;

      this.jsonRoute = {
        "type": "Feature",
        "properties": {
        "name": this.model.get('name'),
        "color": "#000"
        },
        "geometry": {
          "type": "LineString",
          "coordinates": []
        }
      };

      var nMaxAssets = MAX_ASSETS;
      var bAdShown = false;
      this.jsonBlocks = {blocks: []};
      var jsonBlock = {assets: []};

      function writeBlock() {
        // push curr block
        if (jsonBlock.assets.length) {
          self.jsonBlocks.blocks.push(jsonBlock);
          jsonBlock = null;
        }
      }

      function createBlock() {
        // create block for this asset and push
        jsonBlock = {assets: []};
      }

      function pushAsset(jsonAsset) {
        jsonBlock.assets.push(jsonAsset);
      }

      function processAsset(jsonAsset) {
        if (jsonAsset.about != undefined) {
          writeBlock();

          createBlock();
          pushAsset(jsonAsset);
          writeBlock();

          createBlock();
        }
        else if (jsonBlock.assets.length == nMaxAssets) {
          writeBlock();

          createBlock();
          pushAsset(jsonAsset);

          if (nMaxAssets == MAX_ASSETS) {
            nMaxAssets = MAX_ASSETS - 1;
          }
          else {
            nMaxAssets = MAX_ASSETS;
          }
        }
        else {
          pushAsset(jsonAsset);
        }
      }

      // setup blocks
      $.each(this.options.jsonAssets, function(index, jsonAsset) {
        jsonAsset.pos = index;
        // mla - test text
        var fRnd = Math.floor(Math.random() * (4 - 0 + 1)) + 0;
        if (fRnd == 4) {
//        if (index == 0) {
          jsonAsset.about = 'This is example text used to describe this piece of media.';
        }

        if (jsonBlock.assets.length == (nMaxAssets - 1)) {
          var fRnd = Math.floor(Math.random() * (4 - 0 + 1)) + 0;
          if (fRnd == 1 && !bAdShown) {
            bAdShown = true;
            // add placeholder
            var jsonAdAsset = {type: 'advert', filename: 'http://tbassets2.imgix.net/images/competition/_0001_sky.jpg', tags: {height: 806, width: 806}};
            processAsset(jsonAdAsset);
          }
        }

        processAsset(jsonAsset);
      });
      //one left to push
      if (jsonBlock) {
        self.jsonBlocks.blocks.push(jsonBlock);
      }
    },
    
    resize: function(nHeightWideAspectPercent){
      $('.scale-container', $(this.el)).each(function(){
        var nHeight = ($(this).width() * nHeightWideAspectPercent) / 100;
        $(this).height(nHeight);
      });
    },

    render: function(){
      var self = this;

      this.model.set('assetBlocks', this.jsonBlocks);

      var attribs = this.model.toJSON();
      attribs.about = CRtoBR(attribs.about);
      $(this.el).html(this.template(attribs));

      // build geoJSON route
      $.each(this.model.get('route_points'), function(index) {
        self.jsonRoute.geometry.coordinates.push(this.coords);
      });

      this.mapView = new MapView({ jsonRoute: this.jsonRoute, jsonMedia: this.options.jsonAssets });
      this.mapView.render();

      this.mapAssetView = new MapAssetView({ jsonRoute: this.jsonRoute });
      this.mapAssetView.render();

      $('.asset-container', this.el).click(function(evt){
        $('#mapbox-asset-view-container').appendTo($(this).parent());

        $('#mapbox-asset-view-container').show();

        var jsonAsset = self.options.jsonAssets[$(this).attr('data-pos')];
        self.mapAssetView.focus(jsonAsset);
      });

      return this;
    },

    onMarkerSelect: function(markerView) {
      $('#mapbox-asset-view-container').hide();
    }

  });

  return AssetsView;
});
