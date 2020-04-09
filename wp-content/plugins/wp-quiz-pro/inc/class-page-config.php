<?php
/**
 * Class For WP_Quiz_Pro Configuration page
 */
class WP_Quiz_Pro_Page_Config {

	public static function admin_print_styles() {

		wp_enqueue_style( 'semantic-checkbox-css', wp_quiz_pro()->plugin_url() . 'assets/css/checkbox.min.css', array(), wp_quiz_pro()->version );
		wp_enqueue_style( 'chosen-css', wp_quiz_pro()->plugin_url() . 'assets/css/chosen.min.css', array(), wp_quiz_pro()->version );
		?>
			<style type="text/css" media="screen">
				table#quiz_type_settings input[type=checkbox], table#global_settings input[type=checkbox] { float: right; }
				#config-page input[type=text]{ width: 100%; }
				#config-page label{ display: block; font-size: 14px; color: #666; }
				table#quiz_type_settings tr td , table#global_settings tr td { padding: 10px 10px 10px 0; color: #666; font-size: 14px; }
                body.rtl table#quiz_type_settings tr td , body.rtl table#global_settings tr td { padding: 10px 0 10px 10px; }
				table#quiz_type_settings tr td .ui, table#global_settings tr td .ui { float: right; }
                body.rtl table#quiz_type_settings tr td .ui, body.rtl table#global_settings tr td .ui { float: left; }
				table#quiz_type_settings tr td #select, table#quiz_type_settings tr td input, table#global_settings tr td input { border-radius: 2px; width: 100%; }
				table#quiz_type_settings, table#global_settings { border: none; }
				#ad_code_setting .add-new-h2 { top: 0; position: relative; margin-bottom: 5px; float: left; clear: both; }
                body.rtl #ad_code_setting .add-new-h2 { float: right; }
				.ad_row{ margin-bottom: 25px; }
				.ad_row textarea{ width: 100%; }
				.ad_action a{ text-decoration: none; color: #a00; }
				.ad_action a:hover, .ad_action a:active{ color: red; text-decoration: none; }
				.chosen-container{ float: right; width: 100% !important; }
				.ui.toggle.checkbox .box, .ui.toggle.checkbox label { padding-left: 4.15em!important }
                body.rtl .ui.toggle.checkbox .box, body.rtl  .ui.toggle.checkbox label { padding-right: 4.15em!important; padding-left: 0px !important; }
				.mailchimp_row, .getresponse_row, .aweber_row { display: none; }
			</style>
		<?php
	}

	public static function save_post_form() {

		// Set default options if button clicked
		if ( isset( $_POST['submit'] ) ) {

			$settings_key = array( 'rand_questions', 'rand_answers', 'restart_questions', 'promote_plugin', 'embed_toggle', 'show_ads', 'auto_scroll', 'share_meta', 'repeat_ads' );

			foreach ( $settings_key as  $key ) {
				if ( isset( $_POST['defaults'][ $key ] )  && '1' == $_POST['defaults'][ $key ] ) {
					$_POST['defaults'][ $key ] = 1;
				} else {
					$_POST['defaults'][ $key ] = 0;
				}
			}

			if ( isset( $_POST['players_tracking'] ) && '1' == $_POST['players_tracking'] ) {
				$_POST['players_tracking'] = 1;
			} else {
				$_POST['players_tracking'] = 0;
			}

			$analytics_settings = array(
				'profile_name' => $_POST['profile_name'],
				'tracking_id' => $_POST['tracking_id'],
			);

			$mailchimp_settings = array(
				'api_key' => $_POST['mailchimp_key'],
				'list_id' => $_POST['mailchimp_list_id'],
			);

			$getresponse_settings = array(
				'api_key' => $_POST['getresponse_key'],
				'campaign_name' => $_POST['getresponse_name'],
			);

			$aweber_settings = array(
				'consumer_key'    => $_POST['aweber_consumer_key'],
				'consumer_secret' => $_POST['aweber_consumer_secret'],
				'access_key'      => $_POST['aweber_access_key'],
				'access_secret'   => $_POST['aweber_access_secret'],
				'account_id'      => $_POST['aweber_account_id'],
				'listid'          => $_POST['aweber_listid'],
			);

			$settings = array(
				'analytics' => $analytics_settings,
				'mail_service' => $_POST['mail_service'],
				'mailchimp' => $mailchimp_settings,
				'getresponse' => $getresponse_settings,
				'aweber' => $aweber_settings,
				'defaults' => $_POST['defaults'],
				'ad_code' => isset( $_POST['ad_code'] ) ? stripslashes_deep( $_POST['ad_code'] ) : '',
				'players_tracking' => $_POST['players_tracking'],
			);

			update_option( 'wp_quiz_pro_default_settings', $settings );
		}
	}

	public static function display_messages() {

		$message = false;
		if ( isset( $_REQUEST['message'] ) && ( $msg = (int) $_REQUEST['message'] ) ) {
			if ( 3 === $msg ) {
				$message = esc_html__( 'Settings saved', 'wp-quiz-pro' );
			}
		}
		$class = isset( $_REQUEST['error'] ) ? 'error' : 'updated';

		if ( $message ) :
		?>
			<div id="message" class="<?php echo $class; ?> notice is-dismissible"><p><?php echo $message; ?></p></div>
		<?php
		endif;
	}

	public static function load() {

		//needed javascripts to allow drag/drop, expand/collapse and hide/show of boxes
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );

		wp_enqueue_script( 'semantic-checkbox-js', wp_quiz_pro()->plugin_url() . 'assets/js/checkbox.min.js', array( 'jquery' ), wp_quiz_pro()->version, false );
		wp_enqueue_script( 'chosen-js', wp_quiz_pro()->plugin_url() . 'assets/js/chosen.jquery.min.js', array( 'jquery' ), wp_quiz_pro()->version, false );

		$screen = get_current_screen();
		add_meta_box( 'google-analytics-content', esc_html__( 'Google Analytics', 'wp-quiz-pro' ), array( __CLASS__, 'google_analytics_content' ), $screen->id, 'normal', 'core' );
		add_meta_box( 'mail-service', esc_html__( 'Mail Service', 'wp-quiz-pro' ), array( __CLASS__, 'mail_service_content' ), $screen->id, 'normal', 'core' );
		add_meta_box( 'default-quiz-settings', esc_html__( 'Default Quiz Settings', 'wp-quiz-pro' ), array( __CLASS__, 'default_settings_content' ), $screen->id, 'normal', 'core' );
		add_meta_box( 'ad-code', esc_html__( 'Ad Code', 'wp-quiz-pro' ), array( __CLASS__, 'ad_code_content' ), $screen->id, 'normal', 'core' );
		add_meta_box( 'global-settings', esc_html__( 'Global', 'wp-quiz-pro' ), array( __CLASS__, 'global_settings_content' ), $screen->id, 'normal', 'core' );
	}

	public static function page() {

		$screen = get_current_screen();
		$columns = absint( $screen->get_columns() );
		$columns_css = '';
		if ( $columns ) {
			$columns_css = " columns-$columns";
		} ?>
			<div class="wrap" id="config-page">
				<h2><?php esc_html_e( 'General Settings', 'wp-quiz-pro' ); ?></h2>
				<?php self::display_messages(); ?>
				<form action="<?php echo admin_url( 'admin-post.php?action=wp_quiz' ); ?>" method="post">
					<?php wp_nonce_field( 'wp_quiz_config_page' ); ?>
					<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
					<input type="hidden" name="page" value="wp_quiz_config" />
					<div id="poststuff">
						<div id="post-body" class="metabox-holder <?php echo $columns_css ?>">
							<div id="postbox-container-2" class="postbox-container">
							<?php
								$settings = get_option( 'wp_quiz_pro_default_settings' );
								do_meta_boxes( $screen->id, 'normal', $settings );
							?>
							</div>
							<p class="submit">
								<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-quiz-pro' ); ?>">&nbsp;
							</p>
						</div>
					</div>
				</form>
			</div>
			<script type="text/javascript">
				//<![CDATA[
					jQuery(document).ready( function($) {

						// close postboxes that should be closed
						$('.if-js-closed').removeClass('if-js-closed').addClass('closed');

						// postboxes setup
						postboxes.add_postbox_toggles('<?php echo $screen->id ?>');

						$('#new_add_code').on( 'click', function(e) {
							e.preventDefault();
							$html = '<p><div class="ad_row"><textarea rows="4" id="input_ad_code" name="ad_code[]" ></textarea><span class="ad_action"><a href="#" onclick="remove_add(this, event);">Delete</a></span</div></p>';
							$('#ad_code_container').append($html);
						});
						remove_add = function(self, e) {
							e.preventDefault();
							$(self).parent().parent().remove();
						}
						mail_service_change = function() {
							$('.mailchimp_row, .getresponse_row, .aweber_row').hide();
							var val = $('select[name=mail_service]').val();
							if (val == '1') {
								$('.mailchimp_row').show();
							} else if (val == '2') {
								$('.getresponse_row').show();
							}
							else if (val == '3') {
								$('.aweber_row').show();
							}
						}
						mail_service_change();
						$('.ui.toggle').checkbox();
						$('#share_buttons').chosen();

						// Aweber Autorize Code
						$( document ).on( 'click', 'button.aweber_authorization', function() {

							var $this= $( this ),
								parent = $this.parent(),
								code = parent.find( 'textarea' ).val().trim();

							if( '' === code ) {
								alert( 'No authorization code found.' );
								return;
							}

							$.ajax({
								url: ajaxurl,
								type: 'POST',
								data: {
									action: 'connect_aweber',
									aweber_code: code
								}

							}).done(function(response) {

								if ( response && ! response.success && response.error ) {
									alert( response.error );
									return;
								}

								var details = parent.parent();
								for( key in response.data ) {
									details.find( '[name$="aweber_' + key + '"]' ).val( response.data[ key ] );
								}

								parent.hide();
								parent.next().show();
							});
						});

						// Disconnect Aweber
						$( document ).on( 'click', 'a.aweber_disconnect', function() {
							var $this= $( this ),
								parent = $this.closest( '.alert-hint' );

							parent.hide();
							parent.prev().show();

							parent.parent().find( 'input[type="hidden"]' ).val( '' );
						});
					});
				//]]>
			</script>
		<?php
	}

	/**
	 * Html analytics content
	 */
	public static function google_analytics_content( $settings ) {
		?>
			<table id="quiz_type_settings" class="analytics_content" width="100%" frame="border">
				<tr>
					<td><?php esc_html_e( 'Profile Name', 'wp-quiz-pro' ); ?></td>
					<td>
						<input class="ui" name="profile_name" type="text" value="<?php echo $settings['analytics']['profile_name'] ?>" >
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Tracking ID', 'wp-quiz-pro' ); ?></td>
					<td>
						<input class="ui" name="tracking_id" type="text" value="<?php echo $settings['analytics']['tracking_id'] ?>" >
					</td>
				</tr>
			</table>
		<?php
	}

	public static function mail_service_content( $settings ) {
		?>
			<p>
				<select class="ui" id="select" name="mail_service" onchange="mail_service_change()">
					<option value="0" <?php selected( $settings['mail_service'], '0', true ) ?>><?php esc_html_e( 'Select Mail service', 'wp-quiz-pro' ); ?></option>
					<option value="1" <?php selected( $settings['mail_service'], '1', true ) ?>><?php esc_html_e( 'MailChimp', 'wp-quiz-pro' ); ?></option>
					<option value="2" <?php selected( $settings['mail_service'], '2', true ) ?>><?php esc_html_e( 'GetResponse', 'wp-quiz-pro' ); ?></option>
					<option value="3" <?php selected( $settings['mail_service'], '3', true ) ?>><?php esc_html_e( 'Aweber', 'wp-quiz-pro' ); ?></option>
				</select>
			</p>
			<table id="quiz_type_settings"  class="mail_service" width="100%" frame="border">
				<tr class="mailchimp_row">
					<td><?php esc_html_e( 'API key', 'wp-quiz-pro' ); ?></td>
					<td>
						<input class="ui" name="mailchimp_key" type="text" value="<?php echo $settings['mailchimp']['api_key'] ?>" >
					</td>
				</tr>
				<tr class="mailchimp_row">
					<td><?php esc_html_e( 'List ID', 'wp-quiz-pro' ); ?></td>
					<td>
						<input class="ui" name="mailchimp_list_id" type="text" value="<?php echo $settings['mailchimp']['list_id'] ?>" >
					</td>
				</tr>
				<tr class="getresponse_row">
					<td><?php esc_html_e( 'API key', 'wp-quiz-pro' ); ?></td>
					<td>
						<input class="ui" name="getresponse_key" type="text" value="<?php echo $settings['getresponse']['api_key'] ?>" >
					</td>
				</tr>
				<tr class="getresponse_row">
					<td><?php esc_html_e( 'Campaign Name', 'wp-quiz-pro' ); ?></td>
					<td>
						<input class="ui" name="getresponse_name" type="text" value="<?php echo $settings['getresponse']['campaign_name'] ?>" >
					</td>
				</tr>
				<tr class="aweber_row">
					<td><?php esc_html_e( 'Aweber Details', 'wp-quiz-pro' ); ?></td>
					<td>
						<input type="hidden" name="aweber_account_id" value="<?php echo isset( $settings['aweber']['account_id'] ) ? $settings['aweber']['account_id'] : '' ?>" >
						<input type="hidden" name="aweber_access_key" value="<?php echo isset( $settings['aweber']['access_key'] ) ? $settings['aweber']['access_key'] : '' ?>" >
						<input type="hidden" name="aweber_access_secret" value="<?php echo isset( $settings['aweber']['access_secret'] ) ? $settings['aweber']['access_secret'] : '' ?>" >
						<input type="hidden" name="aweber_consumer_key" value="<?php echo isset( $settings['aweber']['consumer_key'] ) ? $settings['aweber']['consumer_key'] : '' ?>" >
						<input type="hidden" name="aweber_consumer_secret" value="<?php echo isset( $settings['aweber']['consumer_secret'] ) ? $settings['aweber']['consumer_secret'] : '' ?>" >

						<div <?php echo ! empty( $settings['aweber']['access_key'] ) ? ' hidden' : '' ?>>
							<strong><?php esc_html_e( 'To connect your Aweber account:', 'wp-quiz-pro' ) ?></strong>
							<br />
							<ul>
								<li><?php printf( wp_kses_post( __( '<span>1.</span> <a href="%s" target="_blank">Click here</a> <span>to open the authorization page and log in.</span>', 'wp-quiz-pro' ) ), 'https://auth.aweber.com/1.0/oauth/authorize_app/1afc783e' ) ?></li>
								<li><?php echo wp_kses_post( __( '<span>2.</span> Copy and paste the authorization code in the field below.', 'wp-quiz-pro' ) ) ?></li>
							</ul>

							<textarea rows="4" cols="80"></textarea>
							<br />
							<button type="button" class="button-primary aweber_authorization">Authorize</button>
						</div>
						<div class="alert alert-hint mb30 <?php echo empty( $settings['aweber']['access_key'] ) ? ' hidden' : '' ?>">
							<p>
								<strong>List ID</strong><br>
								<input class="ui" name="aweber_listid" type="text" value="<?php echo isset( $settings['aweber']['listid'] ) ? $settings['aweber']['listid'] : '' ?>" >
							</p>
							<p>
								<strong><?php esc_html_e( 'Your Aweber Account is connected.', 'wp-quiz-pro' ) ?></strong>
								<?php echo wp_kses_post( __( '<a href="#" class="aweber_disconnect">Click here</a> <span>to disconnect.</span>', 'wp-quiz-pro' ) ) ?>
							</p>
						</div>
					</td>
				</tr>
			</table>
		<?php
	}

	/**
	 * Html default settings content
	 */
	public static function default_settings_content( $settings ) {
		$settings = $settings['defaults'];
		?>
			<table id="quiz_type_settings" width="100%" frame="border">
				<tr>
					<td><?php esc_html_e( 'Randomize Questions', 'wp-quiz-pro' ); ?></td>
					<td>
						<div class="ui toggle checkbox">
							<input name="defaults[rand_questions]" type="checkbox" value="1" <?php checked( $settings['rand_questions'], true, true ) ?>>
						</div>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Randomize Answers', 'wp-quiz-pro' ); ?></td>
					<td>
						<div class="ui toggle checkbox">
							<input name="defaults[rand_answers]" type="checkbox" value="1" <?php checked( $settings['rand_answers'], true, true ) ?>>
						</div>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Restart Questions', 'wp-quiz-pro' ); ?></td>
					<td>
						<div class="ui toggle checkbox">
							<input name="defaults[restart_questions]" type="checkbox" value="1" <?php checked( $settings['restart_questions'], true, true ) ?>>
						</div>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Promote the plugin', 'wp-quiz-pro' ); ?></td>
					<td>
						<div class="ui toggle checkbox">
							<input name="defaults[promote_plugin]" type="checkbox" value="1" <?php checked( $settings['promote_plugin'], true, true ) ?>>
						</div>
					</td>
				</tr>
				<?php
					$desc = empty( $settings['mts_username'] ) ?
						sprintf(
							'<a href="https://mythemeshop.com/#signup" target="_blank">%1$s</a>%2$s',
							esc_html__( 'Signup', 'wp-quiz-pro' ),
							esc_html__( ' and get your referral ID (username) if you don\'t have it already!', 'wp-quiz-pro' )
						) :
						sprintf(
							'%1$s<a href="https://mythemeshop.com/go/aff/member/stats" target="_blank">%2$s</a>',
							esc_html__( 'Check your affiliate earning by following ', 'wp-quiz-pro' ),
							esc_html__( 'this link', 'wp-quiz-pro' )
						);
				?>
				<tr>
					<td><?php esc_html_e( 'MyThemeShop username', 'wp-quiz-pro' ); ?><br /><small><?php echo $desc; ?></small></td>
					<td>
						<input class="ui" name="defaults[mts_username]" type="text" value="<?php echo esc_attr( $settings['mts_username'] ); ?>" >
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Show embed code toggle', 'wp-quiz-pro' ); ?></td>
					<td>
						<div class="ui toggle checkbox">
							<input name="defaults[embed_toggle]" type="checkbox" value="1" <?php checked( $settings['embed_toggle'], true, true ) ?>>
						</div>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Share buttons', 'wp-quiz-pro' ); ?></td>
					<td>
						<?php $settings['share_buttons'] = isset( $settings['share_buttons'] ) ? $settings['share_buttons'] : array(); ?>
						<select name="defaults[share_buttons][]" id="share_buttons" data-placeholder="None" multiple>
							<option value="fb" <?php echo in_array( 'fb', $settings['share_buttons'] ) ? 'selected' : '' ?>><?php esc_html_e( 'Facebook', 'wp-quiz-pro' ); ?></option>
							<option value="tw" <?php echo in_array( 'tw', $settings['share_buttons'] ) ? 'selected' : '' ?>><?php esc_html_e( 'Twitter', 'wp-quiz-pro' ); ?></option>
							<option value="g+" <?php echo in_array( 'g+', $settings['share_buttons'] ) ? 'selected' : '' ?>><?php esc_html_e( 'Google +', 'wp-quiz-pro' ); ?></option>
							<option value="vk" <?php echo in_array( 'vk', $settings['share_buttons'] ) ? 'selected' : '' ?>><?php esc_html_e( 'VK', 'wp-quiz-pro' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Enable or disable Open Graph and Twitter Cards meta tags in single quiz head tag.', 'wp-quiz-pro' ); ?></small></td>
					<td>
						<div class="ui toggle checkbox">
							<input name="defaults[share_meta]" type="checkbox" value="1" <?php checked( $settings['share_meta'], true, true ) ?>>
						</div>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Facebook App ID', 'wp-quiz-pro' ); ?></td>
					<td>
						<input class="ui" name="defaults[fb_app_id]" type="text" value="<?php echo $settings['fb_app_id'] ?>" >
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Show Ads', 'wp-quiz-pro' ); ?></td>
					<td>
						<div class="ui toggle checkbox">
							<input name="defaults[show_ads]" type="checkbox" value="1" <?php checked( $settings['show_ads'], true, true ) ?>>
						</div>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Show Ads after every nth question', 'wp-quiz-pro' ); ?></td>
					<td>
						<input class="ui" name="defaults[ad_nth_display]" type="number" value="<?php echo $settings['ad_nth_display'] ?>" >
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Repeat Ads', 'wp-quiz-pro' ); ?></td>
					<td>
						<div class="ui toggle checkbox">
							<input name="defaults[repeat_ads]" type="checkbox" value="1" <?php checked( $settings['repeat_ads'], true, true ) ?>>
						</div>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Countdown timer [Seconds/question]', 'wp-quiz-pro' ) ?><br/><small><?php esc_html_e( 'applies to trivia quiz, multiple page layout', 'wp-quiz-pro' ) ?></small></td>
					<td>
						<input class="ui" name="defaults[countdown_timer]" type="number" value="<?php echo $settings['countdown_timer'] ?>">
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Auto scroll to next question', 'wp-quiz-pro' ); ?><br/><small><?php esc_html_e( 'applies to trivia or personality quiz, single page layout', 'wp-quiz-pro' ) ?></small></td>
					<td>
						<div class="ui toggle checkbox">
							<input name="defaults[auto_scroll]" type="checkbox" value="1" <?php checked( $settings['auto_scroll'], true, true ) ?>>
						</div>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Force action to see the results', 'wp-quiz-pro' ); ?><br/><small><?php esc_html_e( 'applies to trivia or personality quiz', 'wp-quiz-pro' ) ?></small></td>
					<td>
						<select class="ui" id="select" name="defaults[force_action]">
							<option value="0" <?php selected( $settings['force_action'], '0', true ) ?>><?php esc_html_e( 'No Action', 'wp-quiz-pro' ); ?></option>
							<option value="1" <?php selected( $settings['force_action'], '1', true ) ?>><?php esc_html_e( 'Capture Email', 'wp-quiz-pro' ); ?></option>
							<option value="2" <?php selected( $settings['force_action'], '2', true ) ?>><?php esc_html_e( 'Facebook Share', 'wp-quiz-pro' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'External Font', 'wp-quiz-pro' ) ?></td>
					<td>
						<input class="ui" name="defaults[external_font]" type="text" value="<?php echo isset( $settings['external_font'] ) ? $settings['external_font'] : '' ?>">
					</td>
				</tr>
			</table>

		<?php

	}

	/**
	 * Html Ad Code content
	 */
	public static function ad_code_content( $settings ) {

		?>
		<div id="ad_code_setting">
			<div style="margin-bottom:5px;"><a style="margin-left:0" href="#" id="new_add_code" class="add-new-h2"><?php esc_html_e( 'Add New', 'wp-quiz-pro' ); ?></a><br style="clear:both;"></div>
			<div><div id="ad_code_container">
			<?php if ( ! empty( $settings['ad_code'] ) ) : ?>
					<?php foreach ( $settings['ad_code'] as $ad ) : ?>
						<?php if ( ! empty( $ad ) ) : ?>
							<div class="ad_row"><textarea rows="4" name="ad_code[]" id="input_ad_code"><?php echo esc_html( $ad ) ?></textarea><span class="ad_action"><a href="#" onclick="remove_add(this, event);"><?php esc_html_e( 'Delete', 'wp-quiz-pro' ); ?></a></span></div>
						<?php endif; ?>
					<?php endforeach; ?>
			<?php endif; ?>
			</div></div>
		</div>
	<?php
	}

	public static function global_settings_content( $settings ) {
		?>
		<table id="global_settings" width="100%" frame="border">
			<tr>
				<td><?php esc_html_e( 'Enable or disable players tracking.', 'wp-quiz-pro' ); ?></td>
				<td>
					<div class="ui toggle checkbox">
						<input name="players_tracking" type="checkbox" value="1" <?php checked( $settings['players_tracking'], true, true ) ?>>
					</div>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Subscribe Box Title', 'wp-quiz-pro' ); ?></td>
				<td>
					<input class="ui" name="defaults[subscribe_box_title]" type="text" value="<?php echo isset( $settings['defaults']['subscribe_box_title'] ) ? $settings['defaults']['subscribe_box_title'] : __( 'Just tell us who you are to view your results !', 'wp-quiz-pro' ) ?>">
				</td>
			</tr>
		</table>
	<?php
	}
}
