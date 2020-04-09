<?php
  namespace WLClickToTranslate;

  Class Dashboard{
    private static $_instance;
    private $options;

    public static function getInstance()
    {
      if(!self::$_instance){
        self::$_instance = new Dashboard();
      }
      return self::$_instance;
    }

    function __construct() {
      add_action( 'admin_menu', array($this, "init") );
    }

    function init(){
      $this->registerSettingLinks();
      $this->registerAdminMenu();
    }

    function registerSettingLinks(){
      $plugin = WL_CLICK_TO_TRANSLATE_PLUGIN;
      add_filter("plugin_action_links_$plugin", array($this, 'plugin_settings_link'));
    }

    function registerAdminMenu() {
        $this->url = add_menu_page(
            __( 'Click to Translate', 'wl-click-to-translate' ),
            __( 'Click to Translate', 'wl-click-to-translate' ),
            WL_CLICK_TO_TRANSLATE_CAPABILITY,
            WL_CLICK_TO_TRANSLATE_MENU_SLUG,
            array($this, "settingsIndex"),
            '',
            79
        );
    }

    function plugin_settings_link($links){
      $url = admin_url("admin.php?page=" . WL_CLICK_TO_TRANSLATE_MENU_SLUG);
      $esc_url = esc_url($url);
      $settings_link = "<a href='$esc_url'>" . __('Settings', 'wl-click-to-translate') . '</a>';
      array_unshift($links, $settings_link);
      return $links;
    }

    function postHandler(){
      if($this->shouldCleanCache($_POST)){
        $this->options->refreshRandomNumber();
        $this->options->save();
        return;
      }

      $validatedData = $this->validatedPostData($_POST);
      $this->options->saveValuesByArray($validatedData);
    }

    function settingsIndex(){
      if (!current_user_can(WL_CLICK_TO_TRANSLATE_CAPABILITY)) return;

      $this->options = Option::getInstance();

      if ( $this->isSubmit($_POST)
          && check_admin_referer('wl_click_to_translate_verify', 'wl_click_to_translate_nonce')
      ) {
        $this->postHandler();
      }

      $accessToken = $this->options->getAccessToken();
      $autoload = $this->options->getAutoload();
      $enable= $this->options->getEnable();
      $addButtonOnContentHead = $this->options->getAddButtonOnContentHead();
      $enabledTypes = $this->options->getEnabledTypes();
      $enabledFromLanguages = $this->options->getEnabledFromLanguages();
      $possiblePostTypes = $this->options->possiblePostTypes();
      $possibleFromLanguages = $this->options->possibleFromLanguages();
      $websiteUrl = "https://www.woodpeckerlearning.com";
      require_once(WL_CLICK_TO_TRANSLATE_PLUGIN_DIR."view/"."index.php");
    }

    function isSubmit($post){
      return !empty($post) && isset($post['submit']);
    }

    function validatedPostData($post){
      return [
        "accessToken" => $this->getValidatedAccessToken($post["accessToken"]),
        "autoload" => Validitor::isOn($post["autoload"]),
        "enable" => Validitor::isOn($post["enable"]),
        "autoload" => Validitor::isOn($post["autoload"]),
        "addButtonOnContentHead" => Validitor::isOn($post["addButtonOnContentHead"]),
        "enabledTypes" => $this->getValidatedPostTypes($post["enabledTypes"]),
        "enabledFromLanguages" => $this->getValidatedFromLanguages($post["enabledFromLanguages"]),
      ];
    }

    function shouldCleanCache($post){
      return $post["clearCache"] == "1";
    }

    function getValidatedAccessToken($data){
      $data = sanitize_text_field($data);
      if(!$this->isAccessToken($data)){
        $data = $this->options->getAccessToken();
        if (!$this->isAccessToken($data)){
          $data = WL_CLICK_TO_TRANSLATE_DEFAULT_ACCESS_TOKEN;
        }
      }
      return $data;
    }

    function isAccessToken($str){
      return preg_match("/^[a-z0-9]{40}$/", $str);
    }

    function getValidatedPostTypes($data){
      $validatedData = array();
      $possiblePostTypes = $this->options->possiblePostTypes();
      if (is_array($data) && !$this->is_assoc($data)){
        foreach ($data as $value) {
          if (in_array($value, $possiblePostTypes)){
            array_push($validatedData, $value);
          }
        }
      }
      return $validatedData;
    }

    function getValidatedFromLanguages($data){
      $validatedData = array();
      $possibleFromLanguages = $this->options->possibleFromLanguages();
      if (is_array($data) && !$this->is_assoc($data)){
        foreach ($data as $value) {
          if (isset($possibleFromLanguages[$value])){
            array_push($validatedData, $value);
          }
        }
      }
      return $validatedData;
    }

    function is_assoc($array) {
        return (array_values($array) !== $array);
    }
  }
