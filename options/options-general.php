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
	$options[ 'default_search_radius' ] = $_POST[ 'welocally_default_search_radius' ];
	
	$options[ 'infobox_title_link' ] = $_POST[ 'welocally_infobox_title_link' ];
	$options[ 'infobox_thumbnail' ] = $_POST[ 'welocally_infobox_thumbnail' ];
	$options[ 'infobox_thumb_width' ] = $_POST[ 'welocally_infobox_thumb_width' ];
	$options[ 'infobox_thumb_height' ] = $_POST[ 'welocally_infobox_thumb_height' ];
	
	$options[ 'map_default_marker' ] = trim($_POST[ 'welocally_map_default_marker' ])? trim($_POST[ 'welocally_map_default_marker' ]):plugins_url() . "/welocally-places/resources/images/marker_generic_32.png";
	$options[ 'map_infobox_marker' ] = trim($_POST[ 'welocally_map_infobox_marker' ])? trim($_POST[ 'welocally_map_infobox_marker' ]):plugins_url() . "/welocally-places/resources/images/tipbox_180.png";
	$options[ 'map_infobox_close' ] = trim($_POST[ 'welocally_map_infobox_close' ])? trim($_POST[ 'welocally_map_infobox_close' ]):plugins_url() . "/welocally-places/resources/images/infobox_close_16.png";
	$options[ 'map_icon_web' ] = trim($_POST[ 'welocally_map_icon_web' ])?trim($_POST[ 'welocally_map_icon_web' ]):plugins_url() . "/welocally-places/resources/images/mapicons_web.png";
	$options[ 'map_icon_directions' ] = trim($_POST[ 'welocally_map_icon_directions' ])? trim($_POST[ 'welocally_map_icon_directions' ]):plugins_url() . "/welocally-places/resources/images/mapicons_car.png";
	$options[ 'map_custom_style' ] = str_replace( '\"', '"', $_POST[ 'welocally_map_custom_style' ] ) ;

	$options[ 'font_place_name' ] = $_POST[ 'welocally_font_place_name' ];
	$options[ 'color_place_name' ] = $_POST[ 'welocally_color_place_name' ];
	$options[ 'size_place_name' ] = $_POST[ 'welocally_size_place_name' ];
	
	$options[ 'font_place_address' ] = $_POST[ 'welocally_font_place_address' ];
	$options[ 'color_place_address' ] = $_POST[ 'welocally_color_place_address' ];
	$options[ 'size_place_address' ] = $_POST[ 'welocally_size_place_address' ];
	
	
	$options[ 'cat_map_select_show' ] = $_POST[ 'welocally_cat_map_select_show' ];
	$options[ 'cat_map_select_title' ] = $_POST[ 'welocally_cat_map_select_title' ];
	$options[ 'cat_map_select_excerpt' ] = $_POST[ 'welocally_cat_map_select_excerpt' ];
	$options[ 'cat_map_infobox_marker' ] = $_POST[ 'welocally_cat_map_infobox_marker' ];
	
	$options[ 'cat_map_select_width' ] = $_POST[ 'welocally_cat_map_select_width' ];
	$options[ 'cat_map_select_height' ] = $_POST[ 'welocally_cat_map_select_height' ];
	$options[ 'cat_map_layout' ] = $_POST[ 'welocally_cat_map_layout' ];
	$options[ 'cat_map_infobox_text_scale' ] = $_POST[ 'welocally_cat_map_infobox_text_scale' ];
	
	
	
	wl_save_options($options);

	echo '<div class="updated fade"><p><strong>' . __( 'Settings Saved.' ) . "</strong></p></div>\n";
}

// Get options
$options = wl_set_general_defaults();

?>

<?php if(is_subscribed()):?>
<style>
	#selectable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
	#selectable li { 
			margin: 3px; 
			width: 53px; height:44px;
			display:inline-block; 
			vertical-align:top; 
    }	
    
    #items ul, #items ul, { margin: 0px;  }
    
    #content-body ul, #content-body ol { margin: 0px;  }


	#selectable .ui-selecting {  }
	#selectable .ui-selected { border: 3px solid #777777; margin: 0px; }
	
	#slider_amount {margin:5px; color: #888888; font-variant: small-caps; font:1.2em Verdana, Arial, Helvetica, sans-serif; text-transform:uppercase}

</style>
<script type="text/javascript" charset="utf-8">
var wl_options_imgfield = '';
jQuery(document).ready(function() {
	jQuery("#welocally_size_place_name").val('<?php echo $options[ 'size_place_name' ]; ?>');
	jQuery("#welocally_size_place_address").val('<?php echo $options[ 'size_place_address' ]; ?>');
	jQuery("#welocally_cat_map_layout").val('<?php echo $options[ 'cat_map_layout' ]; ?>');
	jQuery("#welocally_cat_map_<?php echo $options[ 'cat_map_layout' ]; ?>").addClass('ui-selected');
	jQuery("#welocally_default_search_radius").val('<?php echo wl_get_option('default_search_radius',null) ?>');
	
	jQuery( "#selectable" ).selectable({
		   selected: function(event, ui) { 
		   		var type = ui.selected.id.replace("welocally_cat_map_","");
		   		jQuery( "#welocally_cat_map_layout" ).val(type);
		   }
	});
	
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
	
	window.send_to_editor = function(html) {
	 imgurl = jQuery('img',html).attr('src');
	 jQuery("#"+wl_options_imgfield).val(imgurl);
	 tb_remove();
	}
	
});
</script>


<p><?php _e( 'These are the general settings for Welocally Places.' ); ?></p>

<form method="post" action="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-general' ?>">

<span class="wl_options_heading"><?php _e( 'Places Search Options' ); ?></span>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e( 'Default Search City, State' ); ?></th>
		<td>
		<input id="welocally_default_search_addr" name="welocally_default_search_addr"  type="text" size="36" value="<?php echo $options[ 'default_search_addr' ]; ?>" />
		<br/>
		<span class="description"><?php _e( 'This is the base address you want searches to center from, you can enter a full address or just put the City and State (ie. Oakland, CA)' ); ?></span>
		</td>
	</tr>
   <tr>
	<th scope="row"><?php _e( 'Default Search Radius (km)' ); ?></th>
		<td>
			<select id="welocally_default_search_radius" name="welocally_default_search_radius" >
				<option value="2">2 km</option>
				<option value="4">4 km</option>
				<option value="8">8 km</option>
				<option value="12">12 km</option>
				<option value="16">16 km</option>
				<option value="25">25 km</option>
				<option value="50">50 km</option>
			</select>
			<br/>
			<span class="description"><?php _e( 'This is the radius from which the searches will start, maximum results are 25 places' ); ?></span>
		</td>			
	</tr>	
</table>

<span class="wl_options_heading"><?php _e( 'Map Options' ); ?></span>

<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e('Toggel Options' ); ?></th>
		<td>
			<ul>
				<li><input type="checkbox" id="welocally_infobox_title_link" name="welocally_infobox_title_link" <?php if($options[ 'infobox_title_link' ]=='on') { echo 'checked';  }  ?>>Infobox Link Place Name To Post</li>
				<li><input type="checkbox" id="welocally_infobox_thumbnail" name="welocally_infobox_thumbnail" <?php if($options[ 'infobox_thumbnail' ]=='on') { echo 'checked';  }  ?>>Show Thumbnail In Infobox</li>
			</ul>
		</td>
	</tr>
    <th scope="row"><?php _e( 'Infobox Thumbnail Box Size' ); ?></th>
		<td>
			<input id="welocally_infobox_thumb_width" name="welocally_infobox_thumb_width"  type="text" size="4" 
				value="<?php echo $options[ 'infobox_thumb_width' ]; ?>" /> <span class="description"><?php _e( 'width in pixels' ); ?></span>&nbsp;
			<input id="welocally_infobox_thumb_height" name="welocally_infobox_thumb_height"  type="text" size="4" 
				value="<?php echo $options[ 'infobox_thumb_height' ]; ?>" /> <span class="description"><?php _e( 'height in pixels' ); ?></span>
		</td>			
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Default Marker Image' ); ?></th>
		<td>
		<input id="welocally_map_default_marker" name="welocally_map_default_marker"  type="text" size="36" value="<?php echo $options[ 'map_default_marker' ]; ?>" />
		<input id="upload_image_button_1" type="button" value="Upload Image" /><br/>
		<span class="description"><?php _e( 'This is the marker maps will use for places' ); ?></span>
		</td>
	</tr>	
	<?php 
	/* disabled for now
	<tr valign="top">
		<th scope="row"><?php _e( 'Infobox Marker Image' ); ?></th>
		<td>
		<input id="welocally_map_infobox_marker" name="welocally_map_infobox_marker"  type="text" size="36" value="<?php echo $options[ 'map_infobox_marker' ]; ?>" />
		<input id="upload_image_button_2" type="button" value="Upload Image" /><br/>
		<span class="description"><?php _e( 'This is the image that maps infoboxes use to show the location.' ); ?></span>
		</td>
	</tr> */
	?>
    <tr valign="top">
		<th scope="row"><?php _e( 'Infobox Close Icon' ); ?></th>
		<td>
		<input id="welocally_map_infobox_close" name="welocally_map_infobox_close"  type="text" size="36" value="<?php echo $options[ 'map_infobox_close' ]; ?>" />
		<input id="upload_image_button_3" type="button" value="Upload Image" /><br/>
		<span class="description"><?php _e( 'This is the image that maps infoboxes use to close.' ); ?></span>
		</td>
	</tr>	
	<tr valign="top">
		<th scope="row"><?php _e( 'Web Link Icon' ); ?></th>
		<td>
		<input id="welocally_map_icon_web" name="welocally_map_icon_web"  type="text" size="36" value="<?php echo $options[ 'map_icon_web' ]; ?>" />
		<input id="upload_image_button_4" type="button" value="Upload Image" /><br/>
		<span class="description"><?php _e( 'Icon image for the place website address link.' ); ?></span>
		</td>
	</tr>
    <tr valign="top">
		<th scope="row"><?php _e( 'Driving Directions Icon' ); ?></th>
		<td>
		<input id="welocally_map_icon_directions" name="welocally_map_icon_directions"  type="text" size="36" value="<?php echo $options[ 'map_icon_directions' ]; ?>" />
		<input id="upload_image_button_5" type="button" value="Upload Image" /><br/>
		<span class="description"><?php _e( 'Icon image for the driving directions to the place.' ); ?></span>
		</td>
	</tr>			
	<tr>
	<th scope="row"><?php _e( 'Custom Map Style' ); ?></th>
		<td>
			<textarea rows="4" cols="60" name="welocally_map_custom_style"><?php printf($options[ 'map_custom_style' ]); ?></textarea><br/>
			<span class="description"><?php _e( 'This is the custom styling for your maps. Leave blank to use default style. To style your map use the <a href="http://gmaps-samples-v3.googlecode.com/svn/trunk/styledmaps/wizard/index.html">Maps Style Wizard</a>' ); ?></span>
		</td>
	</tr>	
</table>


<span class="wl_options_heading"><?php _e( 'Fonts' ); ?></span>

<table class="form-table">
	<tr>
	<th scope="row"><?php _e( 'Place Name' ); ?></th>
		<td>
			<input id="welocally_font_place_name" name="welocally_font_place_name"  type="text" size="30"  value="<?php echo $options[ 'font_place_name' ]; ?>" />
			<input id="welocally_color_place_name" name="welocally_color_place_name" class="color" type="text" size="9" value="<?php echo $options[ 'color_place_name' ]; ?>"/>
			<select id="welocally_size_place_name" name="welocally_size_place_name" >
				<option value="0.8">0.8em</option>
				<option value="1.0">1.0em</option>
				<option value="1.2">1.2em</option>
				<option value="1.4">1.4em</option>
				<option value="1.6">1.6em</option>
				<option value="1.8">1.8em</option>
			</select>
			<br/>
			<span class="description"><?php _e( 'This is the custom font for the place name. See all fonts at <a href="http://www.google.com/webfonts#ChoosePlace:select">Google Web Fonts</a>' ); ?></span>
		</td>			
	</tr>	
	<tr>
	<th scope="row"><?php _e( 'Place Address' ); ?></th>
		<td>
			<input id="welocally_font_place_address" name="welocally_font_place_address"  type="text" size="30" value="<?php echo $options[ 'font_place_address' ]; ?>" />
			<input id="welocally_color_place_address" name="welocally_color_place_address" class="color" type="text" size="9" value="<?php echo $options[ 'color_place_address' ]; ?>"/>
			<select id="welocally_size_place_address" name="welocally_size_place_address" >
				<option value="0.8">0.8em</option>
				<option value="1.0">1.0em</option>
				<option value="1.2">1.2em</option>
				<option value="1.4">1.4em</option>
				<option value="1.6">1.6em</option>
				<option value="1.8">1.8em</option>
			</select>
			<br/>
			<span class="description"><?php _e( 'This is the custom font for the place address. See all fonts at <a href="http://www.google.com/webfonts#ChoosePlace:select">Google Web Fonts</a>' ); ?></span>
		</td>			
	</tr>		
	
</table>

<span class="wl_options_heading"><?php _e( 'Category Map' ); ?></span>

<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e('Toggel Options' ); ?></th>
		<td>
			<ul>
				<li><input type="checkbox" id="welocally_cat_map_select_show" name="welocally_cat_map_select_show" <?php if($options[ 'cat_map_select_show' ]=='on') { echo 'checked';  } ?>> Show Select Boxes</li>
				<li><input type="checkbox" id="welocally_cat_map_select_excerpt" name="welocally_cat_map_select_excerpt" <?php if($options[ 'cat_map_select_excerpt' ]=='on') { echo 'checked';  } ?>> Show Excerpt Content</li>
			</ul>
		</td>
	</tr>

	<tr>
	<th scope="row"><?php _e( 'Select Box Size' ); ?></th>
		<td>
			<input id="welocally_cat_map_select_width" name="welocally_cat_map_select_width"  type="text" size="4" 
				value="<?php echo $options[ 'cat_map_select_width' ]; ?>" /> <span class="description"><?php _e( 'width in pixels' ); ?></span>&nbsp;
			<input id="welocally_cat_map_select_height" name="welocally_cat_map_select_height"  type="text" size="4" 
				value="<?php echo $options[ 'cat_map_select_height' ]; ?>" /> <span class="description"><?php _e( 'height in pixels' ); ?></span>
		</td>			
	</tr>
	<tr>
	<th scope="row"><?php _e( 'Select Box Text Scaling' ); ?></th>
		<td>
			<input id="welocally_cat_map_infobox_text_scale" name="welocally_cat_map_infobox_text_scale"  type="hidden"  
				value="<?php echo $options[ 'cat_map_infobox_text_scale' ]; ?>" />
			<div id="slider_amount" style="width:300px; text-align:center;"><?php echo $options[ 'cat_map_infobox_text_scale' ]; ?>%</div>
			<div id="slider" style="width: 300px;"></div>
		</td>			
	</tr>
	
	<tr>
	<th scope="row"><?php _e( 'Category Map' ); ?></th>
		<td>
			<input id="welocally_cat_map_layout" name="welocally_cat_map_layout"  type="hidden"  
				value="<?php echo $options[ 'cat_map_layout' ]; ?>" />
			<div id="layout_items">
					<ol id="selectable">
						<li id="welocally_cat_map_none" class="ui-widget-content">&nbsp;</li>
						<li id="welocally_cat_map_center" class="ui-widget-content">&nbsp;</li>
					</ol>
			</div>

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