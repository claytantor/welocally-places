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
		
			function widget( $args, $instance ) {
				global $wp_query,$wlPlaces;
				$options = $wlPlaces->getOptions();
				extract( $args );

				/* User-selected settings. */
				$style = $instance['style'];
				$title = apply_filters('widget_title', $instance['title'] );
				$limit = 20;
				
				if( function_exists( 'get_places' ) ) {
					$old_display = $wp_query->get('placeMapDisplay');
					$wp_query->set('placeMapDisplay', 'places');
					$posts = get_places($limit, null, null);
				}
				
				if( $posts ) {
					/* Display list of places. */
						if( function_exists( 'get_places' ) ) {
							$templateLoc = apply_filters('map_widget_template','');
							//view
							include( $templateLoc );
							
							$wp_query->set('placeMapDisplay', $old_display);
						}
					
						
				} else if( !$noUpcomingEvents ) _e('There are no places.', $this->pluginDomain);

				
			}	
		
			function update( $new_instance, $old_instance ) {
					$instance = $old_instance;

					/* Strip tags (if needed) and update the widget settings. */
					$instance['title'] = strip_tags( $new_instance['title'] );
					$instance['style'] = strip_tags( $new_instance['style'] );
					

					return $instance;
			}
		
			function form( $instance ) {
				/* Set up default widget settings. */
				$defaults = array( 'style'=>'aside', 'title' => 'Published Items', 'limit' => '5', 'start' => 'on', 'start-time' => '','end' => '', 'end-time' => '', 'venue' => '', 'country' => 'on', 'address' => '', 'city' => 'on', 'state' => 'on', 'province' => 'on', 'zip' => '', 'phone' => '', 'cost' => '');
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
