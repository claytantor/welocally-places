/*
	copyright 2012 welocally. NO WARRANTIES PROVIDED
*/

if (!window.WELOCALLY) {
    window.WELOCALLY = { }
}

if (!WELOCALLY.PlaceFinderWidget) {
    WELOCALLY.PlaceFinderWidget = function(cfg) {
    
    	if (!WELOCALLY.PlaceFinderWidget._initialized) {
    			
    			WELOCALLY.PlaceFinderWidget._cfg = cfg;
    			
    			if(!cfg.endpoint){
    				WELOCALLY.PlaceFinderWidget._cfg.endpoint='http://stage.welocally.com';
    			}
    			
    			if(!cfg.imagePath){
    				WELOCALLY.PlaceFinderWidget._cfg.imagePath='http://placehound.com/images';
    			}
    			
    			WELOCALLY.PlaceFinderWidget._observers = cfg.observers;
    			
    			WELOCALLY.PlaceFinderWidget._selectedSection = 
    				jQuery('<div></div>');
    			
    			WELOCALLY.PlaceFinderWidget._ajaxStatus = 
    				jQuery('<div></div>');
    				
    			WELOCALLY.PlaceFinderWidget._mapStatus = 
    				jQuery('<div></div>');	
    			   			
				WELOCALLY.PlaceFinderWidget._locationField =
					jQuery('<input type="text" name="location"/>');
				WELOCALLY.PlaceFinderWidget._searchField =
					jQuery('<input type="text" name="search"/>');
					
				WELOCALLY.PlaceFinderWidget._results = 
    				jQuery('<ol id="selectable"></ol>');	
    				
				WELOCALLY.PlaceFinderWidget._geocoder = new google.maps.Geocoder();
		
				WELOCALLY.PlaceFinderWidget._placeMarkers = [];
				WELOCALLY.PlaceFinderWidget._selectedPlace = {
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
						
				
				WELOCALLY.PlaceFinderWidget._searchLocation = 
					new google.maps.LatLng(); 
				
				WELOCALLY.PlaceFinderWidget.prepSource();
				WELOCALLY.PlaceFinderWidget._initialized = true;
					
				
		}
    
    	 // Get current script object
        var script = jQuery('SCRIPT');
        script = script[script.length - 1]; 
        
        //wrapper
        var wrapper = jQuery('<div></div>');
        jQuery(WELOCALLY.PlaceFinderWidget._selectedSection).attr('class','wl_widget_wrapper');
        
        //status
        jQuery(WELOCALLY.PlaceFinderWidget._ajaxStatus).css('display','none');
        jQuery(wrapper).append(WELOCALLY.PlaceFinderWidget._ajaxStatus);
        
        //location field
        jQuery(wrapper).append('<div>Enter a location to search such as "New York NY". You can even provide a full address for more refined searches.</div>');
        jQuery(WELOCALLY.PlaceFinderWidget._locationField).attr('class','wl_widget_field');
        jQuery(WELOCALLY.PlaceFinderWidget._locationField).bind('change' , WELOCALLY.PlaceFinderWidget.locationFieldInputHandler);       
    	jQuery(wrapper).append(WELOCALLY.PlaceFinderWidget._locationField);
    	
    	//search field
    	jQuery(wrapper).append('<div>Enter what you are searching for, this can be a type of place like "Restaurant", what they sell like "Pizza", or the name of the place like "Seward Park".</div>');       
    	jQuery(WELOCALLY.PlaceFinderWidget._searchField).attr('class','wl_widget_field');
    	jQuery(WELOCALLY.PlaceFinderWidget._searchField).bind('change' , WELOCALLY.PlaceFinderWidget.searchHandler);  
    	jQuery(wrapper).append(WELOCALLY.PlaceFinderWidget._searchField);
    	
    	var buttonDiv = jQuery('<div></div>').attr('class','wl_button_holder'); 	
    	var fetchButton = jQuery('<button>Search</div>');
    	
    	jQuery(fetchButton).attr('class','wl_finder_search');
    	jQuery(fetchButton).bind('click' , WELOCALLY.PlaceFinderWidget.searchHandler);
    	jQuery(buttonDiv).append(fetchButton); 
    	jQuery(wrapper).append(buttonDiv);  
    	
    	//selected
        jQuery(WELOCALLY.PlaceFinderWidget._selectedSection).css('display','none');
        jQuery(WELOCALLY.PlaceFinderWidget._selectedSection).attr('class','wl_widget_selected');
        jQuery(wrapper).append(WELOCALLY.PlaceFinderWidget._selectedSection);
    	    	
    	//google maps does not like jquery instances
    	var map_canvas = document.createElement('DIV');
    	jQuery(map_canvas).css('width','100%');
        jQuery(map_canvas).css('height','350px');
        jQuery(map_canvas).css('border','1px solid');
        jQuery(map_canvas).attr('class','welocally_widget');
    	jQuery(map_canvas).attr("id","map_canvas_widget");
          	
    	var loc = cfg.loc.split('_');
    	
    	WELOCALLY.PlaceFinderWidget._searchLocation = new google.maps.LatLng(loc[0], loc[1]);
    	
        WELOCALLY.PlaceFinderWidget._map = WELOCALLY.PlaceFinderWidget.makeMapFromConfig(cfg, map_canvas);
           
            
        jQuery(wrapper).append(map_canvas);  
        
        //map status
        jQuery(WELOCALLY.PlaceFinderWidget._mapStatus).css('display','none');
        jQuery(wrapper).append(WELOCALLY.PlaceFinderWidget._mapStatus);
        
        //results
        jQuery(WELOCALLY.PlaceFinderWidget._results).selectable();
        jQuery(WELOCALLY.PlaceFinderWidget._results).css('display','none');
        jQuery( WELOCALLY.PlaceFinderWidget._results ).bind( "selectableselected", WELOCALLY.PlaceFinderWidget.selectedItemHandler);        
        jQuery(wrapper).append(WELOCALLY.PlaceFinderWidget._results);
              
        jQuery(script).parent().before(wrapper);    
        
    };
    
    WELOCALLY.PlaceFinderWidget.makeMapFromConfig = function(cfg, map_canvas) {  	
    
    	WELOCALLY.PlaceFinderWidget._options = {
          center: WELOCALLY.PlaceFinderWidget._searchLocation,
          zoom: cfg.zoom,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        
        if(cfg.styles){
    		WELOCALLY.PlaceFinderWidget._options.styles = cfg.styles;
    	}
        
        var map = new google.maps.Map(
        	map_canvas, 
            WELOCALLY.PlaceFinderWidget._options);
            
        WELOCALLY.PlaceFinderWidget.setupMapListeners(map, WELOCALLY.PlaceFinderWidget._placeMarkers);
        
        return map;
    
    };
    
    
    WELOCALLY.PlaceFinderWidget.prepSource = function() {
			console.log('prepped');			
	};
	
	
	WELOCALLY.PlaceFinderWidget.setStatus = function(statusArea, message, type, showloading){
		jQuery(statusArea).html('');
		jQuery(statusArea).removeClass();
		jQuery(statusArea).addClass(type);
		
		if(showloading){
			//jQuery(statusArea).append('<img src="" alt="" title=""/>');
		}
		
		jQuery(statusArea).append('<em>'+message+'</em>');
		
		if(message != ''){
			jQuery(statusArea).show();
		} else {
			jQuery(statusArea).hide();
		}	
		
	};
		
	WELOCALLY.PlaceFinderWidget.getMapRadius = function(map) {
	
		bounds = map.getBounds();

		center = bounds.getCenter();
		ne = bounds.getNorthEast();
		
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
	
	
	WELOCALLY.PlaceFinderWidget.selectedItemHandler = function(event, ui) {
	
		jQuery(WELOCALLY.PlaceFinderWidget._selectedSection).empty();
		
		
		if(this.nodeName=='OL'){
			var index = ui.selected.id.replace("selectable_item_","");
			WELOCALLY.PlaceFinderWidget._selectedPlace = 
				WELOCALLY.PlaceFinderWidget._placeMarkers[index].item;
		} else if(this.nodeName=='MARKER'){
			jQuery('#selectable').find('li').removeClass('ui-selected');
			WELOCALLY.PlaceFinderWidget._selectedPlace = 
				this.item;
		}
		
		if(WELOCALLY.PlaceFinderWidget._cfg.showSelection){
			jQuery(WELOCALLY.PlaceFinderWidget._selectedSection)
			.append('<div class="wl_selected_name">'+WELOCALLY.PlaceFinderWidget._selectedPlace.properties.name+'</div>')
			.append('<div class="wl_selected_adress">'+WELOCALLY.PlaceFinderWidget._selectedPlace.properties.address+' '+
				WELOCALLY.PlaceFinderWidget._selectedPlace.properties.city+
				' '+WELOCALLY.PlaceFinderWidget._selectedPlace.properties.province+
				' '+WELOCALLY.PlaceFinderWidget._selectedPlace.properties.postcode+'</div>');
					
			
			if(WELOCALLY.PlaceFinderWidget._selectedPlace.properties.phone != null) {
				jQuery(WELOCALLY.PlaceFinderWidget._selectedSection)
					.append('<div class="wl_selected_phone">'+WELOCALLY.PlaceFinderWidget._selectedPlace.properties.phone+'</div>');
			}
	
			if(WELOCALLY.PlaceFinderWidget._selectedPlace.properties.website != null && 
				WELOCALLY.PlaceFinderWidget._selectedPlace.properties.website != '' ) {
				var website = WELOCALLY.PlaceFinderWidget._selectedPlace.properties.website;
				if(WELOCALLY.PlaceFinderWidget._selectedPlace.properties.website.indexOf('http://') == -1) {
					website = 'http://'+WELOCALLY.PlaceFinderWidget._selectedPlace.properties.website;				
				}
				
				jQuery(WELOCALLY.PlaceFinderWidget._selectedSection)
					.append('<div class="wl_selected_web"><a target="_new" href="'+website+'">'+website+'</a></div>');
	
			} 
	
			if(WELOCALLY.PlaceFinderWidget._selectedPlace.properties.city != null && 
				WELOCALLY.PlaceFinderWidget._selectedPlace.properties.province != null){
					var qS = WELOCALLY.PlaceFinderWidget._selectedPlace.properties.city+" "+
						WELOCALLY.PlaceFinderWidget._selectedPlace.properties.province;
					if(WELOCALLY.PlaceFinderWidget._selectedPlace.properties.address != null)
						qs=WELOCALLY.PlaceFinderWidget._selectedPlace.properties.address+" "+qS;
					if(WELOCALLY.PlaceFinderWidget._selectedPlace.properties.postcode != null)
						qs=qs+" "+WELOCALLY.PlaceFinderWidget._selectedPlace.properties.postcode;
					var qVal = qs.replace(" ","+");
					
					jQuery(WELOCALLY.PlaceFinderWidget._selectedSection)
					.append('<div class="wl_selected_driving"><a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+
						qVal+'" target="_new">Driving Directions</a></div>');
				
			}
			 
			//the tag
			var tag = '[welocally id="'+WELOCALLY.PlaceFinderWidget._selectedPlace._id+'" /]';
			var inputArea = jQuery('<input/>');
			jQuery(inputArea).val(tag);
			var wlSelectedTagArea = jQuery('<div class="wl_selected_tag"></div>');
			jQuery(wlSelectedTagArea).append(inputArea);
			 
			jQuery(WELOCALLY.PlaceFinderWidget._selectedSection)
			 		.append(wlSelectedTagArea)
					.show();
		
		}
		
		
		//broadcast to listeners
		if(WELOCALLY.PlaceFinderWidget._observers != null && WELOCALLY.PlaceFinderWidget._observers.length>0){
			jQuery.each(WELOCALLY.PlaceFinderWidget._observers, function(i,item){
				if(item instanceof WELOCALLY_PlaceSelectionListener){
					item.show(WELOCALLY.PlaceFinderWidget._selectedPlace);
				}						
			});
		}
		
		console.log('selected:'+WELOCALLY.PlaceFinderWidget._selectedPlace.properties.name);

	};
	
	
	WELOCALLY.PlaceFinderWidget.locationFieldInputHandler = function(event) {
		var addressValue = jQuery(this).val();
		console.log('location: '+addressValue);
		
		WELOCALLY.PlaceFinderWidget.setStatus(WELOCALLY.PlaceFinderWidget._ajaxStatus, 'Geocoding','update',true);
		
		jQuery(WELOCALLY.PlaceFinderWidget._selectedSection).hide();
		
		WELOCALLY.PlaceFinderWidget._geocoder.geocode( { 'address': addressValue}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK &&  WELOCALLY.PlaceFinderWidget.validGeocodeForSearch(results[0])) {
			
			
				jQuery(WELOCALLY.PlaceFinderWidget._locationField).val(results[0].formatted_address);
				WELOCALLY.PlaceFinderWidget._formattedAddress = results[0].formatted_address;
				
			
				if(results[0].address_components.length<=5){
					WELOCALLY.PlaceFinderWidget._map.setZoom(14);
				} else {
					WELOCALLY.PlaceFinderWidget._map.setZoom(16);
				}
					
				//set the model
				WELOCALLY.PlaceFinderWidget._selectedPlace.properties.address = 
					WELOCALLY.PlaceFinderWidget.getShortNameForType("street_number", results[0].address_components)+' '+
					WELOCALLY.PlaceFinderWidget.getShortNameForType("route", results[0].address_components);
				
				WELOCALLY.PlaceFinderWidget._selectedPlace.properties.city = 
					WELOCALLY.PlaceFinderWidget.getShortNameForType("locality", results[0].address_components);
				
				WELOCALLY.PlaceFinderWidget._selectedPlace.properties.province = 
					WELOCALLY.PlaceFinderWidget.getShortNameForType("administrative_area_level_1", results[0].address_components);
		
				WELOCALLY.PlaceFinderWidget._selectedPlace.properties.postcode = 
					WELOCALLY.PlaceFinderWidget.getShortNameForType("postal_code", results[0].address_components);
				
				WELOCALLY.PlaceFinderWidget._selectedPlace.properties.country = 
					WELOCALLY.PlaceFinderWidget.getShortNameForType("country", results[0].address_components);
				
				WELOCALLY.PlaceFinderWidget._selectedPlace.geometry.coordinates = [];
				WELOCALLY.PlaceFinderWidget._selectedPlace.geometry.coordinates.push(results[0].geometry.location.lng());
				WELOCALLY.PlaceFinderWidget._selectedPlace.geometry.coordinates.push(results[0].geometry.location.lat());
				
				WELOCALLY.PlaceFinderWidget._searchLocation = 
					new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
				
							
				//WELOCALLY.PlaceFinderWidget.deleteOverlays(WELOCALLY.PlaceFinderWidget._placeMarkers);
				
				WELOCALLY.PlaceFinderWidget.resetOverlays(
					WELOCALLY.PlaceFinderWidget._searchLocation,
					WELOCALLY.PlaceFinderWidget._placeMarkers) 
									
				//jQuery(WELOCALLY.PlaceFinderWidget._searchField).show();
				WELOCALLY.PlaceFinderWidget.setStatus(WELOCALLY.PlaceFinderWidget._ajaxStatus, '','message',true);
				
				WELOCALLY.PlaceFinderWidget.setStatus(
						WELOCALLY.PlaceFinderWidget._mapStatus, 
						WELOCALLY.PlaceFinderWidget.makeMapStatus(WELOCALLY.PlaceFinderWidget._map, WELOCALLY.PlaceFinderWidget._placeMarkers) , 
						'message', false);
				
				if(WELOCALLY.PlaceFinderWidget._searchField.val()){
					jQuery(WELOCALLY.PlaceFinderWidget._searchField).trigger('change');
				}
				
				//broadcast to listeners
				if(WELOCALLY.PlaceFinderWidget._observers != null && WELOCALLY.PlaceFinderWidget._observers.length>0){
					jQuery.each(WELOCALLY.PlaceFinderWidget._observers, function(i,item){
						if(item instanceof WELOCALLY_DealFinderWidget){
							item.setLocation(results[0].geometry.location.lat(), results[0].geometry.location.lng());
						}						
					});
				}
							
				
			} else {
				WELOCALLY.PlaceFinderWidget.setStatus(WELOCALLY.PlaceFinderWidget._ajaxStatus, 'Could not geocode:'+status,'warning',false);
			} 
		});
		
	};
	
	WELOCALLY.PlaceFinderWidget.searchHandler = function(event) {
	
		jQuery(WELOCALLY.PlaceFinderWidget._selectedSection).hide();
		
		if(WELOCALLY.PlaceFinderWidget._selectedPlace.geometry.coordinates[1] && WELOCALLY.PlaceFinderWidget._selectedPlace.geometry.coordinates[0]){
			
			if(!WELOCALLY.PlaceFinderWidget._locationField) {
				WELOCALLY.PlaceFinderWidget._formattedAddress = results[0].formatted_address;
			}
			
			var searchValue = WELOCALLY.util.replaceAll(jQuery(WELOCALLY.PlaceFinderWidget._searchField).val(),' ','+');

			var query = {
				q: searchValue,
				loc: WELOCALLY.PlaceFinderWidget._selectedPlace.geometry.coordinates[1]+'_'+WELOCALLY.PlaceFinderWidget._selectedPlace.geometry.coordinates[0],
				radiusKm: WELOCALLY.PlaceFinderWidget.getMapRadius(WELOCALLY.PlaceFinderWidget._map)
			};
			
			var surl = WELOCALLY.PlaceFinderWidget._cfg.endpoint +
				'/geodb/place/1_0/search.json?'+WELOCALLY.util.serialize(query)+"&callback=?";
			console.log(surl);
			
			WELOCALLY.PlaceFinderWidget.setStatus(WELOCALLY.PlaceFinderWidget._ajaxStatus, 'Finding places','update',true);
			jQuery(WELOCALLY.PlaceFinderWidget._results).hide();
			
			WELOCALLY.PlaceFinderWidget.resetOverlays(
						WELOCALLY.PlaceFinderWidget._searchLocation,
						WELOCALLY.PlaceFinderWidget._placeMarkers);
				
			jQuery.ajax({
					  url: surl,
					  dataType: "json",
					  success: function(data) {
						//setup the bounds
						var bounds = new google.maps.LatLngBounds();
						bounds.extend(WELOCALLY.PlaceFinderWidget._searchLocation);
						
						//set to result bounds if enough results
						if(data != null && data.length>0){						
							WELOCALLY.PlaceFinderWidget.setStatus(WELOCALLY.PlaceFinderWidget._ajaxStatus, '','message',false);
						} else {
							bounds = WELOCALLY.PlaceFinderWidget._map.getBounds();
							WELOCALLY.PlaceFinderWidget.setStatus(WELOCALLY.PlaceFinderWidget._ajaxStatus, 'No results were found matching your search.','warning',false);
						}
						
						jQuery(WELOCALLY.PlaceFinderWidget._results).empty();
						
						jQuery.each(data, function(i,item){
									
							var latlng = new google.maps.LatLng(item.geometry.coordinates[1], item.geometry.coordinates[0]);
							bounds.extend(latlng);
							
							var itemLocation = 
								new google.maps.LatLng(
									item.geometry.coordinates[1], 
									item.geometry.coordinates[0]);	
							
							WELOCALLY.PlaceFinderWidget.addMarker(
								WELOCALLY.PlaceFinderWidget._placeMarkers,
								WELOCALLY.PlaceFinderWidget._map,
								itemLocation,
								item,
								WELOCALLY.PlaceFinderWidget._cfg.imagePath+'/marker_place_'+WELOCALLY.PlaceFinderWidget.colName(i)+'.png');
							
							//add result to list
							var listItem = WELOCALLY.PlaceFinderWidget.makeItemContents(item,i);
							
							jQuery(listItem).attr('id','selectable_item_'+i);
							jQuery(listItem).attr('class','ui-widget-content');
							jQuery(WELOCALLY.PlaceFinderWidget._results).append(jQuery(listItem));
							jQuery(WELOCALLY.PlaceFinderWidget._results).show();
				
						});
						
						
						WELOCALLY.PlaceFinderWidget._map.fitBounds(bounds);
						var listener = google.maps.event.addListener(WELOCALLY.PlaceFinderWidget._map, "idle", function() { 						
							if (WELOCALLY.PlaceFinderWidget._map.getZoom() > 17) WELOCALLY.PlaceFinderWidget._map.setZoom(17); 
							google.maps.event.removeListener(listener); 
						});
																
					}
			});
		
		
		} else {
			WELOCALLY.PlaceFinderWidget.setStatus(WELOCALLY.PlaceFinderWidget._ajaxStatus, 'Please choose a location for search.','warning',false);
		}
	
		
	
	};
	

	WELOCALLY.PlaceFinderWidget.makeItemContents = function (item, i) {
		var wrapper = jQuery('<li></il>');
		jQuery(wrapper)
			.append(jQuery('<img class="selectable_marker" src='+
					WELOCALLY.PlaceFinderWidget._cfg.imagePath+'/marker_place_'+
				WELOCALLY.PlaceFinderWidget.colName(i)+'.png'+' />'))
			.append(jQuery('<div class="selectable_title">'+
				item.properties.name+'</div>'))
			.append(jQuery('<div class="selectable_address">'+
				item.properties.address+'</div>'))
			.append(jQuery('<div class="selectable_distance">'+item.distance.toFixed(2)+'km </div>'));
		
		wrapper.item = item;
				
		return wrapper;
	
	
	};
	
	
	WELOCALLY.PlaceFinderWidget.colName = function (n) {
		var s = "";
		while(n >= 0) {
			s = String.fromCharCode(n % 26 + 65) + s;
			n = Math.floor(n / 26) - 1;
		}
		return s;
	}
	
	WELOCALLY.PlaceFinderWidget.setupMapListeners = function (map, markers) {	
		google.maps.event.addListener(map, 'zoom_changed', function() {
			zoomChangeBoundsListener = google.maps.event.addListener(map, 'bounds_changed', function(event) {
				WELOCALLY.PlaceFinderWidget.setStatus(
						WELOCALLY.PlaceFinderWidget._mapStatus, 
						WELOCALLY.PlaceFinderWidget.makeMapStatus(map, markers),
						'message', false);
			});
		});		
	};
	
	WELOCALLY.PlaceFinderWidget.makeMapStatus = function (map, markers) {	
		var status = '';
		
		var radius = WELOCALLY.PlaceFinderWidget.getMapRadius(WELOCALLY.PlaceFinderWidget._map);
		if(markers != null && markers.length>0)
			status = status + ' places found: '+markers.length;
		status = status + ' search radius: '+radius.toFixed(2)+'km';
		status = status + ' zoom: '+map.getZoom();
		return status;
	};
		
	WELOCALLY.PlaceFinderWidget.validGeocodeForSearch = function (geocode) {
	
		if(geocode.geometry.location.lat() && geocode.geometry.location.lng())
			return true;
		
	};
		
	WELOCALLY.PlaceFinderWidget.hasType = function(type_name, address_components){
		for (componentIndex in address_components) {
			var component = address_components[componentIndex];
			if(component.types[0] == type_name)
				return true;
		}
		return false;
	};
		
	WELOCALLY.PlaceFinderWidget.getShortNameForType = function(type_name, address_components){
		for (componentIndex in address_components) {
			var component = address_components[componentIndex];
			if(component.types[0] == type_name)
				return address_components[componentIndex].short_name;
		}
		return null;	
	};
	
	//will remove all overlays except for the search location
	WELOCALLY.PlaceFinderWidget.resetOverlays=function (location,markersArray) {
			
		WELOCALLY.PlaceFinderWidget.deleteOverlays(markersArray);
		
		WELOCALLY.PlaceFinderWidget._map.setCenter(location);								
				WELOCALLY.PlaceFinderWidget.addLocationMarker(
					WELOCALLY.PlaceFinderWidget._map,
					location,
					WELOCALLY.PlaceFinderWidget._cfg.imagePath+'/marker_search.png');		
	};
	
	// Shows any overlays currently in the array
	WELOCALLY.PlaceFinderWidget.showOverlays =function (markersArray, map) {
	  if (markersArray) {
		for (i in markersArray) {
		  markersArray[i].setMap(map);
		}
	  }
	};
		
	// Deletes all markers in the array by removing references to them
	WELOCALLY.PlaceFinderWidget.deleteOverlays=function (markersArray) {
	  if (markersArray) {
		for (i in markersArray) {
		  markersArray[i].setMap(null);
		}
		markersArray.length = 0;
	  }
	};
	
	WELOCALLY.PlaceFinderWidget.addLocationMarker = function(markerMap, location, icon) {
	
	  if(WELOCALLY.PlaceFinderWidget._searchLocationMarker != null)	
	  	WELOCALLY.PlaceFinderWidget._searchLocationMarker.setMap(null);
	  
	  var marker = new google.maps.Marker({
		position: location,
		map: markerMap,
		icon: icon
	  });
	  
	  WELOCALLY.PlaceFinderWidget._searchLocationMarker = marker;
	};
	
	WELOCALLY.PlaceFinderWidget.addMarker = function(markersArray, markerMap, location, item, icon) {
	  var marker = new google.maps.Marker({
	    nodeName: 'MARKER',
		position: location,
		title: item.properties.name,
		map: markerMap,
		icon: icon,
		item: item
	  });
	  google.maps.event.addListener(marker, 'click', WELOCALLY.PlaceFinderWidget.selectedItemHandler);
	  markersArray.push(marker);
	};
		
	// Removes the overlays from the map, but keeps them in the array
	WELOCALLY.PlaceFinderWidget.clearOverlays = function (markersArray) {
	  if (markersArray) {
		for (i in markersArray) {
		  markersArray[i].setMap(null);
		}
	  }
	};
}