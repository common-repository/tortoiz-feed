<?php
/**
 * Class tf_Instagram_Cron_Updater
 *
 * Finds all regular feed transients saved in the database and updates
 * each cached feed in the background using WP Cron. This is set up with the
 * "tfi_cron_updater" function in the if-functions.php file. The "display_instagram"
 * function will trigger a single feed update if no transient is found
 * for the feed
 *
 * @since 2.0/5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class tf_Instagram_Cron_Updater
{
	/**
	 * Find and loop through all feed cache transients and update the post and
	 * header caches
	 *
	 * Overwritten in the Pro version
	 *
	 * @since 2.0/5.0
	 */
	public static function do_feed_updates() {
		$feed_caches = tf_Instagram_Cron_Updater::get_feed_cache_option_names();
		shuffle(  $feed_caches );
		$settings = tfi_get_database_settings();

		// this is outputted in system info
		$report = array(
			'notes' => array(
				'time_ran' => date( 'Y-m-d H:i:s' ),
				'num_found_transients' => count( $feed_caches )
			)
		);

		foreach ( $feed_caches as $feed_cache ) {

			$feed_id  = str_replace( '_transient_', '', $feed_cache['option_name'] );
			$report[ $feed_id ] = array();

			$transient = get_transient( $feed_id );

			if ( $transient ) {
				$feed_data                  = json_decode( $transient, true );

				// shortcode attributes are saved in order to recreate the feed is needed
				$atts = isset( $feed_data['atts'] ) ? $feed_data['atts'] : false;
				$last_retrieve = isset( $feed_data['last_retrieve'] ) ? (int)$feed_data['last_retrieve'] : 0;
				// the last approximate time the feed was requested to be displayed on a page is recorded
				// in order to stop updating feeds not in use.
				$last_requested = isset( $feed_data['last_requested'] ) ? (int)$feed_data['last_requested'] : false;
				$report[ $feed_id ]['last_retrieve'] = date( 'Y-m-d H:i:s', $last_retrieve );
				if ( $atts !== false ) {

					if ( ! $last_requested || $last_requested > (time() - 60*60*24*30) ) {
						$instagram_feed_settings = new tf_Instagram_Settings( $atts, $settings );

						if ( empty( $settings['connected_accounts'] ) && empty( $atts['accesstoken'] ) ) {
							$report[ $feed_id ]['did_update'] = 'no - no connected account';
						} else {
							tf_Instagram_Cron_Updater::do_single_feed_cron_update( $instagram_feed_settings, $feed_data, $atts );

							$report[ $feed_id ]['did_update'] = 'yes';
						}
					} else {
						$report[ $feed_id ]['did_update'] = 'no - not recently requested';
					}


				} else {
					$report[ $feed_id ]['did_update'] = 'no - missing atts';
				}

			} else {
				$report[ $feed_id ]['did_update'] = 'no - no transient found';
			}

		}

		update_option( 'tfi_cron_report', $report, false );
	}

	/**
	 * Update a single feed cache based on settings. Local image storing and
	 * resizing is done in the background here as well unless this is the initial
	 * time the feed is created and no cached data exists yet.
	 *
	 * Overwritten in the Pro version
	 *
	 * @param array $instagram_feed_settings associative array generated from
	 *  the tf_instagram_settings class
	 * @param array $feed_data post, header, shortcode settings, and other info
	 *  associated with the feed that is saved in the cache
	 * @param array $atts shortcode settings
	 * @param bool $include_resize whether or not to resize images during the update since
	 *  images can also be resized with an ajax call when the feed is viewed on the frontend
	 *
	 * @return object
	 *
	 * @since 2.0/5.0
	 */
	public static function do_single_feed_cron_update( $instagram_feed_settings, $feed_data, $atts, $include_resize = true ) {
		$instagram_feed_settings->set_feed_type_and_terms();
		$instagram_feed_settings->set_transient_name();
		$transient_name = $instagram_feed_settings->get_transient_name();
		$settings = $instagram_feed_settings->get_settings();
		$feed_type_and_terms = $instagram_feed_settings->get_feed_type_and_terms();

		$instagram_feed = new tf_Instagram_Feed( $transient_name );

		while ( $instagram_feed->need_posts( $settings['num'] ) && $instagram_feed->can_get_more_posts() ) {
			$instagram_feed->add_remote_posts( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
		}

		$to_cache = array(
			'atts' => $atts,
			'last_requested' => $feed_data['last_requested'],
			'last_retrieve' => time()
		);

		$instagram_feed->set_cron_cache( $to_cache, $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );

		if ( $instagram_feed->need_header( $settings, $feed_type_and_terms ) ) {
			$instagram_feed->set_remote_header_data( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );

			$instagram_feed->cache_header_data( $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}

		if ( $include_resize ) {
			$post_data = $instagram_feed->get_post_data();
			$post_data = array_slice( $post_data, 0, $settings['num'] );

			if ( $settings['favor_local'] ) {
				$image_sizes = array(
					'personal' => array( 'full' => 640, 'low' => 320 ),
					'business' => array( 'full' => 640, 'low' => 320 )
				);
			} else {
				$image_sizes = array(
					'personal' => array( 'low' => 320 ),
					'business' => array( 'full' => 640, 'low' => 320 )
				);
			}
			$post_set = new tf_Instagram_Post_Set( $post_data, $transient_name, NULL, $image_sizes );

			$post_set->maybe_save_update_and_resize_images_for_posts();
		}

		return $instagram_feed;
	}

	/**
	 * Retrieve option name column values for all feed cache transients
	 *
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public static function get_feed_cache_option_names() {
		global $wpdb;
		$feed_caches = array();

		$results = $wpdb->get_results( "
		SELECT option_name
        FROM $wpdb->options
        WHERE `option_name` LIKE ('%\_transient\_tfi\_%')
        AND `option_name` NOT LIKE ('%\_transient\_tfi\_header%');", ARRAY_A );

		if ( isset( $results[0] ) ) {
			$feed_caches = $results;
		}

		return $feed_caches;
	}

	/**
	 * Start cron jobs based on user's settings for cron cache update frequency.
	 * This is triggered when settings are saved on the "Configure" tab.
	 *
	 * @param string $tfi_cache_cron_interval arbitrary name from one of the
	 *  settings on the "Configure" tab
	 * @param string $tfi_cache_cron_time hour of the day (1 = 1:00)
	 * @param string $tfi_cache_cron_am_pm am or pm (time of day)
	 *
	 * @since 2.0/5.0
	 */
	public static function start_cron_job( $tfi_cache_cron_interval, $tfi_cache_cron_time, $tfi_cache_cron_am_pm ) {
		wp_clear_scheduled_hook( 'tfi_feed_update' );

		if ( $tfi_cache_cron_interval === '12hours' || $tfi_cache_cron_interval === '24hours' ) {
			$relative_time_now = time() + tfi_get_utc_offset();
			$base_day = strtotime( date( 'Y-m-d', $relative_time_now ) );
			$add_time = $tfi_cache_cron_am_pm === 'pm' ? (int)$tfi_cache_cron_time + 12 : (int)$tfi_cache_cron_time;
			$utc_start_time = $base_day + (($add_time * 60 * 60) - tfi_get_utc_offset());

			if ( $utc_start_time < time() ) {
				if ( $tfi_cache_cron_interval === '12hours' ) {
					$utc_start_time += 60*60*12;
				} else {
					$utc_start_time += 60*60*24;
				}
			}

			if ( $tfi_cache_cron_interval === '12hours' ) {
				wp_schedule_event( $utc_start_time, 'twicedaily', 'tfi_feed_update' );
			} else {
				wp_schedule_event( $utc_start_time, 'daily', 'tfi_feed_update' );
			}

		} else {

			if ( $tfi_cache_cron_interval === '30mins' ) {
				wp_schedule_event( time(), 'tfi30mins', 'tfi_feed_update' );
			} else {
				wp_schedule_event( time(), 'hourly', 'tfi_feed_update' );
			}
		}

	}

}