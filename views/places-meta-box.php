<?php global $post; ?>
<script type="text/javascript">
jQuery(document).ready(function(jQuery) {	
	console.log('place finder ready');
});
</script>
<style type="text/css">
	
	
	
</style>
<body>
	<div>
		<div id="wl-place-finder" class="input-section action" style="display:inline-block" >
			<script type="text/javascript">
				
				jQuery('#wl-place-finder-meta-1').find('.handlediv').click(function(){
					console.log('finder toggle clicked');
					if(jQuery('#wl-place-finder-meta-1').is(':visible')){
						jQuery(WELOCALLY.PlaceFinderWidget._map).hide();
						setTimeout('WELOCALLY.PlaceFinderWidget.refreshMap()', 100);
					}	
																						
				});
			
		      	WELOCALLY.PlaceFinderWidget({ showSelection:'true', imagePath:'<?php echo(WP_PLUGIN_URL.'/welocally-places/resources/'); ?>/images' }); 
		     </script>		
		</div>		
	</div>
</body>