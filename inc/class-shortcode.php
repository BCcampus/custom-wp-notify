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
		add_shortcode( 'cwp_notify', [ $this, 'cwpShortCode' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'cwpScripts' ] );
		add_action( 'wp_ajax_nopriv_cwpOptIn', [ $this, 'cwpOptInCallback' ] );
		add_action( 'wp_ajax_cwpOptIn', [ $this, 'cwpOptInCallback' ] );
	}

	/**
	 * @param $atts
	 * Contents of the shortcode
	 *
	 * @return string
	 */
	function cwpShortCode( $atts ) {

		// Get prefix text for checkbox from the plugin options
		$getoptions = get_option( 'cwp_settings' );
		// Set default prefix text for the checkbox if none exists
		( $getoptions['cwp_notify'] ) ? $usertext = $getoptions['cwp_notify'] : $usertext = 'Subscribe to Notifications';

		// The checkbox with prefix text from options page, and the user value of cwp_notify
		$html = '<div class="cwp-notify">';
		$html .= $usertext . '<input class="notifiable" type="checkbox" name="cwp-opt-in" value="' . $this->cwpUserOpted('') . '">';
		$html .= '<span class="cwp-loading">' . __( '...', 'cwp_notify' ) . '</span>';
		$html .= '<span class="cwp-message">' . __( 'Saved', 'cwp_notify' ) . '</span>';
		$html .= wp_nonce_field( 'notify_preference', 'submit_notify_preference' );
		$html .= '</div>';

		return $html;
	}

	/**
	 *  AJAX callback to update/create user meta
	 */
	function cwpOptInCallback() {
		$new_preference = $_POST['cwp-opt-in'];
		$this->cwpUserOpted( $new_preference );

		wp_send_json_success( __( 'Success', 'cwpOptIn' ) );

	}

	/**
	 * @param $preference
	 * Gets and sets the value of cwp_notify meta for logged in users
	 * Sets the default to 0
	 * @return mixed|string
	 */
	function cwpUserOpted( $preference ) {
		// If it's not a new preference, get the one from the user meta
		if ( is_user_logged_in() && $preference == '') {
			// Get the users stored preference
			$user_id = get_current_user_id();
			$pref    = get_user_meta( $user_id, 'cwp_notify', true );

			// Get the value, set to 0 as default if none existed
			( $pref ) ? $prefvalue = $pref : $prefvalue = '0';

			// create or update meta
			update_user_meta( $user_id, 'cwp_notify', $prefvalue );

			return $prefvalue;

			// If there's a new preference provided, set that as user meta
		} else if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'cwp_notify', $preference );
			return $preference;
			// if they aren't logged in, return 0
		} else {
			$prefvalue = '0';

			return $prefvalue;
		}
	}

	/**
	 * Enqueue scripts, styles, and ajax
	 */
	function cwpScripts() {
		wp_enqueue_script( 'cwp-notify', plugin_dir_url( __DIR__ . '..' ) . 'assets/scripts/cwp-notify.js', [ 'jquery' ], null, true );
		wp_enqueue_style( 'cwp-notify', plugin_dir_url( __DIR__ . '..' ) . 'assets/css/style.css' );
		wp_localize_script( 'cwp-notify', 'settings', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
	}
}


