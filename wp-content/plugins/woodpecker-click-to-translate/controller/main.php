<?php
  namespace WLClickToTranslate;

  Class Main{
    private $restriction;
    private $options;
    private $shortCode;
    private $cdnVersion = "v1.7";

    function __construct() {
      $this->restriction = Restriction::getInstance();
      $this->options = Option::getInstance();
      $this->shortCode = ShortCode::getInstance();

      add_action( 'wp', array( $this, 'insertScriptIfneeded' ) );
      if( is_admin() ) {
        $this->dashboard = Dashboard::getInstance();
        $this->formInPost = Form_In_Single_Editor::getInstance();
      }else{
        add_filter( 'dynamic_sidebar_params', array( $this, 'filterWLbuttonWidget'));
      }
      add_action( 'widgets_init', array( $this, 'register_wlbutton_widget'));
    }

    function filterWLbuttonWidget($params){
      if (isSet($params) && isSet($params[0]) && isSet($params[0]["widget_id"])){
        $widget_id = $params[0]["widget_id"];
        if (strpos($widget_id, 'wlbutton_widget') !== false && !$this->restriction->isEnabled(get_post_type())){
          return nil;
        }
      }
      return $params;
    }

    function insertScriptIfneeded(){
      if ($this->restriction->isEnabled(get_post_type()) 
          && !$this->options->isDisabledInSingle(get_post_type(), get_the_ID())
      ){
        if ($this->options->getAutoload()){
          add_action( 'wp_enqueue_scripts',array( $this, 'addJs'));
          add_filter( 'script_loader_tag', array( $this, 'addJSOptions'), 10, 3);
          add_action( 'wp_enqueue_scripts', array( $this, 'addCss'));
        }
  
        if ($this->options->getAddButtonOnContentHead()){
          add_filter( 'the_content', array($this, "addButtonContainer"));
        }
      }
    }

    // register Foo_Widget widget
    function register_wlbutton_widget() {
      register_widget('WLClickToTranslate\WLButton_Widget' );
    }

    function domain(){
      // return "http://127.0.0.1:8000";
      return WL_CLICK_TO_TRANSLATE_PLUGIN_URL;
    }

    function addJs(){
      wp_enqueue_script( 'WLClickToTranslate.js',
                        $this->domain()."assets/{$this->cdnVersion}/js/WLlookup.js",
                        array(),
                        $this->options->getRandomNumber(),
                        true);
    }

    function addJSOptions($tag, $handle, $src){
      if ( 'WLClickToTranslate.js' === $handle ){
        $class = "wlinit";
        $token = $this->options->getAccessToken();
        $autoload = $this->options->getAutoload() ? "1" : "0";
        $enable = ($this->options->getEnable() && $autoload) ? "1" : "0";
        $fromLanguages = implode(',', $this->options->getEnabledFromLanguages());
        $esc_url = esc_url( $src );
        $tag = "<script type='text/javascript' class='{$class}' src='{$esc_url}' data-renderButton='1' data-token='{$token}' data-autoload='{$autoload}' data-enable='{$enable}' data-fromLanguages='{$fromLanguages}'></script>";
      }

      return $tag;
    }

    function addCss(){
      wp_enqueue_style( 'WLClickToTranslate.css',
                       $this->domain()."assets/{$this->cdnVersion}/css/WLlookup.css",
                       array(),
                       $this->options->getRandomNumber()
                      );
    }

    function addButtonContainer($content) {
      $appendElement = $this->renderButtonContainer();
      return $appendElement.$content;
    }

    function renderButtonContainer(){
      return "<div class='wlapi_toggle_button_container'></div>";
    }
  }
