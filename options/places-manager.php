<?php
global $wlPlaces; 

?>  

<style type="text/css">
tr.d0 td {
	background-color: #E1F0D3; color: black;
}
tr.d1 td {
	background-color: #CAE0B4; color: black;
}

</style>

<div class="wrap">
<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
<h2>Welocally Places Manager</h2>

<?php 
$menubar_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/options/options-infobar.php';
include($menubar_include);

// If options have been updated on screen, update the database
if ( ( !empty( $_POST ) ) && ( check_admin_referer( 'welocally-places-manager', 'welocally_places_manager_nonce' ) ) ) { 
	
	if(!empty($_POST['post_place_id'])){
		delete_post_places($_POST['post_place_id']);		
	}
	
	
	echo '<div class="updated fade"><p><strong>' . __( 'Places Removed.' ) . "</strong></p></div>\n";
}

?>

<?php if(empty($options['siteToken'])  ):?>
<div class="wl_error fade"><p><strong>Please <a href="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-subscribe' ?>">Register Now</a> To Activate Welocally Places</strong></p></div>

<?php else: ?>

<form method="post" action="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-manager' ?>">
<span class="wl_options_heading"><?php _e( 'Post Places: '.count($t->places) ); ?></span>
<p/>
<?php
$t = $wlPlaces->getPlaces($wlPlaces->placeCategory(),500, false);
foreach ($t->places as $place) :
?>

		<div style="border: solid #808080 1px; margin: 5px; background: #f9f9f9;">
			<table>
				<tr>
					<td width="20" style="background: #f9f9f9;"><input type="checkbox" name="post_place_id[]" value="<?php echo($place->post->ID.","); echo($place->_id) ?>" /></td>
					<td width="150" style="background: #f9f9f9;"><strong><?php echo($place->properties->name);?></strong></td>
					<td width="150" style="background: #f9f9f9;">
					<a href="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/post.php?post='.$place->post->ID.'&action=edit' ?>"><strong><?php print_r($place->post->post_title)  ?></strong></a> 
					</td>
					<td width="200" style="background: #f9f9f9;"><?php echo($place->properties->address." ".$place->properties->city." ".$place->properties->state." ".$place->properties->postcode); ?></td>		
					<td align="left" style="background: #f9f9f9;"><a href="#" onclick="jQuery('#post_place_<?php echo($pindex) ?>').toggle(); return false;">show details</a></td>		
							
				</tr>
			</table>
			<div id="post_place_<?php echo($pindex) ?>" style="display:none; padding:10px">
		   		<pre><?php print_r($place) ?></pre>
			</div>
		</div>
			

<?php	
endforeach;
if(count($t->places)>0):
?>		
	<tr valign="top">
		<td colspan="2">
			<?php wp_nonce_field( 'welocally-places-manager','welocally_places_manager_nonce', true, true ); ?>
			<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Delete Post Places' ); ?>"/></p>		
		</td>
	</tr>
<?php 
else:
?>
	<tr valign="top">
		<td colspan="2">
			<h3>No places found.</h3>
		</td>
	</tr>
<?php
endif;
?>
<?php endif; ?>
</form>
</div> 