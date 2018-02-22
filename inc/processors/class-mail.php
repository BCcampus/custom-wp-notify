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
	 * MailWorker constructor
	 */
	public function __construct() {
		$this->queue = wp_option( 'cwp_queue' );
	}


	private function verify() {
		$ok = true;
		// have a queue?
		if ( is_empty( $this->queue ) ) {
			$ok = false;
		}

		return $ok;

	}

	public function run() {
		if ( false === $this->verify() ) {
			return;
		}




	}
}
