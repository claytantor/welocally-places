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
