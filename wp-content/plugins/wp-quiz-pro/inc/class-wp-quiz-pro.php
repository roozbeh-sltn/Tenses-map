<?php
/**
 * Generic WP_Quiz_Pro class. Extended by library specific classes.
 */
class WP_Quiz_Pro {

	/**
	 * quiz ID
	 */
	public $id = 0;

	/**
	 * quiz settings
	 */
	public $settings = array();

	/**
	 * quiz questions
	 */
	public $questions = array();

	/**
	 * quiz results
	 */
	public $results = array();

	/**
	 * quiz type
	 */
	public $type = '';

	/**
	 * quiz ad codes
	 */
	public $ad_codes = array();

	/**
	 * unique identifier
	 */
	public $identifier = 0;

	/**
	 * default options
	 */
	public $options = array();

	/**
	 * Constructor
	 */
	public function __construct( $id ) {

		$this->options 		= get_option( 'wp_quiz_pro_default_settings' );

		$this->id 			= $id;
		$this->settings 	= get_post_meta( $id, 'settings', true );
		$this->questions 	= get_post_meta( $id, 'questions', true );
		$this->results 		= get_post_meta( $id, 'results', true );
		$this->type			= get_post_meta( $id, 'quiz_type', true );
		$this->ad_codes 	= $this->options['ad_code'];
		$this->identifier 	= 'wp_quiz_' . $this->id;
	}

	/**
	 * @return string unique identifier for quiz
	 */
	protected function get_identifier() {
		return $this->identifier;
	}

	/**
	 * Output the HTML
	 *
	 * @return string HTML
	 */
	public function render_public_quiz() {

		$html[] = '<!-- wp quiz -->';
		$html[] = '<div class="wq_quizCtr ' . $this->settings['question_layout'] . ' ' . $this->type . '_quiz" ' . $this->get_data_attrs() . '>';
		$html[] = '   	<div class="wq_quizProgressBarCtr">';
		$html[] = '        ' . $this->get_html_progress_bar();
		$html[] = '   	</div>';
		$html[] = '   	<div class="wq_questionsCtr" >';
		$html[] = '        ' . $this->get_html_questions();
		$html[] = '   	</div>';
		$html[] = '   	<div class="wq_resultsCtr">';
		$html[] = '        ' . $this->get_html_results();
		$html[] = '   	</div>';
		$html[] = '   	<!-- force action -->';
		$html[] = '        ' . $this->get_html_force_action();
		$html[] = '   	<!--// force action-->';
		$html[] = '   	<!-- quiz timer -->';
		$html[] = '        <div class="timerPlaceholder"></div>';
		$html[] = '   	<!--// quiz timer-->';
		$html[] = '   	<!-- embed code -->';
		$html[] = '        ' . $this->get_embed_toggle();
		$html[] = '   	<!--// embed code -->';
		$html[] = '   	<!-- promote link -->';
		$html[] = '        ' . $this->get_html_promote_link();
		$html[] = '   	<!--// promote link-->';
		$html[] = '   	<!-- retake button -->';
		$html[] = '        ' . $this->get_html_retake_button();
		$html[] = '   	<!--// retake button-->';
		$html[] = '</div>';
		$html[] = '<!--// wp quiz-->';

		$wp_quiz = implode( "\n", $html );
		$wp_quiz = apply_filters( 'wp_quiz_output', $wp_quiz, $this->id, $this->settings );

		return $wp_quiz;
	}

	public function get_data_attrs() {

		global $post;
		$id  = $post ? $post->ID : $this->id;
		$url = $post ? get_permalink( $post->ID ) : '';

		$data = '';
		$data .= 'data-current-question="0" ';
		$data .= 'data-questions-answered="0" ';
		$data .= 'data-questions="' . count( $this->questions ) . '" ';
		$data .= 'data-transition_in="' . $this->settings['animation_in'] . '" ';
		$data .= 'data-transition_out="' . $this->settings['animation_out'] . '" ';
		$data .= 'data-correct-answered="0" ';
		$data .= 'data-force-action="' . ( isset( $this->settings['force_action'] ) ? $this->settings['force_action'] : '' ) . '" ';
		$data .= 'data-quiz-pid="' . $this->id . '" ';
		$data .= 'data-share-url="' . $url . '" ';
		$data .= 'data-post-title="' . get_the_title( $id ) . '" ';
		$data .= 'data-retake-quiz="' . $this->settings['restart_questions'] . '" ';
		$data .= 'data-question-layout="' . $this->settings['question_layout'] . '" ';
		$data .= 'data-featured-image="' . wp_get_attachment_url( get_post_thumbnail_id( $id ) ) . '" ';
		$data .= 'data-excerpt="' . get_post_field( 'post_excerpt', $this->id ) . '"';
		$data .= 'data-ajax-url="' . admin_url( 'admin-ajax.php' ) . '"';
		$data .= 'data-auto-scroll="' . $this->settings['auto_scroll'] . '" ';

		$data = apply_filters( 'wp_quiz_data_attrs', $data, $this->id, $this->settings );

		return $data;
	}

	public function get_html_progress_bar() {

		$display = 'single' === $this->settings['question_layout'] ? 'none' : 'block';
		$display = 'swiper' === $this->type ? 'none' : $display;
		$html[] = '<!-- progress bar -->';
		$html[] = '<div class="wq_quizProgressBarCtr" style="display:' . $display . '">';
		$html[] = '<div class="wq_quizProgressBar">';
		$html[] = '<span style="background-color:' . $this->settings['bar_color'] . '" class="wq_quizProgressValue"></span>';
		$html[] = '</div>';
		$html[] = '</div>';
		$html[] = '<!--// progress bar-->';

		$progress_bar = implode( "\n", $html );

		return $progress_bar;
	}

	public function get_html_share() {

		$html[] = '<!-- social share -->';
		$html[] = '<div class="wq_shareCtr">';
		if ( isset( $this->settings['share_buttons'] ) ) {
			$share_buttons = $this->settings['share_buttons'];
			$html[] = '<p style="font-size:14px;">' . esc_html__( 'Share your Results :', 'wp-quiz-pro' ) . '</p>';
			if ( in_array( 'fb', $share_buttons ) ) {
				$html[] = '<button class="wq_shareFB"><i class="sprite sprite-facebook"></i><span>' . esc_html__( 'Facebook', 'wp-quiz-pro' ) . '</span></button>';
			}
			if ( in_array( 'tw', $share_buttons ) ) {
				$html[] = '<button class="wq_shareTwitter"><i class="sprite sprite-twitter"></i><span>' . esc_html__( 'Twitter', 'wp-quiz-pro' ) . '</span></button>';
			}
			if ( in_array( 'g+', $share_buttons ) ) {
				$html[] = '<button class="wq_shareGP"><i class="sprite sprite-google-plus"></i><span>' . esc_html__( 'Google+', 'wp-quiz-pro' ) . '</span></button>';
			}
			if ( in_array( 'vk', $share_buttons ) ) {
				$html[] = '<button class="wq_shareVK"><i class="sprite sprite-vk"></i><span>' . esc_html__( 'VK', 'wp-quiz-pro' ) . '</span></button>';
			}
		}
		$html[] = '</div>';
		$html[] = '<!--// social share-->';

		$social_shares = implode( "\n", $html );
		$social_shares = apply_filters( 'wp_quiz_shares', $social_shares, $this->id, $this->settings );

		return $social_shares;
	}

	public function get_html_force_action() {

		$title = ( isset( $this->options['defaults']['subscribe_box_title'] ) && ! empty( $this->options['defaults']['subscribe_box_title'] ) ) ? $this->options['defaults']['subscribe_box_title'] : __( 'Just tell us who you are to view your results !', 'wp-quiz-pro' );

		$html[] = '<div class="wq_quizForceActionCtr" style="display:none;">';
		$html[] = '<div class="wq_quizEmailCtr" style="display:none;">';
		$html[] = '<form id="wq_infoForm" action="" method="post">';
		$html[] = '<p>' . esc_html( $title ) . '</p>';
		$html[] = '<div><label>' . esc_html__( 'Your first name :', 'wp-quiz-pro' ) . '</label><input type="text" id="wq_inputName"/></div>';
		$html[] = '<div><label>' . esc_html__( 'Your email address :', 'wp-quiz-pro' ) . '</label><input type="email" id="wq_inputEmail"/></div>';
		$html[] = '<p><button type="submit" id="" style="background:' . $this->settings['bar_color'] . '">' . __( 'Show my results &gt;&gt;', 'wp-quiz-pro' ) . '</button></p>';
		$html[] = '</form>';
		$html[] = '</div>';
		$html[] = '<div class="wq_quizForceShareCtr" style="display:none;">';
		$html[] = '<p>' . esc_html__( 'Please share this quiz to view your results . ', 'wp-quiz-pro' ) . '</p>';
		$html[] = '<button class="wq_forceShareFB"><i class="fa fa-facebook icon"></i><span>' . esc_html__( 'Facebook', 'wp-quiz-pro' ) . '</span></button>';
		$html[] = '</div>';
		$html[] = '</div>';

		$force_action = implode( "\n", $html );

		$force_action = apply_filters( 'wp_quiz_capture_email', $force_action, $this->id, $this->settings );

		return $force_action;
	}

	public function get_html_promote_link() {

		$promote_plugin = $this->settings['promote_plugin'];
		$html = array();
		if ( $promote_plugin ) {
			$html[] = '<div style="width:100%;text-align:right;" class="wq_promoteQuizCtr" >';
			$html[] = '<a style="font-size:11px;" href="https://mythemeshop.com/plugins/wp-quiz-pro/" target="_blank">' . esc_html__( 'Powered by WP Quiz Pro', 'wp-quiz-pro' ) . '</a>';
			$html[] = '</div>';
		}

		$promote_link = implode( "\n", $html );
		$promote_link = apply_filters( 'wp_quiz_promote_plugin', $promote_link, $this->id, $this->settings );

		return $promote_link;
	}

	public function get_html_retake_button() {

		$html[] = '<div class="wq_retakeQuizCtr" >';
		$html[] = '<button style="display:none;" class="wq_retakeQuizBtn"><i class="fa fa-undo"></i>&nbsp; ' . esc_html__( 'PLAY AGAIN !', 'wp-quiz-pro' ) . '</button>';
		$html[] = '</div>';

		$retake_button = implode( "\n", $html );

		$retake_button = apply_filters( 'wp_quiz_capture_email', $retake_button, $this->id, $this->settings );

		return $retake_button;
	}

	public function get_embed_toggle() {

		$html = '';
		$embed_toggle = isset( $this->settings['embed_toggle'] ) ? $this->settings['embed_toggle'] : false;

		if ( $embed_toggle ) {
			$html .= '<div style="float:left;" class="wq_embedToggleQuizCtr" >';
			$html .= '<a style="font-size:11px;" href="#">' . __( 'Toggle embed code', 'wp-quiz-pro' ) . '</a>';
			$html .= '</div>';
			$site_url = get_site_url() . '/?wp_quiz_id=' . $this->id;
			$iframe = '<iframe frameborder="0" width="600" height="800" src="' . $site_url . '"></iframe>';
			$html .= '<div class="wq_embedToggleQuiz" style="display:none;"><input type="text" readonly value="' . htmlentities( $iframe ) . '" onClick="this.select();"></div>';
		}

		return apply_filters( 'wp_quiz_embed_toggle', $html, $this->id, $this->settings );
	}

	/**
	 * Include quiz assets
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'wp_quiz-front-js', wp_quiz_pro()->plugin_url() . 'assets/js/main.min.js', array( 'jquery', 'semantic-transition-js', 'semantic-embed-js' ), wp_quiz_pro()->version, true );
		wp_enqueue_script( 'semantic-transition-js', wp_quiz_pro()->plugin_url() . 'assets/js/transition.min.js', array( 'jquery' ), wp_quiz_pro()->version, true );
		wp_enqueue_script( 'semantic-embed-js', wp_quiz_pro()->plugin_url() . 'assets/js/embed.min.js', array( 'jquery' ), wp_quiz_pro()->version, true );

		wp_localize_script( 'wp_quiz-front-js', 'wq_l10n', array(
				'correct'         => esc_html__( 'Correct !', 'wp-quiz-pro' ),
				'wrong'           => esc_html__( 'Wrong !', 'wp-quiz-pro' ),
				'captionTrivia'   => esc_html__( 'You got %%score%% out of %%total%%', 'wp-quiz-pro' ),
				'captionTriviaFB' => esc_html__( 'I got %%score%% out of %%total%%, and you?', 'wp-quiz-pro' ),
				'youVoted'        => esc_html__( 'You voted', 'wp-quiz-pro' ),
				'nonce'           => wp_create_nonce( 'ajax-quiz-content' ),
			)
		);

		// This will be added to the bottom of the page as <head> has already been processed by WordPress sorry.
		wp_enqueue_style( 'semantic-transition-css', wp_quiz_pro()->plugin_url() . 'assets/css/transition.min.css', array(), wp_quiz_pro()->version );
		wp_enqueue_style( 'semantic-embed-css', wp_quiz_pro()->plugin_url() . 'assets/css/embed.min.css', array(), wp_quiz_pro()->version );
		wp_enqueue_style( 'wp_quiz-front-css', wp_quiz_pro()->plugin_url() . 'assets/css/main.css', false, wp_quiz_pro()->version );
		if ( 'traditional' === $this->settings['skin'] ) {
			wp_enqueue_style( 'traditional-skin-css', wp_quiz_pro()->plugin_url() . 'assets/css/traditional-skin.css', array(), wp_quiz_pro()->version );
		} else if ( 'flat' === $this->settings['skin'] ) {
			wp_enqueue_style( 'flat-skin-css', wp_quiz_pro()->plugin_url() . 'assets/css/flat-skin.css', array(), wp_quiz_pro()->version );
		}
		if( is_rtl() ) {
			wp_enqueue_style( 'wp_quiz-rtl-css', wp_quiz_pro()->plugin_url() . 'assets/css/quiz-rtl.css', array(), wp_quiz_pro()->version );
		}

		do_action( 'wp_quiz_register_public_styles' );
	}
}
