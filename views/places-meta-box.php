<script>

var isWLPlace = <?php echo $isWLPlace  ?>;
var jsonObjFeatures = []; //declare features array
var markersArray = [];
var selectedFeatureIndex = 0;
var selectedClassifierLevel='';
var selectedCategories='';
var map;
var geocoder;
var selectedGeocode;
var selectedPlace = {
	properties: {},
	type: "Place",
	classifiers: [],
	geometry: {
			type: "Point",
			coordinates: []
	}
};


function addMarker(location) {
  marker = new google.maps.Marker({
    position: location,
    map: map
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
	jQuery('#welocally-post-error').removeClass('welocally-error');
	jQuery('#welocally-post-error').show();
	jQuery('#welocally-post-error').html('<em>Loading Places...</em>');
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
	  error : function(jqXHR, textStatus, errorThrown) {
			console.error(textStatus);

			jQuery('#welocally-post-error').html('ERROR : '+textStatus);
			jQuery('#welocally-post-error').addClass('welocally-error error fade');
			jQuery('#welocally-post-error').show();
	  },
	  success : function(data, textStatus, jqXHR) {
		    jQuery('#welocally-post-error').removeClass('welocally-error');
		    jQuery('#welocally-post-error').hide();
	  		jQuery('#welocally-post-error').html('');
	  		if(data != null && data.length == 0) {
	  			jQuery('#welocally-post-error').append('<div class="welocally-context-help"></div>');
	  			jQuery('#welocally-post-error').append('Sorry no places were found that match your query, try again or add this as a new place.');
	  			jQuery('#welocally-post-error').addClass('welocally-update updated fade');
	  			
	  		} else if(data != null && data.length > 0) {
				jQuery.each(data, function(i,item){
					console.log(JSON.stringify(item));
					jsonObjFeatures.push(item);	    		
					jQuery('#selectable').append(buildListItemForPlace(item,i));
					
				});
				jQuery('#search-geocoded-section').append(jQuery('#results'));
				jQuery("#results").show();	
				
			} else if(data == null && data.errors != null) {
			
				buildErrorMessages(data.errors);	
					
			}
	  }
	});
    
    
}

function buildListItemForPlace(place,i) {
        var itemLabel = '<b>'+place.properties.name+'</b>';
        if (place.properties.address) {
            itemLabel += "<br>" + place.properties.address;
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


function setSelectedPlaceInfo(selectedItem) {
	
	//set form fields for save post
	selectedPlace = selectedItem;
	
	//hide the selection area
	jQuery("#place-selector").hide();	
	jQuery("#edit-place-form").hide();	
	
	
	//set the form value
	jQuery("#place-selected").val(JSON.stringify(selectedItem));
	
	//show the *selected* area
	var info = buildSelectedInfoForPlace(selectedItem); 
	jQuery("#selected-place-info").html(info);
	
	//build the categories
	buildCategorySelectionsForPlace(selectedItem, jQuery("#selectable-cat")); 
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

	var params = {};
	var base;
	var urlValue =  '/geodb/classifier/1_0/types.json';
	selectedClassifierLevel = 'Type';
	
	if(type != null && category == null){
		base = type;
		var urlValue =  '/geodb/classifier/1_0/categories.json';
		selectedClassifierLevel = 'Category';
		params.type = type;
		urlValue = urlValue+"?"+jQuery.param(params);
	} else if(type != null && category != null){
		base = category;
		var urlValue =  '/geodb/classifier/1_0/subcategories.json';
		selectedClassifierLevel = 'Subcategory';
		params.type = type;
		params.category = category;
		urlValue = urlValue+"?"+jQuery.param(params);
	}
	
	jQuery.ajax({
		  type: 'GET',
		  url : urlValue,
          contentType: 'application/json', // don't do this or request params won't get through
          dataType : 'json',
		  error : function(jqXHR, textStatus, errorThrown) {
					console.error(textStatus);
					jQuery('#welocally-post-error').html('ERROR : '+textStatus);
					jQuery('#welocally-post-error').addClass('welocally-error error fade');
		  },		  
		  success : function(data, textStatus, jqXHR) {
		  	
		  	jQuery('#welocally-post-error').html('');
		  	jQuery('#edit-place-categories-selection-list').html('');
		  	
		  	if(base != null && data.length==1){
		  		jQuery('#edit-place-categories-selection-list').append('<li style="display:inline-block;">'+base+'</li>');
		  	}
		  	
			if(data.errors != null) {
				buildErrorMessages(data.errors);		
			} else {
				currentCategories = data;
				jQuery.each(data, function(key, val) {
					if(val != null && val != '' ) {
						jQuery('#edit-place-categories-selection-list').append('<li style="display:inline-block;">'+val+'</li>');	
					}
				});				
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
	jQuery('#welocally-post-error').html('');
	jQuery('#welocally-post-error').append('<ul>');
	jQuery.each(errors, function(i,error){
		jQuery('#welocally-post-error').append('<li>error:'+error.errorMessage+'</li>');
		
	});
	jQuery('#welocally-post-error').append('</ul>');
	jQuery('#welocally-post-error').addClass('welocally-error error fade');	
	jQuery('#welocally-post-error').show();
}

function buildMissingFieldsErrorMessages(fields) {
	jQuery('#welocally-post-error').html('');
	jQuery('#welocally-post-error').append('<ul>');
	jQuery('#welocally-post-error').append('<li>error '+fields+' missing</li>');
	jQuery('#welocally-post-error').append('</ul>');
	jQuery('#welocally-post-error').addClass('welocally-error error fade');	
	jQuery('#welocally-post-error').show();	
}


function setSelectedPlaceInfoForEditForm(place) {

	jQuery("#edit-place-external-id").val(place._id);
	
	//set the form value
	jQuery("#edit-place-name").val(place.properties.name);
	jQuery("#edit-place-street").val(place.properties.address);
	jQuery("#edit-place-city").val(place.properties.city);
	jQuery("#edit-place-state").val(place.properties.province);
	jQuery("#edit-place-zip").val(place.properties.postalcode);
	jQuery("#edit-place-phone").val(place.properties.phone);
	jQuery("#edit-place-web").val(place.properties.website);
    var categories = "";
	for (classifier in place.classifiers) {
			index = category;
			if(category<place.classifiers.length) {
				categories = place.classifierss[classifier]+", "
			}
	}

	jQuery("#edit-place-cats").val(categories);
	
	
}

jQuery(document).ready(function(jQuery) {
	
	if (typeof(jQuery.fn.parseJSON) == "undefined" || typeof(jQuery.parseJSON) != "function") { 

	    //extensions, this is because prior to 1.4 there was no parse json function
		jQuery.extend({
			parseJSON: function( data ) {
				if ( typeof data !== "string" || !data ) {
					return null;
				}    
				data = jQuery.trim( data );    
				if ( /^[\],:{}\s]*$/.test(data.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@")
					.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]")
					.replace(/(?:^|:|,)(?:\s*\[)+/g, "")) ) {    
					return window.JSON && window.JSON.parse ?
						window.JSON.parse( data ) :
						(new Function("return " + data))();    
				} else {
					jQuery.error( "Invalid JSON: " + data );
				}
			}
		});
	}	
	
	
	geocoder = new google.maps.Geocoder();
	
	 
	var selectedPlaceObject = null;
	
	jQuery( "a, input:submit, button",".action" ).button();
	jQuery( "#next-action" ).button();
	jQuery( "#back-action" ).button();
	
	if(isWLPlace){
		jQuery( "#delete-place-section" ).show();
	}
		
		
	jQuery("#welocally_default_search_radius").val('8');
	
	//startup phase state
	jQuery('#associate-place-section').append(jQuery('#associate-input'));
	jQuery('#associate-place-section').append(jQuery('#next-action'));
		
	jQuery( "#search-places-action" ).click(function() {
		getLocationsByAddress(
			jQuery('#place-address').val(),
			jQuery('#place-search').val(),
			jQuery('#welocally_default_search_radius').val());
		return false;		
	});

	

    jQuery( "#add-place-action" ).click(function() {

		//put the fields
		//put the name search in the top
		jQuery('#edit-place-name-section').append(jQuery('#place-name-title'));
		jQuery('#edit-place-name-section').append(jQuery('#place-name-input'));
		jQuery('#edit-place-name-section').append(jQuery('#place-name-saved'));
		jQuery('#place-name-saved').hide();
		jQuery('#place-name-input').show();
		jQuery('#place-name-title').html('Place Name: Enter the <strong>full name</strong> of the place you want to add.');

        
    	jQuery('#welocally-post-error').removeClass('welocally-error welocally-update error updated fade');
		jQuery('#welocally-post-error').html('');
		jQuery("#results").hide();
    	jQuery("#place-selector").hide();
    	jQuery("#edit-place-form").show();
    	jQuery('#map_canvas').height( 401 );
    	jQuery('#map_canvas').width( '100%' );
    		    	
        return false;
    });
    
    jQuery( "#cancel-add-link" ).click(function() {
        jQuery("#edit-place-form").hide();
        if(selectedPlaceObject == null) {
        	jQuery("#place-selector").show();
        } else {
        	jQuery("#place-selector").show();
        }
        return false;
    });

    
    jQuery( "#btn-new-select" ).click(function() {  
    	jQuery("#selected-place").hide();
    	jQuery("#place-selector").show();    	     
        return false;
    });
    
    //saves a new place
    jQuery( "#save-place-action" ).click(function() {   
    
    	jQuery('#welocally-post-error').removeClass('welocally-error welocally-update error updated fade');
	    jQuery('#welocally-post-error').html('<em>Saving New Place...</em>');
    
    	var missingRequired = false;
    	var fields = '';
	
		if(jQuery('#edit-place-phone').val() != null){
			selectedPlace.properties.phone=jQuery('#edit-place-phone').val();
		}
		
		if(jQuery('#edit-place-web').val() != null){
			selectedPlace.properties.website=jQuery('#edit-place-web').val();
		}
        
		jQuery.ajax({
		  type: 'PUT',
		  url : '/geodb/place/1_0/',
          contentType: 'application/json', // don't do this or request params won't get through
          dataType : 'json',
          data: JSON.stringify(selectedPlace),
		  error : function(jqXHR, textStatus, errorThrown) {
					console.error(textStatus);
					jQuery('#welocally-post-error').html('ERROR : '+textStatus);
					jQuery('#welocally-post-error').addClass('welocally-error error fade');
		  },		  
		  success : function(data, textStatus, jqXHR) {
		  	jQuery('#welocally-post-error').html('');
			if(data.errors != null) {
				buildErrorMessages(data.errors);		
			} else {
				setSelectedPlaceInfo(data);
			}
		  }
		});
        
        
        return false;
    });
    
    jQuery("input[name='isWLPlace']").change(function(){
    	if (jQuery("input[name='isWLPlace']:checked").val() == 'true') { 
        	jQuery("#placeForm").show();
        	
        	//no place has been selected yet
        	if(selectedPlaceObject == null) {
        		jQuery("#place-selector").show();
        	}
        		
    	} else if (jQuery("input[name='isWLPlace']:checked").val() == 'false') { 
	        jQuery("#placeForm").hide();	
    	} 
    });   
    
    jQuery( "#selectable" ).selectable({
		   selected: function(event, ui) {
				selectedFeatureIndex = jQuery("#scroller-places li").index(ui.selected);
				setSelectedPlaceInfo(jsonObjFeatures[selectedFeatureIndex]);				
		   }
	});
	
	jQuery( "#selectable-cat" ).selectable({
		   selected: function(event, ui) {
			   	if(selectedCategories.indexOf(ui.selected.innerText) == -1) {
			   		selectedCategories = selectedCategories + ui.selected.innerText+",";
			   	}
		   		jQuery( "#place-categories-selected" ).val(selectedCategories);		
		   },
		   unselected: function(event, ui) {
		   		if(selectedCategories.indexOf(ui.unselected.innerText) != -1) {
		   			var replaceText =  ui.unselected.innerText+",";
		   			selectedCategories = selectedCategories.replace(new RegExp(replaceText, 'g'),"");
		   			jQuery( "#place-categories-selected" ).val(selectedCategories);	
		   		}		
		   }
	});
	
	jQuery( "#edit-place-categories-selection-list" ).selectable({
		   selected: function(event, ui) {
		   		
		   		if(selectedClassifierLevel == 'Type'){
		   			selectedPlace.classifiers[0].type = ui.selected.innerText;
		   		} else if(selectedClassifierLevel == 'Category'){
		   			selectedPlace.classifiers[0].category = ui.selected.innerText;
		   		} else if(selectedClassifierLevel == 'Subcategory'){
		   			selectedPlace.classifiers[0].subcategory = ui.selected.innerText;
		   		}
		   		
		   		jQuery( "#edit-place-categories-selected")
		   			.append(
		   			'<li class="categories-selected-list-item">'+
		   			selectedClassifierLevel+':'+ui.selected.innerText+'</li>');
		   		
		   		if(selectedPlace.classifiers[0].type != '' &&
		   			selectedPlace.classifiers[0].category != '' &&
		   			selectedPlace.classifiers[0].subcategory != '') {
		   			
		   			//finished
		   			jQuery( '#edit-place-categories-selection').hide();
		   			jQuery( '#save-place-action').show();
		   			jQuery( '#edit-place-optional-section').show();
		   			
		   		} else {
		   			var type = null;
		   			var category = null;
		   		 	if(selectedPlace.classifiers[0].type != '')
		   		 		type= selectedPlace.classifiers[0].type;
		   		 	if(selectedPlace.classifiers[0].category != '')
		   		 		category= selectedPlace.classifiers[0].category;	
		   		 		
		   			getCategories(type, category);
		   		}
			   	
		   },
		   unselected: function(event, ui) {
		   		
		   }
	});

	jQuery( '#edit-place' ).click(function() {
		jQuery('#welocally-post-error').removeClass('welocally-error welocally-update error updated fade');
		jQuery('#welocally-post-error').html('');
		jQuery('#place-form-title').html('edit place');
		jQuery('#save-place-action').html('Save Place');
	
		setSelectedPlaceInfoForEditForm(jsonObjFeatures[selectedFeatureIndex]);	
		
    	jQuery("#place-selector").hide();
    	jQuery("#edit-place-form").show();
		return false;		
	});
	
	jQuery( '#next-action' ).click(function() {
		var phase = this.parentElement.id;
		console.log("next-action phase:"+phase);
		if(phase=='associate-place-section'){
			jQuery('#search-place-name-section').append(jQuery('#edit-place-name-title'));
			jQuery('#search-place-name-section').append(jQuery('#edit-place-name-title'));
			jQuery('#search-place-name-section').append(jQuery('#place-name-input'));
			jQuery('#search-place-name-section').append(jQuery('#place-name-saved'));
			jQuery('#place-name-title').html('Search Term: Enter a search term such as the place name or the category ie. "pizza"');		
			jQuery('#search-place-name-section').append(jQuery('#back-action'));
			jQuery('#search-place-name-section').append(jQuery('#next-action'));
			jQuery('#search-place-name-section').show();
			jQuery('#placeForm').show();
								
		} else if(phase=='search-place-name-section'){
			
        	selectedPlace.properties.name = jQuery("#edit-place-name").val();
        	jQuery('#edit-place-name-selected').html(selectedPlace.properties.name);
        	
						
		    //put the address search in the top
			jQuery('#search-place-address-section').append(jQuery('#street-name-input'));
			jQuery('#place-street-title').html('Location: Choose the location or full address you would like to search from. ie. Oakland, CA');
			jQuery('#search-place-address-section').append(jQuery('#street-address-saved'));	
			
			jQuery('#search-place-address-section').append(jQuery('#back-action'));
			jQuery('#search-place-address-section').append(jQuery('#next-action'));			
			jQuery('#search-place-address-section').show();	
				
		} else if(phase=='search-place-address-section'){
			var address = jQuery('#edit-place-street').val();
		
			//determine mode from parent context
			var mode = this.parentElement.parentElement.id;
			
			geocoder.geocode( { 'address': address}, function(results, status) {
				console.log("geocode result");
				if (status == google.maps.GeocoderStatus.OK &&  validGeocodeForSearch(results[0])) {
					jQuery('#search-geocoded-name-selected').html(selectedPlace.properties.name);
					jQuery('#search-geocoded-address-selected').html(results[0].formatted_address);										
					jQuery('#search-geocoded-section').append(jQuery('#back-action'));
					jQuery('#search-geocoded-section').append(jQuery('#map_canvas'));	
											
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
					map = new google.maps.Map(document.getElementById("map_canvas"),
						myOptions);
							
					deleteOverlays();
					map.setCenter(selectedGeocode.geometry.location);								
					addMarker(selectedGeocode.geometry.location);					
																	
					jQuery('#search-geocoded-section').show();	
					
					//ajax call
					searchLocations(selectedGeocode.geometry.location, selectedPlace.properties.name, 30);
							
				} else {
					console.log("Geocode was not successful for the following reason: " + status);
			  	} 
			});
        		
		}
		jQuery('#'+phase).hide();		
		
		return false;
		
	});
	
	jQuery( '#back-action' ).click(function() {
		var phase = this.parentElement.id;
		console.log("back-action phase:"+phase);
		if(phase=='search-place-name-section'){			
			jQuery('#placeForm').hide();
			jQuery('#associate-place-section').append(jQuery('#next-action'));
			jQuery('#associate-place-section').show();
		} else if(phase=='search-place-address-section') {			
			jQuery('#search-place-name-section').append(jQuery('#back-action'));
			jQuery('#search-place-name-section').append(jQuery('#next-action'));
			jQuery('#search-place-name-section').show();
			jQuery('#search-place-name-section').show();
		} 
		jQuery('#'+phase).hide();	
		return false;
	});
	
	jQuery( '#save-place-name-action' ).click(function() {

		//search-place-name-section || edit-place-name-section
		var mode = this.parentElement.parentElement.id;
		console.log("save-place-name-action mode:"+mode);	

		//finde mode		
		if (!jQuery("#edit-place-name").val().match(/\S/)) {
            jQuery("#edit-place-name-title").removeClass(); 
            jQuery("#edit-place-name-title").addClass('error-txt')
            
        } else {
        	       	
        	jQuery('#place-name-input').hide();
        	selectedPlace.properties.name = jQuery("#edit-place-name").val();
        	jQuery('#edit-place-name-selected').html(selectedPlace.properties.name);
        	
        	jQuery('#place-name-saved').css("display","inline-block");
        	jQuery('#place-name-saved').show();
        	
        	jQuery('#edit-place-name-title').hide(); 


        	if(mode=='search-place-name-section'){
            	//put the address search in the top
    			jQuery('#search-place-address-section').append(jQuery('#street-name-input'));
    			jQuery('#place-street-title').html('Location: Choose the location or full address you would like to search from. ie. Oakland, CA');
    			jQuery('#search-place-address-section').append(jQuery('#map_canvas'));
    			jQuery('#search-place-address-section').append(jQuery('#street-address-saved'));

        	} else if(mode=='edit-place-name-section') {
        		//put the address search in the top
        		jQuery('#street-name-input').show(); 
				jQuery('#place-street-selected').hide();
				jQuery('#street-address-saved').hide(); 
        		       		
    			jQuery('#edit-place-address-section').append(jQuery('#street-name-input'));
    			jQuery('#place-street-title').html('Location: Please enter the <strong>full address</strong> for the place you would like to add. ie. 2069 Antioch Ct Oakland, CA 94611');
    			jQuery('#edit-place-address-section').append(jQuery('#map_canvas'));
    			jQuery('#edit-place-address-section').append(jQuery('#street-address-saved'));
    			jQuery('#edit-place-address-section').show();

    			//hide the map and geocoding fields 
    			jQuery('#map_canvas').hide();

    			
        	}      	        	

        }
		
		return false;		
	});
	
	jQuery( '#geocode-action' ).click(function() {
		console.log("geocode action");
		
		jQuery('#welocally-post-error').removeClass('welocally-error welocally-update error updated fade');
		jQuery('#welocally-post-error').html('');
		
		var missingRequired = false;
    	var fields = '';
		
		if (!jQuery("#edit-place-street").val().match(/\S/)) {
            missingRequired = true;
            fields = fields+' Street Address ';
        }
        
        if(missingRequired){
			buildMissingFieldsErrorMessages(fields);
			return false;
		}
		
		var address = jQuery('#edit-place-street').val();
		
		//determine mode from parent context
		var mode = this.parentElement.parentElement.id;
		
		geocoder.geocode( { 'address': address}, function(results, status) {
			console.log("geocode result");
			if (status == google.maps.GeocoderStatus.OK) {
				jQuery('#edit-place-street').val(results[0].formatted_address);
				verifyGeocode(results[0], mode);
						
			} else {
				console.log("Geocode was not successful for the following reason: " + status);
		  	} 
		});
		
		
		return false;		
	});
	
	jQuery( "#selectable-cat" ).selectable({
		   selected: function(event, ui) {
			   	if(selectedCategories.indexOf(ui.selected.innerText) == -1) {
			   		selectedCategories = selectedCategories + ui.selected.innerText+",";
			   	}
		   		jQuery( "#place-categories-selected" ).val(selectedCategories);		
		   },
		   unselected: function(event, ui) {
		   		if(selectedCategories.indexOf(ui.unselected.innerText) != -1) {
		   			var replaceText =  ui.unselected.innerText+",";
		   			selectedCategories = selectedCategories.replace(new RegExp(replaceText, 'g'),"");
		   			jQuery( "#place-categories-selected" ).val(selectedCategories);	
		   		}		
		   }
	});


});




</script>
<style type="text/css">
	
	#place-intro { margin-top: 5px; margin-bottom: 5px; }
	
	#placeForm { 
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
	
	/* ------- is place */
	#all_place_info { width: 100%; }	
	
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
	.edit-field { width: 680px; margin-bottom: 10px; display:inline-block; font-size:1.2em; }
	.selected-field { width: 680px; height: 15px; margin-bottom: 5px; display:inline-block; font-size:1.4em; }
	
	
	#edit-place-categories-selection-list .ui-selecting { background: #AAAAAA; color: black; }
	#edit-place-categories-selection-list .ui-selected { 
		background: #7A5207; color: white; 
	}
	#edit-place-categories-selection-list { list-style-type: none; margin: 0; padding: 0; }
	#edit-place-categories-selection-list li { 
		margin: 3px 3px 3px 0; padding: 0.2em; cursor: pointer; 
		border-color:#7A5207;
		background-color:#F5E6A6;
		border-width:1px;
		border-style:solid;
		-moz-border-radius:3px;
		-khtml-border-radius:3px;
		-webkit-border-radius:3px;
		border-radius:3px;
		border-style:solid;
		border-spacing:0;
		color:#7A5207;
		display:inline-block;
	}	
	
	.categories-selected-list-item {
	 	border-color:#737373;
		background-color:#DEDEDE;
		border-width:2px;
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
		color:#737373;
		display:inline-block;
	 }
	 	
</style>
<body>
	<div style="display:none">
		
		<div id="associate-input" class="action" style="display:inline-block" >
			<div style="display:inline-block" class="selected-field">Associate a place with this post?&nbsp;</div>			
		</div>
	
		<div id="place-name-input" class="action" style="display:inline-block">
			<div id="place-name-title">*Place Name: <em>Required</em></div>
			<input type="text" id="edit-place-name" name="edit-place-name" class="edit-field" value="Grinders Submarine Sandwiches">
			<button id="save-place-name-action" href="#" style="display:none">Save</button>
		</div>							

		<div id="street-name-input" class="action" style="display:inline-block">			
			<div id="place-street-title">*Full Address: <em>Required</em></div>
			<input type="text" id="edit-place-street" name="edit-place-street" class="edit-field" value="2069 Antioch Ct Oakland, CA 94611, USA">
			<button class="action" id="geocode-action" href="#" style="display:none">Geocode</button>				        	
		</div>
		

		<div id="map_canvas" style="width:100%; height:300px;"></div>
		<div id="results">
				<div id="scroller-places">
					<ol id="selectable">
					</ol>	
				</div>
		</div>	

		
		
		<button id="next-action" href="#">Next</button>
		<button id="back-action" href="#">Back</button>
	</div>
	<div class="container">
		<div class="span-24">	
			<div id="welocally-post-error" class="welocally-error">No Errors...</div>				
				<div>
					<div id="delete-place-section" style="display:none">
						<?php _e('Delete place info for this post?',$this->pluginDomain); ?>&nbsp;
						<label><input type='radio' name='deletePlaceInfo' value='true' />&nbsp;<b><?php _e('Yes', $this->pluginDomain); ?></b></label>
					</div>
					<div style="display:none">
						<?php _e('Associate a place with this post?',$this->pluginDomain); ?>&nbsp;
						<label><input type='radio' name='isWLPlace' value='true' <?php echo $isPlaceChecked; ?> />&nbsp;<b><?php _e('Yes', $this->pluginDomain); ?></b></label>
						<label><input type='radio' name='isWLPlace' value='false' <?php echo $isNotPlaceChecked; ?> />&nbsp;<b><?php _e('No', $this->pluginDomain); ?></b></label>
					</div>
					<div id="associate-place-section"></div>
					

					
				</div>
				<input type="hidden" id="place-selected" name="PlaceSelected">
				<input type="hidden" id="place-categories-selected" name="PlaceCategoriesSelected">
						
				<div id="placeForm" style="display:none">
				 <div id="all_place_info">
					<!-- start place selector -->
					<div id="place-selector">
						<div class="meta-title2">Search Places</div>
						
							<div id="search-place-name-section"></div>
							
							<div id="search-place-address-section">
								<div id="edit-place-name-selected" class="selected-field">&nbsp;</div>
							</div>
							
							<div id="search-geocoded-section">
								<div id="search-geocoded-name-selected" class="selected-field">&nbsp;</div>
								<div id="search-geocoded-address-selected" class="selected-field">&nbsp;</div>
							</div>
							
							
							
							<div id="place-find-range-section" style="display:none">
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
							
							
							<div id="place-find-query-section" style="display:none">						
								<div>*<em>What is the name of the place you are writing about or a simillar keyword...</em> REQUIRED</div> 
								<div><input type="text" id="place-search" class="search-field" value="foo"></div>
							</div>
							
							
							<div id="place-find-actions" class="action" style="display:none">
								<button id="search-places-action">Find Places</button>			    					      
							</div>
							
							
							
							
						</div>

							
					</div> 
					<!-- end place selector -->
					<!-- start place selector -->
					<div id="selected-place">
						<div class="meta-title2">Selected Place</div>
						<div id="selected-place-info"></div>
						<div id="categories-choice">
							<div class="meta-title2">Choose categories for post</div>
							<ol id="selectable-cat"></ol> 
						</div>
						<button id="btn-new-select" href="#">new selection</button>
					</div> 
					<!-- end place selector -->	
					<!-- add place form -->	
				    <div id="edit-place-form">
				    	<div id="place-form-title" class="meta-title2">Add New Place</div>
				    					    		    				    	
				    	<div id="edit-place-name-section"></div>
				        
				        <div id="edit-place-address-section" style="display:none"></div>
				        
				        <div id="categories-section" style="display:none">
				        	<div style="margin-top:15px; margin-bottom:-15px; height:20px;">*Category Info: <em>Required</em></div>
				        	<div id="edit-place-categories-selected"><ul id="edit-place-categories-selected-list"></ul></div>
				        	<div id="edit-place-categories-selection"><ul id="edit-place-categories-selection-list" style="display:inline-block; list-style:none;"></ul></div>
				        </div>

						<div id="edit-place-optional-section" style="display:none">
							<div style="margin-top:10px;"><em>Although these fields are optional is is strongly reccomended that you include the phone number and website of the place if you can find it.</em></div>
							<div id="step-phone">
								Phone Number: (optional):</br>
								<input type="text" id="edit-place-phone" name="edit-place-phone" class="edit-field" value="(510) 339-3721">
							</div>
							<div id="step-web">
								Website: (optional):</br>
								<input type="text" id="edit-place-web" name="edit-place-web" class="edit-field" value="http://grindersmontclair.com">
							</div>
				        </div>
				        
	        			<div class="action" style="display:inline-block; margin-top:10px;">
							<button id="cancel-add-link" href="#">Cancel</button>
							<button id="save-place-action" href="#" style="display:none">Add Place</button>
				        </div> 	   
				    </div> 
				   
				</div>	 <!-- end add place form -->		
						

		</div>
	</div>