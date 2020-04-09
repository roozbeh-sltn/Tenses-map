<?php
/**
 * Override parent 'WP_Quiz_Pro' class with trivia quiz specific markup,
 */
class WP_Quiz_Pro_Trivia_Quiz extends WP_Quiz_Pro {

	/**
	 * Constructor
	 */
	public function __construct( $id  ) {

		parent::__construct( $id );
		add_filter( 'wp_quiz_output', array( $this, 'add_timer_markup' ) );
		add_filter( 'wp_quiz_data_attrs', array( $this, 'add_trivia_data_attrs' ) );
	}

	public function get_html_questions() {

		$questions_html 	= '';

		if ( ! empty( $this->questions ) ) {
			if ( isset( $this->settings['rand_questions'] ) && $this->settings['rand_questions'] ) {
				shuffle( $this->questions );
			}

			$i = 0;
			$show_ads = isset( $this->settings['show_ads'] ) ? $this->settings['show_ads'] : false;
			$repeat_ads = isset( $this->settings['repeat_ads'] ) ? $this->settings['repeat_ads'] : false;
			$ad_nth = isset( $this->settings['ad_nth_display'] ) ? $this->settings['ad_nth_display'] : '0';
			if ( ! empty( $this->settings['ad_codes'] )  ) {
				$ad_codes = explode( ',', $this->settings['ad_codes'] );
				$number_ads = count( $ad_codes );
			} else {
				$ad_codes = $this->ad_codes;
				/*$number_ads = count( $this->ad_codes );*/
			}

			if ( 'single' === $this->settings['question_layout'] ) {
				$ad_display = 'block';
				$display_continue = 'none';
			} else {
				$ad_display = 'none';
				$display_continue = 'block';
			}

			foreach ( $this->questions as $key => $question ) {
				if ( $show_ads && '0' !== $ad_nth && ( 0 === ( $key ) % $ad_nth ) && 0 !== $key ) {

					if ( ! empty( $ad_codes[ $i ] ) && isset( $ad_codes[ $i ] ) ) {

						$questions_html .= '
							<div class="wq_singleQuestionWrapper wq_IsTrivia wq_isAd" style="display:' . $ad_display . ';">
								<p style="font-size:12px;margin-bottom:0;">' . esc_html__( 'Advertisement', 'wp-quiz-pro' ) . '</p>
								' . ( 'block' === $ad_display ? $ad_codes[ $i ] : '<template>' . $ad_codes[ $i ] . '</template>' ) . '
								<div class="wq_continue" style="display:' . $display_continue . ';">
									<button class="wq_btn-continue" style="background-color:' . $this->settings['bar_color'] . '">' . esc_html__( 'Continue &gt;&gt;', 'wp-quiz-pro' ) . '</button>
								</div>
							</div>
						';
						$i++;

						if ( $number_ads == $i && $repeat_ads ) {
							$i  = 0;
						}
					}
				}

				$media_html = '';
				if ( 'image' === $question['mediaType'] ) {
					if ( ! empty( $question['image'] ) ) {
						$media_html = '<div class="wq_questionImage"><img src="' . $question['image'] . '" /><span>' . $question['imageCredit'] . '</span></div>';
					}
				} else if ( 'video' === $question['mediaType'] ) {
					if ( ! empty( $question['video'] ) ) {
						if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $question['video'], $match ) ) {
							if ( ! empty( $match[1] ) ) {
								$media_html = '<div class="ui embed media-' . $key . ' " data-source="youtube" data-id="' . $match[1] . '"  data-placeholder="' . $question['imagePlaceholder'] . ' " data-icon="video play"></div>';
							}
						} else if ( preg_match( '#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[a-z]*/)*([0-9]{6,11})[?]?.*#', $question['video'], $match ) ) {
							if ( ! empty( $match[1] ) ) {
								$media_html = '<div class="ui embed media-' . $key . ' " data-source="vimeo" data-id="' . $match[1] . '"  data-placeholder="' . $question['imagePlaceholder'] . ' " data-icon="video play"></div>';
							}
						} else {
							$media_html = '<div class="ui embed media-' . $key . ' " data-url="' . $question['video'] . ' " data-placeholder="' . $question['imagePlaceholder'] . ' " data-icon="video play"></div>';
						}
						$autoplay = ! empty( $question['imagePlaceholder'] ) ? 'true':'false';
						$media_html .= '<script>jQuery(document).ready(function($) {$(".ui.media-' . $key . ' ").embed({"autoplay":' . $autoplay . ' });});</script>';
					}
				}

				$answers_html = '';
				if ( isset( $question['answers'] ) ) {

					if ( $this->settings['rand_answers'] ) {
						shuffle( $question['answers'] );
					}

					$answers_html    = '<div class="wq_answersWrapper">';
					$answers_has_image = false;

					foreach ( $question['answers'] as $answer ) {
						if ( ! empty( $answer['image'] ) ) {
							$answers_has_image = true;
							$answers_html = '';
							$answers_html = '<div class="wq_answersWrapper">';
							break;
						}

						$answers_html .= '
							<div class="wq_singleAnswerCtr wq_IsTrivia" data-crt="' . $answer['isCorrect'] . '" style="background-color:' . $this->settings['background_color'] . '; color:' . $this->settings['font_color'] . ';">
								<label class="wq_answerTxtCtr">' . $answer['title'] . '</label>
							</div>
						';
					}

					if ( $answers_has_image ) {
						$cols = apply_filters( 'wp_quiz_pro_img_answer_cols', 3 ); // 2 columns if anything else is passed
						if ( 3 === $cols ) {
							$col_class = 'col-md-wq-4';
						} else {
							$col_class = 'col-md-wq-6';
							$cols = 2;
						}

						$j = 0;
						$answers_html .= '<div class="row">';
						foreach ( $question['answers'] as $answer ) {
							$answer_img_html = '';
							$answer_title = '';
							$answer_img_html = '<div class="wq_answerImgCtr"><img src="' . $answer['image'] . '"></div>';
							$answer_title = empty( $answer['title'] ) ? '&nbsp;' : $answer['title'];

							$answers_html .= '
								<div class="' . $col_class . '">
									<div class="wq_singleAnswerCtr wq_IsTrivia wq_hasImage" data-crt="' . $answer['isCorrect'] . '" style="background-color:' . $this->settings['background_color'] . '; color:' . $this->settings['font_color'] . ';">
										' . $answer_img_html . '
										<label class="wq_answerTxtCtr">' . $answer_title . '</label>
									</div>
								</div>
							';
							$j++;
							if ( 0 === $j % $cols ) {
								$answers_html .= '</div><div class="row">';
							}
						}
						$answers_html .= '</div>';
					}
					$answers_html .= '</div>';
				}

				$display = 0 === $key ? 'block' : 'none';
				if ( 'single' === $this->settings['question_layout'] ) {
					$display = 'block';
				}
				$questions_html .= '
					<div class="wq_singleQuestionWrapper wq_IsTrivia" style="display:' . $display . ';">
						<div class="wq_singleQuestionCtr">
							<div class="wq_questionTextWrapper quiz-pro-clearfix">
								<div class="wq_questionTextCtr" style="background-color:' . $this->settings['background_color'] . '; color:' . $this->settings['font_color'] . ';">
									<h4>' . $question['title'] . '</h4>
								</div>
							</div>
							<div class="wq_questionMediaCtr" >
								' . $media_html . '
							</div>
							<div class="wq_questionAnswersCtr">
								' . $answers_html . '
							</div>
							<div class="wq_triviaQuestionExplanation">
								<div class="wq_ExplanationHead"></div>
								<p class="wq_QuestionExplanationText">' . $question['desc'] . '</p>
							</div>
						</div>
						<div class="wq_continue" style="display:none;">
							<button class="wq_btn-continue" style="background-color:' . $this->settings['bar_color'] . '">' . esc_html__( 'Continue &gt;&gt;', 'wp-quiz-pro' ) . '</button>
						</div>
					</div>
				';
			}
		}
		return $questions_html;
	}

	public function get_html_results() {

		$results_html = '';
		$share_html = $this->get_html_share();
		if ( ! empty( $this->results ) ) {
			foreach ( $this->results as $index => $result ) {
				$result_img_html = '';
				if ( ! empty( $result['image'] ) ) {
					$result_img_html = '<p><img class="wq_resultImg" src="' . $result['image'] . '"/></p>';
				}
				$results_html .= '
					<div style="display:none;" class="wq_singleResultWrapper wq_IsTrivia" data-id="' . $index . '" data-min="' . $result['min'] . '" data-max="' . $result['max'] . '">
						<span class="wq_quizTitle">' . get_the_title( $this->id ) . '</span>
						<div class="wq_resultScoreCtr"></div>
						<div class="wq_resultTitle"><strong>' . $result['title'] . '</strong></div>
						' . $result_img_html . '
						<div class="wq_resultDesc">' . wp_kses_post( $result['desc'] ) . '</div>
						' . $share_html . '
					</div>
				';
			}
		}

		return $results_html;
	}

	public function add_timer_markup( $html ) {

		if ( isset( $this->settings['countdown_timer'] ) && $this->settings['countdown_timer'] > 0  && 'multiple' === $this->settings['question_layout'] ) {
			$html_timer = '
				<div class="wq_triviaQuizTimerInfoCtr">
					<p>
						' . esc_html__( 'This is a timed quiz. You will be given ', 'wp-quiz-pro' ) . $this->settings['countdown_timer'] . esc_html__( ' seconds per question. Are you ready?', 'wp-quiz-pro' ) . '
					</p>
					<button type="button" class="wq_beginQuizCtr" style="background-color:' . $this->settings['bar_color'] . '"> ' . esc_html__( 'Begin!', 'wp-quiz-pro' ) . '</button>
				</div>
				<div class="wq_triviaQuizTimerCtr" style="background-color:' . $this->settings['bar_color'] . '">' . $this->settings['countdown_timer'] . '</div>
			';
			return str_replace( array( '<div class="timerPlaceholder"></div>', 'class="wq_quizProgressBarCtr"', 'class="wq_questionsCtr"' ), array( $html_timer, 'class="wq_quizProgressBarCtr" style="display:none;"', 'class="wq_questionsCtr" style="display:none;"' ), $html );

		} else {
			return $html;
		}
	}

	public function add_trivia_data_attrs( $data ) {

		$end_answers = isset( $this->settings['end_answers'] ) && 'single' === $this->settings['question_layout'] ? 'true' : 'false';
		$data .= 'data-end-answers="' . $end_answers . '" ';
		$data .= 'data-quiz-timer="' . ( isset( $this->settings['countdown_timer'] ) ? $this->settings['countdown_timer'] : '0' ) . '" ';

		return $data;
	}
}
