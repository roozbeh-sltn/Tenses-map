<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WP_Quiz_Pro_Admin {

	/**
	 * The Constructor
	 */
	public function __construct() {

		// Common
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		add_action( 'admin_menu', array( $this, 'register_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'upload_mimes', array( $this, 'add_custom_upload_mimes' ) );

		// List
		add_filter( 'manage_edit-wp_quiz_columns', array( $this, 'wp_quiz_columns' ) );
		add_action( 'manage_wp_quiz_posts_custom_column', array( $this, 'manage_wp_quiz_columns' ), 10, 2 );
		add_filter( 'screen_layout_columns', array( $this, 'screen_layout_columns' ), 10, 2 );

		// Edit
		add_action( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_action( 'edit_form_after_title', array( $this, 'add_shortcode_before_editor' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'admin_post_wp_quiz', array( $this, 'save_post_form' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * [admin_notice description]
	 * @return [type] [description]
	 */
	public function admin_notice() {

		if ( ! extension_loaded( 'imagick' ) && ! get_option( 'wp_dismiss_imagick_notice' ) ) : ?>
		<div id="message" class="wp-quiz-notice error notice is-dismissible">
			<p><strong><?php esc_html_e( 'WP Quiz Pro Notice: ', 'wp-quiz-pro' ) ?></strong><?php esc_html_e( 'PHP imagick extension is missing, please install this extention to enable the Facebook Quiz type.', 'wp-quiz-pro' ) ?></p>
		</div>
		<script>
			jQuery( document ).on( 'click', '.wp-quiz-notice .notice-dismiss', function() {
				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: 'dismiss_imagick_notice'
					}
				});
			});
		</script>
		<?php
		endif;

		if ( ! extension_loaded( 'gd' ) && ! get_option( 'wp_dismiss_gdlibrary_notice' ) ) : ?>
		<div id="message" class="wp-quiz-notice-2 error notice is-dismissible">
			<p><strong><?php esc_html_e( 'WP Quiz Pro Notice: ', 'wp-quiz-pro' ) ?></strong><?php esc_html_e( 'PHP GD library is missing, please install this extention to enable the Facebook Quiz type.', 'wp-quiz-pro' ) ?></p>
		</div>
		<script>
			jQuery( document ).on( 'click', '.wp-quiz-notice-2 .notice-dismiss', function() {
				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: 'dismiss_gdlibrary_notice'
					}
				});
			});
		</script>
		<?php
		endif;
	}

	/**
	 * [register_pages description]
	 * @return [type] [description]
	 */
	public function register_pages() {

		$parent = 'edit.php?post_type=wp_quiz';

		// General Settings
		$page_hook = add_submenu_page(
			$parent,
			esc_html__( 'General Settings', 'wp-quiz-pro' ),
			esc_html__( 'General Settings', 'wp-quiz-pro' ),
			'manage_options',
			'wp_quiz_config',
			array( 'WP_Quiz_Pro_Page_Config', 'page' )
		);
		add_action( 'load-' . $page_hook, array( 'WP_Quiz_Pro_Page_Config', 'load' ) );
		add_action( 'admin_print_styles-' . $page_hook, array( 'WP_Quiz_Pro_Page_Config', 'admin_print_styles' ) );

		// Players
		$settings = get_option( 'wp_quiz_pro_default_settings' );
		if ( isset( $settings['players_tracking'] ) && 1 === $settings['players_tracking'] ) {
			$page_hook = add_submenu_page(
				$parent,
				esc_html__( 'Players', 'wp-quiz-pro' ),
				esc_html__( 'Players', 'wp-quiz-pro' ),
				'manage_options',
				'wp_quiz_players',
				array( 'WP_Quiz_Pro_Page_Players', 'page' )
			);
			add_action( 'load-' . $page_hook, array( 'WP_Quiz_Pro_Page_Players', 'load' ) );
			add_action( 'admin_print_styles-' . $page_hook, array( 'WP_Quiz_Pro_Page_Players', 'admin_print_styles' ) );
		}

		// Email Subscriber
		$page_hook = add_submenu_page(
			$parent,
			esc_html__( 'E-mail Subscribers', 'wp-quiz-pro' ),
			esc_html__( 'E-mail Subscribers', 'wp-quiz-pro' ),
			'manage_options',
			'wp_quiz_email_subs',
			array( 'WP_Quiz_Pro_Page_Email_Subs', 'page' )
		);
		add_action( 'load-' . $page_hook, array( 'WP_Quiz_Pro_Page_Email_Subs', 'load' ) );
		add_action( 'admin_print_styles-' . $page_hook, array( 'WP_Quiz_Pro_Page_Email_Subs', 'admin_print_styles' ) );

		// Import/Export
		$page_hook = add_submenu_page(
			$parent,
			esc_html__( 'Import/Export', 'wp-quiz-pro' ),
			esc_html__( 'Import/Export', 'wp-quiz-pro' ),
			'manage_options',
			'wp_quiz_ie',
			array( 'WP_Quiz_Pro_Page_Import_Export', 'page' )
		);
		add_action( 'load-' . $page_hook, array( 'WP_Quiz_Pro_Page_Import_Export', 'load' ) );
		add_action( 'admin_print_styles-' . $page_hook, array( 'WP_Quiz_Pro_Page_Import_Export', 'admin_print_styles' ) );

		$page_hook = add_submenu_page(
			'edit.php?post_type=wp_quiz',
			__( 'Get Support for WP Quiz Pro', 'wp-quiz-pro' ),
			__( 'Support', 'wp-quiz-pro' ),
			'manage_options',
			'wp_quiz_support',
			array( 'WP_Quiz_Pro_Page_Support',
				'page'
			)
		);
		add_action( 'load-' . $page_hook, array( 'WP_Quiz_Pro_Page_Support', 'load' ) );
		add_action( 'admin_print_styles-' . $page_hook, array( 'WP_Quiz_Pro_Page_Support', 'admin_print_styles' ) );

	}

	/**
	 * Register admin JavaScript
	 *
	 * @param  [type] $hook [description]
	 * @return [type]       [description]
	 */
	public function enqueue_scripts( $hook ) {

		global $typenow;

		if ( 'wp_quiz' !== $typenow ) {
			return;
		}

		if ( in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'wp-quiz-jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css' );
			wp_enqueue_style( 'wp_quiz_pro-css', wp_quiz_pro()->plugin_url() . 'assets/css/new-quiz.css', array(), wp_quiz_pro()->version );
			wp_enqueue_style( 'semantic-checkbox-css', wp_quiz_pro()->plugin_url() . 'assets/css/checkbox.min.css', array(), wp_quiz_pro()->version );
			wp_enqueue_style( 'chosen-css', wp_quiz_pro()->plugin_url() . 'assets/css/chosen.min.css', array(), wp_quiz_pro()->version );
			wp_enqueue_style( 'semantic-embed-css', wp_quiz_pro()->plugin_url() . 'assets/css/embed.min.css', array(), wp_quiz_pro()->version );
			wp_enqueue_style( 'tipsy-css', wp_quiz_pro()->plugin_url() . 'assets/css/tipsy.css', array(), wp_quiz_pro()->version );

			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-resizable' );
			wp_enqueue_script( 'wp_quiz_pro-react', wp_quiz_pro()->plugin_url() . 'assets/js/content.min.js', array( 'jquery', 'semantic-checkbox-js', 'chosen-js', 'wp-color-picker', 'leanmodal-js', 'tipsy-js' ), wp_quiz_pro()->version, true );
			wp_enqueue_script( 'wp_quiz_pro-bootstrap', wp_quiz_pro()->plugin_url() . 'assets/js/bootstrap.min.js', array( 'jquery' ), wp_quiz_pro()->version );
			wp_enqueue_script( 'semantic-checkbox-js', wp_quiz_pro()->plugin_url() . 'assets/js/checkbox.min.js', array( 'jquery' ), wp_quiz_pro()->version );
			wp_enqueue_script( 'chosen-js', wp_quiz_pro()->plugin_url() . 'assets/js/chosen.jquery.min.js', array( 'jquery' ), wp_quiz_pro()->version );
			wp_enqueue_script( 'semantic-embed-js', wp_quiz_pro()->plugin_url() . 'assets/js/embed.min.js', array( 'jquery' ), wp_quiz_pro()->version );
			wp_enqueue_script( 'leanmodal-js', wp_quiz_pro()->plugin_url() . 'assets/js/jquery.leanModal.min.js', array( 'jquery' ), wp_quiz_pro()->version );
			wp_enqueue_script( 'tipsy-js', wp_quiz_pro()->plugin_url() . 'assets/js/jquery.tipsy.js', array( 'jquery' ), wp_quiz_pro()->version );

			wp_localize_script( 'wp_quiz_pro-react', 'wq_l10n', array(
				'labelSelectType' 		 => esc_html__( 'Select Quiz Type', 'wp-quiz-pro' ),
				'content'				 => esc_html__( 'Content', 'wp-quiz-pro' ),
				'styling'				 => esc_html__( 'Styling', 'wp-quiz-pro' ),
				'settings'				 => esc_html__( 'Settings', 'wp-quiz-pro' ),
				'questions'				 => esc_html__( 'Questions', 'wp-quiz-pro' ),
				'questionSingle'		 => esc_html__( 'Question', 'wp-quiz-pro' ),
				'addQuestion'			 => esc_html__( 'Add Question', 'wp-quiz-pro' ),
				'editQuestion'			 => esc_html__( 'Edit Question', 'wp-quiz-pro' ),
				'results'				 => esc_html__( 'Results', 'wp-quiz-pro' ),
				'addResult' 			 => esc_html__( 'Add Result', 'wp-quiz-pro' ),
				'editResult' 			 => esc_html__( 'Edit Result', 'wp-quiz-pro' ),
				'addAnswer' 			 => esc_html__( 'Add Answer', 'wp-quiz-pro' ),
				'editAnswer'			 => esc_html__( 'Edit Answer', 'wp-quiz-pro' ),
				'addExplanation' 		 => esc_html__( 'Add Explanation', 'wp-quiz-pro' ),
				'editExplanation'		 => esc_html__( 'Edit Explanation', 'wp-quiz-pro' ),
				'edit'					 => esc_html__( 'Edit', 'wp-quiz-pro' ),
				'delete' 				 => esc_html__( 'Delete', 'wp-quiz-pro' ),
				'question'				 => esc_html_x( 'Question Title', 'input label', 'wp-quiz-pro' ),
				'result' 				 => esc_html_x( 'Result Title', 'input label', 'wp-quiz-pro' ),
				'answer' 				 => esc_html_x( 'Answer Title', 'input label', 'wp-quiz-pro' ),
				'explanation'			 => esc_html_x( 'Explanation', 'input label', 'wp-quiz-pro' ),
				'image'					 => esc_html_x( 'Image', 'input label', 'wp-quiz-pro' ),
				'frontImage'			 => esc_html_x( 'Front Image', 'input label', 'wp-quiz-pro' ),
				'backImage'				 => esc_html_x( 'Back Image', 'input label', 'wp-quiz-pro' ),
				'backImageDesc'			 => esc_html_x( 'Back Image Description', 'input label', 'wp-quiz-pro' ),
				'votesUp'				 => esc_html_x( 'Votes Up', 'input label', 'wp-quiz-pro' ),
				'votesDown'				 => esc_html_x( 'Votes Down', 'input label', 'wp-quiz-pro' ),
				'imageCredit'			 => esc_html_x( 'Image Credit', 'input label', 'wp-quiz-pro' ),
				'mediaType' 			 => esc_html_x( 'Media Type', 'input label', 'wp-quiz-pro' ),
				'videoUrl'				 => esc_html_x( 'Youtube/Vimeo/Custom URL', 'input label', 'wp-quiz-pro' ),
				'placeholderImage' 		 => esc_html_x( 'Image Placeholder', 'input label', 'wp-quiz-pro' ),
				'isCorrect' 			 => esc_html_x( 'Is Correct Answer', 'input label', 'wp-quiz-pro' ),
				'minCorrect' 			 => esc_html_x( 'Minimum Correct', 'input label', 'wp-quiz-pro' ),
				'maxCorrect'			 => esc_html_x( 'Maximum Correct', 'input label', 'wp-quiz-pro' ),
				'minScore'				 => esc_html_x( 'Minimum Score', 'input label', 'wp-quiz-pro' ),
				'maxScore'				 => esc_html_x( 'Maximum Score', 'input label', 'wp-quiz-pro' ),
				'desc'					 => esc_html_x( 'Description', 'input label', 'wp-quiz-pro' ),
				'shtDesc'				 => esc_html_x( 'Short Description', 'input label', 'wp-quiz-pro' ),
				'pointsResult'			 => esc_html_x( 'Result Points', 'input label', 'wp-quiz-pro' ),
				'pointsExplain'			 => esc_html__( '(Association: 0-no, 1-normal, 2-strong)', 'wp-quiz-pro' ),
				'lngDesc'				 => esc_html_x( 'Long Description', 'input label', 'wp-quiz-pro' ),
				'cancel' 				 => esc_html__( 'Cancel', 'wp-quiz-pro' ),
				'saveChanges' 			 => esc_html__( 'Save Changes', 'wp-quiz-pro' ),
				'videoEmbed' 			 => esc_html__( 'Video/Custom Embed', 'wp-quiz-pro' ),
				'noMedia'				 => esc_html__( 'No Media', 'wp-quiz-pro' ),
				'generalSettings'		 => esc_html__( 'General Settings', 'wp-quiz-pro' ),
				'randomizeQuestions'	 => esc_html__( 'Randomize Questions', 'wp-quiz-pro' ),
				'randomizeAnswers'		 => esc_html__( 'Randomize Answers', 'wp-quiz-pro' ),
				'restartQuestions'		 => esc_html__( 'Restart Questions', 'wp-quiz-pro' ),
				'promote'				 => esc_html__( 'Promote the plugin', 'wp-quiz-pro' ),
				'embedToggle'			 => esc_html__( 'Show embed code toggle', 'wp-quiz-pro' ),
				'shareButtons'			 => esc_html__( 'Share buttons', 'wp-quiz-pro' ),
				'countDown'				 => esc_html__( 'Countdown timer [Seconds/question]', 'wp-quiz-pro' ),
				'multipleExplain'		 => esc_html__( '(applies to multiple page layout)', 'wp-quiz-pro' ),
				'autoScroll'			 => esc_html__( 'Auto scroll to next question', 'wp-quiz-pro' ),
				'endAnswers'			 => esc_html__( 'Show right/wrong answers at the end of quiz', 'wp-quiz-pro' ),
				'singleExplain'			 => esc_html__( '(applies to single page layout)', 'wp-quiz-pro' ),
				'forceAction'			 => esc_html__( 'Force action to see results', 'wp-quiz-pro' ),
				'forceAction0'			 => esc_html__( 'No Action', 'wp-quiz-pro' ),
				'forceAction1'			 => esc_html__( 'Capture Email', 'wp-quiz-pro' ),
				'forceAction2'			 => esc_html__( 'Facebook Share', 'wp-quiz-pro' ),
				'showAds'				 => esc_html__( 'Show Ads', 'wp-quiz-pro' ),
				'adsAfterN'				 => esc_html__( 'Ads after every nth question', 'wp-quiz-pro' ),
				'repeatAds'				 => esc_html__( 'Repeat Ads', 'wp-quiz-pro' ),
				'adCodes'				 => esc_html__( 'Ad Codes', 'wp-quiz-pro' ),
				'adCodesDesc'			 => esc_html__( 'comma separated codes', 'wp-quiz-pro' ),
				'customizeLayout'		 => esc_html__( 'Customize Layout and Colors', 'wp-quiz-pro' ),
				'questionsLayout'		 => esc_html__( 'Questions layout', 'wp-quiz-pro' ),
				'showAll'				 => esc_html__( 'Show all', 'wp-quiz-pro' ),
				'mutiplePages'			 => esc_html__( 'Mutiple pages', 'wp-quiz-pro' ),
				'chooseSkin'			 => esc_html__( 'Choose skin', 'wp-quiz-pro' ),
				'traditionalSkin'		 => esc_html__( 'Traditional Skin', 'wp-quiz-pro' ),
				'flatSkin'				 => esc_html__( 'Modern Flat Skin', 'wp-quiz-pro' ),
				'progressColor'			 => esc_html__( 'Progress bar color', 'wp-quiz-pro' ),
				'questionColor'			 => esc_html__( 'Question font color', 'wp-quiz-pro' ),
				'questionBgColor'		 => esc_html__( 'Question background color', 'wp-quiz-pro' ),
				'titleColor'			 => esc_html__( 'Result title color', 'wp-quiz-pro' ),
				'titleSize'				 => esc_html__( 'Result title font size', 'wp-quiz-pro' ),
				'titleFont'				 => esc_html__( 'Result title font', 'wp-quiz-pro' ),
				'chooseProfile'			 => esc_html__( 'Select Profile', 'wp-quiz-pro' ),
				'userProfile'			 => esc_html__( 'User Profile Image', 'wp-quiz-pro' ),
				'friendProfile'			 => esc_html__( 'Friend Profile Image', 'wp-quiz-pro' ),
				'animationIn'			 => esc_html__( 'Animation In', 'wp-quiz-pro' ),
				'animationOut'			 => esc_html__( 'Animation Out', 'wp-quiz-pro' ),
				'quizSize'				 => esc_html__( 'Quiz Size', 'wp-quiz-pro' ),
				'custom'				 => esc_html__( 'Custom', 'wp-quiz-pro' ),
				'customSize'			 => esc_html__( 'Custom Size', 'wp-quiz-pro' ),
				'width'					 => esc_html__( 'Width:' , 'wp-quiz-pro' ),
				'height'				 => esc_html__( 'Height:' , 'wp-quiz-pro' ),
				'customExplain'			 => esc_html__( 'set width and height in px', 'wp-quiz-pro' ),
				'fullWidth'				 => esc_html__( 'Full Width (responsive)', 'wp-quiz-pro' ),
				'answers'				 => esc_html__( 'Answers', 'wp-quiz-pro' ),
				'upload'				 => esc_html__( 'Upload', 'wp-quiz-pro' ),
				'uploadImage'			 => esc_html__( 'Upload Image', 'wp-quiz-pro' ),
				'preview'				 => esc_html__( 'Preview', 'wp-quiz-pro' ),
				'previewImage'			 => esc_html__( 'Preview Image', 'wp-quiz-pro' ),
				'previewMedia'			 => esc_html__( 'Preview Video/Media', 'wp-quiz-pro' ),
				'PrePosition'			 => esc_html__( 'Preview/Position', 'wp-quiz-pro' ),
				'prePositionImage'		 => esc_html__( 'Preview Image and set profile position', 'wp-quiz-pro' ),
				'sliderTitle'			 => esc_html__( 'image border radius', 'wp-quiz-pro' ),
				'ajax_url'				 => esc_url( admin_url( 'admin-ajax.php' ) ),
				'personalityNotice'		 => esc_html__( 'Please add the Results and save the draft before adding questions', 'wp-quiz-pro' ),
				'fbnameNotice'			 => esc_html__( 'Possible name substiution (%%userfirstname%% = user first name, %%userlastname%% = user last name, %%friendfirstname%% = friend first name, %%friendlastname%% = friend last name)', 'wp-quiz-pro' ),
				'fbprofileNotice'		 => esc_html__( 'Friend profile image will only work if the current quiz player/user has Facebook friends that has also authorized your app id to read their friends list.', 'wp-quiz-pro' ),
			) );
		}
        if( is_rtl() ) {
            wp_enqueue_style( 'wp_quiz_pro-rtl-css', wp_quiz_pro()->plugin_url() . 'assets/css/quiz-admin-rtl.css', array(), wp_quiz_pro()->version );
        }
		add_thickbox();
	}

	/**
	 * [wp_quiz_columns description]
	 *
	 * @param  [type] $columns [description]
	 * @return [type]          [description]
	 */
	public function wp_quiz_columns( $columns ) {

		$settings = get_option( 'wp_quiz_pro_default_settings' );

		$new_columns['cb']        = '<input type="checkbox" />';
		$new_columns['title']     = esc_html__( 'Quiz Name', 'wp-quiz-pro' );
		$new_columns['shortcode'] = esc_html__( 'Shortcode', 'wp-quiz-pro' );
		$new_columns['embed']     = esc_html__( 'Embed Code', 'wp-quiz-pro' );

		if ( isset( $settings['players_tracking'] ) && 1 === $settings['players_tracking'] ) {
			$new_columns['plays']   = esc_html__( 'Plays', 'wp-quiz-pro' );
		}

		$new_columns['type'] = esc_html__( 'Quiz type', 'wp-quiz-pro' );
		$new_columns['date'] = esc_html__( 'Date', 'wp-quiz-pro' );

		return $new_columns;
	}

	/**
	 * [manage_wp_quiz_columns description]
	 *
	 * @param  [type] $column_name [description]
	 * @param  [type] $id          [description]
	 * @return [type]              [description]
	 */
	public function manage_wp_quiz_columns( $column_name, $id ) {

		global $wpdb;
		$type = get_post_meta( $id, 'quiz_type', true );

		switch ( $column_name ) {

			case 'shortcode':
				echo '<div class="field"><input type="text" readonly value="[wp_quiz_pro id=&quot;' . $id . '&quot;]" onClick="this.select();" style="width:100%;"></div>';
				break;

			case 'embed':
				$site_url = get_site_url() . '/?wp_quiz_id=' . $id;
				$iframe   = '<iframe frameborder="0" width="600" height="800" src="' . $site_url . '"></iframe>';
				echo '<div class="field"><input type="text" readonly value="' . htmlentities( $iframe ) . '" onClick="this.select();" style="width:100%;"></div>';
				break;

			case 'plays':
				$play_count = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $wpdb->prefix . "wp_quiz_players` WHERE  pid='" . $id . "'" );
				$fb_play_count = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $wpdb->prefix . "wp_quiz_fb_plays` WHERE  pid='" . $id . "'" );
				if ( 'flip' === $type ) {
					$play_count = 'n/a';
				} else if ( 'fb_quiz' === $type ) {
					$play_count = $fb_play_count;
				}
				echo $play_count;
				break;

			case 'type':
				if ( $type ) {
					echo ucfirst( str_replace( '_', ' ', $type ) );
				}
				break;
		}
	}

	/**
	 * [enter_title_here description]
	 * @param  [type] $text [description]
	 * @return [type]       [description]
	 */
	public function enter_title_here( $text ) {

		global $typenow;

		if ( 'wp_quiz' !== $typenow ) {
			return $text;
		}

		return esc_html_x( 'Quiz Title', 'new quiz title placeholder', 'wp-quiz-pro' );
	}

	/**
	 * [add_shortcode_before_editor description]
	 */
	public function add_shortcode_before_editor() {

		global $typenow;

		if ( 'wp_quiz' === $typenow && isset( $_GET['post'] ) ) {
			echo '<div class="inside"><strong style="padding: 0 10px;">' . esc_html__( 'Shortcode:', 'wp-quiz-pro' ) . '</strong> <input type="text" value=\'[wp_quiz_pro id="' . trim( $_GET['post'] ) . '"]\' readonly="readonly" /></div>';
		}
	}

	/**
	 * [add_meta_boxes description]
	 */
	public function add_meta_boxes() {

		add_meta_box(
			'quiz-content',
			esc_html_x( 'Quiz', 'metabox title', 'wp-quiz-pro' ),
			array( $this, 'render_meta_box' ),
			'wp_quiz',
			'normal',
			'high'
		);
	}

	/**
	 * [render_meta_box description]
	 * @return [type] [description]
	 */
	public function render_meta_box() {

		$fb_quiz = extension_loaded( 'imagick' ) ? esc_html__( 'Facebook Quiz', 'wp-quiz-pro' ) : esc_html__( 'Facebook Quiz (ImageMagic Not Found)', 'wp-quiz-pro' );
		$quiz_type = get_post_meta( get_the_ID(), 'quiz_type', true );

		$quiz_types = array(
			'trivia' 		=> esc_html__( 'Trivia', 'wp-quiz-pro' ),
			'personality'	=> esc_html__( 'Personality', 'wp-quiz-pro' ),
			'swiper'		=> esc_html__( 'Swiper', 'wp-quiz-pro' ),
			'flip'			=> esc_html__( 'Flip Cards', 'wp-quiz-pro' ),
			'fb_quiz'		=> $fb_quiz,
		);

		$animations = array(
			'fade',
			'scale',
			'fade up',
			'fade down',
			'fade left',
			'fade right',
			'horizontal flip',
			'vertical flip',
			'drop',
			'fly left',
			'fly right',
			'fly up',
			'fly down',
			'swing left',
			'swing right',
			'swing up',
			'swing down',
			'browse',
			'browse right',
			'slide down',
			'slide up',
			'slide left',
			'slide right',
		);

		$fonts = array();
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			$imagick = new Imagick();
			$fonts   = $imagick->queryFonts();
			$imagick->clear();
		}

		$share_buttons = array(
			'fb' => esc_html__( 'Facebook', 'wp-quiz-pro' ),
			'tw' => esc_html__( 'Twitter', 'wp-quiz-pro' ),
			'g+' => esc_html__( 'Google +', 'wp-quiz-pro' ),
			'vk' => esc_html__( 'VK', 'wp-quiz-pro' ),
		);

		$defaults = get_option( 'wp_quiz_pro_default_settings' );
		unset( $defaults['analytics'] );
		unset( $defaults['mail_service'] );
		unset( $defaults['mailchimp'] );
		unset( $defaults['getresponse'] );
		unset( $defaults['ad_code'] );
		unset( $defaults['share_meta'] );
		unset( $defaults['players_tracking'] );

		foreach ( $defaults as $key => $value ) {

			$defaults[ $key ]['question_layout'] 	 = 'single';
			$defaults[ $key ]['skin'] 			     = 'flat';
			$defaults[ $key ]['bar_color']		     = '#00c479';
			$defaults[ $key ]['font_color']		     = '#444';
			$defaults[ $key ]['background_color']	 = '';
			$defaults[ $key ]['animation_in'] 	     = 'fade';
			$defaults[ $key ]['animation_out']	     = 'fade';
			$defaults[ $key ]['size']				 = 'full';
			$defaults[ $key ]['custom_width']		 = '338';
			$defaults[ $key ]['custom_height']	     = '468';
			$defaults[ $key ]['profile']			 = 'user';
			$defaults[ $key ]['title_size']		     = '20';
			$defaults[ $key ]['title_font']		     = 'Helvetica-Bold';
			$defaults[ $key ]['share_buttons']	     = array( 'fb', 'tw', 'g+', 'vk' );
		}

		wp_localize_script( 'wp_quiz_pro-react', 'quiz', array(
			'types' 			=> $quiz_types,
			'typeSelected' 		=> '' === $quiz_type ? 'trivia' : $quiz_type,
			'nonce' 			=> wp_create_nonce( 'quiz-content' ),
			'questions' 		=> get_post_meta( get_the_ID(), 'questions', true ),
			'results' 			=> get_post_meta( get_the_ID(), 'results', true ),
			'settings' 			=> get_post_meta( get_the_ID(), 'settings', true ),
			'defaultSettings' 	=> $defaults['defaults'],
			'animations'		=> $animations,
			'shareButtons'		=> $share_buttons,
			'fonts'				=> $fonts,
			'profileUrl'		=> wp_quiz_pro()->plugin_url() . 'assets/image/avatar.jpg',
			'titleUrl'			=> wp_quiz_pro()->plugin_url() . 'assets/image/title.png',
			'defaultSkins'		=> array( 'flat' => '#ecf0f1', 'trad' => '#f2f2f2' ),
			'imagickActive'		=> extension_loaded( 'imagick' ) ? 'true' : 'false',
		) );
		?>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$('#tabs').tab();
					$('.color-field').wpColorPicker();
				});
			</script>
		<?php
	}

	/**
	 * [save_post description]
	 * @param  [type] $post_id [description]
	 * @param  [type] $post    [description]
	 * @return [type]          [description]
	 */
	public function save_post( $post_id, $post ) {

		if ( ! wp_verify_nonce( filter_input( INPUT_POST, 'quiz_nonce' ), 'quiz-content' ) ) {
			return $post_id;
		}

		$post_type = get_post_type_object( $post->post_type );
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		$quiz_type = get_post_meta( $post_id, 'quiz_type', true );
		$new_quiz_type = filter_input( INPUT_POST, 'quiz_type', FILTER_SANITIZE_STRING );
		if ( $new_quiz_type && ( '' === $quiz_type || $quiz_type !== $new_quiz_type ) ) {
			update_post_meta( $post_id, 'quiz_type', $new_quiz_type );
		}

		// @TODO: sanitize all inputs
		$settings = $this->sanitize_checkboxes( $_POST['settings'] );
		$questions = '';
		if ( isset( $_POST['questions'] ) ) {
			$questions = array_values( $_POST['questions'] );
			foreach ( $questions as $key => $question ) {
				if ( isset( $questions[ $key ]['answers'] ) ) {
					$questions[ $key ]['answers']  = array_values( $question['answers'] );
				}
			}
		}

		update_post_meta( $post_id, 'questions', $questions );
		update_post_meta( $post_id, 'settings', $settings );

		if ( isset( $_POST['results'] ) && ! empty( $_POST['results'] ) ) {
			update_post_meta( $post_id, 'results', $_POST['results'] );
		}
	}

	/**
	 * [sanitize_checkboxes description]
	 * @param  [type] $post [description]
	 * @return [type]       [description]
	 */
	public function sanitize_checkboxes( $post ) {

		$settings_key = array(
			'rand_questions',
			'rand_answers',
			'restart_questions',
			'promote_plugin',
			'embed_toggle',
			'show_ads',
			'show_countdown',
			'timer',
			'auto_scroll',
			'repeat_ads',
		);

		foreach (  $settings_key as  $key ) {
			if ( isset( $post[ $key ] ) && '1' === $post[ $key ] ) {
				$post[ $key ] = 1;
			} else {
				$post[ $key ] = 0;
			}
		}

		return $post;
	}

	/**
	 * [screen_layout_columns description]
	 * @param  [type] $columns   [description]
	 * @param  [type] $screen_id [description]
	 * @return [type]            [description]
	 */
	public function screen_layout_columns( $columns, $screen_id ) {

		if ( 'wp_quiz_page_wp_quiz_config' === $screen_id ) {
			$columns['wp_quiz_page_wp_quiz_config'] = 2;
		} else if ( 'wp_quiz_page_wp_quiz_ie' === $screen_id ) {
			$columns['wp_quiz_page_wp_quiz_ie'] = 2;
		}

		return $columns;
	}

	/**
	 * [save_post_form description]
	 * @return [type] [description]
	 */
	public function save_post_form() {

		// Allowed Pages
		if ( ! in_array( $_POST['page'], array( 'wp_quiz_config' ) ) ) {
			wp_die( esc_html__( 'Cheating, huh?', 'wp-quiz-pro' ) );
		}

		// Check nonce
		check_admin_referer( $_POST['page'] . '_page' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Cheating, huh?', 'wp-quiz-pro' ) );
		}

		// Call method to save data
		$location = '';
		if ( 'wp_quiz_config' === $_POST['page'] ) {
			WP_Quiz_Pro_Page_Config::save_post_form();
			$location = admin_url() . 'edit.php?post_type=wp_quiz&page=wp_quiz_config';
		}

		// Back to topic
		$location = add_query_arg( 'message', 3, $location );
		wp_safe_redirect( $location );

		exit;
	}

	public function add_custom_upload_mimes( $mimes ) {

		$mimes['ttf'] = 'application/x-font-ttf';

		return $mimes;
	}
}
