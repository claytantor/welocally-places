<?php
global $wlPlaces; 
$options = $wlPlaces->getOptions();
?> 
<div class="widget-title"></div>
 <div class="wl_category_container_widget">  
    <div id="main">
<script type="text/javascript">	
var placeSelected = new WELOCALLY_PlaceWidget({hidePlaceSectionMap: true}).init();
var cfg = { 
		id:'multi_<?php echo $t->uid; ?>',	
		<?php if($options['show_letters']=='on'):?> showLetters: true,<?php else: ?>showLetters: false,<?php endif;?>
		<?php if(!empty($options['widget_selection_style'])):?> overrideSelectableStyle:<?php echo('\''.$options['widget_selection_style'].'\''.',');?><?php else: ?>overrideSelectableStyle: 'width:98%;',<?php endif;?>
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
    placeSelected.initCfg(cfg);
	placesMulti.getSelectedArea().append(placeSelected.makeWrapper());           
</script>
    </div>
  </div>
  <div style="clear:both; margin-bottom:5px;">&nbsp;</div>
