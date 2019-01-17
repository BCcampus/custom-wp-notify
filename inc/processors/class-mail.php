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

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

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
		$subject   = 'Recent Events';
		$limit     = 20;
		$jobs      = $this->queue->getQueueOptions();
		$attempts  = $jobs['attempts'];
		$sent_list = [];
		$now       = date( 'F d, Y g:i A', current_time( 'timestamp' ) );

		// send an email to each recipient
		foreach ( $jobs['list'] as $email => $val ) {
			$to           = $email;
			$sub          = $subject;
			$msg          = $this->applyTemplates( $jobs['payload'], $val );
			$sitename     = strtolower( $_SERVER['SERVER_NAME'] );
			$current_blog = get_option( 'blogname' );

			if ( substr( $sitename, 0, 4 ) === 'www.' ) {
				$sitename = substr( $sitename, 4 );
			}

			$headers = [
				'Content-Type: text/html; charset=UTF-8',
				'From:' . $current_blog . '<no-reply@' . $sitename . '>',
			];

			if ( ! function_exists( 'wp_mail' ) ) {
				include( ABSPATH . 'wp-includes/pluggable.php' );
			}
			$ok = \wp_mail( $to, $sub, $msg, $headers );

			// take the recipient out of the list if it's been successful
			if ( $ok ) {
				// add them to the sent list
				$sent_list[ $email ] = $now;
				unset( $jobs['list'][ $email ] );
			} else {
				\error_log( '\BCcampus\Processors\Mail->maybeRun failed to send a message to ' . $email ); //@codingStandardsIgnoreLine
			}

			if ( -- $limit === 0 ) {
				break;
			}
		}

		// Add the sent list array to the cwp_queue options
		$jobs['sent'] = $sent_list;

		// flag the queue as safe to rebuild
		if ( empty( $jobs['list'] ) ) {
			$jobs['safe_to_rebuild'] = true;
			$jobs['attempts']        = 0;
		} else {
			// update the queue for the next round
			$jobs['attempts'] = $attempts + 1;
		}

		$this->queue->updateQueueOptions( $jobs );

	}

	/**
	 * For testing, sends an email to one address
	 *
	 * @param array $email
	 */
	public function runJustTester( array $email ) {

		$name     = [ 'name' => 'Tester' ];
		$subject  = 'Test Recent Events';
		$jobs     = $this->queue->getQueueOptions();
		$to       = $email;
		$sub      = $subject;
		$msg      = $this->applyTemplates( $jobs['payload'], $name );
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );

		if ( substr( $sitename, 0, 4 ) === 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: Custom WP Notifications <no-reply@' . $sitename . '>',
		];

		if ( ! function_exists( 'wp_mail' ) ) {
			include( ABSPATH . 'wp-includes/pluggable.php' );
		}

		foreach ( $to as $recipient ) {
			$ok = \wp_mail( $recipient, $sub, $msg, $headers );

			// log if no success
			if ( ! $ok ) {
				\error_log( '\BCcampus\Processors\Mail->runJustOne failed to send a message to ' . $recipient ); //@codingStandardsIgnoreLine
			}
		}
	}

	/**
	 * @param $payload
	 *
	 * @param array $user
	 *
	 * @return string
	 */
	private function applyTemplates( $payload, $user ) {
		$settings     = get_option( 'cwp_settings' );
		$template     = get_option( 'cwp_template_settings' );
		$current_blog = get_option( 'blogname' );
		$param        = ( $settings['cwp_param'] ) ? $settings['cwp_param'] : 0;
		$vars         = [
			'events'           => $payload['recent'],
			'custom_events'    => $payload['category'],
			'template'         => html_entity_decode( $template['cwp_template'] ),
			'name'             => $user['name'],
			'preferences'      => $user['event_cats'],
			'style'            => $template['cwp_css'],
			'title'            => 'Custom Notifications',
			'unsubscribe_link' => $template['cwp_unsubscribe'],
			'blogname'         => $current_blog,
			'param'            => $param,
		];

		$vars = $this->placeHolders( $vars );

		$css_file = file_get_contents( $this->getStyleSheetPath() );

		$inline_styles = new CssToInlineStyles();

		ob_start();
		extract( $vars ); //@codingStandardsIgnoreLine
		include( 'templates/html.php' );
		$output = ob_get_contents();
		ob_end_clean();

		$convert = $inline_styles->convert( $output, $css_file );

		return $convert;

	}

	/**
	 * @return string
	 */
	private function getStyleSheetPath() {
		$filename = get_stylesheet_directory() . '/dist/styles/main.css';

		if ( file_exists( $filename ) ) {
			$path = $filename;
		} else {
			$path = get_stylesheet_directory() . '/style.css';
		}

		return $path;
	}

	/**
	 * String replace for placeholders
	 *
	 * @param $vars
	 *
	 * @return mixed
	 */
	private function placeHolders( $vars ) {
		// Events
		$events  = '<ul>';
		$time    = date( 'Y-m-d', current_time( 'timestamp' ) );
		$options = get_option( 'cwp_template_settings' );
		$limit   = $options['cwp_limit'];
		$i       = 0;

		// recent events
		foreach ( $vars['events'] as $event ) {
			$event_title = rawurlencode( str_replace( ' ', '-', $event['title'] ) );
			$campaign    = ( $vars['param'] === 0 ) ? '' : "?pk_campaign=custom-wp-notify-{$time}&pk_kwd={$event_title}";
			$events     .= "<li><a href='{$event['link']}{$campaign}'>{$event['title']}</a></li>";
			$i ++;
			if ( $i >= $limit ) {
				break;
			}
		}

		$events .= '</ul>';

		// custom events
		if ( is_array( $vars['preferences'] ) ) {
			foreach ( $vars['preferences'] as $tax_id ) {
				if ( array_key_exists( $tax_id, $vars['custom_events'] ) && is_array( $vars['custom_events'][ $tax_id ]['posts'] ) ) {
					$events .= sprintf( '<h2>%1$s</h2><ul>', $vars['custom_events'][ $tax_id ]['name'] );

					foreach ( $vars['custom_events'][ $tax_id ]['posts'] as $c_event ) {
						$events .= sprintf( '<li><a href="%1$s">%2$s</a></li>', $c_event['link'], $c_event['title'] );
					}
					$events .= '</ul>';
				}
			}
		}

		// Unsubscribe Link
		$unsubscribe = "<a href='mailto:{$vars['unsubscribe_link']}?subject=remove'>Unsubscribe</a>";

		// Preg Replace
		$vars['template'] = preg_replace( '/{NAME}/', $vars['name'], $vars['template'] );
		$vars['template'] = preg_replace( '/{UNSUBSCRIBE}/', $unsubscribe, $vars['template'] );
		$vars['template'] = preg_replace( '/{EVENTS}/', $events, $vars['template'] );

		return $vars;
	}
}
