<?php
global $wlPlaces;

$menubar_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/options/options-infobar.php';
include($menubar_include);

// If options have been updated on screen, update the database
if ( ( !empty( $_POST ) ) && ( check_admin_referer( 'welocally-places-subscribe', 'welocally_places_subscribe_nonce' ) ) ) { 
	
	$options = wl_get_options();
	
	if($_POST[ 'siteName' ]=='delete'){
		error_log('delete tokens:'.$_POST[ 'siteName' ]);
		delete_subscription_options();
	} else {
		$options = wl_get_options();
		
		$options[ 'siteName' ] = $_POST[ 'siteName' ];
		$options[ 'siteHome' ] = $_POST[ 'siteHome' ];
		$options[ 'siteEmail' ] = $_POST[ 'siteEmail' ];
		$options[ 'siteToken' ] = $_POST[ 'siteToken' ];
		$options[ 'siteKey' ] = $_POST[ 'siteKey' ];
		
		$json_register_request = json_encode($_POST);
		$mapperResult = json_decode(welocally_register($json_register_request));
		$result = json_decode($mapperResult->mapperResult);
		
		echo '<div id="registration_result">'.print_r($result->errors,true).'</div>';			

		wl_save_options($options);	
	}
	
	if(isset($result->errors)){
		echo '<div class="error fade"><p><strong>' . __( 'Error.' ) . "</strong></p></div>\n";
	} else {
		echo '<div class="updated fade"><p><strong>' . __( 'Settings Saved.' ) . "</strong></p></div>\n";
	}
}

?>
<script type="text/javascript">


jQuery(document).ready(function() {
	var registrationWidget = 
		new WELOCALLY_RegisterWidget({
			<?php if(isset($options['siteName'])):?>siteName:<?php echo('\''.$options['siteName'].'\''.','); endif;?>	
			<?php if(isset($options['siteHome'])):?>siteHome:<?php echo('\''.$options['siteHome'].'\''.','); endif;?>	
			<?php if(isset($options['siteEmail'])):?>siteEmail:<?php echo('\''.$options['siteEmail'].'\''.','); endif;?>	
			<?php if(isset($options['siteKey'])):?>siteKey:<?php echo('\''.$options['siteKey'].'\''.','); endif;?>	
			<?php if(isset($options['siteToken']) && $options['siteToken'] != "null"):?>siteToken:<?php echo('\''.$options['siteToken'].'\''.','); endif;?>	
			<?php if(isset($options['api_endpoint'])):?>endpoint:<?php echo('\''.$options['api_endpoint'].'\''.','); endif;?>			
			wrapper: jQuery('#wl_registration_form_wrapper'),
			registeredCallback: wl_register_callback,
			imagePath: '<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images'
			}).init();
	
});

function wl_register_callback(site){
	WELOCALLY.util.log('callback worked');
		
	site.action = 'register';

	jQuery.ajax({
	  type: 'POST',
	  url: ajaxurl,
	  data: site,
	  success: function(response) {
	  }
	});
	 		
	return false;
}

function wl_remove_token(){
	var data = {
		action: 'remove_token'
	};
	jQuery.ajax({
	  type: 'POST',
	  url: ajaxurl,
	  data: data,
	  success: function(response) {
	  }
	});
}

</script>

<div class="wrap">
	<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
	<h2>Welocally Places Registration</h2>
	
	<div class="snp_settings wrap">
	
		<div id="wl-options-error" class="wl-places-error error"></div>
		<div id="wl_registration_form_wrapper">
					
		</div><!-- form -->
	</div>
 
</div> <!-- snp_settings wrap -->
