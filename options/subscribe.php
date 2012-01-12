<?php
global $wlPlaces;
?>
<script type="text/javascript">

<?php if(is_subscribed()):?>
var subscribed_saved = true;
<?php else: ?>
var subscribed_saved = false;
<?php endif; ?>

jQuery(document).ready(function() {

	wl_get_subscriber_info();
	
	jQuery("#offerCode").change( function () {	
		jQuery("#hosted_button_id").val(jQuery("#offerCode").val());
	});
	
	
});


function wl_set_cancelled_state_ajax(){
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

function wl_get_subscriber_info(){
	var data = {
		action: 'getkey'
	};
	
	data.siteHome = jQuery("#siteHome").val();
	data.siteName = jQuery("#siteName").val();
	data.siteDescription = '<?php echo get_bloginfo('description')?>';
	data.siteEmail = jQuery("#siteEmail").val();
	data.iconUrl = '';
			
	jQuery.ajax({
	  type: 'POST',
	  url: ajaxurl,
	  data: data,
	  dataType: 'json',
 	  error : function(jqXHR, textStatus, errorThrown) {
			jQuery('#wl-options-error').append('<div class="error fade">ERROR 100: '+
				textStatus+'</div>');
			jQuery("#publisher-status").html('ERROR 100: '+textStatus);	
	  },	  
	  success: function(keydata) {
		
		if(keydata.key != null){
			set_form_fields( keydata.key, keydata.token, null, keydata.subscriptionStatus, keydata.buttonToken, keydata.paypalFormEndpoint);
		} else {
			jQuery('#wl-options-error').append('<div class="error">ERROR 105: Key could not be created for publisher : '+data.siteName+'</div>');
			jQuery("#publisher-status").html(' ERROR 105: Errors prevent us from sending your key...<br/><ul>');
			jQuery.each(keydata.errors, function(index,error) {
		    	jQuery("#publisher-status").append('<li>' + error + '</li>');
		  	});
		  	jQuery("#publisher-status").append('</ul>');
		}
		
	  }
	});
}


function set_form_fields( key, token, serviceEndDateMillis, status, buttonToken, paypalEndpoint) {

	jQuery('#paypal-form').attr( 'action', paypalEndpoint);
	if(paypalEndpoint == 'https://www.paypal.com/cgi-bin/webscr'){
		jQuery('#paypal-subscribe-img').attr( 'src', 'https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif');
		jQuery('#paypal-pixel-img').attr( 'src', 'https://www.paypalobjects.com/en_US/i/scr/pixel.gif');
		
	}
	
	if(key != null) {
		jQuery("#paypal-custom").val(key);	
		jQuery("#key-assigned").html(key);	
		//set the form vals
		jQuery("#siteKey").val(key);
		
	}
	
	if(token != null){
		jQuery("#token-assigned").html(token);
		//welocally-places-display_token
		jQuery("#welocally-places-display_token").val(token);
		jQuery("#siteToken").val(token);
		//jQuery("#token-section").show();
		//jQuery("#save_options_button").show();
	}
	
	var subscribe_process1 = '<div><img src="<?php echo WP_PLUGIN_URL; ?>'+
		'/welocally-places/resources/images/subscribe_02.png" alt="" title=""/></div>';

	if(status != null) {
		jQuery("#publisher-status").html(status);
		if(status == 'KEY_ASSIGNED') {
			jQuery("#key-section").show();
			
			//the button token
			if(buttonToken != null) {
				jQuery("#hosted_button_id").val(buttonToken);
			} else { 
				jQuery("#hosted_button_id").val('WKLRWF9WC9UZY');
			}
			
			jQuery("#token-section").hide();
		    jQuery("#save_options_button").hide();
			
			jQuery("#offer-section").show();
			jQuery("#paypal-subscribe").show();	
			
			
			jQuery("#finished-action").hide();
			jQuery("#subscribed-action").hide();
			jQuery("#key-assigned-action").show();
			
			
			
			
		} else if(status == 'SUBSCRIBER') {
			jQuery("#key-section").show();
			jQuery("#paypal-subscribe").hide();		
			jQuery("#action-getkey").show();
			
			jQuery("#token-section").show();
		    jQuery("#save_options_button").show();
			
			
			
			
			if(!subscribed_saved) {
				jQuery("#finished-action").hide();
			    jQuery("#key-assigned-action").hide();
				jQuery("#subscribed-action").show();
			} else {
				jQuery("#subscribed-action").hide();					
			    jQuery("#key-assigned-action").hide();
			    jQuery("#finished-action").show();						
			}
			
			
			
		} else if(status == 'CANCELLED') {
				
			//ajax post to admin server
			wl_set_cancelled_state_ajax();
			
			jQuery("#key-section").show();
			
			//the button token
			if(buttonToken != null) {
				jQuery("#hosted_button_id").val(buttonToken);
			} else {
				jQuery("#hosted_button_id").val('WKLRWF9WC9UZY');
			}
			
			jQuery("#offer-section").show();
			jQuery("#paypal-subscribe").show();		
			jQuery("#save_options_button").hide();
			jQuery("#token-section").hide();
			
			
			jQuery("#finished-action").hide();
			jQuery("#subscribed-action").hide();
			jQuery("#key-assigned-action").show();
		}  
	}
	
}


</script>
<style type="text/css">
.form-table form input {border:none;}
#submitLabel {display: block;}
#submitLabel input {
	display: block;
	padding: 0;
}
#checkBoxLabel {}

.publisher-status {
    background:#f2f2f2 repeat-x scroll left top;
	text-decoration: none;
	font-size: 11px;
	line-height: 16px;
	padding: 5px 11px;
	margin-top:10px;
	margin-bottom:5px;
	cursor: pointer;
	border: 1px solid #bbb;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	-moz-box-sizing: content-box;
	-webkit-box-sizing: content-box;
	-khtml-box-sizing: content-box;
	box-sizing: content-box;
	text-shadow: rgba(255,255,255,1) 0 1px 0;
	color: #6b6b6b;
	font-weight: bold;
	text-transform: uppercase;
	width: 380px;
	
}

.option-form-label { width: 100px; display: inline-block;}
.options-title2 {
    	border-bottom:1px solid #cccccc; padding-bottom:3px;
    	margin-bottom: 10px;
		font-weight:bold;
		font-size:1.2em;
		text-transform:uppercase;
		width: 100%;
}

.options-signup-text { 
		margin-bottom: 10px; 
		font-style:italic; 
		font-weight:normal; 
		color: #333333;  }

.line-spacer-10 {
	height: 10px;
}

	
#form-service {
	display: none;
}
	
#form-plugin {
	display: none;
}

#key-section {
	display: none;
}

#offer-section {
	display: none;
}

#save_options_button {
	display: none;
}

#action-getkey { 
	display: none;
}

#paypal-subscribe {
	display: none;
}

#token-section {
	display: none;
}

#wl_api_endpoint {
	display: none;
	width: 400px;
}

#api-endpoint-assigned {
	width: 400px;
}

#siteToken {
	width: 400px;
}
	
.assigned-field {
	background: #DBDBDB;
	font-size:1.2em;
	font-weight:bold; 
	color: #595959;
	font-family: monospace;
	width: 400px;
	margin-bottom: 10px;
}
	
.option-form-field { width: 300px; }

#plugin-options th { width: 80px; }

</style>


<div class="wrap">

<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
<h2>Welocally Places Subscribe</h2>
<?php 
$menubar_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/options/options-infobar.php';
include($menubar_include);
?>

<?php
// If options have been updated on screen, update the database

if ( ( !empty( $_POST ) ) && ( check_admin_referer( 'welocally-places-subscribe', 'welocally_places_subscribe_nonce' ) ) ) { 

	$options = wl_get_options();
	
	$options[ 'siteToken' ] = $_POST[ 'siteToken' ];
	$options[ 'siteKey' ] = $_POST[ 'siteKey' ];
	
	wl_save_options($options);

	echo '<div class="updated fade"><p><strong>' . __( 'Settings Saved.' ) . "</strong></p></div>\n";
}

// Get options
$options = wl_set_general_defaults();

//display error if not subscribed
if(!is_subscribed()) {
	echo '<div class="error fade"><p><strong>' . __( 'Please Subscribe To Activate Welocally Places' ) . "</strong></p></div>\n";
} 


?>

<div class="snp_settings wrap">

	<div id="wl-options-error" class="wl-places-error error"></div>
	<div class="form">
		<!--the form values that will be used to start the subscription -->
		<div id="all-content" >
			<table width="100%">
				<tr>
					<td>
						<div id="content_col_1">						
								<!-- call to action -->							    
							    <div class="options-signup-text" id="signup-text"></div>
							    <div id="action-container">
							    		<div id="key-assigned-action" style="display:none;"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/subscribe_action_1.png" alt="" title=""/></div>
										<div id="subscribed-action" style="display:none;"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/subscribe_action_2.png" alt="" title=""/></div>
										<div id="finished-action" style="display:none;"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/subscribe_action_3.png" alt="" title=""/></div>							    
							    </div>	
							    <!-- status -->
								<div class="publisher-status">
									Publisher Status:<span id="publisher-status">LOADING...</span> 
								</div>
							    
							    <!-- the form -->
								<div id="form-signup">
								 <form method="post" action="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-subscribe' ?>">
								 <fieldset>									
									<div>
										<label for="siteName" class="option-form-label">Site Name:</label>
										<div id="home-assigned" class="assigned-field"><?php echo wl_get_option('siteName',get_bloginfo('name')); ?></div>
										<input type="hidden" name="siteName" id="siteName" value="<?php echo wl_get_option('siteName',get_bloginfo('name')); ?>" />
									</div>
									<div>
										<label for="siteHome" class="option-form-label">Site Home:</label>
										<div id="home-assigned" class="assigned-field"><?php echo wl_get_option('siteHome', get_bloginfo('home')); ?></div>
										<input type="hidden" name="siteHome" id="siteHome" value="<?php echo wl_get_option('siteHome',get_bloginfo('home')); ?>" />
									</div>						
									<div>
										<label for="siteEmail" class="option-form-label" >E-mail:</label>
										<div id="email-assigned" class="assigned-field"><?php echo wl_get_option('siteEmail',get_bloginfo('admin_email')); ?></div>
										<input type="hidden" name="siteEmail" id="siteEmail"  value="<?php echo wl_get_option('siteEmail',get_bloginfo('admin_email')); ?>" />
									</div>	
									<div id="key-section">
										<label for="siteKey" class="option-form-label" >Publisher Key:</label>
										<div id="key-assigned" class="assigned-field"></div>
										<input type="hidden" name="siteKey" id="siteKey"  value="<?php echo wl_get_option('publisherKey',null) ?>" /> 
									</div>
								
									<div id="offer-section">
										<label for="siteToken" class="option-form-label" >Offer Code:</label>
										<input type="text" name="offerCode" id="offerCode" />
									</div>				
									<div id="token-section">
										<label for="siteToken" class="option-form-label" >Publisher Token:</label>
										<div><input type="text" name="siteToken" id="siteToken" value="<?php echo wl_get_option('publisherToken',null) ?>" /></div>
									</div>
								</fieldset>
								<?php wp_nonce_field( 'welocally-places-subscribe','welocally_places_subscribe_nonce', true, true ); ?>
								<div id="save_options_button">				
								<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Settings' ); ?>"/></p>
								</div>
								</form>
							</div>
							<!-- PAYPAL subscribe form WKLRWF9WC9UZY -->
							<div id="paypal-subscribe" style="display:none;">	
								<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="paypal-form">
									<input type="hidden" name="cmd" value="_s-xclick">
									<input type="hidden" name="hosted_button_id" id="hosted_button_id" value="WKLRWF9WC9UZY">
									<input type="hidden" name="custom" id="paypal-custom">
									<input id="paypal-subscribe-img" type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
									<img id="paypal-pixel-img" alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
								</form>
							</div>				
						</div>
					</td>
			
				</tr>
			
			</table>
		</div>
		
		
	</div><!-- form -->
</div>


</div> <!-- snp_settings wrap -->