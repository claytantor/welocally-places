<?php 
global $post, $wlPlaces; 
$options = $wlPlaces->getOptions();
$marker_image_path = WP_PLUGIN_URL.'/welocally-places/resources/images/marker_all_base.png' ;
?>

<body>
	<div>
		<div id="wl-addplace" class="input-section action" style="display:inline-block" >
			<script type="text/javascript">
var addPlaceWidget = new WELOCALLY_AddPlaceWidget({ 
	showShare: true,
	imagePath:'<?php echo($marker_image_path); ?>',
}).init();
		     </script>		
		</div>		
	</div>
</body>
