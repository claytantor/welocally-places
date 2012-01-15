<?php
global $wlPlaces;
$wlPlaces->loadDomainStylesScripts();
global $wp_query;
$wlecCatObject = get_category( $wp_query->query_vars['cat'])
?>
<?php get_header(); ?>
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
.wl-left-sidebar { width: 140px }
	
#wl-sidebar-1 {  width: 140px, display: inline-block;}	
#wl-map-content {  width: 700px, display: inline-block;}
/*-#wl-map-all { list-style-type: none; margin: 0; padding: 0; width: 100%; }
#wl-map-all li{ display:inline-block; }-*/

.title-selectable-place { 
		position:relative; 	
		top:0px;
		left:0px; 
		width:100%;
		z-index:200;}
	

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
<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css" rel="stylesheet" />			
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<script type="text/javascript" charset="utf-8">
var map = null;
var lastindex = null;
var items = [];

function Item (place, content, marker, link) {
    this.place = place;
   	this.content = content;
   	this.marker = marker;
   	this.link = link;
}

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

<?php while ( have_posts() ) : the_post();?> 

<?php $index=$index+1; ?>	
	//console.log('<?php echo $bodytag = str_replace("'", "\'", get_post_meta( $post->ID, '_PlaceSelected', true )); ?>');
	places[<?php echo $index; ?>] = jQuery.parseJSON( '<?php echo $bodytag = str_replace("'", "\'", get_post_meta( $post->ID, '_PlaceSelected', true )); ?>' );	
	
	var latlng = new google.maps.LatLng(places[<?php echo $index; ?>].geometry.coordinates[1], places[<?php echo $index; ?>]..geometry.coordinates[0]);
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
	var item =  addItemForPlace(
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
		
	jQuery("#selectable").append(item.content);
	items[<?php echo $index; ?>] = item;
	
	
	jQuery("#item<?php echo $index; ?>").mouseover(function() {
		jQuery(this).attr('class', 'ui-widget-content ui-selected select-box');
	  }).mouseout(function(){
	  	var index = this.id.replace("item","");
	  	if(lastindex == null || index != lastindex){
			jQuery(this).attr('class', 'ui-widget-content un-select-box');
		}
	  });


<?php endwhile; ?>   

	//init map with bounds   
    if(<?php echo $index; ?>==1){
    	map.setCenter(latlng);
    	map.setZoom(14);
    } else {
    	map.fitBounds(bounds);
    }


	jQuery( "#selectable" ).selectable({
		   selected: function(event, ui) { 
		   		var index = ui.selected.id.replace("item","");
		   		var selectedItem = items[index];
		   		var placeLatLng = new google.maps.LatLng(selectedItem.place.geometry.coordinates[1], selectedItem.place.geometry.coordinates[0]);
		   		map.panTo(placeLatLng);
				boxText.innerHTML = buildContentForInfoWindow(selectedItem.place, ",", selectedItem.marker.webicon, selectedItem.marker.directionsicon);
				ib.open(map, selectedItem.marker);
				lastindex= index;
		   },
		   cancel: ":input,option,a"
	});
	
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
			,closeBoxURL: "<?php echo wl_get_option('map_infobox_closebox') ?>"
			,infoBoxClearance: new google.maps.Size(1, 1)
			,isHidden: false
			,pane: "floatPane"
			,enableEventPropagation: false
		};
		
var ib = new InfoBox(infoboxOptions);

</script>
<div id="main">
<?php if(wl_get_option('cat_map_layout', 'right') == 'right' ) : ?>
	<div id="content-body">
			<div><h2><?php echo $wlecCatObject->name ?></h2></div>
				<div id="map_all"></div>
				<div id="map_canvas"></div>
				<div id="items">
					<ol id="selectable"></ol>
			    </div>
	</div>
	<div class="content-sidebar">	
		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar("Welocally Places 1") ) : ?>
		Default sidebar stuff here...
		<?php endif; ?>	
	</div>
<?php elseif(wl_get_option('cat_map_layout', 'right') == 'left' ) : ?>
	<div class="content-sidebar">	
		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar("Welocally Places 1") ) : ?>
		Default sidebar stuff here...
		<?php endif; ?>	
	</div>
	<div id="content-body">
			<div><h2><?php echo $wlecCatObject->name ?></h2></div>
				<div id="map_all"></div>
				<div id="map_canvas"></div>
				<div id="items">
					<ol id="selectable"></ol>
			    </div>
	</div>	
<?php else: ?>
	<div id="content-body">
			<div><h2><?php echo $wlecCatObject->name ?></h2></div>
				<div id="map_all"></div>
				<div id="map_canvas"></div>
				<div id="items">
					<ol id="selectable"></ol>
			    </div>
	</div>	
<?php endif; ?>
</div>		
	<?php /* For custom template builders...
		   * The following init method should be called before any other loop happens.
		   */
	$wp_query->init(); ?>
<?php get_footer(); ?>

