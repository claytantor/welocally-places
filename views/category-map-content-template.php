<div id="category-map-<?php echo $t->uid; ?>" class="wl-map wl-category-map <?php echo is_archive() ? 'in-archive' : ''; ?>">
	<div class="map-title">
	<?php if (is_archive()): ?>
		<h1 class="entry-title"><?php echo $t->category->name; ?></h1>
	<?php else: ?>
		<span><?php echo $t->category->name; ?></span>
	<?php endif; ?>
	</div>
	<div class="map-all"></div>
	<div class="map-canvas"></div>
	<div class="map-items">
		<ol class="selectable"></ol>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function(jQuery) {	

	var config = {
		showExcerpts: <?php echo wl_get_option('cat_map_select_excerpt') == 'on' ? 'true' : 'false'; ?>,
		showSelectBoxes: <?php echo wl_get_option('cat_map_select_show') == 'on' ? 'true' : 'false'; ?>,
		mapOptions: null,
	};

<?php if(wl_get_option('map_custom_style') != '') : ?>	
	var welocallyMapStyle = <?php printf(base64_decode(wl_get_option("map_custom_style")));  ?>;

	// Create a new StyledMapType object, passing it the array of styles,
  	// as well as the name to be displayed on the map type control.
  	var styledMapType = new google.maps.StyledMapType(welocallyMapStyle, {name: "Custom"});
  	
  	var mapOptions = {
      mapTypeControlOptions: {
      	mapTypeIds: ['welocally_style']
      }
    };

    config.mapOptions = mapOptions;
    
	var map = new WELOCALLY.places.CategoryMap('category-map-<?php echo $t->uid; ?>', config);
	var wl_map_main = map.map;

    //Associate the styled map with the MapTypeId and set it to display.
  	wl_map_main.mapTypes.set('welocally_style', styledMapType);
  	wl_map_main.setMapTypeId('welocally_style');
<?php else: ?>
	var mapOptions = {
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    config.mapOptions = mapOptions;

    var map = new WELOCALLY.places.CategoryMap('category-map-<?php echo $t->uid; ?>', config);
<?php  endif; ?>
	
<?php 
foreach( $t->places as $place_ ):
	$place = $place_->place;
	$post = $place_->posts[0];
?>  
	<?php
	$post_thumbnail = null;
	if (has_post_thumbnail($post->ID)) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id ($post->ID), 'thumbnail' );
		$post_thumbnail = $img[0];
	}
	?>

	map.add(<?php echo json_encode($place); ?>,
			{
				marker: '<?php echo wl_get_option('map_default_marker'); ?>',
				title: '<?php echo str_replace("'", "\'",$post->post_name); ?>',
				link: '<?php echo get_permalink($post->ID); ?>',
				excerpt: '<?php echo str_replace("'", "\'",wl_get_post_excerpt( $post->ID )); ?>',	
				webicon: '<?php echo wl_get_option("map_icon_web"); ?>',
				directionsicon: '<?php echo wl_get_option("map_icon_directions"); ?>',
				showLink: true,
				showThumb: <?php echo has_post_thumbnail($post->ID) ? 'true' : 'false'; ?>,
				thumbnail: <?php echo json_encode($post_thumbnail); ?>
			}
			);
<?php endforeach; ?>
	map.done();
	
	//fix the jquery ui issue
	jQuery('.ui-widget-content a').css(
		{'font-family' : '<?php echo wl_get_option("font_place_name", "Sorts Mill Goudy"); ?>', 
		'color': '#<?php echo wl_get_option("color_place_name", "000000"); ?>'});
	
});
</script>