<?php
global $wlPlaces;
$cat = $wlPlaces->placeCategory();
$options = $wlPlaces->getOptions();
$custom_style=null;
if(class_exists('WelocallyPlacesCustomize' ) && isset($options[ 'map_custom_style' ])  && $options[ 'map_custom_style' ]!=''){
	$custom_style = stripslashes($options[ 'map_custom_style' ]);
}
 
$marker_image_path = WP_PLUGIN_URL.'/welocally-places/resources/images/marker_all_base.png' ;
if(class_exists('WelocallyPlacesCustomize' ) && isset($options[ 'map_default_marker' ])  && $options[ 'map_default_marker' ]!=''){
	$marker_image_path = $options[ 'map_default_marker' ];
}

$endpoint = 'https://api.welocally.com';
if(isset($options[ 'api_endpoint' ]) && $options[ 'api_endpoint' ] !=''){
	$endpoint = $options[ 'api_endpoint' ];
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
				overrideSelectableStyle: 'width: 98%',
				imagePath:'<?php echo($marker_image_path); ?>',
		    	endpoint:'<?php echo($endpoint); ?>',
		    	showSelection: true,
		    	observers:[placeSelected],
		    	<?php if(isset($custom_style)):?> styles:<?php echo($custom_style.','); endif;?>
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
