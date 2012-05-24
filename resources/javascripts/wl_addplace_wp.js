/*
	copyright 2012 welocally. NO WARRANTIES PROVIDED
*/
function WELOCALLY_AddPlaceWidget (cfg) {		
	this.selectedSection;
	this.cfg;
	this.wrapper;
	this.statusArea;
	this.formArea;
	this.placeNameField;
	this.map_canvas;
	this.map;
	this.geocoder;
	this.locationMarker;
	this.selectedClassifiersArea;
	this.classifiersArea;
	this.jqxhr;
	
	this.init = function() {
							
		var error = this.initCfg(cfg);
		
		// Get current script object
		var script = jQuery('SCRIPT');
		script = script[script.length - 1];
		
		if(error){
			jQuery(script).parent().before('<div class="error">Error during configiration: '+error[0]+'</div>');
		} else {
			// Build Widget
			this.wrapper = this.makeWrapper();	
			jQuery(script).parent().before(this.wrapper);
		
		}
		
		return this;
					
	};
	
}

WELOCALLY_AddPlaceWidget.prototype.initCfg = function(cfg) {
	var errors = [];
	if (!cfg) {
		errors.push("Please provide configuration for the widget");
		cfg = {};
	}
	
	// summary (optional) - the summary of the article
	if (!cfg.id) {
		cfg.id = WELOCALLY.util.keyGenerator;
	}
	
	if (!cfg.endpoint) {
		cfg.endpoint = 'http://stage.welocally.com';
	}
	
	if (!cfg.imagePath) {
		cfg.imagePath = 'http://placehound.com/images/marker_all_base.png';
	}
	
	if (!cfg.zoom) {
		cfg.zoom = 16;
	}
	
	if (!cfg.location) {
		cfg.location = new google.maps.LatLng(
		38.548165, 
		-96.064453);
	}
	
	if (!cfg.showShare) {
		cfg.showShare = false;
	}
	
	if (!cfg.siteKey || !cfg.siteToken) {
		error = "Please include your site key and token in the configuration to add a places.";
	}
	
	this.cfg = cfg;
};

WELOCALLY_AddPlaceWidget.prototype.makeWrapper = function() {
	
	var _instance = this;
	
	// Build Widget
	var wrapper = jQuery('<div></div>');	
	jQuery(wrapper).attr('class','wl_addplace_widget');
	jQuery(wrapper).attr('id','wl_addplace_widget_'+_instance.cfg.id);
	
	this.statusArea = jQuery('<div></div>');
	jQuery(this.ajaxStatus).css('display','none');	
	jQuery(wrapper).append(this.statusArea);

	this.geocoder = new google.maps.Geocoder();
	
	//google maps does not like jquery instances
	this.map_canvas = document.createElement('DIV');
	jQuery(this.map_canvas).css('display','none');	
	jQuery(this.map_canvas).css('min-width','200px');
	jQuery(this.map_canvas).css('min-height','200px');
	jQuery(this.map_canvas).attr('class','welocally_addplace_widget_map_canvas');
	jQuery(this.map_canvas).attr("id","wl_addplace_map_canvas_widget");

	this.map = this.initMap(this.map_canvas);
	this.setLocation(this.cfg.location);
	
	//form
	this.formArea = this.createForm(this.map_canvas);
	jQuery(wrapper).append(this.formArea);
	
	this.wrapper = wrapper;

	return wrapper;
	
};


WELOCALLY_AddPlaceWidget.prototype.initMap = function(map_canvas) {
	
	var _instance = this;	
    
	var options = {
      center: _instance.cfg.location,
      zoom: _instance.cfg.zoom,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    if(_instance.cfg.styles){
		options.styles = _instance.cfg.styles;
	}
    
    var map = new google.maps.Map(
    	map_canvas, 
    	options);
       
    return map;
};

WELOCALLY_AddPlaceWidget.prototype.setStatus = function(statusArea, message, type, showloading){
		jQuery(statusArea).html('');
		jQuery(statusArea).removeClass();
		jQuery(statusArea).addClass(type);
		
		jQuery(statusArea).append(message);
		
		if(message != ''){
			jQuery(statusArea).show();
		} else {
			jQuery(statusArea).hide();
		}			
};

WELOCALLY_AddPlaceWidget.prototype.clearFields = function() {
	var _instance = this;
	jQuery('#wl_addplace_form').remove();
	this.formArea = this.createForm(this.map_canvas);
	jQuery(_instance.wrapper).append(this.formArea);
	_instance.setStatus(_instance.statusArea, '','message',true);
	jQuery(this.map_canvas).css('display','none');
};

WELOCALLY_AddPlaceWidget.prototype.createForm = function(map_canvas) {
	var _instance = this;
	var formArea = jQuery('<div id="wl_addplace_form"></div>');
	
	jQuery(formArea)
	.append('<div class="wl_field_description">Enter the real name of the place, such as "The Red Snapper Grill"</div>');
	
	//hidden fields
	jQuery(formArea).append('<input id="wl_addplace_id" type="hidden" name="id"/>');	
	jQuery(formArea).append('<input id="wl_addplace_street_number" type="hidden" name="streetNumber"/>');
	jQuery(formArea).append('<input id="wl_addplace_route" type="hidden" name="route"/>');
	jQuery(formArea).append('<input id="wl_addplace_locality" type="hidden" name="locality"/>');
	jQuery(formArea).append('<input id="wl_addplace_adminl1" type="hidden" name="adminl1"/>');
	jQuery(formArea).append('<input id="wl_addplace_postal_code" type="hidden" name="postalCode"/>');
	jQuery(formArea).append('<input id="wl_addplace_country" type="hidden" name="country"/>');
	jQuery(formArea).append('<input id="wl_addplace_location_val" type="hidden" name="location"/>');
	jQuery(formArea).append('<input id="wl_addplace_classifier_type" type="hidden" name="classifierType"/>');
	jQuery(formArea).append('<input id="wl_addplace_classifier_category" type="hidden" name="classifierCategory"/>');
	jQuery(formArea).append('<input id="wl_addplace_classifier_subcategory" type="hidden" name="classifierSubcategory"/>');
	
	
	this.placeNameField = jQuery('<div class="wl_field_area"><input id="wl_addplace_name_field" type="text" name="placeName" class="wl_widget_field wl_addplace_name"/></div>');
	jQuery(this.placeNameField).find('input').bind('change' , {form: formArea, _instance: this}, this.nameChangeHandler);  
	
	jQuery(formArea).append(this.placeNameField);
	
	var locFieldArea = jQuery('<div class="wl_addplace_loc_field_area" style="display:none;"></div>');
	jQuery(locFieldArea)
	.append('<div class="wl_field_description">Enter the most exact address of the place, such as 1234 Mullberry Rd Oakland CA 94612</div>');
	
	this.locationField = jQuery('<div class="wl_field_area"><input id="wl_addplace_location_field" type="text" name="placeLocation" class="wl_widget_field wl_addplace_location"/></div>');
	jQuery(_instance.locationField).find('input').bind('change' , {form: formArea, _instance: this}, this.locationChangeHandler);  
	
	jQuery(locFieldArea).append(this.locationField);
	
	
	//bind focus
	jQuery(this.placeNameField).keypress(function(e){
        if ( e.which == 13 ){
        	jQuery(_instance.placeNameField).find('input').trigger('change' , {form: formArea, _instance: _instance}, _instance.nameChangeHandler);       	
        	jQuery('#wl_addplace_location_field').focus();
        	return false;
        }
    });
	
	//bind focus
	jQuery(this.locationField).keypress(function(e){
        if ( e.which == 13 ){
        	jQuery(_instance.locationField).find('input').trigger('change' , {form: formArea, _instance: this}, this.locationChangeHandler);  
        	return false;
        }
    });
		
	jQuery(formArea).append(locFieldArea);
	
	jQuery(formArea).append(map_canvas);
	
	//classifiers
	this.selectedClassifiersArea = 
		jQuery('<div class="wl_addplace_selected_classifiers"><ul></ul></div>');
	jQuery(this.selectedClassifiersArea).css('display','none');
	jQuery(formArea).append(this.selectedClassifiersArea);
	
	this.classifiersArea = jQuery('<div class="wl_addplace_classifiers" style="margin-top:10px"><div class="wl_field_description">Click on the classifiers below to categorize the place. (Required)</div><ul id="wl_addplace_selectable"></ul></div>');
	jQuery(this.classifiersArea).find('#wl_addplace_selectable').selectable({ cancel: 'a' });
	jQuery(this.classifiersArea).css('display','none');
	    		
	jQuery(formArea).append(this.classifiersArea);
	
	_instance.addOptionalFieldsToForm(formArea);
	
	_instance.addSaveButtonToForm(formArea);
			
	return formArea;
};

WELOCALLY_AddPlaceWidget.prototype.addOptionalFieldsToForm = function(formArea, selectedPlace) {	
	var _instance = this;
	
	//phone all
	var optionalAll = jQuery('<div id="wl_addplace_optional" style="display:none"></div>');
	
	//phone
	jQuery(optionalAll)
	.append('<div class="wl_field_description">Enter the phone number with areacode and country code, such as +1 510 555-1234</div>');
		
	//phone
	var phoneField = jQuery('<div class="wl_field_area"><input id="wl_addplace_phone" type="text" name="phoneField" class="wl_widget_field wl_addplace_phone"/></div>');
	if(selectedPlace)
		jQuery(optionalAll).find('input').val(selectedPlace.properties.phone);	
	jQuery(phoneField).change(function(event, ui){			
		jQuery(optionalAll).find('.wl_addplace_web').focus();		
	});	
		
	jQuery(optionalAll).append(phoneField);	
	
	//web
	jQuery(optionalAll)
	.append('<div class="wl_field_description">Enter the website for the place if it exists. Include the http prefix, such as http://greatplace.com</div>');
		
	//website
	var webField = jQuery('<div class="wl_field_area"><input id="wl_addplace_web" type="text" name="webField" class="wl_widget_field wl_addplace_web"/></div>');
	if(selectedPlace)
		jQuery(webField).find('input').val(selectedPlace.properties.website);	
	jQuery(optionalAll).append(webField);
	
	jQuery(formArea).append(optionalAll);
	

};

WELOCALLY_AddPlaceWidget.prototype.addSaveButtonToForm = function(formArea, selectedPlace) {	
	//the save button
	var buttonDiv = jQuery('<div id="wl_addplace_buttons" style="display:none"></div>'); 		
	var addPlaceButton = jQuery('<a id="wl_addplace_button_add" href="#">add place</a>');
	jQuery(addPlaceButton).button();	
	jQuery(addPlaceButton).bind('click' , {form: formArea, _instance: this}, this.savePlaceFromFormHandler);  	
	jQuery(buttonDiv).append(addPlaceButton);
	
	//same as dialog close but whatever
	/*jQuery(buttonDiv).append('<a id="wl_addplace_cancel" class="actions wl_addplace_button" href="#">cancel</a>');	
	jQuery(buttonDiv).find('#wl_addplace_cancel').button().click(function(){ return false; });*/
	
	
	jQuery(formArea).append(buttonDiv);
};


WELOCALLY_AddPlaceWidget.prototype.savePlaceFromFormHandler = function(event,ui) {
	var _instance = event.data._instance;
	var formArea = event.data.form;
	var selectedPlace = selectedPlace = {
			properties: {
					classifiers: [
			  			{
			  				type: '',
			  				category: '',
			  				subcategory: ''
			  			}
				  	]
				},
				type: "Place",
				geometry: {
						type: "Point",
						coordinates: []
				}
			};
		
	//extract from form
	if(jQuery(_instance.formArea).find('#wl_addplace_id').val()){
		selectedPlace._id = jQuery(_instance.formArea).find('#wl_addplace_id').val();
	}
	
	selectedPlace.properties.name = jQuery(_instance.placeNameField).find('input').val();
	selectedPlace.properties.address =  
		jQuery(formArea).find('#wl_addplace_street_number').val()+' '+ 
		jQuery(formArea).find('#wl_addplace_route').val();	
	
	selectedPlace.properties.city = jQuery(formArea).find('#wl_addplace_locality').val();
	selectedPlace.properties.province = jQuery(formArea).find('#wl_addplace_adminl1').val();
	selectedPlace.properties.postcode = jQuery(formArea).find('#wl_addplace_postal_code').val();	
	selectedPlace.properties.country = jQuery(formArea).find('#wl_addplace_country').val();	
	selectedPlace.geometry.coordinates = jQuery(formArea).find('#wl_addplace_location_val').val().split('_');
	
	//phone wl_addplace_phone & website wl_addplace_web
	selectedPlace.properties.phone = jQuery(formArea).find('#wl_addplace_phone').val()
	selectedPlace.properties.website = jQuery(formArea).find('#wl_addplace_web').val()
	
	//classifier
	selectedPlace.properties.classifiers[0].type = jQuery(formArea).find('#wl_addplace_classifier_type').val();
	selectedPlace.properties.classifiers[0].category = jQuery(formArea).find('#wl_addplace_classifier_category').val();
	
	if(jQuery(formArea).find('#wl_addplace_classifier_subcategory').val())
		selectedPlace.properties.classifiers[0].subcategory = jQuery(formArea).find('#wl_addplace_classifier_subcategory').val();
	else
		selectedPlace.properties.classifiers[0].subcategory = jQuery(formArea).find('#wl_addplace_classifier_category').val();
	
	
	var errors = _instance.vaidatePlaceForSave(selectedPlace);
	
	if(errors.length==0){
		_instance.savePlace(selectedPlace);
	} else {
		//should make error message;
		_instance.setStatus(_instance.statusArea, 'Error saving place: '+errors[0],'wl_error',false);
	}
	
	//bound to button
	return false;
		
};

WELOCALLY_AddPlaceWidget.prototype.vaidatePlaceForSave = function(selectedPlace) {
	var errors = [];
	if(!selectedPlace.properties.name){
		errors.push("A place name is required.");
	}
	return errors;
};

WELOCALLY_AddPlaceWidget.prototype.selectedClassifierHandler = function(event,ui) {
	
	var _instance = event.data._instance;
	
	var selected = WELOCALLY.util.unescape(ui.selected.innerHTML);
	var selectedClassifierLevel = event.data.classifierType;	
	var selectedType = event.data.type;
	var selectedCategory = event.data.category;
	
	//which i could put this in the data but it doesn't look possible
	var classiferName = ui.selected.innerHTML;
		
	jQuery(_instance.classifiersArea).find('ul').empty();
		
	var classifier = '<li class="wl_addplace_categories_selected_list_item">'+
		selectedClassifierLevel+': '+classiferName+'</li>';
	
	jQuery(_instance.selectedClassifiersArea).find('ul')
		.append(classifier);
		
	//change this for formArea
	if(selectedClassifierLevel == 'Type'){
		selectedType = selected;
		jQuery(_instance.formArea).find('#wl_addplace_classifier_type').val(selected);		
		jQuery(_instance.formArea).find().val(selected)
		_instance.getCategories(selectedType,null);		
	} else if(selectedClassifierLevel == 'Category'){
		selectedCategory = selected;
		jQuery(_instance.formArea).find('#wl_addplace_classifier_category').val(selected);
		_instance.getCategories(selectedType,selectedCategory);		
	} else if(selectedClassifierLevel == 'Subcategory'){
		jQuery(_instance.formArea).find('#wl_addplace_classifier_subcategory').val(selected);
		jQuery(_instance.formArea).find('#wl_addplace_optional').show();
		jQuery(_instance.formArea).find('#wl_addplace_buttons').show();
	}
		
	jQuery(_instance.selectedClassifiersArea).show();
		
	return false;
};

WELOCALLY_AddPlaceWidget.prototype.setSelectedPlaceClassfiers = function(selectedPlace){
	var _instance = this;
	
	jQuery(_instance.selectedClassifiersArea).find('ul').empty();
	
	//form fields
	jQuery(_instance.formArea).find('#wl_addplace_classifier_type').val(
			selectedPlace.properties.classifiers[0].type);
	
	jQuery(_instance.formArea).find('#wl_addplace_classifier_category').val(
			selectedPlace.properties.classifiers[0].category);
	
	jQuery(_instance.formArea).find('#wl_addplace_classifier_subcategory').val(
			selectedPlace.properties.classifiers[0].subcategory);
	
	
	//display
	jQuery(_instance.selectedClassifiersArea).find('ul')
		.append('<li class="wl_addplace_categories_selected_list_item">'+
				'Type: '+selectedPlace.properties.classifiers[0].type+'</li>');
	
	jQuery(_instance.selectedClassifiersArea).find('ul')
	.append('<li class="wl_addplace_categories_selected_list_item">'+
			'Category: '+selectedPlace.properties.classifiers[0].category+'</li>');
	
	if (!selectedPlace.properties.classifiers[0].subcategory) { 
		selectedPlace.properties.classifiers[0].subcategory =
			selectedPlace.properties.classifiers[0].category;
	}
	jQuery(_instance.selectedClassifiersArea).find('ul')
	.append('<li class="wl_addplace_categories_selected_list_item">'+
			'Subcategory: '+selectedPlace.properties.classifiers[0].subcategory+'</li>');
		
	
};




WELOCALLY_AddPlaceWidget.prototype.nameChangeHandler = function(event, ui) {
	
	var _instance = event.data._instance;
	jQuery(event.data.form).find('.wl_addplace_loc_field_area').show();
	jQuery(event.data.form).find('.wl_addplace_location').focus();
	
	return false;
};

WELOCALLY_AddPlaceWidget.prototype.locationChangeHandler = function(event) {
	
	var addressValue = jQuery(this).val();
	
	var _instance = event.data._instance;
	var _field = this;
	
	_instance.setStatus(_instance.statusArea, 'Geocoding','wl_update',true);
	
	jQuery(_instance._selectedSection).hide();
	
	_instance.geocoder.geocode( { 'address': addressValue}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK &&  _instance.validGeocodeForSearch(results[0])) {
			
			jQuery(_field).val(results[0].formatted_address);
						
			//set the model, use map for lat lng location on save
			jQuery(_instance.formArea).find('#wl_addplace_street_number')
				.val(_instance.getShortNameForType("street_number", results[0].address_components));
			jQuery(_instance.formArea).find('#wl_addplace_route')
				.val(_instance.getShortNameForType("route", results[0].address_components));
			jQuery(_instance.formArea).find('#wl_addplace_locality')
				.val(_instance.getShortNameForType("locality", results[0].address_components));
			jQuery(_instance.formArea).find('#wl_addplace_adminl1')
				.val(_instance.getShortNameForType("administrative_area_level_1", results[0].address_components));
			jQuery(_instance.formArea).find('#wl_addplace_postal_code')
				.val(_instance.getShortNameForType("postal_code", results[0].address_components));
			jQuery(_instance.formArea).find('#wl_addplace_country')
				.val(_instance.getShortNameForType("country", results[0].address_components));
			
			///lnglat like the place model
			jQuery(_instance.formArea).find('#wl_addplace_location_val')
				.val(results[0].geometry.location.lng()+'_'+results[0].geometry.location.lat());
												
			if(results[0].address_components.length<=5){
				_instance.map.setZoom(14);
			} else {
				_instance.map.setZoom(16);
			}
			
			_instance.setStatus(_instance.statusArea, '','message',true);
			
			var location = new google.maps.LatLng(
				results[0].geometry.location.lat(), 
				results[0].geometry.location.lng());
			_instance.setLocation(location);
						
					
			jQuery(_instance.map_canvas).show();
			google.maps.event.trigger(_instance.map, 'resize');
			_instance.map.setCenter(location);	
			
			jQuery(_instance.formArea).find('#wl_addplace_optional').show();
			jQuery(_instance.formArea).find('#wl_addplace_buttons').show();
			
			
			
		} else {
			_instance.setStatus(_instance.statusArea, 'We need the full address, ie. 123 Mulberry Rd Oakland CA, to add it.','wl_warning',false);
		} 
	});
		
	return false;
};


WELOCALLY_AddPlaceWidget.prototype.savePlace = function (selectedPlace) {

	var _instance = this;
	
	_instance.setStatus(_instance.statusArea,'Saving Place...', 'wl_message', true);
	
	var data = {
			action: 'save_place',
			place: selectedPlace
	};
		   
	_instance.jqxhr = jQuery.ajax({
	  type: 'POST',		  
	  url: ajaxurl,
	  datatype: 'json',
	  data: data,
	  beforeSend: function(jqXHR){
		_instance.jqxhr = jqXHR;
	  },
	  error : function(jqXHR, textStatus, errorThrown) {
		if(textStatus != 'abort'){
			_instance.setStatus(_instance.statusArea,'ERROR : '+textStatus, 'error', false);
		}		
	  },		  
	  success : function(data, textStatus, jqXHR, dataType) {
		if(data != null && data.errors != null) {
			_instance.setStatus(_instance.statusArea,'ERROR:'+WELOCALLY.util.getErrorString(data.errors), 'wl_error', false);
		} else if(data != null && data.errors != null) {
			_instance.setStatus(_instance.statusArea,'Could not save place:'+WELOCALLY.util.getErrorString(data.errors), 'wl_error', false);
		} else {
			try{
				var response = jQuery.parseJSON(data);
				var tag = '[welocally id="'+response.id+'"/]';				
				_instance.setStatus(_instance.statusArea,'Your place has been saved! <br/><span class="wl_placemgr_place_tag">'+tag+'</span>', 'wl_message', false);
				_instance.savedPlace = selectedPlace;
			} catch(e) {
				_instance.setStatus(_instance.statusArea,'ERROR: Can Not Parse Response.'+data, 'wl_error', false);
			}
			
			
			
		}
	  }
	});
};


WELOCALLY_AddPlaceWidget.prototype.setSelectedPlace = function(selectedPlace) {

	var _instance=this;
	jQuery(this.formArea).hide();
	jQuery('.wl_addplace_widget').find('.wl_addplace_selected_classifiers').hide();
	jQuery('.wl_addplace_widget').find('.wl_field_description').hide();
	jQuery('.wl_addplace_widget').find('.wl_field_area').hide();
	jQuery('.wl_addplace_widget').find('.wl_addplace_save_button_area').hide();
	
	var selectedPlaceArea = jQuery('<div class="wl_selected"></div>');
	
	jQuery(selectedPlaceArea)
	.append('<div class="wl_selected_name">'+selectedPlace.properties.name+'</div>')
	.append('<div class="wl_selected_adress">'+selectedPlace.properties.address+' '+
		selectedPlace.properties.city+
		' '+selectedPlace.properties.province+
		' '+selectedPlace.properties.postcode+'</div>');
			
	
	if(selectedPlace.properties.phone != null) {
		jQuery(selectedPlaceArea)
			.append('<div class="wl_selected_phone">'+selectedPlace.properties.phone+'</div>');
	}

	if(selectedPlace.properties.website != null && 
		selectedPlace.properties.website != '' ) {
		var website = selectedPlace.properties.website;
		if(selectedPlace.properties.website.indexOf('http://') == -1) {
			website = 'http://'+selectedPlace.properties.website;				
		}
		
		jQuery(selectedPlaceArea)
			.append('<div class="wl_selected_web"><a target="_new" href="'+website+'">'+website+'</a></div>');

	} 

	if(selectedPlace.properties.city != null && 
		selectedPlace.properties.province != null){
			var qS = selectedPlace.properties.city+" "+
				selectedPlace.properties.province;
			if(selectedPlace.properties.address != null)
				qs=selectedPlace.properties.address+" "+qS;
			if(selectedPlace.properties.postcode != null)
				qs=qs+" "+selectedPlace.properties.postcode;
			var qVal = qs.replace(" ","+");
			
			jQuery(selectedPlaceArea)
			.append('<div class="wl_selected_driving"><a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+
				qVal+'" target="_new">Driving Directions</a></div>');
		
	}
	
	 
	//the tag
	var tag = '[welocally id="'+selectedPlace._id+'" /]';
	var inputArea = jQuery('<input class="wl_selected_tag_add wl_widget_field" type="text"/>');
	jQuery(inputArea).val(tag);
	var wlSelectedTagArea = jQuery('<div class="wl_selected_tag_add_area"></div>');
	jQuery(wlSelectedTagArea).append(inputArea);
	jQuery(selectedPlaceArea).append(wlSelectedTagArea);	
	
	_instance.setStatus(_instance.statusArea, selectedPlaceArea,'wl_clear_both', false);
	
};

WELOCALLY_AddPlaceWidget.prototype.validGeocodeForSearch = function (geocode) {	
	var _instance = this;
	
	var hasAll = _instance.hasType("country", geocode.address_components);
	hasAll = hasAll && _instance.hasType("route", geocode.address_components);
	hasAll = hasAll && _instance.hasType("locality", geocode.address_components);
	hasAll = hasAll && _instance.hasType("administrative_area_level_1", geocode.address_components);

	return hasAll;
	
};

WELOCALLY_AddPlaceWidget.prototype.loadWithWrapper = function(cfg, map_canvas, wrapper) {
	this.cfg = cfg;
	this.wrapper = wrapper;
	jQuery(this.wrapper).html(map_canvas);
	this.load(map_canvas);
	return this;
};

WELOCALLY_AddPlaceWidget.prototype.hasType = function(type_name, address_components){
	for (componentIndex in address_components) {
		var component = address_components[componentIndex];
		if(component.types[0] == type_name)
			return true;
	}
	return false;
};
	
WELOCALLY_AddPlaceWidget.prototype.getShortNameForType = function(type_name, address_components){
	for (componentIndex in address_components) {
		var component = address_components[componentIndex];
		if(component.types[0] == type_name)
			return address_components[componentIndex].short_name;
	}
	return null;	
};


WELOCALLY_AddPlaceWidget.prototype.setLocation=function (location) {	
	
	var _instance = this;	
	
	_instance.map.setCenter(location);	
	
	var markerIconLocation = 
		new google.maps.MarkerImage(_instance.cfg.imagePath, new google.maps.Size(32, 32), new google.maps.Point(0, 0));

	
	this.setLocationMarker(
		_instance.map,
		location,
		markerIconLocation);	
		
};

WELOCALLY_AddPlaceWidget.prototype.setLocationMarker = function(markerMap, location, icon) {

  if(this.locationMarker != null)	
	this.locationMarker.setMap(null);
  
  
  
  this.locationMarker = new google.maps.Marker({
	position: location,
	map: markerMap,
	icon: icon
  });
  
};



WELOCALLY_AddPlaceWidget.prototype.getCategories = function(type, category) {
	
	var _instance = this;
	
	_instance.setStatus(_instance.statusArea, 'Loading categories...','wl_message',true);
			    
	var options = { };
	
	var base;
	
	var classifier_path = '/geodb/classifier/1_0/types.json';
	
	var selectedClassifierLevel;
	
	if(type == null && category == null) {	
		selectedClassifierLevel = 'Type';
		jQuery(this.classifiersArea)
			.find('#wl_addplace_selectable')
			.unbind('selectableselected')
			.bind( 'selectableselected', { classifierType: 'Type', type: type, category: category, _instance: this },
				this.selectedClassifierHandler);
    }
	
	else if(type != null && category == null){
		base = type;
		selectedClassifierLevel = 'Category';
		var classifier_path = '/geodb/classifier/1_0/categories.json';
		options.type = type;
		
		jQuery(this.classifiersArea)
			.find('#wl_addplace_selectable')
			.unbind('selectableselected')
			.bind( 'selectableselected', { classifierType: 'Category', type: type, category: category, _instance: this },
				this.selectedClassifierHandler);		
				
	} else if(type != null && category != null){
		base = category;
		selectedClassifierLevel = 'Subcategory';
		var classifier_path = '/geodb/classifier/1_0/subcategories.json';		
		options.type = type;
		options.category = category;
		
		jQuery(this.classifiersArea)
			.find('#wl_addplace_selectable')
			.unbind('selectableselected')
			.bind( 'selectableselected', { classifierType: 'Subcategory', type: type, category: category, _instance: this },
				this.selectedClassifierHandler);
	}
	
	var ajaxurl = _instance.cfg.endpoint +
				classifier_path+'?'+WELOCALLY.util.serialize(options)+"&callback=?";

	
	_instance.jqxhr = jQuery.ajax({
	  url: ajaxurl,
	  dataType : 'json',
	  beforeSend: function(jqXHR){
		_instance.jqxhr = jqXHR;
	  },
	  error : function(jqXHR, textStatus, errorThrown) {
		if(textStatus != 'abort'){
			_instance.setStatus(_instance.statusArea, 'ERROR : '+textStatus,'wl_error',false);
		}	
	  },		  
	  success : function(data, textStatus, jqXHR) {
		
		_instance.setStatus(_instance.statusArea,'','wl_message',false);
		
		if(data != null && data.errors != null) {
			_instance.setStatus(_instance.statusArea, 'no data','wl_error',false);		
		} else {
			currentCategories = data;
			jQuery(_instance.classifiersArea).find('ul').empty();
			jQuery.each(data, function(key, val) {
				if(val != null && val != '' ) {
					jQuery(_instance.classifiersArea).find('ul').append('<li>'+val+'</li>');
				}
			});	
			jQuery(_instance.classifiersArea).show();
		}
		
	  }
	});
};

WELOCALLY_AddPlaceWidget.prototype.show = function(selectedPlace) {	
	var _instance = this;
	_instance.setPlaceForEdit(selectedPlace);
	jQuery(_instance.wrapper).show();	                        
};


WELOCALLY_AddPlaceWidget.prototype.setPlaceForEdit = function(selectedPlace) {

	var _instance=this;

		
	jQuery(_instance.formArea).find('#wl_addplace_id').val(selectedPlace._id);
	
	jQuery(_instance.placeNameField).find('input').val(selectedPlace.properties.name);
	
	jQuery('.wl_addplace_loc_field_area').show();
	jQuery('.wl_addplace_loc_field_area').find('input').val(selectedPlace.properties.address+' '+
			selectedPlace.properties.city+
			' '+selectedPlace.properties.province+
			' '+selectedPlace.properties.postcode);
	jQuery(this.locationField).find('input').trigger('change' , {form: _instance.formArea, _instance: this}, this.locationChangeHandler);  
	
	
	if(selectedPlace.properties.classifiers.type){
		_instance.setSelectedPlaceClassfiers(selectedPlace);			
		jQuery(_instance.formArea).find('.wl_addplace_selected_classifiers').show();
	}

		
	jQuery(_instance.formArea).find('#wl_addplace_phone').val(selectedPlace.properties.phone);		
	jQuery(_instance.formArea).find('#wl_addplace_web').val(selectedPlace.properties.website);	
	
	jQuery(_instance.formArea).find('#wl_addplace_optional').show();
	jQuery(_instance.formArea).find('#wl_addplace_button_add').find('span').html('save place')
	jQuery(_instance.formArea).find('#wl_addplace_buttons').show();
	
	
	

	
};
