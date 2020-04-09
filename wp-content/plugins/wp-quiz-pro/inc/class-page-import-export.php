<?php
/**
 * Class For WP Quiz Pro Import Export page
 */
class WP_Quiz_Pro_Page_Import_Export {

	public static function admin_print_styles() {

		?>
			<style>
				ul.tabs { width: 100%; display: table; border-collapse: separate; }
				ul.tabs li { font-weight: bold; text-align: center; margin: 0; cursor: pointer; display: table-cell; line-height: 14px; border: 1px solid #eee; background-color: #eee; }
				ul.tabs li.active { background-color: #fff; }
				ul.tabs li a { padding: 10px 5px; display: block; text-decoration: none; font-weight: bold; }
				ul.tabs li a:focus{ box-shadow: none; }
				table#export_wp_quiz { border-collapse: collapse; border-color: #eee; }
				table#export_wp_quiz tr td { border-bottom: 1px solid #eee; padding: 10px; color: #666; font-size: 14px; }
				.tab-content>.tab-pane { display: none; }
				.tab-content>.active { display: block; }
				.wp_quiz_numbers a{ text-decoration: none; padding: 2px; margin: 0 4px; }
				.wp_quiz_numbers a.selected{ color: #666; }
				.wp_quiz_pag_div{ margin: 5px; }
				#export .tablenav-pages-navspan{ height: 14px; }
				#export .tablenav-pages .current-page{ height: 26px; padding-bottom: 2px; }
				.tablenav .tablenav-pages a{ height: 14px; }
				.paging-input{ margin: 0 5px; }
				.demo-container { width: 32%; display: inline-block; margin-right: 1.3%; background: #FAFBFD; border-radius: 3px; margin-bottom: 20px; padding: 0; }
				.demo-container:nth-child(3n+3) { margin-right: 0; }
                body.rtl .demo-container { margin-right: 0px; margin-left: 1.3%; }
                body.rtl .demo-container:nth-child(3n+3) { margin-left: 0; }
				.demo-container a{ text-decoration: none; }
				.demo-thumb img{ width: 100%; }
				#import hr { margin: 30px 0; }
				.demo-thumb label > input{ display:none; }
				.demo-thumb label img{ cursor:pointer; border:2px solid transparent; }
				.demo-thumb label > input:checked + img { border:2px solid #2196f3; }
			</style>
		<?php

	}

	public static function display_messages() {

		$message = false;
		if ( isset( $_REQUEST['message'] ) && ( $msg = (int) $_REQUEST['message'] ) ) {
			if ( 3 === $msg ) {
				$message = esc_html__( 'Quiz imported successfully', 'wp-quiz-pro' );
			} else if ( 4 === $msg ) {
				$message = esc_html__( 'Failed to import Quiz', 'wp-quiz-pro' );
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

		if ( isset( $_POST['action'] ) ) {
			switch ( $_POST['action'] ) {
				case 'import':
					if ( ! current_user_can( 'manage_options' ) ) {
						break;
					}
					$location = admin_url() . 'edit.php?post_type=wp_quiz&page=wp_quiz_ie';
					$status = false;
					if ( isset( $_POST['demo'] ) && 'true' == $_POST['demo'] ) {
						if ( isset( $_POST['quiz_demo'] ) && ! empty( $_POST['quiz_demo'] ) ) {
							$status = self::import_wp_quiz_demo();
						}
					} else {
						$status = self::import_wp_quiz();
					}
					if ( $status ) {
						$location = add_query_arg( 'message', 3, $location );
						wp_redirect( $location );
					} else {
						$location = add_query_arg( array( 'error' => true, 'message' => 4 ), $location );
						wp_redirect( $location );
					}
					exit;

				case 'export':
					if ( ! current_user_can( 'manage_options' ) ) {
						break;
					}
					if ( isset( $_POST['export_quiz'] ) && isset( $_POST['wp_quizzes'] ) ) {
						self::export_wp_quiz();
						exit;
					} else if ( isset( $_POST['export_settings'] ) ) {
						self::export_wp_quiz_settings();
						exit;
					}
			} // end switch
		}

		// Needed javascripts to allow drag/drop, expand/collapse and hide/show of boxes
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'wp_quiz-bootstrap', wp_quiz_pro()->plugin_url() . 'assets/js/bootstrap.min.js', array( 'jquery' ), wp_quiz_pro()->version, true );

		$screen = get_current_screen();
		add_meta_box( 'import-export-content', esc_html__( 'Import and Export Quizzes', 'wp-quiz-pro' ), array( __CLASS__, 'import_export_content' ), $screen->id, 'normal', 'core' );
	}

	public static function page() {

		$screen 		= get_current_screen();
		$columns 		= absint( $screen->get_columns() );
		$columns_css 	= '';

		if ( $columns ) {
			$columns_css = " columns-$columns";
		}
		?>
			<div class="wrap" id="config-page">
				<h2><?php esc_html_e( 'Import/Export', 'wp-quiz-pro' ); ?></h2>
				<?php self::display_messages(); ?>
				<?php wp_nonce_field( 'wp_quiz_ie_page' ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				<input type="hidden" name="page" value="wp_quiz_config" />
				<div id="poststuff">
					<div id="post-body" class="metabox-holder <?php echo $columns_css ?>">
						<div id="postbox-container-2" class="postbox-container">
							<?php
								do_meta_boxes( $screen->id, 'normal', '' );
							?>
						</div>
					</div>
				</div>

			</div>
			<script type="text/javascript">
				//<![CDATA[
					jQuery(document).ready( function($) {
						// close postboxes that should be closed
						$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
						// postboxes setup
						postboxes.add_postbox_toggles('<?php echo $screen->id ?>');

						$('#tabs').tab();

						$("#selectall").change(function() {
							$(".wp_quizId").prop('checked', $(this).prop("checked"));
						});
					});
				//]]>
			</script>
		<?php
	}

	public static function import_export_content() {
		include wp_quiz_pro()->plugin_dir() . 'inc/pagination-class.php';
		?>
			<div>
				<ul id="tabs" class="tabs" data-tabs="tabs">
					<li class="active"><a href="#import" data-toggle="tab"><?php esc_html_e( 'Import', 'wp-quiz-pro' ); ?></a></li>
					<li><a href="#export" data-toggle="tab"><?php esc_html_e( 'Export', 'wp-quiz-pro' ); ?></a></li>
				</ul>
				<div class='tab-content'>
					<div class="tab-pane" id="export">
						<?php
							global $wpdb;
							$wp_quizzes = $wpdb->get_results( "SELECT `ID`,`post_title` FROM $wpdb->posts WHERE post_type='wp_quiz'" );
						?>
						<?php  if ( ! empty( $wp_quizzes ) ) : ?>
							<p><?php esc_html_e( 'Select Quiz to Export', 'wp-quiz-pro' ); ?></p>
							<form action="<?php echo admin_url( 'edit.php?post_type=wp_quiz&page=wp_quiz_ie' ); ?>" method="post">
								<table id="export_wp_quiz" width="100%" frame="border">
									<input type="hidden" name="post_type" value="wp_quiz" />
									<input type="hidden" name="page" value="wp_quiz_ie" />
									<tr>
										<td style="width:5%;"><input id="selectall" type="checkbox" /></td>
										<td><?php esc_html_e( 'Select All', 'wp-quiz-pro' ); ?></td>
									</tr>
									<?php foreach ( $wp_quizzes as $wp_quiz ) : ?>
									<tr>
										<td><input class="wp_quizId" type="checkbox" value="<?php echo $wp_quiz->ID; ?>" name="wp_quizzes[]" /></td>
										<td><?php echo $wp_quiz->post_title; ?></td>
									</tr>
									<?php endforeach; ?>
								</table>
								<!--<div class="wp_quiz_pag_div tablenav"><div class=""><?php //echo $pageNumbers = '<div class="tablenav-pages">'.$pagination->getLinks($_GET).'</div>'; ?></div></div>-->
								<p>
									<input type="hidden" name="action" value="export" />
									<input type="submit" name="export_quiz" id="submit" class="button-primary" value="Export Quiz">&nbsp;<input type="submit" name="export_settings" id="submit" class="button-primary" value="Export Settings">&nbsp;
								</p>
							</form>

						<?php else : ?>
							<div><?php esc_html_e( 'No Quiz to Export', 'wp-quiz-pro' ); ?></div>
						<?php  endif; ?>

					</div>
					<div class="tab-pane active" id="import">
						<p><?php esc_html_e( 'Import Demos', 'wp-quiz-pro' ); ?></p>
						<form action="<?php echo admin_url( 'edit.php?post_type=wp_quiz&page=wp_quiz_ie' ); ?>" method="post">
							<div>
								<div class="demo-container">
									<a href="http://demo.mythemeshop.com/wp-quiz-pro/category/personality/" target="_blank"><?php esc_html_e( 'View Demo', 'wp-quiz-pro' ); ?></a>
									<div class="demo-thumb">
										<label>
											<input type="radio" name="quiz_demo" value="personality"/>
											<img src="<?php echo wp_quiz_pro()->plugin_url() . '/demo/wp-quiz-pro-personality.jpg'; ?>" />
										</label>
									</div>
								</div>
								<div class="demo-container">
									<a href="http://demo.mythemeshop.com/wp-quiz-pro/category/trivia/" target="_blank"><?php esc_html_e( 'View Demo', 'wp-quiz-pro' ); ?></a>
									<div class="demo-thumb">
										<label>
											<input type="radio" name="quiz_demo" value="trivia"/>
											<img src="<?php echo wp_quiz_pro()->plugin_url() . '/demo/wp-quiz-pro-trivia.jpg'; ?>" />
										</label>
									</div>
								</div>
								<div class="demo-container">
									<a href="http://demo.mythemeshop.com/wp-quiz-pro/category/swiper/" target="_blank"><?php esc_html_e( 'View Demo', 'wp-quiz-pro' ); ?></a>
									<div class="demo-thumb">
										<label>
											<input type="radio" name="quiz_demo" value="swiper"/>
											<img src="<?php echo wp_quiz_pro()->plugin_url() . '/demo/wp-quiz-pro-swiper.jpg'; ?>" />
										</label>
									</div>
								</div>
								<div class="demo-container">
									<a href="http://demo.mythemeshop.com/wp-quiz-pro/category/flip-cards/" target="_blank"><?php esc_html_e( 'View Demo', 'wp-quiz-pro' ); ?></a>
									<div class="demo-thumb">
										<label>
											<input type="radio" name="quiz_demo" value="flip"/>
											<img src="<?php echo wp_quiz_pro()->plugin_url() . '/demo/wp-quiz-pro-flip.jpg'; ?>" />
										</label>
									</div>
								</div>
								<div class="demo-container">
									<a href="http://demo.mythemeshop.com/wp-quiz-pro/category/fb-quiz/" target="_blank"><?php esc_html_e( 'View Demo', 'wp-quiz-pro' ); ?></a>
									<div class="demo-thumb">
										<label>
											<input type="radio" name="quiz_demo" value="facebook"/>
											<img src="<?php echo wp_quiz_pro()->plugin_url() . '/demo/wp-quiz-pro-facebook.jpg'; ?>" />
										</label>
									</div>
								</div>
							</div>
							<p>
								<input type="hidden" name="demo" value="true" />
								<input type="hidden" name="action" value="import" />
								<input type="submit" name="submit" id="submit" class="button-primary" value="Import Demo">&nbsp;
							</p>
						</form>
						<hr/>
						<p><?php esc_html_e( 'Import Quizzes', 'wp-quiz-pro' ); ?></p>
						<form action="<?php echo admin_url( 'edit.php?post_type=wp_quiz&page=wp_quiz_ie' ); ?>" method="post" enctype="multipart/form-data">
							<p><input type="file" name="wp_quizzes" />
							<p>
								<input type="hidden" name="action" value="import" />
								<input type="submit" name="submit" id="submit" class="button-primary" value="Import Quiz">&nbsp;
							</p>
						</form>
					</div>
				</div>
			</div>
		<?php
	}

	public static function export_wp_quiz() {

		if ( ! isset( $_POST['wp_quizzes'] ) ) {
			return false;
		}

		global $wpdb;
		$wp_quiz_ids                = $_POST['wp_quizzes'];
		$exported_wp_quizzes        = array();
		$wq_upload_dir             	= wp_upload_dir();
		$wq_upload_dir['basedir']	= $wq_upload_dir['basedir'] . '/wp_quiz-export/';
		$wp_quiz_images            	= array();

		// Loop through wp_quiz IDs and add them
		foreach ( $wp_quiz_ids as $qid ) {
			$qid 			= absint( $qid );
			$title          = get_the_title( $qid );
			$questions      = get_post_meta( $qid, 'questions', true );
			$results        = get_post_meta( $qid, 'results', true );
			$settings		= get_post_meta( $qid, 'settings', true );

			if ( has_post_thumbnail( $qid ) ) {
				$featured_image_id	= get_post_thumbnail_id( $qid );
				$thumb_url_array   	= wp_get_attachment_image_src( $featured_image_id, 'full' );
				$featured_image     = $thumb_url_array[0];
			} else {
				$featured_image	= '';
			}

			array_push($exported_wp_quizzes, array(
				'title'         	=> $title,
				'type'				=> get_post_meta( $qid, 'quiz_type', true ),
				'questions'         => $questions,
				'results'           => $results,
				'settings'			=> $settings,
				'featured_image'    => $featured_image,
			));
		}

		// Send export
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename=wp_quiz-' . rand( 0, 1000 ) . '.json;' );
		header( 'Content-Transfer-Encoding: binary' );
		echo json_encode( $exported_wp_quizzes );
		die;
	}

	public static function import_wp_quiz() {

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		global $wpdb;

		$imported_file         		= $_FILES['wp_quizzes'];
		$wq_upload_dir            	= wp_upload_dir();
		$wq_upload_dir['basedir']	= $wq_upload_dir['basedir'] . '/wp_quiz-import';
		$wq_upload_dir['baseurl']	= $wq_upload_dir['baseurl'] . '/wp_quiz-import';

		if ( ! move_uploaded_file( $imported_file['tmp_name'], $wq_upload_dir['basedir'] . '/' . $imported_file['name'] ) ) {
			return false;
		}

		// Get JSON File and It's contents
		$quiz_obj = file_get_contents( $wq_upload_dir['basedir'] . '/' . $imported_file['name'] );
		$quiz_obj = json_decode( $quiz_obj, true );

		foreach ( $quiz_obj as $qok => $qov ) {
			// Questions
			foreach ( $qov['questions'] as $qk => $qv ) {
				if ( ! empty( $qv['image'] ) ) {
					$new_src = self::download_image_file( $qv['image'] );
					if ( $new_src && ! empty( $new_src ) ) {
						$qov['questions'][ $qk ]['image']  = $new_src;
					}
				}

				if ( ! empty( $qv['backImage'] ) ) {
					$new_src = self::download_image_file( $qv['backImage'] );
					if ( $new_src && ! empty( $new_src ) ) {
						$qov['questions'][ $qk ]['backImage'] = $new_src;
					}
				}

				if ( ! empty( $qv['answers'] ) ) {
					foreach ( $qv['answers'] as $ak => $av ) {
						if ( ! empty( $av['image'] ) ) {
							$new_src = self::download_image_file( $av['image'] );
							if ( $new_src && ! empty( $new_src ) ) {
								$qov['questions'][ $qk ]['answers'][ $ak ]['image'] = $new_src;
							}
						}
					}
				}
			}

			// Results
			if ( ! empty( $qv['results'] ) ) {
				foreach ( $qov['results'] as $rk => $rv ) {
					if ( ! empty( $rv['image'] ) ) {
						$new_src = self::download_image_file( $rv['image'] );
						if ( $new_src && ! empty( $new_src ) ) {
							$qov['results'][ $rk ]['image'] = $new_src;
						}
					}
				}
			}

			$questions	= $qov['questions'];
			$results    = $qov['results'];
			$settings	= $qov['settings'];
			$type		= $qov['type'];

			$post_id    = wp_insert_post(array(
				'post_content'   => '<p></p>',
				'post_name'      => $qov['title'],
				'post_title'     => $qov['title'],
				'post_status'    => 'pending',
				'post_type'      => 'wp_quiz',
			));

			update_post_meta( $post_id, 'questions', $questions );
			update_post_meta( $post_id, 'results', $results );
			update_post_meta( $post_id, 'settings', $settings );
			update_post_meta( $post_id, 'quiz_type', $type );

			if ( empty( $qov['featured_image'] ) ) {
				continue;
			}

			$new_path = self::download_image_file( $qov['featured_image'], true );

			$wp_filetype = wp_check_filetype( $new_path );
			$attachment = array(
				'guid'         		=> $new_path,
				'post_mime_type'  	=> $wp_filetype['type'],
				'post_title'     	=> basename( $new_path ),
				'post_content'    	=> '',
				'post_status'      	=> 'inherit',
			);

			$attach_id = wp_insert_attachment( $attachment, $new_path, $post_id );

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $new_path );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// add featured image to post
			add_post_meta( $post_id, '_thumbnail_id', $attach_id );
		}

		unlink( $wq_upload_dir['basedir'] . '/' . $imported_file['name'] );
		return true;
	}

	public static function import_wp_quiz_demo() {

		global $wpdb;
		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Get JSON File and It's contents
		$quiz_obj = file_get_contents( wp_quiz_pro()->plugin_dir() . 'demo/wp_quiz_' . $_POST['quiz_demo'] . '.json' );
		$quiz_obj = json_decode( $quiz_obj, true );

		foreach ( $quiz_obj as $qok => $qov ) {

			// Questions
			foreach ( $qov['questions'] as $qk => $qv ) {
				if ( ! empty( $qv['image'] ) ) {
					$new_src = self::download_image_file( $qv['image'] );
					if ( $new_src && ! empty( $new_src ) ) {
						$qov['questions'][ $qk ]['image']  = $new_src;
					}
				}

				if ( ! empty( $qv['backImage'] ) ) {
					$new_src = self::download_image_file( $qv['backImage'] );
					if ( $new_src && ! empty( $new_src ) ) {
						$qov['questions'][ $qk ]['backImage'] = $new_src;
					}
				}

				if ( ! empty( $qv['answers'] ) ) {
					foreach ( $qv['answers'] as $ak => $av ) {
						if ( ! empty( $av['image'] ) ) {
							$new_src = self::download_image_file( $av['image'] );
							if ( $new_src && ! empty( $new_src ) ) {
								$qov['questions'][ $qk ]['answers'][ $ak ]['image'] = $new_src;
							}
						}
					}
				}
			}

			// Results
			if ( ! empty( $qv['results'] ) ) {
				foreach ( $qov['results'] as $rk => $rv ) {
					if ( ! empty( $rv['image'] ) ) {
						$new_src = self::download_image_file( $rv['image'] );
						if ( $new_src && ! empty( $new_src ) ) {
							$qov['results'][ $rk ]['image'] = $new_src;
						}
					}
				}
			}

			$questions	= $qov['questions'];
			$results    = $qov['results'];
			$settings	= $qov['settings'];
			$type		= $qov['type'];

			$post_id    = wp_insert_post(array(
				'post_content'   => '<p></p>',
				'post_name'      => $qov['title'],
				'post_title'     => $qov['title'],
				'post_status'    => 'pending',
				'post_type'      => 'wp_quiz',
			));

			update_post_meta( $post_id, 'questions', $questions );
			update_post_meta( $post_id, 'results', $results );
			update_post_meta( $post_id, 'settings', $settings );
			update_post_meta( $post_id, 'quiz_type', $type );

			if ( empty( $qov['featured_image'] ) ) {
				continue;
			}

			$new_path = self::download_image_file( $qov['featured_image'], true );

			$wp_filetype = wp_check_filetype( $new_path );
			$attachment = array(
				'guid'         		=> $new_path,
				'post_mime_type'  	=> $wp_filetype['type'],
				'post_title'     	=> basename( $new_path ),
				'post_content'    	=> '',
				'post_status'      	=> 'inherit',
			);

			$attach_id = wp_insert_attachment( $attachment, $new_path, $post_id );

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $new_path );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// add featured image to post
			add_post_meta( $post_id, '_thumbnail_id', $attach_id );
		}

		return true;
	}

	public static function download_image_file( $file, $path = false, $post_id = '', $desc = '' ) {

		// Need to require these files
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		if ( ! empty( $file ) && self::is_image_file( $file ) ) {
			// Download file to temp location
			$tmp = download_url( $file );

			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $file, $matches );
			$file_array['name'] = basename( $matches[0] );
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
				return false;
			}
			$desc = $file_array['name'];
			$id = media_handle_sideload( $file_array, $post_id, $desc );
			// If error storing permanently, unlink
			if ( is_wp_error( $id ) ) {
				@unlink( $file_array['tmp_name'] );
				return false;
			}

			if ( $path ) {
				return get_attached_file( $id );
			} else {
				return wp_get_attachment_url( $id );
			}
		}
	}

	public static function export_wp_quiz_settings() {

		$quiz_settings = get_option( 'wp_quiz_pro_default_settings' );

		//Send export
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename=wp_quiz_settings_' . rand( 0, 1000 ) . '.json;' );
		header( 'Content-Transfer-Encoding: binary' );
		echo json_encode( $quiz_settings );
		die;
	}

	public static function is_image_file( $file ) {

		$check = false;
		$filetype = wp_check_filetype( $file );
		$valid_exts = array( 'jpg', 'jpeg', 'gif', 'png' );
		if ( in_array( strtolower( $filetype['ext'] ), $valid_exts ) ) {
			$check = true;
		}

		return $check;
	}
}
