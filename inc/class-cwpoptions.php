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
	CONST PAGE = 'custom-wp-notify';

	/**
	 * Add appropriate hooks
	 */
	function __construct() {
		add_action( 'admin_menu', [ $this, 'addAdminMenu' ] );
		add_action( 'admin_init', [ $this, 'settingsInit' ] );
		add_action( 'admin_init', [ $this, 'settingsUat' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'codeMirror' ] );
	}

	/**
	 * add html and css syntax highlighting
	 */
	function codeMirror() {
		// Code Mirror
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === $this::PAGE ) {

			wp_enqueue_script( 'wp-codemirror' );
			wp_enqueue_script( 'htmlhint' );
			wp_enqueue_script( 'csslint' );
			wp_enqueue_style( 'wp-codemirror' );
		}

	}

	/**
	 * Add admin menu to dashboard
	 */
	function addAdminMenu() {

		add_options_page(
			'Custom WP Notify',
			'Custom WP Notify',
			'manage_options',
			$this::PAGE,
			[ $this, 'optionsPage', ]
		);

	}

	/*
	|--------------------------------------------------------------------------
	| UAT Settings
	|--------------------------------------------------------------------------
	|
	| Testing the output
	|
	|
	*/
	/**
	 *
	 */
	function settingsUat() {
		$page = $options = 'cwp_uat_settings';

		register_setting(
			$options,
			$options,
			[ $this, 'sanitizeUat' ]
		);

		add_settings_section(
			$options . '_section',
			__( 'User Acceptance', 'custom-wp-notify' ),
			'',
			$page
		);

		add_settings_field(
			'test_send',
			__( 'Send Test Email:', 'custom-wp-notify' ),
			[ $this, 'testSend' ],
			$page,
			$options . '_section'
		);
	}

	function sanitizeUat( $settings ) {
		// TODO: sanitize for valid email
		return $settings;
	}

	function testSend() {
		$options = get_option( 'cwp_uat_settings' );

		// add default
		if ( ! isset( $options['test_send'] ) ) {
			$options['test_send'] = '';
		}

		echo "<input type='text' name='cwp_uat_settings[test_send]' value='{$options['test_send']}'>";

	}

	/*
	|--------------------------------------------------------------------------
	| General Settings
	|--------------------------------------------------------------------------
	|
	| Enable/Disable plus Template
	| Nothing happens without this section
	|
	|
	*/
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
			__( 'General Template Settings', 'custom-wp-notify' ),
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

		add_settings_field(
			'cwp_css',
			__( 'Custom CSS:', 'custom-wp-notify' ),
			[ $this, 'cssRender' ],
			$page,
			$options . '_section'
		);

	}

	/**
	 * @param $settings
	 *
	 * @return mixed
	 */
	function sanitize( $settings ) {
		$integers  = [ 'cwp_enable' ];
		$text_only = [ 'cwp_notify' ];
		$esc_html  = [ 'cwp_template', 'cwp_css' ];
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
	 * Custom CSS
	 */
	function cssRender() {
		$options = get_option( 'cwp_settings' );

		// add default
		if ( ! isset( $options['cwp_css'] ) ) {
			$options['cwp_css'] = '#emailContainer{}';
		}

		echo "<textarea id='cwp_css' cols='60' rows='15' name='cwp_settings[cwp_css]'>{$options['cwp_css']}</textarea>";

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

		echo "<textarea id='cwp_template' cols='60' rows='15' name='cwp_settings[cwp_template]'>{$options['cwp_template']}</textarea>";

	}

	/**
	 * The function to be called to output the content for this page
	 */
	function optionsPage() {
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'template';
		?>

		<h2>Custom WP Notify</h2>
		<div id="icon-options-general" class="icon32"></div>
		<h2 class="nav-tab-wrapper">
			<a href="?page=custom-wp-notify&tab=template"
			   class="nav-tab <?php echo $active_tab == 'template' ? 'nav-tab-active' : ''; ?>">Template</a>
			<a href="?page=custom-wp-notify&tab=testing"
			   class="nav-tab <?php echo $active_tab == 'testing' ? 'nav-tab-active' : ''; ?>">Testing</a>
			<a href="?page=custom-wp-notify&tab=user"
			   class="nav-tab <?php echo $active_tab == 'user' ? 'nav-tab-active' : ''; ?>">Subscription Management</a>
			<a href="?page=custom-wp-notify&tab=logs"
			   class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">Logs</a>
		</h2>

		<form action="options.php" method="post">
		<?php

		switch ( $active_tab ) {

			case 'template':

				settings_fields( 'cwp_options' );
				do_settings_sections( 'cwp_options' );

				break;
			case 'testing':

				settings_fields( 'cwp_uat_settings' );
				do_settings_sections( 'cwp_uat_settings' );

				break;
			case 'user':

				settings_fields( 'cwp_user_settings' );
				do_settings_sections( 'cwp_user_settings' );

				break;

			case 'logs':

				settings_fields( 'cwp_log_settings' );
				do_settings_sections( 'cwp_log_settings' );
		}

		submit_button();

		echo "</form>";
		?>
		<script>
			(function ($, wp) {
				var e1 = wp.CodeMirror.fromTextArea(document.getElementById('cwp_template'), {
					lineNumbers: true,
					matchBrackets: true,
					mode: 'text/html'
				});
				var e2 = wp.CodeMirror.fromTextArea(document.getElementById('cwp_css'), {
					lineNumbers: true,
					matchBrackets: true,
					mode: 'text/css'
				});
			})(window.jQuery, window.wp);
		</script>
		<?php
	}

}



