 <?php
 global $wlPlaces;
 $cat = $wlPlaces->placeCategory();
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
