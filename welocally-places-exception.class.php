<?php
/**
 * Exception handling for third-party plugins dealing with the post edit view.
 */
if( !class_exists( 'WLPLACES_Post_Exception' ) ) {
	class WLPLACES_Post_Exception extends Exception {
		/**
		* Display the exception message in the div #wl-post-error
		* @param int $post->ID
		*/
		public function displayMessage( $postId ) {
			if( $error = get_post_meta( $postId, WelocallyPlaces::WLERROROPT, true ) ) : ?>
				<script type="text/javascript">jQuery('#welocally-post-error').append('<h3>Error</h3><p>' + '<?php echo $error; ?>' + '</p>').show();</script>
			<?php endif;
		}
	} // end WLPLACES_Post_Exception
} // end if !class_exists WLPLACES_Post_Exception

/**
 * Exception handling for third-party plugins dealing with the Wordpress options view.
 */
if( !class_exists( 'WLPLACES_Options_Exception' ) ) {
	class WLPLACES_Options_Exception extends Exception {
		/**
		* Display the exception message in the div #welocally-options-error
		*/
		public function displayMessage() {
			$placesOptions = get_option(WelocallyPlaces::OPTIONNAME, array() );
			if( $placesOptions['error'] ) : ?>
				<script type="text/javascript">jQuery('#welocally-options-error').append('<h3>Error</h3><p>' + '<?php echo $placesOptions['error']; ?>' + '</p>').show();</script>
			<?php endif;
	    }
	} // end WLPLACES_Options_Exception
} // end if !class_exists WLPLACES_Options_Exception