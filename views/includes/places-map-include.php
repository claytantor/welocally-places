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
	
	jQuery('#map_widget_container ul').css('list-style-type','none');
	jQuery('#map_widget_container ul').find('*')
		.css('padding','0x')
		.css('margin','0x');
	jQuery('#info-contents-box').css('line-height','15px');
	
	//#sidebar .widget ul li a {background:url(images/ico-meta.gif) no-repeat 0 8px; padding:4px 0 4px 15px; line-height:16px;}
	
	
	//setup the bounds
	var bounds = new google.maps.LatLngBounds();

	//find all  map widgets
	var map_canvas_widgets = jQuery("*[id\^='map_canvas_widget']");
	
	jQuery(map_canvas_widgets).height( 400 );
	jQuery('#map_canvas_widget img').css('max-width' ,'1030px');
	jQuery('.gmnoprint img').css('max-width' ,'1030px');
	
	

<?php if(wl_get_option('map_custom_style') != '') : ?>	
	//make the style map
	var welocallyMapStyle = <?php printf(wl_get_option("map_custom_style"))  ?>;

  	var mapOptions = {
  		mapTypeId: google.maps.MapTypeId.ROADMAP,
		styles: welocallyMapStyle,
		draggableCursor: 'url(http://maps.google.com/mapfiles/openhand.cur)'
    };
    
    var wl_map_widget = new Array();
	
	for (var i=0; i<map_canvas_widgets.length; i++) {
		wl_map_widget[i] = new google.maps.Map(
			map_canvas_widgets[i],
	        mapOptions);
	        
		WELOCALLY.places.map.setMapEvents(wl_map_widget[i]);
		
	}
    

<?php else:?>  	
  	
	var mapOptions = {
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      draggableCursor: 'url(http://maps.google.com/mapfiles/openhand.cur), move'
    };

	var wl_map_widget = new Array();
	
	for (var i=0; i<map_canvas_widgets.length; i++) {
	
		wl_map_widget[i] = new google.maps.Map(
			map_canvas_widgets[i],
	        mapOptions);
	    
	    WELOCALLY.places.map.setMapEvents(wl_map_widget[i]);
	    
		
	}

<?php  endif; ?>

	var places = new Array();

<?php
$index = 0;
foreach( $posts as $post ) {
    $places = get_post_places($post->ID);
    
    foreach ($places as $place):
?>
	places[<?php echo $index; ?>] = <?php echo json_encode($place) ; ?>;
	
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
	for (var i=0; i<wl_map_widget.length; i++) {
		var item<?php echo $index; ?> =  addItemMarker(
			'places-map',
			<?php echo $index; ?>, 
			places[<?php echo $index; ?>], 
			wl_map_widget[i],
			'<?php echo wl_get_option("map_default_marker") ?>',
			'<?php echo str_replace("'", "\'",$post->post_name); ?>',
			'<?php echo get_post_permalink( $post->ID ); ?>',
			'<?php echo str_replace("'", "\'",wl_get_post_excerpt( $post->ID )); ?>',	
			'<?php echo wl_get_option("map_icon_web"); ?>',
			'<?php echo wl_get_option("map_icon_directions"); ?>',
			false,
			false,
			''		
			);
	}	
<?php
	$index=$index+1;
	endforeach;
}
?>	

	for (var i=0; i<wl_map_widget.length; i++) {
	//init map with bounds   
	    if(<?php echo $index; ?>==1){
	    	wl_map_widget[i].setCenter(latlng);
	    	wl_map_widget[i].setZoom(14);
	    } else {
	    	wl_map_widget[i].fitBounds(bounds);
	    }
	}
	
});
</script>
