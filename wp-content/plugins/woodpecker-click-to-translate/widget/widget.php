<?php
namespace WLClickToTranslate;

/**
 * Adds WLButton_Widget widget.
 */
class WLButton_Widget extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'wlbutton_widget', // Base ID
			esc_html__( 'Click to Translate button', 'wl-click-to-translate' ), // Name
			array( 'description' => esc_html__( 'The place to show translation enable / disable button.', 'wl-click-to-translate' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		echo "<div class='wlapi_toggle_button_container'></div>";
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		?>
    <br/>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		return $old_instance;
	}

} // class WLButton_Widget
