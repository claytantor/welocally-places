<style type="text/css">
	.left {
	float: left
	}
	.right {
	float: right;
	}
	.clear {
		margin: 0;
		padding: 0;
		clear: both;
	}
	.arrow_bottom span {
		border-color: #000 transparent transparent transparent;
		margin-top: 6px;
	}
	
	.arrow_right span {
		border-color: transparent transparent transparent #000;
		margin-top: 3px;
	}
	
	.arrow {
		width:0; 
		height:0;
		border-style: solid;
		border-width: 5px; 
		display: block;
		margin-right: 5px;
		float: left;
	}
	.wrap {
		width: 700px;
	}
	#theme_support {
		color: #C14A1C;
		font-weight: bold;
		cursor: pointer;
		margin-top: 10px;
	}
	.theme-support p {
		margin: 0;
	}
	.wrap-theme-support {
		padding: 0 0 50px 5px;
		border-bottom: 1px solid #bbb;
		border-left: 1px solid #bbb;
		border-right: 1px solid #bbb;
		-moz-border-radius: 5px;
		-khtml-border-radius: 5px;
		-webkit-border-radius: 5px;
	}
	p.header {
		background:#f2f2f2 repeat-x scroll left top;
		text-decoration: none;
		font-size: 11px;
		line-height: 16px;
		padding: 5px 11px;
		margin-top:10px;
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
	}
	.theme-option-item textarea {
		width:700px;
	}
</style>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#theme_support').toggle(
			function(){
				jQuery(this).removeClass('arrow_bottom');
				jQuery(this).addClass('arrow_right');
				jQuery(this).parent().find('div').hide('slow');
			},
			function(){
				jQuery(this).removeClass('arrow_right');
				jQuery(this).addClass('arrow_bottom');
				jQuery(this).parent().find('div').show('slow');
		});
		jQuery('.theme-option-item p.toggle').toggle(
				function(){
					jQuery(this).removeClass('arrow_right');
					jQuery(this).addClass('arrow_bottom');
					jQuery(this).parent().find('div').show('slow');
				},
				function(){
					jQuery(this).removeClass('arrow_bottom');
					jQuery(this).addClass('arrow_right');
					jQuery(this).parent().find('div').hide('slow');
			});
	});
	function themeCustomize(){
		jQuery('#ajax_load').show();
		var customize = jQuery('#customize_value').val();
		var submitApplication = function(data) {
			if(data.success == 'true'){
				jQuery('#customize_value').val(data.customize);
				if(data.customize == 'off') {
					jQuery('#theme_opttions').show();
				}
				if (data.customize == 'on') {
					jQuery('#theme_opttions').hide();
				}
				jQuery('#ajax_load').hide();
			}
			else{
				alert(data.error);
				jQuery('#customize').removeAttr("checked");
			    jQuery('#ajax_load').hide();
			}
			    
		};
		jQuery.ajax({
	        type: 'POST',
	        url: ajaxurl,
	        dataType: 'json',
	        success: submitApplication,
	        data: 'action=customize_save&customize='+customize
	 });
	}
</script>
<?php 
global $wlPlaces;
$options = $wlPlaces->getOptions();
if(!isset($options['theme_customize']) || $options['theme_customize'] == ''){
	$options['theme_customize'] = 'off';
	wl_save_options($options);
}

if (!empty($_POST)) {
	foreach($_POST as $key=>$value){
		wl_save_custom_file($key,$value);
	}
}
?>
<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
<h2>Welocally Places Theme Options</h2>
<div class="wrap">
	<form method="post" action="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-theme-options' ?>">
		<span class="wl_options_heading"><?php _e( 'Theme Suport' ); ?></span>
		<div class="theme-support">
			<p id="theme_support" class="header arrow_bottom" ><span class="arrow"></span>Option</p>
			<div class="wrap-theme-support" style="display:block">
				<?php if(get_theme_view_dir() != 'default'): ?>
					<p><img class="left" width="32" height="32" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_check.png" alt="" title=""/><span class="left" style="margin: 9px 0 0 5px;"><?php _e( 'Theme Supported' ); ?>: <?php print get_theme_view_dir();?></span></p>
				<?php else:?>
					<p><img class="left" width="32" height="32" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_cancel.png" alt="" title=""/><span class="left" style="margin: 9px 0 0 5px;"><?php _e( 'Theme Not Supported' ); ?>: <?php print get_current_theme();?></span></p>
				<?php endif;?>
				<div class="clear"></div>
					<p>
					<input id="customize_value" type="hidden" <?php if($options['theme_customize'] == 'on'):?> value="off" <?php else:?> value ="on" <?php endif;?> />
					<input id="customize" type="checkbox" <?php if($options['theme_customize'] == 'on'):?> checked="checked" <?php endif;?>  onChange="themeCustomize();">
					<label for="customize">Customize</label></p>
			</div>
		</div>
		<p><img id="ajax_load" style="display:none;" width="48" height="48" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/ajax-loading.gif"  title="loading"/></p>
		<ul id="theme_opttions" <?php if($options['theme_customize'] == 'off'):?> style="display:none" <?php else:?> style="display: block;" <?php endif;?>>
		<li class="theme-option-item">
			<p class="header toggle arrow_right"><span class="arrow"></span>Category Map</p>
			<div style="display: none">
				<textarea id="option_category" name="category-places-map" rows="10" cols="107"><?php wl_read_custom_file('category-places-map.php');?></textarea>
			</div>
		</li>
		<li class="theme-option-item">
			<p class="header toggle arrow_right"><span class="arrow"></span>Map Widget</p>
			<div style="display: none">
				<textarea id="option_map_widget" name="welocally-places-map-widget-display" rows="10" cols="107"><?php wl_read_custom_file('welocally-places-map-widget-display.php');?></textarea>
			</div>
		</li>
		<li class="theme-option-item">
			<p class="header toggle arrow_right"><span class="arrow"></span>List Widget</p>
			<div style="display: none">
				<textarea id="option_list_widget" name="welocally-places-list-widget-display" rows="10" cols="107"><?php wl_read_custom_file('welocally-places-list-widget-display.php');?></textarea>
			</div>
		</li>
		</ul>
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Settings' ); ?>"/></p>
		
	</form>
</div>