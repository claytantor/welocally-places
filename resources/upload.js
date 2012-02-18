var wl_options_imgfield = '';
jQuery(document).ready(function() {

	
	jQuery('#upload_image_button_1').click(function() {
	 
	 wl_options_imgfield = jQuery('#welocally_map_default_marker').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});
	
	jQuery('#upload_image_button_2').click(function() {
	 
	 wl_options_imgfield = jQuery('#welocally_map_infobox_marker').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});
	
	jQuery('#upload_image_button_3').click(function() {
	 
	 wl_options_imgfield = jQuery('#welocally_map_infobox_close').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});
	
	jQuery('#upload_image_button_4').click(function() {
	 
	 wl_options_imgfield = jQuery('#welocally_map_icon_web').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});
	
	jQuery('#upload_image_button_5').click(function() {
	 
	 wl_options_imgfield = jQuery('#welocally_map_icon_directions').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});
	
	window.send_to_editor = function(html) {
	 imgurl = jQuery('img',html).attr('src');
	 jQuery("#"+wl_options_imgfield).val(imgurl);
	 tb_remove();
	}

});