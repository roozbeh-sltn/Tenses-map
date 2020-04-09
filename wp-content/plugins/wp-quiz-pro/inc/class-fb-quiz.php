<?php
/**
 * Override parent 'WP_Quiz_Pro' class with facebook quiz specific markup,
 */
class WP_Quiz_Pro_Fb_Quiz extends WP_Quiz_Pro {

	/**
	 * Constructor
	 */
	public function __construct( $id  ) {

		parent::__construct( $id );
		add_filter( 'wp_quiz_data_attrs', array( $this, 'add_fb_data_attrs' ) );
	}

	public function get_html_questions() {

		$questions_html = '';

		if ( ! empty( $this->questions ) ) {
			foreach ( $this->questions as $key => $question ) {

				$desc = ! empty( $question['desc'] ) ? '<p class="desc">' . $question['desc'] . '</p>' : '';
				$questions_html .= '
					<div class="wq_singleQuestionWrapper wq_IsFb" style="">
						<div class="wq_loader-container" style="display:none;">
							<div class="wq_loader_text">
								<img src="' . wp_quiz_pro()->plugin_url() . 'assets/image/image_spinner.gif" />
								<h3 id="wq_text_loader">' . esc_html__( 'Analyzing profile ...', 'wp-quiz-pro' ) . '</h3>
							</div>
						</div>
						<div class="wq_questionTextDescCtr" style="color:' . $this->settings['font_color'] . '">
							<h4>' . $question['title'] . '</h4>
							' . $desc . '
						</div>
						<div class="wq_questionMediaCtr" >
							<div class="wq_questionImage"><img src="' . $question['image'] . '" /><span>' . $question['imageCredit'] . '</span></div>
						</div>
						<div>

						</div>
						<div class="wq_questionLogin">
							<p>' . esc_html__( 'Please login with Facebook to see your result', 'wp-quiz-pro' ) . '</p>
							<button class="wq_loginFB"><i class="sprite sprite-facebook"></i><span>' . esc_html__( 'Login with Facebook', 'wp-quiz-pro' ) . '</span></button>
						</div>
					</div>';
			}
		}

		return $questions_html;
	}

	public function get_html_results() {

		$results_html = '';
		$share_html = $this->get_html_share();
		if ( ! empty( $this->results ) ) {
			$results_html .= '
				<div style="display:none;" class="wq_singleResultWrapper wq_IsFb" data-id="">
					<p><img class="wq_resultImg" src=""/></p>
					<div class="wq_resultDesc"></div>
					' . $share_html . '
				</div>
			';
		}

		return $results_html;
	}

	public function add_fb_data_attrs( $data ) {

		$data .= 'data-quiz-profile="' . $this->settings['profile'] . '" ';
		return $data;
	}
}
