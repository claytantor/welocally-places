<?php
global $wlPlaces; 
$options = $wlPlaces->getOptions(); 
?>
 <div class="wl_category_container_tag"> 
    <div id="wl_category_map">
<script type="text/javascript">
var placeSelected = new WELOCALLY_PlaceWidget({hidePlaceSectionMap: true}).init();
var cfg = { 
		id:'multi_<?php echo $t->uid; ?>',
		hideDistance: true, 				
		<?php if($options['show_letters_tag']=='on'):?> showLetters: true,<?php else: ?>showLetters: false,<?php endif;?>
		<?php if(!empty($options['tag_selection_style'])):?> overrideSelectableStyle:<?php echo('\''.$options['tag_selection_style'].'\''.',');?><?php else: ?>overrideSelectableStyle: 'margin: 3px; padding: 2px; float: left; width: 150px; height: 60px;',<?php endif;?>
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
    placeSelected.initCfg(cfg);
	placesMulti.getSelectedArea().append(placeSelected.makeWrapper());         
</script>
    </div>
  </div>
    <div style="clear:both; margin-bottom:5px;">&nbsp;</div>
