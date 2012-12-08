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
?>
<div id="wl_action_bar">
	<div class="rollover" id="btn_support">
	<a href="http://support.welocally.com/categories/welocally-places-wp-basic" target="_new">
		<img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/btn_spacer.png">
		</a>
	</div>
	<div class="rollover" id="btn_contact">
	<a href="http://welocally.com/?page_id=139" target="_new">
		<img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/btn_spacer.png">
		</a>
	</div>
	<div class="rollover" id="btn_guide">
	<a href="http://welocally.com/?page_id=104" target="_new">
		<img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/btn_spacer.png">
		</a>
	</div>	
	<div style="display:inline-block; margin-right: 25px;">
		<img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/quick-help.png">
	</div>									
</div>
<?php
$reqsMissing = $wlPlaces->databaseRequirementsMissing();
if (count($reqsMissing)>0) {
	$showReqsMissing = print_r($reqsMissing,true);
	echo ('<div id="wl_info_bar"><div class="wl_error" style="margin 10px;">Reactivation is required to properly support required database changes for version '.WelocallyPlaces::VERSION.'. Please deactivate and re-activate now. '.$showReqsMissing.'</div></div>');             
}
?>	