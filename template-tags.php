<?php
/**
	template tags for places

**/
if( class_exists( 'WelocallyPlaces' ) ) {

	function get_theme_view_dir(){
	
			$theme_name = get_current_theme();				
			$theme_lookup = get_supported_themes();
			foreach ($theme_lookup as $theme)
			{
			  if($theme_name == $theme["themeName"])
				return $theme["themeDirectory"];
			}
							
			return 'default';
	}
	
	function get_supported_themes(){
					
			$theme_lookup_json = 
				file_get_contents(dirname( __FILE__ ) . '/resources/themes.json');
			
			$theme_lookup = json_decode($theme_lookup_json,true);
									
			return $theme_lookup;
	}
	
	/**
	 * DELEGATE
	 */
	function wl_set_general_defaults() {
		global $wlPlaces;
		
		$options = $wlPlaces->getOptions();
		
		$changed = false;
		
		$default_search_address = 'Oakland, CA'; 
		$default_search_radius = '8'; 	
		$default_marker_icon = plugins_url() . "/welocally-places/resources/images/marker_generic_32.png";
		$default_infobox_marker = plugins_url() . "/welocally-places/resources/images/tipbox_180.png";
		$default_infobox_close = plugins_url() . "/welocally-places/resources/images/infobox_close_16.png";
		$default_icon_directions = plugins_url() . "/welocally-places/resources/images/mapicons_car.png";
		$default_icon_web = plugins_url() . "/welocally-places/resources/images/mapicons_web.png";
		$default_map_style = ''; 
		$default_font = 'Sorts Mill Goudy'; 
		$default_font_color = '000000'; 
		$default_font_size = '1.2'; 
		$default_cat_map_layout = 'none'; 
		$default_cat_map_select_width = '160'; 
		$default_cat_map_select_height = '160'; 
		$default_api_endpoint = 'https://api.welocally.com'; 
		$default_slider_amount = '100' ; 
		$default_update_places = 'off';
		
		$default_infobox_title_link = 'off';
		$default_infobox_thumbnail = 'on';
		$default_infobox_thumb_width = '64';
		$default_infobox_thumb_height = '64';
		
		$default_cat_map_select_show = 'off';
		$default_cat_map_select_title= 'on';
		$default_cat_map_select_excerpt= 'on';
		
		$default_site_name= get_bloginfo('name');
		$default_site_home= get_bloginfo('home');
		$default_email=  get_bloginfo('admin_email');
		

			
		// Set current version level. Because this can be used to detect version changes (and to what extent), this
		// information may be useful in future upgrades
		if ( $options[ 'current_version' ] != $wlPlaces->wl_places_version() ) {
			$options[ 'current_version' ] = $wlPlaces->wl_places_version();
			$changed = true;
		}
		
		// always check each option - if not set, apply default
		if ( !array_key_exists( 'default_search_addr', $options ) ) { $options[ 'default_search_addr' ] = $default_search_address; $changed = true; }
		if ( !array_key_exists( 'default_search_radius', $options ) ) { $options[ 'default_search_radius' ] = $default_search_radius; $changed = true; }		
		

		if ( !array_key_exists( 'infobox_title_link', $options ) ) { $options[ 'infobox_title_link' ] = $default_infobox_title_link; $changed = true; }
		if ( !array_key_exists( 'infobox_thumbnail', $options ) ) { $options[ 'infobox_thumbnail' ] = $default_infobox_thumbnail; $changed = true; }
		if ( !array_key_exists( 'infobox_thumb_width', $options ) ) { $options[ 'infobox_thumb_width' ] = $default_infobox_thumb_width; $changed = true; }
		if ( !array_key_exists( 'infobox_thumb_height', $options ) ) { $options[ 'infobox_thumb_height' ] = $default_infobox_thumb_height; $changed = true; }		
		
		if ( !array_key_exists( 'map_default_marker', $options ) ) { $options[ 'map_default_marker' ] = $default_marker_icon; $changed = true; }
		if ( !array_key_exists( 'map_infobox_marker', $options ) ) { $options[ 'map_infobox_marker' ] = $default_infobox_marker; $changed = true; }
		if ( !array_key_exists( 'map_infobox_close', $options ) ) { $options[ 'map_infobox_close' ] = $default_infobox_close; $changed = true; }
		if ( !array_key_exists( 'map_icon_directions', $options ) ) { $options[ 'map_icon_directions' ] = $default_icon_directions; $changed = true; }
		if ( !array_key_exists( 'map_icon_web', $options ) ) { $options[ 'map_icon_web' ] = $default_icon_web; $changed = true; }
		if ( !array_key_exists( 'map_custom_style', $options ) ) { $options[ 'map_custom_style' ] = $default_map_style; $changed = true; }
	    
	    //site
	    if ( !array_key_exists( 'siteKey', $options ) ) { $options[ 'siteKey' ] = $default_map_style; $changed = true; }
	    if ( !array_key_exists( 'siteToken', $options ) ) { $options[ 'siteToken' ] = $default_map_style; $changed = true; }
		if ( !array_key_exists( 'siteName', $options ) ) { $options[ 'siteName' ] = $default_site_name; $changed = true; }
	    if ( !array_key_exists( 'siteHome', $options ) ) { $options[ 'siteHome' ] = $default_site_home; $changed = true; }
		if ( !array_key_exists( 'siteEmail', $options ) ) { $options[ 'siteEmail' ] = $default_email; $changed = true; }
	    
		
		if ( !array_key_exists( 'font_place_name', $options ) ) { $options[ 'font_place_name' ] = $default_font; $changed = true; }
		if ( !array_key_exists( 'color_place_name', $options ) ) { $options[ 'color_place_name' ] = $default_font_color; $changed = true; }
		if ( !array_key_exists( 'size_place_name', $options ) ) { $options[ 'size_place_name' ] = $default_font_size; $changed = true; }
		if ( !array_key_exists( 'font_place_address', $options ) ) { $options[ 'font_place_address' ] = $default_font; $changed = true; }
		if ( !array_key_exists( 'color_place_address', $options ) ) { $options[ 'color_place_address' ] = $default_font_color; $changed = true; }
		if ( !array_key_exists( 'size_place_address', $options ) ) { $options[ 'size_place_address' ] = $default_font_size; $changed = true; }
		

		if ( !array_key_exists( 'cat_map_select_show', $options ) ) { $options[ 'cat_map_select_show' ] = $default_cat_map_select_show; $changed = true; }
		if ( !array_key_exists( 'cat_map_select_excerpt', $options ) ) { $options[ 'cat_map_select_excerpt' ] = $default_cat_map_select_excerpt; $changed = true; }
		
		
		if ( !array_key_exists( 'cat_map_layout', $options ) ) { $options[ 'cat_map_layout' ] = $default_cat_map_layout; $changed = true; }
		if ( !array_key_exists( 'cat_map_select_width', $options ) ) { $options[ 'cat_map_select_width' ] = $default_cat_map_select_width; $changed = true; }
		if ( !array_key_exists( 'cat_map_select_height', $options ) ) { $options[ 'cat_map_select_height' ] = $default_cat_map_select_height; $changed = true; }
		//welocally_cat_map_infobox_text_scale
		if ( !array_key_exists( 'cat_map_infobox_text_scale', $options ) ) { $options[ 'cat_map_infobox_text_scale' ] = $default_slider_amount; $changed = true; }
		
		//about options
		if ( !array_key_exists( 'api_endpoint', $options ) ) { $options[ 'api_endpoint' ] = $default_api_endpoint; $changed = true; }
		if ( !array_key_exists( 'update_places', $options ) ) { $options[ 'update_places' ] = $default_update_places; $changed = true; }
		
		if ( !array_key_exists( 'update_places', $options ) ) { $options[ 'update_places' ] = $default_update_places; $changed = true; }
		if ( !array_key_exists( 'update_places', $options ) ) { $options[ 'update_places' ] = $default_update_places; $changed = true; }
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
	
	function delete_post_places($post_id=0) {
	    global $wlPlaces;	    
	    if (!$post_id) $post_id = get_the_ID();	    
	    $wlPlaces->deletePostPlaces($post_id);
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
		$places_in_category_posts = get_places_posts_for_category($cat_ID);	
		
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
		
		$query = "SELECT $wpdb->posts.*
			 	FROM $wpdb->posts,$wpdb->postmeta  
			WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id  
			AND $wpdb->postmeta.meta_key='_PlaceSelected'"; 
		
		return $wpdb->get_results($query, OBJECT);
	}
	
	/**
	 * this fuction is used to determine the number of legacy places that will need to be
	 * converted.
	 * 
	 */
	function get_places_legacy_count() {
		global $wpdb;
			
		$query = "SELECT $wpdb->postmeta.*
			 	FROM $wpdb->postmeta 
			WHERE $wpdb->postmeta.meta_key = '_PlaceSelected'";
			
		$return = $wpdb->get_results($query, OBJECT);
		return count($return);
		
	}
	
	
	
	/**
	 * Call this function in a template to query the places and start the loop. Do not
	 * subsequently call the_post() in your template, as this will start the loop twice and then
	 * you're in trouble.
	 * 
	 * http://codex.wordpress.org/Displaying_Posts_Using_a_Custom_Select_Query#Query_based_on_Custom_Field_and_Category
	 *
	 * @param int number of results to display for upcoming or past modes (default 10)
	 * @param string category name to pull places from, defaults to the currently displayed category
	 * @uses $wpdb
	 * @uses $wp_query
	 * @return array results
	 */
	function get_places( $numResults = null, $orderBy = null, $orderDir = null, $catName = null ) {
		if( !$numResults ) $numResults = get_option( 'posts_per_page', 10 );
		global $wpdb, $wlPlaces;
		$wlPlaces->setOptions();
		if( $catName ) {
			$categoryId = get_cat_id( $catName );		
		} else {
			$categoryId = get_query_var( 'cat' );
		}		
		$extraSelectClause ='';
		$orderClause = '';
		if($orderBy) {
			$orderClause = "ORDER BY ".$orderBy." ".$orderDir;
		}
		$placesQuery = "
			SELECT $wpdb->posts.*
				$extraSelectClause
			 	FROM $wpdb->posts 
			LEFT JOIN $wpdb->postmeta as d1 ON($wpdb->posts.ID = d1.post_id)
			LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			WHERE $wpdb->term_taxonomy.term_id = $categoryId
			AND $wpdb->term_taxonomy.taxonomy = 'category'
			AND $wpdb->posts.post_status = 'publish'
			GROUP BY $wpdb->posts.ID ".$orderClause." LIMIT $numResults";
			
		/*syslog(LOG_WARNING, $placesQuery);*/
		$return = $wpdb->get_results($placesQuery, OBJECT);
		return $return;
	}
	
	/*
	 * given a specific category, no matter what it is, return the
	 * post ids within it that are also place posts. this essentially
	 * acts as a place filter for posts in a category so that only 
	 * those will be shown on the map
	 * 
	 */
	function get_places_posts_for_category($categoryId){
				
		global $wpdb, $wlPlaces;
		$wlPlaces->setOptions();
		
		$categoryPlacesId = $wlPlaces->placeCategory();

		$categories_query = "select 
                        $wpdb->posts.*
                        from $wpdb->term_taxonomy, $wpdb->term_relationships, $wpdb->posts, $wpdb->terms 
                        where $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
                        AND $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id
                        AND $wpdb->posts.id = $wpdb->term_relationships.object_id
                        AND $wpdb->term_taxonomy.term_id = $categoryId
                        AND $wpdb->term_taxonomy.taxonomy = 'category' 
                        AND $wpdb->posts.post_status = 'publish'
                        AND $wpdb->posts.id in (select $wpdb->posts.ID  
                                FROM $wpdb->posts 
                                LEFT JOIN $wpdb->postmeta as d1 ON($wpdb->posts.ID = d1.post_id) 
                                LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id) 
                                LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) 
                                WHERE $wpdb->term_taxonomy.term_id = $categoryPlacesId
                                AND $wpdb->term_taxonomy.taxonomy = 'category' 
                                AND $wpdb->posts.post_status = 'publish' 
                                GROUP BY $wpdb->posts.ID)";
                                
		$return = $wpdb->get_results($categories_query, OBJECT);
		
		return $return;
	}
	
	
	/**
	 * is this obsolete?
	 */
	function get_place_post_ids_by_category( $orderBy = null, $orderDir = null, $categoryId = null ) {

		global $wpdb, $wlPlaces;
		$wlPlaces->setOptions();
		
		$categoryPlacesId = $wlPlaces->placeCategory();
		
		//error_log("cat id:".$categoryId, 0);

		//get the posts of s specific category
		$categoryQuery = "
			SELECT $wpdb->posts.ID
			 	FROM $wpdb->posts 
			LEFT JOIN $wpdb->postmeta as d1 ON($wpdb->posts.ID = d1.post_id)
			LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			WHERE $wpdb->term_taxonomy.term_id = $categoryId
			AND $wpdb->term_taxonomy.taxonomy = 'category'
			AND $wpdb->posts.post_status = 'publish' " .
			"GROUP BY $wpdb->posts.ID";
		
		//iterate through those, we could probably come up with a query here 
		//because we are effectively creating a join, but this is already a pretty
		//complex query, this should probably be improved later			
		$return = $wpdb->get_results($placesQuery, OBJECT);
		return $return;
	}
	
	function get_legacy_place_by_post_id( $postId = null) {
		global $wpdb;
			
		$query = "SELECT $wpdb->postmeta.*
			 	FROM $wpdb->postmeta 
			WHERE $wpdb->postmeta.post_id = $postId
			AND $wpdb->postmeta.meta_key = '_PlaceSelected' LIMIT 1";
			
		$return = $wpdb->get_results($query, OBJECT);
		return $return;
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
	
	
	function wl_get_post_excerpt( $postId = null) {
		global $wpdb;
		
		$query = "
			SELECT $wpdb->posts.*
			 	FROM $wpdb->posts 
			WHERE $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.ID = $postId
			GROUP BY $wpdb->posts.ID LIMIT 1";
			
		$queryResult = $wpdb->get_results($query, OBJECT);
		
		$excerpt = trim_excerpt($queryResult[0]->post_content, $queryResult[0]->post_excerpt);	
		$excerpt = WelocallyPlaces_Tag::searchAndReplace($excerpt, create_function('$tag,$tag_str', 'return "";'));
		$excerpt = str_replace( '\'', '', $excerpt );
		$excerpt = trim( preg_replace( '/\s+/', ' ', $excerpt ) );
		$excerpt = "";
		
		return $excerpt;
	}
	
	function trim_excerpt($text, $excerpt)
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
	
// functions for get template
	function wl_places_get_template_map_widget(){
		$templateOverride = locate_template( array( 'places/places-map-widget-display.php' ) );
		$theme_dir = get_theme_view_dir();	
		return $templateOverride ? $templateOverride : dirname( __FILE__ ).'/views/themes/'.$theme_dir.'/welocally-places-map-widget-display.php';
	}
	
	function wl_places_get_template_list_widget(){
		$templateOverride = locate_template( array( 'places/places-map-widget-display.php' ) );
		$theme_dir = get_theme_view_dir();	
		return  $templateOverride ? $templateOverride : dirname( __FILE__ ).'/views/themes/'.$theme_dir.'/welocally-places-list-widget-display.php';
	}
	
	function wl_places_get_template_category(){
		global $wlPlaces;
		$cat_map_layout_type = $wlPlaces->getSingleOption('cat_map_layout');
					
		if($cat_map_layout_type == 'none' 
			|| is_Feed()) {
			return;
		}
			
		$theme_dir = get_theme_view_dir();	
		return dirname( __FILE__ ) . '/views/themes/'.$theme_dir.'/category-places-map.php';
	}
}