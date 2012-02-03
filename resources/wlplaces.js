jQuery(document).ready(function(){
    WLPlaces.init();
});


var WLPlaces = {
    
    init: function() {
        return true;
    },
    
    insertPlace: function(sel, place, options) {
        var $sel = jQuery('#' + sel);
        
    	var showMap = options.showmap;
    	var customStyle = options.isCustom;
        
	    jQuery('.wl-place-name', $sel).html(place.properties.name);
    	jQuery('.wl-place-address', $sel).html(
    		place.properties.address+", "+
    		place.properties.city+" "+
    		place.properties.province+" "+
    		place.properties.postcode);
    		
    	if(place.properties.phone != null) {
    		jQuery('.wl-place-phone', $sel).html(place.properties.phone);
    	}

    	if(place.properties.website != null && place.properties.website != '' ) {
    		var website = place.properties.website;
    		if(place.properties.website.indexOf('http://') == -1) {
    			website = 'http://'+place.properties.website;
    		}
    		jQuery('.wl-place-website', $sel)
    				.html(
    					'<table><tr><td class="wl-place-link-item"><a href="'+
    					website+'">'+
    					'<img src="' + options.map_icon_web + '" border="0"/></a></td>'+
    					'<td class="wl-place-link-item"><a href="'+
    					website+'" target="_new">website</a></td></tr></table>');
    	} 

    	if(place.properties.city != null && place.properties.province != null){
    			var qS = place.properties.city+" "+place.properties.province;
    			if(place.properties.address != null)
    				qs=place.properties.address+" "+qS;
    			if(place.properties.postcode != null)
    				qs=qs+" "+place.properties.postcode;
    			var qVal = qs.replace(" ","+");
			
    			jQuery('.wl-place-driving', $sel)
    				.html(
    					'<table><tr><td class="wl-place-link-item"><a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+
    				qVal+'" target="_new"><img src="' + options.map_icon_directions + '"/></a></td>'+
    					'<td class="wl-place-link-item"><a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+
    				qVal+'" target="_new">directions</a></td></tr></table>');
    	 }

	 if(showMap && customStyle ){
	 	
	 	
	 	var latlng = new google.maps.LatLng(place.geometry.coordinates[1], place.geometry.coordinates[0]);
	
		var welocallyMapStyle = options.map_custom_style;
		
		
		// Create a new StyledMapType object, passing it the array of styles,
		// as well as the name to be displayed on the map type control.
		var styledMapType = new google.maps.StyledMapType(welocallyMapStyle,
			{name: "Custom"});
		
		
		var mapOptions = {
		  zoom: 16,
		  center: latlng,
		  mapTypeControlOptions: {
			mapTypeIds: ['welocally_style']
		  }
		};
		
		map_post = new google.maps.Map(jQuery('.map_canvas_post', $sel)[0],
			mapOptions);
			
		//Associate the styled map with the MapTypeId and set it to display.
		map_post.mapTypes.set('welocally_style', styledMapType);
		map_post.setMapTypeId('welocally_style');
		
		
		//home location
		var mMarker = new google.maps.Marker({
			position: latlng,
			map: map_post,
			icon: options.where_image
		});
		
		jQuery('.map_canvas_post', $sel).show();
	 
	 } else if(showMap && !customStyle ){
	 	var latlng = new google.maps.LatLng(place.geometry.coordinates[1], place.geometry.coordinates[0]);
	
		var mapOptions = {
		  zoom: 12,
		  center: latlng,
		  mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		
		map_post = new google.maps.Map(jQuery('.map_canvas_post', $sel)[0],
			mapOptions);
	
		//home location
		var mMarker = new google.maps.Marker({
			position: latlng,
			map: map_post,
			icon: options.where_image
		});
		
		jQuery('.map_canvas_post', $sel).show();
		
//		jQuery('.map_canvas_post', $sel).show();
	 }


    }
    
      
};