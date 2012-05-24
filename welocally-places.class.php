<?php
require_once('welocally-places-tag.class.php');
require_once('welocally-places-tag-processor.class.php');

if ( !class_exists( 'WelocallyPlaces' ) ) {
	/**
	 * Main plugin  
	 */
	class WelocallyPlaces {
		
		const VERSION 				= '1.2.22';
		const DB_VERSION			= '2.0';
		const WLERROROPT			= '_welocally_errors';
		const CATEGORYNAME	 		= 'Place';
		const OPTIONNAME 			= 'wl_place_options';
		const DATEONLYFORMAT 		= 'F j, Y';
		const TIMEFORMAT			= 'g:i A';
		const DBDATEFORMAT	 		= 'Y-m-d';
		const DBDATETIMEFORMAT 		= 'Y-m-d G:i:s'; 
		
		public $displaying;
		public $latestOptions;
		public $pluginDir;
		public $pluginUrl;
		public $pluginDomain = 'welocallyPlaces';
		
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
			
			$this->currentDay		= '';
			$this->pluginDir		= basename(dirname(__FILE__));
			$this->pluginUrl 		= WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
			$this->errors			= '';
			
			add_action( 'init',				array( $this, 'loadDomainStylesScripts' ) );
			add_action( 'pre_get_posts',	array( $this, 'setOptions' ) );
			add_action( 'admin_enqueue_scripts', 		array( $this, 'loadAdminDomainStylesScripts' ) );
			add_action( 'admin_menu', 		array( $this, 'addPlaceBox' ) );
			add_action( 'save_post',        array( $this, 'handlePublish'));
			
			add_action( 'sp_places_post_errors', array( 'WLPLACES_Post_Exception', 'displayMessage' ) );
			add_action( 'sp_places_options_top', array( 'WLPLACES_Options_Exception', 'displayMessage') );

            add_shortcode('welocally', array ($this,'handleWelocallyPlacesShortcode'));
           
                           
		}
		
		
		function wl_admin_message_1() {
			return $this->wl_admin_message('Welocally Places Activated',false);
		}
		
		function wl_admin_message($message='empty message', $error = false) {
			echo '<div class="updated fade"><p>'.$message.'</p></div>';
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
			
			$options = $this->getOptions();
			if(!empty($options['key1'])){
				wp_enqueue_script('google-maps' , 'https://maps.google.com/maps/api/js?key='.$options['key1'].'&sensor=false&language=en' , false , '3');							
			} else {
				wp_enqueue_script('google-maps' , 'https://maps.google.com/maps/api/js?sensor=false' , false , '3');							
			}	
			
			wp_enqueue_script( 'jquery' ); 
            wp_enqueue_script('jquery-ui-all' , 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');
                      								
			//welocally
			wp_enqueue_script('wl_base_script', WP_PLUGIN_URL.'/welocally-places/resources/javascripts/wl_base.js', array('jquery'), WelocallyPlaces::VERSION);
			wp_enqueue_script('wl_place_widget_script',  WP_PLUGIN_URL.'/welocally-places/resources/javascripts/wl_place_widget.js', array('jquery'), WelocallyPlaces::VERSION);
			wp_enqueue_script('wl_infobox_script', WP_PLUGIN_URL.'/welocally-places/resources/javascripts/wl_infobox.js', array('jquery'), WelocallyPlaces::VERSION);
			wp_enqueue_script('wl_places_multi_script', WP_PLUGIN_URL.'/welocally-places/resources/javascripts/wl_places_multi_widget.js', array('jquery'), WelocallyPlaces::VERSION);
			
			global $wp_styles;
		
			wp_enqueue_style( 'wl_places',WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/wl_places.css', array(), WelocallyPlaces::VERSION, 'screen' );
			wp_register_style('wl_places-ie-only', WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/ie/wl_places.css');
			$wp_styles->add_data('wl_places-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_places-ie-only');
						
			wp_enqueue_style( 'wl_places_place',WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/wl_places_place.css', array(), WelocallyPlaces::VERSION, 'screen' );
			wp_register_style('wl_places_place-ie-only', WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/ie/wl_places_place.css');
			$wp_styles->add_data('wl_places_place-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_places_place-ie-only');
			
			wp_enqueue_style( 'wl_places_multi',WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/wl_places_multi.css', array(), WelocallyPlaces::VERSION, 'screen' );			
			wp_register_style('wl_places_multi-ie-only', WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/ie/wl_places_multi.css');
			$wp_styles->add_data('wl_places_multi-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_places_multi-ie-only');		
			
			//wp specific
			wp_enqueue_style( 'wl_places_wp',WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/wl_places_wp.css', array(), WelocallyPlaces::VERSION, 'screen' );
			wp_register_style('wl_places_wp-ie-only', WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/ie/wl_places_wp.css');
			$wp_styles->add_data('wl_places_wp-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_places_wp-ie-only');	
			

			
		}
		
		public function loadAdminDomainStylesScripts() {
					
			$placesURL = trailingslashit( WP_PLUGIN_URL ) 
				. trailingslashit( plugin_basename( dirname( __FILE__ ) ) ) . 'resources/';
			
			wp_enqueue_script( 'jquery' ); 
            wp_enqueue_script('jquery-ui-all' , 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');
                      								
												
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			//welocally
			wp_enqueue_script('wl_addplace_widget_script', WP_PLUGIN_URL.'/welocally-places/resources/javascripts/wl_addplace_wp.js', array('jquery'), WelocallyPlaces::VERSION);
			wp_enqueue_script('wl_placesmgr_script', WP_PLUGIN_URL.'/welocally-places/resources/javascripts/wl_placemgr.js', array('jquery'), WelocallyPlaces::VERSION);
			wp_enqueue_script('wl_pager', WP_PLUGIN_URL.'/welocally-places/resources/javascripts/wl_pager_wp.js', array('jquery'), WelocallyPlaces::VERSION);
					 	
			wp_enqueue_style('thickbox');
			
			//add places
			wp_enqueue_style( 'wl_places_addplace_style',WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/wl_places_addplace.css',array(), WelocallyPlaces::VERSION, 'screen' );	
						
			global $wp_styles;
		
			wp_enqueue_style( 'wl_pager',WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/wl_pager.css', array(), WelocallyWPPagination::VERSION, 'screen' );
			wp_register_style('wl_pager-ie-only', WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/ie/wl_pager.css');
			$wp_styles->add_data('wl_pager-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_pager-ie-only');
			
			$options = $this->getOptions();
			if(empty($options['pager_theme']))
				$options['pager_theme'] = 'basic'; 

			wp_enqueue_style( 'wl_pager_'.$options['pager_theme'],WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/wl_pager_'.$options['pager_theme'].'.css', array(), WelocallyPlaces::VERSION, 'screen' );
			wp_register_style('wl_pager_'.$options['pager_theme'].'-ie-only', WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/ie/wl_pager_'.$options['pager_theme'].'.css');
			$wp_styles->add_data('wl_pager_'.$options['pager_theme'].'-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_pager_'.$options['pager_theme'].'-ie-only');
			
			//admin specific
			wp_enqueue_style( 'wl_places_admin',WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/wl_places_admin.css', array(), WelocallyPlaces::VERSION, 'screen' );
			wp_register_style('wl_places_admin-ie-only', WP_PLUGIN_URL.'/welocally-places/resources/stylesheets/ie/wl_places_admin.css');
			$wp_styles->add_data('wl_places_admin-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_places_admin-ie-only');	
			
			
		}
		
		public function handleWelocallyPlacesShortcode($attrs,$content = null) {
			
			global $post;
			
			extract(shortcode_atts(array (
				'categories' => null,
				'category' => null,
				'id' => null,
				'post_type'=> null				
			), $attrs));			
			
			//strip paragragh tags if exists this is required
			if ( '</p>' == substr( $content, 0, 4 )
			and '<p>' == substr( $content, strlen( $content ) - 3 ) )
				$content = substr( $content, 4, strlen( $content ) - 7 );
			
			//template for javscript
			$t = new StdClass();
			$t->uid = uniqid();
			
			if(!empty($id)) {
				//this is a place
				$t->wl_id = $id;
				
				$this->processPlaceTag($t, $post->ID);
				
				if(isset($t->place)){
					ob_start();
	                include(dirname(__FILE__) . '/views/place-content-template.php');
	                $resultContent = ob_get_contents();
	                ob_end_clean();			            
		            $t = null;			      
		            return $resultContent;						
				} else {
					return false;
				}					
			}
			
			//right now only one is supported
			$catsSlug = array();						
			if(!empty($categories)) 
				$catsSlug = explode(",", $categories);
			else if(!empty($category))
				$catsSlug = explode(",", $category);
				
			$string = preg_replace('/&(?!amp;)/', '&amp;', $catsSlug[0]);
			$string = preg_replace('/#038;/', '', $string);
		
			$term = get_term_by('name',$string,'category');
			if(!isset($post_type))
				$post_type = 'post';

			return $this->getCategoryMapMarkup($term->term_id, null, null, 25, $post_type);		

		}
		
		
		public function processPlaceTag($tag, $postId=0) {
        	
            global $wpdb;
            global $wlPlaces;
                       
            if (!$tag->wl_id)
                return false;
                
            $postId = intval($postId);
            
            $place = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wl_places WHERE wl_id = %s", $tag->wl_id));
            
            //there is a problem
            if (!$place)
                return false;
            else {
            	$tag->place = $place;
            	$tag->placeJSON = $place->place;
            	
            }
            
            return $place;

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
			
			$display = "all";
					
			switch ( $display ) {			
				case "all":
				case "default":
					$this->displaying		= "all";
					$this->startOperator	= ">";
					$this->order			= "ASC";
					
			}
		}
		
        /**
		 * Creates the category and sets up the theme resource folder with sample 
		 * config files. Calls updateMapPostMeta(). Does all the hard work of figuring out
		 * what the database should look like.
		 * 
		 * @return void
		 */
		public function on_activate( ) {
		    global $wpdb;
		    
			$now = time();
			$firstTime = $now - ($now % 66400);
			$this->create_category_if_not_exists( );
						
			
			//never created before
			if (!$this->tableExists($wpdb->prefix.'wl_places')) {
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                
                $sql = "CREATE TABLE {$wpdb->prefix}wl_places (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    wl_id VARCHAR(255) NOT NULL,
                    place TEXT NULL, " .
                    "likes INT NULL DEFAULT 0, ".
                    "lat DOUBLE NULL, " .
                    "lng DOUBLE NULL,
                    created DATETIME NOT NULL
                );";
                dbDelta($sql);				
			}
			
			if (!$this->tableExists($wpdb->prefix.'wl_places_posts')) {
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                
                $sql = "CREATE TABLE {$wpdb->prefix}wl_places_posts (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    place_id INT NOT NULL,
                    post_id MEDIUMINT(9) NOT NULL,
                    created DATETIME NOT NULL
                );";
                dbDelta($sql);			
			}
			
			//version 1.2.21 and earler had places table but not geo
			if (!$this->columnExists($wpdb->prefix.'wl_places', 'likes')) {
				
				$sql = "ALTER TABLE {$wpdb->prefix}wl_places ".
					"ADD COLUMN likes INT NULL DEFAULT 0 AFTER place";

				$wpdb->query($sql);                
			}
			
			$upgrade_g1 = false;
			if (!$this->columnExists($wpdb->prefix.'wl_places', 'lat')) {
				$upgrade_g1 = true;
                
                $sql = "ALTER TABLE {$wpdb->prefix}wl_places " .
                		"ADD COLUMN lat DOUBLE NULL AFTER likes";
                $wpdb->query($sql);   	
			}
		
			if (!$this->columnExists($wpdb->prefix.'wl_places', 'lng')) {
								
				$upgrade_g1 = true;
                
                $sql = "ALTER TABLE {$wpdb->prefix}wl_places " .
                		"ADD COLUMN lng DOUBLE NULL AFTER lat";
                
                $wpdb->query($sql);
			}
			
			//put the latlng for all the places in the table
			if(true){
				$query = "SELECT id,place FROM {$wpdb->prefix}wl_places";
				$results = $wpdb->get_results($query, ARRAY_A);
				$count = count($results);
			    for ($i = 0; $i < $count; $i++) {
			    	$row = $results[$i];
			    	$place = json_decode($row['place']);
			    	
			    	$lat = $place->geometry->coordinates[1];
			    	$lng = $place->geometry->coordinates[0];
			    	
			    	//update row
			    	$wpdb->update( 
						$wpdb->prefix.'wl_places', 
						array( 
							'lat' => $lat,	// float
							'lng' => $lng	// float 
						), 
						array( 'id' => $row['id'] ), 
						array( 
							'%s',	// value1
							'%s'	// value2
						), 
						array( '%d' ) 
					);
			    				    	
			    }
			}
							
		}
		
		//checks all schema changes and makes sure all is ok.
		public function databaseRequirementsMissing(){
			global $wpdb;
			$reqs = array();
			if(!$this->tableExists($wpdb->prefix.'wl_places'))
				array_push($reqs,'table missing '.$wpdb->prefix.'wl_places');
			if(!$this->tableExists($wpdb->prefix.'wl_places_posts'))
				array_push($reqs,'table missing '.$wpdb->prefix.'wl_places_posts');
			if(!$this->columnExists($wpdb->prefix.'wl_places', 'likes'))
				array_push($reqs,'column missing '.$wpdb->prefix.'wl_places.likes');
			if(!$this->columnExists($wpdb->prefix.'wl_places', 'lat'))
				array_push($reqs,'column missing '.$wpdb->prefix.'wl_places.lat');
			if(!$this->columnExists($wpdb->prefix.'wl_places', 'lng'))
				array_push($reqs,'column missing '.$wpdb->prefix.'wl_places.lng');
				

			return $reqs;
						
		}
		
		function showTables() {
			global $wpdb;
			$return = $wpdb->get_results("show tables", ARRAY_A);
			foreach ( $return as $row ) {
				echo("<div><pre>");
				echo($row["Tables_in_wordpress"]);  
				echo("</pre></div>");    
			}
			return $return;
		}
		
		function describeTable($table_name) {
			global $wpdb;
			$return = $wpdb->get_results(sprintf("describe %s",$table_name), ARRAY_A);
			foreach ( $return as $row ) {
				echo("<div><pre>");
				echo($row["Field"]);
				echo("\t");  
				echo($row["Type"]); 
				echo("</pre></div>");    
			}
			return $return;
		}
		
		public function tableExists($tableName){
			global $wpdb;
			$query = sprintf("SHOW TABLES LIKE '%s' ",$tableName);
			$result = $wpdb->get_var( $wpdb->prepare( $query ) );
			
			if($result == $tableName){
				return true;
			} else {
				return false;
			}
		}
		
		public function columnExists($tableName, $columnName){

			global $wpdb;
			$query = sprintf("SHOW COLUMNS FROM %s LIKE '%s'",$tableName, $columnName);
			$result = $wpdb->get_var( $wpdb->prepare( $query ) );
			if($result == $columnName){
				return true;
			} else {
				return false;
			}
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
		        add_meta_box( 'wl-place-add-meta-2', __( 'Welocally Add Place', 'Place_textdomain' ), 
				                array( $this, 'addPlaceMetaBox' ), $type, 'normal', 'high' );
				            
		    }
 		}

		
		public function addPlaceMetaBox() {
			global $post;
			include( dirname( __FILE__ ) . '/views/addplace-meta-box.php' );
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
		    			
			//changing this to join so it actually must be in post places, someone please
			//use your sprintf magic!
			$place = $wpdb->get_row($wpdb->prepare(
				"SELECT {$wpdb->prefix}wl_places.* " .
				"FROM {$wpdb->prefix}wl_places,{$wpdb->prefix}wl_places_posts " .
				" WHERE {$wpdb->prefix}wl_places.id={$wpdb->prefix}wl_places_posts.place_id " .
				" AND {$wpdb->prefix}wl_places_posts.post_id =".$post->ID.
				" AND wl_id = '". $tag->id."'"));

            if($place) {
                
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

                $whereImage=$this->pluginUrl.'/resources/images/here.png';
                

                // use place template
                $t = new StdClass();
                $t->uid = ++$placecount;
                $t->WPPost = $post->ID;
                $t->placeJSON = $place_json;
                $t->options = array(
                  'showmap' => $showmap,
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
		
		public function getId($filtervalue){
			
		}
		
		
		public function getPlacesNew($maxElements=25) {
			
			global $wpdb;
			
			$query = "SELECT * FROM {$wpdb->prefix}wl_places" ;
			
			$result = $wpdb->get_results($query, OBJECT);
			return $result;		
		
		}
		
		
		/**
		 * this takes the category name, consider consolidating getplacesnew and this
		 */
		public function getPlaces($cat=null, $maxElements=25, $filter=true, $post_type='post') {
			
			
			global $post;			
						
			static $uid = 0;

			if (!$cat)
				$cat = get_query_var('cat');
			elseif (is_object($cat))
				$cat = $cat->cat_ID;
			elseif (isset($post) && !(is_home() || is_front_page())){
				//try to get category for post if possible
				$post_categories = wp_get_post_categories( $post->ID );
				if($post_categories){		
					foreach ($post_categories as $postCatId) {
						$catObj = get_category( $postCatId );
						if($catObj->name != 'Uncategorized' && $catObj->name != 'Place'){
							$cat = $postCatId;
						}
					}
				}
			}
					
			$posts = $this->getPlacePostsInCategory($cat,$post_type);

			$t = new StdClass();
			$t->uid = ++$uid;
			$t->category = get_category($cat);
			$t->catId = $t->category->cat_ID;
			$t->posts = $posts;
			$t->places = array();
			
			$pids = array();
			
			$options = $this->getOptions();

			foreach ($t->posts as $postlocal) {
				$post_places = $this->getPostPlaces($postlocal->ID);

				foreach ($post_places as $place) {
					
					
					//determin if we are filtering
					if(!$filter || !in_array($place->_id, $pids)){
						if($options['infobox_title_link']=='on'){
	   						$place->properties->titlelink=get_permalink( $postlocal->ID ) ;	
	   					} 
						
						$place->post = $postlocal;		
						unset($place->post->post_content);								
						array_push($t->places, $place);
						array_push($pids, $place->_id);						
					}					

				}
				
				//max
				if(count($t->places)>$maxElements){
					$t->places = array_slice($t->places, 0, $maxElements);
					break;
				}
			}	
					
			return $t;
		}

		/**
		 * Builds a category map for a given category.
		 * @param int|object $cat the category object or category ID (optional). defaults to the query category (if any).
		 * @return string the category map HTML (javascript, etc.) ready to be embedded in the website.
		 */
		public function getCategoryMapMarkup(
			$cat=null, $template=null, $showIfEmpty=null, $maxElements=25, $post_type='post') {
			
			
			$t = $this->getPlaces($cat, $maxElements, true, $post_type);
				
					
			//setup options
			$options = $this->getOptions();
			
			$marker_image_path = WP_PLUGIN_URL.'/welocally-places/resources/images/marker_all_base.png' ;
			
			 
			$endpoint = 'https://api.welocally.com';
			if(isset($options[ 'api_endpoint' ]) && $options[ 'api_endpoint' ] !=''){
				$endpoint = $options[ 'api_endpoint' ];
			} 
            
            ob_start();
            
            //we do this so we can provide different style overrides for different template views 
            //while keeping the same controller
            if(count($t->places)>0){
            	if(!isset($template)){
	            	include(dirname(__FILE__) . '/views/category-map-content-template.php');
	            } else {
	            	include($template);
	            }    
            } else {
            	
            	//this is being done so we can show all places if none are found in the cat
            	if($showIfEmpty && ($cat != $this->placeCategory())){
            		return $this->getCategoryMapMarkup($this->placeCategory(), $template, false, $post_type);           		
            	} else {
            		include(dirname(__FILE__) . '/views/category-map-content-empty.php');
            	}
            		
            }
                  
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
		
		public function addPlaceMetaSave( $postId ) {
			$this->addPlaceMeta( $postId, 'save_post' );	
		}	
		
		public function addPlaceMetaPublish( $postId ) {
			$this->addPlaceMeta( $postId, 'publish_post' );	
		}	
			
		/**
		 * obsolete?
		 */	
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

	    /**
	     * we do this instead of using the shortcode handler because if we didnt
	     * we would have an extra database call on every tag hit
	     */
        public function handlePublish($post_id) {       	
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
            $options = $this->latestOptions;
            
            $changed = false;
		
			$default_marker_icon = plugins_url() . "/welocally-places/resources/images/marker_all_base.png";
			
			$default_show_letters = 'on'; 
			$default_show_selection = 'on'; 		
			$default_infobox_title_link = 'on'; 
			
			$default_widget_selection_style = 'width: 100%'; 
			$default_tag_selection_style = 'margin: 3px; padding: 2px; float: left; width: 150px; height: 60px;'; 
	
			$default_show_letters_tag = 'on'; 
			$default_show_selection_tag = 'on'; 		
			$default_infobox_title_link_tag = 'on'; 
			
			$default_widget_post_type = 'post'; 
						
					
			// Set current version level. Because this can be used to detect version changes (and to what extent), this
			// information may be useful in future upgrades
			if ( $options[ 'current_version' ] != WelocallyPlaces::VERSION ) {
				$options[ 'current_version' ] = WelocallyPlaces::VERSION;
				$changed = true;
			}
			
			//widget
			if ( !array_key_exists( 'show_letters', $options ) ) { $options[ 'show_letters' ] = $default_show_letters; $changed = true; }
			if ( !array_key_exists( 'show_selection', $options ) ) { $options[ 'show_selection' ] = $default_show_selection; $changed = true; }
			if ( !array_key_exists( 'infobox_title_link', $options ) ) { $options[ 'infobox_title_link' ] = $default_infobox_title_link; $changed = true; }
			if ( !array_key_exists( 'widget_post_type', $options ) ) { $options[ 'widget_post_type' ] = $default_widget_post_type; $changed = true; }
					
			//tag
			if ( !array_key_exists( 'show_letters_tag', $options ) ) { $options[ 'show_letters_tag' ] = $default_show_letters_tag; $changed = true; }
			if ( !array_key_exists( 'show_selection_tag', $options ) ) { $options[ 'show_selection_tag' ] = $default_show_selection_tag; $changed = true; }
			if ( !array_key_exists( 'infobox_title_link_tag', $options ) ) { $options[ 'infobox_title_link_tag' ] = $default_infobox_title_link_tag; $changed = true; }
			
			//selectable style overrides
			if ( !array_key_exists( 'widget_selection_style', $options ) ) { $options[ 'widget_selection_style' ] = $default_widget_selection_style; $changed = true; }
			if ( !array_key_exists( 'tag_selection_style', $options ) ) { $options[ 'tag_selection_style' ] = $default_tag_selection_style; $changed = true; }
					
			// Update the options, if changed, and return the result
			if ( $changed ) { $this->saveOptions($options) ; }
                       
            return $options;
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
		public function deletePostPlaces($post_place_id) {
			//print_r($post_place_id);
			
			global $wpdb;
			
			foreach ($post_place_id as $p) {		            
		            $parts = preg_split("/[\s,]+/", $p);
		            $postId = $parts[0];
		            $placeId = $parts[1];
		            
		            /*
		             * select 
						  wp_wl_places_posts.id as wp_wl_places_posts_id,
						  wp_wl_places.id as wp_wl_places_id,
						  wp_wl_places_posts.post_id,
						  wp_wl_places.wl_id 
						from wp_wl_places_posts,wp_wl_places 
						where wp_wl_places.id=wp_wl_places_posts.place_id
						and wp_wl_places_posts.post_id=104
						and wp_wl_places.wl_id='WL_sacf2k1jqe5lsbgr9fsre2_58.299649_-134.407658@1329495792'; 
		             */
		            //if you can figure out how to do this with one quesry pleas
		            //be my guest					
					$query = "SELECT {$wpdb->prefix}wl_places.id place_id, {$wpdb->prefix}wl_places_posts.id places_post_id " .
							" FROM {$wpdb->prefix}wl_places, {$wpdb->prefix}wl_places_posts".
							" WHERE {$wpdb->prefix}wl_places.id={$wpdb->prefix}wl_places_posts.place_id " .
							" AND {$wpdb->prefix}wl_places_posts.post_id=$postId " .
							" AND {$wpdb->prefix}wl_places.wl_id='$placeId'" ;
			
					$return = $wpdb->get_results($query, OBJECT);
					//print_r($return);
					$idsObject = $return[0]; 
					print_r('place_id:'.$idsObject->place_id.' ');
					print_r('places_post_id:'.$idsObject->places_post_id);
							            		        		            
		            /*$wpdb->query(
					"
					DELETE FROM {$wpdb->prefix}wl_places 
					WHERE id = ".$idsObject->place_id."
					"
					);*/	
					
					$wpdb->query(
					"
					DELETE FROM {$wpdb->prefix}wl_places_posts 
					WHERE id = ".$idsObject->places_post_id. "
					"
					);	
					
					
		            
		    }

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
			
			$post_type = explode(",",$post_type);

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
		
		public function generatePlaceId($lat,$lng) {
			$guid = substr(uniqid().uniqid(), 0, 22);
			$parts = explode(" ", microtime());
			$placeId = "WL_".$guid."_".$lat."_".$lng."@".$parts[1];
			return $placeId;
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
