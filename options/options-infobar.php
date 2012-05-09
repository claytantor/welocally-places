<?php
global $wlPlaces;
$options = $wlPlaces->getOptions();
?>
<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function() {
	jQuery( "input:submit, a, button", ".action" ).button();
		
});
</script>
<div id="wl-options-error" class="wl-places-error error" style="width:97%"></div>
<div id="helpbar" style="width:97%">
	<table width="100%">
		<tr>
			<td align="left">
				<div class="options-text">We really want to make sure Welocally Places is a great product, so if you have <strong>any</strong> issues or suggestions we want to <a href="http://www.welocally.com/?page_id=139">hear from you</a>.</div>
			</td>
			<td align="right">
					<div style="width: 400px;" class="action">
						<a href="http://www.welocally.com/?page_id=104" target="_blank">Get Help <img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/help_32.png" alt="" title=""/></a>
						<a href="http://www.welocally.com/?page_id=139" target="_blank">Contact Us <img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/contact_32.png" alt="" title=""/></a>
						
						<?php if(is_subscribed()) {?>
						<a href="<?php echo wl_server_base().'/admin/home'?>" target="_blank">Login <img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/login_home_32.png" alt="" title=""/></a>
						<?php } ?>
					</div>						
			</td>
		</tr>
	</table>	
</div>
<div id="welocally-content"></div>