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
		add_action( 'wp_ajax_nopriv_save_notification', [ $this, 'cwpOptIn' ] );
		add_action( 'wp_ajax_save_notification', [ $this, 'cwpOptIn' ] );
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

		// Compatible AJAX request for WordPress
		$url = admin_url( 'admin-ajax.php' );

		// The checkbox with prefix text from options page, and the user value of cwp_notify
		$html = '<form class="cwp-notify" method="post" action="' . $url . '">';
		$html .= $usertext . '<input class="notify" type="checkbox" name="cwp-opt-in" value="' . $prefvalue . '">';
		$html .= '<p class="cwp-notify-response"></p>';
		$html .= '<input type="hidden" name="action" value="custom_action">';
		$html .= wp_nonce_field( 'notify_preference', 'submit_notify_preference' );
		$html .= '<button>Send</button>';

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

			wp_send_json_success( __( 'Thanks for subscribing!', 'reportabug' ) );

		} else {

			wp_send_json_success( __( 'Please Log in or Register to use this feature.', 'reportabug' ) );
		}

		return;
	}

	/**
	 * Enqueue scripts and styles
	 */
	function cwpScripts() {
		wp_enqueue_script( 'cwp-notify', plugin_dir_url( __FILE__ ) . 'assets/scripts/cwp-notify.js', [ 'jquery' ], null, true );
		wp_enqueue_style( 'cwp-notify', plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
		wp_localize_script( 'cwp-notify', 'settings', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
	}
}


