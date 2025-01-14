<?php
/**
 * Class tf_Instagram_Posts_Manager
 *
 * Set as a global object to record and report errors as well
 * as control aspects of image resizing
 *
 * @since 2.0/4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class tf_Instagram_Posts_Manager
{
	/**
	 * @var mixed|void
	 */
	var $tfi_options;

	/**
	 * @var int
	 */
	var $limit;

	/**
	 * @var array
	 */
	var $errors;

	/**
	 * @var array
	 */
	var $frontend_errors;

	/**
	 * @var bool
	 */
	var $resizing_tables_exist;

	/**
	 * tf_Instagram_Posts_Manager constructor.
	 */
	public function __construct() {
		$this->tfi_options = get_option( 'tf_instagram_settings' );
		$this->errors = get_option( 'tf_instagram_errors', array() );
		$this->ajax_status = get_option( 'tf_instagram_ajax_status', array( 'tested' => false, 'successful' => false ) );
		$this->frontend_errors = array();
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_ajax_status() {
		return $this->ajax_status;
	}

	/**
	 * @param $to_update
	 *
	 * @since 2.0/5.0
	 */
	public function update_ajax_status( $to_update ) {
		foreach ( $to_update as $key => $value ) {
			$this->ajax_status[ $key ] = $value;
		}

		update_option( 'tf_instagram_ajax_status', $this->ajax_status );
	}

	/**
	 * When the plugin is first installed and used, an AJAX call to admin-ajax.php
	 * is made to verify that it's available
	 *
	 * @param bool $force_check
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function maybe_start_ajax_test( $force_check = false ) {
		if ( ! $this->ajax_status['tested'] || $force_check ) {
			set_transient( 'tf_instagram_doing_ajax_test', 'yes', 60*60 );
			$this->update_ajax_status( array( 'tested' => true ) );
			return true;
		}

		return false;
	}

	/**
	 * Called if a successful admin ajax request is made
	 *
	 * @since 2.0/5.0
	 */
	public function update_successful_ajax_test() {
		$this->update_ajax_status( array( 'successful' => true ) );
	}

	/**
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function should_add_ajax_test_notice() {
		return ($this->ajax_status['tested'] && ! $this->ajax_status['successful'] && get_transient( 'tf_instagram_doing_ajax_test' ) !== 'yes');
	}

	/**
	 * The plugin has a limit on how many post records can be stored and
	 * images resized to avoid overloading servers. This function deletes the post that
	 * has the longest time passed since it was retrieved.
	 *
	 * @since 2.0/4.0
	 */
	public function delete_least_used_image() {
		global $wpdb;
		$table_name = $wpdb->prefix . tfi_INSTAGRAM_POSTS_TYPE;
		$feeds_posts_table_name = esc_sql( $wpdb->prefix . tfi_INSTAGRAM_FEEDS_POSTS );

		$max = isset( $this->limit ) && $this->limit > 1 ? $this->limit : 1;

		$oldest_posts = $wpdb->get_results( "SELECT id, media_id FROM $table_name ORDER BY last_requested ASC LIMIT $max", ARRAY_A );

		$upload = wp_upload_dir();
		$file_suffixes = array( 'thumb', 'low', 'full' );

		foreach ( $oldest_posts as $post ) {

			foreach ( $file_suffixes as $file_suffix ) {
				$file_name = trailingslashit( $upload['basedir'] ) . trailingslashit( tfi_UPLOADS_NAME ) . $post['media_id'] . $file_suffix . '.jpg';
				if ( is_file( $file_name ) ) {
					unlink( $file_name );
				}
			}

			$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $post['id'] ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $feeds_posts_table_name WHERE record_id = %d", $post['id'] ) );
		}

	}

	/**
	 * Calculates how many records are in the database and whether or not it exceeds the limit
	 *
	 * @return bool
	 *
	 * @since 2.0/4.0
	 */
	public function max_total_records_reached() {
		global $wpdb;
		$table_name = $wpdb->prefix . tfi_INSTAGRAM_POSTS_TYPE;

		$num_records = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		if ( !isset( $this->limit ) && (int)$num_records > tfi_MAX_RECORDS ) {
			$this->limit = (int)$num_records - tfi_MAX_RECORDS;
		}

		return ((int)$num_records > tfi_MAX_RECORDS);
	}

	/**
	 * The plugin caps how many new images are created in a 15 minute window to
	 * avoid overloading servers
	 *
	 * @return bool
	 *
	 * @since 2.0/4.0
	 */
	public function max_resizing_per_time_period_reached() {
		global $wpdb;
		$table_name = $wpdb->prefix . tfi_INSTAGRAM_POSTS_TYPE;

		$fifteen_minutes_ago = date( 'Y-m-d H:i:s', time() - 15 * 60 );

		$num_new_records = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE created_on > '$fifteen_minutes_ago'" );

		return ((int)$num_new_records > 100);
	}

	/**
	 * @return bool
	 *
	 * @since 2.0/4.0
	 */
	public function image_resizing_disabled() {
		$disable_resizing = isset( $this->tfi_options['tf_instagram_disable_resize'] ) ? $this->tfi_options['tf_instagram_disable_resize'] === 'on' || $this->tfi_options['tf_instagram_disable_resize'] === true : false;

		if ( ! $disable_resizing ) {
			$disable_resizing = isset( $this->resizing_tables_exist ) ? ! $this->resizing_tables_exist : ! $this->does_resizing_tables_exist();
		}

		return $disable_resizing;
	}

	/**
	 * Used to skip image resizing if the tables were never successfully
	 * created
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function does_resizing_tables_exist() {
		global $wpdb;

		$table_name = esc_sql( $wpdb->prefix . tfi_INSTAGRAM_POSTS_TYPE );

		if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
			$this->resizing_tables_exist = false;

			return false;
		}

		$feeds_posts_table_name = esc_sql( $wpdb->prefix . tfi_INSTAGRAM_FEEDS_POSTS );

		if ( $wpdb->get_var( "show tables like '$feeds_posts_table_name'" ) != $feeds_posts_table_name ) {
			$this->resizing_tables_exist = false;

			return false;
		}

		return true;
	}

	/**
	 * Resets the custom tables and deletes all image files
	 *
	 * @since 2.0/4.0
	 */
	public function delete_all_tfi_instagram_posts() {
		$upload = wp_upload_dir();

		global $wpdb;

		$posts_table_name = $wpdb->prefix . tfi_INSTAGRAM_POSTS_TYPE;

		$image_files = glob( trailingslashit( $upload['basedir'] ) . trailingslashit( tfi_UPLOADS_NAME ) . '*'  ); // get all file names
		foreach ( $image_files as $file ) { // iterate files
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}

		$options = get_option( 'tf_instagram_settings', array() );
		$connected_accounts =  isset( $options['connected_accounts'] ) ? $options['connected_accounts'] : array();

		foreach ( $connected_accounts as $account_id => $data ) {

			if ( isset( $data['local_avatar'] ) ) {
				$connected_accounts[ $account_id ]['local_avatar'] = false;
			}

		}

		$options['connected_accounts'] = $connected_accounts;

		update_option( 'tf_instagram_settings', $options );

		//Delete tables
		$wpdb->query( "DROP TABLE IF EXISTS $posts_table_name" );

		$feeds_posts_table_name = esc_sql( $wpdb->prefix . tfi_INSTAGRAM_FEEDS_POSTS );
		$wpdb->query( "DROP TABLE IF EXISTS $feeds_posts_table_name" );

		$table_name = $wpdb->prefix . "options";

		$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_\$tfi\_%')
			        " );
		$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_timeout\_\$tfi\_%')
			        " );
		delete_option( 'tfi_hashtag_ids' );

		$upload = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = trailingslashit( $upload_dir ) . tfi_UPLOADS_NAME;
		if ( ! file_exists( $upload_dir ) ) {
			$created = wp_mkdir_p( $upload_dir );
			if ( $created ) {
				$this->remove_error( 'upload_dir' );
			} else {
				$this->add_error( 'upload_dir', array( __( 'There was an error creating the folder for storing resized images.', 'tortoiz-instagram-feed' ), $upload_dir ) );
			}
		} else {
			$this->remove_error( 'upload_dir' );
		}

		tfi_create_database_table();
	}

	/**
	 * @return array
	 *
	 * @since 2.0/4.0
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * @param $type
	 * @param $message_array
	 *
	 * @since 2.0/4.0
	 */
	public function add_error( $type, $message_array ) {
		$this->errors[ $type ] = $message_array;

		update_option( 'tf_instagram_errors', $this->errors, false );
	}

	/**
	 * @param $type
	 *
	 * @since 2.0/4.0
	 */
	public function remove_error( $type ) {
		if ( isset( $this->errors[ $type ] ) ) {
			unset( $this->errors[ $type ] );

			update_option( 'tf_instagram_errors', $this->errors, false );
		}
	}

	/**
	 * @param $type
	 * @param $message
	 *
	 * @since 2.0/5.0
	 */
	public function add_frontend_error( $type, $message ) {
		$this->frontend_errors[ $type ] = $message;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_frontend_errors() {
		return $this->frontend_errors;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function reset_frontend_errors() {
		return $this->frontend_errors = array();
	}

	public function set_status() {

	}

	/**
	 * @since 2.0/5.1.2
	 */
	public function add_api_request_delay( $time_in_seconds = 300, $account_id = false ) {
		if ( $account_id ) {
			set_transient( tfi_USE_BACKUP_PREFIX . 'tfi_'  . $account_id, '1', $time_in_seconds );
		} else {
			set_transient( tfi_USE_BACKUP_PREFIX . 'tfi_delay_requests', '1', $time_in_seconds );
		}
	}

	/**
	 * @since 2.0/5.1.2
	 */
	public function are_current_api_request_delays( $account_id = false ) {
		$is_delay = (get_transient( tfi_USE_BACKUP_PREFIX . 'tfi_delay_requests' ) !== false);

		if ( $is_delay ) {
			$error = '<p><b>' . sprintf( __( 'Error: API requests are being delayed.', 'tortoiz-instagram-feed' ) ) . ' ' . __( 'New posts will not be retrieved.', 'tortoiz-instagram-feed' ) . '</b></p>';
			$errors = $this->get_errors();
			if ( ! empty( $errors )  && current_user_can( 'manage_options' ) ) {
				if ( isset( $errors['api'] ) ) {
					$error .= '<p>' . $errors['api'][1] . '</p>';
				} elseif ( isset( $errors['connection'] ) ) {
					$error .= '<p>' . $errors['connection'][1] . '</p>';
				}
			} else {
				$error .= '<p>' . __( 'There may be an issue with the Instagram access token that you are using. Your server might also be unable to connect to Instagram at this time.', 'tortoiz-instagram-feed' ) . '</p>';
			}

			$this->add_frontend_error( 'api_delay', $error );

		}

		if ( ! $is_delay && $account_id ) {
			$is_delay = (get_transient( tfi_USE_BACKUP_PREFIX . 'tfi_'  . $account_id ) !== false);
		}

		return $is_delay;
	}
}