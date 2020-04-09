<?php
/**
 * Class For WP Quiz Pro Player page
 */
class WP_Quiz_Pro_Page_Players extends WP_List_Table {

	private static $listtable = null;
	public static $messages = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( array(
			'plural'   => 'players',
			'singular' => 'player',
			'ajax'     => true,
		) );
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
			1 => esc_html__( 'Player added.', 'wp-quiz-pro' ),
			2 => esc_html__( 'Player deleted.', 'wp-quiz-pro' ),
			3 => esc_html__( 'Player updated.', 'wp-quiz-pro' ),
			4 => esc_html__( 'Player not added.', 'wp-quiz-pro' ),
			5 => esc_html__( 'Player not updated.', 'wp-quiz-pro' ),
			6 => esc_html__( 'Players deleted.', 'wp-quiz-pro' ),
		);

		$per_page     = $this->get_items_per_page( 'wq_player_per_page', 25 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$data = $this->get_players( $per_page, $current_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );

		$this->items = $data;
	}

	public function no_items() {
		esc_html_e( 'No Player.', 'wp-quiz-pro' );
	}

	/**
	 * @return array
	 */
	public function get_bulk_actions() {

		$actions = array();
		$actions['delete'] = esc_html__( 'Delete', 'wp-quiz-pro' );

		return $actions;
	}

	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'username':
			case 'result':
			case 'quiz_type':
			case 'date':
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
		}
	}

	/**
	 * @return array
	 */
	public function get_columns() {

		$player_columns            		= array();
		$player_columns['cb']      		= '<input type="checkbox" />';
		$player_columns['username']		= esc_html__( 'User', 'wp-quiz-pro' );
		$player_columns['pid'] 			= esc_html__( 'Quiz', 'wp-quiz-pro' );
		$player_columns['result'] 		= esc_html__( 'Result', 'wp-quiz-pro' );
		$player_columns['quiz_type']	= esc_html__( 'Type', 'wp-quiz-pro' );
		$player_columns['date']			= esc_html__( 'Date', 'wp-quiz-pro' );

		return $player_columns;
	}

	/**
	 * @return array
	 */
	public function get_sortable_columns() {

		return array(
			'username'	=> 'username',
			'result'	=> 'result',
			'quiz_type' => 'quiz_type',
			'date' 		=> 'date',
		);
	}

	/**
	 * The cb Column
	 *
	 * @param $item
	 * @return string
	 */
	public function column_cb( $item ) {

		return '<input type="checkbox" name="players[]" value="' . $item['id'] . '" />';
	}

	/**
	 * The name column
	 *
	 * @param $item
	 * @return string
	 */
	public function column_username( $item ) {

		$r = '<strong title="' . esc_html__( 'Username name', 'wp-quiz-pro' ) . '">' . esc_html( $item['username'] ) . '</strong>';
		$actions = array();
		if ( current_user_can( 'manage_options' ) ) {
			$actions['delete'] = '<a class="submitdelete" href="' . wp_nonce_url( admin_url() . 'edit.php?post_type=wp_quiz&page=wp_quiz_players&action=delete&players[]=' . $item['id'] . '&message=2', 'bulk-players' ) . '" onclick="return showNotice.warn();">' . esc_html__( 'Delete', 'wp-quiz-pro' ) . '</a>';
		}

		$r .= '<div class="player-normal">' . $this->row_actions( $actions ) . '</div>';

		return $r;
	}

	/**
	 * The name column
	 *
	 * @param $item
	 * @return string
	 */
	public function column_pid( $item ) {

		global $wpdb;
		$quiz_name = $wpdb->get_var( "SELECT post_title FROM {$wpdb->prefix}posts where ID = '{$item['pid']}' " );

		return  $quiz_name;
	}

	public static function load() {

		//Create Table
		self::$listtable = new self;
		switch ( self::$listtable->current_action() ) {
			case 'delete': //Delete Player
				if ( ! current_user_can( 'manage_options' ) ) {
					break;
				}
				if ( is_array( $_GET['players'] ) ) {
					check_admin_referer( 'bulk-players' );
					foreach ( $_GET['players'] as $player_id ) {
						self::delete_player( $player_id );
					}
				}
				break;
			case 'export':
				if ( ! current_user_can( 'manage_options' ) ) {
					break;
				}
				check_admin_referer( 'export-players' );
				self::export_players();
				exit;
		} // end switch

		add_screen_option( 'per_page', array(
			'label'   => esc_html__( 'Players', 'wp-quiz-pro' ),
			'default' => 75,
			'option'  => 'wq_player_per_page',
		) );

		self::$listtable->prepare_items();
	}

	public static function admin_print_styles() {
		?>
		<style type="text/css" media="screen">
		</style>
		<?php
	}

	public static function page() {

		echo '<div class="wrap" id="player-page">';
		echo '<h2><span id="player-page-icon">&nbsp;</span>' . esc_html__( 'Players', 'wp-quiz-pro' ) . '&nbsp;<a href="' . wp_nonce_url( admin_url() . 'edit.php?post_type=wp_quiz&page=wp_quiz_players&action=export', 'export-players' ) . '" class="add-new-h2">' . esc_html__( 'Export CSV', 'wp-quiz-pro' ) . '</a></h2>';
		self::display_messages();
		echo '<form method="post"><input type="hidden" name="page" value="wp_quiz_players" />';
		self::$listtable->search_box( 'Search Player', 'search_player' );
		echo '</form><form id="posts-filter" action="" method="get">';
		echo '<input type="hidden" name="post_type" value="wp_quiz" />';
		echo '<input type="hidden" name="page" value="wp_quiz_players" />';
		self::$listtable->display();

		echo '</form></div>';
	}

	/**
	 * Retrieve player data from the database
	 *
	 * @return mixed
	 */
	public function get_players( $per_page = 25, $page_number = 1 ) {

		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}wp_quiz_players";
		if ( isset( $_POST['s'] ) ) {
			$sql .= " WHERE username LIKE '%{$_POST['s']}%'";
		}
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY username ASC';
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
		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wp_quiz_players";
		if ( isset( $_POST['s'] ) ) {
			$sql .= " WHERE username LIKE '%{$_POST['s']}%'";
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Delete a player record.
	 *
	 * @param int $id state ID
	 */
	public static function delete_player( $id ) {

		global $wpdb;
		$wpdb->delete( "{$wpdb->prefix}wp_quiz_players", array( 'ID' => $id ), array( '%d' ) );
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

	public static function export_players() {

		global $wpdb;
		$player_list = array( 'id, quiz, date, ip, username, result, type' );

		$players = $wpdb->get_results(
			"SELECT * FROM `" . $wpdb->prefix . "wp_quiz_players` ",
			ARRAY_A
		);

		foreach ( $players as $player ) {
			unset( $player['correct_answered'] );
			unset( $player['rid'] );
			$player['pid'] = html_entity_decode( get_the_title( $player['pid'] ) );

			array_push( $player_list, implode( ',', $player ) );
		}

		//Send export
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Type: text/csv' );
		header( 'Content-Type: application/force-download' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=players.csv;' );
		echo implode( "\r\n", $player_list );
		die;
	}
}
