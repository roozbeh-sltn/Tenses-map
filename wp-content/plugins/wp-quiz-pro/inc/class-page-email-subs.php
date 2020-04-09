<?php
/**
 * Class For WP Quiz Pro Email Subscription page
 */
class WP_Quiz_Pro_Page_Email_Subs extends WP_List_Table {

	private static $listtable = null;
	public static $messages = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( array(
				'plural'   => 'email subscribers',
				'singular' => 'email subscriber',
				'ajax'     => true,
			)
		);
	}

	/**
	 * @return bool|void
	 */
	public function ajax_user_can() {

		return current_user_can( 'manage-options' );
	}

	public function prepare_items() {

		self::$messages = array(
			0 => '', // Unused. Messages start at index 1.
			1 => esc_html__( 'Email Subscriber added.', 'wp-quiz-pro' ),
			2 => esc_html__( 'Email Subscriber deleted.', 'wp-quiz-pro' ),
			3 => esc_html__( 'Email Subscriber updated.', 'wp-quiz-pro' ),
			4 => esc_html__( 'Email Subscriber not added.', 'wp-quiz-pro' ),
			5 => esc_html__( 'Email Subscriber not updated.', 'wp-quiz-pro' ),
			6 => esc_html__( 'Email Subscribers deleted.', 'wp-quiz-pro' ),
		);

		$per_page     = $this->get_items_per_page( 'wq_email_per_page', 25 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$data = $this->get_email_subs( $per_page, $current_page );

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = $data;

	}

	public function no_items() {
		esc_html_e( 'No Email Subscriber.', 'wp-quiz-pro' );
	}

	/**
	 * @return array
	 */
	function get_bulk_actions() {

		$actions             = array();
		$actions['delete'] = esc_html__( 'Delete', 'wp-quiz-pro' );

		return $actions;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'email':
			case 'username':
			case 'date':
				return $item[ $column_name ];
		}
	}

	/**
	 * @return array
	 */
	function get_columns() {

		$email_subs_columns            	= array();
		$email_subs_columns['cb']      	= '<input type="checkbox" />';
		$email_subs_columns['pid'] 		= esc_html__( 'Quiz', 'wp-quiz-pro' );
		$email_subs_columns['username']	= esc_html__( 'Name', 'wp-quiz-pro' );
		$email_subs_columns['email']	= esc_html__( 'E-mail', 'wp-quiz-pro' );
		$email_subs_columns['date'] 	= esc_html__( 'Date', 'wp-quiz-pro' );

		return $email_subs_columns;
	}

	/**
	 * @return array
	 */
	public function get_sortable_columns() {

		return array(
			'email'		=> 'email',
			'username' 	=> 'username',
			'date'		=> 'date',
		);
	}

	/**
	 * The cb Column
	 *
	 * @param $item
	 * @return string
	 */
	public function column_cb( $item ) {

		return '<input type="checkbox" name="email_subs[]" value="' . $item['id'] . '" />';
	}

	/**
	 * The pid column
	 *
	 * @param $item
	 * @return string
	 */
	public function column_pid( $item ) {

		global $wpdb;
		$quiz_name = $wpdb->get_var( "SELECT post_title FROM {$wpdb->prefix}posts where ID ='{$item['pid']}'" );

		$r = '<strong title="' . esc_html__( 'Quiz name', 'wp-quiz-pro' ) . '">' . esc_html( $quiz_name ) . '</strong>';
		$actions = array();
		if ( current_user_can( 'manage_options' ) ) {
			$actions['delete']   = '<a class="submitdelete" href="' . wp_nonce_url( admin_url() . 'edit.php?post_type=wp_quiz&page=wp_quiz_email_subs&action=delete&email_subs[]=' . $item['id'] . '&message=2', 'bulk-email-subs' ) . '" onclick="return showNotice.warn();">' . esc_html__( 'Delete', 'wp-quiz-pro' ) . '</a>';
		}

		$r .= '<div class="email-sub-normal">' . $this->row_actions( $actions ) . '</div>';

		return $r;
	}

	public static function load() {

		// Create Table
		self::$listtable = new self;
		switch ( self::$listtable->current_action() ) {

			// Delete Email Subscriber
			case 'delete':

				if ( ! current_user_can( 'manage_options' ) ) {
					break;
				}
				if ( is_array( $_GET['email_subs'] ) ) {
					//check_admin_referer( 'bulk-email-subs' );
					foreach ( $_GET['email_subs'] as $email_sub_id ) {
						self::delete_email_sub( $email_sub_id );
					}
				}
				break;

			case 'export':
				if ( ! current_user_can( 'manage_options' ) ) {
					break;
				}
				check_admin_referer( 'export-email-subs' );
				self::export_email_subs();
				exit;
		} // end switch

		add_screen_option( 'per_page', array(
				'label'   => esc_html__( 'EmailsSubscribers', 'wp-quiz-pro' ),
				'default' => 75,
				'option'  => 'wq_email_per_page',
			)
		);
		self::$listtable->prepare_items();

	}

	public static function admin_print_styles() {
		?>
			<style type="text/css" media="screen">
			</style>
		<?php
	}


	public static function page() {

		echo '<div class="wrap" id="email-page">';
		echo '<h2><span id="email-sub-page-icon">&nbsp;</span>' . esc_html__( 'Email Subscribers', 'wp-quiz-pro' ) . '&nbsp;<a href="' . wp_nonce_url( admin_url() . 'edit.php?post_type=wp_quiz&page=wp_quiz_email_subs&action=export', 'export-email-subs' ) . '" class="add-new-h2">' . esc_html__( 'Export CSV', 'wp-quiz-pro' ) . '</a></h2>';
		self::display_messages();
		echo '<form method="post"><input type="hidden" name="page" value="wp_quiz_email_subs" />';
		self::$listtable->search_box( 'Search Email', 'search_email' );
		echo '</form><form id="posts-filter" action="" method="get">';
		echo '<input type="hidden" name="post_type" value="wp_quiz" />';
		echo '<input type="hidden" name="page" value="wp_quiz_email_subs" />';
		self::$listtable->display();

		echo '</form></div>';
	}

	/**
	 * Retrieve email subscribers data from the database
	 *
	 * @return mixed
	 */
	public function get_email_subs( $per_page = 25, $page_number = 1 ) {

		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}wp_quiz_emails";
		if ( isset( $_POST['s'] ) ) {
			$sql .= " WHERE email LIKE '%{$_POST['s']}%'";
		}
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY email ASC';
		}
		$sql .= ' LIMIT ' . $per_page;
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		return $result;
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {

		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wp_quiz_emails";
		if ( isset( $_POST['s'] ) ) {
			$sql .= " WHERE email LIKE '%{$_POST['s']}%'";
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Delete a subcriber record.
	 *
	 * @param int $id email subscriber id
	 */
	public static function delete_email_sub( $id ) {

		global $wpdb;
		$wpdb->delete(	"{$wpdb->prefix}wp_quiz_emails", array( 'id' => $id ), array( '%d' ) );
	}

	public static function display_messages() {

		$message = false;
		if ( isset( $_REQUEST['message'] ) && ( $msg = (int) $_REQUEST['message'] ) ) {
			if ( isset( self::$messages[ $msg ] ) ) {
				$message = self::$messages[ $msg ];
			}
		}
		$class = ( isset( $_REQUEST['error'] ) ) ? 'error' : 'updated';

		if ( $message ) :
		?>
			<div id="message" class="<?php echo $class; ?> notice is-dismissible"><p><?php echo $message; ?></p></div>
		<?php
		endif;

	}

	public static function export_email_subs() {

		global $wpdb;
		$email_list = array( 'name, email' );

		$emails = $wpdb->get_results(
			"SELECT * FROM `" . $wpdb->prefix . "wp_quiz_emails` ",
			ARRAY_A
		);

		foreach ( $emails as $email ) {
			array_push( $email_list, $email['username'] . ',' . $email['email'] );
		}

		//Send export
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Type: text/csv' );
		header( 'Content-Type: application/force-download' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=emails.csv;' );
		echo implode( "\r\n", $email_list );
		die;
	}
}
