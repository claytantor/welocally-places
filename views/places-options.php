<script type="text/javascript">
jQuery(document).ready(function(jQuery) {
	
	 if (typeof(jQuery.fn.parseJSON) == "undefined" || typeof(jQuery.parseJSON) != "function") { 

	    //extensions, this is because prior to 1.4 there was no parse json function
		jQuery.extend({
			parseJSON: function( data ) {
				if ( typeof data !== "string" || !data ) {
					return null;
				}    
				data = jQuery.trim( data );    
				if ( /^[\],:{}\s]*$/.test(data.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@")
					.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]")
					.replace(/(?:^|:|,)(?:\s*\[)+/g, "")) ) {    
					return window.JSON && window.JSON.parse ?
						window.JSON.parse( data ) :
						(new Function("return " + data))();    
				} else {
					jQuery.error( "Invalid JSON: " + data );
				}
			}
		});
	}	

	getPublisherInfoFromWelocally();
		
	jQuery("#action-getkey").click( function () {	
		return false;
	});
});

function setFormFields( key, token, serviceEndDateMillis, status, buttonToken) {
	
	if(key != null) {
		jQuery("#paypal-custom").val(key);	
		jQuery("#key-assigned").html(key);	
		//set the form vals
		jQuery("#siteKey").val(key);
		//jQuery("#paypal-os0").val(key);
		
	}
	
	if(token != null){
		jQuery("#token-assigned").html(token);
		//welocally-places-display_token
		jQuery("#welocally-places-display_token").val(token);
		jQuery("#siteToken").val(token);
		jQuery("#token-section").show();
	}
	
	if(status != null) {
		jQuery("#publisher-status").html(status);
		if(status == 'KEY_ASSIGNED') {
			jQuery("#key-section").show();
			
			//the button token
			if(buttonToken != null) {
				jQuery("#hosted_button_id").val(buttonToken);
			} else {
				jQuery("#hosted_button_id").val('CYUU2TQ7EJYFY');
			}
			
			jQuery("#paypal-subscribe").show();		
			
			var stage1text ='We have assigned you a publisher key, but you still need to obtain your publisher token. Welocally Places is '+
					'a paid service, but we offer a <strong>trail period for free!</strong> Just subscribe with paypal '+
					'below and we will provide you with a site key that will let you start transforming your site into a hyper-local '+
					'destination in minutes! Not interested? No problem, just unsubscribe before the trail ends and you pay nothing.';
			
			jQuery("#signup-text").html(stage1text);
			
			
			
		} else if(status == 'SUBSCRIBER') {
			jQuery("#key-section").show();
			jQuery("#paypal-subscribe").hide();		
			jQuery("#action-getkey").show();
			
			var stage2text ='We are really glad you have chosen to evaluate our service! We have assigned you a publisher token. '+
				'If you decide you want to end you subscription just go into paypal and cancel it at any time. If you do this before '+
				'the trail period ends you will be charged nothing. <strong>Please make sure to save the token</strong> so the plugin can use '+
				'work.';
			
			jQuery("#signup-text").html(stage2text);
			
		} else if(status == 'CANCELLED') {
		
		
			jQuery("#key-section").show();
			
			//the button token
			if(buttonToken != null) {
				jQuery("#hosted_button_id").val(buttonToken);
			} else {
				jQuery("#hosted_button_id").val('CYUU2TQ7EJYFY');
			}
			
			jQuery("#paypal-subscribe").show();		

			var stage3text ='We are really sorry to see you go. Would you consider giving us some feedback on what we can do to make '+
					'this service better? If you change your mind its no problem to just subscribe again using the paypal button below. '+
					'We sorry to say we can not offer another trail period.';
			
			jQuery("#signup-text").html(stage3text);
		}  
	}
	
}


function getPublisherInfoFromWelocally(){
	var data = {
		action: 'getkey'
	};
	
	data.siteHome = jQuery("#siteHome").val();
	data.siteName = jQuery("#siteName").val();
	data.siteDescription = jQuery("#siteDescription").val();
	data.siteEmail = jQuery("#siteEmail").val();
	data.iconUrl = jQuery("#iconUrl").val();
			
	jQuery.ajax({
	  type: 'POST',
	  url: ajaxurl,
	  data: data,
	  success: function(response) {
		var keydata = jQuery.parseJSON(response);
		
		if(keydata.key != null){
			setFormFields( keydata.key, keydata.token, null, keydata.subscriptionStatus, keydata.buttonToken);
		}			
	  }
	});
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
.form-table form #secondSubmit {
	background:#f2f2f2;
	text-decoration: none;
	font-size: 11px;
	line-height: 16px;
	padding: 5px 11px;
	margin-bottom:10px;
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
}
.form-table form #secondSubmit {
	background: #f2f2f2;
}

.form-table form #secondSubmit:active {
	background: #eee;
}

.form-table form #secondSubmit:hover {
	color: #000;
	border-color: #666;
}
div.snp_settings{
	width:90%;
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

#action-getkey { 
	display: none;
}

#paypal-subscribe {
	display: none;
}

#token-section {
	display: none;
}
	
.assigned-field {
	background: #DBD0A2;
	font-size:1.0em;
	color: 59543D;
	font-family: monospace;
	width: 350px;
	margin-bottom: 10px;
}
	
.option-form-field { width: 300px; }

#plugin-options th { width: 80px; }

</style>
<div class="snp_settings wrap">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2><?php _e('Welocally Places Settings',$this->pluginDomain); ?></h2>
	<div id="wl-options-error" class="wl-places-error error"></div>
	<?php
	try {
		do_action( 'sp_welocally_options_top' );
		if ( !$this->optionsExceptionThrown ) {
		}
	} catch( WLPLACES_Options_Exception $e ) {
		$this->optionsExceptionThrown = true;
	}
	?>
	<div>
		Publisher Status:<span id="publisher-status"></span> 
	</div>
	<div class="form">
		<!--the form values that will be used to start the subscription -->
		<div id="form-signup">
			
			 <?php
			 if ( function_exists('wp_nonce_field') ) {
				wp_nonce_field('placesPublisherSignup');
			 }
			 ?>
			 <fieldset>
				<legend class="options-title2"><?php _e('Publisher Subscription',$this->pluginDomain); ?></legend>
				<div class="line-spacer-10">&nbsp;</div>
				<div class="options-signup-text" id="signup-text"></div>
				<div>
					<label for="siteName" class="option-form-label">Site Name:</label>
					<div id="home-assigned" class="assigned-field"><?php echo $siteName ?></div>
					<input type="hidden" name="siteName" id="siteName" value="<?php echo $siteName ?>" />
				</div>
				<div>
					<label for="siteHome" class="option-form-label">Site Home:</label>
					<div id="home-assigned" class="assigned-field"><?php echo $siteHome ?></div>
					<input type="hidden" name="siteHome" id="siteHome" value="<?php echo $siteHome ?>" />
				</div>
				<div>
					<label for="siteDescription" class="option-form-label">Description:</label>
					<div id="descripton-assigned" class="assigned-field"><?php echo $siteDescription ?></div>
					<input type="hidden" name="siteDescription" id="siteDescription"  value="<?php echo $siteDescription ?>" />
				</div>							
				<div>
					<label for="siteEmail" class="option-form-label" >E-mail:</label>
					<div id="email-assigned" class="assigned-field"><?php echo $siteEmail ?></div>
					<input type="hidden" name="siteEmail" id="siteEmail"  value="<?php echo $siteEmail ?>" />
				</div>	
				<div id="key-section">
					<label for="siteKey" class="option-form-label" >Publisher Key:</label>
					<div id="key-assigned" class="assigned-field"></div>
					<input type="hidden" name="siteKey" id="siteKey"  value="<?php echo $siteKey ?>" />
 
				</div>
				<div id="token-section">
					<label for="siteToken" class="option-form-label" >Publisher Token:</label>
					<div id="token-assigned" class="assigned-field"></div>
					<input type="hidden" name="siteToken" id="siteToken"  value="<?php echo $siteToken ?>" />
 				</div>
				
				<div class="line-spacer-10">&nbsp;</div>
			</fieldset>
		</div>
		<div id="paypal-subscribe">	
			<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" id="hosted_button_id" value="CYUU2TQ7EJYFY">
				<input type="hidden" name="custom" id="paypal-custom">
				<input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>		
	</div>
</div>