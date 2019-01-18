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

use BCcampus\Models\Em;
use BCcampus\Models\Wp;

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
		if ( $em_events->getRecentGroupedEvents() ) {
			$this->events = $em_events;
		}
		// TODO add else to get API Events
	}

	/**
	 * @return bool
	 */
	private function verify() {
		$ok            = true;
		$already_built = get_option( 'cwp_queue' );

		// have new events since the last mailout?
		if ( empty( $this->events ) ) {
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
	 * Maybe build the queue
	 *
	 * @param bool $force
	 */
	public function maybeBuild( $force = false ) {
		if ( false === $this->verify() && false === $force ) {
			return;
		}

		// safe_to_rebuild = true prevents it from being mailed out
		$safe               = ( true === $force ) ? true : false;
		$events['recent']   = $this->events->getTitlesAndLinks( $this->events->getRecentGroupedEvents() );
		$events['category'] = [];
		$cats               = [];

		// gather all the term_taxonomy_ids, returns
		//  0 = ['term_id' => '20','name' => 'category name']
		$categories = $this->events->getEventCategories();

		if ( is_array( $categories ) ) {
			foreach ( $categories as $key => $value ) {
				$titles_and_links = [];
				$by_cat           = $this->events->getRecentEventsByCategory( $value['term_id'] );
				if ( is_array( $by_cat ) ) {
					$clean            = $this->cleanRecentEvents( $by_cat );
					$titles_and_links = $this->events->getTitlesAndLinks( $clean );
				}

				$cats[ $value['term_id'] ] = [
					'name'  => $categories[ $key ]['name'],
					'posts' => $titles_and_links,
				];
			};
		}

		$events['category'] = $cats;
		$queue              = [
			'queue'           => 'cwp_notify',
			'attempts'        => 0,
			'safe_to_rebuild' => $safe,
			'created_at'      => time(),
			'list'            => $this->users->getUserList(),
			'payload'         => $events,
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

	/**
	 * @param array $events
	 *
	 * @return array
	 */
	private function cleanRecentEvents( array $events ) {
		$clean = [];

		foreach ( $events as $event ) {
			$clean[]['post_id'] = $event['ID'];
		}

		return $clean;
	}
}
