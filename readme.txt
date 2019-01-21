=== Custom Notifications for WP ===
Contributors: bdolor,aparedes
Tags: notifications, email
Requires at least: 4.9.8
Tested up to: 5.0.3
Requires PHP: 7.1
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let your WordPress users opt-in to receive e-mail notifications based on their preferences.

== Description ==

Let your WordPress users opt-in to receive e-mail notifications based on their preferences.


== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates


== Changelog ==

= 1.0.0 (2019-01-18)=
* [bug] multiselect fix for managing email subscriptions
* [enhancement] updated and enforced coding standards
* [enhancement] force build feature
* [enhancement] improved UI for better feedback and admin confidence
* [enhancement] map user preferences to email notifications

= 0.6.0 (2018-06-28) =
* [feature] event categories shortcode
* [feature] shortcode for personalized page
* [enhancement] select all for event categories
* [enhancement] better dependency mgmt, travis integration
* [enhancement] applied coding standards

= 0.5.1 (2018-04-18) =
* [bug] fix for sign up form value not being saved
* [bug] prevent spam, deleted, unregistered users from displaying
* [optimization] assign default unsubscribe value

= 0.5.0 (2018-04-13) =
* [feature] add Matomo integration for email campaigns
* [feature] move template control to admin interface
* [bug] fix logic of recent events to be grouped
* [feature] give admins ability to control how many events are displayed in email
* [feature] extend test email functionality to send to multiple recipients
* [feature] provide greater flexibility with user subscription management

= 0.1.0 =
* initial commit



