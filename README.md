# Custom Notifications for WP #
**Contributors:** bdolor,aparedes  
**Tags:** notifications, email  
**Requires at least:** 4.9.5  
**Tested up to:** 4.9.5  
**Stable tag:** 0.5.0  
**License:** GPLv3 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Let your WordPress users opt-in to receive e-mail notifications based on their preferences.

## Description ##

Let your WordPress users opt-in to receive e-mail notifications based on their preferences. Uses `wp_cron` to send out customized emails to a list of opt-in users.

## Requirements ##

Currently requires Events Manager plugin, but we have plans to make the functionality less exclusive in the future.

## Installation ##

1. Upload `custom-wp-notify` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Changelog ##
### 0.5.0 2018-04-13###
* [feature] add Matomo integration for email campaigns
* [feature] move template control to admin interface
* [bug] fix logic of recent events to be grouped
* [feature] give admins ability to control how many events are displayed in email
* [feature] extend test email functionality to send to multiple recipients
* [feature] provide greater flexibility with user subsription management

### 0.1.0 ###
* initial commit


