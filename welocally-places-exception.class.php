<?php
 /*
	Copyright 2012 clay graham, welocally & RateCred Inc.

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
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