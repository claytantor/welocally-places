<div id="wl-place-content-<?php echo $t->uid; ?>" class="wl-place-content">
	<div class="map_canvas_post"></div>
	
	<div class="wl-place-name" id="place-name-<?php echo $t->uid; ?>"></div>
	<div class="wl-place-address" id="place-address-<?php echo $t->uid; ?>"></div>
	<div class="wl-place-phone" id="place-phone-<?php echo $t->uid; ?>"></div>	
	<ul class="wl-place-links">
		<li class="wl-place-links-lines" >
			<span class="wl-place-website" id="place-website-<?php echo $t->uid; ?>"></span>
		</li>
		<li class="wl-place-links-lines" >
			<span class="wl-place-driving" id="place-driving-<?php echo $t->uid; ?>"></span>
		</li>
		<li class="wl-place-links-lines" >
			<img width="16" height="16" 
			src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/we_16.png" 
			alt="" title=""/>&nbsp;<a id="share-link-<?php echo $t->uid; ?>" href="#">share</a>
		    <script>
			jQuery("#share-link-<?php echo $t->uid; ?>").click(function () {	
			  if(jQuery("#share-tag-<?php echo $t->uid; ?>").is(":visible")){
			  	jQuery("#share-tag-<?php echo $t->uid; ?>").hide(1000);
			  }	else {
			  	jQuery("#share-tag-<?php echo $t->uid; ?>").first().show("fast", function showNext() {
				    jQuery(this).next("#share-tag-<?php echo $t->uid; ?>").show("fast", showNext);
				  });			  	
			  }	
			  return false;
			});			
			</script>
		</li>
	</ul>
	<div>
		<div style="display:none" class="share-tag" id="share-tag-<?php echo $t->uid; ?>">
		<input type="text" name="place-tag-<?php echo $t->uid; ?>" id="place-tag-<?php echo $t->uid; ?>" value="[welocally/]" />
		</div>
	</div>
	
</div>
<script type="text/javascript" charset="utf-8">
    WLPlaces.insertPlace('wl-place-content-<?php echo $t->uid; ?>',
                         <?php echo $t->placeJSON; ?>,
                         <?php echo json_encode($t->options); ?>);
</script>