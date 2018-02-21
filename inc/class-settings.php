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


class Settings {

	/**
	 * Add appropriate hooks
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'cwpAddAdminMenu' ) );
		add_action( 'admin_init', array( $this, 'cwpSettingsInit' ) );
		add_shortcode( 'cwp_notify', array( $this, 'cwpShortCode' ) );
	}

	/**
	 * Add admin menu to dashboard
	 */
	function cwpAddAdminMenu() {

		add_options_page( 'Custom WP Notify', 'Custom WP Notify', 'manage_options', 'custom-wp-notify', array(
			$this,
			'cwpOptionsPage'
		) );

	}

	/**
	 * Register the plugin settings, create fields
	 */
	function cwpSettingsInit() {

		register_setting( 'cwp_options', 'cwp_settings' );

		add_settings_section(
			'cwp_pluginPage_section',
			__( '', 'wordpress' ),
			'',
			'cwp_options'
		);

		add_settings_field(
			'cwp_enable',
			__( 'Enable Notifications', 'wordpress' ),
			array( $this, 'cwpEnableRender' ),
			'cwp_options',
			'cwp_pluginPage_section'
		);

		add_settings_field(
			'cwp_frequency',
			__( 'Notification Frequency', 'wordpress' ),
			array( $this, 'cwpFrequencyRender' ),
			'cwp_options',
			'cwp_pluginPage_section'
		);

		add_settings_field(
			'cwp_optin',
			__( 'Subscribe text:', 'wordpress' ),
			array( $this, 'cwpOptInRender' ),
			'cwp_options',
			'cwp_pluginPage_section'
		);

		add_settings_field(
			'cwp_template',
			__( 'Notification Template:', 'wordpress' ),
			array( $this, 'cwpTemplateRender' ),
			'cwp_options',
			'cwp_pluginPage_section'
		);


	}

	/**
	 * Render the options page enable field
	 */
	function cwpEnableRender() {

		$options = get_option( 'cwp_settings' );
		?>
        <input type='checkbox' name='cwp_settings[cwp_enable]' <?php checked( $options['cwp_enable'], 1 ); ?> value='1'>
		<?php

	}

	/**
	 * Render the options page frequency field
	 */
	function cwpFrequencyRender() {

		$options = get_option( 'cwp_settings' );
		?>
        <select name='cwp_settings[cwp_frequency]'>
            <option value='1' <?php selected( $options['cwp_frequency'], 1 ); ?>>Daily</option>
            <option value='2' <?php selected( $options['cwp_frequency'], 2 ); ?>>Weekly</option>
            <option value='3' <?php selected( $options['cwp_frequency'], 3 ); ?>>Monthly</option>
        </select>

		<?php

	}

	/**
	 * Render the options page opt-in field
	 */
	function cwpOptInRender() {

		$options = get_option( 'cwp_settings' );
		?>
        <input type="text" name="cwp_settings[cwp_notify]" value="<?php echo $options['cwp_notify']; ?>">
		<?php

	}

	/**
	 * Render the options page template field
	 */
	function cwpTemplateRender() {

		$options = get_option( 'cwp_settings' );
		?>
        <textarea cols='60' rows='15'
                  name='cwp_settings[cwp_template]'><?php echo $options['cwp_template']; ?></textarea>
		<?php

	}

	/**
	 * The function to be called to output the content for this page
	 */
	function cwpOptionsPage() {

		?>
        <form action='options.php' method='post'>

            <h2>Custom WP Notify</h2>

			<?php
			settings_fields( 'cwp_options' );
			do_settings_sections( 'cwp_options' );
			submit_button();
			?>

        </form>
		<?php

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
}


function cwpOptIn() {

	// Save save opt-in preference on form submission
	if ( isset( $_POST['cwp-opt-in'] ) ) {
		$user_id = get_current_user_id();
		$opt     = $_POST['cwp-opt-in'];

		// Update or Create User Meta
		update_user_meta( $user_id, 'cwp_notify', $opt );
	}
}