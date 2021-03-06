<?php
/*

Plugin Name: Dictionary Box
Plugin URI: http://www.dictionarybox.com
Description: Provides an internal dictionary on the footer of your pages.
Version: 1.0
Author: Ugur Catak
Author URI: http://www.dictionarybox.com
License: GPL2

Copyright 2011, Ugur Catak

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

/* use WP_PLUGIN_URL if version of WP >= 2.6.0. If earlier, use wp_url */
if($wp_version >= '2.6.0') {
	$stimuli_dictionary_box_plugin_prefix = WP_PLUGIN_URL."/dictionary-box/"; /* plugins dir can be anywhere after WP2.6 */
} else {
	$stimuli_dictionary_box_plugin_prefix = get_bloginfo('wpurl')."/wp-content/plugins/dictionary-box/";
}

/* options page (required for saving prefs)*/
$dictionarybox_options_page = get_option('siteurl') . '/wp-admin/admin.php?page=dictionary-box/options.php';
/* Adds our admin options under "Options" */
function dictionary_box_options_page() {
	add_options_page('Dictionary Box Options', 'Dictionary Box', 10, 'dictionary-box/options.php');
}

function dictionary_box_styles() {
	global $wp_version;
	global $stimuli_dictionary_box_plugin_prefix;
    $pl_dbox_styles_prefix = ($stimuli_dictionary_box_plugin_prefix."css/");
	wp_enqueue_style('dictbox', $pl_dbox_styles_prefix.'dictionary-box-style.css', false, '1.0', 'all');  
}

function elvis_enter_the_building() {
    wp_enqueue_script( 'jquery' );
}    

function dictionary_box_scripts(){
	global $wp_version;
	global $stimuli_dictionary_box_plugin_prefix;
	$pl_dbox_jscripts_prefix = ($stimuli_dictionary_box_plugin_prefix."js/");
	
	wp_register_script('pl-dboxjscript', $pl_dbox_jscripts_prefix.'dictionary-box-funcs.js');
    wp_enqueue_script('pl-dboxjscript');
	

}

function dictionary_box_html(){
	/* What version of WP is running? */
	global $wp_version;
	global $stimuli_dictionary_box_plugin_prefix;
	
    /* The next line figures out where the javascripts and images and CSS are installed, relative to your wordpress server's root: */
    $pl_dbox_html_prefix = ($stimuli_dictionary_box_plugin_prefix."js/");
	
	if (!is_admin()) { // if we are *not* viewing an admin page, like writing a post or making a page:
		/* The code needed for footer to work: */
		$dictionary_box_title = get_option("dictionary_box_title");
		$dictionary_box_subheading = get_option("dictionary_box_subheading");
		$dictionary_box_glossary = get_option("dictionary_box_glossary");
		$dictionary_box_margin_right = get_option("dictionary_box_margin_right");
		
		/* storing default values if CSS properties are NULL */
		if(trim($dictionary_box_margin_right) == "")		$dictionary_box_margin_right = "10";
		if(trim($dictionary_box_glossary) == "")	$dictionary_box_glossary = "EnglishToSpanish,SpanishToEnglish";
		if(trim($dictionary_box_title) == "")	$dictionary_box_title = "Dictionary";
		if(trim($dictionary_box_subheading) == "")	$dictionary_box_subheading = "English Spanish Dictionary";
		
		$flag1= substr($dictionary_box_glossary,0,strpos($dictionary_box_glossary,"To"));
		$flag1_style = getSource($flag1);
		$flag1_style = "$flag1_style";
		if (strpos($dictionary_box_glossary,",")){
			$flag2= substr($dictionary_box_glossary, strlen($flag1)+2, strpos($dictionary_box_glossary,",")- strlen($flag1)-2);
			$flag2_style = getSource($flag2);
			$flag2_style = "$flag2_style";}
		else{
			$flag2_style= "";
		}	
			
		
		$pl_dbox_html = "
		<!-- begin dictionary box -->
		<div id=\"pl-dbox\" style=\"right:$dictionary_box_margin_right"."px\">
		<a class=\"pl-dbox-title\" href=\"javascript:void(0);dboxCursorLoc();\"><span id=\"pl-dbox-title-text\">$dictionary_box_title</span></a>
		<div id=\"pl-dbox-content\">
			<div id=\"pl-dboxFlags\"><ul><li>$flag1_style</li>
			<li>$flag2_style</li><li><h3>$dictionary_box_subheading</h3></li></ul></div>
			<div id=\"pl-dbox-ajax-content\">
			<p class=\"info\">Double click on any word on the page or type a word:</p>
			<p><input type=\"text\" name=\"pl-dbox-search-field\" style=\"width:97%;\" value=\"\" id=\"pl-dbox-search-field\" onKeyPress=\"return dbxChkKy(event);\" autocomplete=off /></p>
			<p id=\"pl-dbox-search-button\"><input type=\"button\" value=\" Search! \" OnClick=\"javascript:getdboxResults();\"/></p>
			</div>
			<input id=\"pl-dbox-glossary\" type=\"hidden\" value=\"$dictionary_box_glossary\"/>
			<div id=\"pl-dbox-credit\">Powered by DictionaryBox.com</div>
		</div>
		</div>
		<!-- end dictionary box -->\n";
 
		echo($pl_dbox_html);
	}
}

function getSource($flag){
if($flag=="Turkish") $source="<img src=\"http://dictionarybox.com/flags/".$flag.".png\" alt=\"sözlük\" />";
elseif($flag=="Spanish") $source="<img src=\"http://dictionarybox.com/flags/".$flag.".png\" alt=\"diccionario\" />";
elseif($flag=="Russian") $source="<img src=\"http://dictionarybox.com/flags/".$flag.".png\" alt=\"словарь\" />";
elseif($flag=="French") $source="<img src=\"http://dictionarybox.com/flags/".$flag.".png\" alt=\"dictionnaire\" />";
elseif($flag=="Italian") $source="<img src=\"http://dictionarybox.com/flags/".$flag.".png\" alt=\"dizionario\" />";
elseif($flag=="German") $source="<img src=\"http://dictionarybox.com/flags/".$flag.".png\" alt=\"wörterbuch\" />";
elseif($flag=="Portuguese") $source="<img src=\"http://dictionarybox.com/flags/".$flag.".png\" alt=\"dicionário\" />";
else $source="<img src=\"http://dictionarybox.com/flags/".$flag.".png\" alt=\"dictionary\" />";

return $source;
}

/* START CODE installing the plugin and adding the plugin options */
function dictionary_box_install()
{ 
	add_option('dictionary_box_title', 'Dictionary');
	add_option('dictionary_box_subheading', 'English Spanish Dictionary');
	add_option('dictionary_box_glossary', 'EnglishToSpanish,SpanishToEnglish');
	add_option('dictionary_box_margin_right', '10');

}
add_action('activate_dictionary-box/dictionary-box.php', 'dictionary_box_install');
/* END CODE installing the plugin and adding the plugin options */

/* START CODE uninstalling the plugin and deleting the plugin options */
function dictionary_box_uninstall()
{ 
	delete_option('dictionary_box_title');
	delete_option('dictionary_box_subheading');
	delete_option('dictionary_box_glossary');
	delete_option('dictionary_box_margin_right');

}
// Add settings link on plugin page
function your_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=dictionary-box/options.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
add_action('deactivate_dictionary-box/dictionary-box.php', 'dictionary_box_uninstall');
/* END CODE uninstalling the plugin and deleting the plugin options */

/* we want to add the above xhtml to the header of our pages: */
add_action('get_header', 'dictionary_box_styles'); 
add_action('wp_enqueue_scripts', 'elvis_enter_the_building');
add_action('wp_enqueue_scripts', 'dictionary_box_scripts');
add_action('admin_menu', 'dictionary_box_options_page');
add_action('wp_footer', 'dictionary_box_html');

/* we want to add settings link to the installed plugins page: */
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );

?>