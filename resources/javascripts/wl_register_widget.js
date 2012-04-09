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
		
		this.showFields();
		
		//get the status
		this.getSubscriberInfo(cfg.siteKey, cfg.siteHome, cfg.siteName, cfg.siteEmail, cfg.siteToken);
				
		return this;
		
		
	};
}

WELOCALLY_RegisterWidget.prototype.makeFormArea = function(){
	var formArea = jQuery('<div></div>');
	
	//key1 hidden field
	jQuery(formArea).append('<input id="wl_register_key1" type="hidden" name="wl_register_key1" />');
	
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
			siteToken: jQuery("#wl_register_token").val(),
			key1:jQuery("#wl_register_key1").val()
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
				_instance.setStatus(_instance._ajaxStatus,'<strong>Issue with publisher info.</strong> '+WELOCALLY.util.getErrorString(data.errors), 'wl_error', false);
				_instance.setFormFields("DENIED", siteKey, siteHome, siteName, siteEmail, siteToken, null);
				
			  } else {	
				  _instance.setStatus(_instance._ajaxStatus, '<span class="wl_subscription_status">'+data.subscriptionStatus+'</span>', 'wl_message', false);  
				  _instance.setFormFields(data.subscriptionStatus, data.site.key, siteHome, siteName, siteEmail, siteToken, data.key1);
				
			  }	
		  }
		});

};



WELOCALLY_RegisterWidget.prototype.setFormFields = 
	function(status, siteKey, siteHome, siteName, siteEmail, siteToken, key1){
	
	var _instance = this;
	
	if(status != null) {
		if(status == 'KEY_ASSIGNED') {
			jQuery("#wl_register_status_area").html('<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" src="'+
				_instance._cfg.imagePath+'/free1.png" alt="" title=""/>'+
				'<span class="options-text">We have assigned a key to you, but to use or basic service you must register. Go ahead, its easy. Just press the <strong>Register Now</strong> button and '+
				'we will send you your secret token.</span><div style="clear:both"></div>');
			jQuery(_instance._formArea).find('#wl_register_submit_action').html('Register Now');
			jQuery(_instance._formArea).find('#wl_register_token_area').hide();
			_instance.showFields();
		} else if (status == 'REGISTERED') {
			jQuery("#wl_register_status_area").html('<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" src="'+_instance._cfg.imagePath+'/token1.png" alt="" title=""/>'+
			'<span class="options-text">Great you are almost there! Now look the your inbox for the email address you gave us, and there should be an email with '+
			'your free token. Presss the <strong>Edit Fields</strong> button, and then enter it in into the Publisher Token field and press the <strong>Save Token</strong> button.</span>'+
			'<div style="clear:both"></div>');
			jQuery(_instance._formArea).find('#wl_register_submit_action').html('Save Token');
			jQuery(_instance._formArea).find('#wl_register_token_area').show();
			_instance.showFields();
		} else if (status == 'SUBSCRIBED') {
			jQuery("#wl_register_status_area").html('<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" '+
					'src="'+_instance._cfg.imagePath+'/subscribed1.png" alt="" title=""/>'+
			'<span class="options-text">You did it! You signed up for our product and now you can use it. '+
			'You have been given access to our user portal with help, support and a whole bunch of other '+
			'free welocally resouces. Be sure to <a href="'+_instance._cfg.endpoint+'/admin/home" target="_blank">log into your portal</a> as soon as possible.</span>'+
			'<div style="clear:both"></div>');
			jQuery(_instance._formArea).find('#wl_register_submit_action').html('Refresh Registration');
			jQuery(_instance._formArea).find('#wl_register_token_area').show();
			_instance.showFields();
		} else if (status == 'DENIED') {
			jQuery("#wl_register_status_area").html('<img width="75" hieght="75" style="float: left; margin-right:5px" class="align-right" '+
					'src="'+_instance._cfg.imagePath+'/denied1.png" alt="" title=""/>'+
			'<span class="options-text">There activity you attemped was denied, this is probably a license issue. Be sure to <a href="'+_instance._cfg.endpoint+'/admin/home" target="_blank">log into your portal</a> and add this site, or contact Welocally.</span>'+
			'<div style="clear:both"></div>');
			jQuery(_instance._formArea).find('#wl_register_submit_action').html('Refresh Registration');
			_instance.showEditable();
			jQuery(_instance._formArea).find('#wl_register_token_area').show();
		}
	
		
		//LICENSE_ISSUE
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
	
	if(key1 != null) {
		jQuery("#wl_register_key1").val(key1);	
	}
	
	jQuery(_instance._formArea).show();
	

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
				_instance.setStatus(_instance._ajaxStatus,'<strong>Could register publisher.</strong> '+WELOCALLY.util.getErrorString(data.errors), 'wl_error', false);
				var origData = _instance.getSiteInfo();
				_instance.setFormFields("DENIED", origData.siteKey, origData.siteHome, origData.siteName, origData.siteEmail, origData.siteToken, origData.key1);
			} else {
				_instance.setStatus(_instance._ajaxStatus, '<span class="wl_subscription_status">'+data.subscriptionStatus+'</span>', 'wl_message', false);
				_instance.setFormFields(data.subscriptionStatus, data.site.key, data.site.home, data.site.name, data.site.email, data.site.token, data.key1);
				data.site.key1 = data.key1; 
				_instance._cfg.registeredCallback(data.site);				
			}
		  }
	});
	
	return false;
	
	
	
}

WELOCALLY_RegisterWidget.prototype.editHandler = function(event, ui) {
	var _instance = event.data.instance;
	 
	//copy all field area to values weird
	jQuery('#wl_register_value_site_name')
		.html(jQuery('#wl_register_site_name').val());	
	
	jQuery('#wl_register_value_site_home')
		.html(jQuery('#wl_register_site_home').val());	
	
	jQuery('#wl_register_value_email')
		.html(jQuery('#wl_register_email').val());	
	
	jQuery('#wl_register_value_key')
		.html(jQuery('#wl_register_key').val());	
	
	jQuery('#wl_register_value_token')
		.html(jQuery('#wl_register_token').val());	
			
	jQuery(_instance._formArea).find('.wl_field_value').each(function(i,item){
		jQuery(item).toggle();		
	});
	
	jQuery(_instance._formArea).find('.wl_field_area').each(function(i,item){
		jQuery(item).toggle();		
	});
	
	return false;
		
};

WELOCALLY_RegisterWidget.prototype.showEditable = function() {
	var _instance = this;
	
	jQuery(_instance._formArea).find('.wl_field_value').each(function(i,item){
		jQuery(item).hide();		
	});
	
	jQuery(_instance._formArea).find('.wl_field_area').each(function(i,item){
		jQuery(item).show();		
	});
	
	return false;
		
};

WELOCALLY_RegisterWidget.prototype.showFields = function() {
	var _instance = this;
	
	jQuery(_instance._formArea).find('.wl_field_value').each(function(i,item){
		jQuery(item).show();		
	});
	
	jQuery(_instance._formArea).find('.wl_field_area').each(function(i,item){
		jQuery(item).hide();		
	});
	
	return false;
		
};
