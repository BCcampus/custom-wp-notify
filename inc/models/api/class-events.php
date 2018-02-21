<?php
/**
 * Project: custom-wp-notify
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright Brad Payne <https://bradpayne.ca>
 * Date: 2018-02-20
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) Brad Payne
 */

namespace BCcampus\Models\Api;


class Events {
	private $path = '/wp-json/wp/v2/event/';
	private $host = '';

	public function __construct() {
		$this->host = network_site_url();

	}


	/**
	 *
	 * @return array|mixed|object
	 */
	public function getRecentEvents() {
		$endpoint = $this->host . $this->path;
		$json     = [];
		$response = wp_remote_get( $endpoint );

		if ( is_wp_error( $response ) ) {
			error_log( '\BCcampus\Models\Events->getRecentEventsRest(), something wrong with the rest endpoint' . $endpoint . $response->get_error_message() );
		}

		if ( $response['response']['code'] < 400 ) {
			$json = json_decode( $response['body'], true );
		}

		return $json;
	}

	public function getTitlesAndLinks( array $post_ids ) {

	}
}
