
function mainMapEntryPoint(params){


  // filters should be initialized before ocTiles first load
  initFilter(params);

  addOCTileLayer(params);

  mapClickInit(params);

  addLayerChangeCallback(params);


  if(params.isFullScreenMap){
    initFullScreenMapControls(params);
  }

  initControls(params);


}

function addOCTileLayer(params) {

  var map = DynamicMapServices.getMapObject(params.mapId);

  var ocLayer = new ol.layer.Tile({
    source: getOcTailSource(params),
    visible: true,
    zIndex: 100,
    wrapX: true,
  });

  OcLayerServices.setOcLayerName(ocLayer, 'oc_okapiTiles');

  // add oc-Tiles layer
  map.addLayer(ocLayer);
}

function getOcTailSource(params, addRandomParam) {

  var ocTileUrl = '/MainMapAjax/getMapTile/{x}/{y}/{z}/'+params.userId;

  // collect filter params, search data etc.
  urlParamsArr = getCommonUrlParams();

  if ( addRandomParam != undefined ) {
    t = new Date();
    urlParamsArr.push("rand=" + "r" + t.getTime());
  }

  if(urlParamsArr.length > 0){
    ocTileUrl += '?' + urlParamsArr.join('&');
  }

  return new ol.source.TileImage({
    url: ocTileUrl,
    opacity: 1,
    wrapDateLine: false,
    wrapX: true,
    noWrap: false
  })

}

function getCommonUrlParams(){
  urlParamsArr = [];

  if ( params.searchData ) {
    urlParamsArr.push("searchdata="+params.searchdata);
  }

  // load filters data
  $.each( dumpFiltersToJson(), function(key,val) {

    if( typeof(val) === typeof(true) ){
      if( val ){ // for booleans: skip false props, skip "true" value
        urlParamsArr.push(key);
      }
    } else {
      urlParamsArr.push(key+'='+val);
    }

  });

  // load cacheSet data if necessary
  if( $('#mapFilters #csEnabled').length ) { // cacheSet menu present
    if( $('#mapFilters #csEnabled').prop("checked") ) { // and cacheSet enabled
      csIdEl = $('#mapFilters #csId');
      urlParamsArr.push( "csId="+csIdEl.val() );
    }
  }

  return urlParamsArr;
}

function mapClickInit(params) {

  var map = DynamicMapServices.getMapObject(params.mapId);

  var pendingClickRequest = null; //last click ajax request
  var pendingClickRequestTimeout = 10000; // default timeout - in milliseconds

  var compiledPopupTpl = null;

  /**
   * Cancel previous ajax requests
   */
  var _abortPreviousRequests = function () {
    if (pendingClickRequest) {
      pendingClickRequest.abort();
      pendingClickRequest = null;
    }
  }

  /**
   * Returns extent 32px x 32px with center in coordinates
   */
  var _getClickBounds = function (coords) {
    unitsPerPixel = map.getView().getResolution();
    circleClicked = new ol.geom.Circle(coords, 16*unitsPerPixel)
    return circleClicked.getExtent()
  }

  var _displayClickMarker = function (coords) {

    clickMarker = map.getOverlayById('mapClickMarker')
    if (clickMarker==null) { //clickMarker is undefined
      // prepare map click marker overlay.

      var clickMarkerDiv = $('<div id="mapClickMarker"></div>');
      map.addOverlay( new ol.Overlay(
          {
            id: 'mapClickMarker',
            element: clickMarkerDiv[0],
            position: coords,
            autoPanAnimation: {
              duration: 250
            },
          }
      ))
    } else {
      clickMarker.setPosition(coords)
    }
  }

  var _hideClickMarker = function () {
    clickMarker = map.getOverlayById('mapClickMarker')
    if ( clickMarker ) { // clickMarker is present
      clickMarker.setPosition(undefined)
    }
  }

  var _hidePopup = function() {
    popup = map.getOverlayById('mapPopup')
    if ( popup ) { // clickMarker is present
      popup.setPosition(undefined)
    }
  }

  var _getPopupDataUrl = function(coords) {

    var url='/MainMapAjax/getPopupData/';

    // add bbox in SWNE format (see OKAPI)
    var extent = _getClickBounds(coords);
    swCorner = ol.proj.transform(ol.extent.getBottomLeft(extent),'EPSG:3857','EPSG:4326');
    neCorner = ol.proj.transform(ol.extent.getTopRight(extent),'EPSG:3857','EPSG:4326');
    url += swCorner[1]+'|'+swCorner[0]+'|'+neCorner[1]+'|'+ neCorner[0];

    // add userId param
    url += '/' + params.userId;

    // collect filter params, search data etc.
    urlParamsArr = getCommonUrlParams();
    if(urlParamsArr.length > 0){
      url += '?' + urlParamsArr.join('&');
    }

    return url;
  }

  var onLeftClickFunc = function(coords) {

    _abortPreviousRequests();

    _displayClickMarker(coords);

    pendingClickRequest = jQuery.ajax({
      url: _getPopupDataUrl(coords),
    });

    pendingClickRequest.always( function() {
      _hideClickMarker();
      pendingClickRequest = null;
    });

    pendingClickRequest.done( function( data ) {

      if (data === null) { // nothing to display
          _hidePopup();
          return; //nothing more to do here
      }

      popup = map.getOverlayById('mapPopup');
      if (popup == null) {

        // there is no popup object - create it
        map.addOverlay( popup = new ol.Overlay(
            {
              id: 'mapPopup',
              element: $('<div id="mapPopup"></div>')[0],
              position: undefined, // will be set below
              autoPan: true,
              autoPanAnimation: {
                duration: 250
              },
            }
        ));

        /* TODO
        // assign click on popup close button handler
        $("#mapPopup-closer").click(function() {
          popup.setPosition(undefined);
          return false;
        });
        */
      }

      // load popup data
      if(compiledPopupTpl == null){
        var popupTpl = $("#mainMapPopupTpl").html();
        var compiledPopupTpl = Handlebars.compile(popupTpl);
      }
      $('#mapPopup').html(compiledPopupTpl(data));

      var cacheCords = ol.proj.transform([data.coords.lon,data.coords.lat],'EPSG:4326','EPSG:3857');
      popup.setPosition(cacheCords);


    });
  };

  var onRightClickFunc = function(coords) {
    _abortPreviousRequests();
    _displayClickMarker(coords)
    //todo
  }

  /**********************************/
  /** init handlers                          */
  /**********************************/

  // assign left-click handler
  map.on("singleclick", function(evt) {
    onLeftClickFunc(evt.coordinate)
  })

  // asign right-click handler
  map.getViewport().addEventListener('contextmenu', function (evt) {
    evt.preventDefault()
    onRightClickFunc(map.getEventCoordinate(evt))
  })

  if(params.openPopupAtCenter){
    onLeftClickFunc(map.getView().getCenter());
  }

}

function dumpFiltersToJson() {

  var json = {};

  // get elements marked as filter params
  $( '#mapFilters .filterParam' ).each(function() {

    if( $(this).is('input[type=checkbox]') ){
      json[$(this).prop('id')] = $(this).prop("checked");

    } else if ( $(this).is('input') ) {
      json[$(this).prop('id')] = $(this).val();

    } else if ( $(this).is('select') ) {
      json[$(this).prop('id')] = $(this).val();

    } else {
      console.err('Unknown filter param?!');
    }
  });

  return json;
}

function initFilter(params) {

  // set filter values saved at server side
  $.each(params.initUserPrefs.filters, function(key, val) {

    var el = $("#mapFilters #"+key);
    if (el.is("input[type=checkbox]")) {
      el.prop('checked', val);
    } else if (el.is("select")) {
      el.val(val);
    } else {
      console.error('Unknown saved element?!:'+key+":"+val);
    }
  });

  /**
   * Filters changed - ocLayer should be refreshed
   */
  var refreshOcTiles = function (refreshTiles) {

    var map = DynamicMapServices.getMapObject(params.mapId);

    // refresh OC-tile layer
    ocLayer = map.getLayers().forEach(function (layer){
      if( OcLayerServices.getOcLayerName(layer) === 'oc_okapiTiles' ) {
        layer.setSource(getOcTailSource(params, refreshTiles));
      }
    });

    // save user map settings to server
    saveUserSettings(params);
  }

  // add filters click handlers
  $('#mapFilters input.filterParam').click(function() {
    refreshOcTiles()
  })

  $('#mapFilters select.filterParam').change(function() {
    refreshOcTiles()
  })

  $('#refreshButton').click(function() {
    refreshOcTiles(true);
  });

}

function addLayerChangeCallback(params){
  DynamicMapServices.addMapLayerSwitchCallback(params.mapId, function(layerName){
    saveUserSettings(params);
  });
}

function saveUserSettings(params) {

  let json = {
      "filters": dumpFiltersToJson(),
      "map": DynamicMapServices.getSelectedLayerName(params.mapId),
  };

  //"map" : DynamicMapServices.getCurrentLayerName(),

  $.ajax({
    type: "POST",
    dataType: 'json',
    url: "/MainMapAjax/saveMapSettingsAjax",
    data: { 'userMapSettings': JSON.stringify( json ) },
    success: function() {
      console.info("User preferences saved.")
    },
    error: function() {
      console.error("Can't save user map preferences!")
    },
  });

}



function initFullScreenMapControls(params) {
    var map = DynamicMapServices.getMapObject(params.mapId);

    map.addControl(new ol.control.Control(
        {
          element: $("#mainMapControls")[0],
        }
    ));

    let filtersDiv = $('#mapFilters');
    filtersDiv.toggle(false); // to be sure filters are hidden now

    // add filters as map control
    map.addControl(new ol.control.Control(
        {
          element: filtersDiv[0],
        }
    ));

    $('#filtersToggle').click(function() {

      // hide/display filters box
      filtersDiv.toggle()
    });

}

function initControls(params){
  var map = DynamicMapServices.getMapObject(params.mapId);


  // init fullscreen - embeded map toggler
  $('#fullscreenToggle').click(function() {

    zoom = map.getView().getZoom();
    coords = map.getView().getCenter();
    projection = map.getView().getProjection().getCode();
    wgs84Coords = ol.proj.transform(coords, projection, 'EPSG:4326');

    if(params.isFullScreenMap){
      var uri = '/MainMap/embeded/';
    }else{
      var uri = '/MainMap/fullscreen/';
    }
    uri += '?lon=' + wgs84Coords[0] + '&lat=' + wgs84Coords[1];
    uri += '&zoom=' + zoom;

    window.location.href = uri;
  });

}



