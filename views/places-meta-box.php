<?php global $post; ?>
<script type="text/javascript">
if (!window.WELOCALLY) {
    window.WELOCALLY = {
    	
    };
}

//this can go farther
WELOCALLY.meta = {
	post: {
		type: '<?php echo $post->post_type; ?>',
		id: '<?php echo $post->ID; ?>'
	}
}

var jsonObjFeatures = []; //declare features array
var markersArray = [];
var selectedFeatureIndex = 0;
var selectedClassifierLevel='';
var selectedCategories='';
var map;
var selectedGeocode;
var selectedPlace = {
	properties: {},
	type: 'Place',
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

var activityType;

var jqxhr;

function setStatus(message, type, showloading){
	jQuery('#welocally-post-error').html('');
	jQuery("#welocally-post-error").removeClass();
	
	if(type=='update'){
		jQuery('#welocally-post-error').addClass('welocally-update');
	} else if(type=='error'){
		jQuery('#welocally-post-error').addClass('welocally-error');
	} else if(type=='message'){
		jQuery('#welocally-post-error').addClass('welocally-message');
	}
	
	if(showloading){
		jQuery('#welocally-post-error').append('<img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/ajax-loading.gif" alt="" title=""/>');
	}
	
	jQuery('#welocally-post-error').append('<em>'+message+'</em>');
	
	if(message != ''){
		jQuery('#welocally-post-error').show();
	} else {
		jQuery('#welocally-post-error').hide();
	}	
	
}

function addMarker(map_marker, location) {
  var marker = new google.maps.Marker({
    position: location,
    map: map_marker
  });
  markersArray.push(marker);
}

// Removes the overlays from the map, but keeps them in the array
function clearOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(null);
    }
  }
}

// Shows any overlays currently in the array
function showOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(map);
    }
  }
}

// Deletes all markers in the array by removing references to them
function deleteOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(null);
    }
    markersArray.length = 0;
  }
}


function searchLocations(location, queryString, radiusKm) {	
	jQuery('#results').hide();
		  		
	setStatus('Loading Places...', 'message', true);
	
	jQuery('#selectable').empty();
	jsonObjFeatures = [];
		    
    
    var options = {
		action: 'get_places',
		q: queryString,
    	loc: location.lat()+'_'+location.lng(),
    	radiusKm: 20
	};
				
	jQuery.ajax({
	  type: 'GET',
	  url: ajaxurl,
	  data: options,
	  dataType : 'json',
	  beforeSend: function(jqXHR){
        jqxhr = jqXHR;
      },
      //dont know why success is not working on empty results
      statusCode: { 
    	200: function(data, textStatus, jqXHR) {
			if(data != null && data.length == 0) {
	  			setStatus('Sorry no places were found that match your query.', 'update', false);
	  			jQuery('#add-place-section').append(jQuery('#cancel-finder-workflow'));	
	  			jQuery('#place-selector').append(jQuery('#add-place-section'));	
	  			jQuery('#add-place-section').show();
	  			  			
	  		}
    	}
      },
	  error : function(jqXHR, textStatus, errorThrown) {	  		
	  		if(textStatus != 'abort'){
	  			setStatus('ERROR : '+textStatus+" there may been a network error or a problem with your settings.", 'error', false);
	  			jQuery('#add-place-section').append(jQuery('#cancel-finder-workflow'));	
	  			jQuery('#place-selector').append(jQuery('#add-place-section'));	
	  			jQuery('#add-place-section').show();
	  			
	  		}	else {
	  			console.log(textStatus);
	  		}		
	  },
	  success : function(data, textStatus, jqXHR) {


	  		
	  		
	  		setStatus('', 'message', false);

		    jQuery('#add-place-section').append(jQuery('#cancel-finder-workflow'));	
		    jQuery('#cancel-finder-workflow').show();	  		
	  		if(data != null && data.length == 0) {
	  			setStatus('Sorry no places were found that match your query.', 'update', false);
	  			
	  			jQuery('#place-selector').append(jQuery('#add-place-section'));	
	  			
	  			
	  		} else if(data != null && data.length > 0) {
				jQuery.each(data, function(i,item){
					jsonObjFeatures.push(item);	    		
					jQuery('#selectable').append(buildListItemForPlace(item,i));				
				});
				jQuery('#search-geocoded-section').append(jQuery('#results'));	
				jQuery('#results').append(jQuery('#add-place-section'));											
				jQuery("#results").show();	
				
				
			} else if(data != null && data.errors != null) {
			
				buildErrorMessages(data.errors);	
					
			} else {
				setStatus('There was a problem, please check your settings and network.', 'error', false);
	  			jQuery('#place-selector').append(jQuery('#add-place-section'));	
	  			
			}
			jQuery('#add-place-section').show();
	  		
	  		
	  }
	});
   
}

function buildListItemForPlace(place,i) {
        var itemLabel = '<b>'+place.properties.name+'</b> - '+place.distance.toFixed(2)+' km';
        if (place.properties.address) {
            itemLabel += "<br>" + place.properties.address+" "+
            	place.properties.city+" "+place.properties.province+" "
            	+place.properties.postcode;
        }
		return '<li class=\"ui-widget-content\" id="f'+i+'" title="select place">'+itemLabel+'</li>';
}

function buildSelectedInfoForPlace(place) {	
        var itemLabel = '<b>'+place.properties.name+'</b>';
        if (place.properties.address) {
            itemLabel += "<br>" + place.properties.address;
        }
		return '<div class=\"selected-place-info\">'+itemLabel+'</div>';
}

function buildCategorySelectionsForPlace(place, container) {
		
		container.html('');
		var index = -1;
		
		for (classifier in place.properties.classifiers) {
			index = classifier;
			container.append('<li class=\"ui-widget-content\">'+place.properties.classifiers[classifier].category+'</li>');
			container.append('<li class=\"ui-widget-content\">'+place.properties.classifiers[classifier].subcategory+'</li>');			
		}
		if(index != -1){
			jQuery("#categories-choice").show();
		}
		
		
}

function setSelectedPlaceInfo(selectedItem, post) {
		
	//set form fields for save post
	selectedPlace = selectedItem;
	
	jQuery('#edit-place-name-selected').html(selectedPlace.properties.name);
	jQuery('#search-geocoded-address-selected').html(selectedPlace.properties.address+
		" "+selectedPlace.properties.city+" "+selectedPlace.properties.province+" "+
		selectedPlace.properties.postcode);
	
	jQuery('#share-meta-tagtext').val(WELOCALLY.places.tag.makePlaceTag(selectedPlace, post));	
	jQuery('#places-tag-selected').show(); 

	
	
	var selectedLocation = new google.maps.LatLng(selectedPlace.geometry.coordinates[1], selectedPlace.geometry.coordinates[0]);		
	var myOptions = {
		center: selectedLocation,
	  	zoom: 15,
	  	mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	
	if(map==null){
		map = new google.maps.Map(document.getElementById("map_canvas"),
			myOptions);
			
		//need to add listeners
		WELOCALLY.places.map.setMapEvents(map);		
	
	} else {
		map.setCenter(selectedLocation);
	}
			
	deleteOverlays();							
	addMarker(map,selectedLocation);	
	
	
	//hide the selection area
	jQuery("#place-selector").hide();	
	jQuery("#edit-place-form").hide();	
	
	//set the form value
	//this is wehere we should build the tag
	jQuery("#place-selected").val(JSON.stringify(selectedItem));
	
	jQuery('#edit-place-name-selected')
	
	//show the *selected* area	
	jQuery("#selected-place-info").append(jQuery('#places-tag-selected'));
	jQuery("#selected-place-info").append(jQuery('#edit-place-name-selected'));
	jQuery("#selected-place-info").append(jQuery('#search-geocoded-address-selected'));	
	jQuery("#selected-place-info").append(jQuery('#map_canvas'));
	
	jQuery("#results").hide();
	jQuery("#selected-place").show();		
	
}

function validGeocodeForSearch(geocode) {
	
	var hasAll = hasType("country", geocode.address_components);
	if(hasAll){
		hasAll = hasType("locality", geocode.address_components);
	}
	if(hasAll){
		hasAll = hasType("administrative_area_level_1", geocode.address_components);
	}
	if(hasAll){
		hasAll = hasType("postal_code", geocode.address_components);
	}
	return hasAll;
}
	
function findCategory(categoryName){
	var cat;
	jQuery.each(currentCategories, function(key, val) {
		if(val.name == categoryName) {
			cat = val;
		}
	});
	return cat;
}

function getCategories(type, category) {
	jQuery('#edit-place-categories-selection').hide();
	
	setStatus('Loading Categories...','message', true);
			    
	 var options = {
		action: 'get_classifiers_types'
	};
	
	var base;
	selectedClassifierLevel = 'Type';
	
	if(type != null && category == null){
		base = type;
		selectedClassifierLevel = 'Category';
		
		options.action='get_classifiers_categories';
		options.type = type;
				
	} else if(type != null && category != null){
		base = category;
		selectedClassifierLevel = 'Subcategory';
				
		options.action='get_classifiers_subcategories';
		options.type = type;
		options.category = category;
	}
	
	jqxhr = jQuery.ajax({
		  type: 'GET',
		  url: ajaxurl,
		  data: options,
		  dataType : 'json',
		  beforeSend: function(jqXHR){
	        jqxhr = jqXHR;
	      },
		  error : function(jqXHR, textStatus, errorThrown) {
			if(textStatus != 'abort'){
	  			setStatus('ERROR : '+textStatus, 'error', false);
	  			jQuery('#edit-place-categories-selection').append(jQuery('#cancel-finder-workflow'));	
	  		}	else {
	  			console.log(textStatus);
	  		}	
		  },		  
		  success : function(data, textStatus, jqXHR) {
		  	
		  	setStatus('', 'message', false);		  	
		  			  	
		  	jQuery('#edit-place-categories-selection-list').html('');
		  			  	
		  	if(base != null && data.length==1){
		  		jQuery('#edit-place-categories-selection-list').append('<li style="display:inline-block;">'+base+'</li>');
		  	}
		  	
			if(data != null && data.errors != null) {
				buildErrorMessages(data.errors);	
				jQuery('#edit-place-categories-selection').append(jQuery('#cancel-finder-workflow'));		
			} else {
				currentCategories = data;
				jQuery.each(data, function(key, val) {
					if(val != null && val != '' ) {
						jQuery('#edit-place-categories-selection-list').append('<li style="display:inline-block;">'+val+'</li>');	
					}
				});		
				jQuery('#categories-section').show();
				jQuery('#edit-place-categories-selection').append(jQuery('#back-action'));		
				jQuery('#edit-place-categories-selection').show();
				
				jQuery('#add-place-actions-section').append(jQuery('#cancel-finder-workflow'));
				jQuery('#cancel-finder-workflow').show();	
				jQuery('#add-place-actions-section').show();	
				
			}
		  }
		});
}

function getShortNameForType(type_name, address_components){
	for (componentIndex in address_components) {
		var component = address_components[componentIndex];
		if(component.types[0] == type_name)
			return address_components[componentIndex].short_name;
	}
	return null;

}

function hasType(type_name, address_components){
	for (componentIndex in address_components) {
		var component = address_components[componentIndex];
		if(component.types[0] == type_name)
			return true;
	}
	return false;

}

function buildErrorMessages(errors) {
	
	var errorMessageOut = "ERROR: ";
	jQuery.each(errors, function(i,error){
		var number = i+1;
		errorMessageOut = errorMessageOut+number+". "+error.errorMessage;
	});	
	setStatus(errorMessageOut,'error', false);
}

function buildMissingFieldsErrorMessages(fields) {
	jQuery('#welocally-post-error').html('');
	jQuery('#welocally-post-error').append('<ul>');
	jQuery('#welocally-post-error').append('<li>error '+fields+' missing</li>');
	jQuery('#welocally-post-error').append('</ul>');
	jQuery('#welocally-post-error').addClass('welocally-error error fade');	
	jQuery('#welocally-post-error').show();	
}

// intercepts a next event and resets everything, but is acting as the first phase
function cancelHandler(event) {
		setStatus('','message', false);
		
		//reset selected place
    	selectedPlace = {
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
		
		//wipe clean any input fields
		jQuery('#place-selector-form input').val('');
		
		//all sections hidden
		jQuery('.resetable').hide();
 	
        jQuery('#edit-place-form').hide();
        jQuery('#place-selector').show();
        
        return nextHandler(event);
        
}

function nextHandler(event) {
	console.log('next phase:'+event.data.phase);
	setStatus('','message', false);
	var phase = event.data.phase;
	var error = false;
	if(phase=='associate-place-section'){
		jQuery('.resetable', jQuery('#search-place-name-section')).show();
		jQuery('#cancel-finder-workflow').hide();
		var section = jQuery('#search-place-name-section');
		jQuery('#back-action' ).hide();
		section.append(jQuery('#edit-place-name-title'));
		section.append(jQuery('#place-name-input'));
		section.append(jQuery('#place-name-saved'));
		section.append(jQuery('#back-action'));
		section.append(jQuery('#next-action'));
		section.show();
		
		jQuery('#place-name-title').html('Search Term: Enter a search term such as the place name or the category ie. "Pizza"');		
		
		//manage event model
		jQuery('#next-action' ).unbind('click').bind('click' , { phase: 'search-place-name-section' }, nextHandler);		
		jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'search-place-name-section' }, backHandler);
		
		jQuery('#place-selector-form').show();
		
							
	} else if(phase=='search-place-name-section'){
		if (WELOCALLY.util.trim(jQuery("#edit-place-name").val()) == ''){
			setStatus('Search Empty. Please enter a search term or place name.','error', false);
			error = true;

		} else {
			selectedPlace.properties.name = jQuery("#edit-place-name").val();
    	
	    	jQuery('#edit-place-name-selected').html(selectedPlace.properties.name);
	    	jQuery('#place-street-title').html('Location: Choose the location or full address you would like to search from. ie. Oakland, CA 94612');
								
		    //put the address search in the top
		    jQuery('.resetable', jQuery('#search-place-address-section')).show();
		    
		    var section = jQuery('#search-place-address-section');	
		    section.append(jQuery('#edit-place-name-selected'));	    
			section.append(jQuery('#street-name-input'));
			section.append(jQuery('#street-address-saved'));	
			section.append(jQuery('#back-action'));
			section.append(jQuery('#next-action'));	
			
			jQuery('#back-action').show();
			
			section.show();	
			
			jQuery('#next-action' ).unbind('click').bind('click' , { phase: 'search-place-address-section' }, nextHandler);		
			jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'search-place-address-section' }, backHandler);
		}
					
	} else if(phase=='search-place-address-section'){
		if (WELOCALLY.util.trim(jQuery('#edit-place-street').val()) == ''){
			setStatus('Location Empty. Please enter a location to start search from ie Oaklnd CA, 94612.','error', false);
			error = true;

		} else {
			
			setStatus('Geocoding...','message', true);
		
			var address = jQuery('#edit-place-street').val();
			
			jQuery('#search-geocoded-section').append(jQuery('#back-action'));	
			jQuery('#search-geocoded-section').hide();
			
			jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'search-place-address-section' }, backHandler);
							
			geocoder.geocode( { 'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK &&  validGeocodeForSearch(results[0])) {
					jQuery('#search-geocoded-name-selected').html(selectedPlace.properties.name);
					jQuery('#search-geocoded-address-selected').html(results[0].formatted_address);										
					jQuery('#search-geocoded-section').append(jQuery('#search-geocoded-address-selected'));
					jQuery('#search-geocoded-section').append(jQuery('#map_canvas'));	
					jQuery('#search-geocoded-section').show();
											
					//do the map stuff
					selectedGeocode = results[0];
		
					//set the model
					selectedPlace.properties.address = 
						getShortNameForType("street_number", selectedGeocode.address_components)+' '+
						getShortNameForType("route", selectedGeocode.address_components);
					
					selectedPlace.properties.city = 
						getShortNameForType("locality", selectedGeocode.address_components);
					
					selectedPlace.properties.province = 
						getShortNameForType("administrative_area_level_1", selectedGeocode.address_components);
			
					selectedPlace.properties.postcode = 
						getShortNameForType("postal_code", selectedGeocode.address_components);
					
					selectedPlace.properties.country = 
						getShortNameForType("country", selectedGeocode.address_components);
					
					selectedPlace.geometry.coordinates = [];
					selectedPlace.geometry.coordinates.push(selectedGeocode.geometry.location.lng());
					selectedPlace.geometry.coordinates.push(selectedGeocode.geometry.location.lat());
	
			
					var myOptions = {
					  zoom: 15,
					  mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					
					if(map==null){
						map = new google.maps.Map(document.getElementById("map_canvas"),
							myOptions);
					}
							
					deleteOverlays();
					map.setCenter(selectedGeocode.geometry.location);								
					addMarker(map,selectedGeocode.geometry.location);					
					
					jQuery('.resetable', jQuery('#search-geocoded-section')).show();	
					jQuery('#back-action').hide();											
					jQuery('#search-geocoded-section').show();	
									
					setStatus('','message', false);
					
					//ajax call
					searchLocations(selectedGeocode.geometry.location, selectedPlace.properties.name, 30);
										
							
				} else {
					console.log("Geocode was not successful for the following reason: " + status);
					setStatus('There was a problem geocoding with the specified location,'+
						' please make your search more specific and try again. status:'+
						status, 'update', false);							
					
					jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'search-place-address-section' }, backHandler);
					jQuery('#search-place-address-section').append(jQuery('#back-action'));	
					jQuery('#search-place-address-section').append(jQuery('#cancel-finder-workflow'));
					jQuery('#search-place-address-section').show();				
					
			  	} 
			});
			
		}
				
		
    		
	} else if(phase=='edit-place-name-section'){
			
			
		selectedPlace.properties.name = jQuery("#edit-place-name").val();
		jQuery('#edit-place-name-selected').html(selectedPlace.properties.name);
					
	    //put the address search in the top
	    jQuery('#place-street-title').html('Location: Enter full street address where the place is as one line ie. 1714 Franklin Street, Oakland, CA 94612');
		
	    var section = jQuery('#edit-place-address-section');
	    section.append(jQuery('#edit-place-name-selected'));
		section.append(jQuery('#street-name-input'));
		section.append(jQuery('#street-address-saved'));			
		section.append(jQuery('#back-action'));
		section.append(jQuery('#next-action'));	
		jQuery('#back-action').show();
		section.show();	
				
		jQuery('#next-action' ).unbind('click').bind('click' , { phase: 'edit-place-address-section' }, nextHandler);		
		jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'edit-place-address-section' }, backHandler);
		
		
	} else if(phase=='edit-place-address-section'){
					
		setStatus('Geocoding...','message', true);
		var address = jQuery('#edit-place-street').val();
		
		var section = jQuery('#edit-place-address-section');
		section.append(jQuery('#back-action'));			
		section.append(jQuery('#next-action'));	
		jQuery('#back-action').show();		
		section.show();	
		
		jQuery('#edit-geocoded-section').append(jQuery('#back-action'));
		
		jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'edit-geocoded-section' }, backHandler);
				
		geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK &&  validGeocodeForSearch(results[0])) {
				jQuery('#edit-geocoded-name-selected').html(selectedPlace.properties.name);
				jQuery('#edit-geocoded-address-selected').html(results[0].formatted_address);										
				
				jQuery('#edit-geocoded-section').append(jQuery('#map_canvas'));	
										
				//do the map stuff
				selectedGeocode = results[0];
	
				//set the model
				selectedPlace.properties.address = 
					getShortNameForType("street_number", selectedGeocode.address_components)+' '+
					getShortNameForType("route", selectedGeocode.address_components);
				
				selectedPlace.properties.city = 
					getShortNameForType("locality", selectedGeocode.address_components);
				
				selectedPlace.properties.province = 
					getShortNameForType("administrative_area_level_1", selectedGeocode.address_components);
		
				selectedPlace.properties.postcode = 
					getShortNameForType("postal_code", selectedGeocode.address_components);
				
				selectedPlace.properties.country = 
					getShortNameForType("country", selectedGeocode.address_components);
				
				selectedPlace.geometry.coordinates = [];
				selectedPlace.geometry.coordinates.push(selectedGeocode.geometry.location.lng());
				selectedPlace.geometry.coordinates.push(selectedGeocode.geometry.location.lat());
			
				var myOptions = {
				  zoom: 15,
				  mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				
				if(map==null){
					map = new google.maps.Map(document.getElementById("map_canvas"),
						myOptions);
				}
						
				deleteOverlays();
				map.setCenter(selectedGeocode.geometry.location);								
				addMarker(map,selectedGeocode.geometry.location);					
															
				setStatus('','message', false);		
					
				jQuery('.resetable', jQuery('#edit-geocoded-section')).show();					
				jQuery('#edit-geocoded-section').show();	
				
				getCategories(null, null);
										
						
			} else {
				setStatus('There was a problem geocoding with the specified location,'+
					' please make your search more specific and try again. status:'+
					status, 'update', false);
				jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'edit-place-address-section' }, backHandler);			
				jQuery('#edit-place-address-section').append(jQuery('#back-action'));	
				jQuery('#edit-place-address-section').append(jQuery('#cancel-finder-workflow'));
				jQuery('#edit-place-address-section').show();	
		  	} 
		});       		
	}	
	
	if(!error){ 
		jQuery('#'+phase).hide();	
	}
	
	return false;
}

function backHandler(event) {
	console.log('back phase:'+event.data.phase);
	setStatus('','message', false);
	var phase = event.data.phase;
	if(phase=='search-place-name-section'){			
		jQuery('#place-selector-form').hide();
		jQuery('#cancel-finder-workflow').hide();
		jQuery('#associate-place-section').append(jQuery('#next-action'));
			
		jQuery('#next-action' ).unbind('click').bind('click' , { phase: 'associate-place-section' }, nextHandler);
		
		jQuery('#associate-place-section').show();		
		
	} else if(phase=='search-place-address-section') {	
		var section = jQuery('#search-place-name-section');		
		section.append(jQuery('#back-action'));
		section.append(jQuery('#next-action'));
		
		jQuery('#next-action' ).unbind('click').bind('click' , { phase: 'search-place-name-section' }, nextHandler);
		jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'search-place-name-section' }, backHandler);
		
		jQuery('#search-geocoded-section').hide(); 		
		
		section.show();		
			
	} else if(phase=='search-geocoded-section') {     
		jqxhr.abort();
		jQuery('#welocally-post-error').html('');
		jQuery('#search-geocoded-section').hide(); 
		jQuery('#search-place-address-section').show(); 
		jQuery("#results").show();  	
		
    } else if (phase =='edit-place-address-section'){
    	var section = jQuery('#edit-place-name-section');
    	section.append(jQuery('#back-action'));
		section.append(jQuery('#next-action'));
		jQuery('#edit-geocoded-section').hide();
		jQuery('#categories-section').hide();
		jQuery('#edit-place-optional-section').hide();
				
		section.show();
    	
    	jQuery('#next-action' ).unbind('click').bind('click' , { phase: 'edit-place-name-section' }, nextHandler);
		jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'edit-place-name-section' }, backHandler);
	
    } else if (phase =='edit-geocoded-section' || phase=='edit-place-optional-section' ){
    	
    	var section = jQuery('#edit-place-address-section');
    	
    	section.append(jQuery('#back-action'));
		section.append(jQuery('#next-action'));
		
		jQuery('#edit-geocoded-section').hide();		
		jQuery('#save-place-action').hide();
		jQuery('#categories-section').hide();
		
		//reset cat selector type
		selectedClassifierLevel='';
		selectedPlace.classifiers[0].type='';
		selectedPlace.classifiers[0].category='';
		selectedPlace.classifiers[0].subcategory='';
		
		jQuery('#edit-place-categories-selected-list').html('');
		
		jQuery('#edit-place-optional-section').hide();		
			
		section.show();
    	
    	jQuery('#next-action' ).unbind('click').bind('click' , { phase: 'edit-place-address-section' }, nextHandler);
		jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'edit-place-address-section' }, backHandler);
	
    } 
    

    
	jQuery('#'+phase).hide();	
	return false;
	
}

jQuery(document).ready(function(jQuery) {
	
	WELOCALLY.env.initJQuery();
	
	//all sections hidden
    jQuery('.resetable').hide();

	activityType = 'search';
		
	geocoder = new google.maps.Geocoder();
		 
	var selectedPlaceObject = null;
	
	//init buttons
	jQuery( 'a, input:submit, button','.action' ).button();
	jQuery( '#next-action,#cancel-finder-workflow,#btn-new-select,#back-action,#save-place-action' ).button();
	
	//resets all and starts over
    jQuery('#cancel-finder-workflow,#btn-new-select' ).bind('click' , { phase: 'associate-place-section' }, cancelHandler);
	
	
	jQuery('#next-action' ).bind('click' , { phase: 'associate-place-section' }, nextHandler);
		
	jQuery('#welocally_default_search_radius').val('8');
	
	//startup phase state
	jQuery('#associate-place-section').append(jQuery('#associate-input'));
	jQuery('#associate-place-section').append(jQuery('#next-action'));
		

    jQuery( '#add-place-action' ).click(function() {

		//put the fields
		//put the name search in the top
		jQuery('#edit-place-name-section').append(jQuery('#place-name-title'));
		jQuery('#edit-place-name-section').append(jQuery('#place-name-input'));
		jQuery('#edit-place-name-section').append(jQuery('#place-name-saved'));
		jQuery('#place-name-saved').hide();
		jQuery('#place-name-input').show();
		jQuery('#place-name-title').html('Place Name: Enter the <strong>full name</strong> of the place you want to add.');
        
    	jQuery('#welocally-post-error').removeClass('welocally-error error welocally-update updated fade');
		jQuery('#welocally-post-error').html('');
		jQuery('#results').hide();
    	jQuery('#place-selector').hide();
    	jQuery('#edit-place-form').show();
    	jQuery('#map_canvas').height( 401 );
    	jQuery('#map_canvas').width( '100%' );
    		    	
        return false;
    });
    
            
    //saves a new place
    jQuery( '#save-place-action' ).click(function() {   
    	
    	setStatus('Saving Place...', 'message', true);
    	
    	var options = {
			action: 'save_place'
		};
    
    	var missingRequired = false;
    	var fields = '';
	
		if(jQuery('#edit-place-phone').val() != null){
			selectedPlace.properties.phone=jQuery('#edit-place-phone').val();
		}
		
		if(jQuery('#edit-place-web').val() != null){
			selectedPlace.properties.website=jQuery('#edit-place-web').val();
		}
          
        //this will get changed to PUT by the ajax admin function     
        options.place = selectedPlace;
           
		jqxhr = jQuery.ajax({
		  type: 'POST',		  
		  url: ajaxurl,
		  data: options,
		  dataType : 'json',
		  beforeSend: function(jqXHR){
	        jqxhr = jqXHR;
	      },
		  error : function(jqXHR, textStatus, errorThrown) {
			if(textStatus != 'abort'){
	  			setStatus('ERROR : '+textStatus, 'error', false);
	  		}	else {
	  			console.log(textStatus);
	  		}		
		  },		  
		  success : function(data, textStatus, jqXHR) {
		  	jQuery('#welocally-post-error').html('');
			if(data != null && data.errors != null) {
				buildErrorMessages(data.errors);		
			} else {
				selectedPlace._id=data.id;
				setStatus('Your new place has been added!', 'message', false);
				setSelectedPlaceInfo(selectedPlace, WELOCALLY.meta.post);
			}
		  }
		});
        
        
        return false;
    });
 
    
    jQuery( "#selectable" ).selectable({
		   selected: function(event, ui) {
				selectedFeatureIndex = jQuery("#scroller-places li").index(ui.selected);	
				setSelectedPlaceInfo(jsonObjFeatures[selectedFeatureIndex], WELOCALLY.meta.post);				
		   }
	});
	
	jQuery( "#selectable-cat" ).selectable({
		   selected: function(event, ui) {
			   	if(selectedCategories.indexOf(ui.selected.innerHTML) == -1) {
			   		selectedCategories = selectedCategories + ui.selected.innerHTML+",";
			   	}
		   		jQuery( "#place-categories-selected" ).val(selectedCategories);		
		   },
		   unselected: function(event, ui) {
		   		if(selectedCategories.indexOf(ui.unselected.innerHTML) != -1) {
		   			var replaceText =  ui.unselected.innerHTML+",";
		   			selectedCategories = selectedCategories.replace(new RegExp(replaceText, 'g'),"");
		   			jQuery( "#place-categories-selected" ).val(selectedCategories);	
		   		}		
		   }
	});
	
	jQuery( "#edit-place-categories-selection-list" ).selectable({
		   selected: function(event, ui) {
		   		
		   		if(selectedClassifierLevel == 'Type'){
		   			selectedPlace.classifiers[0].type = ui.selected.innerHTML;
		   		} else if(selectedClassifierLevel == 'Category'){
		   			selectedPlace.classifiers[0].category = ui.selected.innerHTML;
		   		} else if(selectedClassifierLevel == 'Subcategory'){
		   			selectedPlace.classifiers[0].subcategory = ui.selected.innerHTML;
		   		}
		   		
		   		jQuery( "#edit-place-categories-selected-list")
		   			.append(
		   			'<li class="categories-selected-list-item">'+
		   			selectedClassifierLevel+':'+ui.selected.innerHTML+'</li>');
		   		
		   		if(selectedPlace.classifiers[0].type != '' &&
		   			selectedPlace.classifiers[0].category != '' &&
		   			selectedPlace.classifiers[0].subcategory != '') {
		   			
		   			//finished
		   			jQuery( '#edit-place-categories-selection').hide();
		   			jQuery( '#save-place-action').show();
		   			jQuery( '#edit-place-optional-section').show();
		   			jQuery('#add-place-actions-section').append(jQuery('#back-action' )); 
		   			jQuery('#back-action' ).unbind('click').bind('click' , { phase: 'edit-place-optional-section' }, backHandler);
		   			
		   			
		   			
		   		} else {
		   			var type = null;
		   			var category = null;
		   		 	if(selectedPlace.classifiers[0].type != '')
		   		 		type= WELOCALLY.util.unescape(selectedPlace.classifiers[0].type);
		   		 	if(selectedPlace.classifiers[0].category != '')
		   		 		category= WELOCALLY.util.unescape(selectedPlace.classifiers[0].category);	
		   		 		
		   			getCategories(type, category);
		   				
		   		}
			   	
		   },
		   unselected: function(event, ui) {
		   		
		   }
	});

	
	jQuery( '#add-new-place-action' ).click(function() {
		
		setStatus('', 'message', false);
		
		jQuery("#place-selector").hide();
		
		jQuery('#place-name-title').html('Place Name: Enter the common name of the place, ie. "Treehouse Coffee Shop"');		
				
		var section = jQuery('#edit-place-name-section');
		section.append(jQuery('#edit-place-name-title'));
		section.append(jQuery('#place-name-input'));
		section.append(jQuery('#place-name-saved'));
		section.append(jQuery('#next-action'));
		section.show();
		
		jQuery('#next-action' ).unbind('click').bind('click' , { phase: 'edit-place-name-section' }, nextHandler);
		jQuery('#back-action' ).unbind('click');
				
		jQuery("#edit-place-form").show();
				
		return false; 
		
	});
	
	jQuery( "#selectable-cat" ).selectable({
		   selected: function(event, ui) {
			   	if(selectedCategories.indexOf(ui.selected.innerHTML) == -1) {
			   		selectedCategories = selectedCategories + ui.selected.innerHTML+",";
			   	}
		   		jQuery( "#place-categories-selected" ).val(selectedCategories);		
		   },
		   unselected: function(event, ui) {
		   		if(selectedCategories.indexOf(ui.unselected.innerHTML) != -1) {
		   			var replaceText =  ui.unselected.innerHTML+",";
		   			selectedCategories = selectedCategories.replace(new RegExp(replaceText, 'g'),"");
		   			jQuery( "#place-categories-selected" ).val(selectedCategories);	
		   		}		
		   }
	});


});
</script>
<style type="text/css">
	
	#place-intro { margin-top: 5px; margin-bottom: 5px; }
	
	#place-selector-form { 
		border-color:#dfdfdf;
		background-color:#F9F9F9;
		border-width:1px;
		border-style:solid;
		-moz-border-radius:3px;
		-khtml-border-radius:3px;
		-webkit-border-radius:3px;
		border-radius:3px;
		margin: 0;
		width:95%;
		border-style:solid;
		border-spacing:0;
		padding: 10px;
		
	 }
	 
	.welocally-error { 
		border-color:#996666;
		background-color:#F9AAAA;
		border-width:1px;
		border-style:solid;
		-moz-border-radius:3px;
		-khtml-border-radius:3px;
		-webkit-border-radius:3px;
		border-radius:3px;
		margin: 0;
		width:95%;
		border-style:solid;
		border-spacing:0;
		padding: 10px;
		margin-bottom: 5px;
		color:#996666;
		display:none;
	 } 
	 
	 .error-txt { 
		color:#850F00;
		font-weight:bold;
		font-size:1.0em;
	 } 
	 
	 
	.verified-geocode { 
		border-color:#245E07;
		background-color:#B7ED9D;
		border-width:2px;
		border-style:solid;
		-moz-border-radius:3px;
		-khtml-border-radius:3px;
		-webkit-border-radius:3px;
		border-radius:3px;
		margin: 0px;
		border-style:solid;
		border-spacing:0;
		padding: 0px;
		margin-bottom: 5px;
		color:#245E07;
		display:inline-block;
	 }  
	 
	 .error-geocode { 
		border-color:#8A0E0E;
		background-color:#E68C8C;
		border-width:2px;
		border-style:solid;
		-moz-border-radius:3px;
		-khtml-border-radius:3px;
		-webkit-border-radius:3px;
		border-radius:3px;
		margin: 0px;
		border-style:solid;
		border-spacing:0;
		padding: 0px;
		margin-bottom: 0px;
		color:#8A0E0E;
		display:inline-block;
	}  
	
    #add-span { }
    #add-place-action { }
    #selection { margin-bottom: 10px; }
    #add-form-div { margin-top: 5px; display:none; }
   
       
    /* ------ titles */   
    .meta-title2 {
    	border-bottom:1px solid #cccccc; padding-bottom:3px;
    	margin-bottom: 10px;
		font-weight:bold;
		font-size:1.2em;
		text-transform:uppercase;
	}
	
	.field-title{ 
		font-size:1.2em;
		margin-top: 10px;
		color: #808080;
	 }
	
	/* ------ selection form */
	#place-selector { margin-bottom: 10px;}
	#scroller-places { height: 240px; width: 100%; overflow-y: scroll;}
	.search-field { width: 100%; margin-bottom: 10px; }
    #results { margin-top: 5px; display:none; }
	#selectable .ui-selecting { background: #AAAAAA; color: black; }
	#selectable .ui-selected { background: #444444; color: white; }
	#selectable { list-style-type: none; margin: 0; padding: 0; }
	#selectable li { margin: 3px 3px 3px 0; padding: 0.2em; cursor: pointer; }	
	#categories-choice {  margin-bottom: 10px; display:none; }	
		
		
	/* ------ selected place */
	#selected-place { margin-bottom: 10px; width: 100%; display:none; }
	#btn-new-select { margin-bottom: 10px; margin-top: 10px; }
	#selected-place-categories { margin-bottom: 10px; }
    #selected-place-info { margin-bottom: 10px; }
    #selectable-cat .ui-selecting { background: #AAAAAA; color: black; }
	#selectable-cat .ui-selected { background: #444444; color: white; }
	#selectable-cat { list-style-type: none; margin: 0; padding: 0; }
	#selectable-cat li { margin: 3px 3px 3px 0; padding: 0.2em; cursor: pointer; }
	#show_place_info { margin-bottom: 10px; }
	
	/* ------ add new place */
	#edit-place-form { margin-bottom: 10px; width: 100%; display:none; }
	.edit-field { width: 100%; margin-bottom: 10px; display:inline-block; font-size:1.2em; }
	.selected-field { width: 100%; height: 15px; margin-bottom: 5px; display:inline-block; font-size:1.4em; }
	
	.tag-field { width: 100%; height: 15px; margin-bottom: 5px; 
		display:inline-block; 
		font-weight:bold;color:#696969;
		text-align:left;font-family:courier new, courier, monospace;line-height:1;
		font-size:1.0em; }
	
	#edit-place-categories-selection-list .ui-selecting { background: #AAAAAA; color: black; }
	#edit-place-categories-selection-list .ui-selected { 
		background: #7A5207; color: white; 
	}
	#edit-place-categories-selection-list { list-style-type: none; margin: 0; padding: 0; }
	#edit-place-categories-selection-list li { 
		margin: 3px 3px 3px 0; padding: 0.2em; cursor: pointer; 
		border-color:#4E8727;
		background-color:#C4F2A5;
		border-width:1px;
		border-style:solid;
		-moz-border-radius:3px;
		-khtml-border-radius:3px;
		-webkit-border-radius:3px;
		border-radius:3px;
		border-style:solid;
		border-spacing:0;
		color:#4E8727;
		display:inline-block;
	}	
	
	.categories-selected-list-item {
	 	border-color:#737373;
		background-color:#505050;
		border-width:0px;
		border-style:solid;
		-moz-border-radius:3px;
		-khtml-border-radius:3px;
		-webkit-border-radius:3px;
		border-radius:3px;
		margin: 0px;
		border-style:solid;
		border-spacing:0;
		font-size:1.2em; 
		padding: 5px;
		margin-right: 5px;
		color:#FFFFFF;
		display:inline-block;
	 }
	 	
	.input-section{ width:100%;} 	
	
</style>
<body>
	<div style="display:none">
		
		<div id="associate-input" class="input-section action" style="display:inline-block" >
			<div style="display:inline-block" class="selected-field">Associate a place with this post?&nbsp;</div>			
		</div>
	
		<div id="place-name-input" class="input-section action" style="display:inline-block">
			<div id="place-name-title"  class="field-title">*Place Name: <em>Required</em></div>
			<input type="text" id="edit-place-name" name="edit-place-name" class="edit-field">
		</div>							

		<div id="street-name-input" class="input-section action" style="display:inline-block">			
			<div id="place-street-title"  class="field-title">*Full Address: <em>Required</em></div>
			<input type="text" id="edit-place-street" name="edit-place-street" class="edit-field">
		</div>
		

		<div class="resetable" id="map_canvas" style="width:100%; height:300px;"></div>
		<div class="resetable" id="results">
				<div id="scroller-places">
					<ol id="selectable">
					</ol>	
				</div>
				<div id="add-place-section" class="resetable action" style="margin-top:10px;" >
					<div class="selected-field"><em>Can't find the place you are looking for?</em></div>
					<button id="add-new-place-action" href="#">Add Place</button>
				</div>
		</div>	
	
		<button id="next-action" href="#">Next</button>
		<button id="back-action" href="#">Back</button>
	</div>
	<div class="container">
		<div class="span-24">	
			<div id="welocally-post-error" style="display:none">No Errors...</div>				
				<div>					
					<div id="associate-place-section"></div>
				</div>
				<input type="hidden" id="place-selected" name="PlaceSelected">
				<input type="hidden" id="place-categories-selected" name="PlaceCategoriesSelected">
						
				<div id="place-selector-form" style="display:none">
				 <div id="all_place_info">
					<!-- start place selector -->
					<div id="place-selector">
						<div class="meta-title2">Search Places</div>
						
							<div class="resetable" id="search-place-name-section"></div>
							
							<div class="resetable" id="search-place-address-section">
								<div id="edit-place-name-selected" class="selected-field">&nbsp;</div>
							</div>
							
							<div class="resetable" id="search-geocoded-section">
								<div id="search-geocoded-name-selected" class="selected-field">&nbsp;</div>
								<div id="search-geocoded-address-selected" class="selected-field">&nbsp;</div>
								<div id="places-tag-selected" style="display:none; margin-bottom:10px;">
									<div class="tag-line">
										<div><em>Place this tag in your edit area to link to post. Or use the Welocally TinyMCE button to insert.</em></div>				
										<div><input class="search-field post-place-tag-tagtext" type="text" id="share-meta-tagtext"></input></div>
									</div>						
								</div>
							</div>
																				
							<div class="resetable" id="place-find-range-section" style="display:none">
								<select id="welocally_default_search_radius" name="welocally_default_search_radius" >
									<option value="2">2 km</option>
									<option value="4">4 km</option>
									<option value="8">8 km</option>
									<option value="12">12 km</option>
									<option value="16">16 km</option>
									<option value="25">25 km</option>
									<option value="50">50 km</option>
								</select>&nbsp;<em>Distance in Km</em> 										
							</div>	
														
							<div class="resetable" id="place-find-query-section" style="display:none">						
								<div>*<em>What is the name of the place you are writing about or a simillar keyword...</em> REQUIRED</div> 
								<div><input type="text" id="place-search" class="class="search-field"" value="foo"></div>
							</div>
														
							<div class="resetable" id="place-find-actions" class="action" style="display:none">
								<button id="search-places-action">Find Places</button>			    					      
							</div>												
						</div>
							
					</div> 
					<!-- end place selector -->
					<!-- start place selector -->
					<div id="selected-place" class="resetable">
						<div class="meta-title2">Selected Place</div>
						<div id="selected-place-info"></div>
						<div id="categories-choice">
							<div class="meta-title2">Choose categories for post</div>
							<ol id="selectable-cat"></ol> 
						</div>
						<div class="action"><button id="btn-new-select" href="#">New Selection</button></div>
					</div> 			
					<!-- end place selector -->	
					
					<!-- add place form -->	
				    <div id="edit-place-form">
				    	<div id="place-form-title" class="meta-title2">Add New Place</div>
				    					    		    				    	
				    	<div class="resetable" id="edit-place-name-section"></div>
				        
				        <div class="resetable" id="edit-place-address-section" style="display:none"></div>
				        
				        <div class="resetable" id="edit-geocoded-section">
								<div id="edit-geocoded-name-selected" class="selected-field">&nbsp;</div>
								<div id="edit-geocoded-address-selected" class="selected-field">&nbsp;</div>
						</div>
				        
				        <div class="resetable" id="categories-section" style="display:none">
				        	<div style="margin-top:10px; height:20px;" class="field-title">*Category Info: <em>Required</em></div>
				        	<div id="edit-place-categories-selected"><ul id="edit-place-categories-selected-list"></ul></div>
				        	<div id="edit-place-categories-selection"><ul id="edit-place-categories-selection-list" style="display:inline-block; list-style:none;"></ul></div>
				        </div>

						<div class="resetable" id="edit-place-optional-section" style="display:none">
							<div style="margin-top:10px; margin-bottom:5px;"><em>These fields are optional but it is strongly reccomended that you include the phone number and website.</em></div>
							<div id="step-phone">
								Phone Number: (optional):</br>
								<input type="text" id="edit-place-phone" name="edit-place-phone" class="edit-field">
							</div>
							<div id="step-web">
								Website: (optional):</br>
								<input type="text" id="edit-place-web" name="edit-place-web" class="edit-field">
							</div>
				        </div>
				        
	        			<div class="resetable" id="add-place-actions-section" class="action" style="display:inline-block; margin-top:10px;">
							<button id="cancel-finder-workflow" href="#">Cancel</button>
							<button id="save-place-action" href="#" style="display:none">Save</button>
				        </div> 	   
				    </div> 
				    <!-- end add place form -->	 
				   
				</div>	 <!-- end add place form -->
						

		</div>
	</div>