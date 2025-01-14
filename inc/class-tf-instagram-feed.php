<?php
/**
 * Class tf_Instagram_Feed
 *
 * Retrieves data and generates the html for each feed. The
 * "display_instagram" function in the if-functions.php file
 * is where this class is primarily used.
 *
 * @since 2.0/4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class tf_Instagram_Feed
{
	/**
	 * @var string
	 */
	private $regular_feed_transient_name;

	/**
	 * @var string
	 */
	private $header_transient_name;

	/**
	 * @var string
	 */
	private $backup_feed_transient_name;

	/**
	 * @var string
	 */
	private $backup_header_transient_name;

	/**
	 * @var array
	 */
	private $post_data;

	/**
	 * @var
	 */
	private $header_data;

	/**
	 * @var array
	 */
	private $next_pages;

	/**
	 * @var array
	 */
	private $transient_atts;

	/**
	 * @var int
	 */
	private $last_retrieve;

	/**
	 * @var bool
	 */
	private $should_paginate;

	/**
	 * @var int
	 */
	private $num_api_calls;

	/**
	 * @var array
	 */
	private $image_ids_post_set;

	/**
	 * @var bool
	 */
	private $should_use_backup;

	/**
	 * @var array
	 */
	private $report;

	/**
	 * @var array
	 *
	 * @since 2.1.1/5.2.1
	 */
	private $resized_images;

	/**
	 * @var array
	 *
	 * @since 2.1.3/5.2.3
	 */
	protected $one_post_found;

	/**
	 * tf_Instagram_Feed constructor.
	 *
	 * @param string $transient_name ID of this feed
	 *  generated in the tf_Instagram_Settings class
	 */
	public function __construct( $transient_name ) {
		$this->regular_feed_transient_name = $transient_name;
		$this->backup_feed_transient_name = tfi_BACKUP_PREFIX . $transient_name;

		$tfi_header_transient_name = str_replace( 'tfi_', 'tfi_header_', $transient_name );
		$tfi_header_transient_name = substr($tfi_header_transient_name, 0, 44);
		$this->header_transient_name = $tfi_header_transient_name;
		$this->backup_header_transient_name = tfi_BACKUP_PREFIX . $tfi_header_transient_name;

		$this->post_data = array();
		$this->next_pages = array();
		$this->should_paginate = true;

		// this is a count of how many api calls have been made for each feed
		// type and term.
		// By default the limit is 10
		$this->num_api_calls = 0;
		$this->max_api_calls = apply_filters( 'tfi_max_concurrent_api_calls', 10 );
		$this->should_use_backup = false;

		// used for errors and the tfi_debug report
		$this->report = array();

		$this->resized_images = array();

		$this->one_post_found = false;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_post_data() {
		return $this->post_data;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function set_post_data( $post_data ) {
		$this->post_data = $post_data;
	}

	/**
	 * @return array
	 *
	 * @since 2.1.1/5.2.1
	 */
	public function set_resized_images( $resized_image_data ) {
		$this->resized_images = $resized_image_data;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_next_pages() {
		return $this->next_pages;
	}

	/**
	 * @return array
	 *
	 * @since 2.1.1/5.2.1
	 */
	public function get_resized_images() {
		return $this->resized_images;
	}

	/**
	 * Checks the database option related the transient expiration
	 * to ensure it will be available when the page loads
	 *
	 * @return bool
	 *
	 * @since 2.0/4.0
	 */
	public function regular_cache_exists() {
		//Check whether the cache transient exists in the database and is available for more than one more minute
		$transient_exists = get_transient( $this->regular_feed_transient_name );

		return $transient_exists;
	}

	/**
	 * Checks the database option related the header transient
	 * expiration to ensure it will be available when the page loads
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function regular_header_cache_exists() {
		$header_transient = get_transient( $this->header_transient_name );

		return $header_transient;
	}

	/**
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function should_use_backup() {
		return $this->should_use_backup || empty( $this->post_data );
	}

	/**
	 * The header is only displayed when the setting is enabled and
	 * an account has been connected
	 *
	 * Overwritten in the Pro version
	 *
	 * @param array $settings settings specific to this feed
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function need_header( $settings, $feed_types_and_terms ) {
		$showheader = ($settings['showheader'] === 'on' || $settings['showheader'] === 'true' || $settings['showheader'] === true);
		return ($showheader && isset( $feed_types_and_terms['users'] ));
	}

	/**
	 * Use the transient name to retrieve cached data for header
	 *
	 * @since 2.0/5.0
	 */
	public function set_header_data_from_cache() {
		$header_cache = get_transient( $this->header_transient_name );

		$header_cache = json_decode( $header_cache, true );

		if ( ! empty( $header_cache ) ) {
			$this->header_data = $header_cache;
		}
	}

	public function set_header_data( $header_data ) {
		$this->header_data = $header_data;
	}

	/**
	 * @since 2.0/5.0
	 */
	public function get_header_data() {
		return $this->header_data;
	}

	/**
	 * Sets the post data, pagination data, shortcode atts used (cron cache),
	 * and timestamp of last retrieval from transient (cron cache)
	 *
	 * @param array $atts available for cron caching
	 *
	 * @since 2.0/5.0
	 */
	public function set_post_data_from_cache( $atts = array() ) {
		$transient_data = get_transient( $this->regular_feed_transient_name );

		$transient_data = json_decode( $transient_data, true );

		if ( $transient_data ) {
			$post_data = isset( $transient_data['data'] ) ? $transient_data['data'] : array();
			$this->post_data = $post_data;
			$this->next_pages = isset( $transient_data['pagination'] ) ? $transient_data['pagination'] : array();

			if ( isset( $transient_data['atts'] ) ) {
				$this->transient_atts = $transient_data['atts'];
				$this->last_retrieve = $transient_data['last_retrieve'];
			}
		}
	}

	/**
	 * Sets post data from a permanent database backup of feed
	 * if it was created
	 *
	 * @since 2.0/5.0
	 * @since 2.0/5.1.2 if backup feed data used, header data also set from backup
	 */
	public function maybe_set_post_data_from_backup() {
		$backup_data = get_option( $this->backup_feed_transient_name, false );

		if ( $backup_data ) {
			$backup_data = json_decode( $backup_data, true );

			$post_data = isset( $backup_data['data'] ) ? $backup_data['data'] : array();
			$this->post_data = $post_data;
			$this->next_pages = isset( $backup_data['pagination'] ) ? $backup_data['pagination'] : array();

			if ( isset( $backup_data['atts'] ) ) {
				$this->transient_atts = $backup_data['atts'];
				$this->last_retrieve = $backup_data['last_retrieve'];
			}

			$this->maybe_set_header_data_from_backup();

			return true;
		} else {
			$this->add_report( 'no backup post data found' );

			return false;
		}
	}

	/**
	 * Sets header data from a permanent database backup of feed
	 * if it was created
	 *
	 * @since 2.0/5.0
	 */
	public function maybe_set_header_data_from_backup() {
		$backup_header_data = get_option( $this->backup_header_transient_name, false );

		if ( ! empty( $backup_header_data ) ) {
			$backup_header_data = json_decode( $backup_header_data, true );
			$this->header_data = $backup_header_data;

			return true;
		} else {
			$this->add_report( 'no backup header data found' );

			return false;
		}
	}

	/**
	 * Returns recorded image IDs for this post set
	 * for use with image resizing
	 *
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_image_ids_post_set() {
		return $this->image_ids_post_set;
	}

	/**
	 * Retrieves data related to resized images from custom
	 * tables using either a number, offset, and transient name
	 * or the ids of the posts.
	 *
	 * Retrieving by offset and transient name not used currently
	 * but may be needed in future updates.
	 *
	 * @param array/int $num_or_array_of_ids post ids from the Instagram
	 *  API
	 * @param int $offset number of records to skip
	 * @param string $transient_name ID of the feed
	 *
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public static function get_resized_images_source_set( $num_or_array_of_ids, $offset = 0, $transient_name = '' ) {
		global $tf_instagram_posts_manager;

		if ( $tf_instagram_posts_manager->image_resizing_disabled() ) {
			return array();
		}

		global $wpdb;

		$posts_table_name = $wpdb->prefix . tfi_INSTAGRAM_POSTS_TYPE;
		$feeds_posts_table_name = $wpdb->prefix . tfi_INSTAGRAM_FEEDS_POSTS;

		$feed_id_array = explode( '#', $transient_name );
		$feed_id = $feed_id_array[0];

		if ( is_array( $num_or_array_of_ids ) ) {
			$ids = $num_or_array_of_ids;

			$id_string = "'" . implode( "','", $ids ) . "'";
			$results = $wpdb->get_results( $wpdb->prepare( "
			SELECT p.media_id, p.instagram_id, p.aspect_ratio, p.sizes
			FROM $posts_table_name AS p 
			INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id 
			WHERE f.feed_id = %s
			AND p.instagram_id IN($id_string)
		  	AND p.images_done = 1", $feed_id ), ARRAY_A );

			$return = array();
			if ( !empty( $results ) && is_array( $results ) ) {

				foreach ( $results as $result ) {
					$sizes = maybe_unserialize( $result['sizes'] );
					if ( ! is_array( $sizes ) ) {
						$sizes = array( 'full' => 640 );
					}
					$return[ $result['instagram_id'] ] = array(
						'id' => $result['media_id'],
						'ratio' => $result['aspect_ratio'],
						'sizes' => $sizes
					);
				}

			}
		} else {
			$num = $num_or_array_of_ids;

			$results = $wpdb->get_results( $wpdb->prepare( "
			SELECT p.media_id, p.instagram_id, p.aspect_ratio, p.sizes
			FROM $posts_table_name AS p 
			INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id 
			WHERE f.feed_id = %s
		  	AND p.images_done = 1
			ORDER BY p.time_stamp
			DESC LIMIT %d, %d", $feed_id, $offset, (int)$num ), ARRAY_A );

			$return = array();
			if ( !empty( $results ) && is_array( $results ) ) {

				foreach ( $results as $result ) {
					$sizes = maybe_unserialize( $result['sizes'] );
					if ( ! is_array( $sizes ) ) {
						$sizes = array( 'full' => 640 );
					}
					$return[ $result['instagram_id'] ] = array(
						'id' => $result['media_id'],
						'ratio' => $result['aspect_ratio'],
						'sizes' => $sizes
					);
				}

			}
		}


		return $return;
	}

	public static function update_last_requested( $array_of_ids ) {
		if ( empty( $array_of_ids ) ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . tfi_INSTAGRAM_POSTS_TYPE;
		$id_string =  "'" . implode( "','", $array_of_ids ) . "'";

		$query = $wpdb->query( $wpdb->prepare( "UPDATE $table_name
		SET last_requested = %s
		WHERE instagram_id IN ({$id_string});", date( 'Y-m-d H:i:s' ) ) );
	}

	/**
	 * Cron caching needs additional data saved in the transient
	 * to work properly. This function checks to make sure it's present
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function need_to_start_cron_job() {
		return (( ! empty( $this->post_data ) && ! isset( $this->transient_atts )) || empty( $this->post_data ));
	}

	/**
	 * Checks to see if there are enough posts available to create
	 * the current page of the feed
	 *
	 * @param int $num
	 * @param int $offset
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function need_posts( $num, $offset = 0 ) {
		$num_existing_posts = is_array( $this->post_data ) ? count( $this->post_data ) : 0;
		$num_needed_for_page = (int)$num + (int)$offset;

		($num_existing_posts < $num_needed_for_page) ? $this->add_report( 'need more posts' ) : $this->add_report( 'have enough posts' );

		return ($num_existing_posts < $num_needed_for_page);
	}

	/**
	 * Checks to see if there are additional pages available for any of the
	 * accounts in the feed and that the max conccurrent api request limit
	 * has not been reached
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function can_get_more_posts() {
		$one_type_and_term_has_more_ages = $this->next_pages !== false;
		$max_concurrent_api_calls_not_met = $this->num_api_calls < $this->max_api_calls;
		$max_concurrent_api_calls_not_met ? $this->add_report( 'max conccurrent requests not met' ) : $this->add_report( 'max concurrent met' );
		$one_type_and_term_has_more_ages ? $this->add_report( 'more pages available' ) : $this->add_report( 'no next page' );

		return ($one_type_and_term_has_more_ages && $max_concurrent_api_calls_not_met);
	}

	/**
	 * Appends one filtered API request worth of posts for each feed term
	 *
	 * @param $settings
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 * @param array $connected_accounts_for_feed connected account data for the
	 *  feed types and terms
	 *
	 * @since 2.0/5.0
	 * @since 2.0/5.1 added logic to make a second attempt at an API connection
	 * @since 2.0/5.1.2 remote posts only retrieved if API requests are not
	 *  delayed, terms shuffled if there are more than 5
	 */
	public function add_remote_posts( $settings, $feed_types_and_terms, $connected_accounts_for_feed ) {
		$new_post_sets = array();
		$next_pages = $this->next_pages;
		global $tf_instagram_posts_manager;

		/**
		 * Number of posts to retrieve in each API call
		 *
		 * @param int               Minimum number of posts needed in each API request
		 * @param array $settings   Settings for this feed
		 *
		 * @since 2.0/5.0
		 */
		$num = apply_filters( 'tfi_num_in_request', $settings['minnum'], $settings );
		$num = max( $num, (int)$settings['apinum'] );
		$params = array(
			'num' => $num
		);

		$one_successful_connection = false;
		$one_post_found = false;
		$next_page_found = false;
		$one_api_request_delayed = false;

		foreach ( $feed_types_and_terms as $type => $terms ) {
			if ( is_array( $terms ) && count( $terms ) > 5 ) {
				shuffle( $terms );
			}
			foreach ( $terms as $term_and_params ) {
				$term = $term_and_params['term'];
				$params = array_merge( $params, $term_and_params['params'] );
				$connected_account_for_term = $connected_accounts_for_feed[ $term ];

				$api_requests_delayed = $tf_instagram_posts_manager->are_current_api_request_delays( $connected_account_for_term['user_id'] );

				if ( ! $api_requests_delayed
				     && (! isset( $next_pages[ $term . '_' . $type ] ) || $next_pages[ $term . '_' . $type ] !== false) ) {
					if ( ! empty( $next_pages[ $term . '_' . $type ] ) ) {
						$connection = $this->make_api_connection( $next_pages[ $term . '_' . $type ] );
					} else {
						$connection = $this->make_api_connection( $connected_account_for_term, $type, $params );
					}
					$this->add_report( 'api call made for ' . $term . ' - ' . $type );

					$connection->connect();
					$this->num_api_calls++;

					if ( ! $connection->is_wp_error() && ! $connection->is_instagram_error() ) {
						$one_successful_connection = true;

						$data = $connection->get_data();

						if ( !$connected_account_for_term['is_valid'] ) {
							$this->add_report( 'clearing invalid token' );
							$this->clear_expired_access_token_notice( $connected_account_for_term );
						}

						if ( isset( $data[0]['id'] ) ) {
							$one_post_found = true;

							$post_set = $this->filter_posts( $data, $settings );

							$new_post_sets[] = $post_set;
						}

						$next_page = $connection->get_next_page();
						if ( ! empty( $next_page ) ) {
							$next_pages[ $term . '_' . $type ] = $next_page;
							$next_page_found = true;
						} else {
							$next_pages[ $term . '_' . $type ] = false;
						}
					} else {

						if ( $this->can_try_another_request( $type, $connected_accounts_for_feed[ $term ] ) ) {
							$this->add_report( 'trying other accounts' );
							$i = 0;
							$attempted = array( $connected_accounts_for_feed[ $term ]['user_id'] );
							$success = false;
							$different = true;
							$error = false;

							while ( $different
							        && ! $success
							        && $this->can_try_another_request( $type, $connected_accounts_for_feed[ $term ], $i ) ) {
								$different = $this->get_different_connected_account( $type, $attempted );
								$this->add_report( 'trying the account ' . $different['user_id'] );

								if ( $different ) {
									$connected_accounts_for_feed[ $term ] = $this->get_different_connected_account( $type, $attempted );
									$attempted[] = $connected_accounts_for_feed[ $term ]['user_id'];
									if ( ! empty( $next_pages[ $term . '_' . $type ] ) ) {
										$connection = $this->make_api_connection( $next_pages[ $term . '_' . $type ] );
									} else {
										$connection = $this->make_api_connection( $connected_accounts_for_feed[ $term ], $type, $params );
									}
									$connection->connect();
									$this->num_api_calls++;
									if ( ! $connection->is_wp_error() && ! $connection->is_instagram_error() ) {
										$one_successful_connection = true;
										$data = $connection->get_data();
										if ( isset( $data[0]['id'] ) ) {
											$one_post_found = true;
											$post_set = $this->filter_posts( $data, $settings );
											$new_post_sets[] = $post_set;
										}
										$next_page = $connection->get_next_page();
										if ( ! empty( $next_page ) ) {
											$next_pages[ $term . '_' . $type ] = $next_page;
											$next_page_found = true;
										} else {
											$next_pages[ $term . '_' . $type ] = false;
										}
									} else {
										if ( $connection->is_wp_error() ) {
											$error = $connection->get_wp_error();
										} else {
											$error = $connection->get_data();
										}
									}
									$i++;
								}
							}

							if ( ! $success && $error ) {
								if ( is_wp_error( $error ) ) {
									tf_Instagram_API_Connect::handle_wp_remote_get_error( $error );
								} else {
									tf_Instagram_API_Connect::handle_instagram_error( $error, $connected_accounts_for_feed[ $term ], $type );
								}
								$next_pages[ $term . '_' . $type ] = false;
							}
						} else {

							if ( $connection->is_wp_error() ) {
								tf_Instagram_API_Connect::handle_wp_remote_get_error( $connection->get_wp_error() );
							} else {
								tf_Instagram_API_Connect::handle_instagram_error( $connection->get_data(), $connected_accounts_for_feed[ $term ], $type );
							}

							$next_pages[ $term . '_' . $type ] = false;
						}
					}
				} elseif ( $api_requests_delayed ) {
					$one_api_request_delayed = true;

					$this->add_report( 'delaying API request for ' . $term . ' - ' . $type );

					$error = '<p><b>' . sprintf( __( 'Error: API requests are being delayed for this account.', 'tortoiz-instagram-feed' ), $connected_account_for_term['username'] ) . ' ' . __( 'New posts will not be retrieved.', 'tortoiz-instagram-feed' ) . '</b></p>';
					$errors = $tf_instagram_posts_manager->get_errors();
					if ( ! empty( $errors )  && current_user_can( 'manage_options' ) ) {
						if ( isset( $errors['api'] ) ) {
							$error .= '<p>' . $errors['api'][1] . '</p>';
						} elseif ( isset( $errors['connection'] ) ) {
							$error .= '<p>' . $errors['connection'][1] . '</p>';
						} // https://smashballoon.com/instagram-feed/docs/errors/
						$error .= '<p><a href="https://smashballoon.com/instagram-feed/docs/errors/">' . __( 'Click here to troubleshoot', 'tortoiz-instagram-feed' ) . '</a></p>';
					} else {
						$error .= '<p>' . __( 'There may be an issue with the Instagram access token that you are using. Your server might also be unable to connect to Instagram at this time.', 'tortoiz-instagram-feed' ) . '</p>';
					}

					$tf_instagram_posts_manager->add_frontend_error( 'at_' . $connected_account_for_term['username'], $error );
				}

			}
		}

		if ( ! $one_successful_connection || ($one_api_request_delayed && empty( $new_post_sets )) ) {
			$this->should_use_backup = true;
		}
		$posts = $this->merge_posts( $new_post_sets, $settings );

		$posts = $this->sort_posts( $posts, $settings );

		if ( ! empty( $this->post_data ) && is_array( $this->post_data ) ) {
			$posts = array_merge( $this->post_data, $posts );
		} elseif ( $one_post_found ) {
			$this->one_post_found = true;
		}

		$this->post_data = $posts;

		if ( isset( $next_page_found ) && $next_page_found ) {
			$this->next_pages = $next_pages;
		} else {
			$this->next_pages = false;
		}
	}

	/**
	 * Connects to the Instagram API and records returned data
	 *
	 * @param $settings
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 * @param array $connected_accounts_for_feed connected account data for the
	 *  feed types and terms
	 *
	 * @since 2.0/5.0
	 */
	public function set_remote_header_data( $settings, $feed_types_and_terms, $connected_accounts_for_feed ) {
		$first_user = $this->get_first_user( $feed_types_and_terms );
		$this->header_data = false;

		if ( isset( $connected_accounts_for_feed[ $first_user ] ) ) {
			$connection = new tf_Instagram_API_Connect( $connected_accounts_for_feed[ $first_user ], 'header', array() );

			$connection->connect();

			if ( ! $connection->is_wp_error() && ! $connection->is_instagram_error() ) {
				$this->header_data = $connection->get_data();

				if ( isset( $connected_accounts_for_feed[ $first_user ]['local_avatar'] ) && $connected_accounts_for_feed[ $first_user ]['local_avatar'] ) {
					$upload = wp_upload_dir();
					$resized_url = trailingslashit( $upload['baseurl'] ) . trailingslashit( tfi_UPLOADS_NAME );

					$full_file_name = $resized_url . $this->header_data['username']  . '.jpg';
					$this->header_data['local_avatar'] = $full_file_name;
				}
			} else {
				if ( $connection->is_wp_error() ) {
					tf_Instagram_API_Connect::handle_wp_remote_get_error( $connection->get_wp_error() );
				} else {
					tf_Instagram_API_Connect::handle_instagram_error( $connection->get_data(), $connected_accounts_for_feed[ $first_user ], 'header' );
				}
			}
		}
	}

	/**
	 * Stores feed data in a transient for a specified time
	 *
	 * @param int $cache_time
	 * @param bool $save_backup
	 *
	 * @since 2.0/5.0
	 * @since 2.0/5.1 duplicate posts removed
	 */
	public function cache_feed_data( $cache_time, $save_backup = true ) {
		if ( ! empty( $this->post_data ) || ! empty( $this->next_pages ) ) {
			$this->remove_duplicate_posts();
			$this->trim_posts_to_max();

			$to_cache = array(
				'data' => $this->post_data,
				'pagination' => $this->next_pages
			);

			set_transient( $this->regular_feed_transient_name, wp_json_encode( $to_cache ), $cache_time );

			if ( $save_backup ) {
				update_option( $this->backup_feed_transient_name, wp_json_encode( $to_cache ), false );
			}
		} else {
			$this->add_report( 'no data not caching' );
		}
	}

	/**
	 * Stores feed data with additional data specifically for cron caching
	 *
	 * @param array $to_cache feed data with additional things like the shortcode
	 *  settings, when the cache was last requested, when new posts were last retrieved
	 * @param int $cache_time how long the cache will last
	 * @param bool $save_backup whether or not to also save this as a permanent cache
	 *
	 * @since 2.0/5.0
	 * @since 2.0/5.1 duplicate posts removed, cache set trimmed to a maximum
	 */
	public function set_cron_cache( $to_cache, $cache_time, $save_backup = true ) {
		if ( ! empty( $this->post_data )
		     || ! empty( $this->next_pages )
		     || ! empty( $to_cache['data'] ) ) {
			$this->remove_duplicate_posts();
			$this->trim_posts_to_max();

			$to_cache['data'] = isset( $to_cache['data'] ) ? $to_cache['data'] : $this->post_data;
			$to_cache['pagination'] = isset( $to_cache['next_pages'] ) ? $to_cache['next_pages'] : $this->next_pages;
			$to_cache['atts'] = isset( $to_cache['atts'] ) ? $to_cache['atts'] : $this->transient_atts;
			$to_cache['last_requested'] = isset( $to_cache['last_requested'] ) ? $to_cache['last_requested'] : time();
			$to_cache['last_retrieve'] = isset( $to_cache['last_retrieve'] ) ? $to_cache['last_retrieve'] : $this->last_retrieve;

			set_transient( $this->regular_feed_transient_name, wp_json_encode( $to_cache ), $cache_time );

			if ( $save_backup ) {
				update_option( $this->backup_feed_transient_name, wp_json_encode( $to_cache ), false );
			}
		} else {
			$this->add_report( 'no data not caching' );
		}

	}

	/**
	 * Stores header data for a specified time as a transient
	 *
	 * @param int $cache_time
	 * @param bool $save_backup
	 *
	 * @since 2.0/5.0
	 */
	public function cache_header_data( $cache_time, $save_backup = true ) {
		if ( $this->header_data ) {
			set_transient( $this->header_transient_name, wp_json_encode( $this->header_data ), $cache_time );

			if ( $save_backup ) {
				update_option( $this->backup_header_transient_name, wp_json_encode( $this->header_data ), false );
			}
		}
	}

	/**
	 * Used to randomly trigger an updating of the last requested data for cron caching
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function should_update_last_requested() {
		return (rand( 1, 20 ) === 20);
	}

	/**
	 * Determines if pagination can and should be used based on settings and available feed data
	 *
	 * @param array $settings
	 * @param int $offset
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function should_use_pagination( $settings, $offset = 0 ) {
		$posts_available = count( $this->post_data ) - ($offset + $settings['num']);
		$show_loadmore_button_by_settings = ($settings['showbutton'] == 'on' || $settings['showbutton'] == 'true' || $settings['showbutton'] == true ) && $settings['showbutton'] !== 'false';

		if ( $show_loadmore_button_by_settings ) {
			// used for permanent and whitelist feeds
			if ( $this->feed_is_complete( $settings, $offset ) ) {
				$this->add_report( 'no pagination, feed complete' );
				return false;
			}
			if ( $posts_available > 0 ) {
				$this->add_report( 'do pagination, posts available' );
				return true;
			}
			$pages = $this->next_pages;

			if ( $pages && ! $this->should_use_backup() ) {
				foreach ( $pages as $page ) {
					if ( ! empty( $page ) ) {
						return true;
					}
				}
			}

		}


		$this->add_report( 'no pagination, no posts available' );

		return false;
	}

	/**
	 * Generates the HTML for the feed if post data is available. Although it seems
	 * some of the variables ar not used they are set here to hide where they
	 * come from when used in the feed templates.
	 *
	 * @param array $settings
	 * @param array $atts
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 * @param array $connected_accounts_for_feed connected account data for the
	 *  feed types and terms
	 *
	 * @return false|string
	 *
	 * @since 2.0/5.0
	 */
	public function get_the_feed_html( $settings, $atts, $feed_types_and_terms, $connected_accounts_for_feed ) {
		global $tf_instagram_posts_manager;

		if ( empty( $this->post_data ) && ! empty( $connected_accounts_for_feed ) ) {
			$this->handle_no_posts_found( $settings, $feed_types_and_terms );
		}
		$posts = array_slice( $this->post_data, 0, $settings['minnum'] );
		$header_data = ! empty( $this->header_data ) ? $this->header_data : false;

		$first_user = ! empty( $feed_types_and_terms['users'][0] ) ? $feed_types_and_terms['users'][0]['term'] : false;
		$first_username = false;
		if ( $first_user ) {
			$first_username = isset( $connected_accounts_for_feed[ $first_user ]['username'] ) ? $connected_accounts_for_feed[ $first_user ]['username'] : $first_user;
		} elseif ( $header_data ) { // in case no connected account for feed
			$first_username = tf_Instagram_Parse::get_username( $header_data );
		} elseif ( isset( $feed_types_and_terms['users'] ) && isset( $this->post_data[0] ) ) { // in case no connected account and no header
			$first_username = tf_Instagram_Parse::get_username( $this->post_data[0] );
		}
		$use_pagination = $this->should_use_pagination( $settings, 0 );

		$feed_id = $this->regular_feed_transient_name;
		$shortcode_atts = ! empty( $atts ) ? wp_json_encode( $atts ) : '{}';

		$settings['header_outside'] = false;
		$settings['header_inside'] = false;
		if ( $header_data && $settings['showheader'] ) {
			$settings['header_inside'] = true;
		}

		$other_atts = '';

		$classes = array();
		if ( empty( $settings['widthresp'] ) || $settings['widthresp'] == 'on' || $settings['widthresp'] == 'true' || $settings['widthresp'] === true ) {
			if ( $settings['widthresp'] !== 'false' ) {
				$classes[] = 'tfi_width_resp';
			}
		}
		if ( ! empty( $settings['class'] ) ) {
			$classes[] = esc_attr( $settings['class'] );
		}
		if ( ! empty( $settings['height'] )
		     && (((int)$settings['height'] < 100 && $settings['heightunit'] === '%') || $settings['heightunit'] === 'px') ) {
			$classes[] = 'tfi_fixed_height';
		}
		if ( ! empty( $settings['disablemobile'] )
		     && ($settings['disablemobile'] == 'on' || $settings['disablemobile'] == 'true' || $settings['disablemobile'] == true) ) {
			if ( $settings['disablemobile'] !== 'false' ) {
				$classes[] = 'tfi_disable_mobile';
			}
		}

		$additional_classes = '';
		if ( ! empty( $classes ) ) {
			$additional_classes = ' ' . implode( ' ', $classes );
		}

		$other_atts = $this->add_other_atts( $other_atts, $settings );

		$flags = array();

		if ( $settings['disable_resize'] ) {
			$flags[] = 'resizeDisable';
		} elseif ( $settings['favor_local'] ) {
			$flags[] = 'favorLocal';
		}

		if ( $settings['disable_js_image_loading'] ) {
			$flags[] = 'imageLoadDisable';
		}
		if ( $settings['ajax_post_load'] ) {
			$flags[] = 'ajaxPostLoad';
		}
		if ( isset( $_GET['tfi_debug'] ) ) {
			$flags[] = 'debug';
		}

		$ajax_test_status = $tf_instagram_posts_manager->get_ajax_status();

		if ( $tf_instagram_posts_manager->maybe_start_ajax_test() && ! $ajax_test_status['successful'] ) {
			$flags[] = 'testAjax';
		} elseif ( $tf_instagram_posts_manager->should_add_ajax_test_notice() ) {
			$error = '<p><b>' . __( 'Error: admin-ajax.php test was not successful. Some features may not be available.', 'tortoiz-instagram-feed' ) . '</b>';
			$error .= '<p>' . __( sprintf( 'Please visit %s to troubleshoot.', '<a href="https://smashballoon.com/admin-ajax-requests-are-not-working/">'.__( 'this page', 'tortoiz-instagram-feed' ).'</a>' ), 'tortoiz-instagram-feed' ) . '</p>';

			$tf_instagram_posts_manager->add_frontend_error( 'ajax', $error );
		}

		if ( ! empty( $flags ) ) {
			$other_atts .= ' data-tfi-flags="' . implode(',', $flags ) . '"';
		}

		ob_start();
		include tfi_get_feed_template_part( 'feed', $settings );
		$html = ob_get_contents();
		ob_get_clean();

		if ( $settings['ajaxtheme'] ) {
			$html .= $this->get_ajax_page_load_html();
		}

		return $html;
	}

	/**
	 * Generates HTML for individual tfi_item elements
	 *
	 * @param array $settings
	 * @param int $offset
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 * @param array $connected_accounts_for_feed connected account data for the
	 *  feed types and terms
	 *
	 * @return false|string
	 *
	 * @since 2.0/5.0
	 */
	public function get_the_items_html( $settings, $offset, $feed_types_and_terms, $connected_accounts_for_feed ) {
		if ( empty( $this->post_data ) ) {
			ob_start();
			$html = ob_get_contents();
			ob_get_clean();		?>
            <p><?php _e( 'No posts found.', 'tortoiz-instagram-feed' ); ?></p>
			<?php
			$html = ob_get_contents();
			ob_get_clean();
			return $html;
		}

		$posts = array_slice( $this->post_data, $offset, $settings['num'] );

		ob_start();

		$this->posts_loop( $posts, $settings, $offset );

		$html = ob_get_contents();
		ob_get_clean();

		return $html;
	}

	/**
	 * Overwritten in the Pro version
	 *
	 * @return object
	 */
	public function make_api_connection( $connected_account_or_page, $type = NULL, $params = NULL ) {
		return new tf_Instagram_API_Connect( $connected_account_or_page, $type, $params );
	}

	/**
	 * When the feed is loaded with AJAX, the JavaScript for the plugin
	 * needs to be triggered again. This function is a workaround that adds
	 * the file and settings to the page whenever the feed is generated.
	 *
	 * @return string
	 *
	 * @since 2.0/5.0
	 */
	public static function get_ajax_page_load_html() {
		$tfi_options = tfi_get_database_settings();
		$font_method = isset( $tfi_options['tfi_font_method'] ) ? $tfi_options['tfi_font_method'] : 'svg';
		$upload = wp_upload_dir();
		$resized_url = trailingslashit( $upload['baseurl'] ) . trailingslashit( tfi_UPLOADS_NAME );

		$js_options = array(
			'font_method' => $font_method,
			'placeholder' => trailingslashit( tfi_PLUGIN_URL ) . 'img/placeholder.png',
			'resized_url' => $resized_url
		);

		$encoded_options = wp_json_encode( $js_options );

		$js_option_html = '<script type="text/javascript">var tf_instagram_js_options = ' . $encoded_options . ';</script>';
		$js_option_html .= "<script type='text/javascript' src='" . trailingslashit( tfi_PLUGIN_URL ) . 'js/tf-instagram.min.js?ver=' . tfiVER . "'></script>";

		return $js_option_html;
	}

	/**
	 * Overwritten in the Pro version
	 *
	 * @param $feed_types_and_terms
	 *
	 * @return string
	 *
	 * @since 2.1/5.2
	 */
	public function get_first_user( $feed_types_and_terms ) {
		if ( isset( $feed_types_and_terms['users'][0] ) ) {
			return $feed_types_and_terms['users'][0]['term'];
		} else {
			return '';
		}
	}

	/**
	 * Adds recorded strings to an array
	 *
	 * @param $to_add
	 *
	 * @since 2.0/5.0
	 */
	public function add_report( $to_add ) {
		$this->report[] = $to_add;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_report() {
		return $this->report;
	}

	/**
	 * Additional options/settings added to the main div
	 * for the feed
	 *
	 * Overwritten in the Pro version
	 *
	 * @param $other_atts
	 * @param $settings
	 *
	 * @return string
	 */
	protected function add_other_atts( $other_atts, $settings ) {
		return '';
	}

	/**
	 * Used for filtering a single API request worth of posts
	 *
	 * Overwritten in the Pro version
	 *
	 * @param array $post_set a single set of post data from the api
	 *
	 * @return mixed|array
	 *
	 * @since 2.0/5.0
	 */
	protected function filter_posts( $post_set, $settings = array() ) {
		// array_unique( $post_set, SORT_REGULAR);

		return $post_set;
	}

	protected function handle_no_posts_found( $settings = array(), $feed_types_and_terms = array() ) {
		global $tf_instagram_posts_manager;

		$error = '<p><b>' . __( 'Error: No posts found.', 'tortoiz-instagram-feed' ) . '</b>';
		$error .= '<p>' . __( 'Make sure this account has posts available on instagram.com.', 'tortoiz-instagram-feed' ) . '</p>';

		$error .= '<p><a href="https://smashballoon.com/instagram-feed/docs/errors/">' . __( 'Click here to troubleshoot', 'tortoiz-instagram-feed' ) . '</a></p>';

		$tf_instagram_posts_manager->add_frontend_error( 'noposts', $error );
	}

	protected function remove_duplicate_posts() {
		$posts = $this->post_data;
		$ids_in_feed = array();
		$non_duplicate_posts = array();
		$removed = array();

		foreach ( $posts as $post ) {
			$post_id = tf_Instagram_Parse::get_post_id( $post );
			if ( ! in_array( $post_id, $ids_in_feed, true ) ) {
				$ids_in_feed[] = $post_id;
				$non_duplicate_posts[] = $post;
			} else {
				$removed[] = $post_id;
			}
		}

		$this->add_report( 'removed duplicates: ' . implode(', ', $removed ) );
		$this->set_post_data( $non_duplicate_posts );
	}

	/**
	 * Used for limiting the cache size
	 *
	 * @since 2.0/5.1.1
	 */
	protected function trim_posts_to_max() {
		if ( ! is_array( $this->post_data ) ) {
			return;
		}

		$max = apply_filters( 'tfi_max_cache_size', 500 );
		$this->set_post_data( array_slice( $this->post_data , 0, $max ) );

	}

	/**
	 * Used for permanent feeds or white list feeds to
	 * stop pagination if all posts are already added
	 *
	 * Overwritten in the Pro version
	 *
	 * @param array $settings
	 * @param int $offset
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	protected function feed_is_complete( $settings, $offset = 0 ) {
		return false;
	}

	/**
	 * @param $connected_account_for_term
	 *
	 * @since 2.0/5.1.2
	 */
	private function clear_expired_access_token_notice( $connected_account_for_term ) {
		$tfi_options = get_option( 'tf_instagram_settings' );
		$ca_to_save = isset( $tfi_options['connected_accounts'] ) ? $tfi_options['connected_accounts'] : array();

		if ( ! empty( $ca_to_save ) && ! empty( $connected_account_for_term ) ) {

			foreach ( $ca_to_save as $account ) {
				if ( $connected_account_for_term['access_token'] === $account['access_token'] ) {
					$ca_to_save[ $account['user_id'] ]['is_valid'] = true;
				}
			}

			$tfi_options['connected_accounts'] = $ca_to_save;

			update_option( 'tf_instagram_settings', $tfi_options );
		}
	}

	/**
	 * Iterates through post data and tracks the index of the current post.
	 * The actual post ids of the posts are stored in an array so the plugin
	 * can search for local images that may be available.
	 *
	 * @param array $posts final filtered post data for the feed
	 * @param array $settings
	 * @param int $offset
	 *
	 * @since 2.0/5.0
	 */
	private function posts_loop( $posts, $settings, $offset = 0 ) {

		$image_ids = array();
		$post_index = $offset;
		$icon_type = $settings['font_method'];
		$resized_images = $this->get_resized_images();

		foreach ( $posts as $post ) {
			$image_ids[] = tf_Instagram_Parse::get_post_id( $post );
			$account_type = tf_Instagram_Parse::get_account_type( $post );
			include tfi_get_feed_template_part( 'item', $settings );
			$post_index++;
		}

		$this->image_ids_post_set = $image_ids;
	}

	/**
	 * Uses array of API request results and merges them based on how
	 * the feed should be sorted. Mixed feeds are always sorted alternating
	 * since there is no post date for hashtag feeds.
	 *
	 * @param array $post_sets an array of single API request worth
	 *  of posts
	 * @param array $settings
	 *
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	private function merge_posts( $post_sets, $settings ) {
		$merged_posts = array();
		if ( $settings['sortby'] === 'alternate' ) {
			// don't bother merging posts if there is only one post set
			if ( isset( $post_sets[1] ) ) {
				$min_cycles = max( 1, (int)$settings['num'] );
				for( $i = 0; $i <= $min_cycles; $i++ ) {
					foreach ( $post_sets as $post_set ) {
						if ( isset( $post_set[ $i ] ) && isset( $post_set[ $i ]['id'] ) ) {
							$merged_posts[] = $post_set[ $i ];
						}
					}
				}
			} else {
				$merged_posts = isset( $post_sets[0] ) ? $post_sets[0] : array();
			}
		} else {
			// don't bother merging posts if there is only one post set
			if ( isset( $post_sets[1] ) ) {
				foreach ( $post_sets as $post_set ) {
					if ( isset( $post_set[0]['id'] ) ) {
						$merged_posts = array_merge( $merged_posts, $post_set );
					}
				}
			} else {
				$merged_posts = isset( $post_sets[0] ) ? $post_sets[0] : array();
			}
		}


		return $merged_posts;
	}

	/**
	 * Sorts a post set based on sorting settings. Sorting by "alternate"
	 * is done when merging posts for efficiency's sake so the post set is
	 * just returned as it is.
	 *
	 * @param array $post_set
	 * @param array $settings
	 *
	 * @return mixed|array
	 *
	 * @since 2.0/5.0
	 * @since 2.1/5.2 added filter hook for applying custom sorting
	 */
	private function sort_posts( $post_set, $settings ) {
		if ( empty( $post_set ) ) {
			return $post_set;
		}

		// sorting done with "merge_posts" to be more efficient
		if ( $settings['sortby'] === 'alternate' ) {
			$return_post_set = $post_set;
		} elseif ( $settings['sortby'] === 'random' ) {
			/*
             * randomly selects posts in a random order. Cache saves posts
             * in this random order so paginating does not cause some posts to show up
             * twice or not at all
             */
			usort($post_set, 'tfi_rand_sort' );
			$return_post_set = $post_set;

		} else {
			// compares posted on dates of posts
			usort($post_set, 'tfi_date_sort' );
			$return_post_set = $post_set;
		}

		/**
		 * Apply a custom sorting of posts
		 *
		 * @param array $return_post_set    Ordered set of filtered posts
		 * @param array $settings           Settings for this feed
		 *
		 * @since 2.1/5.2
		 */

		return apply_filters( 'tfi_sorted_posts', $return_post_set, $settings );
	}

	/**
	 * Can trigger a second attempt at getting posts from the API
	 *
	 * Overwritten in the Pro version
	 *
	 * @param string $type
	 * @param array $connected_account_with_error
	 * @param int $attempts
	 *
	 * @return bool
	 *
	 * @since 2.0/5.1.1
	 */
	protected function can_try_another_request( $type, $connected_account_with_error, $attempts = 0 ) {
		return false;
	}

	/**
	 * returns a second connected account if it exists
	 *
	 * Overwritten in the Pro version
	 *
	 * @param string $type
	 * @param array $attempted_connected_accounts
	 *
	 * @return bool
	 *
	 * @since 2.0/5.1.1
	 */
	protected function get_different_connected_account( $type, $attempted_connected_accounts ) {
		return false;
	}

}