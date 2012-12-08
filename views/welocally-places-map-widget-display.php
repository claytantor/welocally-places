<?php
 /*
	Copyright 2012 clay graham, welocally & RateCred Inc.

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
global $wlPlaces; 
$options = $wlPlaces->getOptions();
$custom_style=$wlPlaces->getThemeMapStyle();
$marker_image_path = WP_PLUGIN_URL.'/welocally-places/themes/'.$options[ 'places_theme' ].'/images/marker_all_base.png' ;
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
