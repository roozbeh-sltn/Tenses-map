<?php
/**
 * Plugin Name: Translate English Words by Vocabla
 * Plugin URI: http://vocabla.com/
 * Description: It lets your international visitors translate English words using double click.
 * Version: 1.0
 * Author: Kuba Tutaj, Vocabla
 * Author URI: http://vocabla.com/
 * License: GPL2
 */

/*  Copyright 2013  Kuba Tutaj  (email : kuba@vocabla.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function get_domain_name() {
	//Get rid of www
	$domain_name =  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);

	//output the result
	return $domain_name;
}

function get_vocabla_script() {

	//solve partner name by domain name. i.e. changes www.vocabla.com to www_vocabla_com
	$partner = preg_replace('/\./','_',get_domain_name());
	
	$script = "
		<script type='text/javascript'>  
			(function(){window._la_lang='pl';window._la_base_url='http://vocabla.com/';_partner='" .$partner. "';
			var _la_doc=document;var _la_scr=_la_doc.createElement('script');
			_la_scr.setAttribute('src',_la_base_url+'plugin/plugin_page_script.js?x='+(Math.random()));
			_la_scr.setAttribute('type','text/javascript');_la_doc.body.appendChild(_la_scr);})() </script>";
	return $script;
}
function append_script($input) {
   $script = get_vocabla_script();
   echo $script;
}
function append_double_click_info( $content ) {
	if ( is_single() ) {
        $custom_title = '<p class="vocabla_p">New English word? Translate <strong>any word</strong> using double click.</p>';
		$content = $custom_title . $content;
        return $content;
    } else {
        return $content;
    }
}
/**
 * Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
 */
add_action( 'wp_enqueue_scripts', 'prefix_add_my_stylesheet' );

/**
 * Enqueue plugin style-file
 */
function prefix_add_my_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'prefix-style', plugins_url('style.css', __FILE__) );
    wp_enqueue_style( 'prefix-style' );
}

add_filter( 'the_content', 'append_double_click_info' );
add_action('wp_footer', 'append_script');
?>
