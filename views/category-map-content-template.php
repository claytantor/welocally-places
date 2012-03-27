<?php
global $wlPlaces;
		
$options = $wlPlaces->getOptions();
$custom_style=null;
if(class_exists('WelocallyPlacesCustomize' ) && isset($options[ 'map_custom_style' ])  && $options[ 'map_custom_style' ]!=''){
	$custom_style = stripslashes($options[ 'map_custom_style' ]);
}
 
?>
 
 <div class="wl_category_container"> 
    <div id="wl_category_map">
      <script type="text/javascript">
//<![CDATA[
		var catId = <?php echo $t->catId; ?>;
		
      	var placeSelected = new WELOCALLY_PlaceWidget({}).init();
	    var cfg = { 
				id:'multi_<?php echo $t->uid; ?>',
				showLetters: true,
				imagePath:'<?php echo(WP_PLUGIN_URL.'/welocally-places/resources'); ?>/images',
		    	endpoint:'http://stage.welocally.com',
		    	showSelection: true,
		    	observers:[placeSelected],
			<?php if(isset($custom_style)):?> styles:<?php echo($custom_style); endif;?>,
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
