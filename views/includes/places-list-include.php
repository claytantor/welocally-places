
<style>
/* override the font style   */
.content-sidebar ul li a:hover, .content-sidebar .recentcomments a:hover {
color: #<?php echo wl_get_option("color_place_name", "000000"); ?>; 
}
.wl-place-name { 
	font-family: <?php echo wl_get_option("font_place_name", "Sorts Mill Goudy"); ?>; 
	color: #<?php echo wl_get_option("color_place_name", "000000"); ?>; 
}
.wl-place-name a {
	font-family: <?php echo wl_get_option("font_place_name", "Sorts Mill Goudy"); ?>; 
	color: #<?php echo wl_get_option("color_place_name", "000000"); ?>; 
}

.wl-place-widget-name{ }
.wl-place-address { 
	font-family: <?php echo wl_get_option("font_place_address", "Sorts Mill Goudy"); ?>; 
	color: #<?php echo wl_get_option("color_place_address", "000000"); ?>; 
}
.wl-place-widget-address{ }

</style>
<script type="text/javascript" charset="utf-8">

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
	var placeSelected = <?php echo json_encode($PlaceSelected); ?>;	

	var catsDiv = jQuery("*[id\^='plugin-place-category<?php echo $index;?>']");
	var placeDiv = jQuery("*[id\^='plugin-place<?php echo $index;?>']");	
	
	jQuery(placeDiv).html(
		buildContentForInfoForList(
			placeSelected,
			<?php echo $index; ?>,
			'<?php echo get_permalink( $post->ID ); ?>',
			'<?php echo wl_get_option("map_icon_web"); ?>',
			'<?php echo wl_get_option("map_icon_directions"); ?>'));
				
	for (var i=0; i<placeDiv.length; i++) {
		jQuery(placeDiv[i]).find('#plugin-place-links<?php echo $index; ?>').before(catsDiv[i]);
	}
	catsDiv.show();
	
});
</script>