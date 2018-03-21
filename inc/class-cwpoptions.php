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

class CwpOptions {

	/**
	 * Add appropriate hooks
	 */
	function __construct() {
		add_action( 'admin_menu', [ $this, 'addAdminMenu' ] );
		add_action( 'admin_init', [ $this, 'settingsInit' ] );
	}

	/**
	 * Add admin menu to dashboard
	 */
	function addAdminMenu() {

		add_options_page(
			'Custom WP Notify',
			'Custom WP Notify',
			'manage_options',
			'custom-wp-notify',
			[ $this, 'optionsPage', ]
		);

	}

	/**
	 * Register the plugin settings, create fields
	 */
	function settingsInit() {
		$page = $options = 'cwp_options';

		register_setting(
			$options,
			'cwp_settings',
			[ $this, 'sanitize' ]
		);

		add_settings_section(
			$options . '_section',
			__( '', 'WordPress' ),
			'',
			$page
		);

		add_settings_field(
			'cwp_enable',
			__( 'Enable Notifications:', 'custom-wp-notify' ),
			[ $this, 'enableRender' ],
			$page,
			$options . '_section'
		);

		add_settings_field(
			'cwp_frequency',
			__( 'Notification Frequency:', 'custom-wp-notify' ),
			[ $this, 'frequencyRender' ],
			$page,
			$options . '_section'
		);

		add_settings_field(
			'cwp_optin',
			__( 'Subscribe text:', 'custom-wp-notify' ),
			[ $this, 'optInTextRender' ],
			$page,
			$options . '_section'
		);

		add_settings_field(
			'cwp_template',
			__( 'Notification Template:', 'custom-wp-notify' ),
			[ $this, 'templateRender' ],
			$page,
			$options . '_section'
		);

		add_settings_field(
			'cwp_unsubscribe',
			__( 'Unsubscribe Link:', 'custom-wp-notify' ),
			[ $this, 'unsubscribeRender' ],
			$page,
			$options . '_section'
		);

	}

	function sanitize( $settings ) {
		$integers  = [ 'cwp_enable' ];
		$text_only = [ 'cwp_notify' ];
		$esc_html  = [ 'cwp_template' ];
		$esc_url   = [ 'cwp_unsubscribe' ];

		// integers
		foreach ( $integers as $int ) {
			$settings[ $int ] = absint( $settings[ $int ] );
		}

		// text
		foreach ( $text_only as $text ) {
			$settings[ $text ] = sanitize_text_field( $settings[ $text ] );
		}

		// esc html
		foreach ( $esc_html as $html ) {
			$settings[ $html ] = esc_html( $settings[ $html ] );
		}

		// esc html
		foreach ( $esc_url as $url ) {
			$settings[ $url ] = esc_url( $settings[ $url ] );
		}

		return $settings;
	}

	/**
	 * Customize the value of the unsubscribe link
	 */
	function unsubscribeRender() {
		$options = get_option( 'cwp_settings' );

		// add default
		if ( ! isset( $options['cwp_unsubscribe'] ) ) {
			$options['cwp_unsubscribe'] = '';
		}

		echo "<input type='text' name='cwp_settings[cwp_unsubscribe]' value='{$options['cwp_unsubscribe']}'>";

	}

	/**
	 * Render the options page enable field
	 */
	function enableRender() {

		$options = get_option( 'cwp_settings' );

		// add default
		if ( ! isset( $options['cwp_enable'] ) ) {
			$options['cwp_enable'] = 0;
		}

		echo "<input type='checkbox' name='cwp_settings[cwp_enable]'" . checked( $options['cwp_enable'], 1, false ) . " value='1'>";
	}

	/**
	 * Render the options page frequency field
	 */
	function frequencyRender() {

		$options = get_option( 'cwp_settings' );
		// add default
		if ( ! isset( $options['cwp_frequency'] ) ) {
			$options['cwp_frequency'] = 1;
		}

		echo "<select name='cwp_settings[cwp_frequency]'>
			<option value='1'" . selected( $options['cwp_frequency'], 1, false ) . ">Daily</option>
			<option value='2'" . selected( $options['cwp_frequency'], 2, false ) . ">Weekly</option>
			<option value='3'" . selected( $options['cwp_frequency'], 3, false ) . ">Monthly</option>
		</select>";

	}

	/**
	 * Render the options page opt-in field
	 */
	function optInTextRender() {

		$options = get_option( 'cwp_settings' );

		// add default
		if ( ! isset( $options['cwp_notify'] ) ) {
			$options['cwp_notify'] = 'Subscribe to Notifications';
		}

		echo "<input type='text' name='cwp_settings[cwp_notify]' value='{$options['cwp_notify']}'>";

	}

	/**
	 * Render the options page template field
	 */
	function templateRender() {

		$options = get_option( 'cwp_settings' );

		// add default
		if ( ! isset( $options['cwp_notify'] ) ) {
			$options['cwp_notify'] = '';
		}

		echo "<textarea cols='60' rows='15' name='cwp_settings[cwp_template]'>{$options['cwp_template']}</textarea>";

	}

	/**
	 * The function to be called to output the content for this page
	 */
	function optionsPage() {

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



