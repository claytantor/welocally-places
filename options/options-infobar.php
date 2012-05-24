<?php
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