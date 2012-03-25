/*
	copyright 2012 welocally. NO WARRANTIES PROVIDED
*/

function WELOCALLY_PlaceFinderWidget (cfg) {
	this._cfg;
	this._geocoder;	
	this._searchLocation;
	this._ajaxStatus;
	this._locationField;
	this._searchField;
	this._multiPlacesWidget;
	this._selectedPlace = {
			properties: {},
			type: "Place",
			classifiers: [
				{
					type: '',
					category: '',
					subcategory: ''
				}
			],
			geometry: {
					type: "Point",
					coordinates: []
			}
		};
	
	this.init = function() {
		
		if(!cfg.endpoint){
			cfg.endpoint='http://stage.welocally.com';
		}
		
		if(!cfg.imagePath){
			cfg.imagePath='http://placehound.com/images';
		}
		
		if(!cfg.loc){
			cfg.loc='38.548165_-96.064453';
		}
		
		if(!cfg.zoom){
			cfg.zoom=4;
		}
		
		if(!cfg.radius){
			cfg.radius=20;
		}
		
		this._cfg = cfg;
		
		this._geocoder = new google.maps.Geocoder();
		
		this._searchLocation = 
			new google.maps.LatLng(); 					
			
		 // Get current script object
	    var script = jQuery('SCRIPT');
	    script = script[script.length - 1]; 
	    
	    //wrapper
	    var wrapper = jQuery('<div class="wl_placefinder_wrapper"></div>');
							
		//status
		this._ajaxStatus = 
			jQuery('<div></div>');
	    jQuery(this._ajaxStatus).css('display','none');
	    jQuery(wrapper).append(this._ajaxStatus);
	    
	    //location field
	    this._locationField =
			jQuery('<input type="text" name="location"/>');
	    jQuery(wrapper).append('<div>Enter a location to search such as "New York NY". You can even provide a full address for more refined searches.</div>');
	    jQuery(this._locationField).attr('class','wl_widget_field wl_placefinder_search_field');
	    jQuery(this._locationField).bind('change' , {instance: this}, this.locationFieldInputHandler);       	
	    jQuery(wrapper).append(this._locationField);
		
		//search field
	    this._searchField =
			jQuery('<input type="text" name="search"/>');
		jQuery(wrapper).append('<div>Enter what you are searching for, this can be a type of place like "Restaurant", what they sell like "Pizza", or the name of the place like "Seward Park".</div>');       
		jQuery(this._searchField).attr('class','wl_widget_field wl_placefinder_search_field');
		jQuery(this._searchField).bind('change' , {instance: this}, this.searchHandler);  
		jQuery(wrapper).append(this._searchField);
		
		var buttonDiv = jQuery('<div></div>').attr('class','wl_finder_search_button_area'); 	
		var fetchButton = jQuery('<button id="wl_finder_search_button">Search</div>');
		
		jQuery(fetchButton).attr('class','wl_finder_search');
		jQuery(fetchButton).bind('click' , this.searchHandler);
		jQuery(buttonDiv).append(fetchButton); 
		jQuery(wrapper).append(buttonDiv);  
		
		//now the mutli place component
		this._multiPlacesWidget = 
			new WELOCALLY_PlacesMultiWidget().initCfg(cfg);
		
		//the component wrapper
		jQuery(wrapper).append(this._multiPlacesWidget.makeWrapper());
		
		jQuery(script).parent().before(wrapper);
		
		return this;
		
	};
		
}

WELOCALLY_PlaceFinderWidget.prototype.locationFieldInputHandler = function(event) {
	
	var _instance = event.data.instance;
	
	var addressValue = jQuery(this).val();
	console.log('location: '+addressValue);
	
	_instance.setStatus(_instance._ajaxStatus, 'Geocoding','wl_update',true);
	
	jQuery(_instance._selectedSection).hide();
	
	_instance._geocoder.geocode( { 'address': addressValue}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK &&  _instance.validGeocodeForSearch(results[0])) {
		
		
			jQuery(_instance._locationField).val(results[0].formatted_address);
			_instance._formattedAddress = results[0].formatted_address;
			
				
			//set the model
			_instance._selectedPlace.properties.address = 
				_instance.getShortNameForType("street_number", results[0].address_components)+' '+
				_instance.getShortNameForType("route", results[0].address_components);
			
			_instance._selectedPlace.properties.city = 
				_instance.getShortNameForType("locality", results[0].address_components);
			
			_instance._selectedPlace.properties.province = 
				_instance.getShortNameForType("administrative_area_level_1", results[0].address_components);
	
			_instance._selectedPlace.properties.postcode = 
				_instance.getShortNameForType("postal_code", results[0].address_components);
			
			_instance._selectedPlace.properties.country = 
				_instance.getShortNameForType("country", results[0].address_components);
			
			_instance._selectedPlace.geometry.coordinates = [];
			_instance._selectedPlace.geometry.coordinates.push(results[0].geometry.location.lng());
			_instance._selectedPlace.geometry.coordinates.push(results[0].geometry.location.lat());
			
			_instance._searchLocation = 
				new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
						
			//setup the map
			var sl = _instance._searchLocation;
			_instance._multiPlacesWidget._map.setCenter(sl);
			
			var pm = _instance._multiPlacesWidget._placeMarkers;
			
			//set the zoom
			if(results[0].address_components.length<=5){
				_instance._multiPlacesWidget._map.setZoom(14);						
				
			} else {
				_instance._multiPlacesWidget._map.setZoom(16);
			}		

			//reset overlays
			_instance._multiPlacesWidget.resetOverlays(
				sl,
				pm); 

			_instance.setStatus(_instance._ajaxStatus, '','wl_message',true);
			
			if(_instance._searchField.val()){
				jQuery(_instance._searchField).trigger('change');
			}
							
			jQuery( _instance._multiPlacesWidget._map_canvas).show();
			_instance._multiPlacesWidget.resetOverlays();
			
			
			
			
			//broadcast to listeners
			/*if(_instance._cfg.observers != null && _instance._cfg.observers.length>0){
				jQuery.each(_instance._cfg.observers, function(i,item){
					if(item instanceof WELOCALLY_DealFinderWidget){
						item.setLocation(results[0].geometry.location.lat(), results[0].geometry.location.lng());
					}						
				});
			}*/
						
			
		} else {
			_instance.setStatus(_instance._ajaxStatus, 'Could not geocode:'+status,'wl_warning',false);
		} 
	});
	
};



WELOCALLY_PlaceFinderWidget.prototype.searchHandler = function(event) {
	
	var _instance = event.data.instance;

	jQuery(_instance._selectedSection).hide();
	
	if(_instance._selectedPlace.geometry.coordinates[1] && _instance._selectedPlace.geometry.coordinates[0]){
		
		if(!_instance._locationField) {
			_instance._formattedAddress = results[0].formatted_address;
		}
		
		var searchValue = WELOCALLY.util.replaceAll(jQuery(_instance._searchField).val(),' ','+');

		var query = {
			q: searchValue,
			loc: _instance._selectedPlace.geometry.coordinates[1]+'_'+_instance._selectedPlace.geometry.coordinates[0],
			radiusKm: _instance._multiPlacesWidget.getMapRadius(_instance._multiPlacesWidget._map)
		};
		
		var surl = _instance._cfg.endpoint +
			'/geodb/place/1_0/search.json?'+WELOCALLY.util.serialize(query)+"&callback=?";
		console.log(surl);
		
		_instance.setStatus(_instance._ajaxStatus, 'Finding places','wl_update',true);
		jQuery(_instance._results).hide();
		
		_instance._multiPlacesWidget.resetOverlays(
					_instance._searchLocation,
					_instance._placeMarkers);
			
		jQuery.ajax({
				  url: surl,
				  dataType: "json",
				  success: function(data) {
					//setup the bounds
					//var bounds = new google.maps.LatLngBounds();
					//bounds.extend(_instance._searchLocation);
					
					//set to result bounds if enough results
					if(data != null && data.length>0){						
						_instance.setStatus(_instance._ajaxStatus, '','wl_message',false);
						_instance._multiPlacesWidget.setPlaces(data);
						
					} else {
						bounds = _instance._multiPlacesWidget._map.getBounds();
						_instance.setStatus(_instance._ajaxStatus, 'No results were found matching your search.','wl_warning',false);
					}
					
					
					//_instance._multiPlacesWidget._map.fitBounds(bounds);
					var listener = google.maps.event.addListener(_instance._multiPlacesWidget._map, "idle", function() { 						
						if (_instance._multiPlacesWidget._map.getZoom() > 17) _instance._multiPlacesWidget._map.setZoom(17); 
						google.maps.event.removeListener(listener); 
					});
															
				}
		});
	
	
	} else {
		_instance.setStatus(_instance._ajaxStatus, 'Please choose a location for search.','wl_warning',false);
	}

};


WELOCALLY_PlaceFinderWidget.prototype.getSelectedSection = function() { 
	return this._multiPlacesWidget._selectedSection;
};

WELOCALLY_PlaceFinderWidget.prototype.setStatus = function(statusArea, message, type, showloading){
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

WELOCALLY_PlaceFinderWidget.prototype.validGeocodeForSearch = function (geocode) {

	if(geocode.geometry.location.lat() && geocode.geometry.location.lng())
		return true;
	
};



WELOCALLY_PlaceFinderWidget.prototype.hasType = function(type_name, address_components){
	for (componentIndex in address_components) {
		var component = address_components[componentIndex];
		if(component.types[0] == type_name)
			return true;
	}
	return false;
};
	
WELOCALLY_PlaceFinderWidget.prototype.getShortNameForType = function(type_name, address_components){
	for (componentIndex in address_components) {
		var component = address_components[componentIndex];
		if(component.types[0] == type_name)
			return address_components[componentIndex].short_name;
	}
	return null;	
};


/*function WELOCALLY_PlaceFinderWidget (cfg) {
	
	this._map;
	this._cfg;
	this._options;
	this._selectedPlace;
	this._placeMarkers;
	this._selectedSection;
	this._mapStatus;
	this._locationField;
	this._searchField;
	this._results;
	this._geocoder;
	this._searchLocation;
	
	
	this.init = function() {	
			
		if(!cfg.endpoint){
			cfg.endpoint='http://stage.welocally.com';
		}
		
		if(!cfg.imagePath){
			cfg.imagePath='http://placehound.com/images';
		}
		
		if(!cfg.loc){
			cfg.loc='38.548165_-96.064453';
		}
		
		if(!cfg.zoom){
			cfg.zoom=4;
		}
		
		if(!cfg.radius){
			cfg.radius=20;
		}
		
		this._cfg = cfg;
		
		this._selectedSection = 
			jQuery('<div class="wl_finder_selected"></div>');
		
		this._ajaxStatus = 
			jQuery('<div></div>');
			
		this._mapStatus = 
			jQuery('<div></div>');	
		   			
		this._locationField =
			jQuery('<input type="text" name="location"/>');
		this._searchField =
			jQuery('<input type="text" name="search"/>');
			
		this._results = 
			jQuery('<ol id="wl_placefinder_selectable"></ol>');	
			
		this._geocoder = new google.maps.Geocoder();
	
		this._placeMarkers = [];
		this._selectedPlace = {
			properties: {},
			type: "Place",
			classifiers: [
				{
					type: '',
					category: '',
					subcategory: ''
				}
			],
			geometry: {
					type: "Point",
					coordinates: []
			}
		};
				
		
		this._searchLocation = 
			new google.maps.LatLng(); 
					
			
		 // Get current script object
	    var script = jQuery('SCRIPT');
	    script = script[script.length - 1]; 
	    
	    //wrapper
	    var wrapper = jQuery('<div></div>');
	    
	    
	    //status
	    jQuery(this._ajaxStatus).css('display','none');
	    jQuery(wrapper).append(this._ajaxStatus);
	    
	    //location field
	    jQuery(wrapper).append('<div>Enter a location to search such as "New York NY". You can even provide a full address for more refined searches.</div>');
	    jQuery(this._locationField).attr('class','wl_widget_field wl_placefinder_search_field');
	    jQuery(this._locationField).bind('change' , {instance: this}, this.locationFieldInputHandler);       
		jQuery(wrapper).append(this._locationField);
		
		//search field
		jQuery(wrapper).append('<div>Enter what you are searching for, this can be a type of place like "Restaurant", what they sell like "Pizza", or the name of the place like "Seward Park".</div>');       
		jQuery(this._searchField).attr('class','wl_widget_field wl_placefinder_search_field');
		jQuery(this._searchField).bind('change' , {instance: this}, this.searchHandler);  
		jQuery(wrapper).append(this._searchField);
		
		var buttonDiv = jQuery('<div></div>').attr('class','wl_finder_search_button_area'); 	
		var fetchButton = jQuery('<button id="wl_finder_search_button">Search</div>');
		
		jQuery(fetchButton).attr('class','wl_finder_search');
		jQuery(fetchButton).bind('click' , this.searchHandler);
		jQuery(buttonDiv).append(fetchButton); 
		jQuery(wrapper).append(buttonDiv);  
		
		//selected
	    jQuery(this._selectedSection).css('display','none');
	    jQuery(this._selectedSection).attr('class','wl_finder_selected');
	    jQuery(wrapper).append(this._selectedSection);
		    	
		//google maps does not like jquery instances
		var map_canvas = document.createElement('DIV');
		jQuery(map_canvas).css('min-width','350px');
	    jQuery(map_canvas).css('min-height','350px');
	    jQuery(map_canvas).attr('class','wl_finder_map_canvas');
		jQuery(map_canvas).attr("id","wl_finder_map_canvas");
	      	
		var loc = cfg.loc.split('_');
		
		this._searchLocation = new google.maps.LatLng(loc[0], loc[1]);
		
	    this._map = this.makeMapFromConfig(cfg, map_canvas);
	       	        
	    jQuery(wrapper).append(map_canvas);  
	    
	    //map status
	    jQuery(this._mapStatus).css('display','none');
	    jQuery(wrapper).append(this._mapStatus);
	    
	    //results
	    jQuery(this._results).selectable();
	    	    
	    jQuery(this._results).css('display','none');
	    jQuery( this._results ).bind( "selectableselected", {instance: this}, this.selectedItemHandler);        
	    jQuery(wrapper).append(this._results);
	          
	    jQuery(script).parent().before(wrapper);
		
	};
	
}

WELOCALLY_PlaceFinderWidget.prototype.refreshMap = function() {
	_instance = this;
	
	google.maps.event.trigger(_instance._map, 'resize');
	
	var listener = google.maps.event.addListener(_instance._map, "idle", function() { 						
		_instance._map.setCenter(_instance._searchLocation);								
		_instance.addLocationMarker(
			_instance._map,
			_instance._searchLocation,
			_instance._cfg.imagePath+'/marker_search.png');
		jQuery(_instance._map).show();
		google.maps.event.removeListener(listener); 
	});
};


WELOCALLY_PlaceFinderWidget.prototype.makeMapFromConfig = function(cfg, map_canvas) { 
	
	_instance = this;
    
	_instance._options = {
      center: _instance._searchLocation,
      zoom: cfg.zoom,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    if(cfg.styles){
		_instance._options.styles = cfg.styles;
	}
    
    var map = new google.maps.Map(
    	map_canvas, 
        _instance._options);
        
    _instance.setupMapListeners(map, _instance._placeMarkers);
    
    return map;

};

WELOCALLY_PlaceFinderWidget.prototype.setStatus = function(statusArea, message, type, showloading){
	_instance = this;
	
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
	
WELOCALLY_PlaceFinderWidget.prototype.getMapRadius = function(map) {
	
	_instance = this;

	var bounds = map.getBounds();

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
};


WELOCALLY_PlaceFinderWidget.prototype.selectedItemHandler = function(event, ui) {
	
	_instance = null;
	
	
	if(this.nodeName=='OL'){
		_instance = event.data.instance;
		jQuery(_instance._selectedSection).empty();
		var index = ui.selected.id.replace("wl_placefinder_selectable_","");
		_instance._selectedPlace = 
			_instance._placeMarkers[index].item;
	} else if(this.nodeName=='MARKER'){
		_instance = this.instance;
		jQuery(_instance._selectedSection).empty();
		jQuery('#wl_placefinder_selectable').find('li').removeClass('ui-selected');
		_instance._selectedPlace = 
			this.item;
	}
	
	if(_instance != null && _instance._cfg.showSelection){
		jQuery(_instance._selectedSection)
		.append('<div class="wl_selected_name">'+_instance._selectedPlace.properties.name+'</div>')
		.append('<div class="wl_selected_adress">'+_instance._selectedPlace.properties.address+' '+
			_instance._selectedPlace.properties.city+
			' '+_instance._selectedPlace.properties.province+
			' '+_instance._selectedPlace.properties.postcode+'</div>');
				
		
		if(_instance._selectedPlace.properties.phone != null) {
			jQuery(_instance._selectedSection)
				.append('<div class="wl_selected_phone">'+_instance._selectedPlace.properties.phone+'</div>');
		}

		if(_instance._selectedPlace.properties.website != null && 
			_instance._selectedPlace.properties.website != '' ) {
			var website = _instance._selectedPlace.properties.website;
			if(_instance._selectedPlace.properties.website.indexOf('http://') == -1) {
				website = 'http://'+_instance._selectedPlace.properties.website;				
			}
			
			jQuery(_instance._selectedSection)
				.append('<div class="wl_selected_web"><a target="_new" href="'+website+'">'+website+'</a></div>');

		} 

		if(_instance._selectedPlace.properties.city != null && 
			_instance._selectedPlace.properties.province != null){
				var qS = _instance._selectedPlace.properties.city+" "+
					_instance._selectedPlace.properties.province;
				if(_instance._selectedPlace.properties.address != null)
					qs=_instance._selectedPlace.properties.address+" "+qS;
				if(_instance._selectedPlace.properties.postcode != null)
					qs=qs+" "+_instance._selectedPlace.properties.postcode;
				var qVal = qs.replace(" ","+");
				
				jQuery(_instance._selectedSection)
				.append('<div class="wl_selected_driving"><a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+
					qVal+'" target="_new">Driving Directions</a></div>');
			
		}
		 
		//the tag
		var tag = '[welocally id="'+_instance._selectedPlace._id+'" /]';
		var inputArea = jQuery('<input class="wl_selected_tag wl_widget_field" type="text"/>');
		jQuery(inputArea).val(tag);
		var wlSelectedTagArea = jQuery('<div></div>');
		jQuery(wlSelectedTagArea).append(inputArea);
		 
		jQuery(_instance._selectedSection)
		 		.append(wlSelectedTagArea)
				.show();
	
	}
	
	
	//broadcast to listeners
	if(_instance._cfg.observers != null && _instance._cfg.observers.length>0){
		jQuery.each(_instance._cfg.observers, function(i,item){
			if(item instanceof WELOCALLY_PlaceSelectionListener){
				item.show(_instance._selectedPlace);
			}						
		});
	}

};


WELOCALLY_PlaceFinderWidget.prototype.locationFieldInputHandler = function(event) {
	
	_instance = event.data.instance;
	
	var addressValue = jQuery(this).val();
	console.log('location: '+addressValue);
	
	_instance.setStatus(_instance._ajaxStatus, 'Geocoding','wl_update',true);
	
	jQuery(_instance._selectedSection).hide();
	
	_instance._geocoder.geocode( { 'address': addressValue}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK &&  _instance.validGeocodeForSearch(results[0])) {
		
		
			jQuery(_instance._locationField).val(results[0].formatted_address);
			_instance._formattedAddress = results[0].formatted_address;
			
		
			if(results[0].address_components.length<=5){
				_instance._map.setZoom(14);
			} else {
				_instance._map.setZoom(16);
			}
				
			//set the model
			_instance._selectedPlace.properties.address = 
				_instance.getShortNameForType("street_number", results[0].address_components)+' '+
				_instance.getShortNameForType("route", results[0].address_components);
			
			_instance._selectedPlace.properties.city = 
				_instance.getShortNameForType("locality", results[0].address_components);
			
			_instance._selectedPlace.properties.province = 
				_instance.getShortNameForType("administrative_area_level_1", results[0].address_components);
	
			_instance._selectedPlace.properties.postcode = 
				_instance.getShortNameForType("postal_code", results[0].address_components);
			
			_instance._selectedPlace.properties.country = 
				_instance.getShortNameForType("country", results[0].address_components);
			
			_instance._selectedPlace.geometry.coordinates = [];
			_instance._selectedPlace.geometry.coordinates.push(results[0].geometry.location.lng());
			_instance._selectedPlace.geometry.coordinates.push(results[0].geometry.location.lat());
			
			_instance._searchLocation = 
				new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
			
						
			//_instance.deleteOverlays(_instance._placeMarkers);
			
			_instance.resetOverlays(
				_instance._searchLocation,
				_instance._placeMarkers) 
								
			//jQuery(_instance._searchField).show();
			_instance.setStatus(_instance._ajaxStatus, '','wl_message',true);
			
			_instance.setStatus(
					_instance._mapStatus, 
					_instance.makeMapStatus(_instance._map, _instance._placeMarkers) , 
					'wl_message', false);
			
			if(_instance._searchField.val()){
				jQuery(_instance._searchField).trigger('change');
			}
			
			//broadcast to listeners
			if(_instance._cfg.observers != null && _instance._cfg.observers.length>0){
				jQuery.each(_instance._cfg.observers, function(i,item){
					if(item instanceof WELOCALLY_DealFinderWidget){
						item.setLocation(results[0].geometry.location.lat(), results[0].geometry.location.lng());
					}						
				});
			}
						
			
		} else {
			_instance.setStatus(_instance._ajaxStatus, 'Could not geocode:'+status,'wl_warning',false);
		} 
	});
	
};

WELOCALLY_PlaceFinderWidget.prototype.searchHandler = function(event) {
	
	_instance = event.data.instance;

	jQuery(_instance._selectedSection).hide();
	
	if(_instance._selectedPlace.geometry.coordinates[1] && _instance._selectedPlace.geometry.coordinates[0]){
		
		if(!_instance._locationField) {
			_instance._formattedAddress = results[0].formatted_address;
		}
		
		var searchValue = WELOCALLY.util.replaceAll(jQuery(_instance._searchField).val(),' ','+');

		var query = {
			q: searchValue,
			loc: _instance._selectedPlace.geometry.coordinates[1]+'_'+_instance._selectedPlace.geometry.coordinates[0],
			radiusKm: _instance.getMapRadius(_instance._map)
		};
		
		var surl = _instance._cfg.endpoint +
			'/geodb/place/1_0/search.json?'+WELOCALLY.util.serialize(query)+"&callback=?";
		console.log(surl);
		
		_instance.setStatus(_instance._ajaxStatus, 'Finding places','wl_update',true);
		jQuery(_instance._results).hide();
		
		_instance.resetOverlays(
					_instance._searchLocation,
					_instance._placeMarkers);
			
		jQuery.ajax({
				  url: surl,
				  dataType: "json",
				  success: function(data) {
					//setup the bounds
					var bounds = new google.maps.LatLngBounds();
					bounds.extend(_instance._searchLocation);
					
					//set to result bounds if enough results
					if(data != null && data.length>0){						
						_instance.setStatus(_instance._ajaxStatus, '','wl_message',false);
					} else {
						bounds = _instance._map.getBounds();
						_instance.setStatus(_instance._ajaxStatus, 'No results were found matching your search.','wl_warning',false);
					}
					
					jQuery(_instance._results).empty();
					
					jQuery.each(data, function(i,item){
								
						var latlng = new google.maps.LatLng(item.geometry.coordinates[1], item.geometry.coordinates[0]);
						bounds.extend(latlng);
						
						var itemLocation = 
							new google.maps.LatLng(
								item.geometry.coordinates[1], 
								item.geometry.coordinates[0]);	
						
						_instance.addMarker(
							_instance._placeMarkers,
							_instance._map,
							itemLocation,
							item,
							_instance._cfg.imagePath+'/marker_place_'+_instance.colName(i)+'.png');
						
						//add result to list
						var listItem = _instance.makeItemContents(item,i);
						
						jQuery(listItem).attr('id','wl_placefinder_selectable_'+i);
						jQuery(listItem).attr('class','ui-widget-content');
						
						jQuery(listItem).mouseover(function() {
					    	jQuery(this).css('cursor', 'pointer');
					    });
						
						jQuery(_instance._results).append(jQuery(listItem));
						jQuery(_instance._results).show();
			
					});
					
					
					_instance._map.fitBounds(bounds);
					var listener = google.maps.event.addListener(_instance._map, "idle", function() { 						
						if (_instance._map.getZoom() > 17) _instance._map.setZoom(17); 
						google.maps.event.removeListener(listener); 
					});
															
				}
		});
	
	
	} else {
		_instance.setStatus(_instance._ajaxStatus, 'Please choose a location for search.','wl_warning',false);
	}

	

};

WELOCALLY_PlaceFinderWidget.prototype.makeItemContents = function (item, i) {
	
	_instance = this;
	
	var wrapper = jQuery('<li></il>');
	jQuery(wrapper)
		.append(jQuery('<img class="selectable_marker" src='+
				_instance._cfg.imagePath+'/marker_place_'+
			_instance.colName(i)+'.png'+' />'))
		.append(jQuery('<div class="selectable_title">'+
			item.properties.name+'</div>'))
		.append(jQuery('<div class="selectable_address">'+
			item.properties.address+'</div>'))
		.append(jQuery('<div class="selectable_distance">'+item.distance.toFixed(2)+'km </div>'));
	
	wrapper.item = item;
			
	return wrapper;
};


WELOCALLY_PlaceFinderWidget.prototype.colName = function (n) {
	
	_instance = this; 
	
	var s = "";
	while(n >= 0) {
		s = String.fromCharCode(n % 26 + 65) + s;
		n = Math.floor(n / 26) - 1;
	}
	return s;
};

WELOCALLY_PlaceFinderWidget.prototype.setupMapListeners = function (map, markers) {		
	
	_instance = this;
	
	google.maps.event.addListener(map, 'zoom_changed', function() {
		zoomChangeBoundsListener = google.maps.event.addListener(map, 'bounds_changed', function(event) {
			_instance.setStatus(
					_instance._mapStatus, 
					_instance.makeMapStatus(map, markers),
					'wl_message', false);
		});
	});		
};

WELOCALLY_PlaceFinderWidget.prototype.makeMapStatus = function (map, markers) {	
	
	_instance = this;
	
	var status = '';
	
	var radius = _instance.getMapRadius(_instance._map);
	if(markers != null && markers.length>0)
		status = status + ' places found: '+markers.length;
	status = status + ' search radius: '+radius.toFixed(2)+'km';
	status = status + ' zoom: '+map.getZoom();
	return status;
};
	
WELOCALLY_PlaceFinderWidget.prototype.validGeocodeForSearch = function (geocode) {

	if(geocode.geometry.location.lat() && geocode.geometry.location.lng())
		return true;
	
};
	
WELOCALLY_PlaceFinderWidget.prototype.hasType = function(type_name, address_components){
	for (componentIndex in address_components) {
		var component = address_components[componentIndex];
		if(component.types[0] == type_name)
			return true;
	}
	return false;
};
	
WELOCALLY_PlaceFinderWidget.prototype.getShortNameForType = function(type_name, address_components){
	for (componentIndex in address_components) {
		var component = address_components[componentIndex];
		if(component.types[0] == type_name)
			return address_components[componentIndex].short_name;
	}
	return null;	
};

//will remove all overlays except for the search location
WELOCALLY_PlaceFinderWidget.prototype.resetOverlays=function (location,markersArray) {
	
	_instance = this;
		
	_instance.deleteOverlays(markersArray);
	
	_instance._map.setCenter(location);								
			_instance.addLocationMarker(
				_instance._map,
				location,
				_instance._cfg.imagePath+'/marker_search.png');		
};

// Shows any overlays currently in the array
WELOCALLY_PlaceFinderWidget.prototype.showOverlays = function (markersArray, map) {
	if (markersArray) {
		for (i in markersArray) {
		  markersArray[i].setMap(map);
		}
	}
};
	
// Deletes all markers in the array by removing references to them
WELOCALLY_PlaceFinderWidget.prototype.deleteOverlays=function (markersArray) {
  if (markersArray) {
	for (i in markersArray) {
	  markersArray[i].setMap(null);
	}
	markersArray.length = 0;
  }
};

WELOCALLY_PlaceFinderWidget.prototype.addLocationMarker = function(markerMap, location, icon) {
	
	_instance = this;

	if(_instance._searchLocationMarker != null)	
	  	_instance._searchLocationMarker.setMap(null);
	  
	var marker = new google.maps.Marker({
		position: location,
		map: markerMap,
		icon: icon
	  });
	  
	_instance._searchLocationMarker = marker;
};

WELOCALLY_PlaceFinderWidget.prototype.addMarker = function(markersArray, markerMap, location, item, icon) {
	_instance = this;
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
WELOCALLY_PlaceFinderWidget.prototype.clearOverlays = function (markersArray) {
  if (markersArray) {
	for (i in markersArray) {
	  markersArray[i].setMap(null);
	}
  }
};*/

