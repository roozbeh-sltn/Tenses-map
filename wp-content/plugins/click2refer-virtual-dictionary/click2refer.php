<?php
/*
Plugin Name: Click2Refer Virtual Dictionary
Plugin URI: http://click2refer.zingersaga.net/plugin
Description: Click2Refer helps blog readers to refer Wordnet dictionary for the words double clicked. 
Version: 1.4.1
Author: Dinesh Babu
Author URI: http://www.zingersaga.net
License: GPL
*/


/*  Copyright 2010  Dinesh Babu   (email : click2refer@gmail.com)

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

/* Initializing action starts. */

//delete_option('bkt_info_text'); 
//delete_option('bkt_font_color');
//delete_option('bkt_background');

//delete_option('bkt_showonpage');

add_action('admin_menu', 'bkt_create_menu');
add_action( 'admin_init', 'register_mysettings' ); 
add_action('the_content','init_bkt_appln');

// default values for the variables follows

$bkt_default_info_text ='You can double click on any word to get its meaning';
$bkt_default_show_option = 'top'; // either to display message bar on top or bottom 
$bkt_default_align_option = 'left'; // either align to left or right 
$bkt_default_dict_info = 'wn'; // default is wordnet dictionary 
$bkt_default_showonpage = 1;
$bkt_default_dictionary_mode=1;  // 1 - Google appengine 2 - DICT Server mode 
 
if(get_option('bkt_showonpage')==false) // it's run only once after installation to create the options in database
{
   add_option("bkt_dict_info", $bkt_default_dict_info, '', 'yes');
   add_option("bkt_info_text", $bkt_default_info_text, '', 'yes');   
   add_option("bkt_show_option", $bkt_default_show_option, '', 'yes');
   add_option("bkt_align_option", $bkt_default_align_option, '', 'yes');
   add_option("bkt_showonpage", $bkt_default_showonpage,'','yes');
   //add_option("bkt_dictionary_mode", $bkt_default_dictionary_mode,'','yes');
}  

// Dashboard hooks 

function click2refer_counters() {  ?>

<Br>
<div class='dashboard-widget' style='width: 250px; height: 75px;'>
<h3 class='dashboard-widget-title'><?php _e('Click2Refer Counter'); ?></h3><br>
<div class='dashboard-widget-content' >

<form name="bkt_hits_form">
Number of references so far,  <input type="text" name="counts" size="6" readonly="readonly">
<script src="http://click2refer-wordnet.appspot.com/ViewHits?baseurl=<?php echo bloginfo('wpurl');?>">
</script>
</form>
 </div></div>

<?php  }

add_action('activity_box_end', 'click2refer_counters');

/* Initializing action ends. */

/* Uninstall hook starts  */

function bkt_uninstall() {
 delete_option('bkt_info_text'); 
 delete_option('bkt_show_option');
 delete_option('bkt_align_option');
 delete_option('bkt_showonpage');
 delete_option('bkt_dict_info');
}

register_uninstall_hook(__FILE__, 'bkt_uninstall'); 

/* Uninstall hook ends */


function init_bkt_appln($content) {

if(is_single()!=1 && get_option("bkt_showonpage")!=true )  {
 echo $content;
 return ;
}

if( get_option("bkt_show_option") == 'bottom') echo $content;

 ?>

 <!-- Code for Click2Refer Script begins -->

<div align="<?php echo get_option('bkt_align_option'); ?>">
<span id='click2refer_script' ></span>
 
</div>

<!-- **** The settings values transferred to the core files (Start) **** -->

<script language='javascript'> 
var server_info = "<?php echo bloginfo('wpurl'); ?>"; 
var dict_info = "<?php echo get_option('bkt_dict_info'); ?>";
var bkt_info_text ="<?php echo get_option('bkt_info_text'); ?>";
</script>

<script language="javascript">

//"<?php echo (get_option('bkt_dictionary_mode')==1?"http://click2refer-wordnet.appspot.com/GetWord":"http://click2refer.zingersaga.net/loadword3.aspx")?>";

var bkt_get_word ="http://click2refer-wordnet.appspot.com/GetWord";

</script>

<!-- **** The settings values transferred to the core files (End) **** -->

<script language='javascript' src='<?php echo bloginfo('wpurl');?>/wp-content/plugins/click2refer-virtual-dictionary/js/click2refer_plugin.js'></script>

<!-- Code for Click2Refer Script ends --><br>

<?php
if( get_option("bkt_show_option") == 'top') echo $content;
}

function bkt_create_menu() {	// for creating custom plugin settings menu

add_options_page('BKT Settings', 'Click2Refer', 'administrator', 'bkt-settings', 'bkt_settings_page');

}

function register_mysettings() { 	 
	register_setting( 'bkt-settings-group', 'bkt_info_text','bkt_call_back1');    
        register_setting( 'bkt-settings-group', 'bkt_align_option','bkt_call_back2');
        register_setting( 'bkt-settings-group', 'bkt_showonpage','bkt_call_back3');
	register_setting( 'bkt-settings-group', 'bkt_show_option','bkt_call_back4');
	register_setting( 'bkt-settings-group', 'bkt_dict_info','bkt_call_back5');
        register_setting( 'bkt-settings-group', 'bkt_dictionary_mode','bkt_call_back6');
}

// validation call back functions follow 

function bkt_call_back1($input) { 
  if( strlen( $input) == 0 )
   $input = "You can double click on any word to get its meaning";
 
  return $input;
}

function bkt_call_back2($input) { 
   return  $input;
}

function bkt_call_back3($input) { 
  if(strlen( $input) ==0) 
   return 0;
  else
   return 1;
}

function bkt_call_back4($input) { 
   return  $input;
}

function bkt_call_back5($input) { 
   return  $input;
}

function bkt_call_back6($input) { 
   return  $input;
}
 
function bkt_settings_page() {
?>

<!-- Click2Refer settings page works -->

<div class="wrap">
  
<h2>Click2Refer Virtual Dictionary - Settings </h2>

<form method="post" action="options.php" name="settingsform">
    <?php settings_fields( 'bkt-settings-group' ); ?>

<br>

<table border=1 style="background-color:#CCE6FF;" cellspacing=10>

<tr valign="top">
<td colspan="2" align="left"> <img src='<?php echo bloginfo('wpurl');?>/wp-content/plugins/click2refer-virtual-dictionary/images/click2refer.jpg'> 
</td>
</tr>

<tr valign="top" >

<td width=300><b>The following text displayed when someone click the above image</b></td>
<td><input type="text" name="bkt_info_text" id="msgtext" value="<?php echo get_option('bkt_info_text'); ?>" size="65" maxlength="60" /><br>
<i> (Maximum 60 characters) </i>
<Br>
</td>
</tr>
            
<tr valign="top">
<td><b>Display info image</b> </td>
<td><input type="radio" name="bkt_show_option" value="top" <?php echo (get_option('bkt_show_option')=='top')?'CHECKED':''; ?> /> Before Content
        <input type="radio" name="bkt_show_option" value="bottom" <?php echo (get_option('bkt_show_option')=='bottom')? 'CHECKED':''; ?> /> After Content 
<Br>
 </td>
        </tr>

<tr valign="top">
<td><b>Align info image to </b></td>
 <td><input type="radio" name="bkt_align_option" value="left" <?php echo (get_option('bkt_align_option')=='left')?'CHECKED':''; ?> /> Left
        <input type="radio" name="bkt_align_option" value="right" <?php echo (get_option('bkt_align_option')=='right')? 'CHECKED':''; ?> /> Right

<Br> </td>
        </tr>

<tr valign="top">
<td> <?php get_option("bkt_showonpage");?>  </td>

<td><input type="checkbox" name="bkt_showonpage" value="1" <?php echo (get_option('bkt_showonpage')==1)?'CHECKED':''; ?> /> Enable Click2Refer on Wordpress Pages too.

</tr>

</table> <br> <Br>

<script language="javascript">
document.getElementById('bkt_dict_info').value = '<?php echo get_option('bkt_dict_info'); ?>'; 
</script>

</td>
 
</tr>    
</table>
  
 <p class="submit">
 <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
 </p>

</form>

<br> <i> Please help us in improving Click2Refer by <a href="http://blog.zingersaga.net/?page_id=156"> leaving a feedback. </a>  </i>
</div>
<?php } ?>