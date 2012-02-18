<?php /**
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 * @return string
 */
$PlaceSelected	= get_post_meta( $post->ID, '_PlaceSelected', true );

if( $PlaceSelected != '' && $_REQUEST['json'] != 'get_post') : ?>
<style>
/* override the font style   */
.content-sidebar ul li a:hover, .content-sidebar .recentcomments a:hover {
color: #<?php echo wl_get_option("color_place_name", "000000"); ?>; 
}
.wl-place-name { 
	font-family: <?php echo wl_get_option("font_place_name", "Sorts Mill Goudy"); ?>; 
	color: #<?php echo wl_get_option("color_place_name", "000000"); ?>; 
	font-size: <?php echo wl_get_option("size_place_name", "000000"); ?>em;
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
	var placeSelected = jQuery.parseJSON( '<?php echo $bodytag = str_replace("'", "\'", $PlaceSelected); ?>' );	
	var catsDiv = jQuery('#plugin-place-category<?php echo $post->ID; ?>');
	
	jQuery('#plugin-place<?php echo $post->ID; ?>').html(
		buildContentForInfoForList(
			placeSelected,
			<?php echo $post->ID; ?>,
			'<?php echo get_permalink( $post->ID ); ?>',
			'<?php echo wl_get_option("map_icon_web"); ?>',
			'<?php echo wl_get_option("map_icon_directions"); ?>'));
			
	jQuery('#plugin-place-links<?php echo $post->ID; ?>').before(catsDiv);
	catsDiv.show();
	
});
</script>
<li>
<div class="wl-place-widget-category" id="plugin-place-category<?php echo $post->ID; ?>" style="{display:none;}"><?php echo $this->get_categories($post->ID, $exclude_cats); ?></div>
<div id="plugin-place<?php echo $post->ID; ?>"></div>
</li>
<?php endif; ?>
