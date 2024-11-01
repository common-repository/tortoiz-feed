<?php
/**
 * Includes functions for all admin page templates and
 * functions that add menu pages in the dashboard. Also
 * has code for saving settings with defaults.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function sb_instagram_menu() {
	$cap = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';

	$cap = apply_filters( 'sbi_settings_pages_capability', $cap );

	add_menu_page(
		__( 'Instagram Feed', 'tortoiz-feed' ),
		__( 'Instagram Feed', 'tortoiz-feed' ),
		$cap,
		'sb-instagram-feed',
		'sb_instagram_settings_page'
	);
	add_submenu_page(
		'sb-instagram-feed',
		__( 'Settings', 'tortoiz-feed' ),
		__( 'Settings', 'tortoiz-feed' ),
		$cap,
		'sb-instagram-feed',
		'sb_instagram_settings_page'
	);
}
add_action('admin_menu', 'sb_instagram_menu');

function sb_instagram_settings_page() {

	//Hidden fields
	$sb_instagram_settings_hidden_field = 'sb_instagram_settings_hidden_field';
	$sb_instagram_configure_hidden_field = 'sb_instagram_configure_hidden_field';
	$sb_instagram_customize_hidden_field = 'sb_instagram_customize_hidden_field';

	//Declare defaults
	$sb_instagram_settings_defaults = array(
		'sb_instagram_at'                   	=> '',
		'sb_instagram_user_id'              	=> '',
		'sb_instagram_preserve_settings'    	=> '',
		'sb_instagram_cache_time'           	=> 1,
		'sb_instagram_cache_time_unit'      	=> 'hours',
		'sbi_caching_type'                  	=> 'page',
		'sbi_cache_cron_interval'           	=> '12hours',
		'sbi_cache_cron_time'               	=> '1',
		'sbi_cache_cron_am_pm'              	=> 'am',
		'sb_instagram_width'                	=> '100',
		'sb_instagram_width_unit'           	=> '%',
		'sb_instagram_feed_width_resp'      	=> false,
		'sb_instagram_height'               	=> '',
		'sb_instagram_num'                  	=> '20',
		'sb_instagram_height_unit'          	=> '',
		'sb_instagram_cols'                 	=> '4',
		'sb_instagram_disable_mobile'       	=> false,
		'sb_instagram_image_padding'        	=> '5',
		'sb_instagram_image_padding_unit'   	=> 'px',
		'sb_instagram_sort'                 	=> 'none',
		'sb_instagram_background'           	=> '',
		'sb_instagram_show_btn'             	=> true,
		'sb_instagram_btn_background'       	=> '',
		'sb_instagram_btn_text_color'       	=> '',
		'sb_instagram_btn_text'             	=> __( 'Load More...', 'tortoiz-feed' ),
		'sb_instagram_image_res'            	=> 'auto',
		//Header
		'sb_instagram_show_header'          	=> true,
		'sb_instagram_header_size'  			=> 'small',
		'sb_instagram_header_color'         	=> '',
		//Follow button
		'sb_instagram_show_follow_btn'      	=> true,
		'sb_instagram_folow_btn_background' 	=> '',
		'sb_instagram_follow_btn_text_color' 	=> '',
		'sb_instagram_follow_btn_text'      	=> __( 'Follow on Instagram', 'tortoiz-feed' ),
		//m_iscommadelimited(conn, identifier)
		'sb_instagram_custom_css'           	=> '',
		'sb_instagram_custom_js'            	=> '',
		'sb_instagram_cron'                 	=> 'no',
		'sb_instagram_backup' 					=> true,
		'sb_ajax_initial' 						=> false,
		'enqueue_css_in_shortcode' 				=> false,
		'sb_instagram_ajax_theme'           	=> false,
		'sb_instagram_disable_resize'       	=> false,
		'sb_instagram_favor_local'          	=> false,
		'sb_instagram_minnum' 					=> 0,
		'disable_js_image_loading'          	=> false,
		'enqueue_js_in_head'                	=> false,
		'enqueue_css_in_shortcode' 				=> false,
		'sb_instagram_disable_mob_swipe' 		=> false,
		'sbi_font_method' 						=> 'svg',
		'sb_instagram_disable_awesome'      	=> false,
        'custom_template' 						=> false
	);
	//Save defaults in an array
	$options = wp_parse_args(get_option('sb_instagram_settings'), $sb_instagram_settings_defaults);
	update_option( 'sb_instagram_settings', $options );

	//Set the page variables
	$sb_instagram_at 					= $options[ 'sb_instagram_at' ];
	$sb_instagram_user_id 				= $options[ 'sb_instagram_user_id' ];
	$sb_instagram_preserve_settings 	= $options[ 'sb_instagram_preserve_settings' ];
	$sb_instagram_ajax_theme 			= $options[ 'sb_instagram_ajax_theme' ];
	$enqueue_js_in_head 				= $options[ 'enqueue_js_in_head' ];
	$disable_js_image_loading 			= $options[ 'disable_js_image_loading' ];
	$sb_instagram_disable_resize 		= $options[ 'sb_instagram_disable_resize' ];
	$sb_instagram_favor_local 			= $options[ 'sb_instagram_favor_local' ];
	$sb_instagram_minnum 				= $options[ 'sb_instagram_minnum' ];

	$sb_instagram_cache_time 			= $options[ 'sb_instagram_cache_time' ];
	$sb_instagram_cache_time_unit 		= $options[ 'sb_instagram_cache_time_unit' ];

	$sbi_caching_type 					= $options[ 'sbi_caching_type' ];
	$sbi_cache_cron_interval 			= $options[ 'sbi_cache_cron_interval' ];
	$sbi_cache_cron_time 				= $options[ 'sbi_cache_cron_time' ];
	$sbi_cache_cron_am_pm 				= $options[ 'sbi_cache_cron_am_pm' ];

	$sb_instagram_width 				= $options[ 'sb_instagram_width' ];
	$sb_instagram_width_unit 			= $options[ 'sb_instagram_width_unit' ];
	$sb_instagram_feed_width_resp 		= $options[ 'sb_instagram_feed_width_resp' ];
	$sb_instagram_height 				= $options[ 'sb_instagram_height' ];
	$sb_instagram_height_unit 			= $options[ 'sb_instagram_height_unit' ];
	$sb_instagram_num 					= $options[ 'sb_instagram_num' ];
	$sb_instagram_cols 					= $options[ 'sb_instagram_cols' ];
	$sb_instagram_disable_mobile 		= $options[ 'sb_instagram_disable_mobile' ];
	$sb_instagram_image_padding 		= $options[ 'sb_instagram_image_padding' ];
	$sb_instagram_image_padding_unit 	= $options[ 'sb_instagram_image_padding_unit' ];
	$sb_instagram_sort 					= $options[ 'sb_instagram_sort' ];
	$sb_instagram_background 			= $options[ 'sb_instagram_background' ];
	$sb_instagram_show_btn 				= $options[ 'sb_instagram_show_btn' ];
	$sb_instagram_btn_background 		= $options[ 'sb_instagram_btn_background' ];
	$sb_instagram_btn_text_color 		= $options[ 'sb_instagram_btn_text_color' ];
	$sb_instagram_btn_text 				= $options[ 'sb_instagram_btn_text' ];
	$sb_instagram_image_res 			= $options[ 'sb_instagram_image_res' ];
	//Header
	$sb_instagram_show_header 			= $options[ 'sb_instagram_show_header' ];
	$sb_instagram_header_size 			= $options[ 'sb_instagram_header_size' ];
	$sb_instagram_show_bio 				= isset( $options[ 'sb_instagram_show_bio' ] ) ? $options[ 'sb_instagram_show_bio' ] : true;
	$sb_instagram_header_color 			= $options[ 'sb_instagram_header_color' ];
	//Follow button
	$sb_instagram_show_follow_btn 		= $options[ 'sb_instagram_show_follow_btn' ];
	$sb_instagram_folow_btn_background 	= $options[ 'sb_instagram_folow_btn_background' ];
	$sb_instagram_follow_btn_text_color = $options[ 'sb_instagram_follow_btn_text_color' ];
	$sb_instagram_follow_btn_text 		= $options[ 'sb_instagram_follow_btn_text' ];
	//Misc
	$sb_instagram_custom_css 			= $options[ 'sb_instagram_custom_css' ];
	$sb_instagram_custom_js 			= $options[ 'sb_instagram_custom_js' ];
	$sb_instagram_cron 					= $options[ 'sb_instagram_cron' ];
	$sb_instagram_backup 				= $options[ 'sb_instagram_backup' ];
	$sb_ajax_initial 					= $options[ 'sb_ajax_initial' ];
	$enqueue_css_in_shortcode 			= $options[ 'enqueue_css_in_shortcode' ];
	$sbi_font_method 					= $options[ 'sbi_font_method' ];
	$sb_instagram_disable_awesome 		= $options[ 'sb_instagram_disable_awesome' ];
	$sb_instagram_custom_template 		= $options[ 'custom_template' ];



	//Check nonce before saving data
	if ( ! isset( $_POST['sb_instagram_settings_nonce'] ) || ! wp_verify_nonce( $_POST['sb_instagram_settings_nonce'], 'sb_instagram_saving_settings' ) ) {
		//Nonce did not verify
	} else {
		// See if the user has posted us some information. If they did, this hidden field will be set to 'Y'.
		if( isset($_POST[ $sb_instagram_settings_hidden_field ]) && $_POST[ $sb_instagram_settings_hidden_field ] == 'Y' ) {

			if( isset($_POST[ $sb_instagram_configure_hidden_field ]) && $_POST[ $sb_instagram_configure_hidden_field ] == 'Y' ) {

				$sb_instagram_at = sanitize_text_field( $_POST[ 'sb_instagram_at' ] );
				$sb_instagram_user_id = array();
				if ( isset( $_POST[ 'sb_instagram_user_id' ] )) {
					if ( is_array( $_POST[ 'sb_instagram_user_id' ] ) ) {
						foreach( $_POST[ 'sb_instagram_user_id' ] as $user_id ) {
							$sb_instagram_user_id[] = sanitize_text_field( $user_id );
						}
					} else {
						$sb_instagram_user_id[] = sanitize_text_field( $_POST[ 'sb_instagram_user_id' ] );
					}
				}
				isset($_POST[ 'sb_instagram_preserve_settings' ]) ? $sb_instagram_preserve_settings = sanitize_text_field( $_POST[ 'sb_instagram_preserve_settings' ] ) : $sb_instagram_preserve_settings = '';
				isset($_POST[ 'sb_instagram_cache_time' ]) ? $sb_instagram_cache_time = sanitize_text_field( $_POST[ 'sb_instagram_cache_time' ] ) : $sb_instagram_cache_time = '';
				isset($_POST[ 'sb_instagram_cache_time_unit' ]) ? $sb_instagram_cache_time_unit = sanitize_text_field( $_POST[ 'sb_instagram_cache_time_unit' ] ) : $sb_instagram_cache_time_unit = '';

				isset($_POST[ 'sbi_caching_type' ]) ? $sbi_caching_type = sanitize_text_field( $_POST[ 'sbi_caching_type' ] ) : $sbi_caching_type = '';
				isset($_POST[ 'sbi_cache_cron_interval' ]) ? $sbi_cache_cron_interval = sanitize_text_field( $_POST[ 'sbi_cache_cron_interval' ] ) : $sbi_cache_cron_interval = '';
				isset($_POST[ 'sbi_cache_cron_time' ]) ? $sbi_cache_cron_time = sanitize_text_field( $_POST[ 'sbi_cache_cron_time' ] ) : $sbi_cache_cron_time = '';
				isset($_POST[ 'sbi_cache_cron_am_pm' ]) ? $sbi_cache_cron_am_pm = sanitize_text_field( $_POST[ 'sbi_cache_cron_am_pm' ] ) : $sbi_cache_cron_am_pm = '';

				$options[ 'sb_instagram_at' ] 					= $sb_instagram_at;
				$options[ 'sb_instagram_user_id' ] 				= $sb_instagram_user_id;
				$options[ 'sb_instagram_preserve_settings' ] 	= $sb_instagram_preserve_settings;

				$options[ 'sb_instagram_cache_time' ] 			= $sb_instagram_cache_time;
				$options[ 'sb_instagram_cache_time_unit' ] 		= $sb_instagram_cache_time_unit;

				$options[ 'sbi_caching_type' ] 					= $sbi_caching_type;
				$options[ 'sbi_cache_cron_interval' ] 			= $sbi_cache_cron_interval;
				$options[ 'sbi_cache_cron_time' ] 				= $sbi_cache_cron_time;
				$options[ 'sbi_cache_cron_am_pm' ] 				= $sbi_cache_cron_am_pm;


				//Delete all SBI transients
				global $wpdb;
				$table_name = $wpdb->prefix . "options";
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_sbi\_%')
                    " );
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_timeout\_sbi\_%')
                    " );
				$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_&sbi\_%')
			        " );
				$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_timeout\_&sbi\_%')
			        " );

				if ( $sbi_caching_type === 'background' ) {
					delete_option( 'sbi_cron_report' );
					SB_Instagram_Cron_Updater::start_cron_job( $sbi_cache_cron_interval, $sbi_cache_cron_time, $sbi_cache_cron_am_pm );
				}

			} //End config tab post

			if( isset($_POST[ $sb_instagram_customize_hidden_field ]) && $_POST[ $sb_instagram_customize_hidden_field ] == 'Y' ) {

				//Validate and sanitize width field
				$safe_width = intval( sanitize_text_field( $_POST['sb_instagram_width'] ) );
				if ( ! $safe_width ) $safe_width = '';
				if ( strlen( $safe_width ) > 4 ) $safe_width = substr( $safe_width, 0, 4 );
				$sb_instagram_width = $safe_width;

				$sb_instagram_width_unit = sanitize_text_field( $_POST[ 'sb_instagram_width_unit' ] );
				isset($_POST[ 'sb_instagram_feed_width_resp' ]) ? $sb_instagram_feed_width_resp = sanitize_text_field( $_POST[ 'sb_instagram_feed_width_resp' ] ) : $sb_instagram_feed_width_resp = '';

				//Validate and sanitize height field
				$safe_height = intval( sanitize_text_field( $_POST['sb_instagram_height'] ) );
				if ( ! $safe_height ) $safe_height = '';
				if ( strlen( $safe_height ) > 4 ) $safe_height = substr( $safe_height, 0, 4 );
				$sb_instagram_height = $safe_height;

				$sb_instagram_height_unit = sanitize_text_field( $_POST[ 'sb_instagram_height_unit' ] );

				//Validate and sanitize number of photos field
				$safe_num = intval( sanitize_text_field( $_POST['sb_instagram_num'] ) );
				if ( ! $safe_num ) $safe_num = '';
				if ( strlen( $safe_num ) > 4 ) $safe_num = substr( $safe_num, 0, 4 );
				$sb_instagram_num = $safe_num;

				$sb_instagram_cols = sanitize_text_field( $_POST[ 'sb_instagram_cols' ] );
				isset($_POST[ 'sb_instagram_disable_mobile' ]) ? $sb_instagram_disable_mobile = sanitize_text_field( $_POST[ 'sb_instagram_disable_mobile' ] ) : $sb_instagram_disable_mobile = '';

				//Validate and sanitize padding field
				$safe_padding = intval( sanitize_text_field( $_POST['sb_instagram_image_padding'] ) );
				if ( ! $safe_padding ) $safe_padding = '';
				if ( strlen( $safe_padding ) > 4 ) $safe_padding = substr( $safe_padding, 0, 4 );
				$sb_instagram_image_padding = $safe_padding;

				$sb_instagram_image_padding_unit = sanitize_text_field( $_POST[ 'sb_instagram_image_padding_unit' ] );
				$sb_instagram_sort = sanitize_text_field( $_POST[ 'sb_instagram_sort' ] );
				$sb_instagram_background = sanitize_text_field( $_POST[ 'sb_instagram_background' ] );
				isset($_POST[ 'sb_instagram_show_btn' ]) ? $sb_instagram_show_btn = sanitize_text_field( $_POST[ 'sb_instagram_show_btn' ] ) : $sb_instagram_show_btn = '';
				$sb_instagram_btn_background = sanitize_text_field( $_POST[ 'sb_instagram_btn_background' ] );
				$sb_instagram_btn_text_color = sanitize_text_field( $_POST[ 'sb_instagram_btn_text_color' ] );
				$sb_instagram_btn_text = sanitize_text_field( $_POST[ 'sb_instagram_btn_text' ] );
				$sb_instagram_image_res = sanitize_text_field( $_POST[ 'sb_instagram_image_res' ] );
				//Header
				isset($_POST[ 'sb_instagram_show_header' ]) ? $sb_instagram_show_header = sanitize_text_field( $_POST[ 'sb_instagram_show_header' ] ) : $sb_instagram_show_header = '';
				isset($_POST[ 'sb_instagram_show_bio' ]) ? $sb_instagram_show_bio = sanitize_text_field( $_POST[ 'sb_instagram_show_bio' ] ) : $sb_instagram_show_bio = '';
				if (isset($_POST[ 'sb_instagram_header_size' ]) ) $sb_instagram_header_size = $_POST[ 'sb_instagram_header_size' ];

				$sb_instagram_header_color = sanitize_text_field( $_POST[ 'sb_instagram_header_color' ] );
				//Follow button
				isset($_POST[ 'sb_instagram_show_follow_btn' ]) ? $sb_instagram_show_follow_btn = sanitize_text_field( $_POST[ 'sb_instagram_show_follow_btn' ] ) : $sb_instagram_show_follow_btn = '';
				$sb_instagram_folow_btn_background = sanitize_text_field( $_POST[ 'sb_instagram_folow_btn_background' ] );
				$sb_instagram_follow_btn_text_color = sanitize_text_field( $_POST[ 'sb_instagram_follow_btn_text_color' ] );
				$sb_instagram_follow_btn_text = sanitize_text_field( $_POST[ 'sb_instagram_follow_btn_text' ] );
				//Misc
				$sb_instagram_custom_css = $_POST[ 'sb_instagram_custom_css' ];
				$sb_instagram_custom_js = $_POST[ 'sb_instagram_custom_js' ];
				isset($_POST[ 'sb_instagram_ajax_theme' ]) ? $sb_instagram_ajax_theme = sanitize_text_field( $_POST[ 'sb_instagram_ajax_theme' ] ) : $sb_instagram_ajax_theme = '';
				isset($_POST[ 'enqueue_js_in_head' ]) ? $enqueue_js_in_head = $_POST[ 'enqueue_js_in_head' ] : $enqueue_js_in_head = '';
				isset($_POST[ 'disable_js_image_loading' ]) ? $disable_js_image_loading = $_POST[ 'disable_js_image_loading' ] : $disable_js_image_loading = '';
				isset($_POST[ 'sb_instagram_disable_resize' ]) ? $sb_instagram_disable_resize= sanitize_text_field( $_POST[ 'sb_instagram_disable_resize' ] ) : $sb_instagram_disable_resize = '';
				isset($_POST[ 'sb_instagram_favor_local' ]) ? $sb_instagram_favor_local = sanitize_text_field( $_POST[ 'sb_instagram_favor_local' ] ) : $sb_instagram_favor_local = '';
				isset($_POST[ 'sb_instagram_minnum' ]) ? $sb_instagram_minnum = sanitize_text_field( $_POST[ 'sb_instagram_minnum' ] ) : $sb_instagram_minnum = '';

				if (isset($_POST[ 'sb_instagram_cron' ]) ) $sb_instagram_cron = $_POST[ 'sb_instagram_cron' ];
				isset($_POST[ 'sb_instagram_backup' ]) ? $sb_instagram_backup = $_POST[ 'sb_instagram_backup' ] : $sb_instagram_backup = '';
				isset($_POST[ 'sb_ajax_initial' ]) ? $sb_ajax_initial = $_POST[ 'sb_ajax_initial' ] : $sb_ajax_initial = '';
				isset($_POST[ 'enqueue_css_in_shortcode' ]) ? $enqueue_css_in_shortcode = $_POST[ 'enqueue_css_in_shortcode' ] : $enqueue_css_in_shortcode = '';
				isset($_POST[ 'sbi_font_method' ]) ? $sbi_font_method = $_POST[ 'sbi_font_method' ] : $sbi_font_method = 'svg';
				isset($_POST[ 'sb_instagram_disable_awesome' ]) ? $sb_instagram_disable_awesome = sanitize_text_field( $_POST[ 'sb_instagram_disable_awesome' ] ) : $sb_instagram_disable_awesome = '';

				$options[ 'sb_instagram_width' ] 					= $sb_instagram_width;
				$options[ 'sb_instagram_width_unit' ] 				= $sb_instagram_width_unit;
				$options[ 'sb_instagram_feed_width_resp' ] 			= $sb_instagram_feed_width_resp;
				$options[ 'sb_instagram_height' ] 					= $sb_instagram_height;
				$options[ 'sb_instagram_height_unit' ] 				= $sb_instagram_height_unit;
				$options[ 'sb_instagram_num' ] 						= $sb_instagram_num;
				$options[ 'sb_instagram_cols' ] 					= $sb_instagram_cols;
				$options[ 'sb_instagram_disable_mobile' ] 			= $sb_instagram_disable_mobile;
				$options[ 'sb_instagram_image_padding' ] 			= $sb_instagram_image_padding;
				$options[ 'sb_instagram_image_padding_unit' ] 		= $sb_instagram_image_padding_unit;
				$options[ 'sb_instagram_sort' ] 					= $sb_instagram_sort;
				$options[ 'sb_instagram_background' ] 				= $sb_instagram_background;
				$options[ 'sb_instagram_show_btn' ] 				= $sb_instagram_show_btn;
				$options[ 'sb_instagram_btn_background' ] 			= $sb_instagram_btn_background;
				$options[ 'sb_instagram_btn_text_color' ] 			= $sb_instagram_btn_text_color;
				$options[ 'sb_instagram_btn_text' ] 				= $sb_instagram_btn_text;
				$options[ 'sb_instagram_image_res' ] 				= $sb_instagram_image_res;
				//Header
				$options[ 'sb_instagram_show_header' ] 				= $sb_instagram_show_header;
				$options[ 'sb_instagram_header_size' ] 				= $sb_instagram_header_size;
				$options[ 'sb_instagram_show_bio' ] 				= $sb_instagram_show_bio;
				$options[ 'sb_instagram_header_color' ] 			= $sb_instagram_header_color;
				//Follow button
				$options[ 'sb_instagram_show_follow_btn' ] 			= $sb_instagram_show_follow_btn;
				$options[ 'sb_instagram_folow_btn_background' ] 	= $sb_instagram_folow_btn_background;
				$options[ 'sb_instagram_follow_btn_text_color' ] 	= $sb_instagram_follow_btn_text_color;
				$options[ 'sb_instagram_follow_btn_text' ] 			= $sb_instagram_follow_btn_text;
				//Misc
				$options[ 'sb_instagram_custom_css' ] 				= $sb_instagram_custom_css;
				$options[ 'sb_instagram_custom_js' ] 				= $sb_instagram_custom_js;
				$options[ 'sb_instagram_ajax_theme' ] 				= $sb_instagram_ajax_theme;
				$options[ 'enqueue_js_in_head' ] 					= $enqueue_js_in_head;
				$options[ 'disable_js_image_loading' ] 				= $disable_js_image_loading;
				$options[ 'sb_instagram_disable_resize' ] 			= $sb_instagram_disable_resize;
				$options[ 'sb_instagram_favor_local' ] 				= $sb_instagram_favor_local;
				$options[ 'sb_instagram_minnum' ] 					= $sb_instagram_minnum;

				$options[ 'sb_ajax_initial' ] 						= $sb_ajax_initial;
				$options[ 'sb_instagram_cron' ] 					= $sb_instagram_cron;
				$options['sb_instagram_backup'] 					= $sb_instagram_backup;
				$options['enqueue_css_in_shortcode'] 				= $enqueue_css_in_shortcode;

				$options['sbi_font_method'] 						= $sbi_font_method;
				$options[ 'sb_instagram_disable_awesome' ] 			= $sb_instagram_disable_awesome;

				isset($_POST[ 'sb_instagram_custom_template' ]) ? $sb_instagram_custom_template = $_POST[ 'sb_instagram_custom_template' ] : $sb_instagram_custom_template = '';
				$options['custom_template'] = $sb_instagram_custom_template;

				//clear expired tokens
				delete_option( 'sb_expired_tokens' );

				//Delete all SBI transients
				global $wpdb;
				$table_name = $wpdb->prefix . "options";
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_sbi\_%')
                    " );
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_timeout\_sbi\_%')
                    " );
				$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_&sbi\_%')
			        " );
				$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_timeout\_&sbi\_%')
			        " );

				if( $sb_instagram_cron == 'no' ) wp_clear_scheduled_hook('sb_instagram_cron_job');

				//Run cron when Misc settings are saved
				if( $sb_instagram_cron == 'yes' ){
					//Clear the existing cron event
					wp_clear_scheduled_hook('sb_instagram_cron_job');

					$sb_instagram_cache_time = $options[ 'sb_instagram_cache_time' ];
					$sb_instagram_cache_time_unit = $options[ 'sb_instagram_cache_time_unit' ];

					//Set the event schedule based on what the caching time is set to
					$sb_instagram_cron_schedule = 'hourly';
					if( $sb_instagram_cache_time_unit == 'hours' && $sb_instagram_cache_time > 5 ) $sb_instagram_cron_schedule = 'twicedaily';
					if( $sb_instagram_cache_time_unit == 'days' ) $sb_instagram_cron_schedule = 'daily';

					wp_schedule_event(time(), $sb_instagram_cron_schedule, 'sb_instagram_cron_job');

					sb_instagram_clear_page_caches();
				}

			} //End customize tab post

			//Save the settings to the settings array
			update_option( 'sb_instagram_settings', $options );

			?>
			<div class="updated"><p><strong><?php _e( 'Settings saved.', 'tortoiz-feed' ); ?></strong></p></div>
		<?php } ?>

	<?php } //End nonce check ?>


	<div id="sbi_admin" class="wrap">

		<div id="header">
			<h1><?php _e( 'Instagram Feed', 'tortoiz-feed' ); ?></h1>
		</div>

		<form name="form1" method="post" action="">
			<input type="hidden" name="<?php echo $sb_instagram_settings_hidden_field; ?>" value="Y">
			<?php wp_nonce_field( 'sb_instagram_saving_settings', 'sb_instagram_settings_nonce' ); ?>

			<?php $sbi_active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET['tab'] ) : 'configure'; ?>
			<h2 class="nav-tab-wrapper">
				<a href="?page=sb-instagram-feed&amp;tab=configure" class="nav-tab <?php echo $sbi_active_tab == 'configure' ? 'nav-tab-active' : ''; ?>"><?php _e( '1. Configure', 'tortoiz-feed' ); ?></a>
				<a href="?page=sb-instagram-feed&amp;tab=customize" class="nav-tab <?php echo $sbi_active_tab == 'customize' ? 'nav-tab-active' : ''; ?>"><?php _e( '2. Customize', 'tortoiz-feed' ); ?></a>
				<a href="?page=sb-instagram-feed&amp;tab=display"   class="nav-tab <?php echo $sbi_active_tab == 'display'   ? 'nav-tab-active' : ''; ?>"><?php _e( '3. Display Your Feed', 'tortoiz-feed' ); ?></a>
			</h2>

			<?php if( $sbi_active_tab == 'configure' ) { //Start Configure tab ?>
			<input type="hidden" name="<?php echo $sb_instagram_configure_hidden_field; ?>" value="Y">

			<table class="form-table">
				<tbody>
				<h3><?php _e( 'Configure', 'tortoiz-feed' ); ?></h3>

                <div id="sbi_config">
                    <a data-new-api="https://www.facebook.com/dialog/oauth?client_id=254638078422287&redirect_uri=https://api.smashballoon.com/instagram-graph-api-redirect.php&scope=manage_pages,instagram_basic,instagram_manage_insights,instagram_manage_comments&state=<?php echo admin_url('admin.php?page=sb-instagram-feed'); ?>"
                       data-old-api="https://instagram.com/oauth/authorize/?client_id=3a81a9fa2a064751b8c31385b91cc25c&scope=basic&redirect_uri=https://smashballoon.com/instagram-feed/instagram-token-plugin/?return_uri=<?php echo admin_url('admin.php?page=sb-instagram-feed'); ?>&response_type=token&state=<?php echo admin_url('admin.php?page-sb-instagram-feed'); ?>&hl=en"
                       href="https://instagram.com/oauth/authorize/?client_id=3a81a9fa2a064751b8c31385b91cc25c&scope=basic&redirect_uri=https://smashballoon.com/instagram-feed/instagram-token-plugin/?return_uri=<?php echo admin_url('admin.php?page=sb-instagram-feed'); ?>&response_type=token&state=<?php echo admin_url('admin.php?page-sb-instagram-feed'); ?>&hl=en" class="sbi_admin_btn"><i class="fa fa-user-plus" aria-hidden="true" style="font-size: 20px;"></i>&nbsp; <?php _e('Connect an Instagram Account', 'tortoiz-feed' ); ?></a>

                    <!--<a href="https://instagram.com/oauth/authorize/?client_id=3a81a9fa2a064751b8c31385b91cc25c&scope=basic+public_content&redirect_uri=https://smashballoon.com/instagram-feed/instagram-token-plugin/?return_uri=<?php echo admin_url('admin.php?page=sb-instagram-feed'); ?>&response_type=token&state=<?php echo admin_url('admin.php?page-sb-instagram-feed'); ?>" class="sbi_admin_btn"><i class="fa fa-user-plus" aria-hidden="true" style="font-size: 20px;"></i>&nbsp; <?php _e('Connect an Instagram Account', 'tortoiz-feed' ); ?></a>
                    -->
                </div>

				<!-- Old Access Token -->
				<input name="sb_instagram_at" id="sb_instagram_at" type="hidden" value="<?php echo esc_attr( $sb_instagram_at ); ?>" size="80" maxlength="100" placeholder="Click button above to get your Access Token" />
				<?php

				$returned_data = sbi_get_connected_accounts_data( $sb_instagram_at );
				$connected_accounts = $returned_data['connected_accounts'];
				$user_feeds_returned = isset(  $returned_data['user_ids'] ) ? $returned_data['user_ids'] : false;
				if ( $user_feeds_returned ) {
					$user_feed_ids = $user_feeds_returned;
				} else {
					$user_feed_ids = ! is_array( $sb_instagram_user_id ) ? explode( ',', $sb_instagram_user_id ) : $sb_instagram_user_id;
				}
				$expired_tokens = get_option( 'sb_expired_tokens', array() );
				?>

                <tr valign="top">
                    <th scope="row"><label><?php _e( 'Instagram Accounts', 'tortoiz-feed' ); ?></label><span style="font-weight:normal; font-style:italic; font-size: 12px; display: block;"><?php _e('Use the button above to connect an Instagram account', 'tortoiz-feed'); ?></span></th>
                    <td class="sbi_connected_accounts_wrap">
						<?php if ( empty( $connected_accounts ) ) : ?>
                            <p class="sbi_no_accounts"><?php _e( 'No Instagram accounts connected. Click the button above to connect an account.', 'tortoiz-feed' ); ?></p><br />
						<?php else:  ?>
							<?php foreach ( $connected_accounts as $account ) :
								$username = $account['username'] ? $account['username'] : $account['user_id'];
                                if ( isset( $account['local_avatar'] ) && $account['local_avatar'] && isset( $options['sb_instagram_favor_local'] ) && $options['sb_instagram_favor_local' ] === 'on' ) {
                                    $upload = wp_upload_dir();
                                    $resized_url = trailingslashit( $upload['baseurl'] ) . trailingslashit( SBI_UPLOADS_NAME );
                                    $profile_picture = '<img class="sbi_ca_avatar" src="'.$resized_url . $account['username'].'.jpg" />'; //Could add placeholder avatar image
                                } else {
                                    $profile_picture = $account['profile_picture'] ? '<img class="sbi_ca_avatar" src="'.$account['profile_picture'].'" />' : ''; //Could add placeholder avatar image
                                }
								$account_type = isset( $account['type'] ) ? $account['type'] : 'personal';

								if ( empty( $profile_picture ) && $account_type === 'personal' ) {
									$account_update = sbi_account_data_for_token( $account['access_token'] );
									if ( isset( $account['is_valid'] ) ) {
										$split = explode( '.', $account['access_token'] );
										$connected_accounts[ $split[0] ] = array(
											'access_token' => $account['access_token'],
											'user_id' => $split[0],
											'username' => $account_update['username'],
											'is_valid' => true,
											'last_checked' => time(),
											'profile_picture' => $account_update['profile_picture']
										);
										$sbi_options = get_option( 'sb_instagram_settings', array() );
										$sbi_options['connected_accounts'] = $connected_accounts;
										update_option( 'sb_instagram_settings', $sbi_options );
									}
								}

								$access_token_expired = (in_array(  $account['access_token'], $expired_tokens, true ) || in_array( sbi_maybe_clean( $account['access_token'] ), $expired_tokens, true ));
								$is_invalid_class = ! $account['is_valid'] || $access_token_expired ? ' sbi_account_invalid' : '';
								$in_user_feed = in_array( $account['user_id'], $user_feed_ids, true );

								?>
                                <div class="sbi_connected_account<?php echo $is_invalid_class; ?><?php if ( $in_user_feed ) echo ' sbi_account_active' ?> sbi_account_type_<?php echo $account_type; ?>" id="sbi_connected_account_<?php echo esc_attr( $account['user_id'] ); ?>" data-accesstoken="<?php echo esc_attr( $account['access_token'] ); ?>" data-userid="<?php echo esc_attr( $account['user_id'] ); ?>" data-username="<?php echo esc_attr( $account['username'] ); ?>" data-type="<?php echo esc_attr( $account_type ); ?>">

                                    <div class="sbi_ca_alert">
                                        <span><?php _e( 'The Access Token for this account is expired or invalid. Click the button above to attempt to renew it.', 'tortoiz-feed' ) ?></span>
                                    </div>
                                    <div class="sbi_ca_info">

                                        <div class="sbi_ca_delete">
                                            <a href="JavaScript:void(0);" class="sbi_delete_account"><i class="fa fa-times"></i><span class="sbi_remove_text"><?php _e( 'Remove', 'tortoiz-feed' ); ?></span></a>
                                        </div>

                                        <div class="sbi_ca_username">
											<?php echo $profile_picture; ?>
                                            <strong><?php echo $username; ?><span><?php echo $account_type; ?></span></strong>
                                        </div>

                                        <div class="sbi_ca_actions">
											<?php if ( ! $in_user_feed ) : ?>
                                                <a href="JavaScript:void(0);" class="sbi_use_in_user_feed button-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i><?php _e( 'Add to Primary Feed', 'tortoiz-feed' ); ?></a>
											<?php else : ?>
                                                <a href="JavaScript:void(0);" class="sbi_remove_from_user_feed button-primary"><i class="fa fa-minus-circle" aria-hidden="true"></i><?php _e( 'Remove from Primary Feed', 'tortoiz-feed' ); ?></a>
											<?php endif; ?>
                                            <a class="sbi_ca_token_shortcode button-secondary" href="JavaScript:void(0);"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i><?php _e( 'Add to another Feed', 'tortoiz-feed' ); ?></a>
                                            <p class="sbi_ca_show_token"><input type="checkbox" id="sbi_ca_show_token_<?php echo esc_attr( $account['user_id'] ); ?>" /><label for="sbi_ca_show_token_<?php echo esc_attr( $account['user_id'] ); ?>"><?php _e('Show Access Token', 'tortoiz-feed'); ?></label></p>

                                        </div>

                                        <div class="sbi_ca_shortcode">

                                            <p><?php _e('Copy and paste this shortcode into your page or widget area', 'tortoiz-feed'); ?>:<br>
												<?php if ( !empty( $account['username'] ) ) : ?>
                                                    <code>[instagram-feed user="<?php echo esc_html( $account['username'] ); ?>"]</code>
												<?php else : ?>
                                                    <code>[instagram-feed accesstoken="<?php echo $account['access_token']; ?>"]</code>
												<?php endif; ?>
                                            </p>

                                            <p><?php _e('To add multiple users in the same feed, simply separate them using commas', 'tortoiz-feed'); ?>:<br>
												<?php if ( !empty( $account['username'] ) ) : ?>
                                                    <code>[instagram-feed user="<?php echo esc_html( $account['username'] ); ?>, a_second_user, a_third_user"]</code>
												<?php else : ?>
                                                    <code>[instagram-feed accesstoken="<?php echo $account['access_token']; ?>, another_access_token"]</code>
												<?php endif; ?>

                                            <p><?php echo sprintf( __('Click on the %s tab to learn more about shortcodes', 'tortoiz-feed'), '<a href="?page=sb-instagram-feed&tab=display" target="_blank">'. __( 'Display Your Feed', 'tortoiz-feed' ) . '</a>' ); ?></p>
                                        </div>

                                        <div class="sbi_ca_accesstoken">
                                            <span class="sbi_ca_token_label"><?php _e('Access Token', 'tortoiz-feed');?>:</span><input type="text" class="sbi_ca_token" value="<?php echo $account['access_token']; ?>" readonly="readonly" onclick="this.focus();this.select()" title="<?php _e('To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', 'tortoiz-feed');?>">
                                        </div>

                                    </div>

                                </div>

							<?php endforeach;  ?>
						<?php endif; ?>
                        <a href="JavaScript:void(0);" class="sbi_manually_connect button-secondary"><?php _e( 'Manually Connect an Account', 'tortoiz-feed' ); ?></a>
                        <div class="sbi_manually_connect_wrap">
                            <input name="sb_manual_at" id="sb_manual_at" type="text" value="" style="margin-top: 4px; padding: 5px 9px; margin-left: 0px;" size="64" minlength="15" maxlength="200" placeholder="Enter a valid Instagram Access Token" /><span class='sbi_business_profile_tag'><?php _e('Business Profile', 'tortoiz-feed');?></span>
                            <div class="sbi_manual_account_id_toggle">
                                <label><?php _e('Please enter the User ID for this Business Profile:', 'tortoiz-feed');?></label>
                                <input name="sb_manual_account_id" id="sb_manual_account_id" type="text" value="" style="margin-top: 4px; padding: 5px 9px; margin-left: 0px;" size="40" minlength="5" maxlength="100" placeholder="Eg: 15641403491391489" />
                            </div>
                            <p class="sbi_submit" style="display: inline-block;"><input type="sbi_submit" name="submit" id="sbi_manual_submit" class="button button-primary" style="text-align: center; padding: 0;" value="<?php _e('Connect This Account', 'tortoiz-feed' );?>"></p>
                        </div>
                    </td>
                </tr>


				<?php if( isset($_GET['access_token']) && isset($_GET['graph_api']) && empty($_POST) ) { ?>
					<?php
					$access_token = sbi_maybe_clean(urldecode(sanitize_text_field($_GET['access_token'])));
					$url = 'https://graph.facebook.com/me/accounts?fields=instagram_business_account,access_token&limit=500&access_token='.$access_token;
					$args = array(
						'timeout' => 60,
						'sslverify' => false
					);
					$result = wp_remote_get( $url, $args );
					$pages_data = '{}';
					if ( ! is_wp_error( $result ) ) {
						$pages_data = $result['body'];
					} else {
						$page_error = $result;
					}

					$pages_data_arr = json_decode($pages_data);
					$num_accounts = 0;
					if(isset($pages_data_arr)){
						$num_accounts = is_array( $pages_data_arr->data ) ? count( $pages_data_arr->data ) : 0;
					}
					?>
                    <div id="sbi_config_info" class="sb_list_businesses sbi_num_businesses_<?php echo $num_accounts; ?>">
                        <div class="sbi_config_modal">
                            <div class="sbi-managed-pages">
								<?php if ( isset( $page_error ) && isset( $page_error->errors ) ) {
									foreach ($page_error->errors as $key => $item) {
										echo '<div class="sbi_user_id_error" style="display:block;"><strong>Connection Error: </strong>' . $key . ': ' . $item[0] . '</div>';
									}
								}
								?>
								<?php if( empty($pages_data_arr->data) ) : ?>
                                    <span id="sbi-bus-account-error">
                            <p style="margin-top: 5px;"><b style="font-size: 16px">Couldn't find Business Profile</b><br />
                            Uh oh. It looks like this Facebook account is not currently connected to an Instagram Business profile. Please check that you are logged into the <a href="https://www.facebook.com/" target="_blank">Facebook account</a> in this browser which is associated with your Instagram Business Profile.</p>
                            <p><b style="font-size: 16px">Why do I need a Business Profile?</b><br />
                            A Business Profile is not required. If you want to display a regular User feed then you can do this by selecting to connect a Personal account instead. For directions on how to convert your Personal profile into a Business profile please <a href="https://smashballoon.com/instagram-business-profiles" target="_blank">see here</a>.</p>
                            </span>

								<?php elseif ( $num_accounts === 0 ): ?>
                                    <span id="sbi-bus-account-error">
                            <p style="margin-top: 5px;"><b style="font-size: 16px">Couldn't find Business Profile</b><br />
                            Uh oh. It looks like this Facebook account is not currently connected to an Instagram Business profile. Please check that you are logged into the <a href="https://www.facebook.com/" target="_blank">Facebook account</a> in this browser which is associated with your Instagram Business Profile.</p>
                            <p>If you are, in fact, logged-in to the correct account please make sure you have Instagram accounts connected with your Facebook account by following <a href="https://smashballoon.com/reconnecting-an-instagram-business-profile/" target="_blank">this FAQ</a></p>
                            </span>
								<?php else: ?>
                                    <p class="sbi-managed-page-intro"><b style="font-size: 16px;">Instagram Business profiles for this account</b></p>
									<?php if ( $num_accounts > 1 ) : ?>
                                        <div class="sbi-managed-page-select-all"><input type="checkbox" id="sbi-select-all" class="sbi-select-all"><label for="sbi-select-all">Select All</label></div>
									<?php endif; ?>
                                    <div class="sbi-scrollable-accounts">

										<?php foreach ( $pages_data_arr->data as $page => $page_data ) : ?>

											<?php if( isset( $page_data->instagram_business_account ) ) :

												$instagram_business_id = $page_data->instagram_business_account->id;

												$page_access_token = isset( $page_data->access_token ) ? $page_data->access_token : '';

												//Make another request to get page info
												$instagram_account_url = 'https://graph.facebook.com/'.$instagram_business_id.'?fields=name,username,profile_picture_url&access_token='.$access_token;

												$args = array(
													'timeout' => 60,
													'sslverify' => false
												);
												$result = wp_remote_get( $instagram_account_url, $args );
												$instagram_account_info = '{}';
												if ( ! is_wp_error( $result ) ) {
													$instagram_account_info = $result['body'];
												} else {
													$page_error = $result;
												}

												$instagram_account_data = json_decode($instagram_account_info);

												$instagram_biz_img = isset( $instagram_account_data->profile_picture_url ) ? $instagram_account_data->profile_picture_url : false;
												$selected_class = $instagram_business_id == $sb_instagram_user_id ? ' sbi-page-selected' : '';

												?>
												<?php if ( isset( $page_error ) && isset( $page_error->errors ) ) :
												foreach ($page_error->errors as $key => $item) {
													echo '<div class="sbi_user_id_error" style="display:block;"><strong>Connection Error: </strong>' . $key . ': ' . $item[0] . '</div>';
												}
											else : ?>
                                                <div class="sbi-managed-page<?php echo $selected_class; ?>" data-page-token="<?php echo esc_attr( $page_access_token ); ?>" data-token="<?php echo esc_attr( $access_token ); ?>" data-page-id="<?php echo esc_attr( $instagram_business_id ); ?>">
                                                    <div class="sbi-add-checkbox">
                                                        <input id="sbi-<?php echo esc_attr( $instagram_business_id ); ?>" type="checkbox" name="sbi_managed_pages[]" value="<?php echo esc_attr( $instagram_account_info ); ?>">
                                                    </div>
                                                    <div class="sbi-managed-page-details">
                                                        <label for="sbi-<?php echo esc_attr( $instagram_business_id ); ?>"><img class="sbi-page-avatar" border="0" height="50" width="50" src="<?php echo esc_url( $instagram_biz_img ); ?>"><b style="font-size: 16px;"><?php echo esc_html( $instagram_account_data->name ); ?></b>
                                                            <br />@<?php echo esc_html( $instagram_account_data->username); ?><span style="font-size: 11px; margin-left: 5px;">(<?php echo esc_html( $instagram_business_id ); ?>)</span></label>
                                                    </div>
                                                </div>
											<?php endif; ?>

											<?php endif; ?>

										<?php endforeach; ?>

                                    </div> <!-- end scrollable -->
                                    <a href="JavaScript:void(0);" id="sbi-connect-business-accounts" class="button button-primary" disabled="disabled" style="margin-top: 20px;">Connect Accounts</a>

								<?php endif; ?>

                                <a href="JavaScript:void(0);" class="sbi_modal_close"><i class="fa fa-times"></i></a>
                            </div>
                        </div>
                    </div>
				<?php } ?>

				<?php //Display connected page
				if (isset( $sbi_connected_page ) && strpos($sbi_connected_page, ':') !== false) {

					$sbi_connected_page_pieces 		= explode(":", $sbi_connected_page);
					$sbi_connected_page_id 			= $sbi_connected_page_pieces[0];
					$sbi_connected_page_name 		= $sbi_connected_page_pieces[1];
					$sbi_connected_page_image 		= $sbi_connected_page_pieces[2];

					echo '&nbsp;';
					echo '<p style="font-weight: bold; margin-bottom: 5px;">Connected Business Profile:</p>';
					echo '<div class="sbi-managed-page sbi-no-select">';
					echo '<p><img class="sbi-page-avatar" border="0" height="50" width="50" src="'.$sbi_connected_page_image.'"><b>'.$sbi_connected_page_name.'</b> &nbsp; ('.$sbi_connected_page_id.')</p>';
					echo '</div>';
				}

				$sb_instagram_type = 'user';
				?>

				<tr>
					<th class="bump-left"><label for="sb_instagram_preserve_settings" class="bump-left"><?php _e("Preserve settings when plugin is removed", 'tortoiz-feed'); ?></label></th>
					<td>
						<input name="sb_instagram_preserve_settings" type="checkbox" id="sb_instagram_preserve_settings" <?php if($sb_instagram_preserve_settings == true) echo "checked"; ?> />
						<label for="sb_instagram_preserve_settings"><?php _e('Yes', 'tortoiz-feed'); ?></label>
						<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'tortoiz-feed'); ?></a>
						<p class="sbi_tooltip"><?php _e('When removing the plugin your settings are automatically erased. Checking this box will prevent any settings from being deleted. This means that you can uninstall and reinstall the plugin without losing your settings.', 'tortoiz-feed'); ?></p>
					</td>
				</tr>


                <tr valign="top" class="sbi_cron_cache_opts">
                    <th scope="row"><?php _e( 'Check for new posts', 'tortoiz-feed' ); ?></th>
                    <td>

                        <div class="sbi_row">
                            <input type="radio" name="sbi_caching_type" id="sbi_caching_type_page" value="page" <?php if ( $sbi_caching_type === 'page' ) echo 'checked'; ?>>
                            <label for="sbi_caching_type_page"><?php _e( 'When the page loads', 'tortoiz-feed' ); ?></label>
                            <a class="sbi_tooltip_link" href="JavaScript:void(0);" style="position: relative; top: 2px;"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                            <p class="sbi_tooltip sbi-more-info"><?php _e( 'Your Instagram post data is temporarily cached by the plugin in your WordPress database. There are two ways that you can set the plugin to check for new data', 'tortoiz-feed' ); ?>:<br><br>
	                            <?php _e( '<b>1. When the page loads</b><br>Selecting this option means that when the cache expires then the plugin will check Facebook for new posts the next time that the feed is loaded. You can choose how long this data should be cached for. If you set the time to 60 minutes then the plugin will clear the cached data after that length of time, and the next time the page is viewed it will check for new data. <b>Tip:</b> If you\'re experiencing an issue with the plugin not updating automatically then try enabling the setting labeled <b>\'Force cache to clear on interval\'</b> which is located on the \'Customize\' tab.', 'tortoiz-feed' ); ?>
                                <br><br>
	                            <?php _e( '<b>2. In the background</b><br>Selecting this option means that the plugin will check for new data in the background so that the feed is updated behind the scenes. You can select at what time and how often the plugin should check for new data using the settings below. <b>Please note</b> that the plugin will initially check for data from Instagram when the page first loads, but then after that will check in the background on the schedule selected - unless the cache is cleared.</p>', 'tortoiz-feed' ); ?>
                        </div>
                        <div class="sbi_row sbi-caching-page-options" style="display: none;">
	                        <?php _e( 'Every', 'tortoiz-feed' ); ?>:
                            <input name="sb_instagram_cache_time" type="text" value="<?php echo esc_attr( $sb_instagram_cache_time ); ?>" size="4" />
                            <select name="sb_instagram_cache_time_unit">
                                <option value="minutes" <?php if($sb_instagram_cache_time_unit == "minutes") echo 'selected="selected"' ?> ><?php _e('Minutes', 'tortoiz-feed'); ?></option>
                                <option value="hours" <?php if($sb_instagram_cache_time_unit == "hours") echo 'selected="selected"' ?> ><?php _e('Hours', 'tortoiz-feed'); ?></option>
                                <option value="days" <?php if($sb_instagram_cache_time_unit == "days") echo 'selected="selected"' ?> ><?php _e('Days', 'tortoiz-feed'); ?></option>
                            </select>
                            <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'tortoiz-feed'); ?></a>
                            <p class="sbi_tooltip"><?php _e('Your Instagram posts are temporarily cached by the plugin in your WordPress database. You can choose how long the posts should be cached for. If you set the time to 1 hour then the plugin will clear the cache after that length of time and check Instagram for posts again.', 'tortoiz-feed'); ?></p>
                        </div>

                        <div class="sbi_row">
                            <input type="radio" name="sbi_caching_type" id="sbi_caching_type_cron" value="background" <?php if ( $sbi_caching_type === 'background' ) echo 'checked'; ?>>
                            <label for="sbi_caching_type_cron"><?php _e( 'In the background', 'tortoiz-feed' ); ?></label>
                        </div>
                        <div class="sbi_row sbi-caching-cron-options" style="display: block;">

                            <select name="sbi_cache_cron_interval" id="sbi_cache_cron_interval">
                                <option value="30mins" <?php if ( $sbi_cache_cron_interval === '30mins' ) echo 'selected'; ?>><?php _e( 'Every 30 minutes', 'tortoiz-feed' ); ?></option>
                                <option value="1hour" <?php if ( $sbi_cache_cron_interval === '1hour' ) echo 'selected'; ?>><?php _e( 'Every hour', 'tortoiz-feed' ); ?></option>
                                <option value="12hours" <?php if ( $sbi_cache_cron_interval === '12hours' ) echo 'selected'; ?>><?php _e( 'Every 12 hours', 'tortoiz-feed' ); ?></option>
                                <option value="24hours" <?php if ( $sbi_cache_cron_interval === '24hours' ) echo 'selected'; ?>><?php _e( 'Every 24 hours', 'tortoiz-feed' ); ?></option>
                            </select>

                            <div id="sbi-caching-time-settings" style="display: none;">
	                            <?php _e('at' ); ?>

                                <select name="sbi_cache_cron_time" style="width: 80px">
                                    <option value="1" <?php if ( $sbi_cache_cron_time === '1' ) echo 'selected'; ?>>1:00</option>
                                    <option value="2" <?php if ( $sbi_cache_cron_time === '2' ) echo 'selected'; ?>>2:00</option>
                                    <option value="3" <?php if ( $sbi_cache_cron_time === '3' ) echo 'selected'; ?>>3:00</option>
                                    <option value="4" <?php if ( $sbi_cache_cron_time === '4' ) echo 'selected'; ?>>4:00</option>
                                    <option value="5" <?php if ( $sbi_cache_cron_time === '5' ) echo 'selected'; ?>>5:00</option>
                                    <option value="6" <?php if ( $sbi_cache_cron_time === '6' ) echo 'selected'; ?>>6:00</option>
                                    <option value="7" <?php if ( $sbi_cache_cron_time === '7' ) echo 'selected'; ?>>7:00</option>
                                    <option value="8" <?php if ( $sbi_cache_cron_time === '8' ) echo 'selected'; ?>>8:00</option>
                                    <option value="9" <?php if ( $sbi_cache_cron_time === '9' ) echo 'selected'; ?>>9:00</option>
                                    <option value="10" <?php if ( $sbi_cache_cron_time === '10' ) echo 'selected'; ?>>10:00</option>
                                    <option value="11" <?php if ( $sbi_cache_cron_time === '11' ) echo 'selected'; ?>>11:00</option>
                                    <option value="0" <?php if ( $sbi_cache_cron_time === '0' ) echo 'selected'; ?>>12:00</option>
                                </select>

                                <select name="sbi_cache_cron_am_pm" style="width: 50px">
                                    <option value="am" <?php if ( $sbi_cache_cron_am_pm === 'am' ) echo 'selected'; ?>>AM</option>
                                    <option value="pm" <?php if ( $sbi_cache_cron_am_pm === 'pm' ) echo 'selected'; ?>>PM</option>
                                </select>
                            </div>

	                        <?php
	                        if ( wp_next_scheduled( 'sbi_feed_update' ) ) {
		                        $time_format = get_option( 'time_format' );
		                        if ( ! $time_format ) {
			                        $time_format = 'g:i a';
                                }
                                //
		                        $schedule = wp_get_schedule( 'sbi_feed_update' );
		                        if ( $schedule == '30mins' ) $schedule = __( 'every 30 minutes', 'tortoiz-feed' );
		                        if ( $schedule == 'twicedaily' ) $schedule = __( 'every 12 hours', 'tortoiz-feed' );
		                        $sbi_next_cron_event = wp_next_scheduled( 'sbi_feed_update' );
		                        echo '<p class="sbi-caching-sched-notice"><span><b>' . __( 'Next check', 'tortoiz-feed' ) . ': ' . date( $time_format, $sbi_next_cron_event + sbi_get_utc_offset() ) . ' (' . $schedule . ')</b> - ' . __( 'Note: Saving the settings on this page will clear the cache and reset this schedule', 'tortoiz-feed' ) . '</span></p>';
	                        } else {
		                        echo '<p style="font-size: 11px; color: #666;">' . __( 'Nothing currently scheduled', 'tortoiz-feed' ) . '</p>';
	                        }
	                        ?>

                        </div>

                    </td>
                </tr>

				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>


		<?php } // End Configure tab ?>



		<?php if( $sbi_active_tab == 'customize' ) { //Start Configure tab ?>

			<p class="sb_instagram_contents_links" id="general">
				<span><?php _e( 'Quick links:', 'tortoiz-feed' ); ?> </span>
				<a href="#general"><?php _e( 'General', 'tortoiz-feed' ); ?></a>
				<a href="#follow"><?php _e( "'Follow' Button", 'tortoiz-feed' ); ?></a>
			</p>

			<input type="hidden" name="<?php echo $sb_instagram_customize_hidden_field; ?>" value="Y">

			<h3><?php _e( 'General', 'tortoiz-feed' ); ?></h3>

			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row"><label><?php _e('Width of Feed', 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> width  widthunit
							Eg: width=50 widthunit=%</code></th>
					<td>
						<input name="sb_instagram_width" type="text" value="<?php echo esc_attr( $sb_instagram_width ); ?>" id="sb_instagram_width" size="4" maxlength="4" />
						<select name="sb_instagram_width_unit" id="sb_instagram_width_unit">
							<option value="px" <?php if($sb_instagram_width_unit == "px") echo 'selected="selected"' ?> ><?php _e('px', 'tortoiz-feed'); ?></option>
							<option value="%" <?php if($sb_instagram_width_unit == "%") echo 'selected="selected"' ?> ><?php _e('%', 'tortoiz-feed'); ?></option>
						</select>
						<div id="sb_instagram_width_options">
							<input name="sb_instagram_feed_width_resp" type="checkbox" id="sb_instagram_feed_width_resp" <?php if($sb_instagram_feed_width_resp == true) echo "checked"; ?> /><label for="sb_instagram_feed_width_resp"><?php _e('Set to be 100% width on mobile?', 'tortoiz-feed'); ?></label>
							<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e( 'What does this mean?', 'tortoiz-feed' ); ?></a>
							<p class="sbi_tooltip"><?php _e("If you set a width on the feed then this will be used on mobile as well as desktop. Check this setting to set the feed width to be 100% on mobile so that it is responsive.", 'tortoiz-feed'); ?></p>
						</div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Height of Feed', 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> height  heightunit
							Eg: height=500 heightunit=px</code></th>
					<td>
						<input name="sb_instagram_height" type="text" value="<?php echo esc_attr( $sb_instagram_height ); ?>" size="4" maxlength="4" />
						<select name="sb_instagram_height_unit">
							<option value="px" <?php if($sb_instagram_height_unit == "px") echo 'selected="selected"' ?> ><?php _e('px', 'tortoiz-feed'); ?></option>
							<option value="%" <?php if($sb_instagram_height_unit == "%") echo 'selected="selected"' ?> ><?php _e('%', 'tortoiz-feed'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Background Color', 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> background
							Eg: background=d89531</code></th>
					<td>
						<input name="sb_instagram_background" type="text" value="<?php echo esc_attr( $sb_instagram_background ); ?>" class="sbi_colorpick" />
					</td>
				</tr>
				</tbody>
			</table>

			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row"><label><?php _e('Number of Photos', 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> num
							Eg: num=6</code></th>
					<td>
						<input name="sb_instagram_num" type="text" value="<?php echo esc_attr( $sb_instagram_num ); ?>" size="4" maxlength="4" />
						<span class="sbi_note"><?php _e('Number of photos to show initially.', 'tortoiz-feed'); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Number of Columns', 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> cols
							Eg: cols=3</code></th>
					<td>
						<select name="sb_instagram_cols">
							<option value="1" <?php if($sb_instagram_cols == "1") echo 'selected="selected"' ?> ><?php _e('1', 'tortoiz-feed'); ?></option>
							<option value="2" <?php if($sb_instagram_cols == "2") echo 'selected="selected"' ?> ><?php _e('2', 'tortoiz-feed'); ?></option>
							<option value="3" <?php if($sb_instagram_cols == "3") echo 'selected="selected"' ?> ><?php _e('3', 'tortoiz-feed'); ?></option>
							<option value="4" <?php if($sb_instagram_cols == "4") echo 'selected="selected"' ?> ><?php _e('4', 'tortoiz-feed'); ?></option>
							<option value="5" <?php if($sb_instagram_cols == "5") echo 'selected="selected"' ?> ><?php _e('5', 'tortoiz-feed'); ?></option>
							<option value="6" <?php if($sb_instagram_cols == "6") echo 'selected="selected"' ?> ><?php _e('6', 'tortoiz-feed'); ?></option>
							<option value="7" <?php if($sb_instagram_cols == "7") echo 'selected="selected"' ?> ><?php _e('7', 'tortoiz-feed'); ?></option>
							<option value="8" <?php if($sb_instagram_cols == "8") echo 'selected="selected"' ?> ><?php _e('8', 'tortoiz-feed'); ?></option>
							<option value="9" <?php if($sb_instagram_cols == "9") echo 'selected="selected"' ?> ><?php _e('9', 'tortoiz-feed'); ?></option>
							<option value="10" <?php if($sb_instagram_cols == "10") echo 'selected="selected"' ?> ><?php _e('10', 'tortoiz-feed'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Padding around Images', 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> imagepadding  imagepaddingunit</code></th>
					<td>
						<input name="sb_instagram_image_padding" type="text" value="<?php echo esc_attr( $sb_instagram_image_padding ); ?>" size="4" maxlength="4" />
						<select name="sb_instagram_image_padding_unit">
							<option value="px" <?php if($sb_instagram_image_padding_unit == "px") echo 'selected="selected"' ?> ><?php _e('px', 'tortoiz-feed'); ?></option>
							<option value="%" <?php if($sb_instagram_image_padding_unit == "%") echo 'selected="selected"' ?> ><?php _e('%', 'tortoiz-feed'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e("Disable mobile layout", 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> disablemobile
							Eg: disablemobile=true</code></th>
					<td>
						<input type="checkbox" name="sb_instagram_disable_mobile" id="sb_instagram_disable_mobile" <?php if($sb_instagram_disable_mobile == true) echo 'checked="checked"' ?> />
						&nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e( 'What does this mean?', 'tortoiz-feed' ); ?></a>
						<p class="sbi_tooltip"><?php _e("By default on mobile devices the layout automatically changes to use fewer columns. Checking this setting disables the mobile layout.", 'tortoiz-feed'); ?></p>
					</td>
				</tr>
				</tbody>
			</table>

			<?php submit_button(); ?>

			<hr id="follow" />
			<h3><?php _e("'Follow' Button", 'tortoiz-feed'); ?></h3>
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row"><label><?php _e("Show the Follow button", 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> showfollow
							Eg: showfollow=true</code></th>
					<td>
						<input type="checkbox" name="sb_instagram_show_follow_btn" id="sb_instagram_show_follow_btn" <?php if($sb_instagram_show_follow_btn == true) echo 'checked="checked"' ?> />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label><?php _e('Button Background Color', 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> followcolor
							Eg: followcolor=28a1bf</code></th>
					<td>
						<input name="sb_instagram_folow_btn_background" type="text" value="<?php echo esc_attr( $sb_instagram_folow_btn_background ); ?>" class="sbi_colorpick" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Button Text Color', 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> followtextcolor
							Eg: followtextcolor=000</code></th>
					<td>
						<input name="sb_instagram_follow_btn_text_color" type="text" value="<?php echo esc_attr( $sb_instagram_follow_btn_text_color ); ?>" class="sbi_colorpick" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Button Text', 'tortoiz-feed'); ?></label><code class="sbi_shortcode"> followtext
							Eg: followtext="Follow me"</code></th>
					<td>
						<input name="sb_instagram_follow_btn_text" type="text" value="<?php echo esc_attr( stripslashes( $sb_instagram_follow_btn_text ) ); ?>" size="30" />
					</td>
				</tr>
				</tbody>
			</table>

			<?php submit_button(); ?>

			</form>

			<p><i class="fa fa-chevron-circle-right" aria-hidden="true"></i>&nbsp; <?php _e('Next Step: <a href="?page=sb-instagram-feed&tab=display">Display your Feed</a>', 'tortoiz-feed'); ?></p>


		<?php } //End Customize tab ?>



		<?php if( $sbi_active_tab == 'display' ) { //Start Display tab ?>

			<h3><?php _e('Display your Feed', 'tortoiz-feed'); ?></h3>
			<p><?php _e("Copy and paste the following shortcode directly into the page, post or widget where you'd like the feed to show up:", 'tortoiz-feed'); ?></p>
			<input type="text" value="[instagram-feed]" size="16" readonly="readonly" style="text-align: center;" onclick="this.focus();this.select()" title="<?php _e('To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', 'tortoiz-feed'); ?>" />

			<h3 style="padding-top: 10px;"><?php _e( 'Multiple Feeds', 'tortoiz-feed' ); ?></h3>
			<p><?php _e("If you'd like to display multiple feeds then you can set different settings directly in the shortcode like so:", 'tortoiz-feed'); ?>
				<code>[instagram-feed num=9 cols=3]</code></p>
			<p><?php _e( 'You can display as many different feeds as you like, on either the same page or on different pages, by just using the shortcode options below. For example:', 'tortoiz-feed' ); ?><br />
				<code>[instagram-feed]</code><br />
				<code>[instagram-feed num=4 cols=4 showfollow=false]</code><br />
				<code>[instagram-feed accesstoken="ANOTHER_ACCESS_TOKEN"]</code>
			</p>

		<?php } //End Display tab ?>


		


		<div class="sbi_quickstart">
			<h3><i class="fa fa-rocket" aria-hidden="true"></i>&nbsp; <?php _e('Display your feed', 'tortoiz-feed'); ?></h3>
			<p><?php _e('Copy and paste this shortcode directly into the page, post or widget where you\'d like to display the feed:', 'tortoiz-feed'); ?>        <input type="text" value="[instagram-feed]" size="15" readonly="readonly" style="text-align: center;" onclick="this.focus();this.select()" title="To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac)."></p>
		</div>

	</div> <!-- end #sbi_admin -->

<?php } //End Settings page