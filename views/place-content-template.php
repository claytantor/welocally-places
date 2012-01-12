<script type="text/javascript" charset="utf-8">

var map_post = null;
jQuery(document).ready(function(jQuery) {
	
	//themes can screw up google maps
	jQuery('#map_canvas_post img').css('max-width' ,'1030px');
	jQuery('.gmnoprint img').css('max-width' ,'1030px');
	
	
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
	
	var place = jQuery.parseJSON( '%2$s' );	
	
	var showMap = %3$s;
	var customStyle = %4$s;
	
	jQuery('#place-name-%1$d').html(place.properties.name);
	
	jQuery('#place-address-%1$d').html(
		place.properties.address+", "+
		place.properties.city+" "+
		place.properties.province+" "+
		place.properties.postcode);
			
	if(place.properties.phone != null) {
		jQuery('#place-phone-%1$d').html(place.properties.phone);
	}
	
	if(place.properties.website != null && place.properties.website != '' ) {
		var website = place.properties.website;
		if(place.properties.website.indexOf('http://') == -1) {
			website = 'http://'+place.properties.website;
		}
		jQuery('#place-website-%1$d')
				.html(
					'<table><tr><td class="wl-place-link-item"><a href="'+
					website+'">'+
					'<img src="%6$s" border="0"/></a></td>'+
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
			
			jQuery('#place-driving-%1$d')
				.html(
					'<table><tr><td class="wl-place-link-item"><a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+
				qVal+'" target="_new"><img src="%7$s"/></a></td>'+
					'<td class="wl-place-link-item"><a href="http://maps.google.com/maps?f=d&source=s_q&hl=en&geocode=&q='+
				qVal+'" target="_new">directions</a></td></tr></table>');
	 }
	 
	 if(showMap && customStyle ){
	 	
	 	
	 	var latlng = new google.maps.LatLng(place.geometry.coordinates[1], place.geometry.coordinates[0]);
	
		var welocallyMapStyle = %5$s;
		
		
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
		
		map_post = new google.maps.Map(document.getElementById("map_canvas_post"),
			mapOptions);
			
		//Associate the styled map with the MapTypeId and set it to display.
		map_post.mapTypes.set('welocally_style', styledMapType);
		map_post.setMapTypeId('welocally_style');
		
		
		//home location
		var mMarker = new google.maps.Marker({
			position: latlng,
			map: map_post,
			icon: '%14$s'
		});
		
		jQuery('#map_canvas_post').show();
	 
	 } else if(showMap && !customStyle ){
	 	var latlng = new google.maps.LatLng(place.geometry.coordinates[1], place.geometry.coordinates[0]);
	
		var mapOptions = {
		  zoom: 12,
		  center: latlng,
		  mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		
		map_post = new google.maps.Map(document.getElementById("map_canvas_post"),
			mapOptions);
	
		//home location
		var mMarker = new google.maps.Marker({
			position: latlng,
			map: map_post,
			icon: '%14$s'
		});
		
		jQuery('#map_canvas_post').show();
	 }
	
});

</script>
<div id="wl-place-content">
	<div id="map_canvas_post"></div>
	<div class="wl-place-name" id="place-name-%1$d"></div>
	<div class="wl-place-address" id="place-address-%1$d"></div>
	<div class="wl-place-phone" id="place-phone-%1$d"></div>
	
	<ul>
		<li class="wl-place-links-lines" >
			<span id="place-website-%1$d"></span>
		</li>
		<li class="wl-place-links-lines" >
			<span id="place-driving-%1$d"></span>
		</li>
	</li>
</div>
