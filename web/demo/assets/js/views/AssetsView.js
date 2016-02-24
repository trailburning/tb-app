var HEIGHT_WIDE_ASPECT_PERCENT = 56;
var MAX_ASSETS = 3;

define([
  'underscore', 
  'backbone',
  'turf',
  'views/MapAssetView',
  'views/MapView'
], function(_, Backbone, turf, MapAssetView, MapView, DistanceMarkerView){

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

      this.jsonAssetsLine = {
        "type": "Feature",
        "properties": {},
        "geometry": {
          "type": "LineString",
          "coordinates": []
        }
      };

      var nMaxAssets = MAX_ASSETS;
      this.nCurrAsset = 0;
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

      $.each(this.options.jsonAssets, function(index, jsonAsset) {
        // mla
        jsonAsset.standard_res = 'http://tbmedia2.imgix.net/' + jsonAsset.versions[0].path + '?fm=jpg&q=80&w=1024&fit=fill';
        jsonAsset.thumb_res = 'http://tbmedia2.imgix.net/' + jsonAsset.versions[0].path + '?fm=jpg&q=80&w=128&h=128&fit=crop';
      });

      $.each(this.options.jsonFeed, function(index, item) {
        self.insertFeedAsset(item);
      });

      var fRnd = Math.floor(Math.random() * ((this.options.jsonAssets.length) - 0 + 1)) + 0;
      this.options.jsonAssets.splice(fRnd, 0,
        {type: 'advert', standard_res: 'http://tbassets2.imgix.net/images/competition/_0001_sky.jpg', thumb_res: 'http://tbassets2.imgix.net/images/competition/_0001_sky.jpg', tags: {height: 806, width: 806}}
      );

      // setup blocks
      $.each(this.options.jsonAssets, function(index, jsonAsset) {
        jsonAsset.pos = index;
        // mla - test text
        var fRnd = Math.floor(Math.random() * (4 - 0 + 1)) + 0;
        if (fRnd == 4) {
//        if (index == 0) {
          jsonAsset.about = 'This is example text used to describe this piece of media.';
        }
        processAsset(jsonAsset);
      });
      //one left to push
      if (jsonBlock) {
        self.jsonBlocks.blocks.push(jsonBlock);
      }

      // keyboard control
      $(document).keydown(function(e){
        switch (e.keyCode) {
          case 37: // previous
            if (self.nCurrAsset-1 < 0) {
              self.nCurrAsset = self.options.jsonAssets.length-1;
            }
            else {
              self.nCurrAsset--;
            }
            self.showFullscreenAsset(self.options.jsonAssets[self.nCurrAsset], true);
            break;

          case 39: // next
            if (self.nCurrAsset+1 > self.options.jsonAssets.length-1) {
              self.nCurrAsset = 0;
            }
            else {
              self.nCurrAsset++;
            }
            self.showFullscreenAsset(self.options.jsonAssets[self.nCurrAsset], true);
            break;
        }
      });
    },
    
    resize: function(){
      var nHeightWideAspectPercent = HEIGHT_WIDE_ASPECT_PERCENT;

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

      $('#fs-asset-view-container').click(function(evt){
        $('#fs-asset-view-container').hide();
        $('body').removeClass('fs');

        $('#assets-View').css('visibility', 'visible');
      });

      $('.asset-container', this.el).click(function(evt){
        self.nCurrAsset = Number($(this).attr('data-pos'));
        self.showFullscreen(self.options.jsonAssets[self.nCurrAsset]);
      });

      return this;
    },

    insertFeedAsset: function(jsonFeedAsset) {
      var arrCoords = new Array;
      $.each(this.options.jsonAssets, function(index, jsonAsset) {
        arrCoords.push([jsonAsset.coords.lat, jsonAsset.coords.long]);
      });
      this.jsonAssetsLine.geometry.coordinates = arrCoords;

      // look for point on assets line
      var pt = {
        "type": "Feature",
        "properties": {},
        "geometry": {
          "type": "Point",
          "coordinates": [jsonFeedAsset.location.latitude, jsonFeedAsset.location.longitude]
        }
      }

      var snapped = turf.pointOnLine(this.jsonAssetsLine, pt);
      if (snapped.properties.dist < 1) {
//        console.log(snapped);
        this.options.jsonAssets.splice(snapped.properties.index+1, 0, 
          {type: 'instagram', tags: {width: 1080, height: 1080}, standard_res: jsonFeedAsset.images.standard_resolution.url, thumb_res: jsonFeedAsset.images.low_resolution.url, coords: {lat: jsonFeedAsset.location.latitude, long: jsonFeedAsset.location.longitude}}
        );
      }
    },

    showFullscreenAsset: function(jsonMedia, bAnimate) {
      $('#fs-asset-view .image').removeClass('portrait');

      if (Number(jsonMedia.tags.height) >= Number(jsonMedia.tags.width)) {
        $('#fs-asset-view .image').addClass('portrait');
      }
      $('#fs-asset-view .image').css('background-image', 'url(' + jsonMedia.standard_res + ')');

      $('#fs-asset-view-container').show();
      $('#fs-asset-view .type').hide();
      if (jsonMedia.type == 'instagram') {
        $('#fs-asset-view .type').show();
      }
      this.mapAssetView.focus(jsonMedia, bAnimate);
    },

    showFullscreen: function(jsonMedia) {
      $('body').addClass('fs');

      this.showFullscreenAsset(jsonMedia, false);

      $('#assets-View').css('visibility', 'hidden');
    },

    onMarkerSelect: function(markerView) {
      this.nCurrAsset = markerView.pos;

      switch (markerView.parentID) {
        case MAP_VIEW:
          this.showFullscreen(markerView.jsonMedia);
          break;

        case MAP_ASSET_VIEW:
          break;
      }
    }

  });

  return AssetsView;
});
