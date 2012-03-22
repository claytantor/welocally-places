<script type="text/javascript">
jQuery(document).ready(function() {	
	

});
</script>

<div id="wl-place-content-<?php echo $t->uid; ?>" class="wl-place-content">
	<div class="template-wrapper">
		<div>
		<script type="text/javascript" charset="utf-8">
		var place<?php echo $t->uid; ?> = <?php echo $t->placeJSON; ?>;
		var cfg = { id:  place<?php echo $t->uid; ?>._id, endpoint:'http://stage.welocally.com', showShare: false};
			    		
		var placeWidget<?php echo $t->uid; ?> = 
			  new WELOCALLY_PlaceWidget(cfg)
		  		.init();
		placeWidget<?php echo $t->uid; ?>.loadLocal(place<?php echo $t->uid; ?>); 	 
		
		</script>
		</div>
	</div>

</div>
