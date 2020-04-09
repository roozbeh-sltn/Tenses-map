<?php
/*
 * Plugin Name: Woodpecker - Click to Translate
 * Plugin URI: https://www.woodpeckerlearning.com/en/
 * Description: A bilingual dictionary lookup tool for every website.
 * Version: 1.6
 * Author: Woodpecker Learning
 * Author URI: https://www.woodpeckerlearning.com/en/
 * Text Domain: wl_click_to_translate
 * License: GPL2
 * Domain Path: /languages/
*/


namespace WLClickToTranslate;


define('WL_CLICK_TO_TRANSLATE_PLUGIN', plugin_basename( __FILE__ ) );
define('WL_CLICK_TO_TRANSLATE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define('WL_CLICK_TO_TRANSLATE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define('WL_CLICK_TO_TRANSLATE_MENU_SLUG', "WL_CLICK_TO_TRANSLATE_SETTINGS");
define('WL_CLICK_TO_TRANSLATE_VERSION', 1.6 );
define('WL_CLICK_TO_TRANSLATE_CAPABILITY',  'update_plugins');
define('WL_CLICK_TO_TRANSLATE_DEFAULT_ACCESS_TOKEN',  'fill in access token!!');


Class WLClickToTranslate{
  private $classesDir = array (
    "controller",
    "utils",
    "widget"
  );

  function __construct() {
    $this->__autoload();
    add_action( 'plugins_loaded', array( $this, 'init_textdomain' ) );
    $main = new Main();
  }

  function __autoload() {
    foreach ($this->classesDir as $directory) {
      foreach (glob(WL_CLICK_TO_TRANSLATE_PLUGIN_DIR . $directory . "/*.php") as $filename)
      {
        if (file_exists($filename)) {
          require_once($filename);
        }
      }
    }
  }

  function init_textdomain() {
    $plugin_rel_path = WL_CLICK_TO_TRANSLATE_PLUGIN_DIR . 'languages';
    load_plugin_textdomain( 'wl_click_to_translate', false, $plugin_rel_path );
  }
}


$var = new WLClickToTranslate();
