<?php
 global $wlPlaces;
 $cat = $wlPlaces->placeCategory();

		
$options = $wlPlaces->getOptions();
$custom_style=null;
if(class_exists('WelocallyPlacesCustomize' ) && isset($options[ 'map_custom_style' ])  && $options[ 'map_custom_style' ]!=''){
	$custom_style = stripslashes($options[ 'map_custom_style' ]);
}
 
?> 
 
 
 <div class="wl_category_container">  
    <div id="main">
      <script type="text/javascript">
//<![CDATA[
		var catId = <?php echo($cat); ?>;
		
      	var placeSelected = new WELOCALLY_PlaceWidget({}).init();
	    var cfg = { 
				id:'multi_1',
				showLetters: true,
				imagePath:'<?php echo(WP_PLUGIN_URL.'/welocally-places/resources'); ?>/images',
		    	endpoint:'http://stage.welocally.com',
		    	showSelection: true,
		    	observers:[placeSelected],
		    	<?php if(isset($custom_style)):?> styles:<?php echo($custom_style); endif;?>,
				places: <?php echo(get_places_for_category($cat));  ?>
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
