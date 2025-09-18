<?php

if (!class_exists('DzsCommonGutenbergBlockFactory')) {
  /**
   * version v1.00
   * init with
   * array(
   * 'gutenbergBlockName' => 'dzswtl_filters_block',
   * 'gutenbergBlockNameJs' => 'dzswtl/filters_block',
   * 'blockJsUrl' => 'http://something/wpfactory/wp-content/plugins/dzs-wootable/inc/js-gutenberg/filters-block/filters-block.js',
   * 'blockShortcode' => 'dzs_woo_filter_gutenberg',
   * 'actualShortcode' => 'dzs_woo_filter',
   * 'blockOptions' => include(DZSWTL_PATH.'configs/config-filter-options.php'),
   * )
   * Class DzsGutenbergBlockFactory
   */
  class DzsCommonGutenbergBlockFactory {
    public $gutenbergBlockName;
    public $blockJsUrl;
    public $blockShortcode;
    public $gutenbergBlockNameJs;
    /**
     * @var mixed|string generated from json
     */
    public $blockOptions;
    public $actualShortcode;


    /**
     * DzsGutenbergBlockFactory constructor
     * init with
     * array(<br>
     *   'gutenbergBlockName' => 'dzswtl_filters_block',<br>
     *   'gutenbergBlockNameJs' => 'dzswtl/filters_block',<br>
     *   'blockJsUrl' => 'http://something/wpfactory/wp-content/plugins/dzs-wootable/inc/js-gutenberg/filters-block/filters-block.js',<br>
     *   'blockShortcode' => 'dzs_woo_filter_gutenberg',<br>
     *   'actualShortcode' => 'dzs_woo_filter',<br>
     *   'blockOptions' => include(DZSWTL_PATH.'configs/config-filter-options.php'),
     * )
     * @param array $pargs
     */
    function __construct($pargs = array()) {
      $blockFactoryAtts = array_merge(array(
        'gutenbergBlockName' => '',
        'gutenbergBlockNameJs' => '',
        'blockJsUrl' => '',
        'blockShortcode' => '',
        'blockOptions' => '',
        'actualShortcode' => '',
      ), $pargs);
      $this->gutenbergBlockName = $blockFactoryAtts['gutenbergBlockName'];
      $this->gutenbergBlockNameJs = $blockFactoryAtts['gutenbergBlockNameJs'];
      $this->blockJsUrl = $blockFactoryAtts['blockJsUrl'];
      $this->blockShortcode = $blockFactoryAtts['blockShortcode'];
      $this->blockOptions = $blockFactoryAtts['blockOptions'];
      $this->actualShortcode = $blockFactoryAtts['actualShortcode'];


      add_action('init', array($this, 'handle_init'), 5);
      add_action('init', array($this, 'add_support_block'), 500);
      add_action('admin_footer', array($this, 'load_script'), 500);
    }


    /**
     * @param $argarr
     * @return array converts to array('type' => 'string','default' => $default,) for gutenberg php
     */
    public static function sanitize_config_to_gutenberg_register_block_type($argarr) {

      $foutarr = array();
      foreach ($argarr as $lab => $arr) {

        $key = $lab;
        $default = '';

        if (isset($arr['default'])) {
          $default = $arr['default'];
        }


        $foutarr[$key] = array(
          'type' => 'string',
          'default' => $default,
        );
      }
      return $foutarr;

    }

    /**
     * called on init start
     */
    function handle_init() {

      // -- we store this for loading in the footer once all dependencies are loaded
      if (is_admin() && function_exists('wp_register_script')) {
        wp_register_script(
          $this->gutenbergBlockName,
          $this->blockJsUrl,
          array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor')
        );
      }


      if (is_admin() && class_exists('DZSWTLHelpers')) {
        DZSWTLHelpers::enqueue_ultibox();
      }

      add_shortcode($this->blockShortcode, array($this, 'shortcode_render'));

    }

    /**
     * init end
     */
    function add_support_block() {
      // -- in init


      // -- default atrributes gallery


      $atts_gutenberg_block = DzsCommonGutenbergBlockFactory::sanitize_config_to_gutenberg_register_block_type($this->blockOptions);

      if (function_exists('register_block_type')) {

        // -- register gutenberg
        // todo: real atts
        register_block_type($this->gutenbergBlockNameJs, array(
          'attributes' => $atts_gutenberg_block,
          'render_callback' => array($this, 'shortcode_render'),
        ));
      }

    }

    /**
     * called in admin_footer
     */
    function load_script() {


      global $post;

//     -- we need to remove gutenberg support if this is avada or wpbakery


      $isLoadScript = true;

      if ($post && $post->post_content && strpos($post->post_content, 'vc_row') !== false) {
        $isLoadScript = false;
      }
      if ((defined('AVADA_VERSION'))) {

        $isLoadScript = false;
      }

      // -- disable if it's not gutenberg
      if (function_exists('get_current_screen')) {
        if (method_exists('get_current_screen', 'is_block_editor') && !get_current_screen()->is_block_editor()) {
          $isLoadScript = false;
        }
      }

      if (isset($_GET['post_type']) && $_GET['post_type'] == 'sp_easy_accordion') {

        $isLoadScript = false;
      }

      if ($isLoadScript) {
        wp_enqueue_script('wp-blocks');
        wp_enqueue_script('wp-element');
        wp_enqueue_script($this->gutenbergBlockName);
      }

    }


    function shortcode_render($attributes) {
      // -- player render

      $fout = '';

      if (is_admin()) {
      }


      $attributes['called_from'] = 'gutenberg_factory_render';
      // todo: here
      $fout .= '<div class="gutenberg-dzs-generator-con">';
      $fout .= call_user_func_array($this->actualShortcode, array($attributes));
      $fout .= '</div>';

      return $fout;
    }
  }
}