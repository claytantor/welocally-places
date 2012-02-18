<?php
global $wlPlaces;
if ( ( !empty( $_POST ) ) && 
	( check_admin_referer( 'welocally-places-subscribe', 'welocally_places_subscribe_nonce' ) ) ) { 
	if($_POST[ 'siteName' ]=='delete'){
		error_log('delete tokens:'.$_POST[ 'siteName' ]);
		delete_subscription_options();
	}	
}
?>
<script type="text/javascript">

<?php if(is_subscribed()):?>
var subscribed_saved = true;
<?php else: ?>
var subscribed_saved = false;
<?php endif; ?>

jQuery(document).ready(function() {
	
	if(jQuery("#registration_result").html() != null){
		var keydata = JSON.parse(jQuery("#registration_result").html());
		
		set_form_fields( 
			keydata.siteKey, 
			null, 
			keydata.subscriptionStatus);
		
	} else {
		wl_get_subscriber_info();
	}

		
	jQuery("#edit-form-action").click( function () {
		jQuery("#siteName").toggle();
		jQuery("#name-assigned").toggle();
		
		jQuery("#siteHome").toggle();
		jQuery("#home-assigned").toggle();
		
		jQuery("#siteEmail").toggle();
		jQuery("#email-assigned").toggle();
		
		jQuery("#siteKey").toggle();
		jQuery("#key-assigned").toggle();
		
		jQuery("#siteToken").toggle();
		jQuery("#token-assigned").toggle();
		
		if(jQuery("#siteKey").is(":visible")){
			jQuery("#token-section").show();		   	
		}
			
		return false;
	});
	
	jQuery("#siteName").change( function () {	
		jQuery("#name-assigned").html(jQuery("#siteName").val());
	});
	
	jQuery("#siteHome").change( function () {	
		jQuery("#home-assigned").html(jQuery("#siteName").val());
	});
	
	jQuery("#siteEmail").change( function () {	
		jQuery("#email-assigned").html(jQuery("#siteEmail").val());
	});
	
	jQuery("#siteKey").change( function () {	
		jQuery("#key-assigned").html(jQuery("#siteKey").val());
	});
	
	jQuery("#siteToken").change( function () {	
		jQuery("#token-assigned").html(jQuery("#siteToken").val());
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
	
	data.siteKey = '<?php echo wl_get_option('siteKey',null) ?>' ;
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
			set_form_fields( keydata.key, keydata.token, keydata.subscriptionStatus);
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


function set_form_fields( key, token, status) {
	
	if(key != null) {
		jQuery("#paypal-custom").val(key);	
		jQuery("#key-assigned").html(key);	
		jQuery("#siteKey").val(key);	
	}
	
	if(token != null){
		jQuery("#token-assigned").html(token);
		jQuery("#welocally-places-display_token").val(token);
		jQuery("#siteToken").val(token);
	}
		
	if(status != null) {
		jQuery("#publisher-status").html(status);
		if(status == 'KEY_ASSIGNED') {
			wl_set_cancelled_state_ajax();
			jQuery("#siteToken").val('');
			jQuery("#key-section").show();
			
						
			jQuery("#token-section").hide();
		    jQuery("#save_options_button").hide();
			
			jQuery("#offer-section").show();
			jQuery("#paypal-subscribe").show();	
			
			
			jQuery("#finished-action").hide();
			jQuery("#subscribed-action").hide();
			jQuery("#key-assigned-action").show();
			
			var statusimg = '<img width="75" hieght="75" style="float: left;" class="align-right" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/free1.png" alt="" title=""/>'+
			'<span class="options-text">We have assigned a key to you, but to use or basic service you must register. Go ahead, its easy. Just press the <em>Save Settings</em> button and we will send you your secret token.</span>'; 
			jQuery("#action-area").html(statusimg);
			
						
		}  else if(status == 'REGISTERED') {
			jQuery("#key-section").show();
			jQuery("#token-section").show();
			
			jQuery("#finished-action").hide();
			jQuery("#subscribed-action").hide();
			jQuery("#key-assigned-action").show();	
			
			
			var statusimg = '<img width="75" hieght="75" style="float: left;" class="align-right" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/token1.png" alt="" title=""/>'+
				'<span class="options-text">Great you are almost there! Now look the your inbox for the email address you gave us, and there should be an email with your free token. Enter it in into the Publisher Token field and press the <em>Save Settings</em> button.</span>'; 
			jQuery("#action-area").html(statusimg);
			
					
		} else if(status == 'SUBSCRIBED') {
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
			
			var statusimg = '<img width="75" hieght="75" style="float: left;" class="align-right" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/subscribed1.png" alt="" title=""/>'+
				'<span class="options-text">You did it! You signed up for our product and now you can use it. You have been given access to our user portal with help, support and a whole bunch of other free welocally resouces. Be sure to <a href="<?php echo wl_server_base().'/admin/home'?>" target="_blank">log into your portal</a> as soon as possible.</span>'; 
			jQuery("#action-area").html(statusimg);
			
			
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
	width: 95%;
	
}

.basic-frame {
	display: inline-block;
    background:#ffffff;
	margin-top:10px;
	margin-bottom:10px;
	border: 2px solid #bbb;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	-moz-box-sizing: content-box;
	-webkit-box-sizing: content-box;
	-khtml-box-sizing: content-box;
	color: #6b6b6b;
	width: 95%;
	padding: 5px;
	
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
	width: 95%;
}

#api-endpoint-assigned {
	width: 400px;
}


#form-signup {
	width: 95%;
}
	
.assigned-field {
	background: #DBDBDB;
	font-size:1.2em;
	font-weight:bold; 
	color: #595959;
	font-family: monospace;
	width: 95%;
	height:20px;
	margin-bottom: 10px;
}

label { font-size:1.2em; color: #595959; font-weight:bold; width:400px; margin-top:10px;  }
	
.option-form-field { 
	width: 95%; 
	height:20px;
	margin-bottom: 10px;
}

#plugin-options th { width: 80px; }

</style>


<div class="wrap">

<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
<h2>Welocally Places Registration</h2>
<?php 
$menubar_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/options/options-infobar.php';
include($menubar_include);

// If options have been updated on screen, update the database

if ( ( !empty( $_POST ) ) && ( check_admin_referer( 'welocally-places-subscribe', 'welocally_places_subscribe_nonce' ) ) ) { 

	$options = wl_get_options();
	
	$options[ 'siteName' ] = $_POST[ 'siteName' ];
	$options[ 'siteHome' ] = $_POST[ 'siteHome' ];
	$options[ 'siteEmail' ] = $_POST[ 'siteEmail' ];
	$options[ 'siteToken' ] = $_POST[ 'siteToken' ];
	$options[ 'siteKey' ] = $_POST[ 'siteKey' ];
	
	wl_save_options($options);

	echo '<div class="updated fade"><p><strong>' . __( 'Settings Saved.' ) . "</strong></p></div>\n";
	
	$json_register_request = json_encode($_POST);
	if($_POST[ 'siteName' ]!='delete'){
		error_log('register:'.$_POST[ 'siteName' ]);
		$result = welocally_register($json_register_request);
		echo '<div style="display:none" id="registration_result">'.$result.'</div>';			
		$resultJson = json_encode($result);	
	}
	

	
}

// Get options
$options = wl_set_general_defaults();
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
							    <div class="basic-frame" id="action-area" >&nbsp;</div>

							    <!-- status -->
								<div class="publisher-status">
									Publisher Status:<span id="publisher-status">LOADING...</span> 
								</div>
							    
							    <!-- the form -->
								<div id="form-signup">
								 <div class="action" style="width:100%; text-align:right;"><a id="edit-form-action" href="#">Edit</a></div>
								 <form method="post" action="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-subscribe' ?>">
								 <fieldset>									
									<div>
										<label for="siteName">Site Name: </label>										
										<div><em>You can name your site anything you want but it has to be unique to our system.</em></div>
										<div id="name-assigned" class="assigned-field"><?php echo wl_get_option('siteName',get_bloginfo('name')); ?></div>
										<input class="option-form-field" type="text"  style="display:none" name="siteName" id="siteName" value="<?php echo wl_get_option('siteName',get_bloginfo('name')); ?>" />
									</div>
									<div>
										<label for="siteHome" >Site Home:</label>
										<div><em>This has to be the real base URL for your site, we check this when you make a request. If this site is not on the internet we can't give you a token.</em></div>
										<div id="home-assigned" class="assigned-field"><?php echo wl_get_option('siteHome', get_bloginfo('home')); ?></div>
										<input  class="option-form-field"  type="text"  style="display:none" name="siteHome" id="siteHome" value="<?php echo wl_get_option('siteHome',get_bloginfo('home')); ?>" />
									</div>						
									<div>
										<label for="siteEmail" >E-mail:</label>
										<div><em>This needs to be real, we send your token by email.</em></div>
										<div id="email-assigned" class="assigned-field"><?php echo wl_get_option('siteEmail',get_bloginfo('admin_email')); ?></div>
										<input  class="option-form-field"  type="text"  style="display:none" name="siteEmail" id="siteEmail"  value="<?php echo wl_get_option('siteEmail',get_bloginfo('admin_email')); ?>" />
									</div>	
									<div id="key-section">
										<label for="siteKey" >Publisher Key:</label>
										<div><em>We assign this to you, so if you change this you should probably have a good reason like we told you to. Better to leave it alone.</em></div>
										<div id="key-assigned" class="assigned-field"></div>
										<input  class="option-form-field"  type="text"  style="display:none" name="siteKey" id="siteKey"  value="<?php echo wl_get_option('siteKey',null) ?>" /> 
									</div>
									<div id="token-section">
										<label for="siteToken" >Publisher Token:</label>
										<div><em>Please place the token you recieved by email here.</em></div>
										<div id="token-assigned" class="assigned-field"><?php echo wl_get_option('siteToken','') ?></div>
										<div><input  class="option-form-field" type="text" style="display:none" name="siteToken" id="siteToken" value="<?php echo wl_get_option('siteToken','') ?>" /></div>
									</div>
								</fieldset>
								<?php wp_nonce_field( 'welocally-places-subscribe','welocally_places_subscribe_nonce', true, true ); ?>
								
								<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Settings' ); ?>"/></p>
																
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