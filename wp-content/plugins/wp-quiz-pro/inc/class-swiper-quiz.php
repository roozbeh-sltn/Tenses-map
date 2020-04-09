<?php
/**
 * Override parent 'WP_Quiz_Pro' class with swiper quiz specific markup,
 *
 */
class WP_Quiz_Pro_Swiper_Quiz extends WP_Quiz_Pro {

	public function get_html_questions() {

		$questions_html 	= '';

		if ( ! empty( $this->questions ) ) {
			if ( $this->settings['rand_questions'] ) {
				shuffle( $this->questions );
			}

			$height = 'auto';
			$width = '100%;';
			if ( isset( $this->settings['size'] ) && 'custom' === $this->settings['size'] ) {
				if ( isset( $this->settings['custom_height'] ) && ! empty( $this->settings['custom_height'] ) ) {
					$height = $this->settings['custom_height'] + 31 . 'px;';
				}
				if ( isset( $this->settings['custom_width'] ) && ! empty( $this->settings['custom_width'] ) ) {
					$width = $this->settings['custom_width'] + 12 . 'px;';
				}
				$questions_html .= '<style>@media screen and (max-width:' . $this->settings['custom_width'] . 'px) {.wq_QuestionWrapperSwiper > div:first-child{width:100%!important;height:auto!important}}</style>';
			}

			$style = 'width:' . $width . 'height:' . $height;
			$questions_html .= $this->preview_markup();
			$questions_html .= '<div class="wq_QuestionWrapperSwiper" style="display:none;"><div style="margin:0 auto;' . $style . '"><div class="wq_IsSwiper"><ul>';

			foreach ( $this->questions as $key => $question ) {
				$index_count = $key + 1 . '/' . count( $this->questions );
				$questions_html .= '
					<li class="in-deck" data-slideid="' . $question['uid'] . '">
						<i class="sprite sprite-check"></i>
						<i class="sprite sprite-times"></i>
						<div  style="height:100%;">
							<div class="wq_questionImage"><img class="img" src="' . $question['image'] . '"/><span>' . $question['imageCredit'] . '</span></div>
							<div class="slide_info"><span class="slide_title" style="color:' . $this->settings['font_color'] . '">' . $question['title'] . '</span><span class="slide_index">' . $index_count . '</span></div>
						</div>
					</li>';
			}
			$questions_html .= '</ul></div></div>';
		}
		$questions_html .= '<div class="actions"><a href="#" class="dislike"><i class="sprite sprite-thumbs-down"></i></a>  <a href="#" class="like"><i class="sprite sprite-thumbs-up"></i></a></div></div>';
		return $questions_html;
	}

	public function preview_markup() {

		$html = '
			<div class="wq_swiperQuizPreviewInfoCtr">
				<p>
					' . esc_html__( 'This is a swiper quiz, swipe right for yes, swipe left for no. ', 'wp-quiz-pro' ) . '
				</p>
				<button type="button" class="wq_beginQuizSwiperCtr" style="background-color:' . $this->settings['bar_color'] . '"> ' . esc_html__( 'Begin!', 'wp-quiz-pro' ) . '</button>
			</div>
		';
		return $html;
	}

	public function sort_by_votes( $a, $b ) {
		return $b['votesUp'] - $a['votesUp'];
	}

	public function get_html_results() {

		$results_html = '';
		$str_to_remove = esc_html__( 'Share your Results :', 'wp-quiz-pro' );
		$share_html = str_replace( $str_to_remove, '', $this->get_html_share() );

		$results_html .= '<div class="wq_singleResultWrapper wq_IsSwiperResult" style="display:none;"><div><h3>' . esc_html__( 'Results', 'wp-quiz-pro' ) . '</h3><div class="resultList" >';
		if ( ! empty( $this->questions ) ) {

			usort( $this->questions, array( $this, 'sort_by_votes' ) );

			foreach ( $this->questions as $key => $slide ) {
				$position = $key + 1;
				$results_html .= '
					<div id="result-' . $slide['uid'] . '" class="resultItem" data-uid="' . $slide['uid'] . '" data-result="">
						<div class="resultImageWrapper">
							<img src="' . $slide['image'] . '" />
							<span class="indexWrapper">' . $position . '</span>
						</div>
						<div class="resultContent">
							<span>' . $slide['title'] . ' </span>
							<div class="resultUpDownVote">
								<span class="resultUpVote">
									<i class="sprite sprite-check"></i><span id="upVote">' . $this->format_number( $slide['votesUp'] ) . '</span>
								</span>
								<span class="resultDownVote">
									<i class="sprite sprite-times"></i><span id="downVote">' . $this->format_number( $slide['votesDown'] ) . '</span>
								</span>
							</div>
						</div>
					</div>
				';
			}
		}
		$results_html .= '</div><div class="wq_retakeSwiperWrapper"><button style="display:none;" class="wq_retakeSwiperBtn"><i class="fa fa-undo"></i>&nbsp;' . esc_html__( 'Play Again!', 'wp-quiz-pro' ) . '</button></div></div>' . $share_html . '</div>';

		return $results_html;
	}

	public function format_number( $number ) {
		return $number >= 1000 ? round( $number / 1000, 1 ) . 'k' : $number;
	}

	/**
	 * Include quiz assets
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'tinder-js', wp_quiz_pro()->plugin_url() . 'assets/js/jquery.jTinder.min.js', array( 'jquery', 'hammer-js', 'dynamic-js' ), wp_quiz_pro()->version, true );
		wp_enqueue_script( 'hammer-js', wp_quiz_pro()->plugin_url() . 'assets/js/hammer.min.js', array( 'jquery' ), wp_quiz_pro()->version, true );
		wp_enqueue_script( 'dynamic-js', wp_quiz_pro()->plugin_url() . 'assets/js/dynamics.min.js', array( 'jquery' ), wp_quiz_pro()->version, true );

		parent::enqueue_scripts();
	}
}
