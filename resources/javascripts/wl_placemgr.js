/*
 * copyright 2012 welocally. NO WARRANTIES PROVIDED
 */
function WELOCALLY_PlaceManager(cfg) {
	this.cfg;
	this.wrapper;

	this.init = function() {
		return this;
		
	};

};

WELOCALLY_PlaceManager.prototype.initCfg = function(cfg) {
	var errors = [];
	if (!cfg) {
		errors.push("Please provide configuration for the widget");
		cfg = {};
	}

	if (errors.length > 0)
		return errors;

	this.cfg = cfg;
};

WELOCALLY_PlaceManager.prototype.makeWrapper = function(){
	
	var _instance = this;
	
	var wrapper = jQuery('<div></div>');
	
	this.statusArea = jQuery('<div></div>');
	this.statusArea.css('display','none');
	jQuery(wrapper).append(this.statusArea);
	
	this.deleteDialog = jQuery('<div></div>');
	this.deleteDialog.css('display','none');
	jQuery(wrapper).append(this.deleteDialog);
	
	//the add place button
	var btnAdd = jQuery('<div style="margin-bottom: 5px;"><div style="display:inline-block; width: 150px;">Create New Place:</div>'+
			'<div style="display:inline-block;"><a class="wl_placemgr_button" href="#">add place</a></div></div>');
	
	jQuery(btnAdd).bind('click',{instance: this}, this.addHandler);
	
	jQuery(wrapper).append(btnAdd);
		
	var filterField = jQuery('<div id="wl_placesmgr_searchterm" style="margin-bottom: 5px; "><div style="display:inline-block; width: 150px;">Enter Search Term</div><div style="display:inline-block;">'+
			' <input type="text" id="wl_placemgr_filter" style="width:400px"/><a class="wl_placemgr_button" id="wl_placemgr_filter_btn" href="#">search</a></div></div>');	
	//#wl_placesmgr_searchterm
	jQuery(filterField).css('display','none');
	jQuery(filterField).find('a').click(function(event,ui){
		_instance.pager.cfg.filter= 'place~'+jQuery(filterField).find('input').val();
		_instance.pager.getMetadata();
		_instance.pager.load(1);
		return false;
	});
	
	jQuery(wrapper).append(filterField);
	
	//add as an edit widget
	this.addPlaceWidget = new WELOCALLY_AddPlaceWidget();
    this.addPlaceWidget.initCfg(_instance.cfg);		
	this.addPlaceWrapper = this.addPlaceWidget.makeWrapper();
	jQuery(this.addPlaceWrapper).hide();	
	jQuery(wrapper).append(this.addPlaceWrapper);
	
	//now the pagination
	this.pager = new WELOCALLY_WpPager();
	this.pager.initCfg(_instance.cfg);
	jQuery(wrapper).append(this.pager.makeWrapper());
	this.pager.getMetadata();	
	this.pager.load(1);	
	
	this.wrapper = wrapper;
	return wrapper;
	
};



WELOCALLY_PlaceManager.prototype.setPlaceRow = function(i, place, row) {
	
	if(place){
		jQuery(this.wrapper).find('#wl_placesmgr_searchterm').show();
		
		
		var placeRowContent = jQuery('<div></div>');	
		jQuery(placeRowContent).append('<div class="wl_placemgr_place_tag">[welocally id="'+place._id+'" /]</div>');	
		
		var placeInfo = jQuery('<div class="wl_placemgr_place_info"></div>');

		jQuery(placeInfo).append('<div class="place_field">'+place.properties.name+'</div>');
		jQuery(placeInfo).append('<div class="place_field">'+place.properties.address+'</div>');
		jQuery(placeInfo).append('<div class="place_field">'+place.properties.city+'</div>');
		jQuery(placeInfo).append('<div class="place_field">'+place.properties.province+'</div>');
		jQuery(placeInfo).append('<div class="place_field">'+place.properties.postcode+'</div>');
		if(place.properties.website)
			jQuery(placeInfo).append('<div class="place_field">'+place.properties.website+'</div>');
		if(place.properties.phone)
			jQuery(placeInfo).append('<div class="place_field">'+place.properties.phone+'</div>');
		jQuery(placeRowContent).append(placeInfo);
		
		var actions = jQuery('<div class="wl_placemgr_actions"></div>');
		var btnEdit = jQuery('<a class="wl_placemgr_button" href="#">edit</a>');
		jQuery(btnEdit).bind('click',{instance: this, place: place, index: i, row: row}, this.editHandler);

		var btnDelete = jQuery('<a class="wl_placemgr_button" href="#">delete</a>');
		
		jQuery(btnDelete).bind('click',{instance: this, place: place, index: i, row: row}, this.deleteDialogHandler);
		
		jQuery(actions).append(btnEdit);
		jQuery(actions).append(btnDelete);		
		jQuery(placeRowContent).append(actions);
			
		jQuery(row).html(placeRowContent);
	} else {
		
		jQuery(this.wrapper).find('#wl_placesmgr_searchterm').show();
		
		
		var placeRowContent = jQuery('<div></div>');	
		
		var placeInfo = jQuery('<div class="wl_placemgr_place_info">PLACE EMPTY PLEASE DELETE AND ADD A NEW ONE</div>');
		jQuery(placeRowContent).append(placeInfo);
		
		var actions = jQuery('<div class="wl_placemgr_actions"></div>');
		var btnDelete = jQuery('<a class="wl_placemgr_button" href="#">delete</a>');
		
		jQuery(btnDelete).bind('click',{instance: this, place: place, index: i, row: row}, this.deleteDialogHandler);
		
		jQuery(actions).append(btnDelete);		
		jQuery(placeRowContent).append(actions);
			
		jQuery(row).html(placeRowContent);
	}
	
};

WELOCALLY_PlaceManager.prototype.editHandler = function(event,ui) {
	var place = event.data.place;
	var _instance = event.data.instance;
	_instance.addPlaceWidget.savedPlace = null;
	_instance.addPlaceWidget.show(place);
	jQuery( _instance.addPlaceWrapper).dialog({
		title: 'edit place',
		minWidth: 600,
		modal: true
	});	
	
	jQuery( _instance.addPlaceWrapper).bind('dialogclose',
			{instance: _instance, 
			widget:_instance.addPlaceWidget, 
			index: event.data.index, 
			row: jQuery('#wl_placemgr_place_'+event.data.index)}, 
			_instance.editDialogClosedHandler);
	
	return false;
		
};


WELOCALLY_PlaceManager.prototype.addHandler = function(event,ui) {
	var _instance = event.data.instance;
	_instance.addPlaceWidget.clearFields();
	
	jQuery( _instance.addPlaceWrapper).dialog({
		title: 'add place',
		position: "top",
		minWidth: 650,
		modal: true
	});
	
	jQuery(_instance.addPlaceWrapper).bind('dialogclose', {
		instance : _instance
	}, _instance.addDialogClosedHandler);

	
	return false;
		
};


WELOCALLY_PlaceManager.prototype.addDialogClosedHandler = function(event,ui) {
	var _instance = event.data.instance;
	
	_instance.pager.getMetadata();	
	_instance.pager.load(1);	
	
	
	return false;	
};

WELOCALLY_PlaceManager.prototype.editDialogClosedHandler = function(event,ui) {
	var _instance = event.data.instance;
	var place = event.data.widget.savedPlace;
	if(place){
		var index = event.data.index;
		var row = event.data.row;
		
		_instance.setPlaceRow(index, place, row);
	}

	jQuery(_instance.statusArea).delay(5000).fadeOut('slow'); 
	
	return false;	
};

WELOCALLY_PlaceManager.prototype.deleteDialogHandler = function(event,ui) {
	var _instance = event.data.instance;
	jQuery( _instance.deleteDialog).bind('deleteplace',
			event.data, 
			_instance.deleteHandler);
	var title = 'Delete Place?';
	if(event.data.place) {
		jQuery( _instance.deleteDialog).html('Please confirm that you would like to delete '+
				event.data.place.properties.name+' at '+event.data.place.properties.address+
				'. <strong>This action can not be undone.</strong> You will also need to delete the tag from your post.');
		title = 'Delete '+event.data.place.properties.name+'?'
	} else {
		jQuery( _instance.deleteDialog).html('The place is missing from this record, you should go ahead and delete it and add a new place');
	}
	
	jQuery( _instance.deleteDialog).dialog({
		resizable: false,
		title: title,
		width: 400,
		height:200,
		modal: true,
		buttons: {
			"Delete": function() {				
				jQuery( _instance.deleteDialog).trigger('deleteplace',
						event.data, 
						_instance.deleteHandler);
				jQuery( this ).dialog( "close" );
			},
			Cancel: function() {
				jQuery( this ).dialog( "close" );
			}
		}
	});	


	
};

WELOCALLY_PlaceManager.prototype.deleteHandler = function(event,ui) {
	var _instance = event.data.instance;
	WELOCALLY.ui.setStatus(_instance.statusArea,'Deleting Place...', 'wl_message', true);
	
	var data = {
			action: 'delete_place',
			id: event.data.index
	};
		   
	_instance.jqxhr = jQuery.ajax({
	  type: 'POST',		  
	  url: ajaxurl,
	  data: data,
	  beforeSend: function(jqXHR){
		_instance.jqxhr = jqXHR;
	  },
	  error : function(jqXHR, textStatus, errorThrown) {
		if(textStatus != 'abort'){
			WELOCALLY.ui.setStatus(_instance.statusArea,'ERROR : '+textStatus, 'error', false);
		}		
	  },		  
	  success : function(data, textStatus, jqXHR) {
		if(data != null && data.errors != null) {
			WELOCALLY.ui.setStatus(_instance.statusArea,'ERROR:'+WELOCALLY.util.getErrorString(data.errors), 'wl_error', false);
		} else if(data != null && data.errors != null) {
			WELOCALLY.ui.setStatus(_instance.statusArea,'Could not delete place:'+WELOCALLY.util.getErrorString(data.errors), 'wl_error', false);
		} else {
			WELOCALLY.ui.setStatus(_instance.statusArea,'Your place has been deleted!', 'wl_message', false);
			jQuery('#wl_placemgr_place_'+event.data.index).hide();
			jQuery('#wl_placemgr_place_'+event.data.index).html('Deleted.');
			jQuery('#wl_placemgr_place_'+event.data.index).show('slow');
			jQuery(_instance.statusArea).delay(5000).fadeOut('slow'); 
			
		}
	  }
	});
	
	
	
	return false;
	
};




