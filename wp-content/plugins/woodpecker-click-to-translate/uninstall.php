<?php
namespace WLClickToTranslate;

require_once("controller/option.php");

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

Class Uninstall{
  function __construct() {
    // $this->action();
  }

  function action(){
		$options = Option::getInstance();
    $options->cleanUp();
  }
}

$var = new Uninstall();
?>
