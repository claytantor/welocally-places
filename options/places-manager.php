<?php
global $wlPlaces,$wpdb; 
$places = $wlPlaces->getPlacesNew();
?>  
<script type="text/javascript">
function setPlaceRow(i, placeJSON){
	var place = jQuery.parseJSON(placeJSON);
	window.wlPlacesManager.setPlaceRow(i, place, jQuery('#wl_placemgr_place_'+i));
}
</script>
<?php 
$menubar_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/options/options-infobar.php';
include($menubar_include);
?>
<div class="wrap">
<div class="icon32"><img src="<?php echo WP_PLUGIN_URL; ?>/welocally-places/resources/images/screen_icon.png" alt="" title="" height="32px" width="32px"/><br /></div>
<h2>Welocally Places Manager</h2>

<?php

// If options have been updated on screen, update the database
if ( ( !empty( $_POST ) ) && ( check_admin_referer( 'welocally-places-manager', 'welocally_places_manager_nonce' ) ) ) { 
	
	if(!empty($_POST['post_place_id'])){
		delete_post_places($_POST['post_place_id']);		
	}
	
	echo '<div class="updated fade"><p><strong>' . __( 'Places Removed.' ) . "</strong></p></div>\n";
}

?>
<form method="post" action="<?php echo bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=welocally-places-manager' ?>">

<div class="template-wrapper" style="margin-top: 20px;">
		<div id="wl_places_mgr_wrapper"></div>
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
			
			window.wlPlacesManager = 
				  new WELOCALLY_PlaceManager();			
			window.wlPlacesManager.initCfg(cfg);
			jQuery('#wl_places_mgr_wrapper' ).html(window.wlPlacesManager.makeWrapper());  		
		 
	 		 </script>
		</div>
	</div>
			

</form>
</div> 