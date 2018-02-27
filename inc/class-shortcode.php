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
		if ( is_admin() ) {
			add_action( 'wp_ajax_nopriv_cwpOptIn', [ $this, 'cwpOptInCallback' ] );
			add_action( 'wp_ajax_cwpOptIn', [ $this, 'cwpOptInCallback' ] );
		}
	}

	/**
	 * @param $atts
	 * Contents of the shortcode
	 *
	 * @return string
	 */
	function cwpShortCode( $atts ) {

		// Get prefix text for our checkbox from the plugin options
		$getoptions = get_option( 'cwp_settings' );

		if ( is_user_logged_in() ) {
			// Set default prefix text for the checkbox if none exists
			( $getoptions['cwp_notify'] ) ? $opt_in_text = $getoptions['cwp_notify'] : $opt_in_text = 'Subscribe to Notifications';

			// Build the checkbox with prefix text from options page, and the user value of cwp_notify
			$html = '<div class="cwp-notify">';
			$html .= $opt_in_text . '<input class="notifiable" type="checkbox" name="cwp-opt-in" value="">';
			$html .= '<span class="cwp-loading">' . __( '...', 'cwp_notify' ) . '</span>';
			$html .= '<span class="cwp-message">' . __( 'Saved', 'cwp_notify' ) . '</span>';
			$html .= wp_nonce_field( 'notify_preference', 'submit_notify_preference' );
			$html .= '</div>';


		} else {
			// Not logged in, disable the checkbox with a message

			// Set default prefix text for the disabled checkbox if none exists
			( $getoptions['cwp_disabled'] ) ? $disabled_text = $getoptions['cwp_disabled'] : $disabled_text = 'Log in to subscribe to notifications';

			$html = '<div class="cwp-notify">';
			$html .= $disabled_text . '<input class="notifiable" type="checkbox" name="cwp-opt-in" value="" disabled>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 *  AJAX callback to update/create user meta
	 */
	function cwpOptInCallback() {
		// Get the user ID, and existing value.
		$new_value  = $_POST['new_value'];
		$user_id    = get_current_user_id();
		$user_value = get_user_meta( $user_id, 'cwp_notify', true );

		// The new value shouldn't match the stored value
		if ( $user_value != $new_value ) {
			$response = update_user_meta( $user_id, 'cwp_notify', $new_value );
			// send back the new value
			wp_send_json_success( $response );
		}

	}

	/**
	 * @return string
	 */

	function cwpCheckboxState() {
		if ( is_user_logged_in() ) {
			$user_id    = get_current_user_id();
			$user_value = get_user_meta( $user_id, 'cwp_notify', true );
			( $user_value === 0 ) ? $checked = '0' : $checked = '1';
		} else {
			$checked = '0';
		}

		return $checked;
	}

	/**
	 * Enqueue scripts, styles, and ajax
	 */
	function cwpScripts() {
		wp_enqueue_script( 'cwp-notify', plugin_dir_url( __DIR__ . '..' ) . 'assets/scripts/cwp-notify.js', [ 'jquery' ], null, true );
		wp_enqueue_style( 'cwp-notify', plugin_dir_url( __DIR__ . '..' ) . 'assets/css/style.css' );
		wp_localize_script( 'cwp-notify', 'settings', [
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			'checkstate' => $this->cwpCheckboxState()
		] );
	}
}


