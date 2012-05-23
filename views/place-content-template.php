<?php
global $wlPlaces;
$options = $wlPlaces->getOptions();

$custom_style=null;
if(class_exists('WelocallyPlacesCustomize' ) && isset($options[ 'map_custom_style' ])  && $options[ 'map_custom_style' ]!=''){
	$custom_style = stripslashes($options[ 'map_custom_style' ]);
}
 

$marker_image_path = WP_PLUGIN_URL.'/welocally-places/resources/images/marker_all_base.png' ;
if(class_exists('WelocallyPlacesCustomize' ) && isset($options[ 'map_default_marker' ])  && $options[ 'map_default_marker' ]!=''){
	$marker_image_path = $options[ 'map_default_marker' ];
}

$endpoint = 'https://api.welocally.com';
if(isset($options[ 'api_endpoint' ]) && $options[ 'api_endpoint' ] !=''){
	$endpoint = $options[ 'api_endpoint' ];
}

?>
<div id="wl-place-content-<?php echo $t->uid; ?>" class="wl-place-content">
	<div class="template-wrapper">
		<div>
<script type="text/javascript" charset="utf-8">
var place<?php echo $t->uid; ?> = <?php echo $t->placeJSON; ?>;
var cfg = { 
	id:  place<?php echo $t->uid; ?>._id, 
	imagePath:'<?php echo($marker_image_path); ?>', 
	endpoint:'<?php echo($endpoint); ?>', 
	<?php if(isset($custom_style)):?> styles:<?php echo($custom_style.','); endif;?>
	showShare: false,
	placehoundPath: 'http://placehound.com'
};
var placeWidget<?php echo $t->uid; ?> = 
	  new WELOCALLY_PlaceWidget(cfg)
		.init();
placeWidget<?php echo $t->uid; ?>.load(place<?php echo $t->uid; ?>); 	 		
</script>
		</div>
	</div>

</div>
