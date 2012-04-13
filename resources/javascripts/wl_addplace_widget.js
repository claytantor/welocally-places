/*
	copyright 2012 welocally. NO WARRANTIES PROVIDED
*/
//foo
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
	
	this.selectedPlace = {
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
		
		// Get current script object
		var script = jQuery('SCRIPT');
		script = script[script.length - 1];

		// Build Widget
		this.wrapper = jQuery('<div></div>');	
		jQuery(this.wrapper).attr('class','wl_addplace_widget');
		jQuery(this.wrapper).attr('id','wl_addplace_widget');
		
		this.statusArea = jQuery('<div></div>');
		jQuery(this.ajaxStatus).css('display','none');	
		jQuery(this.wrapper).append(this.statusArea);
	
		jQuery(script).parent().before(this.wrapper);
		
		if(error){
			this.setStatus(this.statusArea, error,'wl_error',false);
		} else {
			this.geocoder = new google.maps.Geocoder();
			
			//selected
			
			//name and address
			this.formArea = this.createForm();
			jQuery(this.wrapper).append(this.formArea);
		
			//google maps does not like jquery instances
			this.map_canvas = document.createElement('DIV');
			jQuery(this.map_canvas).css('display','none');	
			jQuery(this.map_canvas).css('min-width','200px');
			jQuery(this.map_canvas).css('min-height','200px');
			jQuery(this.map_canvas).attr('class','welocally_addplace_widget_map_canvas');
			jQuery(this.map_canvas).attr("id","wl_addplace_map_canvas_widget");
			//jQuery(this.wrapper).append(this.map_canvas);
			jQuery(this.formArea).append(this.map_canvas);
					
			this.map = this.initMap(this.map_canvas);
			this.setLocation(this.cfg.location);
			
			this.selectedClassifiersArea = 
				jQuery('<div class="wl_addplace_selected_classifiers"><ul></ul></div>');
			jQuery(this.selectedClassifiersArea).css('display','none');
			jQuery(this.wrapper).append(this.selectedClassifiersArea);
			
			this.classifiersArea = jQuery('<div class="wl_addplace_classifiers" style="margin-top:10px"><div class="wl_field_description">Click on the classifiers below to categorize the place. (Required)</div><ul id="wl_addplace_selectable"></ul></div>');
			jQuery(this.classifiersArea).find('#wl_addplace_selectable').selectable({ cancel: 'a' });
			jQuery(this.classifiersArea).css('display','none');
       
        		
			//jQuery(this.wrapper).append(this.classifiersArea);
			jQuery(this.formArea).append(this.classifiersArea);
						
		
		}
		
		return this;
					
	};
	
}

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
    
    
    
//    this.setLocationMarker(
//    	map, 
//    	_instance.cfg.location,  
//    	_instance.cfg.imagePath+'/marker_search.png');
    
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

WELOCALLY_AddPlaceWidget.prototype.createForm = function() {
	var _instance = this;
	var formArea = jQuery('<div></div>');
	
	jQuery(formArea)
	.append('<div class="wl_field_description">Enter the real name of the place, such as "The Red Snapper Grill"</div>');
	
	this.placeNameField = jQuery('<div class="wl_field_area"><input id="wl_addplace_name_field" type="text" name="placeName" class="wl_widget_field wl_addplace_name"/></div>');
	jQuery(this.placeNameField).find('input').bind('change' , {form: formArea, _instance: this}, this.nameChangeHandler);  
	
	jQuery(formArea).append(this.placeNameField);
	
	var locFieldArea = jQuery('<div class="wl_addplace_loc_field_area" style="display:none;"></div>');
	jQuery(locFieldArea)
	.append('<div class="wl_field_description">Enter the most exact address of the place, such as 1234 Mullberry Rd Oakland CA 94612</div>');
	
	this.locationField = jQuery('<div class="wl_field_area"><input id="wl_addplace_location_field" type="text" name="placeLocation" class="wl_widget_field wl_addplace_location"/></div>');
	jQuery(this.locationField).find('input').bind('change' , {form: formArea, _instance: this}, this.locationChangeHandler);  
	
	jQuery(locFieldArea).append(this.locationField);	
	
	//bind focus
	jQuery(this.placeNameField).keypress(function(e){
        if ( e.which == 13 ){
        	jQuery('#wl_addplace_name_field').trigger('change' , {form: formArea, _instance: _instance}, _instance.nameChangeHandler);
        	jQuery('#wl_addplace_location_field').focus();
        	return false;
        }
    });
	
	//bind focus
	jQuery(this.locationField).keypress(function(e){
        if ( e.which == 13 ){
        	jQuery('#wl_addplace_location_field').trigger('change' , {form: formArea, _instance: _instance}, _instance.locationChangeHandler);
        	return false;
        }
    });
	
	
	jQuery(formArea).append(locFieldArea);
		
	return formArea;
};

WELOCALLY_AddPlaceWidget.prototype.addOptionalFieldsToForm = function(formArea) {	
	var _instance = this;
	
	jQuery(formArea)
	.append('<div class="wl_field_description">Enter the phone number with areacode and country code, such as +1 510 555-1234</div>');
		
	//phone
	var phoneField = jQuery('<div class="wl_field_area"><input type="text" name="phoneField" class="wl_widget_field wl_addplace_phone"/></div>');
	jQuery(phoneField).change(function(event, ui){
		_instance.selectedPlace.properties.phone=jQuery(this).find('input').val();		
		jQuery(formArea).find('.wl_addplace_web').focus();		
	});	
	jQuery(formArea).append(phoneField);
	
	jQuery(formArea)
	.append('<div class="wl_field_description">Enter the website for the place if it exists. Include the http prefix, such as http://greatplace.com</div>');
		
	//website
	var webField = jQuery('<div class="wl_field_area"><input type="text" name="webField" class="wl_widget_field wl_addplace_web"/></div>');
	jQuery(webField).change(function(event, ui){
		_instance.selectedPlace.properties.website=jQuery(this).find('input').val();
		
	});	
	jQuery(formArea).append(webField);
	
	var buttonDiv = jQuery('<div></div>').attr('class','wl_addplace_save_button_area'); 		
	var addPlaceButton = jQuery('<button id="wl_addplace_save" class="actions wl_addplace_button">Add Place</button>');
	jQuery(addPlaceButton).click(function(){
		_instance.savePlace(_instance.selectedPlace);
		return false;
	});
	jQuery(buttonDiv).append(addPlaceButton);
	jQuery(formArea).append(buttonDiv);

};

WELOCALLY_AddPlaceWidget.prototype.selectedClassifiedHandler = function(event,ui) {
	//alert(event.data.msg);
	
	var _instance = event.data._instance;
	
	var selected = WELOCALLY.util.unescape(ui.selected.innerHTML);
	var selectedClassifierLevel = event.data.classifierType;
	var selectedType = event.data.type;
	var selectedCategory = event.data.category;
		
	jQuery(_instance.classifiersArea).find('ul').empty();
	
	jQuery(_instance.selectedClassifiersArea).find('ul')
		.append('<li class="wl_addplace_categories_selected_list_item">'+
		selectedClassifierLevel+':'+ui.selected.innerHTML+'</li>');
		
	if(selectedClassifierLevel == 'Type'){
		selectedType = selected;
		_instance.selectedPlace.classifiers[0].type = selected;
		_instance.getCategories(selectedType,null);
		
	} else if(selectedClassifierLevel == 'Category'){
		selectedCategory = selected;
		_instance.selectedPlace.classifiers[0].category = selected;
		_instance.getCategories(selectedType,selectedCategory);
		
	} else if(selectedClassifierLevel == 'Subcategory'){
		_instance.selectedPlace.classifiers[0].subcategory = selected;
		_instance.addOptionalFieldsToForm(_instance.wrapper)
	}
		
	jQuery(_instance.selectedClassifiersArea).show();
	
	
	
	return false;
};

WELOCALLY_AddPlaceWidget.prototype.nameChangeHandler = function(event, ui) {
	
	var _instance = event.data._instance;
	_instance.selectedPlace.properties.name=jQuery(this).val();	
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
					
			if(results[0].address_components.length<=5){
				_instance.map.setZoom(14);
			} else {
				_instance.map.setZoom(16);
			}
				
			//set the model
			_instance.selectedPlace.properties.address = 
				_instance.getShortNameForType("street_number", results[0].address_components)+' '+
				_instance.getShortNameForType("route", results[0].address_components);
			
			_instance.selectedPlace.properties.city = 
				_instance.getShortNameForType("locality", results[0].address_components);
			
			_instance.selectedPlace.properties.province = 
				_instance.getShortNameForType("administrative_area_level_1", results[0].address_components);
	
			_instance.selectedPlace.properties.postcode = 
				_instance.getShortNameForType("postal_code", results[0].address_components);
			
			_instance.selectedPlace.properties.country = 
				_instance.getShortNameForType("country", results[0].address_components);
			
			_instance.selectedPlace.geometry.coordinates = [];
			_instance.selectedPlace.geometry.coordinates.push(results[0].geometry.location.lng());
			_instance.selectedPlace.geometry.coordinates.push(results[0].geometry.location.lat());
						
			_instance.setStatus(_instance.statusArea, '','message',true);
			
			var location = new google.maps.LatLng(
				results[0].geometry.location.lat(), 
				results[0].geometry.location.lng());
			_instance.setLocation(location);
						
					
			jQuery(_instance.map_canvas).show();
			google.maps.event.trigger(_instance.map, 'resize');
			_instance.map.setCenter(location);	
			
			_instance.getCategories(null,null);
								
			
		} else {
			_instance.setStatus(_instance.statusArea, 'We need the full address, ie. 123 Mulberry Rd Oakland CA, to add it.','wl_warning',false);
		} 
	});
		
	return false;
};


WELOCALLY_AddPlaceWidget.prototype.savePlace = function (selectedPlace) {

	var _instance = this;
	
	_instance.setStatus(_instance.statusArea,'Saving Place...', 'wl_message', true);
	
	var ajaxurl = _instance.cfg.endpoint +
				'/geodb/place/1_0/save.json';
	
	   
	_instance.jqxhr = jQuery.ajax({
	  type: 'GET',		  
	  url: ajaxurl,
	  data: selectedPlace,
	  dataType : 'jsonp',
	  beforeSend: function(jqXHR){
		_instance.jqxhr = jqXHR;
		_instance.jqxhr.setRequestHeader("site-key", _instance.cfg.siteKey);
		_instance.jqxhr.setRequestHeader("site-token", _instance.cfg.siteToken);
	  },
	  error : function(jqXHR, textStatus, errorThrown) {
		if(textStatus != 'abort'){
			_instance.setStatus(_instance.statusArea,'ERROR : '+textStatus, 'error', false);
		}		
	  },		  
	  success : function(data, textStatus, jqXHR) {
		if(data != null && data.errors != null) {
			_instance.setStatus(_instance.statusArea,'Could not save place', 'wl_error', false);
		} else {
			_instance.selectedPlace._id=data.id;
			_instance.setStatus(_instance.statusArea,'Your new place has been added!', 'wl_message', false);
			_instance.setSelectedPlace(selectedPlace);
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
	
	_instance.setStatus(_instance.statusArea, selectedPlaceArea,'wl_clear', false);
	
};

WELOCALLY_AddPlaceWidget.prototype.validGeocodeForSearch = function (geocode) {	
	var _instance = this;
	
	var hasAll = _instance.hasType("country", geocode.address_components);
	hasAll = hasAll && _instance.hasType("street_number", geocode.address_components);
	hasAll = hasAll && _instance.hasType("route", geocode.address_components);
	hasAll = hasAll && _instance.hasType("locality", geocode.address_components);
	hasAll = hasAll && _instance.hasType("administrative_area_level_1", geocode.address_components);
	hasAll = hasAll && _instance.hasType("postal_code", geocode.address_components);

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
				this.selectedClassifiedHandler);
    }
	
	else if(type != null && category == null){
		base = type;
		selectedClassifierLevel = 'Category';
		var classifier_path = '/geodb/classifier/1_0/categories.json';
		//options.action='get_classifiers_categories';
		options.type = type;
		
		jQuery(this.classifiersArea)
			.find('#wl_addplace_selectable')
			.unbind('selectableselected')
			.bind( 'selectableselected', { classifierType: 'Category', type: type, category: category, _instance: this },
				this.selectedClassifiedHandler);		
				
	} else if(type != null && category != null){
		base = category;
		selectedClassifierLevel = 'Subcategory';
		var classifier_path = '/geodb/classifier/1_0/subcategories.json';		
		//options.action='get_classifiers_subcategories';
		options.type = type;
		options.category = category;
		
		jQuery(this.classifiersArea)
			.find('#wl_addplace_selectable')
			.unbind('selectableselected')
			.bind( 'selectableselected', { classifierType: 'Subcategory', type: type, category: category, _instance: this },
				this.selectedClassifiedHandler);
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
}

