<?php
/**
	template tags for places

**/
if( class_exists( 'WelocallyPlaces' ) ) {

	
	function wl_get_options() {
		global $wlPlaces;
		return $wlPlaces->getOptions();
	}
	
	function wl_save_options($options) {
		
		$options_r = print_r($options, true);
		//error_log("saving options:".$options_r, 0);
		global $wlPlaces;
		$wlPlaces->saveOptions($options);
		
	}
	
	function delete_post_places($post_place_id=null) {
	    global $wlPlaces;	    
	    if ($post_place_id){
	    	$wlPlaces->deletePostPlaces($post_place_id);
	    }
	    
	}
	
	
	
	function delete_post_places_meta($post_id=0) {
	    global $wlPlaces;	    
	    if (!$post_id) $post_id = get_the_ID();	    
	    $wlPlaces->deletePostPlacesMeta($post_id);
	}
	
	
	/**
	 * DELEGATE TO Places Class 
	 * retrieve specific key from options array, optionally provide a default return value
	 */
	function wl_get_option($optionName, $default = '') {
		global $wlPlaces;
		if($optionName) {			
			$options = $wlPlaces->getOptions();
			return ( $options[$optionName] ) ? $options[$optionName] : $default;
		}
	}
		
	function is_subscribed(){
		$is_token_valid = wl_get_option('siteToken', null);
		return isset($is_token_valid);
	}	
	
	
	function is_registered(){
		$is_token_valid = wl_get_option('siteToken', null);
		return isset($is_token_valid);
	}	

		
	
	/**
	 * Template function: 
	 * @return boolean
	 */
	function is_place( $postId = null ) {
		if ( $postId === null || !is_numeric( $postId ) ) {
			global $post;
			$postId = $post->ID;
		}
		if (get_post_meta( $postId, '_isWLPlace', true )) {
			return true;
		}
		return false;
	}
	
	

	
	/**
	 * 	
	 * takes a legacy json string in and returns a json string out
	 */
	function convert_legacy_place($legacyPlaceJsonRaw){
		$legacyPlaceJson = 
				json_decode($legacyPlaceJsonRaw, true);
		
		$legacyCategories = $legacyPlaceJson{'categories'};
		
		$template = file_get_contents(dirname( __FILE__ ) . '/templates/newplace-template.json');
		
		$resultJson = sprintf ( 
				$template, 
				str_replace("SG_", "WL_",$legacyPlaceJson{'externalId'}), //1
				$legacyPlaceJson{'name'}, //2
				$legacyPlaceJson{'address'}, //3 
				$legacyPlaceJson{'city'}, //4
				$legacyPlaceJson{'state'}, //5
				$legacyPlaceJson{'postalCode'},//6
				$legacyPlaceJson{'phone'},//7
				$legacyPlaceJson{'website'}, //8 
				$legacyCategories[0], //9
				"", //10
				"",//11
				$legacyPlaceJson{'latitude'}, //12
				$legacyPlaceJson{'longitude'} //13		
				);				
				
		return trim($resultJson );
	}
	
	//select post_id from wp_postmeta where meta_key='_PlaceSelected';
	function get_legacy_posts() {
		global $wpdb;
		
		$query = "SELECT $wpdb->posts.*, $wpdb->postmeta.meta_value
			 	FROM $wpdb->posts,$wpdb->postmeta  
			WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id  
			AND $wpdb->postmeta.meta_key='_PlaceSelected'"; 
			
		
		$metaPlaces = $wpdb->get_results($query, OBJECT);
		$arr = array();
		foreach ($metaPlaces as $post)
		{
			$placeJson = 
				json_decode($post->meta_value, true);
			if(!empty ($placeJson['externalId']) || 
				!empty ($placeJson['simpleGeoId']) ){
				array_push($arr, $post);	
			}
		}
		return $arr;		
		
	}
	
	/**
	 * this function is used to determine the number of legacy places that will need to be
	 * converted.
	 * 
	 */
	function get_places_legacy_count() {
		global $wpdb;
			
		$query = "SELECT $wpdb->postmeta.*
			 	FROM $wpdb->postmeta 
			WHERE $wpdb->postmeta.meta_key = '_PlaceSelected'";
			
		$metaPlaces = $wpdb->get_results($query, OBJECT);
		$legacyCount = 0;
		foreach ($metaPlaces as $place)
		{
			$placeJson = 
				json_decode($place->meta_value, true);
			
			if(!empty ($placeJson['externalId']) || 
				!empty ($placeJson['simpleGeoId']) )			
			{
				$legacyCount = $legacyCount+1;
			}
		}
		return $legacyCount;
		
	}
	
	
	
	/**
	 * Call this function in a template to query the places and start the loop. Do not
	 * subsequently call the_post() in your template, as this will start the loop twice and then
	 * you're in trouble.
	 * 
	 * http://codex.wordpress.org/Displaying_Posts_Using_a_Custom_Select_Query#Query_based_on_Custom_Field_and_Category
	 *
	 * @param int number of results to display for upcoming or past modes (default 10)
	 * @param string column name used for ordering
	 * @param string order direction
	 * @param string category name to pull places from, defaults to the currently displayed category (if any) or else, everything
	 * @uses WelocallyPlaces::getPlacePosts()
	 * @uses $wpdb
	 * @uses $wp_query
	 * @return array results
	 */
	function get_places( $numResults = null, $orderBy = null, $orderDir = null, $catName = null ) {
		global $wlPlaces;

		if (!$numResults)
			$numResults = get_option('posts_per_page', 10);

		$categoryId = $catName ? intval(get_cat_ID($catName)) : intval(get_query_var('cat'));

		return $wlPlaces->getPlacePosts('post', $categoryId, array('limit' => $numResults,
											  			 		   'order_by' => $orderBy,
											  			  		   'order_dir' => $orderDir));
	}
	
	/**
	 * @see WelocallyPlaces::getPlacePostsInCategory()
	 * @deprecated deprecated since 1.1.17
	 */
	function get_places_posts_for_category($categoryId){
		global $wlPlaces;
		return $wlPlaces->getPlacePostsInCategory($categoryId, 'post');
	}
			
	function get_legacy_place_by_post_id( $postId = null) {
		global $wpdb;
			
		$query = "SELECT $wpdb->postmeta.*
			 	FROM $wpdb->postmeta 
			WHERE $wpdb->postmeta.post_id = $postId
			AND $wpdb->postmeta.meta_key = '_PlaceSelected' LIMIT 1";
			
		$return = $wpdb->get_results($query, OBJECT);
		
		$placeJson = 
				json_decode($return[0]->meta_value, true);
		
		
		return $placeJson;
	}
	
	function delete_place_by_post_id( $postId = null) {
		
		delete_post_meta($postId, '_PlaceSelected');
		
	}

	function delete_subscription_options() {
	
		$options = wl_get_options();
		
		$options['siteKey'] = null;
		$options['siteToken'] = null;
	
		wl_save_options($options);

	}
	
	function places_get_mapview_link( ) {
		global $wlPlaces;
		$mainPlacesCat = $wlPlaces->placeCategory();
		$currentCat = get_query_var( 'cat' );
		$link = get_category_link( $wlPlaces->placeCategory() );
		return $link;
		
	}
	
	function get_post_places($post_id=0) {
	    global $wlPlaces;
	    
	    if (!$post_id) $post_id = get_the_ID();
	    
	    return $wlPlaces->getPostPlaces($post_id);
	}
	
	function get_post_places_meta($post_id=0) {
	    global $wlPlaces;
	    
	    if (!$post_id) $post_id = get_the_ID();
	    
	    return $wlPlaces->getPostPlacesMeta($post_id);
	}
	
	function wl_places_get_template_map_widget(){		
		global $wlPlaces;
		$currentCat = get_query_var( 'cat' );
		
		if(!$currentCat){
			$currentCat = $wlPlaces->placeCategory();
		}
		echo($wlPlaces->getCategoryMapMarkup(
			$currentCat, 
			dirname( __FILE__ ).'/views/welocally-places-map-widget-display.php',
			true));
	}

	function the_category_map($category=null) {
		global $wlPlaces;
		echo $wlPlaces->getCategoryMapMarkup($category);
	}
}