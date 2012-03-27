/*
	copyright 2012 welocally. NO WARRANTIES PROVIDED
*/

function WELOCALLY_PlacesMultiWidget (cfg) {	
	
	this._map;
	this._map_canvas;
	this._searchLocation;
	this._cfg;
	this._options;
	this._placeMarkers = [];
	this._mapStatus;
	this._results;
	this._selectedSection;
	this._infoBox;
	this._boxText;

	this.init = function() {
		
		this.initCfg(cfg);
		
		// Get current script object
		var script = jQuery('SCRIPT');
		script = script[script.length - 1];

		var wrapper = this.makeWrapper();
	
		jQuery(script).parent().before(wrapper);
		jQuery(this._map_canvas).show();	
		
		
		jQuery(wrapper).show();	
		
		return this;
					
	};
	
}

WELOCALLY_PlacesMultiWidget.prototype.initCfg = function(cfg) {
	var error;
	if (!cfg) {
		error = "Please provide configuration for the widget";
		cfg = {};
	}
	
	// summary (optional) - the summary of the article
	// hostname (optional) - the name of the host to use
	if (!cfg.endpoint) {
		cfg.endpoint = 'http://stage.welocally.com';
	}
	
	if (!cfg.imagePath) {
		cfg.imagePath = 'http://placehound.com/images';
	}
	
	if (!cfg.zoom) {
		cfg.zoom = 16;
	}
			
		
	this._cfg = cfg;
	
	
	return this;
	
};

WELOCALLY_PlacesMultiWidget.prototype.makeWrapper = function() {
	// Build wrapper
	var wrapper = jQuery('<div></div>');		
	jQuery(wrapper).attr('class','wl_places_multi_widget');
	jQuery(wrapper).attr('id','wl_places_multi_widget_'+cfg.id);
	
	//selected area
	this._selectedSection = 
		jQuery('<div class="wl_places_multi_selected"></div>');
	jQuery(this._selectedSection).css('display', 'none');
	jQuery(wrapper).append(this._selectedSection);
	
	//google maps does not like jquery instances
	this._map_canvas = document.createElement('DIV');
	jQuery(this._map_canvas).css('display','none');	
    jQuery(this._map_canvas).attr('class','wl_places_multi_map_canvas');
	jQuery(this._map_canvas).attr("id","welocally_places_multi_map_canvas_"+cfg.id);
	jQuery(wrapper).append(this._map_canvas);
	
	this._mapStatus = jQuery('<div class="wl_places_multi_map_status"></div>');
	jQuery(wrapper).append(this._mapStatus);
	
	//results
	this._results = 
		jQuery('<ol id="wl_places_mutli_selectable"></ol>');	
	jQuery(this._results).selectable();
	jQuery(this._results).css('display','none');
	jQuery( this._results ).bind( "selectableselected", {instance: this}, this.selectedItemHandler);
	jQuery(wrapper).append(this._results);
	

	if(this._cfg.places != null && this._cfg.places.length>0){
		this.setPlaces(this._cfg.places);
	} else {
		//just the map init
		this._map = this.initMap(this._map_canvas);
	}
	
	return wrapper;
};

WELOCALLY_PlacesMultiWidget.prototype.setPlaces = function(places) {
	var _instance = this;
	if(_instance._map == null){
		_instance._map = _instance.initMapForPlaces(_instance._cfg.places, _instance._map_canvas, _instance._placeMarkers);
	} else {
		_instance.deleteOverlays(_instance._placeMarkers);
	    _instance.addPlaces(_instance._map, places, _instance._placeMarkers);
	    _instance.setMapEvents(_instance._map, _instance._placeMarkers);		
	}		
};

WELOCALLY_PlacesMultiWidget.prototype.initMap = function(map_canvas) {
	
	var _instance = this;
    
	var options = {
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      zoom: _instance._cfg.zoom
    };
    
    if(_instance._cfg.styles){
		options.styles = _instance._cfg.styles;
	}
    
    var map = new google.maps.Map(
    	map_canvas, 
        options);
    
    //init the infobox for the map
    _instance._boxText = document.createElement("div");
    _instance._boxText.className = "wl_places_mutli_infobox"; 
    _instance._boxText.innerHTML = "none selected";
    
    _instance._infoBox = this.makeInfoBox(_instance._boxText);
    
    return map;
	
};

WELOCALLY_PlacesMultiWidget.prototype.initMapForPlaces = function(places, map_canvas, placeMarkers) {
		
    var _instance = this;
    var map = _instance.initMap(map_canvas);
       
    //add all the markers
    _instance.addPlaces(map, places, placeMarkers);
           
    _instance.setMapEvents(map, placeMarkers);
    
//    //init the infobox for the map
//    _instance._boxText = document.createElement("div");
//    _instance._boxText.className = "wl_places_mutli_infobox"; 
//    _instance._boxText.innerHTML = "none selected";
//    
//    _instance._infoBox = this.makeInfoBox(_instance._boxText);
    
    
    return map;
    

};

WELOCALLY_PlacesMultiWidget.prototype.makeInfoBox = function(boxText){
	
	var _instance = this;

	var infobox_cfg = {
				content: boxText
				,disableAutoPan: false
				,maxWidth: 0
				,pixelOffset: new google.maps.Size(0, 5)
				,zIndex: null
				,boxStyle: { 
				  	opacity: 0.85
				 }
				,closeBoxMargin: '2px 2px 2px 2px'
				,closeBoxURL: _instance._cfg.imagePath+'/monotone_close.png'
				,infoBoxClearance: new google.maps.Size(1, 1)
				,isHidden: false
				,pane: "floatPane"
				,enableEventPropagation: false
			};
			
	var ib = new WELOCALLY_InfoBox(infobox_cfg);
	return ib;
};


WELOCALLY_PlacesMultiWidget.prototype.addPlaces = function(map, places, placeMarkers){
	
	var _instance = this;
	var bounds = new google.maps.LatLngBounds();
	
	
	
	jQuery.each(places, function(i,item){
		
		var latlng = new google.maps.LatLng(item.geometry.coordinates[1], item.geometry.coordinates[0]);
		bounds.extend(latlng);
		
		var itemLocation = 
			new google.maps.LatLng(
				item.geometry.coordinates[1], 
				item.geometry.coordinates[0]);	
		
		var markerIcon = _instance._cfg.imagePath+'/marker_place.png';	
		if(_instance._cfg.showLetters){
			markerIcon = _instance._cfg.imagePath+'/marker_place_'+_instance.colName(i)+'.png';
		}
		
		_instance.addMarker(
			placeMarkers,
			map,
			itemLocation,
			item,
			markerIcon);
		
		//add result to list
		var listItem = _instance.makeItemContents(item,i, true);
		
		jQuery(listItem).attr('id','wl_places_mutli_selectable_'+i);
		jQuery(listItem).attr('class','ui-widget-content');
		
		jQuery(listItem).mouseover(function() {
	    	jQuery(this).css('cursor', 'pointer');
	    });
		
		jQuery(_instance._results).append(jQuery(listItem));
		jQuery(_instance._results).show();
	
	});	
	
//	var marker = new google.maps.Marker({
//		position: bounds.getCenter(),
//		map: map,
//		icon: _instance._cfg.imagePath+'/marker_search.png'
//	});
		
	map.fitBounds(bounds);
	
	
};


WELOCALLY_PlacesMultiWidget.prototype.makeItemContents = function (item, i, showMarker) {
	
	var _instance = this;
	
	var wrapper = jQuery('<li></il>');
	
	if(_instance._cfg.showLetters && showMarker){
		jQuery(wrapper)
		.append(jQuery('<img class="selectable_marker" src='+
				_instance._cfg.imagePath+'/marker_place_'+
			_instance.colName(i)+'.png'+' />'));
	} 
	
	
	if (item.properties.titlelink != null
			&& item.properties.titlelink != '') {

		jQuery(wrapper).append('<a href="'
						+ item.properties.titlelink
						+ '"><div class="selectable_title">'
						+ item.properties.name
						+ '</a></div>');
		
	} else {
		jQuery(wrapper)
			.append('<div class="selectable_title">' + item.properties.name + '</div>');
	}

	
	jQuery(wrapper)
		.append(jQuery('<div class="selectable_address">'+
			item.properties.address+'</div>'));
	if(item.distance){
		jQuery(wrapper)
		.append(jQuery('<div class="selectable_distance">'+
				item.distance.toFixed(2)+'km </div>'));
	}

	
	wrapper.item = item;
			
	return wrapper;
};

WELOCALLY_PlacesMultiWidget.prototype.setMapEvents = function(map, markers){
	
	var _instance = this;
	
	google.maps.event.addListener(map, 'zoom_changed', function() {
		zoomChangeBoundsListener = google.maps.event.addListener(map, 'bounds_changed', function(event) {

		});
	});		
	
	var tilesHandle = google.maps.event.addListener(map, 'tilesloaded', function() {
		console.log('tilesloaded2');
		jQuery('.wl_places_multi_map_canvas').find('img').css('max-width','none');
		
		google.maps.event.removeListener(tilesHandle);
		WELOCALLY.util.preload([
				 'http://maps.google.com/mapfiles/openhand.cur'
		]);          				
	}); 
	
},

WELOCALLY_PlacesMultiWidget.prototype.selectedItemHandler = function(event, ui) {
	
	var _instance = null;
	
	if(this.nodeName=='OL'){
		_instance = event.data.instance;
		jQuery(_instance._selectedSection).empty();
		var index = ui.selected.id.replace("wl_places_mutli_selectable_","");
		_instance._selectedPlace = 
			_instance._placeMarkers[index].item;
	} else if(this.nodeName=='MARKER'){
		_instance = this.instance;
		jQuery(_instance._selectedSection).empty();
		jQuery('#wl_placefinder_selectable').find('li').removeClass('ui-selected');
		_instance._selectedPlace = 
			this.item;
		
		//should probably do this
		//map.panTo(mMarker.position);
		
		//lets do this the jquery way	
		var contents = _instance.makeItemContents(_instance._selectedPlace, 0, false);	
		jQuery(contents).attr('class','wl_places_multi_infobox');
		jQuery(contents).css('min-width','100px');
		jQuery(contents).css('min-height','60px');				
		jQuery(_instance._boxText).html(contents);
		 
		jQuery(_instance._boxText).find('li a img').load(function(){
			jQuery(_instance._boxText).find('li a').css('background','none').css('padding','0px'); 
			jQuery(_instance._boxText).css('line-height','15px');
			jQuery(_instance._boxText).find('ul').css('margin','0px');
		});
		
		_instance._infoBox.setOffset(contents);
		_instance._infoBox.open(this.map, this);	
		_instance._infoBox.show();
		
		
	}
	
	if(_instance != null){
		//broadcast to listeners
		if(_instance._cfg.observers != null && _instance._cfg.observers.length>0){
			jQuery.each(_instance._cfg.observers, function(i,item){
				item.show(_instance._selectedPlace);
			});
		}	
	}

};

//will remove all overlays except for the search location
WELOCALLY_PlacesMultiWidget.prototype.resetOverlays=function (location, markersArray) {
	
	var _instance = this; 
	_instance._map.setCenter(location);
	jQuery(_instance._map_canvas).show();
	jQuery(_instance._map).show();
	
	_instance.deleteOverlays(markersArray);
	_instance.refreshMap(location);
	

};

WELOCALLY_PlacesMultiWidget.prototype.refreshMap = function(location) {
	var _instance = this;
	if(_instance._searchLocationMarker != null)	{
		_instance._searchLocationMarker.setMap(null);
		_instance._searchLocationMarker = null;
	}

	google.maps.event.trigger(_instance._map, 'resize');
	
	var listener = google.maps.event.addListener(_instance._map, "tilesloaded", function() {
		console.log('tilesloaded');
					
		_instance._map.setCenter(location);	
				
		_instance._searchLocationMarker = new google.maps.Marker({
			position: location,
			map: _instance._map,
			icon: _instance._cfg.imagePath+'/marker_search.png'
		  });
		
		_instance.setStatus(
				_instance._mapStatus, 
				_instance.makeMapStatus(_instance._map, _instance._placeMarkers) , 
				'wl_message', false);
		
		google.maps.event.removeListener(listener); 
	});
	
	

};


// Shows any overlays currently in the array
WELOCALLY_PlacesMultiWidget.prototype.showOverlays = function (markersArray, map) {
	if (markersArray) {
		for (i in markersArray) {
		  markersArray[i].setMap(map);
		}
	}
};
	
// Deletes all markers in the array by removing references to them
WELOCALLY_PlacesMultiWidget.prototype.deleteOverlays=function (markersArray) {
  if (markersArray) {
	for (i in markersArray) {
	  markersArray[i].setMap(null);
	}
	markersArray.length = 0;
  }
};

WELOCALLY_PlacesMultiWidget.prototype.addLocationMarker = function(markerMap, location, icon) {
	
	var _instance = this;

//	if(_instance._searchLocationMarker != null)	{
//	  	_instance._searchLocationMarker.setMap(null);
//	}
	  
	var marker = new google.maps.Marker({
		position: location,
		map: markerMap,
		icon: icon
	  });
	  
	_instance._searchLocationMarker = marker;
};

WELOCALLY_PlacesMultiWidget.prototype.colName = function (n) {
	
	var _instance = this;
	
	var s = "";
	while(n >= 0) {
		s = String.fromCharCode(n % 26 + 65) + s;
		n = Math.floor(n / 26) - 1;
	}
	return s;
};



WELOCALLY_PlacesMultiWidget.prototype.addMarker = function(markersArray, markerMap, location, item, icon) {
	var _instance = this;
	var marker = new google.maps.Marker({
	    nodeName: 'MARKER',
	    instance: this,
		position: location,
		title: item.properties.name,
		map: markerMap,
		icon: icon,
		item: item
	});
	google.maps.event.addListener(marker, 'click', this.selectedItemHandler);
	markersArray.push(marker);

};
	
// Removes the overlays from the map, but keeps them in the array
WELOCALLY_PlacesMultiWidget.prototype.clearOverlays = function (markersArray) {
  if (markersArray) {
	for (i in markersArray) {
	  markersArray[i].setMap(null);
	}
  }
};


WELOCALLY_PlacesMultiWidget.prototype.getMapRadius = function(map) {
	
	var _instance = this;
	
	if(_instance._map){
		var bounds = _instance._map.getBounds();

		var center = bounds.getCenter();
		var ne = bounds.getNorthEast();
		
		// r = radius of the earth in statute km
		var r = 6378.8;  
		
		// Convert lat or lng from decimal degrees into radians (divide by 57.2958)
		var lat1 = center.lat() / 57.2958; 
		var lon1 = center.lng() / 57.2958;
		var lat2 = ne.lat() / 57.2958;
		var lon2 = ne.lng() / 57.2958;
		
		// distance = circle radius from center to Northeast corner of bounds
		var dis = r * Math.acos(Math.sin(lat1) * Math.sin(lat2) + 
		  Math.cos(lat1) * Math.cos(lat2) * Math.cos(lon2 - lon1));	
		
		return dis/2.0;
	} else {
		return 0.0;
	}

	
};

WELOCALLY_PlacesMultiWidget.prototype.setStatus = function(statusArea, message, type, showloading){
	var _instance  = this;
	
	jQuery(statusArea).html('');
	jQuery(statusArea).removeClass();
	jQuery(statusArea).addClass(type);
	
	if(showloading){
		jQuery(statusArea).append('<div><img class="wl_ajax_loading" src="'+
				_instance._cfg.imagePath+'/ajax-loader.gif"/></div>');
	}
	
	jQuery(statusArea).append('<em>'+message+'</em>');
	
	if(message != ''){
		jQuery(statusArea).show();
	} else {
		jQuery(statusArea).hide();
	}	
	
};

WELOCALLY_PlacesMultiWidget.prototype.makeMapStatus = function (map, markers) {	
	
	var _instance = this;
	
	var status = '';
	
	var radius = _instance.getMapRadius(_instance._map);
	if(markers != null && markers.length>0)
		status = status + ' places found: '+markers.length;
	status = status + ' search radius: '+radius.toFixed(2)+'km';
	status = status + ' zoom: '+map.getZoom();
	return status;
};