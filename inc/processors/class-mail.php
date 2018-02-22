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

class Mail {

	private $queue;

	/**
	 * MailWorker constructor.
	 *
	 * @param Queue $queue
	 */
	public function __construct( Queue $queue ) {

		$this->queue = $queue;
	}


	private function verify() {
		// have a queue?
	}

	public function run() {

	}
}
