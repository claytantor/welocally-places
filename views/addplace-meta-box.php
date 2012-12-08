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
global $post, $wlPlaces; 
$options = $wlPlaces->getOptions();
$marker_image_path = WP_PLUGIN_URL.'/welocally-places/resources/images/marker_all_base.png' ;
?>

<body>
	<div>
		<div id="wl-addplace" class="input-section action" style="display:inline-block" >
			<script type="text/javascript">
var addPlaceWidget = new WELOCALLY_AddPlaceWidget({ 
	showShare: true,
	imagePath:'<?php echo($marker_image_path); ?>',
}).init();
		     </script>		
		</div>		
	</div>
</body>
