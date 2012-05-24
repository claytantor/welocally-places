<?php
/**
 * this stuff should all be moved into the class.
 * 
 */

function wl_menu_initialise() {
	
	//jquery ui
	wp_register_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/smoothness/jquery-ui.css' );

	$main_slug = add_menu_page( 'Welocally Places Options', 'Welocally Places', 'manage_options', 'welocally-places-general', 'wl_general_options', WP_PLUGIN_URL . '/welocally-places/resources/images/welocally_places_button_color.png' );
	$main_content =  file_get_contents(dirname( __FILE__ ) . '/help/options-general-help.php');
	add_contextual_help( $main_slug, __( $main_content ) );
	
	$about_slug = wl_add_submenu( 'Welocally Places About', 'About', 'welocally-places-about', 'wl_support_about' );
	$placesmgr_slug = wl_add_submenu( 'Welocally Places Manager', 'Places Manager', 'welocally-places-manager', 'wl_places_manager' );

	add_filter( 'plugin_action_links', 'wl_places_add_settings_link', 10, 2 );
	
	//hook so only the admin placemgr screen get jquery ui, conflicts were occurring
	add_action( 'admin_print_styles-' . $placesmgr_slug, 'wl_placesmgr_plugin_admin_styles' );
		
}

add_action( 'admin_menu','wl_menu_initialise' );
add_filter( 'plugin_row_meta', 'wl_set_plugin_meta', 10, 2 );

function wl_placesmgr_plugin_admin_styles() {	
	wp_enqueue_style( 'jquery-ui-style' );		
}

function wl_general_options() {
	include_once( WP_PLUGIN_DIR . "/welocally-places/options/options-general.php" );
}

function wl_support_about() {
	include_once( WP_PLUGIN_DIR . "/welocally-places/options/about.php" );
}

function wl_places_manager() {
	
	global $wpdb;
	
	$t = new StdClass();
	$t->uid = uniqid();
	$t->table = $wpdb->prefix.'wl_places';
	$t->fields = 'id,place';
	$t->filter = null;
	$t->orderBy = 'created desc';
	$t->odd = 'wl_placemgr_place_odd';
	$t->even = 'wl_placemgr_place_even';
	$t->pagesize = 10;
	$t->content = '<div class="%ROW_TOGGLE%" style="display:block;">' .
			'<div class="wl_placemgr_place_id field_inline">%id%</div>'.
			'<div class="wl_placemgr_place field_inline" id="wl_placemgr_place_%id%"></div>'.
			'<script type="text/javascript">var pval = %place%;' .
			'setPlaceRow(%id%, pval);' .
			'</script>'.
			'</div>';	
	
	ob_start();
	$imagePrefix = WP_PLUGIN_URL.'/welocally-places/resources/images/';
    include(dirname( __FILE__ ) . '/options/places-manager.php');
    $main_content = ob_get_contents();
    ob_end_clean();
    
    $t = null;
	echo $main_content;
}


function wl_places_add_settings_link( $links, $file ) {

	static $this_plugin;

	if ( !$this_plugin ) { $this_plugin = plugin_basename( __FILE__ ); }

	if ( strpos( $file, 'welocally-places.php' ) !== false ) {
		$settings_link = '<a href="admin.php?page=welocally-places-general">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}


function wl_set_plugin_meta( $links, $file ) {

	if ( strpos( $file, 'welocally-places.php' ) !== false ) {
		$links = array_merge( $links, array( '<a href="http://support.welocally.com/categories/welocally-places-wp-basic" target="_new">' . __( 'Support' ) . '</a>' ) );		
		$links = array_merge( $links, array( '<a href="http://welocally.com/?page_id=139" target="_new">' . __( 'Contact' ) . '</a>' ) );	
		$links = array_merge( $links, array( '<a href="admin.php?page=welocally-places-manager">' . __( 'Places Manager' ) . '</a>' ) );				
		$links = array_merge( $links, array( '<a href="admin.php?page=welocally-places-about">' . __( 'About' ) . '</a>' ) );		
	}

	return $links;
}


function wl_add_submenu( $page_title, $menu_title, $menu_slug, $function ) {

	$profile_slug = add_submenu_page( 'welocally-places-general', $page_title, $menu_title, 'manage_options', $menu_slug, $function );

	if ( $menu_slug == "welocally-places-general" ) { $help_text = file_get_contents(dirname( __FILE__ ) . '/help/options-general-help.php'); }
	
	if ( $menu_slug == "welocally-places-about" ) { $help_text = file_get_contents(dirname( __FILE__ ) . '/help/about-help.php'); } 
	
	if ( $menu_slug == "welocally-places-manager" ) { $help_text = file_get_contents(dirname( __FILE__ ) . '/help/manager-help.php'); } 
	
	add_contextual_help( $profile_slug, __( $help_text ) );

	return $profile_slug;
}

?>