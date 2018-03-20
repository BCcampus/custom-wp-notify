<?php
/**
 * Project: custom-wp-notify
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright Brad Payne <https://bradpayne.ca>
 * Date: 2018-02-19
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) Brad Payne
 */

namespace BCcampus\Models\Posts;


class CwpPosts {


	public function __construct() {

	}

	/**
	 * Custom query for recent events associated with the Events Manager Plugin
	 *
	 * Will return an associative array of post_id for recently posted events
	 *
	 * @return array|null
	 */
	public function getRecentEvents() {
		global $wpdb;
		$today = date( 'Y-m-d', time() );
		$limit = 4;

		$sanitized_query = $wpdb->prepare( "SELECT DISTINCT SQL_CALC_FOUND_ROWS {$wpdb->prefix}em_events.post_id FROM {$wpdb->prefix}em_events
  LEFT JOIN {$wpdb->prefix}em_locations ON {$wpdb->prefix}em_locations.location_id={$wpdb->prefix}em_events.location_id
WHERE (`event_status`=1) AND (`recurrence`!=1 OR `recurrence` IS NULL) AND (`event_private`=0 OR (`event_private`=1 AND (`group_id` IS NULL OR `group_id` = 0)) OR (`event_private`=1 AND `group_id` IN (1))) AND  (event_start_date > CAST(%s AS DATE))
ORDER BY event_start_date ASC, event_start_time ASC, event_name ASC
LIMIT %d OFFSET 0", $today, $limit );

		$results = $wpdb->get_results( $sanitized_query, ARRAY_A );

		return $results;
	}


	/**
	 * Get posts
	 *
	 * Will return an associative array of posts
	 *
	 * @return array
	 */
	public function getRecentPosts() {

		// post args
		$args = [
			'posts_per_page' => 5,
			'offset'         => 0,
		];

		// post query
		$results = get_posts( $args );

		return $results;
	}

	/**
	 * Given and array of post ids will retrieve titles and links
	 *
	 * @param array $post_ids
	 *
	 * @return array of titles (key) and links (val)
	 */
	public function getTitlesAndLinks( array $post_ids ) {
		$posts = [];
		foreach ( $post_ids as $v ) {
			$p                               = get_post( $v['post_id'] );
			$posts[ $v['post_id'] ]['title'] = $p->post_title;
			$posts[ $v['post_id'] ]['link']  = urlencode( $p->guid );
		}

		return $posts;
	}


}
