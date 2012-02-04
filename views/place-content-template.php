<div id="wl-place-content-<?php echo $t->uid; ?>" class="wl-place-content">
	<div class="map_canvas_post"></div>
	<div class="wl-place-name" id="place-name-<?php echo $t->uid; ?>"></div>
	<div class="wl-place-address" id="place-address-<?php echo $t->uid; ?>"></div>
	<div class="wl-place-phone" id="place-phone-<?php echo $t->uid; ?>"></div>
	
	<ul>
		<li class="wl-place-links-lines" >
			<span class="wl-place-website" id="place-website-<?php echo $t->uid; ?>"></span>
		</li>
		<li class="wl-place-links-lines" >
			<span class="wl-place-driving" id="place-driving-<?php echo $t->uid; ?>"></span>
		</li>
	</li>
</div>
<script type="text/javascript" charset="utf-8">
    WLPlaces.insertPlace('wl-place-content-<?php echo $t->uid; ?>',
                         <?php echo $t->placeJSON; ?>,
                         <?php echo json_encode($t->options); ?>);
</script>