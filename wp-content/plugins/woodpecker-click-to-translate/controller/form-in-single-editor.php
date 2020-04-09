<?php

namespace WLClickToTranslate;

class Form_In_Single_Editor
{
  private static $_instance;
  private $options;

  private $nonce_private_key_name = "metabox_save";
  private $nonce_public_key_name = "wp-ctc-disable-by-single-nonce";

  public static function getInstance()
  {
    if (!self::$_instance) {
      self::$_instance = new Form_In_Single_Editor();
    }
    return self::$_instance;
  }

  function __construct()
  {
    $this->options = Option::getInstance();

    add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
    add_action('save_post', array($this, 'save_post'), 10,  2);
  }

  function add_meta_boxes($post_type, $post){
    if (in_array($post_type, $this->options->getEnabledTypes())){
      $id = "wp-ctc-disable-by-post";
      $title = "Click to Translate.";
      $context = "side";
      $priority = 'default';
      add_meta_box( 
        $id, 
        $title, 
        array($this, 'render_meta_box'), 
        $post_type, 
        $context, 
        $priority
      );
    }
  }

  function render_meta_box($post, $metabox){
    $disabled = $this->options->isDisabledInSingle($post->post_type, $post->ID);
    wp_nonce_field($this->nonce_private_key_name, $this->nonce_public_key_name );
    require_once(WL_CLICK_TO_TRANSLATE_PLUGIN_DIR."view/"."meta-box-disable-in-single.php");  
  }

  function save_post($post_id, $post){
    if (!in_array($post->post_type, $this->options->getEnabledTypes())){
      return;
    }

    $nonce_value   = isset( $_POST['wp-ctc-disable-by-single-nonce'] ) ? $_POST['wp-ctc-disable-by-single-nonce'] : '';

    // Check if nonce is valid.
    if (!wp_verify_nonce(
          $nonce_value, 
          $this->nonce_private_key_name
        )) {
        return;
    }

    // Check if user has permissions to save data.
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }
    
    $this->options->setDisabledSingleList(
      $post->post_type, 
      $post_id,
      Validitor::isOn($_POST["wp-ctc-disable-of-single"])
    );
  }
}
