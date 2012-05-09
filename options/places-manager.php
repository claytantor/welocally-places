<?php
global $wlPlaces; 
$places = $wlPlaces->getPlacesNew();
?>  
<script type="text/javascript">
jQuery(function() {
	if(!window.wlPlacesManager){
		window.wlPlacesManager = new WELOCALLY_PlaceManager();
		window.wlPlacesManager.initCfg({});
	}	

});
</script>
<style type="text/css">
tr.d0 td {
	background-color: #E1F0D3; color: black;
}
tr.d1 td {
	background-color: #CAE0B4; color: black;
}

.place-field{ display: inline-block; margin-right:10px;}

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
<form method="post" action="<?php echo bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-manager' ?>">
<?php
if(count($places)>0):?>
<span class="wl_options_heading"><?php _e( 'Places' ); ?></span>
<p/>

<div class="template-wrapper">
		<div>
			<script type="text/javascript" charset="utf-8">
			var cfg = { 
				id:  '<?php echo $t->uid; ?>', 
				ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
				table:  '<?php echo $t->table; ?>',
				pagesize:  '<?php echo $t->pagesize; ?>', 
				fields:  '<?php echo $t->fields; ?>', 
				filter:  '<?php echo $t->filter; ?>', 
				orderBy:  '<?php echo $t->orderBy; ?>', 
				odd:  '<?php echo $t->odd; ?>',
				even:  '<?php echo $t->even; ?>',				 			
				content:  '<?php echo base64_encode($t->content); ?>' 
			};			    		
			var pager<?php echo $t->uid; ?> = 
				  new WELOCALLY_WpPager(cfg)
			  		.init();			 
	 		 </script>
		</div>
	</div>

		
	<tr valign="top">
		<td colspan="2">
			<?php wp_nonce_field( 'welocally-places-manager','welocally_places_manager_nonce', true, true ); ?>
			<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Delete Post Places' ); ?>"/></p>		
		</td>
	</tr>
<?php
else:?>
	<tr valign="top">
		<td colspan="2">
			<span class="wl_options_heading"><?php _e( 'No Places Found' ); ?></span>
		</td>
	</tr>

<?php endif;
?>
</form>
</div> 