<?php
if ( !class_exists( 'WelocallyPlaces' ) ) {
	/**
	 * Main plugin
	 */
	class WelocallyPlaces {
		
		const VERSION 				= '1.0.16';
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
			add_action( 'save_post',		array( $this, 'addPlaceMetaSave' ), 15 );
			add_action( 'publish_post',		array( $this, 'addPlaceMetaPublish' ), 15 );
			add_action( 'template_redirect',array($this, 'templateChooser' ), 1 );
			
			add_action( 'sp_places_post_errors', array( 'WLPLACES_Post_Exception', 'displayMessage' ) );
			add_action( 'sp_places_options_top', array( 'WLPLACES_Options_Exception', 'displayMessage') );
			
			add_filter( 'the_content', array($this, 'wl_content_search' ));
			
		}
		
		
		function wl_content_search( $content ) {
			$content = str_replace( '[welocally/]', $this->addPostPlaceInfoMarkup( $GLOBALS['post']->ID ), $content );
			return $content;
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
			wp_enqueue_script('google-maps' , 'https://maps.google.com/maps/api/js?key=AIzaSyACXX0_pKBA6L0Z2ajyIvh5Bi8h9crGVlg&sensor=true' , false , '3');
			wp_enqueue_script('sp-places-script', $placesURL.'places.js', array('jquery') );
			if( locate_template( array('places/places.css') ) ) {
				$templateArray = explode( '/', TEMPLATEPATH );
				$themeName = $templateArray[count($templateArray)-1];
				wp_enqueue_style('sp-places-style', WP_CONTENT_URL.'/themes/'.$themeName.'/places/places.css', array(), WelocallyPlaces::VERSION, 'screen' );
			} else wp_enqueue_style('sp-places-style', $placesURL.'places.css', array(), WelocallyPlaces::VERSION, 'screen' );
			
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
			
		}
		

		public function loadAdminDomainStylesScripts() {
					
			$placesURL = trailingslashit( WP_PLUGIN_URL ) 
				. trailingslashit( plugin_basename( dirname( __FILE__ ) ) ) . 'resources/';
			
			//app stuff
			wp_enqueue_script('jquery-ui-all' , 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');
			wp_enqueue_script('js-color-picker',WP_PLUGIN_URL.'/welocally-places/resources/jscolor.js', array('jquery'));									
			wp_enqueue_script('media-upload');
				 	
			wp_enqueue_style('thickbox');
			
			wp_register_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/south-street/jquery-ui.css' );
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
			$now = time();
			$firstTime = $now - ($now % 66400);
			$this->create_category_if_not_exists( );	
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
				add_meta_box( 'Place Details', __( 'Welocally Places', 'Place_textdomain' ), 
		                array( $this, 'placeMetaBox' ), 'post', 'normal', 'high' );
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
		
		


	
		
		/* Callback for adding to the post itself
		 * @return void
		 */
		public function addPostPlaceInfoMarkup( $postId ) {
			$resultContent = '';
			$PlaceSelected = get_post_meta( $postId, '_PlaceSelected', true );
			$ShowPlaceAddress = get_post_meta( $postId, '_ShowPlaceAddress', true );
			
			$showmap='true';
			$cat_ID = get_query_var( 'cat' );
			if(is_single()){
				$showmap='true';
			} else if(is_home() || isset($cat_ID)){
				$showmap='false';
			}
			
			$isCustom = 'false';
			$customMapJson = '[  ]';
			if(wl_get_option('map_custom_style') != ''){
				$isCustom = 'true';
				$customMapJson = base64_decode(wl_get_option("map_custom_style"));
			}
			
			$whereImage=$this->pluginUrl.'/resources/images/here.png';
			
			
			$template = file_get_contents(dirname( __FILE__ ) . '/views/place-content-template.php');
			
			
			
			$map_post_div= '<div id="map_canvas_post"></div>';
			
			
			/*
			%1$d - $postId
			%2$s - $PlaceSelected
			%3$s - $showmap
			%4$s - $isCustom
			%5$s - $option["map_custom_style"]
			%6$s - $option["map_icon_web"]
			%7$s - $option["map_icon_directions"]
			%8$s - $option["font_place_name"]
			%9$s - $option["color_place_name"]
			%10$s - $option["size_place_name"]
			%11$s - $option["font_place_address"]
			%12$s - $option["color_place_address"]
			%13$s - $option["size_place_address"]
			%14$s - where image file
							
			*/
			
			$resultContent = sprintf ( 
				$template, 
				$postId, 
				str_replace("'", "\'", $PlaceSelected),  
				$showmap, 
				$isCustom, 
				$customMapJson,
				wl_get_option("map_icon_web"),
				wl_get_option("map_icon_directions"),
				wl_get_option("font_place_name"),
				wl_get_option("color_place_name"),
				wl_get_option("size_place_name"),
				wl_get_option("font_place_address"),
				wl_get_option("color_place_address"),
				wl_get_option("size_place_address"),
				$whereImage			
				);		
					
			return $resultContent;
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
		
		/**
		 * redundant
		 */
		public function create_custom_category_if_not_exists( $categoryName ) {
			if ( !category_exists( $categoryName ) ) {
				$category_id = wp_create_category( $categoryName );
				return $category_id;
			} else {
				return get_cat_ID( $categoryName );
			}
			
		}
		
		
		
		public function addPlaceMetaSave( $postId ) {
			$this->addPlaceMeta( $postId, 'save_post' );	
		}	
		
		public function addPlaceMetaPublish( $postId ) {
			$this->addPlaceMeta( $postId, 'publish_post' );	
		}	
			
		public function addPlaceMeta( $postId, $action ) {
			error_log("addPlaceMeta: [".$_POST['PlaceSelected']."]", 0);
			//check to delete existing place info
			
			
			if(!empty( $_POST['deletePlaceInfo'] )){
				delete_post_meta($postId, '_PlaceSelected');
				delete_post_meta($postId, '_isWLPlace');
				update_post_meta( $postId, '_isWLPlace', 'false' );
			} else if( !empty( $_POST['PlaceSelected']) ) {

				//error_log("save case 1: [".$_POST['PlaceSelected']."]", 0);

				$category_id = $this->create_category_if_not_exists();				
							
				update_post_meta( $postId, '_PlaceSelected',  $_POST['PlaceSelected']);
				update_post_meta( $postId, '_isWLPlace', 'true' );
				
						
				//do custom categories
				if(isset( $_POST['PlaceCategoriesSelected'] )){
					
					$custom_categories = preg_split('/[,]+/', $_POST['PlaceCategoriesSelected'],-1, PREG_SPLIT_NO_EMPTY);
					
					$cat_index = 0;
					$custom_cat_ids = array();
					foreach ( $custom_categories as $custom_cat ) {
						$custom_cat_ids[$cat_index] = $this->create_custom_category_if_not_exists($custom_cat);	
						$cat_index = $cat_index+1;	 					
					}
					
					// merge place category into this post
					$cats = wp_get_object_terms($postId, 'category', array('fields' => 'ids'));
					$new_cats1 = array_merge( array( $category_id ), $cats ); 
					$new_cats2 = array_merge( $new_cats1, $custom_cat_ids );
					wp_set_post_categories( $postId, $new_cats2 );
									
				} 
							
			} 			
			/*else if($_POST['isWLPlace'] == 'true') {
				update_post_meta( $postId, '_isWLPlace', 'true' );
			}*/
			else if($_POST['isWLPlace'] == 'true' && empty( $_POST['PlaceSelected'] )) {
				error_log("save case 2", 0);
				update_post_meta( $postId, '_isWLPlace', 'false' );
			} 
			else if($_POST['isWLPlace'] == 'false' || !isset( $_POST['isWLPlace']) ) {
				error_log("save case 3", 0);
				update_post_meta( $postId, '_isWLPlace', 'false' );
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
		
		
		
	}
	global $wlPlaces;
	$wlPlaces = new WelocallyPlaces();
}
?>