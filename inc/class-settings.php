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
		add_action( 'admin_menu', [ $this, 'cwpAddAdminMenu' ] );
		add_action( 'admin_init', [ $this, 'cwpSettingsInit' ] );
		add_shortcode( 'cwp_notify', [ $this, 'cwpShortCode' ] );
	}

	/**
	 * Add admin menu to dashboard
	 */
	function cwpAddAdminMenu() {

		add_options_page( 'Custom WP Notify', 'Custom WP Notify', 'manage_options', 'custom-wp-notify', [
			$this,
			'cwpOptionsPage',
		] );

	}

	/**
	 * Register the plugin settings, create fields
	 */
	function cwpSettingsInit() {

		register_setting( 'cwp_options', 'cwp_settings' );

		add_settings_section(
			'cwp_pluginPage_section',
			__( '', 'WordPress' ),
			'',
			'cwp_options'
		);

		add_settings_field(
			'cwp_enable',
			__( 'Enable Notifications', 'WordPress' ),
			[ $this, 'cwpEnableRender' ],
			'cwp_options',
			'cwp_pluginPage_section'
		);

		add_settings_field(
			'cwp_frequency',
			__( 'Notification Frequency', 'WordPress' ),
			[ $this, 'cwpFrequencyRender' ],
			'cwp_options',
			'cwp_pluginPage_section'
		);

		add_settings_field(
			'cwp_optin',
			__( 'Subscribe text:', 'WordPress' ),
			[ $this, 'cwpOptInRender' ],
			'cwp_options',
			'cwp_pluginPage_section'
		);

		add_settings_field(
			'cwp_template',
			__( 'Notification Template:', 'WordPress' ),
			[ $this, 'cwpTemplateRender' ],
			'cwp_options',
			'cwp_pluginPage_section'
		);
	}

	/**
	 * Render the options page enable field
	 */
	function cwpEnableRender() {

		$options = get_option( 'cwp_settings' );
		?><input
        type='checkbox' name='cwp_settings[cwp_enable]' <?php checked( $options['cwp_enable'], 1 ); ?>
        value='1'><?php
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
}

