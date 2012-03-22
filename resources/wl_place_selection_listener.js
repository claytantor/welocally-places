//http://localhost:8082/geodb/place/1_0/WL_3Wnkj5RxX8iKzTR5qek2Fs_37.826065_-122.209171@1293134755.json
function WELOCALLY_PlaceSelectionListener (cfg) {	
	
	this.selectedSection;
	this.cfg;
	this.wrapper;
	this.map_canvas;
	this.placeWidget;
	this.dialog;
	this.topbar;
	
	this.init = function() {
		
		var error;
		if (!cfg) {
			error = "Please provide configuration for the widget";
			cfg = {};
		}
		
		// summary (optional) - the summary of the article
		// hostname (optional) - the name of the host to use
		if (!cfg.endpoint) {
			cfg.endpoint = 'http://placehound.com';
		}
		
		if (!cfg.zoom) {
			cfg.zoom = 16;
		}
		
		//look in query string
		if (!cfg.id) {
			cfg.id = WELOCALLY.util.getParameter(
					window.top.location.search.substring(1),
					'id');
		}
			
		this.cfg = cfg;
		
		// Build Widget
		this.wrapper = jQuery('<div></div>');
		jQuery(this.wrapper).css('display','none');			
		jQuery(this.wrapper).attr('class','wl_selection');
		jQuery(this.wrapper).attr('id','welocally_selection');
		
		this.dialog = cfg.dialog;	
		
		
		jQuery(this.dialog).append(this.wrapper);
		return this;
						
	};

}


WELOCALLY_PlaceSelectionListener.prototype.show = function(selectedPlace) {
	
	var zoomLevel = this.cfg.zoom;
	
	var cfg = {
    		endpoint:'http://stage.welocally.com',
    		imagePath: 'http://placehound.com/images',
    		id: selectedPlace._id,
    		showShare: true,
    		zoom: zoomLevel,
    		styles: [
					  {
						stylers: [
						  { saturation: -45 }
						]
					  },{
						featureType: "road",
						stylers: [
						  { hue: "#ff5500" }
						]
					  },{
						featureType: "water",
						stylers: [
						  { hue: "#ffa200" },
						  { lightness: -55 }
						]
					  },{
						featureType: "poi.park",
						stylers: [
						  { hue: "#ffc300" },
						  { lightness: 14 },
						  { saturation: 21 }
						]
					  }
					]
    	    };
	
	//google maps does not like jquery instances
	if(!this.map_canvas){
		this.map_canvas = document.createElement('DIV');
		jQuery(this.map_canvas).attr('class','welocally_place_widget_map_canvas');
	}
		
	var observerPlace = 
		  new WELOCALLY_PlaceWidget().loadWithWrapper(cfg, this.map_canvas, this.dialog);
	
	if(!this.topbar){
		this.topbar = jQuery('<div class="wl_place_dialog_top"></div>');
		jQuery(".ui-dialog-titlebar").hide();
		jQuery('.ui-dialog-titlebar').after(this.topbar);
	}
	
	var closer = jQuery('<div class="wl_place_dialog_close">&nbsp;</div><div style="clear:both;"></div>');
	var _instance = this;
	jQuery(closer).click(function(){jQuery(_instance.dialog).dialog('close');});
	jQuery(this.topbar).html(closer);
		

	jQuery(".ui-dialog").addClass("wl_placehound_dialog");
	
	jQuery(this.dialog).dialog('open');
	
	
};	