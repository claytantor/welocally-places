<?php
global $wlPlaces;
$options = $wlPlaces->getOptions();
?>
<?php 
$menubar_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/options/options-infobar.php';
include($menubar_include);
?> 
<div class="wrap">
<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
<h2>Welocally Places Options</h2>
<?php
if ( ( !empty( $_POST ) ) && ( check_admin_referer( 'welocally-places-general', 'welocally_places_general_nonce' ) ) ) { 
	
	//infobox_title_link
	$options[ 'infobox_title_link' ] = $_POST[ 'welocally_infobox_title_link' ];
	if(!isset($options['infobox_title_link']) || $options['infobox_title_link'] == ''){
		$options['infobox_title_link'] = 'off';
	} 
	
	
	//widget
	//welocally_show_letters
	$options[ 'show_letters' ] = $_POST[ 'welocally_show_letters' ];
	if(!isset($options['show_letters']) || $options['show_letters'] == ''){
		$options['show_letters'] = 'off';
	} 
	$options[ 'widget_post_type' ] = $_POST[ 'welocally_widget_post_type' ];
	
	//welocally_show_letters
	$options[ 'show_selection' ] = $_POST[ 'welocally_show_selection' ];
	if(!isset($options['show_selection']) || $options['show_selection'] == ''){
		$options['show_selection'] = 'off';
	} 
	
	$options[ 'widget_selection_style' ] = $_POST[ 'welocally_widget_selection_style' ];
	 
	
		
	//welocally_show_letters_tag
	$options[ 'show_letters_tag' ] = $_POST[ 'welocally_show_letters_tag' ];
	if(!isset($options['show_letters_tag']) || $options['show_letters_tag'] == ''){
		$options['show_letters_tag'] = 'off';
	} 
	
	//welocally_show_letters_tag
	$options[ 'show_selection_tag' ] = $_POST[ 'welocally_show_selection_tag' ];
	if(!isset($options['show_selection_tag']) || $options['show_selection_tag'] == ''){
		$options['show_selection_tag'] = 'off';
	} 
	
	$options[ 'tag_selection_style' ] = $_POST[ 'welocally_tag_selection_style' ];
		
	wl_save_options($options);

	echo '<div class="updated fade"><p><strong>' . __( 'Settings Saved.' ) . 
		"</strong></p></div>\n";
}


?>

<script type="text/javascript" charset="utf-8">
var wl_options_imgfield = '';
jQuery(document).ready(function() {
	window.send_to_editor = function(html) {
	 imgurl = jQuery('img',html).attr('src');
	 jQuery("#"+wl_options_imgfield).val(imgurl);
	 tb_remove();
	}
	
});
</script>


<form method="post" action="<?php echo bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-general' ?>">
<table class="wl-form-table" style="margin-top:20px;">
	<tr valign="top">
		<th scope="row"><?php _e('General Map Options' ); ?></th>
		<td>
			<ul>
				<li><input type="checkbox" id="welocally_infobox_title_link" name="welocally_infobox_title_link" <?php if($options[ 'infobox_title_link' ]=='on') { echo 'checked';  }  ?>>Infobox Link Place Name To Post</li>
			</ul>
		</td>
	</tr
	<tr valign="top">
		<th scope="row"><?php _e('Category Widget Map Options' ); ?></th>
		<td>
			<ul>
				<li><input type="checkbox" id="welocally_show_letters" name="welocally_show_letters" <?php if($options[ 'show_letters' ]=='on') { echo 'checked';  }  ?>>Show Indexed Markers</li>
				<li><input type="checkbox" id="welocally_show_selection" name="welocally_show_selection" <?php if($options[ 'show_selection' ]=='on') { echo 'checked';  }  ?>>Show Selection Item List</li>
				<li>Selection Item Style<br /><input style="width:400px" type="text" id="welocally_widget_selection_style" name="welocally_widget_selection_style" value="<?php echo $options[ 'widget_selection_style' ];?>"></li>
				<li>Post Types (comma separated) <br /><input style="width:400px" type="text" id="welocally_widget_post_type" name="welocally_widget_post_type" value="<?php echo $options[ 'widget_post_type' ];?>"></li>
			</ul>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Category Tag Map Options' ); ?></th>
		<td>
			<ul>
				<li><input type="checkbox" id="welocally_show_letters_tag" name="welocally_show_letters_tag" <?php if($options[ 'show_letters_tag' ]=='on') { echo 'checked';  }  ?>>Show Indexed Markers</li>
				<li><input type="checkbox" id="welocally_show_selection_tag" name="welocally_show_selection_tag" <?php if($options[ 'show_selection_tag' ]=='on') { echo 'checked';  }  ?>>Show Selection Item List</li>
				<li>Selection Item Style<br/><input style="width:400px" type="text" id="welocally_tag_selection_style" name="welocally_tag_selection_style" value="<?php echo $options[ 'tag_selection_style' ];?>"></li>
			</ul>
		</td>
	</tr>
	
</table>


<?php wp_nonce_field( 'welocally-places-general','welocally_places_general_nonce', true, true ); ?>

<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Settings' ); ?>"/></p>

</form>

</div>