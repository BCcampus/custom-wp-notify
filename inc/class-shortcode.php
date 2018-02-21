<?php
/**
 * Project: custom-wp-notify
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Date: 2018-02-19
 * Licensed under GPLv3, or any later version
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace BCcampus;


class Shortcode {

	/**
	 * Add appropriate hooks
	 */
	function __construct() {
		add_shortcode( 'cwp_notify', array( $this, 'cwpShortCode' ) );
	}

	/**
	 * @param $atts
	 * Contents of the shortcode
	 *
	 * @return string
	 */
	function cwpShortCode( $atts ) {

		// Get the users stored preference
		$user_id = get_current_user_id();
		$pref    = get_user_meta( $user_id, 'cwp_notify', true );
		// Get the value, set to 0 as default
		( $pref ) ? $prefvalue = $pref : $prefvalue = 0;

		// Get prefix text for checkbox from the plugin options
		$getoptions = get_option( 'cwp_settings' );
		// Set default prefix text for the checkbox if none exists
		( $getoptions['cwp_notify'] ) ? $usertext = $getoptions['cwp_notify'] : $usertext = "Subscribe to Notifications";

		// The checkbox with prefix text from options page, and the user value of cwp_notify
		$html = $usertext . '<input type="checkbox" name="cwp-opt-in" value="' . $prefvalue . '">';

		return $html;
	}


	/**
	 *  Update/Create user meta cwp_notify
	 */
	function cwpOptIn() {

		// Save save opt-in preference on form submission
		if ( isset( $_POST['cwp-opt-in'] ) ) {
			$user_id = get_current_user_id();
			$opt     = $_POST['cwp-opt-in'];

			update_user_meta( $user_id, 'cwp_notify', $opt );
		}
	}

}