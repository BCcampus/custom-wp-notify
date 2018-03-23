<?php
/**
 * Plugin Name:     Custom Notifications for WP
 * Plugin URI:      https://github.com/BCcampus/custom-wp-notify
 * Description:     Let your WordPress users opt-in to receive e-mail notifications based on their preferences
 * Author:          bdolor
 * Text Domain:     custom-wp-notify
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Custom_Wp_Notify
 */

// Your code starts here.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
|
|
|
|
*/
if ( ! defined( 'CWP_DIR' ) ) {
	define( 'CWP_DIR', __DIR__ . '/' );
}

require_once CWP_DIR . 'autoloader.php';
require_once CWP_DIR . 'vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Update values required for Custom Rest Routes
|--------------------------------------------------------------------------
|
|
|
|
*/
add_action( 'init', function () {

	$slug = 'rest_routes';
	$args = [ 'event' => 1, 'location' => 1 ];

	if ( is_multisite() ) {
		$exists = get_site_option( $slug, false );
		if ( ! $exists ) {
			update_site_option( $slug, $args );
		}
	} else {
		$exists = get_option( $slug );
		if ( ! $exists ) {
			update_option( $slug, $args );
		}
	}
} );

/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
|
| Admin Notification
|
|
*/
add_action( 'admin_notices', function () {
	if ( ! is_plugin_active( 'events-manager/events-manager.php' ) ) {
		$class   = 'notice notice-error';
		$message = __( 'Custom WP Notify: Please install the Events Manager plugin.', 'custom-wp-notify' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}
} );

/*
|--------------------------------------------------------------------------
| Deactivation
|--------------------------------------------------------------------------
|
| clear all cron jobs when plugin deactivated
|
|
*/
register_deactivation_hook( __FILE__, function () {
	$b_timestamp = wp_next_scheduled( 'cwp_cron_build_hook' );
	wp_unschedule_event( $b_timestamp, 'cwp_cron_build_hook' );

	$m_timestamp = wp_next_scheduled( 'cwp_cron_notify_hook' );
	wp_unschedule_event( $m_timestamp, 'cwp_cron_notify_hook' );

} );

/*
|--------------------------------------------------------------------------
| Cron Instance
|--------------------------------------------------------------------------
|
| singleton
|
|
*/
\BCcampus\Cron::getInstance();

/*
|--------------------------------------------------------------------------
| For Dev Only
|--------------------------------------------------------------------------
|
| Delete eventually
|
|
*/
//$u = new \BCcampus\Models\Wp\Users();
//$q = new \BCcampus\Processors\Queue( $u );
//$q->maybeBuild();
//echo '<pre>'; print_r( _get_cron_array() ); echo '</pre>';
//$u = new \BCcampus\Models\Wp\Users();
//$q = new \BCcampus\Processors\Queue( $u );
//$m = new \BCcampus\Processors\Mail( $q );
//$m->maybeRun();

/**
 * Check the user has the right permissions for the options page
 */
if ( is_admin() ) {
	new \BCcampus\CwpOptions();
}

/**
 * The shortcode can be placed anywhere
 */
new \BCcampus\CwpShortcode();

/**
 * Add support for BP registration page
 */
if ( function_exists( 'bp_is_active' ) ) {
	// TODO: allow admins to activate, move this to Options
	new \BCcampus\CwpBp();
}
