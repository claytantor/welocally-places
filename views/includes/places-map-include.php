<style>
	#place-details { width: 100%; display: none; background-color: #eeeeee; }
	#sp-click-action-call { width: 100%; background-color: #eeeeee; }
	.simple_grey_italic { 
		font-size:1.2em; 
		font-style:italic; 
		font-weight:normal; 
		color: #333333; 
		font-family:Adobe Garamond Pro, Garamond, Palatino, Palatino Linotype, Times, Times New Roman, Georgia, serif;
	}
	
	img.wl-link-image { 
		/*float: right;*/ 
		margin: 4px; 
		border:0px;
	}
	
.wl-place-widget-links { text-align:right; }

#details-place-name{ margin: 3px;}

#details-place-excerpt{ margin: 3px 3px 10px 3px;}

.sidebar-item li {
	list-style: none;
 }

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
var wl_map_widget;

jQuery(document).ready(function(jQuery) {
	
	 if (typeof(jQuery.fn.parseJSON) == "undefined" 
	 	|| typeof(jQuery.parseJSON) != "function") { 

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

	
	//setup the bounds
	var bounds = new google.maps.LatLngBounds();

	jQuery('#map_canvas_widget').height( 400 );
	jQuery('#map_canvas_widget img').css('max-width' ,'1030px');
	jQuery('.gmnoprint img').css('max-width' ,'1030px');
	
	

<?php if(wl_get_option('map_custom_style') != '') : ?>	

	var welocallyMapStyle = <?php printf(base64_decode(wl_get_option("map_custom_style")))  ?>;

	// Create a new StyledMapType object, passing it the array of styles,
  	// as well as the name to be displayed on the map type control.
  	var styledMapType = new google.maps.StyledMapType(welocallyMapStyle, {name: "Custom"});
  	
  	var mapOptions = {
      mapTypeControlOptions: {
      	mapTypeIds: ['welocally_style']
      }
    };
    
    wl_map_widget = new google.maps.Map(document.getElementById("map_canvas_widget"),
        mapOptions);
        
    //Associate the styled map with the MapTypeId and set it to display.
  	wl_map_widget.mapTypes.set('welocally_style', styledMapType);
  	wl_map_widget.setMapTypeId('welocally_style');  

<?php else:?>  	
  	
	var mapOptions = {
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    wl_map_widget = new google.maps.Map(document.getElementById("map_canvas_widget"),
        mapOptions);
        


<?php  endif; ?>

    

	var places = new Array();

<?php
$index = 0;
foreach( $posts as $post ) { ?>
	places[<?php echo $index; ?>] = 
		jQuery.parseJSON( 
			'<?php echo $bodytag = str_replace("'", "\'", get_post_meta( $post->ID, '_PlaceSelected', true )); ?>' );		
	
	var latlng = new google.maps.LatLng(places[<?php echo $index; ?>].geometry.coordinates[1], places[<?php echo $index; ?>].geometry.coordinates[0]);
	bounds.extend(latlng);
	
	/*
	index, 
	place, 
	wl_map_widget,  
	image, 
	title, 
	link, 
	excerpt,
	webicon,
	directionsicon
	*/
	var item<?php echo $index; ?> =  addItemMarker(
		'places-map',
		<?php echo $index; ?>, 
		places[<?php echo $index; ?>], 
		wl_map_widget,
		'<?php echo wl_get_option("map_default_marker") ?>',
		'<?php echo str_replace("'", "\'",$post->post_name); ?>',
		'<?php echo get_post_permalink( $post->ID ); ?>',
		'<?php echo str_replace("'", "\'",wl_get_post_excerpt( $post->ID )); ?>',	
		'<?php echo wl_get_option("map_icon_web"); ?>',
		'<?php echo wl_get_option("map_icon_directions"); ?>',
		false,
		'',
		false,
		''		
		);
		
<?php
	$index=$index+1;
}
?>	

	//init map with bounds   
    if(<?php echo $index; ?>==1){
    	wl_map_widget.setCenter(latlng);
    	wl_map_widget.setZoom(14);
    } else {
    	wl_map_widget.fitBounds(bounds);
    }
	
});
</script>