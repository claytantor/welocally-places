<?php

/*
Plugin Name: Welocally Places
Plugin URI: http://www.welocally.com/wordpress/?page_id=2
Description: The Welocally Places plugin lets easily associate places from our 21M POI database without manual geocoding. The map widget makes it easy for your users to find the places your are writing about on a map.
Version: 1.0.16
Author: Welocally Inc. 
Author URI: http://welocally.com
License: GPL2 
*/

register_activation_hook(__FILE__, 'welocally_activate');
add_action('admin_head', 'welocally_requirements_check');

//ajax proxy calls
add_action('wp_ajax_getkey', 'welocally_getkey');
add_action('wp_ajax_add_place', 'welocally_add_place');
add_action('wp_ajax_get_places', 'welocally_getplaces');
add_action('wp_ajax_remove_token', 'welocally_remove_token');
add_action('wp_loaded', 'wl_self_deprecating_sidebar_registration');
add_filter('the_excerpt', 'wl_get_excerpt_basic'); 

function wl_get_excerpt_basic() {
	global $post;
	return wl_get_post_excerpt($post->ID);
}

function wl_server_base() {
	return wl_get_option("api_endpoint", null);
}

function wl_self_deprecating_sidebar_registration() {
	$i = 1;
	$args = array (
		'name' => sprintf(__('Welocally Places %d'), $i),
		'id' => 'wl-sidebar-1',
		'description' => '',
		'before_widget' => '<li id="%1$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	);
	register_sidebar($args);

	$sidebar_widgets = wp_get_sidebars_widgets();

	$new_category_widget = array (
		1 => 'categories-2'
	);
	$new_sidebar = array (
		'wl-sidebar-1' => $new_category_widget
	);
	$new_widgets = array_merge($new_sidebar, $sidebar_widgets);
	wp_set_sidebars_widgets($new_widgets);
}

function welocally_remove_token() {

	$selectedPostJson = json_encode($_POST);
	syslog(LOG_WARNING, "A" . var_export($selectedPostJson, true));

	$options = wl_get_options();

	$options['siteToken'] = null;

	wl_save_options($options);

	die(); // this is required to return a proper result
}

function welocally_getkey() {

	$selectedPostJson = json_encode($_POST);
	//syslog(LOG_WARNING, "A" . var_export($selectedPostJson, true));

	//set POST variables 
	$url = wl_server_base() . '/admin/signup/plugin/key.json';
	
	error_log("url:".$url, 0);

	$result_json = wl_do_curl_post($url, $selectedPostJson, array (
		'Content-Type: application/json; charset=utf-8'
	));

	die(); // this is required to return a proper result
}

function wl_do_curl_get($url, $headers){
	error_log("wl_do_curl_get url:".$url, 0);
	
	$result_json = '';
	if (preg_match("/https/", $url)) {
		$result_json = wl_do_curl_get_https($url, $headers);
	} else {
		$result_json = wl_do_curl_get_http($url, $headers);
	}

	return $result_json;
}

function wl_do_curl_get_http($url, $headers) {
	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//execute post
	$result_json = curl_exec($ch);

	curl_close($ch);

	return $result_json;
}


function wl_do_curl_get_https($https_url, $headers) {

	//open connection
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_CAINFO, NULL);
	curl_setopt($ch, CURLOPT_CAPATH, NULL);

	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $https_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//execute post
	$result_json = curl_exec($ch);

	curl_close($ch);

	return $result_json;
}

//-------- POST ------------
function wl_do_curl_post($url, $selectedPostJson, $headers) {
	$result_json = '';
	if (preg_match("/https/", $url)) {
		$result_json = wl_do_curl_post_https($url, $selectedPostJson, $headers);
	} else {
		$result_json = wl_do_curl_post_http($url, $selectedPostJson, $headers);
	}

	return $result_json;
}

function wl_do_curl_post_http($url, $selectedPostJson, $headers) {
	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $selectedPostJson);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//execute post
	$result_json = curl_exec($ch);

	curl_close($ch);

	return $result_json;
}

function wl_do_curl_post_https($https_url, $selectedPostJson, $headers) {

	//open connection
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_CAINFO, NULL);
	curl_setopt($ch, CURLOPT_CAPATH, NULL);

	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $https_url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $selectedPostJson);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//execute post
	$result_json = curl_exec($ch);

	curl_close($ch);

	return $result_json;
}

function welocally_getplaces() {

	error_log($url, 0);
	$url = wl_server_base() .'/geodb/place/1_0/search.json?'.http_build_query($_GET);

	$result_json = wl_do_curl_get($url, array (
		'Content-Type: application/json; charset=utf-8',
		'site-key:' . wl_get_option('siteKey', null),
		'site-token:' . wl_get_option('siteToken', null)
	));

	die(); // this is required to return a proper result
}

function welocally_add_place() {

	$selectedPostJson = json_encode($_POST);

	//set POST variables 
	$url = wl_server_base() . '/admin/publisher/place/addplace.json';

	$result_json = wl_do_curl($url, $selectedPostJson, array (
		'Content-Type: application/json; charset=utf-8'
	));

	die(); // this is required to return a proper result
}

function welocally_requirements_check() {

	if (version_compare(PHP_VERSION, "5.1", "<")) {
		echo "<div class='error fade'>Welocally Places requires PHP 5.1 or greater.  Please de-activate Welocally Places.</div>";
	}

	if (!welocally_is_curl_installed()) {
		echo "<div class='error fade'>Welocally Places requires libCURL be installed.  Please de-activate Welocally Places or install.</div>";
	}

}

function welocally_is_curl_installed() {
	if (in_array('curl', get_loaded_extensions())) {
		return true;
	} else {
		return false;
	}
}




function welocally_activate() {
	if (version_compare(PHP_VERSION, "5.1", "<") && welocally_is_curl_installed()) {
		trigger_error('Can Not Install Welocally Places, Please Check Requirements', E_USER_ERROR);
	} else {
		require_once (dirname(__FILE__) . "/welocally-places.class.php");
		require_once (dirname(__FILE__) . "/welocally-places-exception.class.php");
		require_once (dirname(__FILE__) . "/welocally-places-list-widget.class.php");
		require_once (dirname(__FILE__) . "/welocally-places-map-widget.class.php");
		require_once (dirname(__FILE__) . "/template-tags.php");
		require_once (dirname(__FILE__) . "/mcebutton.php");
		require_once (dirname(__FILE__) . "/menu.php");
		
		/*if(get_theme_view_dir() == 'default') {
			echo "<div class='updated fade'>The theme ".get_current_theme()." is not tested with Welocally Places. Goto the ".
				"<a href='admin.php?page=welocally-places-about'>" . __( 'About Settings' ) . "</a> for more information.</div>";
		}*/
		
		syslog(LOG_WARNING, "activate");
		global $wlPlaces;
		$wlPlaces->on_activate();
	}
}

if (version_compare(phpversion(), "5.1", ">=") && welocally_is_curl_installed()) {
	require_once (dirname(__FILE__) . "/welocally-places.class.php");
	require_once (dirname(__FILE__) . "/welocally-places-exception.class.php");
	require_once (dirname(__FILE__) . "/welocally-places-list-widget.class.php");
	require_once (dirname(__FILE__) . "/welocally-places-map-widget.class.php");
	require_once (dirname(__FILE__) . "/template-tags.php");
	require_once (dirname(__FILE__) . "/mcebutton.php");
	require_once (dirname(__FILE__) . "/menu.php");
	
	/*if(get_theme_view_dir() == 'default') {
			echo "<div class='updated fade'>The theme ".get_current_theme()." is not tested with Welocally Places. Goto the ".
				"<a href='admin.php?page=welocally-places-about'>" . __( 'About Settings' ) . "</a> for more information.</div>";
	}*/
}
?>
