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
		add_action( 'wp_ajax_nopriv_cwpOptIn', [ $this, 'cwpOptIn' ] );
		add_action( 'wp_ajax_cwpOptIn', [ $this, 'cwpOptIn' ] );
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
		( $getoptions['cwp_notify'] ) ? $usertext = $getoptions['cwp_notify'] : $usertext = 'Subscribe to Notifications';

		// The checkbox with prefix text from options page, and the user value of cwp_notify
		$html = '<div class="cwp-notify">';
		$html .= $usertext . '<input class="notifiable" type="checkbox" name="cwp-opt-in" value="' . $prefvalue . '">';
		$html .= '<span class="cwp-loading">' . __( '...', 'cwp_notify' ) . '</span>';
		$html .= '<span class="cwp-message">' . __( 'Saved', 'cwp_notify' ) . '</span>';
		$html .= wp_nonce_field( 'notify_preference', 'submit_notify_preference' );
		$html .= '</div>';

		return $html;
	}

	/**
	 *  AJAX callback to update/create user meta
	 */
	function cwpOptIn() {

		// Check nonce is valid and save opt-in preference if user is logged in
		if ( is_user_logged_in() && isset( $_POST['cwp-opt-in'] ) && check_admin_referer( 'notify_preference', 'submit_notify_preference' ) ) {
			$user_id = get_current_user_id();
			$opt     = $_POST['cwp-opt-in'];
			// create or update meta
			update_user_meta( $user_id, 'cwp_notify', $opt );
			wp_send_json_success( __( 'Thanks for subscribing!', 'cwpOptIn' ) );
		} else {
			wp_send_json_success( __( 'Please Log in or Register to use this feature.', 'cwpOptIn' ) );
		}

		return;
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


