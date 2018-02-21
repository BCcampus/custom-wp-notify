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

require_once __DIR__ . '/autoloader.php';

/*
|--------------------------------------------------------------------------
| Dependency check
|--------------------------------------------------------------------------
|
| Add what we don't have
|
|
*/

add_action( 'init', function () {
	// Looks for the existence of dependencies
	if ( ! class_exists( 'BCcampus\Rest\Routes' ) ) {
		require_once __DIR__ . '/vendor/autoload.php';
	} else { // only update options once

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
	}
} );

