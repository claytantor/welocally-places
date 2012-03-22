<?php global $post; ?>
<script type="text/javascript">
jQuery(document).ready(function(jQuery) {	

});
</script>
<style type="text/css">
	
	
	
</style>
<body>
	<div>
		<div id="wl-addplace" class="input-section action" style="display:inline-block" >
			<script type="text/javascript">
var cfg = { endpoint: 'http://stage.welocally.com', imagePath:'<?php echo(WP_PLUGIN_URL.'/welocally-places/resources/'); ?>/images'};
<?php if(wl_get_option('siteKey',null) != null):?>
cfg.siteKey = '<?php echo(wl_get_option('siteKey',null)); ?>';
<?php endif;?>
<?php if(wl_get_option('siteToken',null) != null):?>
cfg.siteToken = '<?php echo(wl_get_option('siteToken',null)); ?>';
<?php endif;?>

 var addPlaceWidget = new WELOCALLY_AddPlaceWidget(cfg).init();
		     </script>		
		</div>		
	</div>
</body>