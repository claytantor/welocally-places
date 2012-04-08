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
		
		var _instance = this;
		
		if(!cfg.endpoint){
			cfg.endpoint='http://stage.welocally.com';
		}
		
		if(!cfg.imagePath){
			cfg.imagePath = 'http://placehound.com/images/marker_all_base.png';
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
	    jQuery(wrapper).append('<div class="wl_field_description">Enter a location to search such as "New York NY". You can even provide a full address for more refined searches.</div>');
	    jQuery(this._locationField).attr('class','wl_widget_field wl_placefinder_search_field');
	    jQuery(this._locationField).bind('change' , {instance: this}, this.locationFieldInputHandler);   
	    
	    
	    jQuery(wrapper).append(this._locationField);
		
		//search field
	    this._searchField =
			jQuery('<input type="text" name="search" id="wl_finder_search_field"/>');
		jQuery(wrapper).append('<div class="wl_field_description">Enter what you are searching for, this can be a type of place like "Restaurant", what they sell like "Pizza", or the name of the place like "Seward Park".</div>');       
		jQuery(this._searchField).attr('class','wl_widget_field wl_placefinder_search_field');
		jQuery(this._searchField).bind('change' , {instance: this}, this.searchHandler);  
			
		jQuery(wrapper).append(this._searchField);
		
		//bind focus
		jQuery(this._locationField).keypress(function(e){
	        if ( e.which == 13 ){
	        	jQuery(this._locationField).trigger('change' , {instance: this}, this.locationFieldInputHandler);
	        	jQuery('#wl_finder_search_field').focus();
	        	return false;
	        }
	    });
		
		jQuery(this._searchField).keypress(function(e){
	        if ( e.which == 13 ){
	        	jQuery(this._searchField).trigger('change' , {instance: _instance}, this.searchHandler);
	        	jQuery('#wl_finder_search_button').focus();
	        	return false;
	        }
	    });
		
		
				
		var buttonDiv = jQuery('<div></div>').attr('class','wl_finder_search_button_area'); 	
		var fetchButton = jQuery('<button id="wl_finder_search_button">Search</div>');
		
		jQuery(fetchButton).attr('class','wl_finder_search');
		jQuery(fetchButton).bind('click' , {instance: this}, this.searchHandler);
		jQuery(buttonDiv).append(fetchButton); 
		jQuery(wrapper).append(buttonDiv);  
		
		//now the mutli place component
		this._multiPlacesWidget = 
			new WELOCALLY_PlacesMultiWidget().initCfg(cfg);
		
		
		//the component wrapper
		jQuery(wrapper).append(this._multiPlacesWidget.makeWrapper());
		
		jQuery(script).parent().before(wrapper);
		
		if(this._cfg.defaultLocation){
			jQuery(this._locationField).val(this._cfg.defaultLocation);
			jQuery(this._locationField).trigger('change' , {instance: _instance}, this.locationFieldInputHandler); 
		}
		
	
		return this;
		
	};
		
}

WELOCALLY_PlaceFinderWidget.prototype.locationFieldInputHandler = function(event) {
	
	var _instance = event.data.instance;
	
	var addressValue = jQuery(this).val();
	
	if(addressValue){
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
				
				_instance._multiPlacesWidget.refreshMap(_instance._searchLocation);
				
							
				
			} else {
				_instance.setStatus(_instance._ajaxStatus, 'Could not geocode:'+status,'wl_warning',false);
			} 
		});
	} else {
		_instance.setStatus(_instance._ajaxStatus, 'Please choose a location to start your search.','wl_warning',false);
	}
	
	
	
	
	return false;
	
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
		
		_instance.setStatus(_instance._ajaxStatus, 'Finding places','wl_update',true);
		jQuery(_instance._multiPlacesWidget._results).hide();
		
		_instance._multiPlacesWidget.resetOverlays(
					_instance._searchLocation,
					_instance._multiPlacesWidget._placeMarkers);
			
		jQuery.ajax({
				  url: surl,
				  dataType: "json",
				  success: function(data) {
					//set to result bounds if enough results
					if(data != null && data.length>0){						
						_instance.setStatus(_instance._ajaxStatus, '','wl_message',false);
						_instance._multiPlacesWidget.setPlaces(data);						
					} else {
						
						_instance.setStatus(_instance._ajaxStatus, 'No results were found matching your search.','wl_warning',false);						
						_instance._multiPlacesWidget.refreshMap(_instance._searchLocation);
					}
					

					var listener = google.maps.event.addListener(_instance._multiPlacesWidget._map, "idle", function() { 						
						if (_instance._multiPlacesWidget._map.getZoom() > 17) _instance._multiPlacesWidget._map.setZoom(17); 
						google.maps.event.removeListener(listener); 
					});
															
				}
		});
	
	
	} else {
		_instance.setStatus(_instance._ajaxStatus, 'Please choose a location for search.','wl_warning',false);
	}
	
	return false;

};


WELOCALLY_PlaceFinderWidget.prototype.getSelectedSection = function() { 
	return this._multiPlacesWidget._selectedSection;
};

WELOCALLY_PlaceFinderWidget.prototype.setStatus = function(statusArea, message, type, showloading){
	var _instance  = this;
	
	jQuery(statusArea).html('');
	jQuery(statusArea).removeClass();
	jQuery(statusArea).addClass(type);
	
	//need a solution for this
	if(showloading){
//		jQuery(statusArea).append('<div><img class="wl_ajax_loading" src="'+
//				_instance._cfg.imagePath+'/ajax-loader.gif"/></div>');
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
