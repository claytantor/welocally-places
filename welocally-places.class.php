<?php
require_once('welocally-places-tag.class.php');
require_once('welocally-places-tag-processor.class.php');

if ( !class_exists( 'WelocallyPlaces' ) ) {
	/**
	 * Main plugin
	 */
	class WelocallyPlaces {
		
		const VERSION 				= '1.1.18';
		const DB_VERSION			= '2.0';
		const WLERROROPT			= '_welocally_errors';
		const CATEGORYNAME	 		= 'Place';
		const OPTIONNAME 			= 'wl_place_options';
		// default formats, they are overridden by WP options or by arguments to date methods
		const DATEONLYFORMAT 		= 'F j, Y';
		const TIMEFORMAT			= 'g:i A';
		const DBDATEFORMAT	 		= 'Y-m-d';
		const DBDATETIMEFORMAT 		= 'Y-m-d G:i:s'; 
		
		public $displaying;
		public $latestOptions;
		public $pluginDir;
		public $pluginUrl;
		public $pluginDomain = 'welocallyPlaces';
		
		public $metaTags = array(
					'_isWLPlace',
					'_PlaceSelected',
					'_ShowPlaceAddress',
					self::WLERROROPT
				);
		
		
		/**
	     * Gets the Category id to use for a Place
	     * @return int|false Category id to use or false is none is set
	     */
	    static function placeCategory() {
			return get_cat_ID( WelocallyPlaces::CATEGORYNAME );
	    }
	    
	    static function wl_places_version() {
			return WelocallyPlaces::VERSION ;
	    }
	    
	    
		
		 /**
		  *  Initializes plugin variables and sets up wordpress hooks/actions.
		  *
		  * @return void
		  */
		function __construct( ) {
			//syslog(LOG_WARNING, "WelocallyPlaces __construct");
			
			$this->currentDay		= '';
			$this->pluginDir		= basename(dirname(__FILE__));
			$this->pluginUrl 		= WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
			$this->errors			= '';

			register_deactivation_hook( __FILE__, 	array( &$this, 'on_deactivate' ) );	
			register_activation_hook(__FILE__, 'add_defaults_fn');
			add_action( 'init',				array( $this, 'loadDomainStylesScripts' ) );
			add_action( 'pre_get_posts',	array( $this, 'setOptions' ) );
			add_action( 'admin_enqueue_scripts', 		array( $this, 'loadAdminDomainStylesScripts' ) );
			add_action( 'admin_menu', 		array( $this, 'addPlaceBox' ) );
			add_action( 'save_post',        array( $this, 'tagHandling'));
			add_action( 'template_redirect',array($this, 'templateChooser' ), 1 );
			
			add_action( 'sp_places_post_errors', array( 'WLPLACES_Post_Exception', 'displayMessage' ) );
			add_action( 'sp_places_options_top', array( 'WLPLACES_Options_Exception', 'displayMessage') );

            add_filter( 'the_content', array( $this, 'replaceTagsInContent') );
		}
		
		public function templateChooser() {

			$cat_map_layout_type = $this->getSingleOption('cat_map_layout');
					
			if($cat_map_layout_type == 'none' 
				|| is_Feed()) {
				return;
			}
			
			$cat_ID = get_query_var( 'cat' );
			
			$places_in_category_posts = get_places_posts_for_category($cat_ID);			
			
			error_log("got place count:".count($places_in_category_posts), 0);
			
			if(count($places_in_category_posts) == 0)
				return;
	
			
					
			global $wp_query, $wlPlaces;
			$options = $wlPlaces->getOptions();
			if ( $this->in_category()) {
				
			    if( '' == locate_template( array( 'places/category-places-map.php' ), true ) ) {
					$templateLoc = apply_filters('category_template','');
						//view
					load_template( $templateLoc );					
				}
				exit;	
			} else {
				return;
			}
		}
		
		
		
		/**
		 * @return 
		 */
		public function in_category() {
			
			$cat_id = get_query_var( 'cat' );
			
			if( !is_singular() && $cat_id ) {
				return true;
			}

		}
		
		/**
		 * @return bool true if is_category() is on a child of the place category
		 */
		public function in_place_category() {
			if( is_category( WelocallyPlaces::CATEGORYNAME ) ) {
				return true;
			}
			$cat_id = get_query_var( 'cat' );
			if( !is_singular() && $cat_id == $this->placeCategory() ) {
				return true;
			}
			$cats = get_categories('child_of=' . $this->placeCategory());
			$is_child = false;
			foreach( $cats as $cat ) {
				if( is_category( $cat->name ) ) {
					$is_child = true;
				}
			}
			return $is_child;
		}

		

		public function loadDomainStylesScripts() {
		
			
			$placesURL = trailingslashit( WP_PLUGIN_URL ) . trailingslashit( plugin_basename( dirname( __FILE__ ) ) ) . 'resources/';
			
			//app stuff, for right now we will embed this key but this should be coming from a web service
			wp_enqueue_script('google-maps' , 'https://maps.google.com/maps/api/js?key=AIzaSyACXX0_pKBA6L0Z2ajyIvh5Bi8h9crGVlg&sensor=true&language=en' , false , '3');
			wp_enqueue_script('wl-places-script', $placesURL.'places.js', array('jquery'), WelocallyPlaces::VERSION  );
			if( locate_template( array('places/places.css') ) ) {
				$templateArray = explode( '/', TEMPLATEPATH );
				$themeName = $templateArray[count($templateArray)-1];
				wp_enqueue_style('wl-places-style', WP_CONTENT_URL.'/themes/'.$themeName.'/places/places.css', array(), WelocallyPlaces::VERSION, 'screen' );
			} else wp_enqueue_style('wl-places-style', $placesURL.'places.css', array(), WelocallyPlaces::VERSION, 'screen' );
			
			wp_enqueue_style( 'tinymce_button', WP_PLUGIN_URL . '/welocally-places/resources/tinymce-button.css' );
			
			//font names
			$fonts[1] = $this->getSingleOption( 'font_place_name' );
			$fonts[2] = $this->getSingleOption( 'font_place_address' );
			
			$fontList = $this->makeUniqueGoogleFontList($fonts);
						 
			wp_enqueue_style( 'wl_font_list', 'https://fonts.googleapis.com/css?family='.$fontList );
						
			//admin stuff
			wp_enqueue_style( 'welocally_places', WP_PLUGIN_URL . '/welocally-places/resources/places.css' );
			wp_enqueue_style('thickbox');
			
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			/*wp_register_script('wl-upload', WP_PLUGIN_URL.'/welocally-places/resources/upload.js', array('jquery','media-upload','thickbox'));
			wp_enqueue_script('wl-upload');*/
			
			//color picker
			wp_enqueue_script('js-color-picker',WP_PLUGIN_URL.'/welocally-places/resources/jscolor.js', array('jquery'));
			
			wp_enqueue_script('wlplaces', WP_PLUGIN_URL . '/welocally-places/resources/wlplaces.js', array('jquery'), WelocallyPlaces::VERSION);
		}
		

		public function loadAdminDomainStylesScripts() {
					
			$placesURL = trailingslashit( WP_PLUGIN_URL ) 
				. trailingslashit( plugin_basename( dirname( __FILE__ ) ) ) . 'resources/';
			
			//app stuff
			wp_enqueue_script('jquery-ui-all' , 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');
			wp_enqueue_script('js-color-picker',WP_PLUGIN_URL.'/welocally-places/resources/jscolor.js', array('jquery'));									
			wp_enqueue_script('media-upload');
				 	
			wp_enqueue_style('thickbox');
			
			wp_register_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui-style' );		
		}
		
		public function makeUniqueGoogleFontList($fontList){
		
			$resultFonts = '';
			//make the list unique
			$uniqueFonts = array_unique($fontList);
			//iterate and format result;
			$lastkey = array_pop(array_keys($uniqueFonts));
			foreach ($uniqueFonts as $key => $value) {			
				$fontitem = str_replace( ' ', '+',  $value);
				$resultFonts = $resultFonts.$fontitem;
				if($key != $lastkey)
					$resultFonts = $resultFonts.'|';
			}
			
			return $resultFonts; 
		}
		
				
		public function setOptions( ) {
			global $wp_query;
			$display = ( isset( $wp_query->query_vars['placeDisplay'] ) ) ? $wp_query->query_vars['placeDisplay'] : wl_get_option('viewOption','all');
					
			switch ( $display ) {			
				case "all":
				case "default":
					$this->displaying		= "all";
					$this->startOperator	= ">";
					$this->order			= "ASC";
					
			}
		}
		
        /**
		 * Creates the category and sets up the theme resource folder with sample config files. Calls updateMapPostMeta().
		 * 
		 * @return void
		 */
		public function on_activate( ) {
		    global $wpdb;
		    
			$now = time();
			$firstTime = $now - ($now % 66400);
			$this->create_category_if_not_exists( );
			
			// create places table
			$db_version = get_option('Welocally_DBVersion');
			
			if ($db_version != self::DB_VERSION) {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                
                $sql = "CREATE TABLE {$wpdb->prefix}wl_places (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    wl_id VARCHAR(255) NOT NULL,
                    place TEXT NULL,
                    created DATETIME NOT NULL
                );";
                dbDelta($sql);

                $sql = "CREATE TABLE {$wpdb->prefix}wl_places_posts (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    place_id INT NOT NULL,
                    post_id MEDIUMINT(9) NOT NULL,
                    created DATETIME NOT NULL
                );";
                dbDelta($sql);
			}
			
			update_option('Welocally_DBVersion', self::DB_VERSION);
		}


		/**
		 * Adds the place specific query vars to Wordpress
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 * @return mixed array of query variables that this plugin understands
		 */
		public function placeQueryVars( $qvars ) {
			$qvars[] = 'placeDisplay';
			$qvars[] = 'placeName';
			return $qvars;		  
		}
			
		/* Callback for adding the Meta box to the admin page
		 * @return void
		 */
		public function addPlaceBox( ) {
			foreach (array('post','page') as $type)
		    {
		        add_meta_box( 'wl-place-finder-meta-1', __( 'Welocally Places', 'Place_textdomain' ), 
				                array( $this, 'placeMetaBox' ), $type, 'normal', 'high' );
		    }
 		}
		
		public function placeMetaBox() {
			global $post;
			
			$options = '';
			$style = '';
			$postId = $post->ID;
			foreach ( $this->metaTags as $tag ) {
				if ( $postId ) {
					$$tag = get_post_meta( $postId, $tag, true );
				} else {
					$$tag = '';
				}
			}
			
			$isWLPlace = get_post_meta( $postId, '_isWLPlace', true );
			$ShowPlaceAddress = get_post_meta( $postId, '_ShowPlaceAddress', true );
			
			$isPlaceChecked		= ( $isWLPlace == 'true' ) ? 'checked' : '';
			$isNotPlaceChecked		= ( $isWLPlace == 'false' || $isWLPlace == '' || is_null(  $isWLPlace ) ) ? 'checked' : '';
			$PlaceSelected = get_post_meta( $postId, '_PlaceSelected', true );
						
			include( dirname( __FILE__ ) . '/views/places-meta-box.php' );
		}

		public function replaceTagsInContent($content) {
            return WelocallyPlaces_Tag::searchAndReplace($content, array($this, 'addTagMarkup'));
		}

		public function addTagMarkup($tag, $str) {
			switch ($tag->type) {
				case 'post':
				default:
					/* handle [welocally id="..." /] tags */
					if (!$tag->id) return $str;

					return $this->getPlaceMapMarkup($tag);
					break;
				case 'category':
					/* handle [welocally categories="..." /] tag */
					$categoryIds = array();

					if (!$tag->categories):
						$categoryIds = array(get_query_var('cat')); // fetch category from request
					else:
						$categoryIds = array_map('get_cat_ID', $tag->categories);
					endif;

					if (!$categoryIds) return $str;

					$html = '';
					foreach ($categoryIds as $cat)
						$html .= $this->getCategoryMapMarkup($cat);

					return $html;

					break;
			}
		}

		/**
		 * Builds a place map for a given tag.
		 * @param object $tag the welocally place tag.
		 * @return string the place map HTML (javascript, etc.) ready to be embedded in the website.
		 */
		public function getPlaceMapMarkup($tag) {
		    global $post;
		    global $wpdb;
		    static $placecount = 0;

            if ($place = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wl_places WHERE wl_id = %s", $tag->id))) {
                $place_json = $place->place;
                
                $resultContent = '';
                
                $showmap=true;
                $cat_ID = get_query_var( 'cat' );
                
                // if(is_single()){
                if(is_singular()){
                 $showmap=true;
                } else if(is_home() || isset($cat_ID)){
                 $showmap=false;
                }
                
                $isCustom = false;
                $customMapJson = '[  ]';
                if(wl_get_option('map_custom_style') != ''){
                 $isCustom = true;
                 $customMapJson = wl_get_option("map_custom_style");
                }
                
                $whereImage=$this->pluginUrl.'/resources/images/here.png';
                

                // use place template
                $t = new StdClass();
                $t->uid = ++$placecount;
                $t->WPPost = $post->ID;
                $t->postId = $tag->postId;
                $t->placeJSON = $place_json;
                $t->options = array(
                  'showmap' => $showmap,
                  'isCustom' => $isCustom,                
                  'map_custom_style' => $customMapJson,
                  'map_icon_web' => wl_get_option("map_icon_web"),
                  'map_icon_directions' => wl_get_option("map_icon_directions"),
                  'font_place_name' => wl_get_option("font_place_name"),
                  'color_place_name' => wl_get_option("color_place_name"),
                  'size_place_name' => wl_get_option("size_place_name"),
                  'font_place_address' => wl_get_option("font_place_address"),
                  'color_place_address' => wl_get_option("color_place_address"),
                  'size_place_address' => wl_get_option("size_place_address"),
                  'where_image' => $whereImage
                );
                
                ob_start();
                include(dirname(__FILE__) . '/views/place-content-template.php');
                $resultContent = ob_get_contents();
                ob_end_clean();
                
                $t = null;
                
                return $resultContent;

            }
            
            return null;
		}

		/**
		 * Builds a category map for a given category.
		 * @param int|object $cat the category object or category ID (optional). defaults to the query category (if any).
		 * @return string the category map HTML (javascript, etc.) ready to be embedded in the website.
		 */
		public function getCategoryMapMarkup($cat=null) {
			static $uid = 0;

			if (!$cat)
				$cat = get_query_var('cat');
			elseif (is_object($cat))
				$cat = $cat->cat_ID;

			$posts = $this->getPlacePostsInCategory($cat);

			$t = new StdClass();
			$t->uid = ++$uid;
			$t->category = get_category($cat);
			$t->posts = $posts;
			$t->places = array();

			foreach ($t->posts as $post) {
				$post_places = $this->getPostPlaces($post->ID);

				foreach ($post_places as $place) {
					if (isset($t->places[$place->_id])) {
						$t->places[$place->_id]->posts[] = $post;
					} else {
						$t->places[$place->_id] = new StdClass();
						$t->places[$place->_id]->place = $place;
						$t->places[$place->_id]->posts = array($post);
					}
				}
			}
            
            ob_start();
            include(dirname(__FILE__) . '/views/includes/infobox-map-include.php');
            include(dirname(__FILE__) . '/views/includes/category-map-include.php');
            include(dirname(__FILE__) . '/views/category-map-content-template.php');
            $html = ob_get_contents();
            ob_end_clean();
            
            $t = null;

			return $html;
		}
		
		/* Callback for adding to the post itself
		 * @return void
		 */
		public function setupLoopStart( ) {
			include( dirname( __FILE__ ) . '/views/loop-start.php' );
		}

		

		/**
		 * Creates the places category
		 * @return int cat_ID
		 */
		public function create_category_if_not_exists( ) {
			if ( !category_exists( WelocallyPlaces::CATEGORYNAME ) ) {
				$category_id = wp_create_category( WelocallyPlaces::CATEGORYNAME );
				return $category_id;
			} else {
				return $this->placeCategory();
			}
		}
		
        public function tagHandling($post_id) {
            if (!wp_is_post_revision($post_id)) {
                $post = get_post($post_id);
                
                $tags = WelocallyPlaces_Tag::parseText($post->post_content);

                $proc = new WelocallyPlaces_TagProcessor();
                $result = $proc->processTags($tags, $post_id);
            }
        }
		
		
		/* gets the data from a URL */
		function get_url_data($url)
		{
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			return $data;
		}	
		

		/// OPTIONS DATA
		//--------------------------------------------
        public function getOptions() {
            $this->latestOptions = get_option(WelocallyPlaces::OPTIONNAME, array());
            return $this->latestOptions;
        }

		public function getSingleOption( $key ) {
			$options = $this->getOptions();
			return $options[$key];
		}
		        
        public function saveOptions($options) {
           update_option(WelocallyPlaces::OPTIONNAME, $options);
           $this->latestOptions = $options;
        }
        
        public function deleteOptions() {
            delete_option(WelocallyPlaces::OPTIONNAME);
        }

		
		public function truncate($text, $excerpt_length = 44) {
			$text = strip_shortcodes( $text );

			$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]&gt;', $text);
			$text = strip_tags($text);

			$words = explode(' ', $text, $excerpt_length + 1);
			if (count($words) > $excerpt_length) {
				array_pop($words);
				$text = implode(' ', $words);
				$text = rtrim($text);
				$text .= '&hellip;';
			}
			return $text;
		}

		/*
		 * just remove legacy meta
		 */
		public function deletePostPlacesMeta($postId) {
			
		    global $wpdb;
			    
			//will recurse to elements    		    		   	    
		    if (is_array($postId)) {  	        
		        foreach ($postId as $p) {		            
		            $this->deletePostPlacesMeta($p);
		        }
		    }		    
	    
		    $wpdb->query(
					"
					DELETE FROM {$wpdb->prefix}postmeta 
					WHERE post_id = '$postId' AND meta_key='_PlaceSelected';
					"
					);
		    	    		   
		}
		
		/*
		 * this is probably inefficient and could be done better with
		 * some sort of join
		 */
		public function deletePostPlaces($postId) {
			
		    global $wpdb;
			    
			//will recurse to elements    		    		   	    
		    if (is_array($postId)) {  	        
		        foreach ($postId as $p) {		            
		            $this->deletePostPlaces($p);
		        }
		    }		    
		    	    
		    $postId = is_array($postId) ? $postId['ID'] : (is_object($postId) ? $postId->ID : intval($postId));
		    
		    //remove it from places table
		    $post_places = $this->getPostPlaces($postId) ;
		    foreach ($post_places as $place) {

		    	$wpdb->query(
					"
					DELETE FROM {$wpdb->prefix}wl_places 
					WHERE wl_id = '$place->_id';
					"
					);		    	
		    }		    	
		    
		    //remove it from posts
		    $wpdb->query(
					"
					DELETE FROM {$wpdb->prefix}wl_places_posts 
					WHERE post_id = $postId;
					"
					);		  
		}
		
		public function getPostPlaces($postId) {
		    global $wpdb;
		    
		    if (is_array($postId)) {
                $places = array();
		        
		        foreach ($postId as $p) {
		            if (is_object($p) || is_array($p)) {
		                $p_ = (array) $p;
		                $places = $places + $this->getPostPlaces($p_['ID']);
		            } else if (is_int($p)) {
		                $places = $places + $this->getPostPlaces($p);
		            }
		        }
		        
                return $places;
		    }
		    
		    $postId = is_array($postId) ? $postId['ID'] : (is_object($postId) ? $postId->ID : intval($postId));
		    
		    $places = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT place FROM {$wpdb->prefix}wl_places p INNER JOIN {$wpdb->prefix}wl_places_posts pp ON p.id = pp.place_id WHERE pp.post_id = %d", $postId));
		    
		    if ($places) 
		        return array_map('json_decode', $places);
		        
		    return array();
		}

		public function getPostPlacesMeta($postId) {
		    global $wpdb;
		    
		    if (is_array($postId)) {
                $places = array();
		        
		        foreach ($postId as $p) {
		            if (is_object($p) || is_array($p)) {
		                $p_ = (array) $p;
		                $places = $places + $this->getPostPlacesMeta($p_['ID']);
		            } else if (is_int($p)) {
		                $places = $places + $this->getPostPlacesMeta($p);
		            }
		        }
		        
                return $places;
		    }
		    
		    $postId = is_array($postId) ? $postId['ID'] : (is_object($postId) ? $postId->ID : intval($postId));
		    
		    $places = $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d and meta_key='_PlaceSelected'", $postId));
		    
		    if ($places) 
		        //return array_map('json_decode', $places);
		        return json_decode($places[0]);
		        
		    return array();
		}	

		/**
		 * Return the posts with places associated inside a category.
		 * @param int the category ID
		 * @param string|array post types to search for in the category (defaults to 'post'). use false/null if you want to retrieve all post types.
		 * @return array 
		 */
		public function getPlacePostsInCategory($categoryId, $post_type='post') {
			global $wpdb, $wlPlaces;
			$wlPlaces->setOptions();

			$post_type_and = '1=1';
			if (is_array($post_type) && !empty($post_type)) {
				$post_type_and = "{$wpdb->posts}.post_type IN ('" . implode('\',\'', $post_type) . "')";
			} elseif (is_string($post_type)) {
				$post_type_and = "{$wpdb->posts}.post_type = '" . $wpdb->escape($post_type) . "'";
			}

			$categories_query = "select 
	                        $wpdb->posts.*
	                        from $wpdb->term_taxonomy, $wpdb->term_relationships, $wpdb->posts, $wpdb->terms 
	                        where $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
	                        AND $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id
	                        AND $wpdb->posts.id = $wpdb->term_relationships.object_id
	                        AND $wpdb->term_taxonomy.term_id = $categoryId
	                        AND $wpdb->term_taxonomy.taxonomy = 'category' 
	                        AND $wpdb->posts.post_status = 'publish' AND " . $post_type_and . "
	                        AND $wpdb->posts.id in (select $wpdb->posts.ID  
	                                FROM $wpdb->posts INNER JOIN {$wpdb->prefix}wl_places_posts
	                                ON $wpdb->posts.ID = {$wpdb->prefix}wl_places_posts.post_id
	                                GROUP BY $wpdb->posts.ID)";

			$return = $wpdb->get_results($categories_query, OBJECT);
			return $return;
		}

		/**
		 * Returns posts with associated place information.
		 * @param string|array post types to search for (defaults to 'post'), use false/null to search all post types
		 * @param string|int|array name or ID of category to search in or array of such IDs/names in order to search in several categories at once (intval is required for integers)
		 * @param array array of other options: 'limit' (max number of results), 'order_by' (column name for ordering) and 'order_dir' (ordering direction)
		 * @return array posts with associated places that match the filtering options
		 */
		public function getPlacePosts($post_type='post', $category=null, $opts=array()) {
			global $wpdb, $wlPlaces;
			$wlPlaces->setOptions();

			$options = array_merge(array('limit' => null, 'order_by' => "{$wpdb->posts}.ID", 'order_dir' => 'asc'), $opts);

			// filter by post type
			$post_typeClause = '1=1';
			if (is_array($post_type) && !empty($post_type)) {
				$post_typeClause = "{$wpdb->posts}.post_type IN ('" . implode('\',\'', $post_type) . "')";
			} elseif (is_string($post_type)) {
				$post_typeClause = "{$wpdb->posts}.post_type = '" . $wpdb->escape($post_type) . "'";
			}

			// filter by categories
			$categoriesClause = '1=1';
			$categories = array();
			if (!is_array($category)) $category = array($category);

			foreach ($category as $cat) {
				if (is_string($cat)) {
					if ($catid = get_cat_ID($cat))
						$categories[] = $catid;
				} elseif (is_int($cat) && $cat > 0) {
					$categories[] = $cat;
				}
			}

			if ($categories) {
				$categoriesClause = "{$wpdb->term_taxonomy}.term_id IN (" . implode(',', $categories) . ")";
			}

			// ordering/limit number
			$orderClause = '';
			if ($options['order_by'])
				$orderClause = sprintf('ORDER BY %s %s', $options['order_by'], $options['order_dir'] ? $options['order_dir'] : 'ASC');
			
			$limitClause = '';
			if ($options['limit'] && $options['limit'] > 0) {
				$limitClause = sprintf('LIMIT %d', $options['limit']);
			}

			$query = "
				SELECT {$wpdb->posts}.* FROM {$wpdb->posts}
				LEFT JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)
				LEFT JOIN {$wpdb->term_taxonomy} ON ({$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id)
				WHERE {$wpdb->term_taxonomy}.taxonomy = 'category'
				AND {$categoriesClause}
				AND {$wpdb->posts}.post_status = 'publish'
				GROUP BY {$wpdb->posts}.ID
				{$orderClause}
				{$limitClause}
			";

	  		$results = $wpdb->get_results($query);	  		
			return $results;
		}
			
	}

	global $wlPlaces;
	$wlPlaces = new WelocallyPlaces();
}
?>
