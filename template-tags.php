<?php
/**
	template tags for places

**/
if( class_exists( 'WelocallyPlaces' ) ) {
	
	/**
	 * DELEGATE
	 */
	function wl_set_general_defaults() {
		global $wlPlaces;
		
		$options = $wlPlaces->getOptions();
		
		$changed = false;
		
		$default_search_address = ''; 
		$default_marker_icon = plugins_url() . "/welocally-places/resources/images/marker_all_base.png";
		$default_api_endpoint = 'https://api.welocally.com'; 
		$default_update_places = 'off';
		
		$default_site_name= get_bloginfo('name');
		$default_site_home= get_bloginfo('home');
		$default_email=  get_bloginfo('admin_email');
		
		$default_show_letters = 'on'; 
		$default_show_selection = 'on'; 		
		$default_infobox_title_link = 'on'; 

		$default_show_letters_tag = 'on'; 
		$default_show_selection_tag = 'on'; 		
		$default_infobox_title_link_tag = 'on'; 

			
		// Set current version level. Because this can be used to detect version changes (and to what extent), this
		// information may be useful in future upgrades
		if ( $options[ 'current_version' ] != $wlPlaces->wl_places_version() ) {
			$options[ 'current_version' ] = $wlPlaces->wl_places_version();
			$changed = true;
		}
		
		// always check each option - if not set, apply default
		if ( !array_key_exists( 'default_search_addr', $options ) ) { $options[ 'default_search_addr' ] = $default_search_address; $changed = true; }
		
		//widget
		if ( !array_key_exists( 'show_letters', $options ) ) { $options[ 'show_letters' ] = $default_show_letters; $changed = true; }
		if ( !array_key_exists( 'show_selection', $options ) ) { $options[ 'show_selection' ] = $default_show_selection; $changed = true; }
		if ( !array_key_exists( 'infobox_title_link', $options ) ) { $options[ 'infobox_title_link' ] = $default_infobox_title_link; $changed = true; }
				
		//tag
		if ( !array_key_exists( 'show_letters_tag', $options ) ) { $options[ 'show_letters_tag' ] = $default_show_letters_tag; $changed = true; }
		if ( !array_key_exists( 'show_selection_tag', $options ) ) { $options[ 'show_selection_tag' ] = $default_show_selection_tag; $changed = true; }
		if ( !array_key_exists( 'infobox_title_link_tag', $options ) ) { $options[ 'infobox_title_link_tag' ] = $default_infobox_title_link_tag; $changed = true; }

		
	    //site
	    if ( !array_key_exists( 'siteKey', $options ) ) { $options[ 'siteKey' ] = null; $changed = true; }
	    if ( !array_key_exists( 'siteToken', $options ) ) { $options[ 'siteToken' ] = null; $changed = true; }
		if ( !array_key_exists( 'siteName', $options ) ) { $options[ 'siteName' ] = $default_site_name; $changed = true; }
	    if ( !array_key_exists( 'siteHome', $options ) ) { $options[ 'siteHome' ] = $default_site_home; $changed = true; }
		if ( !array_key_exists( 'siteEmail', $options ) ) { $options[ 'siteEmail' ] = $default_email; $changed = true; }
		
		//about options
		if ( !array_key_exists( 'api_endpoint', $options ) ) { $options[ 'api_endpoint' ] = $default_api_endpoint; $changed = true; }
		if ( !array_key_exists( 'update_places', $options ) ) { $options[ 'update_places' ] = $default_update_places; $changed = true; }
		
	
		// Update the options, if changed, and return the result
		if ( $changed ) { $wlPlaces->saveOptions($options) ; }
		return $options;
	}
	
	function wl_get_options() {
		global $wlPlaces;
		return $wlPlaces->getOptions();
	}
	
	function wl_save_options($options) {
		
		$options_r = print_r($options, true);
		//error_log("saving options:".$options_r, 0);
		global $wlPlaces;
		$wlPlaces->saveOptions($options);
		
		//update places
		if ( array_key_exists( 'update_places', $options )) {
			update_places();
		}
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
	
	
	function update_places(){
		global $wlPlaces;
		$cat_ID = $wlPlaces->placeCategory();		
		$places_in_category_posts = $wlPlaces->getPlacePostsInCategory($cat_ID, null);
		
		$index = 0;
		foreach( $places_in_category_posts as $post ) {	
			
			
			$placeJsonRaw = str_replace(
						"\'", "", 
						get_post_meta( $post->ID, '_PlaceSelected', true ));		
											
			$placeJson = 
				json_decode($placeJsonRaw, true); 
			
			$pname = str_replace("\\'", "'", $placeJson{'name'});
			
			if($pname != null){
				$newPlaceJson = convert_legacy_place($placeJsonRaw);
				update_post_meta( $post->ID, '_PlaceSelected',  $newPlaceJson);
			}
					
		}		
		
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
	
	
	/*function get_places_for_category($categoryId){
		global $wlPlaces;
		$options = $wlPlaces->getOptions();
		$result = array();
		$places_in_category_posts = $wlPlaces->getPlacePostsInCategory($categoryId, 'post');
		foreach( $places_in_category_posts as $post ){
			$places = get_post_places($post->ID);  
    		foreach ($places as $place){
   				if($options['infobox_title_link']=='on'){
   					$place->properties->titlelink=get_permalink( $post->ID ) ;	
   				}   						
	   			array_push($result, $place);
    		}
		}		
		return json_encode($result);		
	}*/
	
		
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
	
	
	/*function wl_get_post_excerpt( $postId = null) {
		global $wpdb;
		
		$query = "
			SELECT $wpdb->posts.*
			 	FROM $wpdb->posts 
			WHERE $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.ID = $postId
			GROUP BY $wpdb->posts.ID LIMIT 1";
			
		$queryResult = $wpdb->get_results($query, OBJECT);
		
		$excerpt = wl_trim_excerpt($queryResult[0]->post_content, $queryResult[0]->post_excerpt);	
		$excerpt = WelocallyPlaces_Tag::searchAndReplace($excerpt, create_function('$tag,$tag_str', 'return "";'));
		$excerpt = str_replace( '\'', '', $excerpt );
		$excerpt = trim( preg_replace( '/\s+/', ' ', $excerpt ) );
		
		return $excerpt;
	}*/
	
	/*function wl_trim_excerpt($text, $excerpt)
	{
		if ($excerpt) return $excerpt;
	
		$text = strip_shortcodes( $text );
	
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = strip_tags($text);
		$excerpt_length = apply_filters('excerpt_length', 55);
		$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
		$words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
		if ( count($words) > $excerpt_length ) {
				array_pop($words);
				$text = implode(' ', $words);
				$text = $text . $excerpt_more;
		} else {
				$text = implode(' ', $words);
		}
	
		return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
	}*/
	
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