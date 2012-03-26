 <div class="wl_category_container">  
    <div id="main">
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
