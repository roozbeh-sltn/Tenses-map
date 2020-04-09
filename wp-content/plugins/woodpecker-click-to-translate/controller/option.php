<?php

namespace WLClickToTranslate;

define( 'WL_CLICK_TO_TRANSLATE_OPTION_KEY', "wlClickToTranslateOptionKeyV1.0" );

Class Option{

  // static

  private static $_instance;

  public static function getInstance()
  {
    if(!self::$_instance){
      self::$_instance = new Option();
    }
    return self::$_instance;
  }

  // instance

  private $defaultValueTypes;
  private $optionValues;

  function __construct() {
    $this->fromLanguages = [
      "en" => [
        "name" => "English"
      ],
      "es" => [
        "name" => "Español"
      ],
      "fr" => [
        "name" => "Français"
      ],
      "vi" => [
        "name" => "Tiếng Việt"
      ],
      "zh_CN" => [
        "name" => "简体中文"
      ],
      "zh_TW" => [
        "name" => "繁體中文"
      ],
    ];

    $this->fetch();
  }

  function fetch(){

    $this->optionValues = get_option( WL_CLICK_TO_TRANSLATE_OPTION_KEY );

    if (!$this->optionValues){
      $this->optionValues = $this->getDefaultValue();
      $this->save();
    }

    if ($this->getVersion() != WL_CLICK_TO_TRANSLATE_VERSION){
      $this->syncToNewestValues();
      $this->setVersion(WL_CLICK_TO_TRANSLATE_VERSION);
      $this->refreshRandomNumber();
    }
  }

  public function saveValuesByArray($dataArray){
    $this->setAccessToken($dataArray["accessToken"]);
    $this->setAutoload($dataArray["autoload"]);
    $this->setEnable($dataArray["enable"]);
    $this->setAddButtonOnContentHead($dataArray["addButtonOnContentHead"]);
    $this->setEnabledTypes($dataArray["enabledTypes"]);
    $this->setEnabledFromLanguages($dataArray["enabledFromLanguages"]);
    $this->save();
  }

  public function getVersion(){
    return $this->getValue("version");
  }

  public function setVersion($version){
    return $this->setValue("version", $version);
  }

  public function getRandomNumber(){
    return $this->getValue("randomNumber");
  }

  public function setRandomNumber($randomNumber){
    return $this->setValue("randomNumber", $randomNumber);
  }

  public function refreshRandomNumber(){
    $this->setRandomNumber($this->createRandomNumber());
    $this->save();
  }

  public function getAccessToken(){
    return $this->getValue("accessToken");
  }

  public function setAccessToken($accessToken){
    return $this->setValue("accessToken", $accessToken);
  }

  public function getAutoload(){
    return $this->getValue("autoload");
  }

  public function setAutoload($autoload){
    return $this->setValue("autoload", $autoload);
  }

  public function getEnable(){
    return $this->getValue("enable");
  }

  public function setEnable($enable){
    return $this->setValue("enable", $enable);
  }

  public function getAddButtonOnContentHead(){
    return $this->getValue("addButtonOnContentHead");
  }

  public function setAddButtonOnContentHead($addButtonOnContentHead){
    return $this->setValue("addButtonOnContentHead", $addButtonOnContentHead);
  }

  public function getEnabledTypes(){
    return $this->getValue("enabledTypes");
  }

  public function setEnabledTypes($enabledTypes){
    return $this->setValue("enabledTypes", $enabledTypes);
  }

  public function getEnabledFromLanguages(){
    return $this->getValue("enabledFromLanguages");
  }

  public function setEnabledFromLanguages($enabledFromLanguages){
    return $this->setValue("enabledFromLanguages", $enabledFromLanguages);
  }

  public function isDisabledInSingle($post_type, $post_id){
    $disabledSingleList = $this->getDisabledSingleList();
    $key = "{$post_type}_{$post_id}";
    if (isset($disabledSingleList[$key])){
      return true;
    }
    return false;
  }

  public function getDisabledSingleList(){
    return $this->getValue("disabledSingleList");
  }

  public function setDisabledSingleList($post_type, $post_id, $value){
    $disabledSingleList = $this->getDisabledSingleList();
    $key = "{$post_type}_{$post_id}";
    if(!$value && isset($disabledSingleList[$key])){
      unset($disabledSingleList[$key]);
    }else if($value){
      $disabledSingleList[$key] = $value;
    }
    return $this->setValue("disabledSingleList", $disabledSingleList, true);
  }

  private function getValue($key){
    if ($key){
      return $this->optionValues[$key];
    }
    return null;
  }

  private function setValue($key, $value, $autoSave = false){
    if($this->validation($key, $value)){
      $this->optionValues[$key] = $value;
    }

    if ($autoSave){
      $this->save();
    }
  }

  function save(){
    update_option(WL_CLICK_TO_TRANSLATE_OPTION_KEY, $this->optionValues, true);
  }

  function cleanUp(){
    delete_option(WL_CLICK_TO_TRANSLATE_OPTION_KEY);

    // for site options in Multisite
    delete_site_option(WL_CLICK_TO_TRANSLATE_OPTION_KEY);
  }

  private function validation($key, $value){

    if(!is_string($key) || $value === null){
      return false;
    }
    return ($this->valueTypes()[$key] == gettype($value));
  }

  private function createRandomNumber(){
    return substr(md5(microtime()), rand(0,26),5);
  }

  private function syncToNewestValues(){
    $this->optionValues = array_merge($this->getDefaultValue(), $this->optionValues);
  }

  private function getDefaultValue(){
    return  [
      "version" => WL_CLICK_TO_TRANSLATE_VERSION,
      "randomNumber" => $this->createRandomNumber(),
      "accessToken" => WL_CLICK_TO_TRANSLATE_DEFAULT_ACCESS_TOKEN,
      "autoload" => true,
      "enable" => false,
      "addButtonOnContentHead" => false,
      "enabledTypes" => [
        "post"
      ],
      "enabledFromLanguages" => array_keys($this->fromLanguages),
      "disabledSingleList" => []
    ];
  }

  private function valueTypes(){
    if (!$this->defaultValueTypes){
      $this->defaultValueTypes = [
        "version" => gettype(WL_CLICK_TO_TRANSLATE_VERSION) ,
        "randomNumber" => gettype(""),
        "accessToken" => gettype(""),
        "autoload" => gettype(true),
        "enable" => gettype(true),
        "addButtonOnContentHead" => gettype(true),
        "enabledTypes" => gettype([]),
        "enabledFromLanguages" => gettype([]),
        "disabledSingleList" => gettype([]),
      ];
    }
    return $this->defaultValueTypes;
  }

  function possiblePostTypes(){
    return array_diff(get_post_types(),
    ["elementor_library", "amn_smtp", "attachment", "revision", "nav_menu_item", "custom_css", "customize_changeset", "oembed_cache", "user_request"]);
  }

  function possibleFromLanguages(){
    return $this->fromLanguages;
  }
}
