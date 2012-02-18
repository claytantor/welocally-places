<?php

add_action( 'init','welocally_places_button' );

function welocally_places_button() {

	if ( current_user_can( 'edit_pages' ) ) {

		if ( ( get_user_option( 'rich_editing' ) == 'true' ) ) {
			add_filter( 'mce_external_plugins', 'add_welocally_places_mce_plugin' );
			add_filter( 'mce_buttons', 'register_welocally_addplace_button' );
		}
	}
}

function register_welocally_addplace_button( $buttons ) {
	array_push( $buttons, '|', 'Welocally' );
	return $buttons;
}

function add_welocally_places_mce_plugin( $plugin_array ) {
	$plugin_array[ 'Welocally' ] = WP_PLUGIN_URL . '/welocally-places/resources/mcebutton.js';
	return $plugin_array;
}


?>