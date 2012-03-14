<?php
function wl_menu_initialise() {

	$main_slug = add_menu_page( 'Welocally Places Options', 'Welocally Places', 'manage_options', 'welocally-places-general', 'wl_general_options', WP_PLUGIN_URL . '/welocally-places/resources/images/welocally_places_button_color.png' );
	$main_content =  file_get_contents(dirname( __FILE__ ) . '/help/options-general-help.php');
	add_contextual_help( $main_slug, __( $main_content ) );
	
	wl_add_submenu( 'Welocally Places Registration', 'Register', 'welocally-places-subscribe', 'wl_places_subscribe' );
	wl_add_submenu( 'Welocally Places About', 'About', 'welocally-places-about', 'wl_support_about' );
	wl_add_submenu( 'Welocally Places Manager', 'Places Manager', 'welocally-places-manager', 'wl_support_manager' );

	add_filter( 'plugin_action_links', 'wl_add_settings_link', 10, 2 );
	
	
	
}
add_action( 'admin_menu','wl_menu_initialise' );
add_filter( 'plugin_row_meta', 'wl_set_plugin_meta', 10, 2 );

function wl_places_subscribe() {
	include_once( WP_PLUGIN_DIR . "/welocally-places/options/subscribe.php" );
}


function wl_general_options() {
	include_once( WP_PLUGIN_DIR . "/welocally-places/options/options-general.php" );
}

function wl_support_about() {
	include_once( WP_PLUGIN_DIR . "/welocally-places/options/about.php" );
}

function wl_support_manager() {
	include_once( WP_PLUGIN_DIR . "/welocally-places/options/places-manager.php" );
}


function wl_add_settings_link( $links, $file ) {

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
		$links = array_merge( $links, array( '<a href="admin.php?page=welocally-places-subscribe">' . __( 'Register' ) . '</a>' ) );
		$links = array_merge( $links, array( '<a href="admin.php?page=welocally-places-about">' . __( 'Support' ) . '</a>' ) );		
	}

	return $links;
}


function wl_add_submenu( $page_title, $menu_title, $menu_slug, $function ) {

	$profile_slug = add_submenu_page( 'welocally-places-general', $page_title, $menu_title, 'manage_options', $menu_slug, $function );

	if ( $menu_slug == "welocally-places-general" ) { $help_text = file_get_contents(dirname( __FILE__ ) . '/help/options-general-help.php'); }

	if ( $menu_slug == "welocally-places-subscribe" ) { $help_text = file_get_contents(dirname( __FILE__ ) . '/help/subscribe-help.php'); }
	
	if ( $menu_slug == "welocally-places-about" ) { $help_text = file_get_contents(dirname( __FILE__ ) . '/help/about-help.php'); } 
	
	if ( $menu_slug == "welocally-places-manager" ) { $help_text = file_get_contents(dirname( __FILE__ ) . '/help/manager-help.php'); } 
	
	add_contextual_help( $profile_slug, __( $help_text ) );

	return;
}

?>