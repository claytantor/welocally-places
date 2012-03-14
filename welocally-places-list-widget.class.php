<?php
if( !class_exists( 'WelocallyPlaces_Widget' ) ) {
	/**
	 * Places List Widget
	 *
	 * Creates a widget that displays the next upcoming x places
	 */

	class WelocallyPlaces_Widget extends WP_Widget {
		
		public $pluginDomain = 'welocallyPlaces';
		
		function WelocallyPlaces_Widget() {
				/* Widget settings. */
				$widget_ops = array( 'classname' => 'welocallyPlacesWidget', 'description' => __( 'A widget that shows what items have been published.', $this->pluginDomain) );

				/* Widget control settings. */
				$control_ops = array( 'id_base' => 'welocally-places-widget' );

				/* Create the widget. */
				$this->WP_Widget( 'welocally-places-widget', 'Welocally Places List Widget', $widget_ops, $control_ops );
			}
		
			function widget( $args, $instance ) {
				global $wp_query,$wlPlaces;
				$options = $wlPlaces->getOptions();
				extract( $args );
				/* User-selected settings. */
				$style = $instance['style'];
				$title = apply_filters('widget_title', $instance['title'] );
				
				$limit = 1000;
				$limit_places = $instance['limit'];
				$order_by = $instance['order_by'];
				$order_dir = $instance['order_dir'];
				$exclude_cats = $instance['exclude_cats'];
				
				if( function_exists( 'get_places' ) ) {
					$old_display = $wp_query->get('placeDisplay');
					$wp_query->set('placeDisplay', 'places');
					$posts = get_places($limit, $order_by, $order_dir);
				}
				
				if( $posts ) {
					/* Display list of places. */
						if( function_exists( 'get_places' ) ) {
							$templateLoc = apply_filters('list_widget_template','');
							//view
							include( $templateLoc );
							
							$wp_query->set('placeMapDisplay', $old_display);
						}
					
						
				} else if( !$noUpcomingEvents ) _e('There are no places.', $this->pluginDomain);


				
			}	
			
			
		
			function update( $new_instance, $old_instance ) {
					$instance = $old_instance;

					/* Strip tags (if needed) and update the widget settings. */
					$instance['style'] = strip_tags( $new_instance['style'] );
					$instance['title'] = strip_tags( $new_instance['title'] );
					$instance['limit'] = strip_tags( $new_instance['limit'] );
					$instance['order_by'] = strip_tags( $new_instance['order_by'] );
					$instance['order_dir'] = strip_tags( $new_instance['order_dir'] );	
					$instance['exclude_cats'] = strip_tags( $new_instance['exclude_cats'] );

					return $instance;
			}
		
			function form( $instance ) {
				/* Set up default widget settings. */
				$defaults = array( 'style'=>'aside',  'title' => 'Published Items', 'limit' => '5', 'start' => 'on', 'start-time' => '','end' => '', 'end-time' => '', 'venue' => '', 'country' => 'on', 'address' => '', 'city' => 'on', 'state' => 'on', 'province' => 'on', 'zip' => '', 'phone' => '', 'cost' => '');
				$instance = wp_parse_args( (array) $instance, $defaults );			
				include( dirname( __FILE__ ) . '/views/welocally-places-list-widget-admin.php' );
			}
			
			function get_permalink($postID){
				
			}
			
			function get_categories($postID, $excludeIds, $seperator = NULL){
				if($seperator == NULL)
					$seperator = ',';
				$cats = get_the_category($postID);
				$exclude = explode(',', $excludeIds, 10);
				$result = '';
				
				//remove emlements we want to exclude
				foreach($cats as $category) { 
					if (in_array($category->cat_ID, $exclude)) {
						$cats = $this->remove_item_by_value($cats, $category);
					}
				}
				
				//now just do it with valid elements
				$i=0; 
				$len = count($cats);
				foreach($cats as $category) { 
						$cat_link = get_category_link( $category->cat_ID );
						$result = $result.'<a href="'. $cat_link . '" >' . $category->cat_name . '</a>';
						//seperator
						if ($i != $len - 1) {
								$result = $result.$seperator.' ';
						}		
						$i++;
				}
				return $result;
			}
			
			function remove_item_by_value($array, $val = '', $preserve_keys = true) {
				if (empty($array) || !is_array($array)) return false;
				if (!in_array($val, $array)) return $array;
			
				foreach($array as $key => $value) {
					if ($value == $val) unset($array[$key]);
				}
			
				return ($preserve_keys === true) ? $array : array_values($array);
			}
	}

	/* Add function to the widgets_ hook. */
	add_action( 'widgets_init', 'welocally_places_load_widgets' );

	/* Function that registers widget. */
	function welocally_places_load_widgets() {
		global $pluginDomain;
		register_widget( 'WelocallyPlaces_Widget' );
		// load text domain after class registration
		load_plugin_textdomain( $pluginDomain, false, basename(dirname(__FILE__)) . '/lang/');
	}
}
