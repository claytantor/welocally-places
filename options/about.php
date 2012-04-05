<?php
global $wlPlaces;
$options = $wlPlaces->getOptions();
?>
<script type="text/javascript">
jQuery(document).ready(function() {
		
	//set the endpoint info always	
	jQuery("#edit-server-link").click( function () {	
		jQuery("#edit-server-link").hide();
		jQuery("#api-endpoint-assigned").hide();
		jQuery("#wl_api_endpoint").show();
		
		return false;
	});
});

</script>

<div class="wrap">

<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
<h2>Welocally Places About</h2>
<?php 
$menubar_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/options/options-infobar.php';
include($menubar_include);
?>
<?php if(empty($options['siteToken'])  ):?>
<div class="wl_error fade"><p><strong>Please <a href="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-subscribe' ?>">Register Now</a> To Activate Welocally Places</strong></p></div>
<?php endif; ?>

<?php
// If options have been updated on screen, update the database
if ( ( !empty( $_POST ) ) && ( check_admin_referer( 'welocally-places-about', 'welocally_places_about_nonce' ) ) ) { 

	$options = wl_get_options();

	$options[ 'api_endpoint' ] = $_POST[ 'wl_api_endpoint' ];
	$options[ 'update_places' ] = $_POST[ 'wl_update_places' ];
	
	wl_save_options($options);

	echo '<div class="updated fade"><p><strong>' . __( 'Settings Saved.' ) . "</strong></p></div>\n";
}
?>
<form method="post" action="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-about' ?>">
<fieldset>
<span class="wl_options_heading"><?php _e( 'Plugin Info' ); ?></span>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e( 'Version' ); ?></th>
		<td><div><?php echo $wlPlaces->wl_places_version();  ?></div></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Places Version Support' ); ?></th>	
		<td>			
		<?php if(get_places_legacy_count() > 0): ?>	
		<div><img width="48" height="48" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_cancel.png" alt="" title=""/></div>
		<div>You have recenty upgraded Welocally Places. <?php echo(get_places_legacy_count());  ?> Legacy Posts were found. You can use the <a href="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-manager' ?>">Place Manager</a> 
		to track your migration. Please read the <a href="http://www.welocally.com/?page_id=104" target="_blank">help documentation</a> when upgrading. If you have problems <a href="http://www.welocally.com/?page_id=139" target="_new">email us</a>. 
		<p/><strong>ALWAYS BACKUP PRIOR TO UPGRADE</strong></div>
		<?php else: ?>
		<img width="48" height="48" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_check.png" alt="" title=""/>	Your places are up to date. 	
		<?php endif; ?>
		</td>
	</tr>	
	<tr valign="top">
		<th scope="row"><?php _e( 'CURL Installed' ); ?></th>
		<td>
		<?php if(welocally_is_curl_installed()): ?>
			<img width="48" height="48" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_check.png" alt="" title=""/>
		<?php else: ?>
			<img width="48" height="48" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_cancel.png" alt="" title=""/>			
		<?php endif; ?>
		</td>
	</tr>	
	<tr valign="top">
		<th scope="row"><?php _e( 'API Server' ); ?></th>
		<td>
			<div id="server-section" class="action">
				<span id="api-endpoint-assigned" class="assigned-field" style="margin-right:10px;"><?php echo wl_get_option('api_endpoint',null) ?></span><button id="edit-server-link" href="#">Change</button>
				<input type="text" name="wl_api_endpoint" id="wl_api_endpoint" value="<?php echo wl_get_option('api_endpoint',null) ?>" /></div>
			</div>	
		</td>
	</tr>
	
</table>

<div>
	<?php wp_nonce_field( 'welocally-places-about','welocally_places_about_nonce', true, true ); ?>
	<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Settings' ); ?>"/></p>		
</div>


</form>
				
</div> <!--wrap end-->