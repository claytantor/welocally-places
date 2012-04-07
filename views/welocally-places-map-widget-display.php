<?php
global $wlPlaces; 
$options = $wlPlaces->getOptions();
?> 
<div class="widget-title"></div>
 <div class="wl_category_container_widget">  
    <div id="main">
      <script type="text/javascript">
//<![CDATA[
				
      	var placeSelected = new WELOCALLY_PlaceWidget({placehoundPath: 'http://placehound.com'}).init();
	    var cfg = { 
				id:'multi_<?php echo $t->uid; ?>',
				placehoundPath: 'http://placehound.com',		
				<?php if($options['show_letters']=='on'):?> showLetters: true,<?php else: ?>showLetters: false,<?php endif;?>
				<?php if(isset($options['widget_selector_override'])):?> overrideSelectableStyle:<?php echo('\''.$options['widget_selector_override'].'\''.',');?><?php else: ?>overrideSelectableStyle: 'width:98%;',<?php endif;?>
				imagePath:'<?php echo($marker_image_path); ?>',
		    	endpoint:'<?php echo($endpoint); ?>',
		    	<?php if($options['show_selection']=='on'):?>showSelection: true,<?php else: ?>showSelection: false,<?php endif;?>
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
