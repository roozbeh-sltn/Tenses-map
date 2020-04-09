<?php
  namespace WLClickToTranslate;

  Class Restriction{
    private static $_instance;

    private $options;
    private $enabledTypes;

    public static function getInstance()
    {
      if(!self::$_instance){
        self::$_instance = new Restriction();
      }
      return self::$_instance;
    }

    function __construct() {
      $this->options = Option::getInstance();
      $this->enabledTypes = $this->options->getEnabledTypes();
    }

    function isEnabled($type){
      return is_singular() && $this->isPostTypeEnabled($type);
    }

    function isPostTypeEnabled($type){
      return in_array($type, $this->enabledTypes);
    }
  }
