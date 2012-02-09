<script>

var isWLPlace = <?php echo $isWLPlace  ?>;
var jsonObjFeatures = []; //declare features array
var markersArray = [];
var selectedFeatureIndex = 0;
var selectedClassifierLevel='';
var selectedCategories='';
var map;
var selectedGeocode;
var selectedPlace = {
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

var activityType;

var jqxhr;

function setStatus(message, iserror, showloading){
	jQuery('#welocally-post-error').html('');
	if(!iserror){
		jQuery('#welocally-post-error').removeClass('welocally-error');
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
	setStatus('Loading Places...', false, true);
	
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
	  error : function(jqXHR, textStatus, errorThrown) {
	  		
	  		if(textStatus != 'abort'){
	  			console.error(textStatus);
	  			jQuery('#welocally-post-error').html('ERROR : '+textStatus);
				jQuery('#welocally-post-error').addClass('welocally-error error fade');
				jQuery('#welocally-post-error').show();
	  		}	else {
	  			console.log(textStatus);
	  		}		
	  },
	  success : function(data, textStatus, jqXHR) {

		    
		    setStatus('', false, false);
		    
		    //jQuery('#welocally-post-error').hide();
	  		//jQuery('#welocally-post-error').html('');
	  		
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
				//jQuery('#search-geocoded-section').append(jQuery('#results'));
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
	jQuery("#placeForm").hide();
	jQuery("#edit-place-form").hide();	
	
	//set the form value
	//this is wehere we should build the tag
	jQuery("#place-selected").val(JSON.stringify(selectedItem));
	
	//jQuery('#edit-place-name-selected')
	
	//show the *selected* area
	jQuery("#selected-place-info").html('');
	jQuery("#selected-place-info").append(jQuery('#search-geocoded-name-selected'));
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
	
	setStatus('Loading Categories...',false, true);
	
	 var options = {
		action: 'get_classifiers_types',
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
					console.error(textStatus);
					jQuery('#welocally-post-error').html('ERROR : '+textStatus);
					jQuery('#welocally-post-error').addClass('welocally-error error fade');
		  },		  
		  success : function(data, textStatus, jqXHR) {
		  	
		  	setStatus('', false, false);		  	
		  	
		  	
		  	jQuery('#edit-place-categories-selection-list').html('');
		  	
		  	if(base != null && data.length==0){
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
				jQuery('#categories-section').show();
				jQuery('#edit-place-categories-selection').show();		
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

jQuery(document).ready(function(jQuery) {

	activityType = 'search';
	
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

	// register buttons
	jQuery( "a, input:submit, button",".action" ).button();
	jQuery( "#start-search-action" ).button();
	jQuery( "#add-new-place-action" ).button();
	jQuery( "#next-search-action" ).button();
	jQuery( "#back-search-action" ).button();
	jQuery( "#btn-new-select" ).button();
	jQuery( "#back-addplace-action" ).button();
	jQuery( "#next-addplace-action" ).button();
	jQuery( "#cancel-addplace-action" ).button();
	jQuery( "#save-place-action" ).button();

	if(isWLPlace){
		jQuery( "#delete-place-section" ).show();
	}

    jQuery( "#cancel-add-link" ).click(function() {
        jQuery("#edit-place-form").hide();
        if(selectedPlaceObject == null) {
        	jQuery("#place-selector").show();
        } else {
        	jQuery("#place-selector").show();
        }
        return false;
    });

    //saves a new place
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
		   			savePlaceFormShow();
		   			
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
		
});

function showSearchStep(step){
	if (step != 4){
		jQuery('.step').hide();
		jQuery('#next-search-action').show();
	}
	jQuery('#welocally-post-error').html('');
	jQuery('#welocally-post-error').removeClass('error');
	jQuery('#welocally-post-error').hide('');
	if (step != 0){
		var next_step = step + 1;
		var back_step = step -1;
		jQuery('#next-search-action').attr('OnClick','showSearchStep('+next_step+')');
		jQuery('#back-search-action').attr('OnClick','showSearchStep('+back_step+')');
	}
	switch (step){
	 case 0: 
		 	jQuery('#placeForm').hide();
		 	jQuery('.step-'+step).show('slow');
	 break;
	 case 1: 
		 	jQuery('#placeForm').show();
		 	jQuery('.step-'+step).show('slow');
	 break;
	 case 2:
		 selectedPlace.properties.name = jQuery("#edit-place-name").val();
		 jQuery('#edit-place-name-selected').html(selectedPlace.properties.name);
		 jQuery('.step-'+step).show('slow');
	 break;
	 case 3:
		 var address = jQuery('#edit-place-street').val();
		 setStatus('Geocoding...',false, true);

		 geocoder.geocode( { 'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK &&  validGeocodeForSearch(results[0])) {
					jQuery('#search-geocoded-name-selected').html(selectedPlace.properties.name);
					jQuery('#search-geocoded-address-selected').html(results[0].formatted_address);	

					jQuery("#search-geocoded-section").append(jQuery('#search-geocoded-name-selected'));
					jQuery("#search-geocoded-section").append(jQuery('#search-geocoded-address-selected'));								
					
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
					addMarker(selectedGeocode.geometry.location);					
					jQuery("#search-geocoded-section").append(jQuery('#map_canvas'));											
					jQuery('#search-geocoded-section').find('#search-geocoded-section-error').empty();
					jQuery("#search-geocoded-section").find('#map_canvas').show();
					jQuery('#next-search-action').show();
					setStatus('',false, false);	
			    }
				else{
					jQuery("#search-geocoded-section").find('#map_canvas').hide();
					jQuery('#search-geocoded-section').find('#search-geocoded-section-error').append("Geocode was not successful for the following reason: " + status);
					setStatus('',false, false);
					jQuery('#next-search-action').hide();	
				}
		 });
		 jQuery('.step-'+step).show('slow');
	 break;
	 case 4:
		 jQuery('#next-search-action').hide();
		 jQuery('#back-search-action').attr('OnClick','showSearchStep(2)');
		 //ajax call
		searchLocations(selectedGeocode.geometry.location, selectedPlace.properties.name, 30);
		jQuery('.step-'+step).show('slow');
	 break;
	}
}
function clearSearchSteps(){
	jQuery("#selected-place").hide();
	jQuery("#place-selector").show();
	showSearchStep(1);
}

function showAddPlaceStep(step){
	jQuery('#placeForm').hide();
	jQuery('#addPlaceForm').show();
	if (step != 4){
		jQuery('.addstep').hide();
		jQuery('#next-addplace-action').show();
	}
	if (step != 0){
		var next_step = step + 1;
		var back_step = step -1;
		jQuery('#next-addplace-action').attr('OnClick','showAddPlaceStep('+next_step+')');
		jQuery('#back-addplace-action').attr('OnClick','showAddPlaceStep('+back_step+')');
	}
	if (step != 1){
		jQuery('#back-addplace-action').show();
	}
	else{
		jQuery('#back-addplace-action').hide();
	}
	
	switch (step){
	 case 1: 
		 jQuery('#next-addplace-action').show();
	 break;

	 case 2: 
		 selectedPlace.properties.name = jQuery("#add-edit-place-name").val();
		 jQuery('#add-edit-place-name-selected').html(selectedPlace.properties.name);
	 break;
	 case 3: 
		 setStatus('Geocoding...',false, true);
			var address = jQuery('#add-edit-place-street').val();
		
			geocoder.geocode( { 'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK &&  validGeocodeForSearch(results[0])) {
					jQuery('#edit-geocoded-name-selected').html(selectedPlace.properties.name);
					jQuery('#edit-geocoded-address-selected').html(results[0].formatted_address);										
					
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
					addMarker(selectedGeocode.geometry.location);					

					jQuery('#edit-geocoded-section').append(jQuery('#map_canvas'));	
					jQuery('#edit-geocoded-section').find('#edit-geocoded-section-error').empty();
					jQuery("#edit-geocoded-section").find('#map_canvas').show();
					jQuery('#next-addplace-action').show();
																
					setStatus('',false, false);												
					//getCategories(null, null);
							
				} 
				else {
					jQuery("#edit-geocoded-section").find('#map_canvas').hide();
					jQuery('#edit-geocoded-section').find('#edit-geocoded-section-error').append("Geocode was not successful for the following reason: " + status);
					setStatus('',false, false);
					jQuery('#next-addplace-action').hide();
				}
			 }); 
	 break;
	 
	 case 4:
		 clearAddPlaceCategory();
		 getCategories(null, null);
		 jQuery('#back-addplace-action').attr('OnClick','showAddPlaceStep(2)');
		 jQuery('#next-addplace-action').hide();
	 break;
	 
	}
	 
	jQuery('.addstep-'+step).show('slow');
}

function cancelAddPlace(){
	jQuery('#addPlaceForm').hide();
	showSearchStep(3);
	showSearchStep(4);
	jQuery('#placeForm').show();
}

function clearAddPlaceCategory(){
	jQuery('#edit-place-categories-selected-list').empty();
	jQuery('#edit-place-optional-section').hide();
	if(selectedPlace.classifiers[0]){
		selectedPlace.classifiers[0].type = '';
		selectedPlace.classifiers[0].category = '';
		selectedPlace.classifiers[0].subcategory = '';
	}
}

function savePlaceFormShow(){
	jQuery( '#edit-place-categories-selection').hide();
	jQuery( '#save-place-action').show();
	jQuery( '#edit-place-optional-section').show();
	jQuery( '#save-place-action' ).attr('OnClick','savePlaceAction()');
}

function savePlaceAction(){
	
	setStatus('Saving Place...', false, true);

	var options = {
			action: 'save_place',
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
				console.error(textStatus);
				jQuery('#welocally-post-error').html('ERROR : '+textStatus);
				jQuery('#welocally-post-error').addClass('welocally-error error fade');
	  },		  
	  success : function(data, textStatus, jqXHR) {
	  	jQuery('#welocally-post-error').html('');
		if(data != null && data.errors != null) {
			buildErrorMessages(data.errors);		
		} else {
			setSelectedPlaceInfo(selectedPlace);
		}
	  }
	});
	
    return false;
}
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
	.edit-field { width: 680px; margin-bottom: 10px; display:inline-block; font-size:1.2em; }
	.selected-field { width: 680px; height: 15px; margin-bottom: 5px; display:inline-block; font-size:1.4em; }
	
	
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
	 	
</style>
<body>
	<div class="container">
		<div class="span-24">	
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
				<div style="display:none">
					<div id="search-geocoded-name-selected" class="selected-field">&nbsp;</div>
					<div id="search-geocoded-address-selected" class="selected-field">&nbsp;</div>
					<div id="map_canvas" style="width:100%; height:300px;"></div>
					<div id="map_canvas_add" style="width:100%; height:300px;"></div>
				</div>
			</div>
			<input type="hidden" id="place-selected" name="PlaceSelected">
			<input type="hidden" id="place-categories-selected" name="PlaceCategoriesSelected">
			<div class="wrap-search-steps">
				<div class="step step-0">
					<div id="associate-input" style="display:inline-block" >
						<div style="display:inline-block" class="selected-field">Associate a place with this post?&nbsp;</div>
						<span id="start-search-action" onClick="showSearchStep(1)" href="#">Next</span>			
					</div>
				</div>
				<div id="placeForm" style="display:none">
				 <div id="all_place_info">
					<!-- start place selector -->
					<div id="place-selector">
						<div class="meta-title2">Search Places</div>
						<div class="step step-1" style="display:none">
							<div id="associate-place-section">
								<div id="place-name-input" class="action" style="display:inline-block">
									<div id="place-name-title"  class="field-title">Search Term: Enter a search term such as the place name or the category ie. "pizza"</div>
									<input type="text" id="edit-place-name" name="edit-place-name" class="edit-field" value="FTW Group">
								</div>
							</div>
						</div>
						<div class="step step-2" style="display:none">
							<div id="search-place-address-section">
								<div id="edit-place-name-selected" class="selected-field">&nbsp;</div>
								<div id="street-name-input" class="action" style="display:inline-block">			
									<div id="place-street-title"  class="field-title">Location: Enter full street address where the place is as one line ie. 1714 Franklin Street, Oakland, CA 94612</div>
									<input type="text" id="edit-place-street" name="edit-place-street" class="edit-field" value="1305 Franlkin Street, Oakland CA 94612">
								</div>
							</div>
						</div>
						<div class="step step-3" style="display:none">
							<div id="search-geocoded-section">
							<div id="search-geocoded-section-error"></div>
							</div>
						</div>
						<div class="step step-4">
							<div id="results">
								<div id="scroller-places">
									<ol id="selectable">
									</ol>	
								</div>
								<div class="action" style="margin-top:10px;">
									<div class="selected-field"><em>Can't find the place you are looking for?</em></div>
									<span id="add-new-place-action" OnClick="showAddPlaceStep(1)">Add Place</span>
								</div>
							</div>	
						</div>
					</div>
					</div>
					<div id="welocally-post-error" class="welocally-error">No Errors...</div>
					<span id="back-search-action" onClick="showSearchStep(0)">Back</span>
					<span id="next-search-action" onClick="showSearchStep(2)" href="#">Next</span>
				 </div>
				 <div id="addPlaceForm" style="display:none">
				 	<div id="place-selector">
					 	<div class="meta-title2">Add New Place</div>
					 	<div class="addstep addstep-1" style="display:none">
					 		<div id="add-place-name-input" class="action" style="display:inline-block">
								<div id="add-place-name-title"  class="field-title">Place Name: Enter the common name of the place, ie. "Treehouse Coffee Shop"</div>
								<input type="text" id="add-edit-place-name" name="add-edit-place-name" class="edit-field" value="FTW Group">
							</div>
					 	</div>
					 	<div class="addstep addstep-2" style="display:none">
					 		<div id="edit-place-address-section">
						 		<div id="add-edit-place-name-selected" class="selected-field">&nbsp;</div>
								<div id="place-street-title"  class="field-title">Location: Enter full street address where the place is as one line ie. 1714 Franklin Street, Oakland, CA 94612</div>
								<input type="text" id="add-edit-place-street" name="add-edit-place-street" class="edit-field" value="1305 Franlkin Street, Oakland CA 94612">
							</div>
					 	</div>
					 	<div class="addstep addstep-3" style="display:none">
					 		<div id="edit-geocoded-name-selected" class="selected-field"></div>
					 		<div id="edit-geocoded-address-selected" class="selected-field"></div>
					 		<div id="edit-geocoded-section">
					 			<div id="edit-geocoded-section-error"></div>
					 		</div>
					 	</div>
					 	<div class="addstep addstep-4" style="display:none">
					 		 <div id="categories-section" style="display:none">
				        		<div style="margin-top:10px; height:20px;" class="field-title">*Category Info: <em>Required</em></div>
				        		<div id="edit-place-categories-selected"><ul id="edit-place-categories-selected-list"></ul></div>
				        		<div id="edit-place-categories-selection"><ul id="edit-place-categories-selection-list" style="display:inline-block; list-style:none;"></ul></div>
				        	</div>
				        	<div id="edit-place-optional-section" style="display:none">
								<div style="margin-top:10px;"><em>Although these fields are optional is is strongly reccomended that you include the phone number and website of the place if you can find it.</em></div>
								<div id="step-phone">
									Phone Number: (optional):</br>
									<input type="text" id="edit-place-phone" name="edit-place-phone" class="edit-field" value="(415) 484-3593">
								</div>
								<div id="step-web">
									Website: (optional):</br>
									<input type="text" id="edit-place-web" name="edit-place-web" class="edit-field" value="http://ftwgroup.com">
								</div>
								
								<div class="action" style="display:inline-block; margin-top:10px;">
									<span id="save-place-action" onClick="" style="display:none">Save</span>
					        	</div>
							</div>	
				        </div>
					 </div>
					 	<div id="welocally-post-error" class="welocally-error">No Errors...</div>
					 	<span id="back-addplace-action" onClick="" style="display:none">Back</span>
						<span id="next-addplace-action" onClick="showAddPlaceStep(2)" href="#">Next</span>
						<div style="margin-top: 40px;">
							<span id="cancel-addplace-action" onClick="cancelAddPlace()">Cancel</span>
						</div>
				 </div>
			 </div>
			 <div id="selected-place">
					<div class="meta-title2">Selected Place</div>
					<div id="selected-place-info"></div>
					<div id="categories-choice">
						<div class="meta-title2">Choose categories for post</div>
						<ol id="selectable-cat"></ol> 
					</div>
					<span id="btn-new-select" onClick="clearSearchSteps()" href="#">New Selection</span>
			</div>
		</div>
	</div>