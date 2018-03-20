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
	/**
	 * @var Queue
	 */
	private $queue;

	/**
	 * MailWorker constructor
	 *
	 * @param Queue $queue
	 */
	public function __construct( Queue $queue ) {
		$this->queue = $queue;
	}

	/**
	 * @return bool
	 */
	private function verify() {
		$ok       = true;
		$options  = $this->queue->getQueueOptions();
		$settings = get_option( 'cwp_settings' );

		// admin has disabled?
		if ( ! isset( $settings['cwp_enable'] ) || ! $settings['cwp_enable'] === 1 ) {
			return false;
		}

		// have jobs?
		if ( empty( $options['list'] ) || true === $options['safe_to_rebuild'] ) {
			return false;
		}

		// TODO check if it's stale (older than frequency perhaps?)

		return $ok;

	}

	/**
	 *
	 */
	public function maybeRun() {
		if ( false === $this->verify() ) {
			return;
		}
		$subject  = 'Recent Events';
		$limit    = 20;
		$jobs     = $this->queue->getQueueOptions();
		$attempts = $jobs['attempts'];

		// send an email to each recipient
		foreach ( $jobs['list'] as $email => $name ) {
			$to       = $email;
			$sub      = $subject;
			$msg      = $this->applyTemplates( $jobs['payload'], $name );
			$sitename = strtolower( $_SERVER['SERVER_NAME'] );
			if ( substr( $sitename, 0, 4 ) == 'www.' ) {
				$sitename = substr( $sitename, 4 );
			}
			$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

			if ( ! function_exists( 'wp_mail' ) ) {
				include( ABSPATH . 'wp-includes/pluggable.php' );
			}
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
		}

		$this->queue->updateQueueOptions( $jobs );

	}

	/**
	 * @param $payload
	 *
	 * @return string
	 */
	private function applyTemplates( $payload, $name ) {
		$settings = get_option( 'cwp_settings' );
		$vars     = [
			'events'           => $payload,
			'template'         => $settings['cwp_template'],
			'name'             => $name,
			'style'            => '',
			'title'            => 'Custom Notifications',
			'unsubscribe_link' => ''
		];


		ob_start();
		extract( $vars );
		include( 'templates/html.php' );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;

	}
}
