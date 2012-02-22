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
?>
<?php
// If options have been updated on screen, update the database
if ( ( !empty( $_POST ) ) && ( check_admin_referer( 'welocally-places-manager', 'welocally_places_manager_nonce' ) ) ) { 
	
	if(!empty($_POST['post_id'])){
		delete_post_places($_POST['post_id']);
	}
	
	if(!empty($_POST['post_id_meta'])){
		delete_post_places_meta($_POST['post_id_meta']);
	}
		
	echo '<div class="updated fade"><p><strong>' . __( 'Places Removed.' ) . "</strong></p></div>\n";
}

//display error if not subscribed
if(!is_subscribed()) {
	echo '<div class="error fade"><p><strong>' . __( 'Please Register To Activate Welocally Places' ) . "</strong></p></div>\n";
} 

?>
<form method="post" action="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-manager' ?>">
<fieldset>
<span class="wl_options_heading"><?php _e( 'Places' ); ?></span>
<table class="form-table">	
<?php
$places_found = false;
$index = 0;
$pindex = 0;
$posts = get_places($limit, null, null ,WelocallyPlaces::CATEGORYNAME);
foreach( $posts as $post ) {
    $places = get_post_places($post->ID);  
    echo("<tr class=\"d".($index & 1)."\">");
    if(count($places)>0):
    $places_found = true;
?>
	<td>
		<div>
			<input type="checkbox" name="post_id[]" value="<?php echo($post->ID) ?>" />
			<a href="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/post.php?post='.$post->ID.'&action=edit' ?>"><strong><?php print_r($post->post_title)  ?></strong></a> Places Linked: <?php echo(count($places));?>
		</div>
<?php    
    foreach ($places as $place):
?>
		<div style="border: solid #808080 1px; margin: 5px; background: #f9f9f9;">
			<table>
				<tr>
					<td width="150" style="background: #f9f9f9;">   <strong><?php echo($place->properties->name);?></strong></td>
					<td width="200" style="background: #f9f9f9;"><?php echo($place->properties->address." ".$place->properties->city." ".$place->properties->state." ".$place->properties->postcode); ?></td>		
					<td align="left" style="background: #f9f9f9;"><a href="#" onclick="jQuery('#post_place_<?php echo($pindex) ?>').toggle(); return false;">show details</a></td>		
							
				</tr>
			</table>
			<div id="post_place_<?php echo($pindex) ?>" style="display:none; padding:10px">
		   		<pre><?php print_r($place) ?></pre>
			</div>
		</div>
			

<?php
	$pindex=$pindex+1;
	endforeach;
	endif;
?>
	</td>	
	</tr>	
<?php	
	$index=$index+1;
}
if($places_found):
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
</table>
</form>
	
<form method="post" action="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-manager' ?>">
<fieldset>
<span class="wl_options_heading"><?php _e( 'Legacy Places Support' ); ?></span>
<table class="form-table">
	<tr valign="top">	
		<td>			
		<?php if(get_places_legacy_count() > 0): ?>	
		<div><img width="48" height="48" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_cancel.png" alt="" title=""/></div>
		<div><strong>You need to relink the places in this section!</strong>&nbsp; You have recenty upgraded Welocally Places. <?php print_r(get_places_legacy_count());  ?> Legacy Posts were found.  
		We have created a <a href="http://welocally.com/?p=780" target="_new">Release Guide</a>. Finally, if you have problems <a href="http://www.welocally.com/?page_id=139" target="_new">email us</a>. 
		<p/><strong>ALWAYS BACKUP PRIOR TO UPGRADE</strong></div>
		<?php else: ?>
		<img width="48" height="48" src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/Crystal_Clear_check.png" alt="" title=""/>	Your places are up to date. 	
		<?php endif; ?>
		</td>
	</tr>
<?php
$places_found_meta = false;
$index = 0;
foreach( $posts as $post ) {
    $places = get_post_places_meta($post->ID);  
    echo("<tr class=\"d".($index & 1)."\">");
    if(count($places)>0):
    $places_found_meta = true;
?>
		<td>
			<div>
				<input type="checkbox" name="post_id_meta[]" value="<?php echo($post->ID) ?>" />
				<a href="<?php echo get_bloginfo( 'wpurl' ).'/wp-admin/post.php?post='.$post->ID.'&action=edit' ?>"><strong><?php print_r($post->post_title)  ?></strong></a> Places Linked: <?php echo(count($places));?>
				&nbsp; <a href="#" onclick="jQuery('#post_meta_place_<?php echo($post->ID) ?>').toggle(); return false;">show details</a>
			</div>
			<div id="post_meta_place_<?php echo($post->ID) ?>" style="display:none">
		   		<pre><?php print_r($places) ?></pre>
		   </div>	   
	   </td>
	</tr>
<?php
	endif;
	$index=$index+1;
}

if($places_found_meta):
?>	
	<tr valign="top">
		<td colspan="2">
			<?php wp_nonce_field( 'welocally-places-manager','welocally_places_manager_nonce', true, true ); ?>
			<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Delete Post Meta' ); ?>"/></p>		
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
</table>
</form>	
	
				
</div> 