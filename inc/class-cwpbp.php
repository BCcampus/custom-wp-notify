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

class CwpBp {

	/**
	 * Add appropriate hooks
	 */
	function __construct() {
		add_action( 'bp_signup_profile_fields', [ $this, 'bpRegister' ] );
		add_action( 'bp_actions', [ $this, 'bpValidate' ], 0 );
		add_filter( 'bp_signup_usermeta', [ $this, 'bpSignUpMeta' ] );
		add_filter( 'bp_core_activated_user', [ $this, 'bpActivated' ], 10, 3 );
	}

	/**
	 * Add opt in to the BP registration page
	 */
	function bpRegister() {
		$options = get_option( 'cwp_settings' );
		$label   = ( ! empty( $options['cwp_notify'] ) ) ? $options['cwp_notify'] : 'Subscribe to Email Notifications';

		?>

		<div id="notify-field">
			<label for="cwp_bp_notify">
				<span class="bp-required-field-label"><?php echo $label ?></span>
			</label>
			<?php do_action( 'bp_notify_errors' ); ?>
			<input type="radio" id="cwp_bp_notify" name="cwp_bp_notify" value="1" checked/> Yes
			<input type="radio" id="cwp_bp_notify" name="cwp_bp_notify" value="0"/> No
		</div>

		<?php
	}

	/**
	 * Validate and sanitize checkbox
	 */
	function bpValidate() {
		if ( isset( $_POST['cwp_bp_notify'] ) ) {

			global $bp, $notify_field_value;

			// check that we are on the registration page
			if ( ! function_exists( 'bp_is_current_component' ) || ! bp_is_current_component( 'register' ) ) {
				return;
			}

			// input can only be 1 or 0
			if ( ! ( $_POST['cwp_bp_notify'] == "1" || $_POST['cwp_bp_notify'] == "0" ) ) {
				if ( ! isset( $bp->signup->errors ) ) {
					$bp->signup->errors = [];
				}
				// error message
				$bp->signup->errors['cwp_bp_notify'] = __( 'Please choose yes or no', 'cwp_notify' );
			} else {
				// input looks good, proceed
				$notify_field_value = sanitize_text_field( $_POST['cwp_bp_notify'] );
			}
		}

		return;
	}

	/**
	 * Create the sign up meta
	 */
	function bpSignUpMeta( $usermeta ) {

		global $notify_field_value;

		return array_merge( [ 'cwp_bp_notify' => $notify_field_value ], $usermeta );
	}


	/**
	 * Hooks into process where user is created
	 */
	function bpActivated( $user_id, $key, $user ) {

		$tag = 'notify';

		if ( $user_id && ! empty( $user['meta'][ $tag ] ) ) {
			return update_user_meta( $user_id, "cwp_{$tag}", $user['meta'][ $tag ] );
		}
	}

}
