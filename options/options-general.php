<?php
global $wlPlaces;
?>
<div class="wrap">
<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
<h2>Welocally Places Options</h2>
<?php 
$menubar_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/options/options-infobar.php';
include($menubar_include);
?>
<?php
// If options have been updated on screen, update the database


if(!is_subscribed()) {
	echo '<div class="error fade"><p><strong>' . __( 'Please Register To Activate Welocally Places' ) . "</strong></p></div>\n";
} 

if ( ( !empty( $_POST ) ) && ( check_admin_referer( 'welocally-places-general', 'welocally_places_general_nonce' ) ) ) { 
	
	$options = wl_get_options();
	
	
	$options[ 'default_search_addr' ] = $_POST[ 'welocally_default_search_addr' ];
	
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
	
	//welocally_show_letters
	$options[ 'show_selection' ] = $_POST[ 'welocally_show_selection' ];
	if(!isset($options['show_selection']) || $options['show_selection'] == ''){
		$options['show_selection'] = 'off';
	} 
	
		
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
	
	
	wl_save_options($options);

	echo '<div class="updated fade"><p><strong>' . __( 'Settings Saved.' ) . "</strong></p></div>\n";
}

// Get options
$options = wl_set_general_defaults();

?>

<?php if(is_subscribed()):?>

<script type="text/javascript" charset="utf-8">
var wl_options_imgfield = '';
jQuery(document).ready(function() {
	jQuery("#welocally_size_place_name").val('<?php echo $options[ 'size_place_name' ]; ?>');
	jQuery("#welocally_size_place_address").val('<?php echo $options[ 'size_place_address' ]; ?>');
	
	
	/*
	jQuery("#slider").slider(
		{ 
			min: 0, 
			max: 200,
			stop: function(event, ui) {
				var value = jQuery("#slider").slider( "option", "value" );
				jQuery("#welocally_cat_map_infobox_text_scale").val(value);	
				jQuery("#slider_amount").html(value+"%");
							
			} 
		}
	);
	
	jQuery("#slider").slider( "option", "value", <?php echo $options[ 'cat_map_infobox_text_scale' ]; ?> );
	
	*/
	
	window.send_to_editor = function(html) {
	 imgurl = jQuery('img',html).attr('src');
	 jQuery("#"+wl_options_imgfield).val(imgurl);
	 tb_remove();
	}
	
});
</script>


<p><?php _e( 'These are the general settings for Welocally Places.' ); ?></p>

<form method="post" action="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-general' ?>">

<span class="wl_options_heading"><?php _e( 'General Settings' ); ?></span>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e( 'Default Search City, State' ); ?></th>
		<td>
		<input id="welocally_default_search_addr" name="welocally_default_search_addr"  type="text" size="36" value="<?php echo $options[ 'default_search_addr' ]; ?>" />
		<br/>
		<span class="description"><?php _e( 'This is the base address you want searches to center from, you can enter a full address or just put the City and State (ie. Oakland, CA)' ); ?></span>
		</td>
	</tr>
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
			</ul>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Category Tag Map Options' ); ?></th>
		<td>
			<ul>
				<li><input type="checkbox" id="welocally_show_letters_tag" name="welocally_show_letters_tag" <?php if($options[ 'show_letters_tag' ]=='on') { echo 'checked';  }  ?>>Show Indexed Markers</li>
				<li><input type="checkbox" id="welocally_show_selection_tag" name="welocally_show_selection_tag" <?php if($options[ 'show_selection_tag' ]=='on') { echo 'checked';  }  ?>>Show Selection Item List</li>
			</ul>
		</td>
	</tr>
	
</table>


<?php wp_nonce_field( 'welocally-places-general','welocally_places_general_nonce', true, true ); ?>

<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Settings' ); ?>"/></p>

</form>
<?php else:?>
<div style="text-align:center;">
	<img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/subscribe_intro.png" alt="" title=""/>
</div>
<div style="text-align:center;">
	<a href="admin.php?page=welocally-places-subscribe"><img border="0" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/subscribe_button.png" alt="" title=""/></a>
</div>
<?php endif; ?>

</div>