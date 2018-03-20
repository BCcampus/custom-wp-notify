<?php
/**
 * Project: custom-wp-notify
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2018 Brad Payne
 * Date: 2018-02-21
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) Brad Payne
 */

namespace BCcampus\Processors;

use BCcampus\Models\Wp;
use BCcampus\Models\Posts;
use BCcampus\Models\Api;

/**
 * Class Queue
 * @package BCcampus\Processors
 */
class Queue {
	private $users;
	private $posts;

	/**
	 * Queue constructor.
	 *
	 * @param Wp\Users $users
	 */
	public function __construct( Wp\Users $users ) {
		$this->users = $users;
		$new_posts   = new Posts\CwpPosts();
		if ( $new_posts->getRecentEvents() ) {
			$this->posts = $new_posts;
		}
		if ( $new_posts->getRecentPosts() ) {
			$this->posts = $new_posts;
		}
		// TODO add else to get API posts
	}

	/**
	 * @return bool
	 */
	private function verify() {
		$ok            = true;
		$already_built = get_option( 'cwp_queue' );

		// have new posts since the last mailout?
		if ( empty( $this->posts ) ) {
			return false;
		}
		// have a list of emails?
		if ( empty( $this->users->getUserList() ) ) {
			return false;
		}
		// have valid templates?
		// TODO include validation for template readiness

		// don't build if we're in the middle of iterating through a queue
		if ( $already_built['safe_to_rebuild'] === false ) {
			return false;
		}

		return $ok;
	}

	/**
	 * build the queue
	 */
	public function maybeBuild() {
		if ( false === $this->verify() ) {
			return;
		}

		$posts = $this->posts->getTitlesAndLinks( $this->posts->getRecentEvents() );

		$queue = [
			'queue'           => 'cwp_notify',
			'attempts'        => 0,
			'safe_to_rebuild' => false,
			'created_at'      => time(),
			'list'            => $this->users->getUserList(),
			'payload'         => $posts
		];

		update_option( 'cwp_queue', $queue );

	}

	/**
	 * @return mixed
	 */
	public function getQueueOptions() {
		$queue = get_option( 'cwp_queue' );

		return $queue;

	}

	/**
	 * @param $option
	 */
	public function updateQueueOptions( $option ) {
		if ( is_array( $option ) ) {
			update_option( 'cwp_queue', $option );
		}
	}


}
