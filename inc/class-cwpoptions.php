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
	const PAGE = 'custom-wp-notify';

	/**
	 * Add appropriate hooks
	 */
	function __construct() {
		add_action( 'admin_menu', [ $this, 'addAdminMenu' ] );
		add_action( 'admin_init', [ $this, 'settingsInit' ] );
		add_action( 'admin_init', [ $this, 'settingsUat' ] );
		add_action( 'admin_init', [ $this, 'settingsLogs' ] );
		add_action( 'admin_init', [ $this, 'settingsManage' ] );
		add_action( 'admin_init', [ $this, 'settingsTemplate' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'adminScripts' ] );
	}

	/**
	 * add html and css syntax highlighting
	 */
	function adminScripts() {

		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === $this::PAGE ) { //@codingStandardsIgnoreLine
			// Code Mirror
			if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] === 'template' ) { //@codingStandardsIgnoreLine
				wp_enqueue_script( 'wp-codemirror' );
				wp_enqueue_script( 'htmlhint' );
				wp_enqueue_script( 'csslint' );
				wp_enqueue_style( 'wp-codemirror' );
				wp_enqueue_script( 'cwp-codemirror-script', plugin_dir_url( __FILE__ ) . '../assets/scripts/cwp-codemirror.js', [ 'jquery' ], null, true );
			}
			if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] === 'manage-users' ) { //@codingStandardsIgnoreLine
				wp_enqueue_script( 'cwp-multi-select', 'https://cdn.jsdelivr.net/npm/multiselect-two-sides@2.5.5/dist/js/multiselect.min.js', [ 'jquery' ], null, true );
				wp_enqueue_script( 'cwp-multi-select-script', plugin_dir_url( __FILE__ ) . '../assets/scripts/cwp-multiselect.js', [ 'jquery' ], null, true );
				wp_enqueue_style( 'bootstrap3', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', '', null, 'screen' );
			}
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
	| Manage User Config
	|--------------------------------------------------------------------------
	|
	| Interface for selecting subscription options
	|
	|
	*/
	/**
	 *
	 */
	function settingsManage() {
		$page    = 'cwp_manage_users';
		$options = 'cwp_manage_users';

		register_setting(
			$options,
			$options,
			[ $this, 'sanitizeUsers' ]
		);

		add_settings_section(
			$options . '_section',
			__( 'Manage Users', 'custom-wp-notify' ),
			[ $this, 'manageUsers' ],
			$page
		);

	}

	/**
	 */
	function sanitizeUsers() {

		if ( isset( $_POST['no'] ) && false !== wp_verify_nonce( $_POST['cwp-options-update-field'], 'cwp-options-update-action' ) ) {
			foreach ( $_POST['no'] as $username ) {
				// Get the user object by login name
				$userobject = get_user_by( 'login', $username );
				// Get the user ID
				$user_id = $userobject->ID;
				// Get the existing preference if any
				$user_value = get_user_meta( $user_id, 'cwp_notify', true );
				// Update their preference only if it's different
				if ( $user_value !== '0' ) {
					update_user_meta( $user_id, 'cwp_notify', '0' );
				}
			}
		}

		if ( isset( $_POST['yes'] ) && false !== wp_verify_nonce( $_POST['cwp-options-update-field'], 'cwp-options-update-action' ) ) {
			foreach ( $_POST['yes'] as $username ) {
				// Get the user object by login name
				$userobject = get_user_by( 'login', $username );
				// Get the user ID
				$user_id = $userobject->ID;
				// Get the existing preference if any
				$user_value = get_user_meta( $user_id, 'cwp_notify', true );
				// Update their preference only if it's different
				if ( $user_value !== '1' ) {
					update_user_meta( $user_id, 'cwp_notify', '1' );
				}
			}
		}
	}

	/**
	 *
	 */
	function manageUsers() {

		$all_users      = get_users();
		$subscribed     = [];
		$not_subscribed = [];

		foreach ( $all_users as $user ) {

			// skip over spam users, or those not yet registered
			if ( '0' !== $user->data->user_status ) {
				continue;
			}

			$preference = get_user_meta( $user->ID, 'cwp_notify', true );
			if ( $preference === '1' ) {
				$subscribed[] = [
					'email' => $user->user_email,
					'login' => $user->user_login,
				];
			} else {
				$not_subscribed[] = [
					'email' => $user->user_email,
					'login' => $user->user_login,
				];
			}
		}

		$html  = "<div class='row'><div class='col-xs-5'>";
		$html .= '<h5>Subscribed</h5>';
		$html .= "<select name='yes[]' id='multiselect' class='form-control' size='8' multiple='multiple'>";
		foreach ( $subscribed as $user ) {
			$html .= "<option value='{$user['login']}'>{$user['email']}[{$user['login']}]</option>";
		}
		$html .= '</select></div>';
		$html .= "<div class='col-xs-2'>";
		$html .= "<button type='button' id='multiselect_rightAll' class='btn btn-block'><i class='glyphicon glyphicon-forward'></i></button>";
		$html .= "<button type='button' id='multiselect_rightSelected' class='btn btn-block'><i class='glyphicon glyphicon-chevron-right'></i></button>";
		$html .= "<button type='button' id='multiselect_leftSelected' class='btn btn-block'><i class='glyphicon glyphicon-chevron-left'></i></button>";
		$html .= "<button type='button' id='multiselect_leftAll' class='btn btn-block'><i class='glyphicon glyphicon-backward'></i></button>";
		$html .= "</div><div class='col-xs-5'>";
		$html .= '<h5>Not Subscribed</h5>';
		$html .= "<select name='no[]' id='multiselect_to' class='form-control' size='8' multiple='multiple'>";
		foreach ( $not_subscribed as $user ) {
			$html .= "<option value='{$user['login']}'>{$user['email']}[{$user['login']}]</option>";
		}
		$html .= '</select></div></div>';

		echo $html;

	}
	/*
	|--------------------------------------------------------------------------
	| Log Settings
	|--------------------------------------------------------------------------
	|
	| Admin interface for viewing log files
	|
	|
	*/

	/**
	 *
	 */
	function settingsLogs() {
		$page    = 'cwp_log_settings';
		$options = 'cwp_log_settings';

		register_setting(
			$options,
			$options,
			[ $this, 'sanitizeCron' ]
		);

		add_settings_section(
			$options . '_section',
			__( 'Logs', 'custom-wp-notify' ),
			[ $this, 'cronLogs' ],
			$page
		);

	}

	/**
	 * removes email addresses from an active queue
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	function sanitizeCron( $settings ) {

		if ( ! empty( $_POST['cwp_remove'] ) && wp_verify_nonce( $_POST['cwp-options-remove-emails-field'], 'cwp-options-remove-action' ) ) {
			$options = get_option( 'cwp_queue' );

			foreach ( $_POST['cwp_remove'] as $email => $value ) {
				unset( $options['list'][ $email ] );
			}
			update_option( 'cwp_queue', $options );

		}

		return $settings;
	}

	/**
	 *
	 */
	function cronLogs() {
		$options       = get_option( 'cwp_queue' );
		$last_build    = date( 'l, F d, Y g:i A', $options['created_at'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
		$remaining     = count( $options['list'] );
		$attempts      = $options['attempts'];
		$recent_events = count( $options['payload'] );
		$timestamp     = wp_next_scheduled( 'cwp_cron_build_hook' ); // next notification happens at the same time as build

		if ( $options['sent'] ) {
			$sent_list = '<ol>';
			foreach ( $options['sent']  as $s_email => $s_timestamp ) {
				$sent_list .= "<li>{$s_email} [{$s_timestamp}]</li>";
			}
			$sent_list .= '</ol>';
		} else {
			$sent_list = '';
		}

		if ( $options['list'] ) {
			$remaining_list = '<ol>';
			foreach ( array_keys( $options['list'] ) as $r_email ) {
				$remaining_list .= "<li><input type='checkbox' name='cwp_remove[{$r_email}]' " . checked( 0, 1, false ) . " value='1'/><label for='cwp_remove[{$r_email}]'>{$r_email}</label></li>";
			}
			$remaining_list .= '<ol>';
		} else {
			$remaining_list = '';
		}

		if ( ! empty( $timestamp ) ) {
			$next = date( 'l, F d, Y g:i A', $timestamp + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
		} else {
			$next = 'none scheduled';
		}

		$html  = '<table class="form-table"><tbody>';
		$html .= '<tr><td><b>Queue:</b> Last build (list of recent events and email addresses)</td><td>' . $last_build . '</td></tr>';
		$html .= '<tr><td><b>Queue:</b> Number of recent events available</td><td>' . $recent_events . '</td></tr>';
		$html .= '<tr><td><b>Queue:</b> Remaining number of emails to be sent</td><td>' . $remaining . '</td></tr>';
		$html .= '<tr><td><b>Notifications:</b> Next scheduled build and email notification</td><td>' . $next . '</td></tr>';
		$html .= '<tr><td><b>Notifications:</b> Number of email batches already sent (20 emails at a time)</td><td>' . $attempts . '</td></tr>';
		$html .= '</tbody></table>';

		$html .= '<hr><table class="widefat"><caption>Email Queue</caption><tbody>';
		$html .= '<thead><tr><th width="50%">Sent:</th><th width="50%">Awaiting:</th></tr></thead>';
		$html .= '<tr><td>' . $sent_list . '</td><td>' . $remaining_list . '</td></tr>';

		$html .= '</tbody></table>';

		echo $html;
	}

	/*
	|--------------------------------------------------------------------------
	| UAT Settings
	|--------------------------------------------------------------------------
	|
	| Testing the output of the template
	|
	|
	*/

	/**
	 * User Acceptance Testing Settings
	 */
	function settingsUat() {
		$page    = 'cwp_uat_settings';
		$options = 'cwp_uat_settings';

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
		$success_msg = 'Email(s) sent. Please check your inbox';

		// Get the comma separated email addresses
		$email_list = explode( ',', $settings['test_send'] );

		// Trim the whitespace so it can pass the is_email test
		$email_trimmed = array_map( 'trim', $email_list );

		// Placeholder array for our invalid emails
		$invalid = [];

		// Loop through to check for invalid emails
		foreach ( $email_trimmed as $k => $email ) {
			if ( false === is_email( $email ) ) {
				// If invalid email was found, let's add it to invalid[]
				$invalid[] = $email;
				unset( $email_trimmed[ $k ] );
			}
		}

		// Check if there were any invalid email addresses added to $invalid[]
		if ( false === empty( $invalid ) ) {
			add_settings_error(
				'cwp_uat_settings',
				'settings_uat_updated',
				'Please enter only valid e-mail addresses. Notifications have not been sent, invalid emails have been removed.',
				'error'
			);

			// put it back together, minus the baddies
			$settings['test_send'] = implode( ',', $email_trimmed );

		} elseif ( count( $email_trimmed ) > 20 ) {
			add_settings_error(
				'cwp_uat_settings',
				'settings_uat_updated',
				'Exceeded maximum limit of 20 email addresses. Notifications have not been sent.',
				'error'
			);
		} else { // All e-mails were valid and within the limit, proceed
			$u = new Wp\Users();
			$q = new Processors\Queue( $u );
			$m = new Processors\Mail( $q );
			$m->runJustTester( $email_trimmed );

			add_settings_error(
				'cwp_uat_settings',
				'settings_uat_updated',
				$success_msg,
				'updated'
			);
			unset( $settings['test_send'] );
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

		echo "<input type='text' name='cwp_uat_settings[test_send]' value='{$options['test_send']}'></br><small id='emailHelp' class='form-text text-muted'>Enter up to 20 valid email addresses, comma separated.</small>";

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
		$page    = 'cwp_options';
		$options = 'cwp_options';

		register_setting(
			$options,
			'cwp_settings',
			[ $this, 'sanitizeGeneral' ]
		);

		add_settings_section(
			$options . '_section',
			__( 'General Settings', 'custom-wp-notify' ),
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
			'cwp_optin',
			__( 'Subscribe text:', 'custom-wp-notify' ),
			[ $this, 'optInTextRender' ],
			$page,
			$options . '_section'
		);

		add_settings_field(
			'cwp_param',
			__( 'Tracking Campaign Parameter (Matamo/Piwik)', 'custom-wp-notify' ),
			[ $this, 'paramRender' ],
			$page,
			$options . '_section'
		);

		add_settings_section(
			$options . '_reset_section',
			__( 'Reset Cron and Rebuild Queue', 'custom-wp-notify' ),
			'',
			$page
		);

		add_settings_field(
			'cwp_frequency',
			__( 'Notification Frequency:', 'custom-wp-notify' ),
			[ $this, 'frequencyRender' ],
			$page,
			$options . '_reset_section'
		);

		add_settings_field(
			'cwp_start',
			__( 'First Notification Delayed Start (Hours):', 'custom-wp-notify' ),
			[ $this, 'startRender' ],
			$page,
			$options . '_reset_section'
		);
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
			<option value='cwp_weekly'" . selected( $options['cwp_frequency'], 'cwp_weekly', false ) . '>Weekly</option>
		</select><small> <i>NOTE: Changing the frequency triggers notifications to be sent out immediately if the delay (below) is set to zero.</i></small>';
	}

	/**
	 * Render the options page frequency field
	 */
	function startRender() {

		$options   = get_option( 'cwp_settings' );
		$timestamp = time();
		// add default
		if ( ! isset( $options['cwp_start'] ) ) {
			$options['cwp_start'] = 1;
		}

		$select_list = "<select name='cwp_settings[cwp_start]'>";
		for ( $i = 0; $i <= 167; $i ++ ) {
			$time_value   = $timestamp + ( $i * HOUR_IN_SECONDS );
			$time_display = date( 'l, F d, Y g:i A', $time_value + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
			$select_list .= "<option value='{$i}'" . selected( $options['cwp_start'], $i, false ) . ">Delay by {$i} hours: {$time_display}</option>";
		}
		$select_list .= '</select><small> <i>Delay sending the first notification by these many hours. Next scheduled build can be verified in the Logs tab.</i></small>';

		echo $select_list;
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
	 * Customize the value of the unsubscribe link
	 */
	function paramRender() {
		$options = get_option( 'cwp_settings' );

		// add default
		if ( ! isset( $options['cwp_param'] ) ) {
			$options['cwp_param'] = 0;
		}

		echo "<input type='checkbox' name='cwp_settings[cwp_param]'" . checked( $options['cwp_param'], 1, false ) . " value='1'>";
	}

	/**
	 * @param $settings
	 *
	 * @return mixed
	 */
	function sanitizeGeneral( $settings ) {
		$integers  = [ 'cwp_enable', 'cwp_param', 'cwp_start' ];
		$text_only = [ 'cwp_notify' ];
		$enum      = [ 'daily', 'cwp_weekly' ];
		$options   = get_option( 'cwp_settings' );
		$next      = date( 'F d, Y g:i A', time() + ( $settings['cwp_start'] * HOUR_IN_SECONDS ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
		$force     = true;
		// integers
		foreach ( $integers as $int ) {
			if ( isset( $settings[ $int ] ) ) {
				$settings[ $int ] = absint( $settings[ $int ] );
			}
		}

		// text
		foreach ( $text_only as $text ) {
			if ( isset( $settings[ $text ] ) ) {
				$settings[ $text ] = sanitize_text_field( $settings[ $text ] );
			}
		}

		// enumeration
		if ( ! in_array( $settings['cwp_frequency'], $enum, true ) ) {
			unset( $settings['cwp_frequency'] );

			add_settings_error(
				'cwp_options',
				'settings_frequency_updated',
				'Could not find that Notification Frequency',
				'error'
			);
		}

		/*
		|--------------------------------------------------------------------------
		| Feedback, update messages
		|--------------------------------------------------------------------------
		|
		| Unschedule, rebuild with different triggers
		|
		|
		*/
		$s_enabled = ( 1 === $settings['cwp_enable'] ) ? 'true' : 'false';
		$o_enabled = ( 1 === $options['cwp_enable'] ) ? 'true' : 'false';

		if ( 0 !== strcmp( $settings['cwp_enable'], $options['cwp_enable'] ) && 1 === $settings['cwp_enable'] ) {

			add_settings_error(
				'cwp_options',
				'settings_enable',
				'<h1>Notifications have been enabled!</h1>
                <table class="widefat"><tbody>
				<thead><tr><th width="50%">Previous Settings:</th><th width="50%">Current Settings:</th></tr></thead>
				<tr><td>Enabled: ' . $o_enabled . '</td><td>Enabled: ' . $s_enabled . '</td></tr>
				</tbody></table>
				<p>If you want to stop all notifications immediately, uncheck `Enable Notifications` below</p>',
				'updated'
			);
		}

		if ( 0 !== strcmp( $settings['cwp_frequency'], $options['cwp_frequency'] ) || 0 !== strcmp( $settings['cwp_start'], $options['cwp_start'] ) ) {
			$message1 = ( 1 === $settings['cwp_enable'] ) ? 'The first batch of notifications will be sent ' . $next . ' and repeated ' . $settings['cwp_frequency'] . '' : 'Notifications will only be sent if enabled';
			$message2 = ( 1 === $settings['cwp_enable'] ) ? 'If you want to stop all notifications immediately, uncheck `Enable Notifications` below' : 'If you want to send notifications, check `Enable Notifications` below';

			BCcampus\Cron::getInstance()->unScheduleEvents( 'cwp_cron_build_hook' );
			BCcampus\Cron::getInstance()->buildTheQueue( $force );
			BCcampus\Cron::getInstance()->scheduleEventCustomInterval( $settings['cwp_frequency'], $settings['cwp_start'] );

			add_settings_error(
				'cwp_options',
				'settings_cron_change',
				'<h1>Notification Frequency has been updated!</h1>
                            <h3>' . $message1 . '</h3>
                <table class="widefat"><tbody>
				<thead><tr><th width="50%">Previous Settings:</th><th width="50%">Current Settings:</th></tr></thead>
				<tr><td>Frequency: ' . $options['cwp_frequency'] . '</td><td>Frequency: ' . $settings['cwp_frequency'] . '</td></tr>
				<tr><td>First Notification Delay: ' . $options['cwp_start'] . ' hour(s)</td><td>First Notification Delay: ' . $settings['cwp_start'] . ' hour(s)</td></tr>
				</tbody></table>
				<p>' . $message2 . '</p>',
				'updated'
			);
		}

		return $settings;
	}

	/*
	|--------------------------------------------------------------------------
	| Template Settings
	|--------------------------------------------------------------------------
	|
	| Settings for the template
	|
	|
	*/

	/**
	 * Template Settings
	 */
	function settingsTemplate() {
		$page    = 'cwp_template_settings';
		$options = 'cwp_template_settings';

		register_setting(
			$options,
			$options,
			[ $this, 'sanitizeTemplate' ]
		);

		add_settings_section(
			$options . '_section',
			__( 'Template Settings', 'custom-wp-notify' ),
			'',
			$page
		);

		add_settings_field(
			'cwp_template',
			__( 'Notification Template:', 'custom-wp-notify' ),
			[ $this, 'templateRender' ],
			$page,
			$options . '_section'
		);

		add_settings_field(
			'cwp_limit',
			__( 'Number of Recent Events:', 'custom-wp-notify' ),
			[ $this, 'limitRender' ],
			$page,
			$options . '_section'
		);

		add_settings_field(
			'cwp_unsubscribe',
			__( 'Unsubscribe E-mail:', 'custom-wp-notify' ),
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
	 * Render the options page template field
	 */
	function templateRender() {

		$options = get_option( 'cwp_template_settings' );

		// add default
		if ( ! isset( $options['cwp_template'] ) ) {
			$options['cwp_template'] = '';
		}

		echo "<textarea id='cwp_template' cols='60' rows='15' name='cwp_template_settings[cwp_template]' placeholder='<p>Hello {NAME}</p>, \n Here are the latest: \n {EVENTS} \n To Unsubscribe {UNSUBSCRIBE}'>{$options['cwp_template']}</textarea><small><dl><dt>{NAME}</dt><dd>Will be replaced with the name of the subscriber</dd><dt>{EVENTS}</dt><dd>An unordered list of recent events</dd><dt>{UNSUBSCRIBE}</dt><dd>Required unsubscribe link</dd></dl></small>";

	}

	/**
	 * Customize the value of the unsubscribe link
	 */
	function unsubscribeRender() {
		$options = get_option( 'cwp_template_settings' );

		// add default
		if ( ! isset( $options['cwp_unsubscribe'] ) ) {
			$options['cwp_unsubscribe'] = '';
		}

		echo "<input type='text' name='cwp_template_settings[cwp_unsubscribe]' value='{$options['cwp_unsubscribe']}'>";

	}

	/**
	 * Custom CSS
	 */
	function cssRender() {
		$options = get_option( 'cwp_template_settings' );

		// add default
		if ( ! isset( $options['cwp_css'] ) ) {
			$options['cwp_css'] = '#emailContainer{}';
		}

		echo "<textarea id='cwp_css' cols='60' rows='15' name='cwp_template_settings[cwp_css]'>{$options['cwp_css']}</textarea>";

	}

	/**
	 *
	 */
	function limitRender() {
		$options = get_option( 'cwp_template_settings' );

		// add default
		if ( ! isset( $options['cwp_limit'] ) ) {
			$options['cwp_limit'] = 4;
		}

		$select_list = "<select name='cwp_template_settings[cwp_limit]'>";
		for ( $i = 1; $i <= 20; $i ++ ) {
			$select_list .= "<option value='{$i}'" . selected( $options['cwp_limit'], $i, false ) . ">{$i}</option>";
		}
		$select_list .= '</select>';

		echo $select_list;
	}

	/**
	 * @param $settings
	 *
	 * @return mixed
	 */
	function sanitizeTemplate( $settings ) {
		$esc_html  = [ 'cwp_template', 'cwp_css' ];
		$esc_email = [ 'cwp_unsubscribe' ];
		$integers  = [ 'cwp_limit' ];

		// integers
		foreach ( $integers as $int ) {
			$settings[ $int ] = absint( $settings[ $int ] );
		}

		// esc html
		foreach ( $esc_html as $html ) {
			$settings[ $html ] = esc_html( $settings[ $html ] );
		}

		// esc email
		foreach ( $esc_email as $email ) {
			$settings[ $email ] = sanitize_email( $settings[ $email ] );
		}

		if ( empty( $settings['cwp_unsubscribe'] ) || false === is_email( $settings['cwp_unsubscribe'] ) ) {
			add_settings_error(
				'cwp_template_options',
				'settings_updated',
				'Please enter a valid email in UNSUBSCRIBE EMAIL below where people can reply to unsubscribe.',
				'error'
			);
		}

		return $settings;
	}

	/*
	|--------------------------------------------------------------------------
	| Options page
	|--------------------------------------------------------------------------
	|
	| Admin Interface
	|
	|
	*/

	/**
	 * The function to be called to output the content for this page
	 */
	function optionsPage() {
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general'; //@codingStandardsIgnoreLine
		?>
		<h2>Custom WP Notify</h2>
		<div id="icon-options-general" class="icon32"></div>
		<h2 class="nav-tab-wrapper">
			<a href="?page=custom-wp-notify&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">General</a>
			<a href="?page=custom-wp-notify&tab=template" class="nav-tab <?php echo $active_tab === 'template' ? 'nav-tab-active' : ''; ?>">Template</a>
			<a href="?page=custom-wp-notify&tab=testing" class="nav-tab <?php echo $active_tab === 'testing' ? 'nav-tab-active' : ''; ?>">Testing</a>
			<a href="?page=custom-wp-notify&tab=manage-users" class="nav-tab <?php echo $active_tab === 'manage-users' ? 'nav-tab-active' : ''; ?>">Subscription Management</a>
			<a href="?page=custom-wp-notify&tab=logs" class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">Logs</a>
		</h2>

		<form action="options.php" method="post">
		<?php

		switch ( $active_tab ) {

			case 'general':
				settings_fields( 'cwp_options' );
				do_settings_sections( 'cwp_options' );

				break;
			case 'template':
				settings_fields( 'cwp_template_settings' );
				do_settings_sections( 'cwp_template_settings' );

				break;
			case 'testing':
				settings_fields( 'cwp_uat_settings' );
				do_settings_sections( 'cwp_uat_settings' );
				submit_button( 'Send Test Email' );

				break;
			case 'manage-users':
				settings_fields( 'cwp_manage_users' );
				do_settings_sections( 'cwp_manage_users' );

				break;

			case 'logs':
				settings_fields( 'cwp_log_settings' );
				do_settings_sections( 'cwp_log_settings' );
				wp_nonce_field( 'cwp-options-remove-action', 'cwp-options-remove-emails-field' );

				submit_button( 'Remove selected emails from the queue' );

				break;
		}

		if ( ! in_array( $active_tab, [ 'logs', 'testing' ], true ) ) {
			wp_nonce_field( 'cwp-options-update-action', 'cwp-options-update-field' );
			submit_button();
		}

		echo '</form>';
	}
}


