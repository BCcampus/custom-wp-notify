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
require_once __DIR__ . '/vendor/autoload.php';
