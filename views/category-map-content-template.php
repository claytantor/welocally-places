<?php
global $wlPlaces; 
$options = $wlPlaces->getOptions(); 
?>
 <div class="wl_category_container_tag"> 
    <div id="wl_category_map">
      <script type="text/javascript">
//<![CDATA[
		
      	var placeSelected = new WELOCALLY_PlaceWidget({}).init();
	    var cfg = { 
				id:'multi_<?php echo $t->uid; ?>',
				showLetters: true,
				<?php if(isset($options['category_selector_override'])):?> overrideSelectableStyle:<?php echo('\''.$options['category_selector_override'].'\''.','); endif;?>
				imagePath:'<?php echo($marker_image_path); ?>',
		    	endpoint:'<?php echo($endpoint); ?>',
		    	showSelection: true,
		    	observers:[placeSelected],
				<?php if(isset($custom_style)):?> styles:<?php echo($custom_style.','); endif;?>
				places: <?php echo json_encode($t->places); ?>
	    };
	    var placesMulti = 
			  new WELOCALLY_PlacesMultiWidget(cfg)
		  		.init();
  		
  		//now register the display for the place
	    placeSelected.setWrapper(cfg, jQuery(placesMulti._selectedSection));	          
	 
      //]]>
      </script>
    </div>
  </div>
