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
require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Check the user has the right permissions
 */
if ( [ $this, 'current_user_can( "manage_options" )' ] ) {
	new \BCcampus\Settings();
	new \BCcampus\Shortcode();
} else {
	return;
}

