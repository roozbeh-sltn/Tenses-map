<?php 
/*
Plugin Name: Double Click Dictionary Look-up
Description: Subscribers can easily look up a word's definition by double clicking on a word and clicking the qtip that appears.
Version: 0.2
Author: Ivan Tse
Plugin URI: http://code.tseivan.com/dbl-click-dictionary-look-up/
Author URI: http://tseivan.com
License: GPLv2 or later

Copyright 2011 Ivan Tse (email : ivan.tse1@gmail.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

add_filter( 'the_content', 'dblclick_enable_look_up' );
add_filter( 'comment_text', 'dblclick_enable_look_up' );
add_action('admin_menu', 'dblclick_create_submenu');
add_action('admin_init', 'dblclick_admin_init');
register_activation_hook( __FILE__, 'dblclick_activate' );
define( 'DBLCLICK_JS', plugin_dir_url( __FILE__ ).'js/' );
define( 'DBLCLICK_CSS', plugin_dir_url( __FILE__ ).'css/' );
wp_enqueue_script( 'dblclick_text_selection', DBLCLICK_JS.'text-selection.js', array('jquery'), '', true );
wp_enqueue_style( 'dblclick_qtip', DBLCLICK_CSS.'qTip.css' );
//$params = array('text' => '?');
$params = get_option( 'dblclick_options' );
wp_localize_script( 'dblclick_text_selection', 'dblclick_params', $params );
function dblclick_activate(){
	if ( !get_option( 'dblclick_options' ) ){
		$defaults = array(
			'text' => '?' //,
//			'border_color' => 'd6d6d6'
		);
		add_option( 'dblclick_options', $defaults );
	}
}
function dblclick_enable_look_up( $content ){
	return '<div class="dblclick_enableLookUp">'.$content.'</div>';
}

function dblclick_create_submenu(){
	add_options_page('Double Click Dictionary Look-up Settings', 'Dbl Click Dict Settings', 'manage_options', 'dblclick_settings', 'dblclick_settings_page');
}

function dblclick_settings_page(){
?>
	<div class="wrap">
		<?php screen_icon( 'plugins' ); ?>
		<h2>Double Click Dictionary Look-up Settings</h2>
		<form action="options.php" method="post">
			<?php settings_fields('dblclick_options');
			do_settings_sections('dblclick_settings'); ?>
			<br />
			<input name="submit" type="submit" value="Save Changes" class="button-primary" />
		</form>
	</div>

<?php
}

function dblclick_admin_init(){
	register_setting( 'dblclick_options', 'dblclick_options', 'dblclick_validate_options' );
	add_settings_section( 'dblclick_settings_general', 'General (Look and Feel)', 'dblclick_settings_general_text', 'dblclick_settings' );
	add_settings_field( 'dblclick_text', 'qTip Text', 'dblclick_text_input', 'dblclick_settings', 'dblclick_settings_general');
//	add_settings_field( 'dlbclick_border_color', 'Border Color', 'dblclick_border_color_input', 'dblclick_settings', 'dblclick_settings_general');
}
function dblclick_settings_general_text(){
	echo 'These options enable you to adjust the look of the qTip that appears above the word.';
}
function dblclick_text_input(){
	$options = get_option( 'dblclick_options' );
	$text = $options['text'];
	echo "<input id='dblclick_text' type='text' name='dblclick_options[text]' value='$text' />";
}
// function dblclick_border_color_input(){
	// $options = get_option( 'dblclick_options' );
	// $color = $options['border_color'];
	// echo "<input id='dblclick_text' type='text' name='dblclick_options[border_color]' value='$color' />";
// }
function dblclick_validate_options( $input ){
	$valid = array();
	$valid['text'] = $input['text'];
//	$valid['border_color'] = $input['border_color'];
	return $valid;
}
function dblclick_uninstall() {
	delete_option('dblclick_options'); 
}

register_uninstall_hook(__FILE__, 'dblclick_uninstall'); 

?>