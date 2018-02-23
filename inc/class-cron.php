<?php
/**
 * Project: custom-wp-notify
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2018 Brad Payne
 * Date: 2018-02-23
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) Brad Payne
 */

namespace BCcampus;

use BCcampus\Models\Wp;
use BCcampus\Processors;

class Cron {

	/**
	 * Cron constructor
	 */
	public function __construct() {
		add_action( 'cwp_cron_mail_hook', [ $this, 'notifyTheQueue' ] );
		add_action( 'cwp_cron_build_hook', [ $this, 'buildTheQueue' ] );
		add_action( 'init', [ $this, 'scheduleEvents' ] );
		add_filter( 'cron_schedules', [ $this, 'mailInterval' ] );


	}

	/**
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function mailInterval( $schedules ) {
		$schedules['cwp_five_minutes'] = [
			'interval' => ( MINUTE_IN_SECONDS * 5 ),
			'display'  => __( 'Every Five Minutes', 'cwp_notify' ),
		];

		return $schedules;
	}

	/**
	 *
	 */
	public function notifyTheQueue() {
		$u = new Wp\Users();
		$q = new Processors\Queue( $u );
		$m = new Processors\Mail( $q );
		$m->maybeRun();
	}

	/**
	 *
	 */
	public function buildTheQueue() {
		$u = new Wp\Users();
		$q = new Processors\Queue( $u );
		$q->maybeBuild();
	}

	/**
	 *
	 */
	public function scheduleEvents() {
		$b_timestamp = wp_next_scheduled( 'cwp_cron_build_hook' );

		if ( ! $b_timestamp ) {
			wp_schedule_event( time(), 'daily', 'cwp_cron_build_hook' );
		}

		$m_timestamp = wp_next_scheduled( 'cwp_cron_mail_hook' );

		if ( ! $m_timestamp ) {
			wp_schedule_event( time(), 'cwp_five_minutes', 'cwp_cron_mail_hook' );
		}

	}

}