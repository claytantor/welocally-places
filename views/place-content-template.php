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


$endpoint = 'https://api.welocally.com';
if(isset($options[ 'api_endpoint' ]) && $options[ 'api_endpoint' ] !=''){
	$endpoint = $options[ 'api_endpoint' ];
}

?>
<div id="wl-place-content-<?php echo $t->uid; ?>" class="wl-place-content">
	<div class="template-wrapper">
		<div>
<script type="text/javascript" charset="utf-8">
var place<?php echo $t->uid; ?> = <?php echo $t->placeJSON; ?>;
var cfg = { 
	id:  place<?php echo $t->uid; ?>._id, 
	imagePath:'<?php echo($marker_image_path); ?>', 
	endpoint:'<?php echo($endpoint); ?>', 
	<?php if(isset($custom_style)):?> styles:<?php echo($custom_style.','); endif;?>
	showShare: false,
	placehoundPath: 'http://placehound.com'
};
var placeWidget<?php echo $t->uid; ?> = 
	  new WELOCALLY_PlaceWidget(cfg)
		.init();
placeWidget<?php echo $t->uid; ?>.load(place<?php echo $t->uid; ?>); 	 		
</script>
		</div>
	</div>

</div>
