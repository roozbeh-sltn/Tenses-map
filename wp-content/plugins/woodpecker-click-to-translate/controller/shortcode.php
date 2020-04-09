<?php

namespace WLClickToTranslate;

Class ShortCode {
  private static $_instance;

  public static function getInstance()
  {
    if(!self::$_instance){
      self::$_instance = new ShortCode();
    }
    return self::$_instance;
  }

  function __construct() {
    $this->registerShortCode();
  }

  private function registerShortCode(){
    add_shortcode('wl-lookup', array($this, 'renderButton'));
  }

  function renderButton( $attributes ){
    foreach ($attributes as $value) {
      if ($value == "button"){
        return "<div class='wlapi_toggle_button_container'></div>";
      }
    }
  }
}
