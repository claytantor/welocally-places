<?php /**
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 * @return string
 */
$MapPlaceSelected	= get_post_meta( $post->ID, '_PlaceSelected', true );

?>
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
var map;


var boxText = document.createElement("div");
boxText.className = "wl-map-infobox"; 
boxText.innerHTML = "none selected";

var infoboxOptions = {
			content: boxText
			,disableAutoPan: false
			,maxWidth: 0
			,pixelOffset: new google.maps.Size(-90, 0)
			,zIndex: null
			,boxStyle: { 
			  background: "url('<?php echo wl_get_option('map_infobox_marker') ?>') no-repeat"
			  ,opacity: 0.85
			  ,width: "180px"
			 }
			,closeBoxMargin: "10px 2px 2px 2px"
			,closeBoxURL: "<?php echo wl_get_option('map_infobox_close') ?>"
			,infoBoxClearance: new google.maps.Size(1, 1)
			,isHidden: false
			,pane: "floatPane"
			,enableEventPropagation: false
		};
		
var ib = new InfoBox(infoboxOptions);


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

	
	//setup the bounds
	var bounds = new google.maps.LatLngBounds();

	jQuery('#map_canvas').height( 400 );
	
	

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
    
    map = new google.maps.Map(document.getElementById("map_canvas"),
        mapOptions);
        
    //Associate the styled map with the MapTypeId and set it to display.
  	map.mapTypes.set('welocally_style', styledMapType);
  	map.setMapTypeId('welocally_style');  

<?php else:?>  	
  	
	var mapOptions = {
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    map = new google.maps.Map(document.getElementById("map_canvas"),
        mapOptions);
        


<?php  endif; ?>

    

	var places = new Array();

<?php
$index = 0;
foreach( $posts as $post ) { ?>
	places[<?php echo $index; ?>] = 
		jQuery.parseJSON( 
			'<?php echo $bodytag = str_replace("'", "\'", get_post_meta( $post->ID, '_PlaceSelected', true )); ?>' );		
	
	var latlng = new google.maps.LatLng(places[<?php echo $index; ?>].latitude, places[<?php echo $index; ?>].longitude);
	bounds.extend(latlng);
	
	/*
	index, 
	place, 
	map,  
	image, 
	title, 
	link, 
	excerpt,
	webicon,
	directionsicon
	*/
	var item =  addItemMarker(
		<?php echo $index; ?>, 
		places[<?php echo $index; ?>], 
		map,
		'<?php echo wl_get_option("map_default_marker") ?>',
		'<?php echo str_replace("'", "\'",$post->post_name); ?>',
		'<?php the_permalink(); ?>',
		'<?php echo str_replace("'", "\'",wl_get_post_excerpt( $post->ID )); ?>',	
		'<?php echo wl_get_option("map_icon_web"); ?>',
		'<?php echo wl_get_option("map_icon_directions"); ?>'
		);
		
<?php
	$index=$index+1;
}
?>	

	//init map with bounds   
    if(<?php echo $index; ?>==1){
    	map.setCenter(latlng);
    	map.setZoom(14);
    } else {
    	map.fitBounds(bounds);
    }
	
});





</script>
<li class="sidebar-item">
<div id="map_widget_container">
	<h3><?php echo $title; ?></h3>
	<div id="map_canvas"></div>
	<div id="place-details-area">
		<div id="sp-click-action-call" class="simple_grey_italic text-align-right">click to select place</div>
		<div id="place-details">
			<div class="wl-place-name wl-place-widget-name" id="details-place-name"></div>
			<div class="wl-place-excerpt" id="details-place-excerpt"></div>				
		</div>				
	</div>
			<div class="wl-place-widget-website text-align-right">
			<a href="<?php echo places_get_mapview_link(); ?>">
				<?php _e('view larger', $wlPlaces->pluginDomain)?>
			</a>
			</div>
</div>
</li>

