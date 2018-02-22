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
	 *
	 * @param Queue $queue
	 */
	public function __construct( Queue $queue ) {
		$this->queue = $queue;
	}


	private function verify() {
		$ok = true;
		// have a queue?
		if ( empty( $this->queue->getQueueOptions() ) ) {
			return false;
		}

		// TODO check if it's stale (older than frequency perhaps?)

		return $ok;

	}

	/**
	 *
	 */
	public function run() {
		if ( false === $this->verify() ) {
			return;
		}
		$subject  = 'Recent Events';
		$limit    = 20;
		$jobs     = $this->queue->getQueueOptions();
		$attempts = $jobs['attempts'];
		$message  = $this->applyTemplates( $jobs['payload'] );

		// send an email to each recipient
		foreach ( $jobs['list'] as $email => $name ) {
			$to      = $email;
			$sub     = $subject;
			$msg     = $message;
			$headers = [ 'Content-Type: text/html; charset=UTF-8', 'From: My Site Name &lt;no-reply@example.com' ];

			$ok = \wp_mail( $to, $sub, $msg, $headers );

			// take the recipient out of the list if it's been successful
			if ( $ok ) {
				unset( $jobs['list'][ $email ] );
			} else {
				\error_log( '\BCcampus\Processors\Mail->run failed to send a message to ' . $email );
			}

			if ( -- $limit == 0 ) {
				break;
			}
		}

		// flag the queue as safe to rebuild
		if ( empty( $jobs['list'] ) ) {
			$jobs['safe_to_rebuild'] = true;
		} else {
			// update the queue for the next round
			$jobs['attempts'] = $attempts + 1;
			$this->queue->updateQueueOptions( $jobs );
		}

	}

	/**
	 * @param $payload
	 *
	 * @return string
	 */
	private function applyTemplates( $payload ) {
		//$settings = get_option('cwp_settings');
		//$settings['cwp_template'];

		$html = '<ul>';

		foreach ( $payload as $event ) {
			$html .= "<li><a href='{$event['link']}'>{$event['title'] }</a></li>";

		}
		$html .= '</ul>';

		return $html;
	}
}
