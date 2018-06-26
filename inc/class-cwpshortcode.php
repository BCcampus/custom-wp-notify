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

use BCcampus\Models\Em;

class CwpShortcode {

	/**
	 * Add appropriate hooks
	 */
	function __construct() {
		add_shortcode( 'cwp_notify', [ $this, 'userSubscribe' ] );
		add_shortcode( 'cwp_notify_em_cat', [ $this, 'userCategories' ] );
		add_shortcode( 'cwp_notify_em_user_cat', [ $this, 'displayUserCategories' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
		if ( is_admin() ) {
			add_action( 'wp_ajax_nopriv_cwpOptIn', [ $this, 'optInCallback' ] );
			add_action( 'wp_ajax_cwpOptIn', [ $this, 'optInCallback' ] );
			add_action( 'wp_ajax_cwpCategoryPrefs', [ $this, 'userCategoriesCallback' ] );
		}
	}

	/**
	 * Adds user controlled un/subscribe function
	 *
	 * @return string
	 */
	function userSubscribe() {

		// Get prefix text for our checkbox from the plugin options
		$label = get_option( 'cwp_settings' );

		if ( \is_user_logged_in() ) {
			$user_value = get_user_meta( get_current_user_id(), 'cwp_notify', true );

			// Set default prefix text for the checkbox if none exists
			( $label['cwp_notify'] ) ? $opt_in_text = $label['cwp_notify'] : $opt_in_text = 'Subscribe to Notifications';

			// Build the checkbox with prefix text from options page, and the user value of cwp_notify
			$html = '<div class="cwp-notify">';
			$html .= '<label><input class="notifiable" id="cwp-opt-in" type="checkbox" name="cwp-opt-in"' . checked( $user_value, 1, false ) . ' value="1">' . $opt_in_text . '</label>';
			$html .= '<span class="cwp-loading">' . __( '...', 'custom-wp-notify' ) . '</span>';
			$html .= '<span class="cwp-message">' . __( 'Saved', 'custom-wp-notify' ) . '</span>';
			$html .= '<span class="cwp-message-error">' . __( 'Error', 'custom-wp-notify' ) . '</span>';
			$html .= '</div>';

		} else {
			// Not logged in, disable the checkbox with a message

			// Set default prefix text for the disabled checkbox if none exists
			( $label['cwp_disabled'] ) ? $disabled_text = $label['cwp_disabled'] : $disabled_text = 'Log in to subscribe to notifications';

			$html = '<div class="cwp-notify">';
			$html .= '<label><input class="notifiable" type="checkbox" name="cwp-opt-in" value="" disabled>' . $disabled_text . '</label>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Adds user controlled preferences for event categories
	 *
	 * @return string
	 */
	function userCategories() {
		$em   = new Em\Events();
		$html = '';
		if ( \is_user_logged_in() ) {
			$user_prefs = get_user_meta( get_current_user_id(), 'cwp_notify_categories', true );
			$cats       = $em->getEventCategories();

			if ( ! empty( $cats ) ) {
				$html = '<fieldset>';
				$html .= '<form><div class="checkbox cwp-notify-categories">';
				$html .= '<label class="checkbox" for="select_all"><input class="notifiable-categories" type="checkbox" id="select_all">Select All</label>';
				foreach ( $cats as $category ) {
					// set state of checkbox only if user preference exists
					$checked = ( is_array( $user_prefs ) && in_array( $category['term_id'], $user_prefs ) ) ? 1 : 0;
					$html    .= "<label class='checkbox' for='{$category['term_id']}'>";
					$html    .= "<input class='notifiable-categories' type='checkbox' name='cwp_notify_categories[]' id='{$category['term_id']}'" . checked( $checked, 1, false ) . " value='{$category['term_id']}'>";
					$html    .= "{$category['name']}</label>";
				}
				$html .= '<br><button class="notifiable-categories" type="submit">Save</button>';
				$html .= '<span class="cwp-cat-loading">' . __( ' ...', 'custom-wp-notify' ) . '</span>';
				$html .= '<span class="cwp-cat-message">' . __( ' Saved', 'custom-wp-notify' ) . '</span>';
				$html .= '<span class="cwp-cat-message-error">' . __( ' Error', 'custom-wp-notify' ) . '</span>';
				$html .= '</div></form></fieldset>';
			}
		}

		return $html;
	}

	/**
	 * callback to set user meta
	 */
	function userCategoriesCallback() {
		// Check for nonce security
		$nonce      = $_POST['nonce'];
		$user_prefs = [];

		if ( ! wp_verify_nonce( $nonce, 'cwp_cat_nonce' ) ) {
			wp_send_json_error();
		} else {
			// Get the user ID, and existing value.
			$user_id = get_current_user_id();
			if ( ! empty( $_POST['categories'] ) ) {
				$user_prefs = array_values( $_POST['categories'] );
			}

			// The new value shouldn't match the stored value
			$response = update_user_meta( $user_id, 'cwp_notify_categories', $user_prefs );
			// send back the new value
			wp_send_json_success( $response );
		}
	}

	/**
	 *
	 */
	function displayUserCategories() {
		$em          = new Em\Events();
		$default_msg = 'Currently there are no upcoming events in the categories selected.';
		$html        = '';

		if ( \is_user_logged_in() ) {
			$user_prefs = get_user_meta( get_current_user_id(), 'cwp_notify_categories', true );
			if ( is_array( $user_prefs ) ) {

				foreach ( $user_prefs as $term_id ) {
					$title  = $em->getCategoryName( $term_id );
					$events = $em->getRecentEventsByCategory( $term_id );

					if ( ! empty( $title ) && ! empty( $events ) ) {
						$titles_and_links = $em->getTitlesAndLinks( $this->cleanRecentEvents( $events ) );
						$html             .= "<h2>{$title[0]['name']}</h2>";
						$html             .= '<table class="events-table"><thead><tr><th>Date</th><th>Event Description</th><th>Certificate Hours</th><th>Cost</th><th>Registration Link</th></tr></thead><tbody>';
						foreach ( $titles_and_links as $id => $event ) {
							$html             .= '<tr>';
							$event_details    = $em->getEvent( $id );
							$event_attributes = get_post_meta( $id );
							$hours            = ( isset( $event_attributes['Professional Development Certificate Credit Hours'] ) ) ? $event_attributes['Professional Development Certificate Credit Hours'][0] : '';
							$fee              = ( isset( $event_attributes['Registration Fee'] ) ) ? $event_attributes['Registration Fee'][0] : '';
							$link             = ( isset( $event_attributes['Registration Link'] ) ) ? $event_attributes['Registration Link'][0] : '';

							$html .= "<td>{$event_details[0]['event_start_date']}</td>";
							$html .= "<td><a href='{$event['link']}'>{$event['title']}</a></br>{$event_details[0]['location_name']}<br>{$event_details[0]['location_town']}, {$event_details[0]['location_state']}</td>";
							$html .= "<td>{$hours}</td>";
							$html .= "<td>{$fee}</td>";
							$html .= "<td><a href='{$link}' target='_blank'>Registration Link</a></td>";
							$html .= '</tr>';
						}
					}
					$html .= '</tbody></table>';
				}
			}
			if ( empty( $html ) ) {
				$html = $default_msg;
			}
		}

		return $html;
	}

	/**
	 * @param array $events
	 *
	 * @return array
	 */
	private function cleanRecentEvents( array $events ) {
		$clean = [];

		foreach ( $events as $event ) {
			$clean[]['post_id'] = $event['ID'];
		}

		return $clean;
	}

	/**
	 *  AJAX callback to update/create user meta
	 */
	function optInCallback() {

		// Check for nonce security
		$nonce = $_POST['security'];
		if ( ! wp_verify_nonce( $nonce, 'cwp_nonce' ) ) {
			wp_send_json_error();
		} else {

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
	}

	/**
	 * Enqueue scripts, styles, and ajax
	 */
	function scripts() {
		wp_enqueue_script( 'cwp-notify', plugin_dir_url( __DIR__ . '..' ) . 'assets/scripts/cwp-notify.js', [ 'jquery' ], null, true );
		wp_enqueue_script( 'cwp-notify-categories', plugin_dir_url( __DIR__ . '..' ) . 'assets/scripts/cwp-notify-categories.js', [ 'jquery' ], null, true );
		wp_enqueue_style( 'cwp-notify', plugin_dir_url( __DIR__ . '..' ) . 'assets/css/style.css' );
		wp_localize_script(
			'cwp-notify', 'settings', [
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'cwp_nonce' ),
			]
		);
		wp_localize_script(
			'cwp-notify-categories', 'category_settings', [
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'cwp_cat_nonce' ),
			]
		);
	}
}


