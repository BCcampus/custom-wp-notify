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
use BCcampus\Models\Em;
use BCcampus\Models\Api;

/**
 * Class Queue
 * @package BCcampus\Processors
 */
class Queue {
	private $users;
	private $events;

	/**
	 * Queue constructor.
	 *
	 * @param Wp\Users $users
	 */
	public function __construct( Wp\Users $users ) {
		$this->users = $users;
		$em_events   = new Em\Events();
		if ( $em_events->getRecentEvents() ) {
			$this->events = $em_events;
		}
		// TODO add else to get API Events
	}

	/**
	 * @return bool
	 */
	private function verify() {
		$ok = true;
		// have new events since the last mailout?
		if ( is_empty( $this->events ) ) {
			$ok = false;
		}
		// have a list of emails?
		if ( empty( $this->users->getUserList() ) ) {
			$ok = false;
		}
		// have valid templates?
		// TODO include validation for template readiness

		return $ok;
	}

	/**
	 * build the queue
	 */
	public function build() {
		if ( false === $this->verify() ) {
			return;
		}
		$events = $this->events->getTitlesAndLinks( $this->events->getRecentEvents() );

		$queue = [
			'queue'      => 'cwp_notify',
			'attempts'   => 0,
			'created_at' => time(),
			'list'       => $this->users->getUserList(),
			'payload'    => $events
		];

		update_option( 'cwp_queue', $queue );

	}

	/**
	 * @return mixed
	 */
	public function getQueue() {
		$queue = get_option( 'cwp_queue' );

		return $queue;

	}


}
