<?php
global $wlPlaces; 
$options = $wlPlaces->getOptions(); 
?>
 <div class="wl_category_container_tag"> 
    <div id="wl_category_map">
      <script type="text/javascript">
//<![CDATA[
		
      	var placeSelected = new WELOCALLY_PlaceWidget({hidePlaceSectionMap: true}).init();
	    var cfg = { 
				id:'multi_<?php echo $t->uid; ?>',
				hideDistance: true, 				
				<?php if($options['show_letters_tag']=='on'):?> showLetters: true,<?php else: ?>showLetters: false,<?php endif;?>
				overrideSelectableStyle: 'margin: 3px; padding: 2px; float: left; width: 175px; height: 70px;',
				imagePath:'<?php echo($marker_image_path); ?>',
		    	endpoint:'<?php echo($endpoint); ?>',
		    	<?php if($options['show_selection_tag']=='on'):?>showSelection: true,<?php else: ?>showSelection: false,<?php endif;?>
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
