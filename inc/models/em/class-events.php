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

namespace BCcampus\Models\Em;

class Events {


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

		$sanitized_query = $wpdb->prepare(
			"SELECT DISTINCT SQL_CALC_FOUND_ROWS {$wpdb->prefix}em_events.post_id FROM {$wpdb->prefix}em_events 
					LEFT JOIN {$wpdb->prefix}em_locations ON {$wpdb->prefix}em_locations.location_id={$wpdb->prefix}em_events.location_id
					WHERE (`event_status`=1) 
					AND (`recurrence`!=1 OR `recurrence` IS NULL) 
					AND (`event_private`=0 OR (`event_private`=1 AND (`group_id` IS NULL OR `group_id` = 0)) OR (`event_private`=1 AND `group_id` IN (1))) 
					AND  (event_start_date > CAST(%s AS DATE))
					ORDER BY event_start_date ASC, event_start_time ASC, event_name ASC OFFSET 0", $today
		);

		$results = $wpdb->get_results( $sanitized_query, ARRAY_A );

		return $results;
	}

	/**
	 * @return array|null|object
	 */
	public function getRecentGroupedEvents() {
		global $wpdb;
		$today = date( 'Y-m-d', time() );

		$sanitized_query = $wpdb->prepare(
			"SELECT DISTINCT SQL_CALC_FOUND_ROWS {$wpdb->prefix}em_events.post_id
					FROM (
						SELECT {$wpdb->prefix}em_events.*,
							@cur := IF({$wpdb->prefix}em_events.location_id = @id, @cur+1, 1) AS RowNumber,
							@id := {$wpdb->prefix}em_events.location_id AS IdCache
						FROM {$wpdb->prefix}em_events
						INNER JOIN (
							SELECT @id:=0, @cur:=0
						) AS lookup
						
						 WHERE (`event_status`=1) AND (`recurrence`!=1 OR `recurrence` IS NULL) AND ( event_start >= CAST(%s AS DATE) OR (event_end >= CAST(%s AS DATE))) AND (`event_private`=0 OR (`event_private`=1 AND (`group_id` IS NULL OR `group_id` = 0)) OR (`event_private`=1 AND `group_id` IN (1)))
						ORDER BY {$wpdb->prefix}em_events.location_id , event_date_created DESC
						) {$wpdb->prefix}em_events
					WHERE RowNumber = 1
					ORDER BY event_date_created DESC;", $today, $today
		);

		$results = $wpdb->get_results( $sanitized_query, ARRAY_A );

		return $results;
	}

	/**
	 * @param int $taxonomy_id
	 *
	 * @return array|null|object
	 */
	public function getRecentCategoryEvents( int $taxonomy_id ) {
		global $wpdb;
		$today = date( 'Y-m-d', time() );

		$sanitized_query = $wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS  {$wpdb->prefix}posts.ID FROM {$wpdb->prefix}posts  LEFT JOIN {$wpdb->prefix}term_relationships ON ({$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id) INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id )  INNER JOIN {$wpdb->prefix}postmeta AS mt1 ON ( {$wpdb->prefix}posts.ID = mt1.post_id ) WHERE 1=1  AND ( 
  					{$wpdb->prefix}term_relationships.term_taxonomy_id IN ( $taxonomy_id )
					) AND ( 
					  {$wpdb->prefix}postmeta.meta_key = '_event_start_local' 
					  AND 
					  ( 
					    ( mt1.meta_key = '_event_end' AND CAST(mt1.meta_value AS DATETIME) > %s )
					  )
					) AND {$wpdb->prefix}posts.post_type = 'event' AND ({$wpdb->prefix}posts.post_status = 'publish' OR {$wpdb->prefix}posts.post_status = 'private') GROUP BY {$wpdb->prefix}posts.ID ORDER BY CAST({$wpdb->prefix}postmeta.meta_value AS DATETIME) ASC LIMIT 0, 10", $today
				);

		$results = $wpdb->get_results( $sanitized_query, ARRAY_A );

		return $results;
	}

	/**
	 * @return array|null|object
	 */
	public function getEventCategories() {
		global $wpdb;

		$sanitized_query = $wpdb->prepare(
			"SELECT {$wpdb->prefix}term_taxonomy.term_id,{$wpdb->prefix}terms.name FROM {$wpdb->prefix}term_taxonomy
					INNER JOIN {$wpdb->prefix}terms on ({$wpdb->prefix}term_taxonomy.term_id = {$wpdb->prefix}terms.term_id)
					WHERE {$wpdb->prefix}term_taxonomy.taxonomy = 'event-categories'"
		);

		$results = $wpdb->get_results( $sanitized_query, ARRAY_A );

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
			$posts[ $v['post_id'] ]['link']  = $p->guid;
		}

		return $posts;
	}


}
