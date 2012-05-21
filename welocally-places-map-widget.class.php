<?php
if( !class_exists( 'WelocallyPlacesMap_Widget' ) ) {
	/**
	 * PlacesMap List Widget
	 *
	 * Creates a widget that displays the next upcoming x places
	 */

	class WelocallyPlacesMap_Widget extends WP_Widget {
		
		public $pluginDomain = 'welocallyPlacesMap';
		
		function WelocallyPlacesMap_Widget() {
				/* Widget settings. */
				$widget_ops = array( 'classname' => 'welocallyPlacesMapWidget', 'description' 
					=> __( 'A map widget that shows what items have been published.', 
						$this->pluginDomain) );

				/* Widget control settings. */
				$control_ops = array( 'id_base' => 'welocally-places-map-widget' );

				/* Create the widget. */
				$this->WP_Widget( 'welocally-places-map-widget', 'Welocally Places Map Widget', 
					$widget_ops, $control_ops );
			}
		
		
			/**
			 * needs to be refactored
			 * 
			 */
			function widget( $args, $instance ) {
				
				global $wlPlaces;
				$options = $wlPlaces->getOptions();
				extract( $args );

	
				$post_type = $options['widget_post_type'];					
				
				$cat = $wlPlaces->placeCategory();
				
				$cat_var = get_query_var('cat');
				
				
				if (!empty($cat_var) && !(is_home() || is_front_page())) {			
					$cat = get_category(intval($cat_var));
				}					
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
					
				
				if(!isset($post_type))
					$post_type = 'post';
	
				//add support for custom post type
				echo $wlPlaces->getCategoryMapMarkup(
					$cat, 
					dirname( __FILE__ ).'/views/welocally-places-map-widget-display.php',
					true, 
					25, 
					$post_type);	
				
			}	
		
			function update( $new_instance, $old_instance ) {
					$instance = $old_instance;					
					return $instance;
			}
		
			function form( $instance ) {
				/* Set up default widget settings. */
				$defaults = array( 'style'=>'aside', 'title' => 'Published Places', 'limit' => '25', 'post_type'=>'post');
				$instance = wp_parse_args( (array) $instance, $defaults );			
				include( dirname( __FILE__ ) . '/views/welocally-places-map-widget-admin.php' );
			}
			
			
	}

	/* Add function to the widgets_ hook. */
	add_action( 'widgets_init', 'welocally_places_map_load_widgets' );

	/* Function that registers widget. */
	function welocally_places_map_load_widgets() {
		global $pluginDomain;
		register_widget( 'WelocallyPlacesMap_Widget' );
		// load text domain after class registration
		load_plugin_textdomain( $pluginDomain, false, basename(dirname(__FILE__)) . '/lang/');
	}
}
