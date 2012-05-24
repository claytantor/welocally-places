<?php

/*
Plugin Name: Welocally Places Core
Plugin URI: http://www.welocally.com/wordpress/?page_id=2
Description: Great maps that link to your content. The Welocally Places plugin lets easily associate places to your content without manual geocoding. Add any place to your database and link it to your posts and categories with simple shortcodes. The map widget makes it easy for your users to find the places your are writing about on a map and is smart enough to automatically relate places by category. If you are upgrading and an install this manually make sure to re-activate the plugin, activation does important database updates. 
Version: 1.2.22
Author: Welocally  
Author URI: http://welocally.com
License: GPL2 
*/

register_activation_hook(__FILE__, 'welocally_activate');
register_deactivation_hook( __FILE__, 'welocally_deactivate' );

add_action('admin_head', 'welocally_requirements_check');
add_action('wp_loaded', 'wl_self_deprecating_sidebar_registration');
add_action('wp_ajax_save_place', 'welocally_save_place');
add_action('wp_ajax_edit_place', 'welocally_edit_place');
add_action('wp_ajax_delete_place', 'welocally_delete_place');
add_action('wp_ajax_getpage', 'wl_places_pager_getpage');
add_action('wp_ajax_get_metadata', 'wl_places_pager_get_metadata');

// add filter's for plugin templates
add_filter('map_widget_template', 'wl_places_get_template_map_widget',10);


//----- end of neworking section ----------//

function wl_debug() {
    $args = func_get_args();
    
    echo '<pre>';
    foreach ($args as $arg) {
        print_r($arg);
    }
    echo '</pre>';
}

function wl_debug_e() {
    call_user_func_array('wl_debug', func_get_args());
    exit;
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

/**
 * consider moving core impl to the class
 */
function welocally_save_place() {
	
	global $wpdb, $wlPlaces;

	$placeToSave = $_POST['place'];
	$placeToSave['properties']['name'] = stripslashes($_POST['place']['properties']['name']);
	$placeToSave['properties']['address'] = stripslashes($_POST['place']['properties']['address']);
	
	//generate if new (save)
	if(!isset($placeToSave['_id'])){
		//init date
		date_default_timezone_set('UTC');
		$mysqldate = date( 'Y-m-d H:i:s', time() );
		$placeToSave['_id'] = $wlPlaces->generatePlaceId($placeToSave['geometry']['coordinates'][1],$placeToSave['geometry']['coordinates'][0]); 
	                
			//save to places table
		if ($wpdb->insert("{$wpdb->prefix}wl_places", 
			array('wl_id' => $placeToSave['_id'], 'place' => json_encode($placeToSave), 'lat' =>  $placeToSave['geometry']['coordinates'][1], 'lng' =>  $placeToSave['geometry']['coordinates'][0], 'created' =>$mysqldate )) ) {
				$place = $wpdb->get_row(
				$wpdb->prepare("SELECT * FROM {$wpdb->prefix}wl_places WHERE wl_id = %s", $placeToSave['_id']) );
	    }
	    
	    echo '{ "id": "'.$placeToSave['_id'].'"}';
	    
	} else { //update
		
		$placeId = $placeToSave['_id'];
		$placeJSON = addslashes(json_encode($placeToSave));
		
		$statement = "UPDATE {$wpdb->prefix}wl_places SET place='".$placeJSON."' WHERE wl_id = '".$placeId."'";
		$wpdb->query($wpdb->prepare($statement));
		
		echo '{ "id": "'.$placeToSave['_id'].'"}';
	}	

	die(); // this is required to return a proper result
}


function wl_places_pager_get_metadata() {	
	
	global $wlPager;
	$fields = $_POST['fields'];
	$table = $_POST['table'];
	$filter = $_POST['filter'];
	$orderBy = $_POST['orderBy'];
	$pagesize = $_POST['pagesize'];
		
	echo json_encode($wlPager->getMetadata($table, $fields, $filter, $orderBy, $pagesize));	

	die(); // this is required to return a proper result		
}

function wl_places_pager_getpage() {
	
	global $wlPager;
	$fields = $_POST['fields'];
	$table = $_POST['table'];
	$filter = $_POST['filter'];
	$pagesize = $_POST['pagesize'];
	$orderBy = $_POST['orderBy'];
	$odd = $_POST['odd'];
	$even = $_POST['even'];	
	$page = $_POST['page'];
	
	$content = base64_decode($_POST['content']);
	
	if($wlPager->tableAllowed($table))	
		echo $wlPager->embedPagination($table, $fields, $filter, $orderBy, $pagesize, $content, $page, $odd, $even);
	else
		echo '<div class="wl_error">The table you have selected for pagination is not permitted. Goto Welocally Pager Shortcode options in Admin Dashboard for settings.</div>' ;	
		
	die(); // this is required to return a proper result
}

function welocally_edit_place() {
	
	global $wpdb, $wlPlaces;

	$placeId = $_POST['wl_id'];
	$recordId = $_POST['id'];
	$place = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$wpdb->prefix}wl_places WHERE wl_id = %s", $placeId) );
		
	echo '{ "id":'.$recordId.', "places" :[' .$place->place.']}';

	die(); // this is required to return a proper result
}

function welocally_delete_place() {
	
	global $wpdb, $wlPlaces;

	//$placeId = $_POST['wl_id'];
	$recordId = $_POST['id'];
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}wl_places WHERE id = %d", $recordId));
		
	echo '{ "id":'.$recordId.',"status": "SUCCEED", "action": "DELETE" }';

	die(); // this is required to return a proper result
}

function welocally_requirements_check() {

	if (version_compare(PHP_VERSION, "5.1", "<")) {
		trigger_error('Welocally Places requires PHP 5.1 or greater.  Please de-activate Welocally Places.', E_USER_ERROR);		
	}


}


function welocally_activate() {
	if (version_compare(PHP_VERSION, "5.1", "<")) {
		trigger_error('Can Not Install Welocally Places, Please Check Requirements', E_USER_ERROR);
	} else {
		require_once (dirname(__FILE__) . "/welocally-places.class.php");
		require_once (dirname(__FILE__) . "/WelocallyWPPagination.class.php");
		require_once (dirname(__FILE__) . "/welocally-places-exception.class.php");
		require_once (dirname(__FILE__) . "/welocally-places-map-widget.class.php");
		require_once (dirname(__FILE__) . "/template-tags.php");
		require_once (dirname(__FILE__) . "/menu.php");
	
		global $wlPlaces;
		$wlPlaces->on_activate();
	}
}


function welocally_deactivate() {	
	if(function_exists('wl_customize_deactivate')){		
		deactivate_plugins(WP_PLUGIN_DIR.'/welocally-places-customize/welocally-places-customize.php');
	}
}

if (version_compare(phpversion(), "5.1", ">=")) {
	require_once (dirname(__FILE__) . "/welocally-places.class.php");
	require_once (dirname(__FILE__) . "/WelocallyWPPagination.class.php");
	require_once (dirname(__FILE__) . "/welocally-places-exception.class.php");
	require_once (dirname(__FILE__) . "/welocally-places-map-widget.class.php");
	require_once (dirname(__FILE__) . "/template-tags.php");
	require_once (dirname(__FILE__) . "/menu.php");	
}





?>
