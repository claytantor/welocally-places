<?php 
/**
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 * @return string
 */
global $wlPlaces;
$MapPlaceSelected	= get_post_meta( $post->ID, '_PlaceSelected', true );

$places_map_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/places-map-include.php';
include($places_map_include);

$infobox_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/infobox-map-include.php';
include($infobox_include);

?>


<aside class="widget sidebar-item">
<h3 class="widget-title"><?php echo $title; ?></h3>
<div id="map_widget_container">
	<div id="map_canvas_widget"></div>
	<div id="place-details-area">
		<div id="sp-click-action-call" class="simple_grey_italic text-align-right">click to select place</div>
		<div id="place-details">
			<div class="wl-place-name wl-place-widget-name" id="details-place-name"></div>
			<div class="wl-place-excerpt" id="details-place-excerpt"></div>				
		</div>				
	</div>

</div>
</aside>

