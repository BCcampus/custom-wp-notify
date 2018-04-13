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

		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === $this::PAGE ) {
			// Code Mirror
			if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] === 'template' ) {
				wp_enqueue_script( 'wp-codemirror' );
				wp_enqueue_script( 'htmlhint' );
				wp_enqueue_script( 'csslint' );
				wp_enqueue_style( 'wp-codemirror' );
				wp_enqueue_script( 'cwp-codemirror-script', plugin_dir_url( __FILE__ ) . '../assets/scripts/cwp-codemirror.js', [ 'jquery' ], NULL, TRUE );
			}
			if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] === 'manage-users' ) {
				wp_enqueue_script( 'cwp-multi-select', 'https://cdn.jsdelivr.net/npm/multiselect-two-sides@2.5.0/dist/js/multiselect.min.js/', [ 'jquery' ], NULL, TRUE );
				wp_enqueue_script( 'cwp-multi-select-script', plugin_dir_url( __FILE__ ) . '../assets/scripts/cwp-multiselect.js', [ 'jquery' ], NULL, TRUE );
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
		$page = $options = 'cwp_manage_users';

		register_setting(
			$options,
			$options, $this->updateUsers()
		);

		add_settings_section(
			$options . '_section',
			__( 'Manage Users', 'custom-wp-notify' ),
			[ $this, 'manageUsers' ],
			$page
		);

	}

	/**
	 *
	 */
	function updateUsers() {

		if ( isset( $_POST['no'] ) ) {
			foreach ( $_POST['no'] as $username ) {
				// Get the user object by login name
				$userobject = get_user_by( 'login', $username );
				// Get the user ID
				$user_id = $userobject->ID;
				// Get the existing preference if any
				$user_value = get_user_meta( $user_id, 'cwp_notify', TRUE );
				// Update their preference only if it's different
				if ( $user_value != '0' ) {
					update_user_meta( $user_id, 'cwp_notify', '0' );
				}
			}
		}

		if ( isset( $_POST['yes'] ) ) {
			foreach ( $_POST['yes'] as $username ) {
				// Get the user object by login name
				$userobject = get_user_by( 'login', $username );
				// Get the user ID
				$user_id = $userobject->ID;
				// Get the existing preference if any
				$user_value = get_user_meta( $user_id, 'cwp_notify', TRUE );
				// Update their preference only if it's different
				if ( $user_value != '1' ) {
					update_user_meta( $user_id, 'cwp_notify', '1' );
				}
			}
		}
	}

	/**
	 *
	 */
	function manageUsers() {

		$all_users = get_users();

		// To build the listboxes, we need to check for the value of cwp_notify, so let's make sure it exists.
		foreach ( $all_users as $user ) {
			// get the existing meta values
			$user_preference = get_user_meta( $user->ID, 'cwp_notify', TRUE );

			// If a preference doesn't already exist, create it with default to 0
			if ( ! ( $user_preference == '1' || $user_preference == '0' ) ) {
				update_user_meta( $all_users->ID, 'cwp_notify', '0' );
			}
		}

		$subscribed     = get_users(
			[
				'meta_key'   => 'cwp_notify',
				'meta_value' => '1',
			]
		);
		$not_subscribed = get_users(
			[
				'meta_key'     => 'cwp_notify',
				'meta_value'   => '1',
				'meta_compare' => '!=',
			]
		);

		$html = "<div class='row'><div class='col-xs-5'>";
		$html .= '<h5>Subscribed</h5>';
		$html .= "<select name='yes[]' id='multiselect' class='form-control' size='8' multiple='multiple'>";
		foreach ( $subscribed as $user ) {
			$html .= "<option value='{$user->user_login}'>$user->user_email [$user->user_login]</option>";
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
			$html .= "<option value='{$user->user_login}'>$user->user_email [$user->user_login]</option>";
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
		$options        = get_option( 'cwp_queue' );
		$last_build     = date( 'F d, Y g:i A (T)', $options['created_at'] );
		$remaining      = count( $options['list'] );
		$remaining_list = implode( ', ', array_keys( $options['list'] ) );
		$attempts       = $options['attempts'];
		$recent_events  = count( $options['payload'] );
		$timestamp      = wp_next_scheduled( 'cwp_cron_build_hook' );

		if ( $options['sent'] ) {
			$sent_list = implode( ', ', array_keys( $options['sent'] ) );
		} else {
			$sent_list = 'no previously sent notifications';
		}

		if ( ! empty( $timestamp ) ) {
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
		$html .= '<tr><td><b>Previously sent notifications to: </b></td><td>' . $sent_list . '</td></tr>';
		$html .= '<tr><td><b>Upcoming notifications will be sent to: </b></td><td>' . $remaining_list . '</td></tr>';
		$html .= '</table>';

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
		$success_msg = 'Email(s) sent. Please check your inbox';

		// Get the comma separated email addresses
		$email_list = explode( ',', $settings['test_send'] );

		// Trim the whitespace so it can pass the is_email test
		$email_trimmed = array_map( 'trim', $email_list );

		// Placeholder array for our invalid emails
		$invalid = [];

		// Loop through to check for invalid emails
		foreach ( $email_trimmed as $k => $email ) {
			if ( FALSE === is_email( $email ) ) {
				// If invalid email was found, let's add it to invalid[]
				$invalid[] = $email;
				unset( $email_trimmed[ $k ] );
			}
		}

		// Check if there were any invalid email addresses added to $invalid[]
		if ( FALSE === empty( $invalid ) ) {
			add_settings_error(
				'cwp_uat_settings',
				'settings_uat_updated',
				'Please enter only valid e-mail addresses. Notifications have not been sent, invalid emails have been removed.',
				'error'
			);

			// put it back together, minus the baddies
			$settings['test_send'] = implode( ',', $email_trimmed );

		} else if ( count( $email_trimmed ) > 20 ) {
			add_settings_error(
				'cwp_uat_settings',
				'settings_uat_updated',
				'Exceeded maximum limit of 20 email addresses. Notifications have not been sent.',
				'error'
			);
		} // All e-mails were valid and within the limit, proceed
		else {
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
		$page = $options = 'cwp_options';

		register_setting(
			$options,
			'cwp_settings',
			[ $this, 'sanitize' ]
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
			'cwp_frequency',
			__( 'Notification Frequency:', 'custom-wp-notify' ),
			[ $this, 'frequencyRender' ],
			$page,
			$options . '_section'
		);

		add_settings_field(
			'cwp_start',
			__( 'Notification Delayed Start (Hours):', 'custom-wp-notify' ),
			[ $this, 'startRender' ],
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

		echo "<input type='checkbox' name='cwp_settings[cwp_enable]'" . checked( $options['cwp_enable'], 1, FALSE ) . " value='1'>";
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
			<option value='daily'" . selected( $options['cwp_frequency'], 'daily', FALSE ) . ">Daily</option>
			<option value='cwp_weekly'" . selected( $options['cwp_frequency'], 'cwp_weekly', FALSE ) . '>Weekly</option>
		</select><small> <i>NOTE: Changing the frequency triggers notifications to be sent out immediately if the delay (below) is set to zero.</i></small>';
	}

	/**
	 * Render the options page frequency field
	 */
	function startRender() {

		$options = get_option( 'cwp_settings' );
		// add default
		if ( ! isset( $options['cwp_start'] ) ) {
			$options['cwp_start'] = 1;
		}

		$select_list = "<select name='cwp_settings[cwp_start]'>";
		for ( $i = 0; $i <= 167; $i ++ ) {
			$select_list .= "<option value='{$i}'" . selected( $options['cwp_start'], $i, FALSE ) . ">{$i}</option>";
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

		echo "<input type='checkbox' name='cwp_settings[cwp_param]'" . checked( $options['cwp_param'], 1, FALSE ) . " value='1'>";
	}

	/**
	 * @param $settings
	 *
	 * @return mixed
	 */
	function sanitize( $settings ) {
		$integers  = [ 'cwp_enable', 'cwp_param', 'cwp_start' ];
		$text_only = [ 'cwp_notify' ];
		$enum      = [ 'daily', 'cwp_weekly' ];
		$options   = get_option( 'cwp_settings' );

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
		if ( ! in_array( $settings['cwp_frequency'], $enum ) ) {
			unset( $settings['cwp_frequency'] );

			add_settings_error(
				'cwp_options',
				'settings_frequency_updated',
				'Could not find that Notification Frequency',
				'error'
			);
		} else {
			// check for a change in the stored value
			if ( 0 !== strcmp( $settings['cwp_frequency'], $options['cwp_frequency'] ) || 0 !== strcmp( $settings['cwp_start'], $options['cwp_start'] ) ) {
				BCcampus\Cron::getInstance()->unScheduleEvents( 'cwp_cron_build_hook' );
				BCcampus\Cron::getInstance()->scheduleEventCustomInterval( $settings['cwp_frequency'], $settings['cwp_start'] );
			}
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
		$page = $options = 'cwp_template_settings';

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
			$select_list .= "<option value='{$i}'" . selected( $options['cwp_limit'], $i, FALSE ) . ">{$i}</option>";
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

		if ( empty( $settings['cwp_unsubscribe'] ) || FALSE === is_email( $settings['cwp_unsubscribe'] ) ) {
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
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		?>
        <!-- Bootstrap styling -->
        <link rel="stylesheet"
              href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
              integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
              crossorigin="anonymous">
        <h2>Custom WP Notify</h2>
        <div id="icon-options-general" class="icon32"></div>
        <h2 class="nav-tab-wrapper">
            <a href="?page=custom-wp-notify&tab=general"
               class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
            <a href="?page=custom-wp-notify&tab=template"
               class="nav-tab <?php echo $active_tab == 'template' ? 'nav-tab-active' : ''; ?>">Template</a>
            <a href="?page=custom-wp-notify&tab=testing"
               class="nav-tab <?php echo $active_tab == 'testing' ? 'nav-tab-active' : ''; ?>">Testing</a>
            <a href="?page=custom-wp-notify&tab=manage-users"
               class="nav-tab <?php echo $active_tab == 'manage-users' ? 'nav-tab-active' : ''; ?>">Subscription
                Management</a>
            <a href="?page=custom-wp-notify&tab=logs"
               class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">Logs</a>
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
		}

		if ( ! in_array( $active_tab, [ 'logs', 'testing' ] ) ) {
			submit_button();
		}

		echo '</form>';
	}
}


