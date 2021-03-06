<?php
/**
 * Project: custom-wp-notify
 * Project Sponsor: BCcampus
 * Copyright Brad Payne <https://bradpayne.ca>
 * Date: 2018-02-19
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) Brad Payne
 */

namespace BCcampus\Models\Wp;

class Users {

	public function __construct() {

	}

	/**
	 * Retrieve names and emails of folks who have opted in.
	 *
	 * @return array
	 */
	public function getUserList() {
		// loop through all users who have opted in
		$args  = [
			'meta_key' => 'cwp_notify', //@codingStandardsIgnoreLine
			'meta_value' => 1, //@codingStandardsIgnoreLine
		];
		$list  = [];
		$users = get_users( $args );

		foreach ( $users as $user ) {
			// skip over spam users, or those not yet registered
			if ( '0' !== $user->data->user_status ) {
				continue;
			}

			$meta = get_user_meta( $user->data->ID, 'cwp_notify_categories', false );
			$cats = [];

			if ( is_array( $meta ) ) {
				$cats = maybe_unserialize( $meta[0] );
			}

			$list[ $user->data->user_email ] = [
				'name'       => $user->data->display_name,
				'event_cats' => $cats,
			];
		}

		return $list;

	}

	/**
	 * will update existing users to either un/subscribe
	 *
	 * @param int $notify
	 */
	public function updateUserList( int $notify ) {
		// loop through all users who have opted in
		$users = get_users();

		foreach ( $users as $user ) {
			// skip over spam users, or those not yet registered
			if ( '0' !== $user->data->user_status ) {
				continue;
			}
			if ( ! get_user_meta( $user->data->ID, 'cwp_notify', true ) ) {
				update_user_meta( $user->data->ID, 'cwp_notify', $notify );
			}
		}
	}
}
