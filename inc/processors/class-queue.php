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
			$this->events = $em_events->getRecentEvents();
		}
		// TODO add else to get API Events
	}

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

	public function build() {
		if ( false === $this->verify() ) {
			return;
		}


	}

	public function getQueue() {

	}


}
