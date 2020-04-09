<?php
/*
Plugin Name: Text To Speech Widget
Plugin URI:  https://github.com/pmbaldha/wp-current-location-on-map/
Description: WP Text To Speech Widget converts any text in to speech in selected language and voice.
Text Domain: text-to-speech-widget
Domain Path: /languages
Version:     1.0
Author:      Prashant Baldha
Author URI:  https://github.com/pmbaldha/
License:     GPL2
*/

/**
 * Adds Ttsw_Widget widget.
 */
class Ttsw_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'ttsw_widget', // Base ID
			__( 'Text To Speech', 'text-to-speech-widget' ), // Name
			array( 'description' => __( 'Converts text to spech', 'text-to-speech-widget' ), ) // Args
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
		wp_enqueue_style( 'voice_css' );
		wp_enqueue_script( 'responsivevoice_js' );
		wp_enqueue_script( 'voice_js' );
		
		echo $args['before_widget'];
		echo '<div class="tts_container">';
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
		echo '<textarea name="ttsw_text" class="ttsw_text" placeholder="Write text which you would like to speak"></textarea>';
		echo '<select class="ttsw_voice"><option value="UK English Female">UK English Female</option><option value="UK English Male">UK English Male</option><option value="US English Female">US English Female</option><option value="Arabic Male">Arabic Male</option><option value="Arabic Female">Arabic Female</option><option value="Armenian Male">Armenian Male</option><option value="Australian Female">Australian Female</option><option value="Brazilian Portuguese Female">Brazilian Portuguese Female</option><option value="Chinese Female">Chinese Female</option><option value="Czech Female">Czech Female</option><option value="Danish Female">Danish Female</option><option value="Deutsch Female">Deutsch Female</option><option value="Dutch Female">Dutch Female</option><option value="Finnish Female">Finnish Female</option><option value="French Female">French Female</option><option value="Greek Female">Greek Female</option><option value="Hatian Creole Female">Hatian Creole Female</option><option value="Hindi Female">Hindi Female</option><option value="Hungarian Female">Hungarian Female</option><option value="Indonesian Female">Indonesian Female</option><option value="Italian Female">Italian Female</option><option value="Japanese Female">Japanese Female</option><option value="Korean Female">Korean Female</option><option value="Latin Female">Latin Female</option><option value="Norwegian Female">Norwegian Female</option><option value="Polish Female">Polish Female</option><option value="Portuguese Female">Portuguese Female</option><option value="Romanian Male">Romanian Male</option><option value="Russian Female">Russian Female</option><option value="Slovak Female">Slovak Female</option><option value="Spanish Female">Spanish Female</option><option value="Spanish Latin American Female">Spanish Latin American Female</option><option value="Swedish Female">Swedish Female</option><option value="Tamil Male">Tamil Male</option><option value="Thai Female">Thai Female</option><option value="Turkish Female">Turkish Female</option><option value="Afrikaans Male">Afrikaans Male</option><option value="Albanian Male">Albanian Male</option><option value="Bosnian Male">Bosnian Male</option><option value="Catalan Male">Catalan Male</option><option value="Croatian Male">Croatian Male</option><option value="Czech Male">Czech Male</option><option value="Danish Male">Danish Male</option><option value="Esperanto Male">Esperanto Male</option><option value="Finnish Male">Finnish Male</option><option value="Greek Male">Greek Male</option><option value="Hungarian Male">Hungarian Male</option><option value="Icelandic Male">Icelandic Male</option><option value="Latin Male">Latin Male</option><option value="Latvian Male">Latvian Male</option><option value="Macedonian Male">Macedonian Male</option><option value="Moldavian Male">Moldavian Male</option><option value="Montenegrin Male">Montenegrin Male</option><option value="Norwegian Male">Norwegian Male</option><option value="Serbian Male">Serbian Male</option><option value="Serbo-Croatian Male">Serbo-Croatian Male</option><option value="Slovak Male">Slovak Male</option><option value="Swahili Male">Swahili Male</option><option value="Swedish Male">Swedish Male</option><option value="Vietnamese Male">Vietnamese Male</option><option value="Welsh Male">Welsh Male</option><option value="US English Male">US English Male</option><option value="Fallback UK Female">Fallback UK Female</option></select>';
		echo '<div class="ttsw_msg"></div>';
		echo '<input type="button" class="play" value="🔊 Play" />';
		echo '<input type="button" class="stop" value="&#9899; Stop" />';
		echo '</div>';
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
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Text To Speech' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
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
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Ttsw_Widget
function register_ttsw_widget() {
    register_widget( 'Ttsw_Widget' );
}
add_action( 'widgets_init', 'register_ttsw_widget' );


function ttsw_scripts() {
    //get some external script that is needed for this script
    wp_register_script('responsivevoice_js', 
					    plugins_url( 'js/responsivevoice.js', __FILE__ ), 
                        array ('jquery' ), 
                        '1.4.7', false);
						
	wp_register_script('voice_js', 
                        plugins_url( 'js/voice.js', __FILE__ ), 
                        array ('jquery', 'responsivevoice_js' ), 
                        '1.0', false);
						
	wp_register_style('voice_css', 
                        plugins_url( 'css/voice.css', __FILE__ ), 
                        false, 
                        '1.0', false);
						
     
}
add_action("wp_enqueue_scripts", "ttsw_scripts");