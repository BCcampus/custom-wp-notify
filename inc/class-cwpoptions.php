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
		add_action( 'admin_init', [ $this, 'settingsManage' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'codeMirror' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'multiSelect' ] );
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
	 * Add multi select jQuery plugin
	 * http://crlcu.github.io/multiselect/
	 */
	function multiSelect() {
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === $this::PAGE ) {
			wp_enqueue_script( 'cwp-multi-select', 'https://cdn.jsdelivr.net/npm/multiselect-two-sides@2.5.0/dist/js/multiselect.min.js/', [ 'jquery' ], null, true );
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
			function updateUsers (){
	    if (isset($_POST['yes']) | isset($_POST['no'])) {

	      if (isset($_POST['no'])) {
	      foreach ($_POST['no'] as $username){
	          // Get the user object by login name
	           $userobject = get_user_by('login', $username);
                // Get the user ID
	           $user_id = $userobject->ID;
	           // Get the existing preference if any
               $user_value = get_user_meta( $user_id, 'cwp_notify', true );
                // Update their preference only if it's different
        	    if ( $user_value != "0" ) {
				update_user_meta( $user_id, 'cwp_notify', "0" );
	      }
	      }
	      }

	      	      if (isset($_POST['yes'])) {
	      foreach ($_POST['yes'] as $username){
	          // Get the user object by login name
	           $userobject = get_user_by('login', $username);
                // Get the user ID
	           $user_id = $userobject->ID;
	           // Get the existing preference if any
               $user_value = get_user_meta( $user_id, 'cwp_notify', true );
                // Update their preference only if it's different
        	    if ( $user_value != "1" ) {
				update_user_meta( $user_id, 'cwp_notify', "1" );
	      }
	      }
	      }

	    } else {
	        return;
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
			$user_preference = get_user_meta( $user->ID, 'cwp_notify', true );

			// If a preference doesn't already exist, create it with default to 0
			if ( ! ( $user_preference == "1" || $user_preference == "0" ) ) {
				update_user_meta( $all_users->ID, 'cwp_notify', "0" );
			}
		}

		$subscribed     = get_users( [ 'meta_key' => 'cwp_notify', 'meta_value' => '1' ] );
		$not_subscribed = get_users( [ 'meta_key' => 'cwp_notify', 'meta_value' => '1', 'meta_compare' => '!=' ] );

		$html = "<div class='row'><div class='col-xs-5'>";
		$html .= "<select name='yes[]' id='multiselect' class='form-control' size='8' multiple='multiple'>";
		$html .= "<h2>Subscribed</h2>";
		foreach ( $subscribed as $user ) {
			$html .= "<option value='{$user->user_login}'>{$user->user_login}</option>";
		}
		$html .= "</select></div>";
		$html .= "<div class='col-xs-2'>";
		$html .= "<button type='button' id='multiselect_rightAll' class='btn btn-block'><i class='glyphicon glyphicon-forward'></i></button>";
		$html .= "<button type='button' id='multiselect_rightSelected' class='btn btn-block'><i class='glyphicon glyphicon-chevron-right'></i></button>";
		$html .= "<button type='button' id='multiselect_leftSelected' class='btn btn-block'><i class='glyphicon glyphicon-chevron-left'></i></button>";
		$html .= "<button type='button' id='multiselect_leftAll' class='btn btn-block'><i class='glyphicon glyphicon-backward'></i></button>";
		$html .= "</div><div class='col-xs-5'>";
		$html .= "<select name='no[]' id='multiselect_to' class='form-control' size='8' multiple='multiple'>";
		$html .= "<h2>Not Subscribed</h2>";
		foreach ( $not_subscribed as $user ) {
			$html .= "<option value='{$user->user_login}'>{$user->user_login}</option>";
		}
		$html .= "</select></div></div>";

		echo $html;

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
		$success_msg = 'Email sent. Please check your inbox';

		$valid = is_email( $settings['test_send'] );

		if ( false === $valid ) {
			unset ( $settings['test_send'] );

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
	 * @param $settings
	 *
	 * @return mixed
	 */
	function sanitize( $settings ) {
		$integers  = [ 'cwp_enable' ];
		$text_only = [ 'cwp_notify' ];
		$esc_html  = [ 'cwp_template', 'cwp_css' ];
		$esc_email   = [ 'cwp_unsubscribe' ];
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

		// esc email
		foreach ( $esc_email as $email ) {
			$settings[ $email ] = sanitize_email( $settings[ $email ] );
		}

		if ( empty( $settings['cwp_unsubscribe'] ) || false === is_email( $settings['cwp_unsubscribe'] ) ) {
			add_settings_error(
				'cwp_options',
				'settings_updated',
				'Please enter a valid email in UNSUBSCRIBE EMAIL below where people can reply to unsubscribe.',
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
		} elseif ( isset( $_POST['cwp_settings']['cwp_frequency'] ) ) {
			// check for a change in the stored value
			if ( 0 !== strcmp( $_POST['cwp_settings']['cwp_frequency'], $options['cwp_frequency'] ) ) {
				BCcampus\Cron::getInstance()->unScheduleEvents( 'cwp_cron_build_hook' );
				BCcampus\Cron::getInstance()->scheduleEventCustomInterval( $_POST['cwp_settings']['cwp_frequency'] );
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
		<!-- Bootstrap styling -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <h2>Custom WP Notify</h2>
        <div id="icon-options-general" class="icon32"></div>
        <h2 class="nav-tab-wrapper">
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

			case 'template':

				settings_fields( 'cwp_options' );
				do_settings_sections( 'cwp_options' );

				break;
			case 'testing':

				settings_fields( 'cwp_uat_settings' );
				do_settings_sections( 'cwp_uat_settings' );

				break;
			case 'manage-users':

				settings_fields( 'cwp_manage_users' );
				do_settings_sections( 'cwp_manage_users' );

				break;

			case 'logs':

				settings_fields( 'cwp_log_settings' );
				do_settings_sections( 'cwp_log_settings' );
		}

		if ( ! in_array( $active_tab, [ 'logs' ] ) ) {
			submit_button();
		}

		echo "</form>";

        // Do the CodeMirror JS in the appropriate tab to avoid console errors

        if ($active_tab === 'template') { ?>
	        <script type="text/javascript">
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
        // Do the MultiSelect JS in the appropriate tab only
            if ($active_tab === 'manage-users') { ?>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
	                    $('#multiselect').multiselect();
                });
                </script>
		<?php
    }
  }
}


