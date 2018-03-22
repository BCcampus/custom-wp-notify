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

use BCcampus;
use BCcampus\Models\Wp;
use BCcampus\Processors;

class CwpOptions {
	/**
	 *
	 */
	CONST PAGE = 'custom-wp-notify';

	/**
	 * Add appropriate hooks
	 */
	function __construct() {
		add_action( 'admin_menu', [ $this, 'addAdminMenu' ] );
		add_action( 'admin_init', [ $this, 'settingsInit' ] );
		add_action( 'admin_init', [ $this, 'settingsUat' ] );
		add_action( 'admin_init', [ $this, 'settingsLogs' ] );
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
			[ $this, 'optionsPage' ]
		);

	}

	/*
	|--------------------------------------------------------------------------
	| Log Settings
	|--------------------------------------------------------------------------
	|
	|
	|
	|
	*/

	/**
	 *
	 */
	function settingsLogs() {
		$page = $options = 'cwp_log_settings';

		register_setting(
			$options,
			$options
		);

		add_settings_section(
			$options . '_section',
			__( 'Logs', 'custom-wp-notify' ),
			[ $this, 'cronLogs' ],
			$page
		);

	}

	/**
	 *
	 */
	function cronLogs() {
		$options       = get_option( 'cwp_queue' );
		$last_build    = date( 'F d, Y g:i A (T)', $options['created_at'] );
		$remaining     = count( $options['list'] );
		$attempts      = $options['attempts'];
		$recent_events = count( $options['payload'] );
		$timestamp     = wp_next_scheduled( 'cwp_cron_build_hook' );
		if ( ! empty ( $timestamp ) ) {
			$next = date( 'F d, Y g:i A (T)', $timestamp );
		} else {
			$next = 'none scheduled';
		}

		$html = '<table>';
		$html .= '<tr><td><b>Last build:</b></td><td>' . $last_build . '</td></tr>';
		$html .= '<tr><td><b>Next scheduled:</b></td><td>' . $next . '</td></tr>';
		$html .= '<tr><td><b>Remaining notifications:</b></td><td>' . $remaining . '</td></tr>';
		$html .= '<tr><td><b>Number of attempts (20 emails at a time):</b></td><td>' . $attempts . '</td></tr>';
		$html .= '<tr><td><b>Number of published events:</b></td><td>' . $recent_events . '</td></tr>';
		$html .= '</table>';

		echo $html;
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
	 * User Acceptance Testing Settings
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

	/**
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	function sanitizeUat( $settings ) {
		$email       = [ 'test_send' ];
		$success_msg = 'Email sent. Please check your inbox';

		foreach ( $settings[ $email ] as $valid ) {
			$settings[ $valid ] = is_email( $valid );
		}

		if ( false === $settings['test_send'] || empty( $settings['test_send'] ) ) {
			add_settings_error(
				'cwp_uat_settings',
				'settings_uat_updated',
				'Email field is not a valid email address',
				'error'
			);
		} else {
			$u = new Wp\Users();
			$q = new Processors\Queue( $u );
			$m = new Processors\Mail( $q );
			$m->runJustOne( $settings['test_send'] );

			add_settings_error(
				'cwp_uat_settings',
				'settings_uat_updated',
				$success_msg,
				'updated'
			);
		}

		return $settings;
	}

	/**
	 *
	 */
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
		$enum      = [ 'daily', 'cwp_weekly' ];
		$options   = get_option( 'cwp_settings' );


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

		// esc url
		foreach ( $esc_url as $url ) {
			$settings[ $url ] = esc_url( $settings[ $url ] );
		}

		if ( empty( $settings['cwp_unsubscribe'] ) || false === wp_http_validate_url( $settings['cwp_unsubscribe'] ) ) {
			add_settings_error(
				'cwp_options',
				'settings_updated',
				'Please enter a valid url in UNSUBSCRIBE LINK below where people can unsubscribe.',
				'error'
			);
		}

		// enumeration
		if ( ! in_array( $settings['cwp_frequency'], $enum ) ) {
			unset ( $settings['cwp_frequency'] );

			add_settings_error(
				'cwp_options',
				'settings_frequency_updated',
				'Could not find that Notification Frequency',
				'error'
			);
		} else {
			// check for a change in the stored value
			if ( 0 !== strcmp( $_POST['cwp_settings']['cwp_frequency'], $options['cwp_frequency'] ) ) {
				BCcampus\Cron::getInstance()->unScheduleEvents( 'cwp_cron_build_hook' );
				BCcampus\Cron::getInstance()->scheduleEventCustomInterval( 'cwp_cron_build_hook', $_POST['cwp_settings']['cwp_frequency'] );
			}
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
			$options['cwp_frequency'] = 'weekly';
		}

		echo "<select name='cwp_settings[cwp_frequency]'>
			<option value='daily'" . selected( $options['cwp_frequency'], 'daily', false ) . ">Daily</option>
			<option value='cwp_weekly'" . selected( $options['cwp_frequency'], 'cwp_weekly', false ) . ">Weekly</option>
		</select>";

		// display next build time
		$timestamp = wp_next_scheduled( 'cwp_cron_build_hook' );
		if ( ! empty ( $timestamp ) ) {
			echo "<p>next scheduled build: " . date( 'F d, Y g:i A (T)', $timestamp ) . "</p>";
		}
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



