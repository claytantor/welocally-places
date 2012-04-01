<?php 
global $post, $wlPlaces; 
$options = $wlPlaces->getOptions();

$marker_image_path = WP_PLUGIN_URL.'/welocally-places/resources/images/marker_all_base.png' ;
if(class_exists('WelocallyPlacesCustomize' ) && isset($options[ 'map_default_marker' ])  && $options[ 'map_default_marker' ]!=''){
	$marker_image_path = $options[ 'map_default_marker' ];
}

?>
<body>
	<div>
		<div id="wl-addplace" class="input-section action" style="display:inline-block" >
			<script type="text/javascript">
var cfg = { 
	showShare: true,
	<?php if(isset($options['api_endpoint'])):?> endpoint:<?php echo('\''.$options['api_endpoint'].'\''.','); endif;?>	
	imagePath:'<?php echo($marker_image_path); ?>',
	<?php if(isset($options['category_selector_override'])):?> overrideSelectableStyle:<?php echo('\''.$options['category_selector_override'].'\''.','); endif;?>	
	<?php if(wl_get_option('siteKey',null) != null):?>siteKey: '<?php echo(wl_get_option('siteKey',null)); ?>',<?php endif;?>
	<?php if(wl_get_option('siteToken',null) != null):?>siteToken: '<?php echo(wl_get_option('siteToken',null)); ?>',<?php endif;?>	
};
var addPlaceWidget = new WELOCALLY_AddPlaceWidget(cfg).init();
		     </script>		
		</div>		
	</div>
</body>