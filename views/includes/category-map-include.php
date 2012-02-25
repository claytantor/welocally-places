<style>
	#map_all { width: 100%; margin-bottom:5px; } 
	#map_spacer { width: 100%; margin-bottom:20px; } 

	.wl-map-infobox { border: 1px solid #444444; margin-top: 8px; background: #eeeeee; padding: 5px;  /* for IE */ }

	ul#icons {margin: 0; padding: 0;}
	ul#icons li {margin: 2px; position: relative; padding: 4px 0; cursor: pointer; float: left;  list-style: none;}
	ul#icons span.ui-icon {float: left; margin: 0 4px;}

	/*---- selectable ----*/
	#selectable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
	#selectable li { 
			padding: 5px;
			height: <?php echo wl_get_option("cat_map_select_height", "160"); ?>px; 
			width: <?php echo wl_get_option("cat_map_select_width", "160"); ?>px; 
			display:inline-block; 
			vertical-align:top; 
            overflow:hidden; 
            margin:3px;
            border: 1px solid #777777; 
    }	
    
    #items ul, #items ul, { margin: 0px;  }
    
    #content-body ul, #content-body ol { margin: 0px;  }

	#selectable .ui-selecting { background: #C4C4C4; }
	#selectable .ui-selected { background: #e4e4e4; }
	#selectable .wl-item-content { 
			position:relative;
			top:-20px;
			left:0px;
			margin: 3px; 
			padding: 0.4em; 
			z-index:1;
	}
	
    
	#selected_content { margin: 10; padding: 10; width: 100%;  }
	
	.wl-category-title { 
		/*margin-bottom: 5px;
		border-bottom: 1px solid #777777;*/
		font-size:2.0em; 
		font-weight:bold;
		font-style: italic; 
		color: #911D1D; 
		font-family:Adobe Garamond Pro, Garamond, Palatino, Palatino Linotype, Times, Times New Roman, Georgia, serif;
	}
	
	.un-select-box { border: 1px solid #ffffff; }
	
	.wl-over-box { background: #a4a4a4; }
	

	img.wl-link-image { 
		/*float: right;*/ 
		margin: 4px; 
		border:0px;
}
	
.wl-place-widget-links { text-align:right; }
.wl-left-sidebar { width: 100% }
	
#wl-sidebar-1 {  width: 100%, display: inline-block;}	
#wl-map-content {  width: 100%, display: inline-block;}

.title-selectable-place { 
		position:relative; 	
		top:0px;
		left:0px; 
		width:100%;
		z-index:200;}
	

/* override the font style catmap   */
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

.wl-place-address { 
	font-family: <?php echo wl_get_option("font_place_address", "Sorts Mill Goudy"); ?>; 
	color: #<?php echo wl_get_option("color_place_address", "000000"); ?>; 
}
.wl-place-widget-address{ }

.wl-infobox-text_scale { 
	font-size: <?php echo wl_get_option("cat_map_infobox_text_scale", "100"); ?>%;
}


#map-all{ width:100%; background: #eeeeee; }
#map_canvas { font-size:  <?php echo wl_get_option("cat_map_infobox_text_scale", "100"); ?>%; }

</style>
<link type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css" rel="stylesheet" />			
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<script type="text/javascript" charset="utf-8">
var wl_map_main = null;
var lastindex = null;
var items = [];

function Item (place, content, marker, link) {
    this.place = place;
   	this.content = content;
   	this.marker = marker;
   	this.link = link;
}

//only used by category map
function addItemForCategoryPlace(
	index, 
	place, 
	wl_map_main,  
	image, 
	title, 
	link, 
	excerpt,
	webicon,
	directionsicon,
	showLink,
	showThumb,
	thumbUrl) {
	
	var marker = addItemMarker(
		'category-map',
		index, 
		place, 
		wl_map_main,  
		image, 
		title, 
		link, 
		excerpt,
		webicon,
		directionsicon,
		showLink,
		showThumb,
		thumbUrl);  	
	
		
	return buildListItemForPlace(index, place, marker, excerpt, link, <?php if(wl_get_option('cat_map_select_excerpt') == 'on') { echo 'true'; } else { echo 'false';} ?>);	
}	


jQuery(document).ready(function(jQuery) {
	
	//parse json has been missing in some versions of jquery
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
	
	//basic size of the map
	jQuery('#map_canvas').height( 400 );
		
<?php if(wl_get_option('map_custom_style') != '') : ?>	

	var welocallyMapStyle = <?php printf(wl_get_option("map_custom_style"))  ?>;
	
	var mapOptions = {
		zoom : 16,
		center : latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		styles: welocallyMapStyle
	};
  
    wl_map_main = new google.maps.Map(document.getElementById("map_canvas"),
        mapOptions);
        
    WELOCALLY.places.map.setMapEvents(wl_map_main);
    
   



<?php else:?>  	
  	
	var mapOptions = {
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    wl_map_main = new google.maps.Map(document.getElementById("map_canvas"),
        mapOptions);
        
    WELOCALLY.places.map.setMapEvents(wl_map_main);
    

<?php  endif; ?>
	//the loop
   	var places = new Array();
	var showLink = true;    
	
<?php 
$index = 0;
$cat_ID = get_query_var( 'cat' );
$places_in_category_posts = get_places_posts_for_category($cat_ID);

foreach( $places_in_category_posts as $post ):
    $places = get_post_places($post->ID);
    
    foreach ($places as $place):
?>  
<?php $index=$index+1; ?>	
	
	var showThumb<?php echo $index; ?>= false;
	var thumbImage<?php echo $index; ?> = '';

<?php if (has_post_thumbnail( $post->ID )) : ?>
	showThumb<?php echo $index; ?> = true;
<?php $image = 
           wp_get_attachment_image_src( 
           		get_post_thumbnail_id( $post->ID ), 
             	'thumbnail' ); ?>
    thumbImage<?php echo $index; ?> = '<?php echo $image[0]; ?>';
<?php endif; ?>	
	

	places[<?php echo $index; ?>] = <?php echo json_encode($place); ?>;	
	var latlng = new google.maps.LatLng(places[<?php echo $index; ?>].geometry.coordinates[1], places[<?php echo $index; ?>].geometry.coordinates[0]);
	bounds.extend(latlng);
	
	/*
	index, 
	place, 
	wl_map_main,  
	image, 
	title, 
	link, 
	excerpt,
	webicon,
	directionsicon
	*/
	var item =  addItemForCategoryPlace(
		<?php echo $index; ?>, 
		places[<?php echo $index; ?>], 
		wl_map_main,
		'<?php echo wl_get_option("map_default_marker") ?>',
		'<?php echo str_replace("'", "\'",$post->post_name); ?>',
		'<?php the_permalink(); ?>',
		'<?php echo str_replace("'", "\'",wl_get_post_excerpt( $post->ID )); ?>',	
		'<?php echo wl_get_option("map_icon_web"); ?>',
		'<?php echo wl_get_option("map_icon_directions"); ?>',
		showLink,
		showThumb<?php echo $index; ?>,
		thumbImage<?php echo $index; ?>
		);
		
	<?php if(wl_get_option("cat_map_select_show") == 'on'  ) { 	?>
	jQuery("#selectable").append(item.content);
	<?php } ?>
	
	items[<?php echo $index; ?>] = item;
	
	
	jQuery("#item<?php echo $index; ?>")
	.mouseover(function() {
		jQuery(this).attr('class', 'ui-widget-content ui-selected select-box');
	})
	.mouseout(function(){
	  	var index = this.id.replace("item","");
	  	if(lastindex == null || index != lastindex){
			jQuery(this).attr('class', 'ui-widget-content un-select-box');
		}
	});

	
<?php
    endforeach;
endforeach;
?>		

	//init map with bounds   
    if(<?php echo $index; ?>==1){
    	wl_map_main.setCenter(latlng);
    	wl_map_main.setZoom(14);
    } else {
    	wl_map_main.fitBounds(bounds);
    }
    
<?php if(wl_get_option('cat_map_select_show') == 'on') : ?>
	jQuery( "#selectable" ).selectable({
		   selected: function(event, ui) { 
		   		
		   		var index = ui.selected.id.replace("item","");
		   		var selectedItem = items[index];
		   		var placeLatLng = new google.maps.LatLng(selectedItem.place.geometry.coordinates[1], selectedItem.place.geometry.coordinates[0]);
		   		wl_map_main.panTo(placeLatLng);
		   				   	
		   		var contentsBox = jQuery(document.createElement('div'));
					
		   		var contents = buildContentForInfoWindow(
		   			WELOCALLY.places.map.infobox.baseWidth,
					selectedItem.place, ",", 
					selectedItem.marker.webicon, 
					selectedItem.marker.directionsicon,
					selectedItem.marker.linkedTitle,
					selectedItem.marker.linkUrl,
					selectedItem.marker.showThumb,
					selectedItem.marker.thumbUrl,
					WELOCALLY.places.map.infobox.thumbMaxSize);
			
				jQuery(contentsBox).html(contents);
				
				boxText.innerHTML = contents;
				
				WELOCALLY.places.map.infobox.setOffset(contentsBox,ib);
		
				ib.open(wl_map_main, selectedItem.marker);
				ib.show();
				ib_widget.hide();
				
				jQuery('#info-contents-box ul li a').css('background','none').css('padding','0px');
							
				lastindex= index;
		   },
		   cancel: ":input,option,a"
	});
<?php endif; ?>	

	jQuery('.wl-item-article' ).mouseover(function() {
			jQuery(this).css('cursor', 'pointer');
	  	});
	  	
	jQuery('.wl-item-article' ).click(function() {
	  		var index = this.id.replace('wl-item-article','');
			var selectedItem = items[index];
			window.location = selectedItem.link;
	});	  		
	
	//fix the jquery ui issue
	jQuery('.ui-widget-content a').css(
		{'font-family' : '<?php echo wl_get_option("font_place_name", "Sorts Mill Goudy"); ?>', 
		'color': '#<?php echo wl_get_option("color_place_name", "000000"); ?>'});
	
	
});

</script>