function WELOCALLY_RegisterWidget (cfg) {	
	
	this._wrapper;
	this._cfg;
	this._ajaxStatus;
	this._formArea;
	this._jqxhr;
	
	this.init = function() {
		var error;
		if (!cfg) {
			error = "Please provide configuration for the widget";
			cfg = {};
		}
		
		if (!cfg.siteKey) {
			cfg.siteKey = '';
		}
		
		if (!cfg.siteHome) {
			cfg.siteHome = '';
		}
		
		if (!cfg.siteEmail) {
			cfg.siteEmail = '';
		}
		
		if (!cfg.siteName) {
			cfg.siteName = '';
		}
		
		if (!cfg.siteToken) {
			cfg.siteToken = '';
		}
				
		if (!cfg.endpoint) {
			cfg.endpoint = 'http://stage.welocally.com';
		}
		
		if (!cfg.imagePath) {
			cfg.imagePath = 'http://placehound.com/images';
		}
		
		if (!cfg.wrapper) {
			// Get current script object
			var script = jQuery('SCRIPT');
			script = script[script.length - 1];

			// Build Widget
			this._wrapper = jQuery('<div></div>');	
			jQuery(this._wrapper).attr('class','wl_register_widget');
			jQuery(this._wrapper).attr('id','wl_register_widget');
		} else {
			this._wrapper = cfg.wrapper;
		}
		
		this._cfg = cfg;
		
		//now add the form fields
		this._ajaxStatus = jQuery('<div></div>');
		jQuery(this._ajaxStatus).css('display','none');	
		jQuery(this._wrapper).append(this._ajaxStatus);
		
		//the form
		this._formArea = this.makeFormArea();
		jQuery(this._formArea).css('display','none');
		jQuery(this._wrapper).append(this._formArea);
		
		//get the status
		this.getSubscriberInfo(cfg.siteKey, cfg.siteHome, cfg.siteName, cfg.siteEmail, cfg.siteToken);
				
		return this;
		
		
	};
}

WELOCALLY_RegisterWidget.prototype.makeFormArea = function(){
	var formArea = jQuery('<div></div>');
	//workflow status
	jQuery(formArea).append('<div id="wl_register_status_area"></div>');

	//form fields
	jQuery(formArea).append('<div class="wl_field_title">Site Name</div>');	
	jQuery(formArea).append('<div class="wl_field_description">You can name your site anything you want but it has to be unique to our system.</div>');	
	jQuery(formArea).append('<div class="wl_field_value wl_register_value" id="wl_register_value_site_name"></div>');	
	jQuery(formArea).append('<div class="wl_field_area"><input id="wl_register_site_name" type="text" name="siteName" class="wl_widget_field wl_register_field"/></div>');
	
	jQuery(formArea).append('<div class="wl_field_title">Site Home</div>');
	jQuery(formArea).append('<div class="wl_field_description">This has to be the real base URL for your site, we check this when you make a request. If this site is not on the internet we can not give you a token.</div>');	
	jQuery(formArea).append('<div class="wl_field_value wl_register_value" id="wl_register_value_site_home"></div>');
	jQuery(formArea).append('<div class="wl_field_area"><input id="wl_register_site_home" type="text" name="siteHome" class="wl_widget_field wl_register_field"/></div>');

	jQuery(formArea).append('<div class="wl_field_title">Email</div>');	
	jQuery(formArea).append('<div class="wl_field_description">This needs to be real, we send your token by email.</div>');	
	jQuery(formArea).append('<div class="wl_field_value wl_register_value" id="wl_register_value_email"></div>');
	jQuery(formArea).append('<div class="wl_field_area"><input id="wl_register_email" type="text" name="email" class="wl_widget_field wl_register_field"/></div>');
	
	jQuery(formArea).append('<div class="wl_field_title">Publisher Key</div>');	
	jQuery(formArea).append('<div class="wl_field_description">We assign this to you, so if you change this you should probably have a good reason like we told you to. Better to leave it alone.</div>');	
	jQuery(formArea).append('<div class="wl_field_value wl_register_value" id="wl_register_value_key"></div>');
	jQuery(formArea).append('<div class="wl_field_area"><input id="wl_register_key" type="text" name="siteKey" class="wl_widget_field wl_register_field"/></div>');

	
	var tokenArea = jQuery('<div id="wl_register_token_area"></div>');
	jQuery(tokenArea).append('<div class="wl_field_title">Publisher Token</div>');	
	jQuery(tokenArea).append('<div class="wl_field_description">Please place the token you recieved by email here.</div>');	
	jQuery(tokenArea).append('<div class="wl_field_value wl_register_value" id="wl_register_value_token"></div>');	
	jQuery(tokenArea).append('<div class="wl_field_area"><input id="wl_register_token" type="text" name="siteToken" class="wl_widget_field wl_register_field"/></div>');	
	jQuery(tokenArea).css('display','none');
	jQuery(formArea).append(tokenArea);
		
	var buttons = jQuery('<p class="submit">'+
	'<a href="#" id="wl_register_edit_fields_action" class="button-primary" value="edit">Edit Fields</a>&nbsp;'+
	'<a href="#" id="wl_register_submit_action" class="button-primary" value="edit">Save</a></p>' );
	
	jQuery(buttons).find('#wl_register_edit_fields_action').bind("click", {instance: this}, this.editHandler);
	
	//jQuery(buttons).find('#wl_register_submit_action').bind("click", {instance: this}, this._cfg.registeredCallback);
	jQuery(buttons).find('#wl_register_submit_action').bind("click", {instance: this}, this.registrationHandler);
	
	jQuery(formArea).append(buttons);
	
	jQuery(formArea).find('.wl_field_area').hide();
				
	return formArea;
	
};

WELOCALLY_RegisterWidget.prototype.getSiteInfo = function(){
	
	var data = {
			siteKey: jQuery("#wl_register_key").val(),
			siteName: jQuery("#wl_register_site_name").val(),
			siteHome: jQuery("#wl_register_site_home").val(),
			siteEmail: jQuery("#wl_register_email").val(),
			siteToken: jQuery("#wl_register_token").val()
	};
		
	return data;
	
};

WELOCALLY_RegisterWidget.prototype.getSubscriberInfo = function(siteKey, siteHome, siteName, siteEmail, siteToken){

	var _instance = this;
	var data = {
		siteKey: siteKey,
		siteName: siteName,
		siteHome: siteHome,
		siteEmail: siteEmail,
		siteToken: siteToken
	};
		
	_instance.setStatus(_instance._ajaxStatus,'Getting publisher info...', 'wl_message', true);
	
	var ajaxurl = _instance._cfg.endpoint +
		'/admin/signup/3_0/plugin/key.json';
	
	_instance.jqxhr = jQuery.ajax({
		  type: 'GET',		  
		  url: ajaxurl,
		  data: data,
		  dataType : 'jsonp',
		  beforeSend: function(jqXHR){
			jqxhr = jqXHR;
			jqxhr.setRequestHeader("site-key", siteKey);
			if(siteToken){
				jqxhr.setRequestHeader("site-token", _instance._cfg.siteToken);
			}
			
		  },
		  error : function(jqXHR, textStatus, errorThrown) {
			if(textStatus != 'abort'){
				_instance.setStatus(_instance._ajaxStatus,'ERROR : '+textStatus, 'error', false);
			}		
		  },		  
		  success : function(data, textStatus, jqXHR) {
			if(data != null && data.errors != null) {
				_instance.setStatus(_instance._ajaxStatus,'Could not get publisher info. '+WELOCALLY.util.getErrorString(data.errors), 'wl_error', false);
			} else {
				_instance.setStatus(_instance._ajaxStatus, JSON.stringify(data), 'wl_message', false);
				_instance.setFormFields(data.subscriptionStatus, data.site.key, siteHome, siteName, siteEmail, siteToken);
				
			}
		  }
		});

};



WELOCALLY_RegisterWidget.prototype.setFormFields = 
	function(status, siteKey, siteHome, siteName, siteEmail, siteToken){
	
	var _instance = this;
	
	if(status != null) {
		if(status == 'KEY_ASSIGNED') {
			jQuery("#wl_register_status_area").html('<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" src="'+
				_instance._cfg.imagePath+'/free1.png" alt="" title=""/>'+
				'<span class="options-text">We have assigned a key to you, but to use or basic service you must register. Go ahead, its easy. Just press the <strong>Register Now</strong> button and '+
				'we will send you your secret token.</span><div style="clear:both"></div>');
			jQuery(_instance._formArea).find('#wl_register_submit_action').html('Register Now');
		} else if (status == 'REGISTERED') {
			jQuery("#wl_register_status_area").html('<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" src="'+_instance._cfg.imagePath+'/token1.png" alt="" title=""/>'+
			'<span class="options-text">Great you are almost there! Now look the your inbox for the email address you gave us, and there should be an email with '+
			'your free token. Presss the <strong>Edit Fields</strong> button, and then enter it in into the Publisher Token field and press the <strong>Save Token</strong> button.</span>'+
			'<div style="clear:both"></div>');
			jQuery(_instance._formArea).find('#wl_register_submit_action').html('Save Token');
			jQuery(_instance._formArea).find('#wl_register_token_area').show();
		} else if (status == 'SUBSCRIBED') {
			jQuery("#wl_register_status_area").html('<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" '+
					'src="'+_instance._cfg.imagePath+'/subscribed1.png" alt="" title=""/>'+
			'<span class="options-text">You did it! You signed up for our product and now you can use it. '+
			'You have been given access to our user portal with help, support and a whole bunch of other '+
			'free welocally resouces. Be sure to <a href="'+_instance._cfg.endpoint+'/admin/home" target="_blank">log into your portal</a> as soon as possible.</span>'+
			'<div style="clear:both"></div>');
			jQuery(_instance._formArea).find('#wl_register_submit_action').html('Refresh Registration');
			jQuery(_instance._formArea).find('#wl_register_token_area').show();
		}
	
	}
	
	if(siteKey != null) {
		jQuery("#wl_register_key").val(siteKey);	
		jQuery("#wl_register_value_key").html(siteKey);
	}
	
	if(siteEmail != null) {
		jQuery("#wl_register_email").val(siteEmail);	
		jQuery("#wl_register_value_email").html(siteEmail);
	}
	
	if(siteHome != null) {
		jQuery("#wl_register_site_home").val(siteHome);	
		jQuery("#wl_register_value_site_home").html(siteHome);
	}
	
	if(siteName != null) {
		jQuery("#wl_register_site_name").val(siteName);	
		jQuery("#wl_register_value_site_name").html(siteName);
	}
	
	if(siteToken != null) {
		jQuery("#wl_register_token").val(siteToken);	
		jQuery("#wl_register_value_token").html(siteToken);
	}
	
	
	jQuery(_instance._formArea).show();
	
	/*if(key != null) {
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
			
			jQuery("#token-box").hide();
			
			jQuery("#siteToken").val('');
			jQuery("#token-assigned").val('');
			
			jQuery("#key-section").show();
						
		    jQuery("#save_options_button").hide();
			
			jQuery("#offer-section").show();
			jQuery("#paypal-subscribe").show();	
			
			
			jQuery("#finished-action").hide();
			jQuery("#subscribed-action").hide();
			jQuery("#key-assigned-action").show();
			
			var statusimg = '<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/free1.png" alt="" title=""/>'+
			'<span class="options-text">We have assigned a key to you, but to use or basic service you must register. Go ahead, its easy. Just press the <strong>Register Now</strong> button and we will send you your secret token.</span>'; 
			jQuery("#action-area").html(statusimg);
			
			jQuery("#primary-action-button").val('Register Now');
					
						
		}  else if(status == 'REGISTERED') {
			
			jQuery("#token-box").show();
			
			jQuery("#key-section").show();
			jQuery("#token-section").show();
			
			jQuery("#finished-action").hide();
			jQuery("#subscribed-action").hide();
			jQuery("#key-assigned-action").show();	
			
			
			var statusimg = '<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/token1.png" alt="" title=""/>'+
				'<span class="options-text">Great you are almost there! Now look the your inbox for the email address you gave us, and there should be an email with your free token. Enter it in into the Publisher Token field and press the <em>Save Settings</em> button.</span>'; 
			jQuery("#action-area").html(statusimg);
			
			jQuery("#primary-action-button").val('Save Token');
			
					
		} else if(status == 'SUBSCRIBED') {
			
			jQuery("#token-box").show();
			
			jQuery("#key-section").show();
			jQuery("#token-section").show();
			
			jQuery("#paypal-subscribe").hide();		
			jQuery("#action-getkey").show();
			
			
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
			
			var statusimg = '<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/subscribed1.png" alt="" title=""/>'+
				'<span class="options-text">You did it! You signed up for our product and now you can use it. You have been given access to our user portal with help, support and a whole bunch of other free welocally resouces. Be sure to <a href="<?php echo wl_server_base().'/admin/home'?>" target="_blank">log into your portal</a> as soon as possible.</span>'; 
			jQuery("#action-area").html(statusimg);
			
			jQuery("#primary-action-button").val('Save Settings');
			
			
		} else if(status == 'CANCELLED') {
			
			jQuery("#token-box").hide();
				
			//ajax post to admin server
			wl_set_cancelled_state_ajax();
			
			jQuery("#key-section").show();
			jQuery("#token-section").show();
			
			//the button token
			if(buttonToken != null) {
				jQuery("#hosted_button_id").val(buttonToken);
			} else {
				jQuery("#hosted_button_id").val('WKLRWF9WC9UZY');
			}
			
			jQuery("#offer-section").show();
			jQuery("#paypal-subscribe").show();		
			jQuery("#save_options_button").hide();
						
			jQuery("#finished-action").hide();
			jQuery("#subscribed-action").hide();
			jQuery("#key-assigned-action").show();
			
			jQuery("#primary-action-button").val('Save Settings');
		}  */
};
	
WELOCALLY_RegisterWidget.prototype.setStatus = function(statusArea, message, type, showloading){
	var _instance  = this;
	
	jQuery(statusArea).html('');
	jQuery(statusArea).removeClass();
	jQuery(statusArea).addClass(type);
	
	//need a solution for this
	if(showloading){

	}
	
	jQuery(statusArea).append('<em>'+message+'</em>');
	
	if(message != ''){
		jQuery(statusArea).show();
	} else {
		jQuery(statusArea).hide();
	}		
};

WELOCALLY_RegisterWidget.prototype.registrationHandler = function(event, ui) {
	//WELOCALLY.util.log('handler worked');
	
	var _instance = event.data.instance;
	
	_instance.setStatus(_instance._ajaxStatus,'Registering site...', 'wl_message', true);
	
	var ajaxurl = _instance._cfg.endpoint +
		'/admin/signup/3_0/plugin/register.json';
	
	var data = _instance.getSiteInfo();
	
	_instance.jqxhr = jQuery.ajax({
		  type: 'GET',		  
		  url: ajaxurl,
		  data: data,
		  dataType : 'jsonp',
		  beforeSend: function(jqXHR){
			jqxhr = jqXHR;
			jqxhr.setRequestHeader("site-key", _instance._cfg.siteKey);
			if(_instance._cfg.siteToken){
				jqxhr.setRequestHeader("site-token", _instance._cfg.siteToken);
			}			
		  },
		  error : function(jqXHR, textStatus, errorThrown) {
			if(textStatus != 'abort'){
				_instance.setStatus(_instance._ajaxStatus,'ERROR : '+textStatus, 'error', false);
			}		
		  },		  
		  success : function(data, textStatus, jqXHR) {
			if(data != null && data.errors != null) {
				_instance.setStatus(_instance._ajaxStatus,'Could not get publisher info. '+WELOCALLY.util.getErrorString(data.errors), 'wl_error', false);
			} else {
				_instance.setStatus(_instance._ajaxStatus, JSON.stringify(data), 'wl_message', false);
				_instance.setFormFields(data.subscriptionStatus, data.site.key, data.site.home, data.site.name, data.site.email, data.site.token);
				_instance._cfg.registeredCallback(data.site);				
			}
		  }
	});
	
	return false;
	
	
	
}

WELOCALLY_RegisterWidget.prototype.editHandler = function(event, ui) {
	var _instance = event.data.instance;
	
	jQuery(_instance._formArea).find('.wl_field_value').each(function(i,item){
		jQuery(item).toggle();		
	});
	
	jQuery(_instance._formArea).find('.wl_field_area').each(function(i,item){
		jQuery(item).toggle();		
	});
	
	return false;
		
};
