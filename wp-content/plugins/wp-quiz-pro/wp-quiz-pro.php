<?php
/**
 * Plugin Name: WP Quiz Pro
 * Plugin URI:  https://mythemeshop.com/plugins/wp-quiz-pro/
 * Description: WP Quiz Pro lets you easily add polished, responsive and modern quizzes to your site or blog! Increase engagement and shares while building your mailing list! WP Quiz Pro makes it easy!
 * Version:     1.2.10
 * Author:      20script
 * Author URI:  http://www.20script.ir/
 *
 * Text Domain: wp-quiz-pro
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'WP_Quiz_Pro_Plugin' ) ) :

	/**
	 * Register the plugin.
	 *
	 * Display the administration panel, insert JavaScript etc.
	 */
	class WP_Quiz_Pro_Plugin {

		/**
		 * Hold plugin version
		 *
		 * @var string
		 */
		public $version = '1.2.10';

		/**
		 * Hold an instance of WP_Quiz_Pro_Plugin class.
		 *
		 * @var WP_Quiz_Pro_Plugin
		 */
		protected static $instance = null;

		/**
		 * Hold the current quiz instance
		 *
		 * @var WP_Quiz_Pro
		 */
		public $quiz = null;

		/**
		 * Plugin url.
		 * @var string
		 */
		private $plugin_url = null;

		/**
		 * Plugin path.
		 * @var string
		 */
		private $plugin_dir = null;

		/**
		 * Main WP_Quiz_Pro_Plugin instance.
		 * @return WP_Quiz_Pro_Plugin - Main instance.
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new WP_Quiz_Pro_Plugin;
			}

			return self::$instance;
		}

		/**
		 * You cannot clone this class.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-quiz-pro' ), $this->version );
		}

		/**
		 * You cannot unserialize instances of this class.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-quiz-pro' ), $this->version );
		}

		/**
		 * The Constructor
		 */
		private function __construct() {
			$this->includes();
			$this->hooks();
			$this->setup_shortcode();
		}

		/**
		 * Load required classes
		 */
		private function includes() {

			// Auto loader
			spl_autoload_register( array( $this, 'autoloader' ) );

			new WP_Quiz_Pro_Admin;
		}

		/**
		 * Autoload classes
		 */
		public function autoloader( $class ) {

			$dir = $this->plugin_dir() . 'inc' . DIRECTORY_SEPARATOR;
			$class_file_name = 'class-' . str_replace( array( 'wp_quiz_pro_', '_' ), array( '', '-' ), strtolower( $class ) ) . '.php';
			if ( file_exists( $dir . $class_file_name ) ) {
				require $dir . $class_file_name;
			}
		}

		/**
		 * Register the [wp_quiz_pro] shortcode.
		 */
		private function setup_shortcode() {

			add_shortcode( 'wp_quiz_pro', array( $this, 'register_shortcode' ) );
			add_shortcode( 'wp_quiz_listing', array( $this, 'quiz_listing' ) );
		}

		/**
		 * Hook WP Quiz into WordPress
		 */
		private function hooks() {

			// Common
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'init', array( $this, 'register_post_type' ) );
			add_action( 'init', array( $this, 'embeded_output' ) );

			// Frontend
			add_action( 'wp_head', array( $this, 'inline_script' ), 1 );
			add_filter( 'the_content', array( $this, 'create_quiz_page' ) );

			// Ajax
			add_action( 'wp_ajax_wq_quizResults', array( $this, 'save_quiz_results' ) );
			add_action( 'wp_ajax_nopriv_wq_quizResults', array( $this, 'save_quiz_results' ) );

			add_action( 'wp_ajax_wq_submitInfo', array( $this, 'save_quiz_user_info' ) );
			add_action( 'wp_ajax_nopriv_wq_submitInfo', array( $this, 'save_quiz_user_info' ) );

			add_action( 'wp_ajax_wq_submitFbInfo', array( $this, 'save_quiz_fb_user_info' ) );
			add_action( 'wp_ajax_nopriv_wq_submitFbInfo', array( $this, 'save_quiz_fb_user_info' ) );

			add_action( 'wp_ajax_check_image_file', array( $this, 'check_image_file' ) );
			add_action( 'wp_ajax_check_video_file', array( $this, 'check_video_file' ) );
			add_action( 'wp_ajax_dismiss_imagick_notice', array( $this, 'dismiss_imagick_notice' ) );
			add_action( 'wp_ajax_dismiss_gdlibrary_notice', array( $this, 'dismiss_gdlibrary_notice' ) );
			add_action( 'wp_ajax_wpquiz_get_debug_log', array( $this, 'wp_quiz_pro_get_debug_log' ) );

			add_action( 'wp_ajax_connect_aweber', array( $this, 'connect_aweber' ) );

			// FB SDK version 2.9 fix
			if ( isset( $_GET['fbs'] ) && ! empty( $_GET['fbs'] ) ) {
				add_action( 'template_redirect', array( $this, 'fb_share_fix' ) );
			}
		}

		/**
		 * Initialise translations
		 */
		public function load_plugin_textdomain() {

			load_plugin_textdomain( 'wp-quiz-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Register Quiz post type
		 */
		public function register_post_type() {

			$labels = array(
				'name'               => __( 'WP Quiz', 'wp-quiz-pro' ),
				'menu_name'          => __( 'WP Quiz Pro', 'wp-quiz-pro' ),
				'singular_name'      => __( 'WP Quiz', 'wp-quiz-pro' ),
				'name_admin_bar'     => _x( 'WP Quiz Pro', 'name admin bar', 'wp-quiz-pro' ),
				'all_items'          => __( 'All Quizzes', 'wp-quiz-pro' ),
				'search_items'       => __( 'Search Quizzes', 'wp-quiz-pro' ),
				'add_new'            => _x( 'Add New', 'quiz', 'wp-quiz-pro' ),
				'add_new_item'       => __( 'Add New Quiz', 'wp-quiz-pro' ),
				'new_item'           => __( 'New Quiz', 'wp-quiz-pro' ),
				'view_item'          => __( 'View Quiz', 'wp-quiz-pro' ),
				'edit_item'          => __( 'Edit Quiz', 'wp-quiz-pro' ),
				'not_found'          => __( 'No Quizzes found.', 'wp-quiz-pro' ),
				'not_found_in_trash' => __( 'WP Quiz not found in Trash.', 'wp-quiz-pro' ),
				'parent_item_colon'  => __( 'Parent Quiz', 'wp-quiz-pro' ),
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Holds the quizzes and their data.', 'wp-quiz-pro' ),
				'menu_position'      => 5,
				'menu_icon'			 => 'dashicons-editor-help',
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'supports'           => array( 'title', 'author', 'thumbnail', 'excerpt' ),
			);

			register_post_type( 'wp_quiz', $args );

			if ( false === get_option( 'wp_quiz_pro_version' ) ) {
				flush_rewrite_rules();
				update_option( 'wp_quiz_pro_version', $this->version );
			}
		}

		public function quiz_listing( $atts = array() ) {

			extract( shortcode_atts( array(
				'num' => 30
			), $atts ) );

			$args = array(
				'post_type' => 'wp_quiz',
				'post_status' => 'publish',
				'posts_per_page' => $num,

				'no_found_rows' => true,
				'update_post_term_cache' => false
			);

			$the_query = new WP_Query( $args );

			if ( ! $the_query->have_posts() ) {
				return '';
			}

			wp_enqueue_style( 'wp-quiz-listing', $this->plugin_url() . 'assets/css/listing.css', null, null );

			ob_start();
				include_once 'tmpl-quiz-listing.php';
			$out =  ob_get_clean();

			wp_reset_query();

			return $out;
		}

		/**
		 * Shortcode used to display quiz
		 *
		 * @return string HTML output of the shortcode
		 */
		public function register_shortcode( $atts ) {

			if ( ! isset( $atts['id'] ) ) {
				return false;
			}

			// we have an ID to work with
			$quiz = get_post( $atts['id'] );

			// check if ID is correct
			if ( ! $quiz || 'wp_quiz' !== $quiz->post_type ) {
				return "<!-- wp_quiz {$atts['id']} not found -->";
			}

			// lets go
			$this->set_quiz( $atts['id'] );
			$this->quiz->enqueue_scripts();

			return $this->quiz->render_public_quiz();
		}

		/**
		 * Set the current quiz
		 */
		public function set_quiz( $id ) {
			$quiz_type = get_post_meta( $id, 'quiz_type', true );
			$quiz_type = str_replace( '_quiz', '', $quiz_type );
			$quiz_type = 'WP_Quiz_Pro_' . ucwords( $quiz_type ) . '_Quiz';
			$this->quiz = new $quiz_type( $id );
		}

		/**
		 * [create_quiz_page description]
		 * @param  [type] $content [description]
		 * @return [type]          [description]
		 */
		public function create_quiz_page( $content ) {

			global $post;

			if ( 'wp_quiz' !== $post->post_type ) {
				return $content;
			}

			if ( ! is_single() ) {
				return $content;
			}

			$quiz_html = $this->register_shortcode( array( 'id' => $post->ID ) );

			return $quiz_html . $content;
		}

		/**
		 * [save_quiz_results description]
		 * @return [type] [description]
		 */
		public function save_quiz_results() {

			if ( ! wp_verify_nonce( $_POST['_nonce'], 'ajax-quiz-content' ) ) {
				return;
			}

			$correct   = isset( $_POST['correct'] ) ? absint( $_POST['correct'] ) : 0;
			$rid       = isset( $_POST['rid'] ) ? $_POST['rid'] : '';
			$pid       = absint( $_POST['pid'] );
			$type      = sanitize_text_field( $_POST['type'] );
			$user_ip   = $this->get_ip();
			$user_id   = get_current_user_id();
			$user_info = get_userdata( $user_id );
			$username  = is_user_logged_in() ? $user_info->user_login : 'Guest';
			$result    = '';

			$results = get_post_meta( $pid, 'results', true );

			if ( 'trivia' === $type ) {
				$rid = '';
				foreach ( $results as $result ) {
					if ( $result['min'] <= $correct && $result['max'] >= $correct ) {
						$result = $result['title'];
						break;
					}
				}
			} elseif ( 'personality' === $type ) {
				for ( $i = 0; $i < count( $results ); $i++ ) {
					if ( $i == $rid ) {
						$result = $results[ $i ]['title'];
						break;
					}
				}
			} elseif ( 'swiper' === $type ) {
				$results = $_POST['results'];
				$questions 	= get_post_meta( $pid, 'questions', true );
				foreach ( $questions as $q_key => $question ) {
					foreach ( $results as $key => $result ) {
						if ( $question['uid'] == $key ) {
							if ( '0' == $result ) {
								$questions[ $q_key ]['votesDown'] = $question['votesDown'] + 1;
							} else {
								$questions[ $q_key ]['votesUp'] = $question['votesUp'] + 1;
							}
						}
					}
				}
				update_post_meta( $pid, 'questions', $questions );
				$result = '';
			}

			// Save Result
			$settings = get_option( 'wp_quiz_pro_default_settings' );
			if ( isset( $settings['players_tracking'] ) && 1 === $settings['players_tracking'] ) {
				global $wpdb;
				$wpdb->insert(
					$wpdb->prefix . 'wp_quiz_players',
					array(
						'pid'               => $pid,
						'date'       		=> date( 'Y-m-d', time() ),
						'user_ip'           => $user_ip,
						'username'          => $username,
						'correct_answered'  => $correct,
						'result'            => $result,
						'quiz_type'         => $type,
					),
					array( '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
				);
			}

			die( 'SUCCESS!' );
		}

		/**
		 * [save_quiz_user_info description]
		 * @return [type] [description]
		 */
		public function save_quiz_user_info() {

			if ( ! wp_verify_nonce( $_POST['_nonce'], 'ajax-quiz-content' ) ) {
				return;
			}

			$output = array( 'status' => 1 );

			if ( is_email( $_POST['email'] ) ) {

				global $wpdb;
				$username	= sanitize_text_field( $_POST['username'] );
				$email		= sanitize_email( $_POST['email'] );
				$pid		= absint( $_POST['pid'] );

				$this->subscribe_user( $pid, $username, $email );
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wp_quiz_emails WHERE email = '" . $email . "'" );

				if ( ! $result ) {
					//Save info
					$wpdb->insert(
						$wpdb->prefix . 'wp_quiz_emails',
						array(
							'pid'      => $pid,
							'username' => $username,
							'email'    => $email,
							'date'     => date( 'Y-m-d', time() ),
						),
						array( '%d', '%s', '%s', '%s' )
					);
				}

				$output['status'] = 2;
			}

			wp_send_json( $output );
		}

		/**
		 * [save_quiz_fb_user_info description]
		 * @return [type] [description]
		 */
		public function save_quiz_fb_user_info() {

			if ( ! wp_verify_nonce( $_POST['_nonce'], 'ajax-quiz-content' ) ) {
				return;
			}

			$output = array( 'status' => 1 );
			if ( ! empty( $_POST['user'] ) ) {
				global $wpdb;

				$user = $_POST['user'];
				$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wp_quiz_fb_users WHERE uid = '" . $user['id'] . "'" );

				if ( ! $result ) {
					$wpdb->insert(
						$wpdb->prefix . 'wp_quiz_fb_users',
						array(
							'uid'			=> absint( $user['id'] ),
							'email'			=> isset( $user['email'] ) ? $user['email'] : '',
							'first_name'	=> $user['first_name'],
							'last_name'		=> $user['last_name'],
							'gender'		=> isset( $user['gender'] ) ? $user['gender'] : '',
							'picture'		=> isset( $user['picture'] ) ? $user['picture'] : '',
							'friends'		=> isset( $user['friends'] ) ? serialize( $user['friends'] ) : '',
							'created_at'	=> date( 'Y-m-d', time() ),
							'updated_at'	=> date( 'Y-m-d', time() ),
						),
						array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
					);

					$user['insert_id'] = $wpdb->insert_id;
				} else {
					$user['insert_id'] = $result->id;
				}

				if ( 'user' === $_POST['profile'] ) {
					$return = $this->generate_result_user_image( $_POST['pid'], $user );
				} else {
					$return = $this->generate_result_friend_image( $_POST['pid'], $user );
				}

				if ( ! empty( $return['src'] ) ) {
					$output['src'] = $return['src'];
					$output['desc'] = $return['desc'];
					$output['key'] = $return['key'];
					$output['status'] = 2;
				} else {
					$output['error'] = $return['error'];
				}
			}

			wp_send_json( $output );
		}

		/**
		 * [generate_result_user_image description]
		 * @param  [type] $post_id [description]
		 * @param  [type] $user    [description]
		 * @return [type]          [description]
		 */
		public function generate_result_user_image( $post_id, $user ) {
			global $wpdb;

			$return		= array();
			$results	= get_post_meta( $post_id, 'results', true );

			if ( extension_loaded( 'imagick' ) && ! empty( $results ) ) {

				$index 	= array_rand( $results );
				$result	= $results[ $index ];
				$result['key'] = $index;

				$play = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wp_quiz_fb_plays WHERE user_id = '" . $user['insert_id'] . "' AND pid = '" . $post_id . "'" );

				if ( ! $play ) {
					$wpdb->insert(
						$wpdb->prefix . 'wp_quiz_fb_plays',
						array(
							'user_id'	=> absint( $user['insert_id'] ),
							'pid'		=> absint( $post_id ),
						),
						array( '%d', '%d' )
					);
				}

				$names = array(
					'user_first_name'	=> $user['first_name'],
					'user_last_name'	=> $user['last_name'],
					'friend_first_name'	=> '',
					'friend_last_name'	=> '',
				);

				$profile = 'https://graph.facebook.com/' . $user['id'] . '/picture?width=320&height=320';
				$profile = $this->get_redirect_url( $profile );

				$data 	= $this->generate_fb_result( $post_id, $result, $profile, $names );
				$return	= $data;
			}

			return $return;
		}

		public function generate_result_friend_image( $post_id, $user ) {
			global $wpdb;

			$return 	= array();
			$results 	= get_post_meta( $post_id, 'results', true );

			if ( extension_loaded( 'imagick' ) && ! empty( $results ) && ! empty( $user['friends'] ) ) {

				$index 	= array_rand( $results );
				$result	= $results[ $index ];
				$result['key'] = $index;

				$index_2	= array_rand( $user['friends'] );
				$friend		= $user['friends'][ $index_2 ];

				$play = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wp_quiz_fb_plays WHERE user_id = '" . $user['insert_id'] . "' AND pid = '" . $post_id . "'" );

				if ( ! $play ) {
					$wpdb->insert(
						$wpdb->prefix . 'wp_quiz_fb_plays',
						array(
							'user_id'	=> absint( $user['insert_id'] ),
							'pid'		=> absint( $post_id ),
						),
						array( '%d', '%d' )
					);
				}

				$profile = 'https://graph.facebook.com/' . $friend['id'] . '/picture?width=320&height=320';
				$profile = $this->get_redirect_url( $profile );

				$friend_name = explode( ' ', $friend['name'] );

				$names = array(
					'user_first_name'	=> $user['first_name'],
					'user_last_name'	=> $user['last_name'],
					'friend_first_name'	=> $friend_name[0],
					'friend_last_name'	=> $friend_name[1],
				);

				$data 	= $this->generate_fb_result( $post_id, $result, $profile, $names );
				$return	= $data;
			}

			return $return;
		}

		public function generate_fb_result( $post_id, $result, $profile, $names ) {

			$return = array(
				'src'   => '',
				'desc'  => '',
				'error' => '',
			);

			$profile_tmp = null;
			$output      = null;
			$draw        = null;

			try {

				$options    = get_option( 'wp_quiz_pro_default_settings' );
				$settings   = get_post_meta( $post_id, 'settings', true );
				$find       = array( '%%userfirstname%%', '%%userlastname%%', '%%friendfirstname%%', '%%friendlastname%%' );
				$replace    = array( $names['user_first_name'], $names['user_last_name'], $names['friend_first_name'], $names['friend_last_name'] );
				$title      = str_replace( $find, $replace, $result['title'] );
				$desc       = str_replace( $find, $replace, $result['desc'] );
				$upload_dir = wp_upload_dir();

				// Load images
				$profile_tmp = download_url( $profile );
				$profile     = new Imagick( $profile_tmp );
				$profile->resizeImage( $result['proImageWidth'], $result['proImageHeight'], imagick::FILTER_LANCZOS, 0.9 );
				$profile->roundCorners( $result['imageRadius'], $result['imageRadius'] );

				// Create new image from result
				$output = new Imagick( str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $result['image'] ) );
				$output->compositeImage( $profile, Imagick::COMPOSITE_DEFAULT, $result['pos_x'], $result['pos_y'] );

				// Annotate it
				if ( ! empty( $title ) ) {

					$draw = new ImagickDraw();
					$draw->setFillColor( $settings['title_color'] );
					$draw->setGravity( 1 );
					$draw->setFontSize( $settings['title_size'] );

					if ( isset( $options['defaults']['external_font'] ) && ! empty( $options['defaults']['external_font'] ) ) {
						$external_font = str_replace( home_url( '/' ), '', $options['defaults']['external_font'] );
						$draw->setFont( '../' . $external_font );
					} else {
						$draw->setFontFamily( $settings['title_font'] );
					}

					list( $lines, $line_height ) = $this->word_wrap_annotation( $output, $draw, $title, $result['titleImageWidth'] );

					for ( $i = 0; $i < count( $lines ); $i++ ) {
						$output->annotateImage( $draw, $result['pos_title_x'], $result['pos_title_y'] + $i * $line_height, 0, $lines[ $i ] );
					}
				}

				// Save to new image
				$upload_dir['basedir'] = $upload_dir['basedir'] . '/wp_quiz-result-images';
				$upload_dir['baseurl'] = $upload_dir['baseurl'] . '/wp_quiz-result-images';
				$output_name           = 'image-' . rand( 0, 100000 ) . '.png';
				$output->writeImage( $upload_dir['basedir'] . '/' . $output_name );

				$return['src']  = $upload_dir['baseurl'] . '/' . $output_name;
				$return['desc'] = $desc;
				$return['key']  = $result['key'];

			} catch ( Exception $ex ) {
				$return['error'] = $ex->getMessage();
			}

			// Clean up
			if ( ! is_null( $profile ) && is_a( $profile, 'Imagick' ) ) {
				$profile->clear();
			}
			if ( ! is_null( $output ) && is_a( $output, 'Imagick' ) ) {
				$output->clear();
			}
			if ( ! is_null( $draw ) && is_a( $draw, 'ImagickDraw' ) ) {
				$draw->clear();
			}
			if ( ! is_null( $profile_tmp ) ) {
				@unlink( $profile_tmp );
			}

			return $return;
		}

		public function get_redirect_url( $url ) {

			$response     = wp_remote_head( $url );
			$redirect_url = wp_remote_retrieve_header( $response, 'location' );

			return $redirect_url ? $redirect_url : $url;
		}

		public function word_wrap_annotation( $image, $draw, $text, $max_width ) {

			$words       = preg_split( '%\s%', $text, -1, PREG_SPLIT_NO_EMPTY );
			$lines       = array();
			$i           = 0;
			$line_height = 0;

			while ( count( $words ) > 0 ) {
				$metrics     = $image->queryFontMetrics( $draw, implode( ' ', array_slice( $words, 0, ++$i ) ) );
				$line_height = max( $metrics['textHeight'], $line_height );

				if ( $metrics['textWidth'] > $max_width || count( $words ) < $i ) {
					if ( 1 === $i ) {
						$i++;
					}

					$lines[] = implode( ' ', array_slice( $words, 0, --$i ) );
					$words   = array_slice( $words, $i );
					$i       = 0;
				}
			}

			return array( $lines, $line_height );
		}

		public function subscribe_user( $id, $name, $email ) {

			$settings = get_post_meta( $id, 'settings', true );
			$options  = get_option( 'wp_quiz_pro_default_settings' );

			if ( '1' === $settings['force_action'] ) {
				if ( '1' === $options['mail_service'] ) {
					$this->subscribe_mailchimp( $options, $name, $email );
				} elseif ( '2' === $options['mail_service'] ) {
					$this->subscribe_getresponse( $options, $name, $email );
				} elseif ( '3' === $options['mail_service'] ) {
					$this->subscribe_aweber( $options, $name, $email );
				}
			}
		}

		private function subscribe_aweber( $options, $name, $email ) {

			// check for valid data
			if ( empty( $email ) ) {
				wp_send_json( array(
					'success' => false,
					'error'   => esc_html__( 'No email address found.', 'wp-quiz-pro' ),
				) );
			}

			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				wp_send_json( array(
					'success' => false,
					'error'   => esc_html__( 'Not a valid email address.', 'wp-quiz-pro' ),
				) );
			}

			// Call service subscription method
			try {
				$service = new WP_Quiz_Pro_Subscription_Aweber();
				$list_id = $options['aweber']['listid'];
				$status  = $service->subscribe( $name, $email, $list_id );

				wp_send_json(array(
					'success' => true,
					'status'  => $status['status'],
				));
			} catch ( Exception $e ) {
				wp_send_json(array(
					'success' => false,
					'error'   => $e->getMessage(),
				));
			}
		}

		private function subscribe_mailchimp( $options, $name, $email ) {

			$mc_api_key   = $options['mailchimp']['api_key'];
			$mc_list_id   = $options['mailchimp']['list_id'];
			$double_optin = apply_filters( 'wp_quiz_mailchimp_double_notification', false );
			$vendor_path  = $this->get_vendor_path();

			if ( $email && null !== $mc_api_key && null !== $mc_list_id ) {

				try {
					if ( ! class_exists( 'Mailchimp' ) ) {
						require_once( $vendor_path . '/Mailchimp.php' );
					}

					$list       = new Mailchimp_Lists( new Mailchimp( $mc_api_key ) );
					$merge_vars = null;

					if ( $name ) {
						$fname = $name;
						$lname = '';
						if ( $space_pos = strpos( $name, ' ' ) ) {
							$fname = substr( $name, 0, $space_pos );
							$lname = substr( $name, $space_pos );
						}
						$merge_vars = array(
							'FNAME' => $fname,
							'LNAME' => $lname,
						);
					}
					$list->subscribe( $mc_list_id, array( 'email' => $email ), $merge_vars, 'html', (bool) $double_optin, true );

				} catch ( Exception $ex ) {

				}
			}
		}

		private function subscribe_getresponse( $options, $name, $email ) {

			$gr_api_key = $options['getresponse']['api_key'];
			$gr_list_id = $options['getresponse']['campaign_name'];

			$vendor_path = $this->get_vendor_path();

			if ( $email && null !== $gr_api_key && null !== $gr_list_id ) {
				try {
					if ( ! class_exists( 'GetResponse' ) ) {
						require_once( $vendor_path . '/getresponse.php' );
					}

					$api              = new GetResponse( $gr_api_key );
					$campaign_ame     = $gr_list_id;
					$subscriber_name  = $name;
					$subscriber_email = $email;

					$result      = $api->getCampaigns( 'EQUALS', $campaign_ame );
					$campaigns   = array_keys( (array) $result );
					$campaign_id = array_pop( $campaigns );

					$api->addContact( $campaign_id, $subscriber_name, $subscriber_email );
				} catch ( Exception $ex ) {

				}
			}
		}

		public function get_vendor_path() {

			return plugin_dir_path( __FILE__ ) . 'vendor';
		}

		public function check_image_file() {

			$output = array( 'status' => 1 );
			$check  = false;
			if ( @getimagesize( $_POST['url'] ) ) {
				$check = true;
			}

			$output['check'] = $check;
			wp_send_json( $output );
		}

		public function check_video_file() {

			$output  = array( 'status' => 1 );
			$check   = false;
			$id      = $_POST['video_id'];
			$url     = "//www.youtube.com/oembed?url=http://www.youtube.com/watch?v=$id&format=json";
			$headers = get_headers( $url );
			if ( '404' !== substr( $headers[0], 9, 3 ) ) {
				$check = true;
			}

			$output['check'] = $check;
			wp_send_json( $output );
		}

		public static function activate_plugin() {

			// Don't activate on anything less than PHP 5.4.0 or WordPress 3.4
			if ( version_compare( PHP_VERSION, '5.4.0', '<' ) || version_compare( get_bloginfo( 'version' ), '3.4', '<' ) || ! function_exists( 'spl_autoload_register' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
				deactivate_plugins( basename( __FILE__ ) );
				wp_die( __( 'WP Quiz Pro requires PHP version 5.4.0 with spl extension or greater and WordPress 3.4 or greater.', 'wp-quiz-pro' ) );
			}

			//Dont't activate if wp quiz is active
			if ( defined( 'WP_QUIZ_VERSION' ) ) {
				deactivate_plugins( basename( __FILE__ ) );
				wp_die( __( 'Please deactivate WP Quiz plugin first to use the Premium features!', 'wp-quiz-pro' ) );
			}

			include( 'inc/activate-plugin.php' );

		}

		public function get_ip() {

			//Just get the headers if we can or else use the SERVER global
			if ( function_exists( 'apache_request_headers' ) ) {
				$headers = apache_request_headers();
			} else {
				$headers = $_SERVER;
			}

			//Get the forwarded IP if it exists
			if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
				$the_ip = $headers['X-Forwarded-For'];
			} elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
				$the_ip = $headers['HTTP_X_FORWARDED_FOR'];
			} else {
				$the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
			}
			return $the_ip;

		}

		public function dismiss_imagick_notice() {
			add_option( 'wp_dismiss_imagick_notice', 'true' );
		}

		public function dismiss_gdlibrary_notice() {
			add_option( 'wp_dismiss_gdlibrary_notice', 'true' );
		}

		public function wp_quiz_pro_get_debug_log() {
			$page = new WP_Quiz_Pro_Page_Support();
			$page->get_debug_log();
		}

		public function fb_share_fix() {

			$data   = array_map( 'urldecode', $_GET );
			$result = get_post_meta( $data['id'], 'results', true );
			$result = isset( $result[ $data['rid'] ] ) ? $result[ $data['rid'] ] : array();

			// Picture
			if ( 'r' === $data['pic'] ) {
				$data['source'] = $result['image'];
			} elseif ( 'f' === $data['pic'] ) {
				$data['source'] = wp_get_attachment_url( get_post_thumbnail_id( $data['id'] ) );
			} elseif ( ( substr( $data['pic'], 0, 6 ) === 'image-' ) ) {
				$upload_dir            = wp_upload_dir();
				$upload_dir['baseurl'] = $upload_dir['baseurl'] . '/wp_quiz-result-images';
				$data['source']        = $upload_dir['baseurl'] . '/' . $data['pic'] . '.png';
			} else {
				$data['source'] = false;
			}

			// Description
			if ( 'r' === $data['desc'] ) {
				$data['description'] = $result['desc'];
			} elseif ( 'e' === $data['desc'] ) {
				$data['description'] = get_post_field( 'post_excerpt', $data['id'] );
			} else {
				$data['description'] = false;
			}

			if ( $data['description'] ) {

				$first = array( '%%userfirstname%%', '%%friendfirstname%%' );
				$last  = array( '%%userlastname%%', '%%friendlastname%%' );

				$data['description'] = str_replace( $first, $data['nf'], $data['description'] );
				$data['description'] = str_replace( $last, $data['nl'], $data['description'] );
			}

			$settings = get_option( 'wp_quiz_pro_default_settings' );
			$url      = ( is_ssl() ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

			global $post;
			$pid          = $post ? $post->ID : $data['id'];
			$original_url = get_permalink( $pid );
			?>
			<html>
				<head>
					<title><?php wp_title( '' ); ?></title>
					<meta property="fb:app_id" content="<?php echo $settings['defaults']['fb_app_id'] ?>">
					<meta property="og:type" content="website">
					<meta name="twitter:card" content="summary_large_image">
					<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
					<?php if ( ! empty( $data['text'] ) ) :
						$title = get_the_title( $pid );
						$text = esc_attr( $data['text'] );

						$title = $title === $text ? $title : $title . ' - ' . $text;
					?>
					<meta property="og:title" content="<?php echo $title ?>">
					<meta property="twitter:title" content="<?php echo $title ?>">
					<?php endif; ?>
					<?php if ( ! empty( $data['source'] ) ) : ?>
					<meta property="og:image" content="<?php echo esc_url( $data['source'] ); ?>">
					<meta property="twitter:image" content="<?php echo esc_url( $data['source'] ); ?>">
						<?php list( $img_width, $img_height ) = getimagesize( $data['source'] ); ?>
						<?php if ( isset( $img_width ) && $img_width ) : ?>
							<meta property="og:image:width" content="<?php echo $img_width ?>">
						<?php else: ?>
							<meta property="og:image:width" content="600">
						<?php endif; ?>
						<?php if ( isset( $img_height ) && $img_height ) : ?>
							<meta property="og:image:height" content="<?php echo $img_height ?>">
						<?php else: ?>
							<meta property="og:image:height" content="400">
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( ! empty( $data['description'] ) ) : ?>
					<meta property="og:description" content="<?php echo esc_attr( $data['description'] ); ?>">
					<meta property="twitter:description" content="<?php echo esc_attr( $data['description'] ); ?>">
					<?php endif; ?>
					<meta http-equiv="refresh" content="0;url=<?php echo esc_url( $original_url ); ?>">
				</head>
			<body>
				Redirecting please wait....
			</body>
			</html>
			<?php
			exit;
		}

		/**
		 * [inline_script description]
		 * @return [type] [description]
		 */
		public function inline_script() {
			$settings = get_option( 'wp_quiz_pro_default_settings' );
			?>
			<script>
			var quizSiteUrl = '<?php echo home_url( '/' ) ?>';
			<?php if ( ! empty( $settings['analytics']['tracking_id'] ) ) { ?>
				(function(i,s,o,g,r,a,m) {i['GoogleAnalyticsObject']=r;i[r]=i[r]||function() {
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

				ga('create', '<?php echo $settings['analytics']['tracking_id'] ?>', 'auto');
				ga('send', 'pageview');
			<?php } ?>
			<?php if ( ! empty( $settings['defaults']['fb_app_id'] ) ) { ?>
				window.fbAsyncInit = function() {
					FB.init({
						appId    : '<?php echo $settings['defaults']['fb_app_id'] ?>',
						xfbml    : true,
						version  : 'v2.9'
					});

					FB.getLoginStatus(function( response ) {
						getLogin( response );
					});
				};

				(function(d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) {return;}
					js = d.createElement(s); js.id = id;
					js.src = "//connect.facebook.net/en_US/sdk.js";
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));
			<?php } ?>
			</script>

			<?php
			if ( is_singular( array( 'wp_quiz' ) ) && isset( $settings['defaults']['share_meta'] ) && 1 === $settings['defaults']['share_meta'] ) {
				global $post, $wpseo_og;
				$twitter_desc = $og_desc = str_replace( array( "\r", "\n" ), '', strip_tags( $post->post_excerpt ) );
				if ( defined( 'WPSEO_VERSION' ) ) {
					remove_action( 'wpseo_head', array( $wpseo_og, 'opengraph' ), 30 );
					remove_action( 'wpseo_head', array( 'WPSEO_Twitter', 'get_instance' ), 40 );
					//use description from yoast
					$twitter_desc 	= get_post_meta( $post->ID, '_yoast_wpseo_twitter-description', true );
					$og_desc		= get_post_meta( $post->ID, '_yoast_wpseo_opengraph-description', true );
				}
				?>
				<meta name="twitter:title" content="<?php echo get_the_title(); ?>">
				<meta name="twitter:description" content="<?php echo $twitter_desc; ?>">
				<meta name="twitter:domain" content="<?php echo esc_url( site_url() ); ?>">
				<meta property="og:url" content="<?php the_permalink(); ?>" />
				<meta property="og:title" content="<?php echo get_the_title(); ?>" />
				<meta property="og:description" content="<?php echo $og_desc; ?>" />
				<?php
				if ( has_post_thumbnail() ) {
					$thumb_id = get_post_thumbnail_id();
					$thumb_url_array = wp_get_attachment_image_src( $thumb_id, 'full', true );
					$thumb_url = $thumb_url_array[0];
					?>
					<meta name="twitter:card" content="summary_large_image">
					<meta name="twitter:image:src" content="<?php echo $thumb_url; ?>">
					<meta property="og:image" content="<?php echo $thumb_url; ?>" />
					<meta itemprop="image" content="<?php echo $thumb_url; ?>">
				<?php
				}
			}
		}

		public function embeded_output() {

			if ( ! isset( $_GET['wp_quiz_id'] ) ) {
				return;
			}

			$qid		= absint( $_GET['wp_quiz_id'] );
			$quiz_html	= $this->register_shortcode( array( 'id' => $qid ) );
			$settings	= get_post_meta( $qid, 'settings', true );
			if ( empty( $quiz_html ) ) {
				return;
			}
			?>
				<link rel='stylesheet' href='<?php echo $this->plugin_url() . 'assets/css/main.css'; ?>' type='text/css' media='all' />
				<link rel='stylesheet' href='<?php echo $this->plugin_url() . 'assets/css/transition.min.css'; ?>' type='text/css' media='all' />
				<link rel='stylesheet' href='<?php echo $this->plugin_url() . 'assets/css/embed.min.css'; ?>' type='text/css' media='all' />
				<style>
					.wq_embedToggleQuizCtr{ display: none; }
				</style>
			<?php
			if ( 'traditional' === $settings['skin'] ) {
				?>
					<link rel='stylesheet' href='<?php echo $this->plugin_url() . 'assets/css/traditional-skin.css'; ?>' type='text/css' media='all' />
				<?php
			} elseif ( 'flat' === $settings['skin'] ) {
				?>
					<link rel='stylesheet' href='<?php echo $this->plugin_url() . 'assets/css/flat-skin.css'; ?>' type='text/css' media='all' />
				<?php
			}
			$this->inline_script();
			?>
				<script>
					var wq_l10n = {"correct": "Correct !", "wrong": "Wrong !","captionTrivia":"You got %%score%% out of %%total%%","captionTriviaFB":"I got %%score%% out of %%total%%, and you?","youVoted":"You voted","nonce": "<?php echo wp_create_nonce( 'ajax-quiz-content' ) ?>"};
				</script>
			<?php

			echo '<div class="wq_embed">' . $quiz_html . '</div>';
			?>
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
				<script src="<?php echo $this->plugin_url() . 'assets/js/embed.min.js'; ?>"></script>
				<script src="<?php echo $this->plugin_url() . 'assets/js/transition.min.js'; ?>"></script>
				<script src="<?php echo $this->plugin_url() . 'assets/js/jquery.flip.min.js'; ?>"></script>
				<script src="<?php echo $this->plugin_url() . 'assets/js/hammer.min.js'; ?>"></script>
				<script src="<?php echo $this->plugin_url() . 'assets/js/dynamics.min.js'; ?>"></script>
				<script src="<?php echo $this->plugin_url() . 'assets/js/jquery.jTinder.min.js'; ?>"></script>
				<script src="<?php echo $this->plugin_url() . 'assets/js/main.min.js'; ?>"></script>
			<?php
			die();
		}

		/**
		 * [connect_aweber description]
		 * @return [type] [description]
		 */
		public function connect_aweber() {

			// check for data
			$aweber_code = isset( $_REQUEST['aweber_code'] ) ? $_REQUEST['aweber_code'] : array();
			if ( empty( $aweber_code ) ) {
				wp_send_json( array(
					'success' => false,
					'error' => esc_html__( 'No aweber authorization code found.', 'wp-quiz-pro' ),
				) );
			}

			try {
				$service = new WP_Quiz_Pro_Subscription_Aweber();
				$data = $service->connect( $aweber_code );

				wp_send_json(array(
					'success' => true,
					'data' => $data,
				));
			} catch ( Exception $e ) {
				wp_send_json(array(
					'success' => false,
					'error' => $e->getMessage(),
				));
			}
		}

		/**
		 * Get plugin directory.
		 * @return string
		 */
		public function plugin_dir() {
			if ( is_null( $this->plugin_dir ) ) {
				$this->plugin_dir = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/';
			}
			return $this->plugin_dir;
		}

		/**
		 * Get plugin uri.
		 * @return string
		 */
		public function plugin_url() {
			if ( is_null( $this->plugin_url ) ) {
				$this->plugin_url = untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/';
			}
			return $this->plugin_url;
		}
	}

	/**
	 * Main instance of WP_Quiz_Pro_Plugin.
	 *
	 * Returns the main instance of WP_Quiz_Pro_Plugin to prevent the need to use globals.
	 *
	 * @return WP_Quiz_Pro_Plugin
	 */

	function wp_quiz_pro() {
		return WP_Quiz_Pro_Plugin::get_instance();
	}

endif;

add_action( 'plugins_loaded', 'wp_quiz_pro', 10 );
register_activation_hook( __FILE__, array( 'WP_Quiz_Pro_Plugin', 'activate_plugin' ) );
