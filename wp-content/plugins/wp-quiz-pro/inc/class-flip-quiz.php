<?php
/**
 * Override parent 'WP_Quiz_Pro' class with flip quiz specific markup,
 */
class WP_Quiz_Pro_Flip_Quiz extends WP_Quiz_Pro {

	public function get_html_questions() {

		$questions_html 	= '';

		if ( ! empty( $this->questions ) ) {

			if ( $this->settings['rand_questions'] ) {
				shuffle( $this->questions );
			}

			$custom_class = '';
			$image_height = '';
			$height = 'auto;';
			$width = '100%;';
			$css_height = '';

			if ( isset( $this->settings['size'] ) && 'custom' === $this->settings['size'] ) {
				if ( isset( $this->settings['custom_height'] ) && ! empty( $this->settings['custom_height'] ) ) {
					$height = $this->settings['custom_height'] . 'px;';
					$front_height = $this->settings['custom_height'] - 52;
					$custom_class = 'custom';
					$image_height = 'height: 100% !important;';
					$css_height   = '.wq_IsFlip .front.custom, .wq_IsFlip .back.custom{height: ' . $front_height . 'px !important}';
				}

				if ( isset( $this->settings['custom_width'] ) && ! empty( $this->settings['custom_width'] ) ) {
					$width = $this->settings['custom_width'] . 'px;';
				}

				$questions_html .= '<style>@media screen and (max-width:' . $this->settings['custom_width'] . 'px) {.wq_singleQuestionWrapper{width:100%!important;height:auto!important}}' . $css_height . '</style>';
			}
			$style = 'width:' . $width . 'height:' . $height;

			$i = 0;
			$show_ads = $this->settings['show_ads'];
			$repeat_ads = $this->settings['repeat_ads'];
			$ad_nth = $this->settings['ad_nth_display'];
			if ( ! empty( $this->settings['ad_codes'] )  ) {
				$ad_codes = explode( ',', $this->settings['ad_codes'] );
				$number_ads = count( $ad_codes );
			} else {
				$ad_codes = $this->ad_codes;
				$number_ads = count( $this->ad_codes );
			}

			foreach ( $this->questions as $key => $question ) {

				if ( $show_ads && '0' !== $ad_nth && ( 0 === ( $key ) % $ad_nth ) && 0 !== $key ) {

					if ( ! empty( $ad_codes[ $i ] ) && isset( $ad_codes[ $i ] ) ) {

						$questions_html .= '
							<div class="wq_singleQuestionWrapper wq_IsFlipAd wq_isAd">
								<p style="font-size:12px;margin-bottom:0;"> ' . esc_html__( 'Advertisement', 'wp-quiz-pro' ) . '</p>
								' . $ad_codes[ $i ] . '
							</div>
						';
						$i++;

						if ( $number_ads == $i && $repeat_ads ) {
							$i  = 0;
						}
					}
				}

				$position = $key + 1;
				$desc = ! empty( $question['desc'] ) ? '<div class="desc"><div>' . $question['desc'] . '</div></div>' : '';

				$questions_html .= '
					<div class="wq_singleQuestionWrapper wq_IsFlip" style="margin-left:auto;margin-right:auto;' . $style . '">
						<div class="item_top">
							<div class="title_container">
								<div class="wq_questionTextCtr" style="color:' . $this->settings['font_color'] . '">
									<h4>' . $question['title'] . '</h4>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="front ' . $custom_class . '">
								<img style="' . $image_height . '" src="' . $question['image'] . '" /><span class="credits">' . $question['imageCredit'] . '</span>
								<span class="desc">' . esc_html__( 'Click to Flip', 'wp-quiz-pro' ) . '</span>
							</div>
							<div class="back ' . $custom_class . '">
								<img style="' . $image_height . '" src="' . $question['backImage'] . '" /><span class="credits">' . $question['backImageCredit'] . '</span>
								' . $desc . '
							</div>
						</div>
					</div>';
			}
		}

		return $questions_html;
	}

	public function get_html_results() {

		if ( isset( $this->settings['share_buttons'] ) && ! empty( $this->settings['share_buttons'] ) ) {
			$share_html = $this->get_html_share();
			$results_html = '<div class="wq_singleResultWrapper" style="display:block;">' . $share_html . '</div>';
			$str_to_remove = esc_html__( 'Share your Results :', 'wp-quiz-pro' );
			return str_replace( $str_to_remove, '', $results_html );
		}

	}

	/**
	 * Include quiz assets
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'flip-js', wp_quiz_pro()->plugin_url() . 'assets/js/jquery.flip.min.js', array( 'jquery' ), wp_quiz_pro()->version, true );

		parent::enqueue_scripts();
	}
}
