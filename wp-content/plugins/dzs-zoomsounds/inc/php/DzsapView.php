<?php
include_once(DZSAP_BASE_PATH . 'inc/php/view-functions/view-embed-functions.php');
include_once(DZSAP_BASE_PATH . 'inc/php/shortcodes/shortcode-player.php');
include_once(DZSAP_BASE_PATH . 'inc/php/shortcodes/shortcode-playlist.php');
include_once(DZSAP_BASE_PATH . 'inc/features/view/player--custom-colors/player--custom-colors.php');

class DzsapView {

  public $viewVar = 0;
  /** @var DzsAudioPlayer */
  public $dzsap;


  public $footer_style = '';
  /** @var string used if init_javascript_method is script */
  public $footerScript = '';
  public $footer_style_configs = array();

  public $extraFunctionalities = array();


  /** @var array used for playlist audio player configs */
  private $audioPlayerConfigs = array();

  /**
   * DzsapView constructor.
   * @param DzsAudioPlayer $dzsap
   */
  function __construct($dzsap) {

    $this->dzsap = $dzsap;


    add_action('init', array($this, 'handle_init_begin'), 3);
    add_action('init', array($this, 'handle_init'), 55);
    add_action('init', array($this, 'handle_init_end'), 900);
    add_action('wp_head', array($this, 'handle_wp_head'));
    add_action('wp_head', array($this, 'handle_wp_head_end'), 900);
    add_action('wp_enqueue_scripts', array($this, 'handle_wp_enqueue_scripts'), 900);


    add_action('wp_footer', array($this, 'handle_wp_footer_start'), 5);
    add_action('wp_footer', array($this, 'handle_wp_footer_end'), 500);


    add_shortcode(DZSAP_ZOOMSOUNDS_ACRONYM, array($this, 'shortcode_playlist_main'));
    add_shortcode('dzs_' . DZSAP_ZOOMSOUNDS_ACRONYM, array($this, 'shortcode_playlist_main'));


  }

  function handle_init_begin() {

    if (function_exists('vc_add_shortcode_param')) {
      vc_add_shortcode_param('dzs_add_media_att', 'vc_dzs_add_media_att');
    }
  }


  /**
   * will encode to json
   * @return void
   */
  function printHeadScripts() {

    $dzsap = $this->dzsap;

    $usrId = get_current_user_id();
    $usrData = null;

    if ($usrId) {
      $usrData = get_user_by('id', $usrId);
    }
    global $post;


    $mainDzsapSettings = array(
      'dzsap_site_url' => site_url() . '/',
      'pluginurl' => DZSAP_URL_AUDIOPLAYER,
      'dzsap_curr_user' => $usrId,
      'version' => DZSAP_VERSION,
      'view_replace_audio_shortcode' => $dzsap->mainoptions['replace_audio_shortcode'],
      'ajax_url' => admin_url('admin-ajax.php') . '',
    );


    $lab = 'dzsaap_default_portal_upload_type';
    if ($dzsap->mainoptions[$lab] && $dzsap->mainoptions[$lab] != 'audio') {
      $mainDzsapSettings[$lab] = $dzsap->mainoptions[$lab];
    }
    if ($post && $post->post_type == DZSAP_REGISTER_POST_TYPE_NAME) {
      $mainDzsapSettings['playerid'] = $post->ID;
    }
    if (($usrData)) {
      $mainDzsapSettings['comments_username'] = $usrData->data->display_name;
      $mainDzsapSettings['comments_avatar'] = DZSZoomSoundsHelper::get_avatar_url(get_avatar($usrId, 40));
    }
    if ($dzsap->mainoptions['try_to_cache_total_time'] == 'on') {
      $jsName = 'action_received_time_total';
      $value = 'send_total_time';
      $mainDzsapSettings[$jsName] = $value;
    }
    if ($dzsap->mainoptions['construct_player_list_for_sync'] == 'on') {

      $mainDzsapSettings['syncPlayers_buildList'] = 'on';
      $mainDzsapSettings['syncPlayers_autoplayEnabled'] = true;
    }


    echo json_encode($mainDzsapSettings);

  }

  function handle_wp_enqueue_scripts() {
    $dzsap = $this->dzsap;


  }


  function handle_init() {
    $dzsap = $this->dzsap;


    if (!is_admin()) {
      if ($this->dzsap->mainoptions['replace_playlist_shortcode'] == 'on') {
        add_shortcode('playlist', array($this, 'shortcode_wpPlaylist'));
      }

      include_once DZSAP_BASE_PATH . 'inc/extensions/view/view-global-vol-icon.php';
      add_shortcode('zoomsounds_global_vol_icon', 'shortcode_zoomsounds_global_vol_icon');
    }


    add_shortcode('dzsap_show_curr_plays', array($this, 'show_curr_plays'));
    add_shortcode('zoomsounds_player_comment_field', array($this, 'shortcode_player_comment_field'));

    add_shortcode(DZSAP_ZOOMSOUNDS_ACRONYM . '_player', array($this, 'shortcode_player'));

    dzsap_view_embed_init_listeners();
  }

  function handle_init_end() {

    $dzsap = $this->dzsap;
    if ($dzsap->mainoptions['replace_audio_shortcode'] && $dzsap->mainoptions['replace_audio_shortcode'] !== 'off') {
      add_shortcode('audio', array($this, 'shortcode_audio'));

      wp_enqueue_script('replace-gutenberg-player', DZSAP_BASE_URL . 'libs/dzsap/replace-gutenberg-player/replace-gutenberg-player.js', array(), DZSAP_VERSION);


      $replaceAudioConfigVpConfig = $dzsap->mainoptions['replace_audio_shortcode'];
      $vpsettings = DZSZoomSoundsHelper::getVpConfigFromConfigsDatabase($replaceAudioConfigVpConfig);
      unset($vpsettings['settings']['id']);


      $this->audioPlayerConfigsAdd($replaceAudioConfigVpConfig, $vpsettings['settings']);
      $this->footer_style .= dzsap_view_generateCssPlayerCustomColors(array(
        'configId' => $replaceAudioConfigVpConfig,
        'config' => $vpsettings['settings'],
      ));

    }


    if ($dzsap->mainoptions['extra_css']) {
      wp_register_style('dzsap-hook-head-styles', false);
      wp_enqueue_style('dzsap-hook-head-styles');
      wp_add_inline_style('dzsap-hook-head-styles', DZSZoomSoundsHelper::sanitize_for_special_character(stripslashes($dzsap->mainoptions['extra_css'])));
    }

    if (!is_admin()) {

      // -- extra
      include_once(DZSAP_BASE_PATH . 'inc/php/view-functions/extra-functionality/syncPlayers-autoplay-toggler.php');
      add_shortcode('dzsap_syncplayers_autoplay_toggler', 'dzsap_shortcode_syncplayers_autoplay_toggler');
    }

  }

  function handle_wp_head() {
    $dzsap = $this->dzsap;


    if (is_tax(DZSAP_TAXONOMY_NAME_SLIDERS) || ($dzsap->mainoptions['single_index_seo_disable'] == 'on' && is_singular('dzsap_items'))) {
      echo '<meta name="robots" content="noindex, follow">';
    }


    if ($dzsap->mainoptions['replace_powerpress_plugin'] == 'on') {
      global $post;
      if ($post) {
        if ($post->ID != '4812' && $post->ID != '23950') {
          $dzsap->mainoptions['replace_powerpress_plugin'] = 'off';
        }
      }
    }

    if (isset($_GET['dzsap_generate_pcm']) && $_GET['dzsap_generate_pcm']) {
      include DZSAP_BASE_PATH . 'class_parts/part-regenerate-waves-player.php';
    }


    if ($dzsap->mainoptions['replace_powerpress_plugin'] == 'on') {
      add_filter('the_content', array($this, 'filter_the_content'));
    }


    if (!is_single() && (is_post_type_archive(DZSAP_REGISTER_POST_TYPE_NAME) || is_tax(DZSAP_REGISTER_POST_TYPE_CATEGORY))) {
      if ($dzsap->mainoptions['excerpt_hide_zoomsounds_data'] == 'on' || $dzsap->mainoptions['exceprt_zoomsounds_posts']) {
        add_filter('get_the_excerpt', array($this, 'filter_the_content_end'), 9999);
      }
    }

    echo '<script id="dzsap-main-settings" class="dzsap-main-settings" type="application/json">';
    $this->printHeadScripts();
    echo '</script>';

    wp_register_style('dzsap-init-styles', false);
    wp_enqueue_style('dzsap-init-styles');

    wp_add_inline_style('dzsap-init-styles', '.audioplayer,.audioplayer-tobe,.audiogallery{opacity:0;}');

    DZSZoomSoundsHelper::echoJavascriptKeyboardControls($dzsap);

  }


  function handle_wp_footer_start() {

    if ($this->dzsap->mainoptions['init_javascript_method'] == 'script') {

      $this->footerScript .= 'jQuery(document).ready(function($){';
      $this->footerScript .= '$(\'.audioplayer-tobe:not(.dzsap-inited)\').addClass(\'auto-init\'); ';
      $this->footerScript .= 'if(window.dzsap_init_allPlayers) { window.dzsap_init_allPlayers($); }';
      $this->footerScript .= '});';


      wp_register_script(DZSAP_ID . '-inline-footer', false);
      wp_enqueue_script(DZSAP_ID . '-inline-footer');
      wp_add_inline_script(DZSAP_ID . '-inline-footer', $this->footerScript, 'before');
    }


    if ($this->dzsap->mainoptions['failsafe_ajax_reinit_players'] == 'on') {
      wp_enqueue_script('dzsap-init-all-players-on-interval', DZSAP_BASE_URL . 'inc/js/shortcodes/init-all-players-on-interval.js', array(), DZSAP_VERSION);
    }

    $this->generateArgsForFooterStickyPlayerFromMeta();

    if (isset($_GET['action'])) {
      if ($_GET['action'] == 'embed_zoomsounds') {
        dzsap_view_embed_generateHtml();
      }
    }
    $this->handle_footer_extraHtml();
  }

  function handle_wp_footer_end() {

  }

  function filter_the_content($fout) {
    $dzsap = $this->dzsap;

    if ($dzsap->mainoptions['replace_powerpress_plugin'] == 'on') {
      include_once(DZSAP_BASE_PATH . 'inc/php/compatibilities/powerpress/powerpress-functions.php');
      return dzsap_powerpress_filter_content($fout);
    }


    return $fout;
  }

  function filter_the_content_end($fout) {
    global $post;
    $dzsap = $this->dzsap;

    $postTitle = '';

    if ($post && $post->post_title) {
      $postTitle = $post->post_title;
    }


    if ($dzsap->mainoptions['exceprt_zoomsounds_posts']) {

      $shortcodePatternStr = DZSZoomSoundsHelper::sanitize_from_shortcode_pattern($dzsap->mainoptions['exceprt_zoomsounds_posts'], $post);
      $fout = do_shortcode($shortcodePatternStr);
    } else {
      if ($dzsap->mainoptions['excerpt_hide_zoomsounds_data'] == 'on') {

        $fout = str_replace($postTitle . $postTitle . $postTitle, '', $fout);
        $fout = str_replace($postTitle . $postTitle, '', $fout);
        $fout = str_replace('Stats Edit Delete', '', $fout);
        $fout = str_replace('Add to cart', '', $fout);

        $fout = preg_replace('/\[zoomsounds.*?]/', ' ', $fout);;;
        $fout = preg_replace('/&lt;iframe.*?&lt;\/iframe&gt;/', ' ', $fout);;
      }

    }


    return $fout;
  }

  /**
   * gets properties only needed for frontend
   * eliminates default props
   * @param $config_name
   * @return array|string[][]
   */
  public function get_zoomsounds_player_config_settings($config_name) {

    $dzsap = $this->dzsap;

    $vpsettingsdefault = array(
      'id' => 'default',
      'skin_ap' => 'skin-wave',
      'skinwave_dynamicwaves' => 'off',
      'skinwave_enablespectrum' => 'off',
      'skinwave_enablereflect' => 'on',
      'skinwave_comments_enable' => 'off',
      'disable_volume' => 'default',
      'playfrom' => 'default',
      'enable_embed_button' => 'off',
      'loop' => 'off',
      'soundcloud_track_id' => '',
      'soundcloud_secret_token' => '',
    );


    $vpconfig_k = -1;

    $vpsettings = array();
    $vpconfig_id = $config_name;


    if (is_array($config_name)) {


      $vpsettings['settings'] = $config_name;


    } else {

      for ($i = 0; $i < count($dzsap->mainitems_configs); $i++) {
        if ((isset($vpconfig_id)) && ($vpconfig_id == $dzsap->mainitems_configs[$i]['settings']['id'])) {
          $vpconfig_k = $i;
        }
      }


      if ($vpconfig_k > -1) {
        $vpsettings = $dzsap->mainitems_configs[$vpconfig_k];
      } else {
        $vpsettings['settings'] = $vpsettingsdefault;
      }

      if (is_array($vpsettings) == false || is_array($vpsettings['settings']) == false) {
        $vpsettings = array('settings' => $vpsettingsdefault);
      }
    }

    return $vpsettings;
  }


  function handle_footer_extraHtml() {

    $dzsap = $this->dzsap;

    if ($this->footer_style) {
      wp_register_style('dzsap-footer-style', false);
      wp_enqueue_style('dzsap-footer-style');
      wp_add_inline_style('dzsap-footer-style', $this->footer_style);
    }


    if ($dzsap->og_data && count($dzsap->og_data)) {
      $ogThumbnailSrc = '';

      if (isset($dzsap->og_data['image'])) {
        $ogThumbnailSrc = $dzsap->og_data['image'];
      }
      echo '<meta property="og:title" content="' . $dzsap->og_data['title'] . '" />';
      echo '<meta property="og:description" content="' . strip_tags($dzsap->og_data['description']) . '" />';

      if ($ogThumbnailSrc) {
        echo '<meta property="og:image" content="' . $ogThumbnailSrc . '" />';
      }
    }


    if ($dzsap->isEnableMultisharer) {
      dzsap_generateHtmlMultisharer();
    }


    if (count($this->audioPlayerConfigs) > 0) {
      ?>
      <div hidden class="dzsap-feed--dzsap-configs"><?php echo json_encode($this->audioPlayerConfigs); ?></div><?php
    }

    if (($dzsap->mainoptions['wc_loop_product_player'] && $dzsap->mainoptions['wc_loop_product_player'] != 'off') || ($dzsap->mainoptions['wc_single_product_player'] && $dzsap->mainoptions['wc_single_product_player'] != 'off')) {


      if ($dzsap->mainoptions['wc_loop_player_position'] == 'overlay') {
        dzsap_generateHtmlWoocommerceOverlayPlayer();
      }
    }


    if (isset($dzsap->mainoptions['replace_powerpress_plugin']) && $dzsap->mainoptions['replace_powerpress_plugin'] == 'on') {
      dzsap_powerpress_generateHtmlEnclosureData();
    }


  }

  /**
   *  generate the footer player args from the meta info
   */
  function generateArgsForFooterStickyPlayerFromMeta() {
    global $wp_query;

    $dzsap = $this->dzsap;

    $isFooterPlayerEnabled = false;
    $footer_player_source = 'fake';
    $footer_player_config = 'fake';
    $footer_player_type = 'fake';
    $footer_player_songName = '';


    if ($dzsap->mainoptions['enable_global_footer_player'] != 'off') {

      $isFooterPlayerEnabled = true;
      $footer_player_source = 'fake';
      $footer_player_type = 'fake';
      $footer_player_config = $dzsap->mainoptions['enable_global_footer_player'];
    }

    if ($wp_query && $wp_query->post) {
      if ((get_post_meta($wp_query->post->ID, DZSAP_META_NAME_FOOTER_FEATURED_MEDIA, true)
        || get_post_meta($wp_query->post->ID, DZSAP_META_NAME_FOOTER_ENABLE, true) == 'on')
      ) {

        $isFooterPlayerEnabled = true;


        $footer_player_config = get_post_meta($wp_query->post->ID, DZSAP_META_NAME_FOOTER_VPCONFIG, true);
        if (get_post_meta($wp_query->post->ID, DZSAP_META_NAME_FOOTER_FEED_TYPE, true) == 'custom') {
          $footer_player_source = get_post_meta($wp_query->post->ID, DZSAP_META_NAME_FOOTER_FEATURED_MEDIA, true);
          $footer_player_type = get_post_meta($wp_query->post->ID, DZSAP_META_NAME_FOOTER_TYPE, true);

        }
        if (get_post_meta($wp_query->post->ID, DZSAP_META_NAME_FOOTER_SONG_NAME, true)) {
          $footer_player_songName = get_post_meta($wp_query->post->ID, DZSAP_META_NAME_FOOTER_SONG_NAME, true);

        }
      }
    }


    if ($isFooterPlayerEnabled) {
      if ($footer_player_source) {
        $this->view_generateFooterPlayer($footer_player_type, $footer_player_source, $footer_player_config, $footer_player_songName);
      }
    }
  }

  /**
   * generate the output
   * @param $footer_player_type
   * @param $footer_player_source
   * @param $footer_player_config
   * @param $footer_player_songName
   */
  function view_generateFooterPlayer($footer_player_type, $footer_player_source, $footer_player_config, $footer_player_songName) {

    $dzsap = $this->dzsap;


    $dzsap->front_scripts();

    $cueMedia = 'on';
    if ($footer_player_type === 'fake') {
      $cueMedia = 'off';
    }

    $playerArgs = array(
      'player_id' => DZSAP_VIEW_STICKY_PLAYER_ID,

      'source' => $footer_player_source,
      'cueMedia' => $cueMedia,
      'config' => $footer_player_config,
      'autoplay' => 'off',
      'songname' => $footer_player_songName,
      'type' => $footer_player_type,
    );

    wp_enqueue_script('dzsap-player-' . 'change-media', DZSAP_BASE_URL . 'audioplayer/parts/player/change-media/change-media.js');

    $vpsettings = DZSZoomSoundsHelper::getVpConfigFromConfigsDatabase($footer_player_config);


    echo '<div class="dzsap-sticktobottom-placeholder dzsap-sticktobottom-placeholder-for-' . $vpsettings['settings']['skin_ap'] . '"></div>
<section class="dzsap-sticktobottom ';


    if ((!isset($vpsettings['settings']['skin_ap']) ||
        $vpsettings['settings']['skin_ap'] == 'skin-wave') &&
      (isset($vpsettings['settings']['skinwave_mode']) && $vpsettings['settings']['skinwave_mode'] == 'small'
      )
    ) {
      echo ' dzsap-sticktobottom-for-skin-wave';
    }


    if (!isset($vpsettings['settings']['skin_ap']) || ($vpsettings['settings']['skin_ap'] == 'skin-silver')) {
      echo ' dzsap-sticktobottom-for-skin-silver';
    }


    echo '">';

    echo '<div class="dzs-container">';


    if (!isset($vpsettings['settings']['enable_footer_close_button']) || ($vpsettings['settings']['enable_footer_close_button'] == 'on')) {
      echo '<div class="sticktobottom-close-con">' . $dzsap->general_assets['svg_stick_to_bottom_close_hide'] . $dzsap->general_assets['svg_stick_to_bottom_close_show'] . ' </div>';

      wp_enqueue_script('dzsap-footer-player-close-btn', DZSAP_URL_AUDIOPLAYER . 'parts/footer-player/footer-player-icon-hide.js');
    }


    $aux = array('called_from' => 'footer_player');

    $playerArgs = array_merge($playerArgs, $aux);


    echo $dzsap->classView->shortcode_player($playerArgs);


    echo '</div>';
    echo '</section>';


    wp_enqueue_style(DZSAP_ID . '-sticky-player', DZSAP_URL_AUDIOPLAYER . 'parts/player/sticky-player/sticky-player.css');
    wp_enqueue_script(DZSAP_ID . '-sticky-player', DZSAP_URL_AUDIOPLAYER . 'parts/player/sticky-player/sticky-player.js');
  }

  /**
   * @param $singleItemInstance
   * @param array $pargs
   * @return string pcm data as string
   */
  function generate_pcm($singleItemInstance, $pargs = array()) {

    $dzsap = $this->dzsap;

    $margs = array(
      'generate_only_pcm' => false, // -- generate only the pcm not the markup
      'identifierSource' => '',
      'identifierId' => '',
    );

    if (!is_array($pargs)) {
      $pargs = array();
    }

    $margs = array_merge($margs, $pargs);

    $fout = '';


    $pcmIdentifierId = $margs['identifierId'];
    $pcmIdentifierSource = $margs['identifierSource'];


    // -- if it's a post... stdObject
    if (isset($singleItemInstance->post_title)) {
      $args = array();
      $pcmIdentifierSource = $dzsap->get_track_source($singleItemInstance->ID, $singleItemInstance->ID, $args);
      $singleItemInstance = (array)$singleItemInstance;

      $singleItemInstance['playerid'] = $singleItemInstance['id'];
    }


    if (isset($singleItemInstance['source']) && $singleItemInstance['source']) {
      $pcmIdentifierSource = $singleItemInstance['source'];
    }
    if (isset($singleItemInstance['playerid']) && $singleItemInstance['playerid']) {
      $pcmIdentifierId = $singleItemInstance['playerid'];
    }
    if (isset($singleItemInstance['wpPlayerPostId']) && $singleItemInstance['wpPlayerPostId']) {
      $pcmIdentifierId = $singleItemInstance['wpPlayerPostId'];
    }

    if ($pcmIdentifierSource == 'fake') {
      return '';
    }


    $stringPcm = DzsApView::getPcmData($singleItemInstance, $pcmIdentifierId, $pcmIdentifierSource);


    if (!DzsapView::isPcmInvalid($stringPcm)) {
      $fout .= ' data-pcm=\'' . stripslashes($stringPcm) . '\'';
    }

    if ($margs['generate_only_pcm'] && !DzsapView::isPcmInvalid($stringPcm)) {
      $fout = stripslashes($stringPcm);
    }


    return $fout;
  }


  function handle_wp_head_end() {

    $dzsap = $this->dzsap;

    if ($dzsap->mainoptions['script_use_async'] === 'on' || $dzsap->mainoptions['script_use_defer'] === 'on') {

      add_filter('script_loader_tag', array($this, 'script_use_async'), 10, 3);
    }

  }

  function script_use_async($tag, $handle) {

    $dzsap = $this->dzsap;

    if ($dzsap->mainoptions['script_use_async'] === 'on' && $dzsap->mainoptions['init_javascript_method'] != 'script') {
      if (strpos($handle, DZSAP_ID) !== false) {
        $tag = str_replace('<script', '<script async', $tag);
      }
    }

    if ($dzsap->mainoptions['script_use_defer'] === 'on' && $dzsap->mainoptions['init_javascript_method'] != 'script') {
      if (strpos($handle, DZSAP_ID) !== false) {
        $tag = str_replace('<script', '<script defer', $tag);
      }
    }

    return $tag;
  }


  /**
   * [zoomsounds_player source="pathto.mp3" artistname="" songname=""]
   * @param array $argsShortcodePlayer
   * @param string $content
   * @return string
   */
  function shortcode_player($argsShortcodePlayer = array(), $content = '') {

    return dzsap_view_shortcode_player($argsShortcodePlayer, $content, $this->dzsap);
  }

  /**
   * called in parse_items()
   * @param $playerAttributes
   * @param $argPlaylistOptions
   * @param $argPlayerOptions
   * @param $argPlayerConfig
   * @param $playerid
   * @param $che_post
   * @return string[]
   */
  public function parseItems_determineExtraHtml($playerAttributes, $argPlaylistOptions, $argPlayerOptions, $argPlayerConfig, $playerid, $che_post) {

    $extraHtmlAreas = array(
      'bottom' => '',
      'bottom_left' => '',
      'afterArtist' => '',
      'controlsLeft' => '',
      'controlsRight' => '',
      'afterPlayPause' => '',
      'afterConControls' => '',
    );
    $i_fout = '';


    $playlistOptions = array(
      'enable_downloads_counter' => 'off'
    );
    $playerOptions = array(
      'is_single' => 'off',
      'embedded' => 'off',
    );
    /** @var $playlistAndPlayerOptions array  common attributes */
    $playlistAndPlayerOptions = array(
      'enable_rates' => 'off',
      'enable_views' => 'off',
      'enable_likes' => 'off',
      'enable_download_button' => 'off',
      'menu_right_enable_info_btn' => 'off',
      'js_settings_extrahtml_in_float_right_from_config' => '',
      'js_settings_extrahtml_in_bottom_controls_from_config' => '',
    );
    $playerConfig = array(
      'enable_config_button' => 'off',
      'enable_embed_button' => 'off',
    );

    if (count($playerConfig)) {
      $playerConfig = array_merge($playerConfig, $argPlayerConfig);
    }
    if (count($playerOptions)) {
      $playerOptions = array_merge($playerOptions, $argPlayerOptions);
    }

    if (count($argPlaylistOptions)) {
      $playlistOptions = array_merge($playlistOptions, $argPlaylistOptions);
    }
    $playlistAndPlayerOptions = array_merge($playlistAndPlayerOptions, $playlistOptions);
    $playlistAndPlayerOptions = array_merge($playlistAndPlayerOptions, $playerConfig);
    $playlistAndPlayerOptions = array_merge($playlistAndPlayerOptions, $playerOptions);


    $dzsap = $this->dzsap;


    include_once DZSAP_BASE_PATH . 'inc/php/view-functions/view-determine-html-areas.php';

    $extraHtmlAreas['bottom'] = dzsap_view_determineHtmlAreas_bottom($dzsap, $playerAttributes, $playlistAndPlayerOptions, $playerid);

    $extraHtmlAreas['bottom_left'] = dzsap_view_determineHtmlAreas_bottomLeft($dzsap, $playerAttributes, $playerOptions, $playlistAndPlayerOptions, $playerConfig, $playerid);

    $extraHtmlAreas['controlsLeft'] = dzsap_view_determineHtmlAreas_controlsLeft($playerAttributes);
    $extraHtmlAreas['controlsRight'] = dzsap_view_determineHtmlAreas_controlsRight($dzsap, $playerAttributes, $playerConfig, $che_post, $playlistAndPlayerOptions);
    $extraHtmlAreas['afterPlayPause'] = dzsap_view_determineHtmlAreas_controlsAfterPlayPause($playerConfig, $playerOptions);
    $extraHtmlAreas['afterConControls'] = dzsap_view_determineHtmlAreas_controlsAfterConControls($playerConfig, $playerOptions);

    foreach ($extraHtmlAreas as $key => $extraHtmlArea) {

      if (is_string($extraHtmlArea) && $extraHtmlArea) {
        if (strpos($extraHtmlArea, 'btn-like') !== false) {
          DZSZoomSoundsHelper::enqueuePartScript('player-like-functionality');
        }
        if (strpos($extraHtmlArea, '<i class="fa') !== false) {
          DZSZoomSoundsHelper::enqueueFontAwesome();
        }
      }
    }


    return $extraHtmlAreas;
  }

  /**
   * called in parse_items()
   * @param $singleItemInstance
   * @param $argPlaylistOptions
   * @param $argPlayerOptions
   * @param $argPlayerConfig
   * @param $playerid
   * @param $che_post
   * @return string
   */
  public function generatePlayerExtraHtml($extraHtmlAreas, $singleItemInstance) {

    $i_fout = '';

    foreach ($extraHtmlAreas as $key => $extraHtmlArea) {

      $extraHtmlAreas[$key] = DZSZoomSoundsHelper::sanitize_for_extraHtml($extraHtmlAreas[$key], $singleItemInstance);;
    }
    if ($extraHtmlAreas['controlsLeft']) {
      $i_fout .= $extraHtmlAreas['controlsLeft'];
    }
    if (isset($extraHtmlAreas['controlsRight']) && $extraHtmlAreas['controlsRight']) {
      $i_fout .= $extraHtmlAreas['controlsRight'];
    }
    if (isset($extraHtmlAreas['afterPlayPause']) && $extraHtmlAreas['afterPlayPause']) {
      $i_fout .= $extraHtmlAreas['afterPlayPause'];
    }
    if (isset($extraHtmlAreas['afterConControls']) && $extraHtmlAreas['afterConControls']) {
      $i_fout .= $extraHtmlAreas['afterConControls'];
    }
    if ($extraHtmlAreas['bottom_left']) {

      $i_fout .= '<div hidden class="feed-dzsap feed-dzsap--extra-html feed-dzsap--extra-html--bottom-left ">';
      $i_fout .= $extraHtmlAreas['bottom_left'];
      $i_fout .= '</div><!-- end .extra-html--left-->';
    }
    if ($extraHtmlAreas['bottom']) {

      $i_fout .= '<div hidden class="feed-dzsap feed-dzsap--extra-html" data-playerid="' . $singleItemInstance['playerid'] . '" style="opacity:0;">' . ($extraHtmlAreas['bottom']) . '</div>';
    }

    return $i_fout;
  }

  /**
   * @return array|mixed
   */
  function get_wishlist() {


    $arr_wishlist = array();

    if (get_user_meta(get_current_user_id(), 'dzsap_wishlist', true) && get_user_meta(get_current_user_id(), 'dzsap_wishlist', true) != 'null') {
      try {

        $arr_wishlist = json_decode(get_user_meta(get_current_user_id(), 'dzsap_wishlist', true), true);
      } catch (Exception $e) {

      }
    }

    return $arr_wishlist;
  }


  function shortcode_player_comment_field() {

    $fout = '';

    global $current_user;


    if ($current_user->ID) {
      $fout .= '<div class="zoomsounds-comment-wrapper">
                <div class="zoomsounds-comment-wrapper--avatar divimage" style="background-image: url(https://www.gravatar.com/avatar/?d=identicon);"></div>
                <div class="zoomsounds-comment-wrapper--input-wrap">
                    <input type="text" class="comment_text" placeholder="' . esc_html__("Write a comment") . '"/>
                    <input type="text" class="comment_email" placeholder="' . esc_html__("Your email") . '"/>
                    <!--<input type="text" class="comment_user" placeholder="' . esc_html__("Your display name") . '"/>-->
                </div>

                <div class="zoomsounds-comment-wrapper--buttons">
                    <span class="dzs-button-dzsap comments-btn-cancel">' . esc_html__("Cancel") . '</span>
                    <span class="dzs-button-dzsap comments-btn-submit">' . esc_html__("Submit") . '</span>
                </div>
            </div>';


      wp_enqueue_script('dzsap-part-player-comments', DZSAP_URL_AUDIOPLAYER . 'parts/player/player-comments/player-comments.js');
      wp_enqueue_style('dzsap-part-player-comments', DZSAP_URL_AUDIOPLAYER . 'parts/player/player-comments/player-comments.css');
    } else {
      $fout .= esc_html__("You need to be logged in to comment", DZSAP_ID);
    }


    return $fout;


  }


  function show_curr_plays($pargs = array(), $content = '') {
    global $post;

    $fout = '';


    $str_views = $this->dzsap->mainoptions['str_views'];


    if (isset($pargs['id'])) {
      $post = get_post($pargs['id']);
    }


    if ($post) {
      $str_views = $this->dzsap->ajax_functions->get_metaViews($post->ID);
      $fout = str_replace('{{get_plays}}', $str_views, $fout);
    }
    return $fout;
  }

  /**
   * default wordpress audio [zoomsounds_player source="pathto.mp3"]
   * @param array $atts
   * @param null $content
   * @return string
   */
  function shortcode_audio($atts = array(), $content = null) {


    // --


    $dzsap = $this->dzsap;
    $dzsap->sliders__player_index++;

    $fout = '';


    DZSZoomSoundsHelper::enqueueMainScrips();

    $margs = array(
      'mp3' => '',
      'wav' => '',
      'm4a' => '',
      'config' => 'default',
    );

    if (!is_array($atts)) {
      $atts = array();
    }

    $margs = array_merge($margs, $atts);

    if ($margs['mp3']) {
      $margs['source'] = $margs['mp3'];
    } else {
      if ($margs['wav']) {
        $margs['source'] = $margs['wav'];
      } else {
        if ($margs['m4a']) {
          $margs['source'] = $margs['m4a'];
        }
      }
    }
    $margs['config'] = $dzsap->mainoptions['replace_audio_shortcode'];
    $margs['called_from'] = 'audio_shortcode';


    $audio_attachments = get_posts(array(
      'post_type' => 'attachment',
      'post_mime_type' => 'audio'
    ));


    $pid = 0;
    foreach ($audio_attachments as $lab => $val) {


      if ($val->guid == $margs['source']) {
        $pid = $val->ID;
        break;
      }
    }

    if ($pid) {


      $margs['source'] = $pid;
    }


    if ($dzsap->mainoptions['replace_audio_shortcode_extra_args']) {
      try {
        $arr = json_decode($dzsap->mainoptions['replace_audio_shortcode_extra_args'], true);
        $margs = array_merge($margs, $arr);
      } catch (Exception $e) {
      }
    }

    if ($dzsap->mainoptions['replace_audio_shortcode_play_in_footer'] == 'on') {
      $margs['play_target'] = 'footer';
    }

    $playerid = '';

    $fout .= $dzsap->classView->shortcode_player($margs, $content);


    return $fout;
  }


  /**
   * [playlist ids="2,3,4"]
   * @param $atts
   * @return string
   * @throws Exception
   */
  function shortcode_wpPlaylist($atts) {

    //
    $dzsap = $this->dzsap;

    global $current_user;
    $fout = '';
    $iout = ''; //items parse

    $defaultPlaylistOptions = array(
      'ids' => '1'
    , 'embedded_in_zoombox' => 'off'
    , 'embedded' => 'off'
    , 'db' => 'main'
    );

    if ($atts == '') {
      $atts = array();
    }

    $defaultPlaylistOptions = array_merge($defaultPlaylistOptions, $atts);


    $po_array = explode(",", $defaultPlaylistOptions['ids']);

    $fout .= '[zoomsounds id="playlist_gallery" embedded="' . $defaultPlaylistOptions['embedded'] . '" for_embed_ids="' . $defaultPlaylistOptions['ids'] . '"]';


    // -- setting up the db ( deprecated )
    $currDb = '';
    if (isset($defaultPlaylistOptions['db']) && $defaultPlaylistOptions['db'] != '') {
      $dzsap->currDb = $defaultPlaylistOptions['db'];
      $currDb = $dzsap->currDb;
    }
    $dzsap->dbs = get_option(DZSAP_DBNAME_LEGACY_DBS);

    $dbname_mainitems = DZSAP_DBNAME_MAINITEMS;
    if ($currDb != 'main' && $currDb != '') {
      $dbname_mainitems .= '-' . $currDb;
      $dzsap->mainitems = get_option($dbname_mainitems);
    }
    // -- setting up the db END


    $dzsap->front_scripts();


    $dzsap->sliders_index++;


    $i = 0;
    $k = 0;
    $id = DZSAP_VIEW_SHOWCASE_PLAYLIST_ID;
    if (isset($defaultPlaylistOptions['id'])) {
      $id = $defaultPlaylistOptions['id'];
    }


    $term_meta = array();
    $its = array(
      'settings' => array(),
    );
    $selected_term_id = '';


    $args = array(
      'id' => $id,
      'force_ids' => $defaultPlaylistOptions['ids'],
      'called_from' => 'shortcode_playlist',
    );
    $this->get_its_items($its, $args);

    if ($dzsap->mainoptions['playlists_mode'] == 'normal') {
      $tax = DZSAP_TAXONOMY_NAME_SLIDERS;
      $reference_term = get_term_by('slug', $id, $tax);
      if ($reference_term) {

        $selected_term_id = $reference_term->term_id;
        $term_meta = get_option("taxonomy_$selected_term_id");
      }
    }


    $this->get_its_settings($its, $defaultPlaylistOptions, $term_meta, $selected_term_id);


    $enable_likes = 'off';
    $enable_views = 'off';
    $enable_downloads_counter = 'off';

    if ($its) {
      $lab = 'enable_views';
      if (isset($its['settings'][$lab]) && $its['settings'][$lab]) {
        $enable_views = $its['settings'][$lab];
      }
      $lab = 'enable_likes';
      if (isset($its['settings'][$lab]) && $its['settings'][$lab]) {
        $enable_likes = $its['settings'][$lab];
      }
      $lab = 'enable_downloads_counter';
      if (isset($its['settings'][$lab]) && $its['settings'][$lab]) {
        $enable_downloads_counter = $its['settings'][$lab];
      }
    }


    foreach ($po_array as $po_id) {


      if (is_numeric($po_id)) {

        $po = get_post($po_id);

        if ($po) {

          $title = $po->post_title;
          $title = str_replace(array('"', '[', ']'), '&quot;', $title);
          $desc = $po->post_content;
          $desc = str_replace(array('"', '[', ']'), '&quot;', $desc);
          $fout .= '[zoomsounds_player source="' . $po->guid . '" config="playlist_player" playerid="' . $po_id . '" thumb="" autoplay="on" cueMedia="on" enable_likes="' . $enable_likes . '" enable_views="' . $enable_views . '"  enable_downloads_counter="' . $enable_downloads_counter . '" songname="' . $title . '" artistname="' . $desc . '" init_player="off"]';
        }
      } else {

        $fout .= '[zoomsounds_player source="' . $po_id . '" config="playlist_player" playerid="' . $po_id . '" thumb="" autoplay="off" cueMedia="on" enable_likes="' . $enable_likes . '" enable_views="' . $enable_views . '"  enable_downloads_counter="' . $enable_downloads_counter . '"  init_player="off"]';
      }

    }
    $fout .= '[/zoomsounds]';


    $fout = do_shortcode($fout);


    return $fout;
  }

  function playlist_initialSetup(&$its) {


    // -- embed

    if (isset($its['settings']['gallery_embed_type'])) {
      if ($its['settings']['gallery_embed_type'] === 'on-no-embed') {

      }
      if ($its['settings']['gallery_embed_type'] === 'on-with-embed') {


        $its['playerConfigSettings']['enable_embed_button'] = 'in_lightbox';
      }
    }

  }


  /** [zoomsounds id="theid"]
   * @param array $atts
   * @param null $content
   * @return string
   * @throws Exception
   */
  public function shortcode_playlist_main($atts = array(), $content = null) {

    return dzsap_view_shortcode_playlist($atts, $content, $this, $this->dzsap);

  }

  /**
   * @param string $apConfig
   * @param array $apConfigSettings
   */
  function audioPlayerConfigsAdd($apConfig, $apConfigSettings) {

    $sanitizedApConfigId = DZSZoomSoundsHelper::sanitizeToValidObjectLabel($apConfig);
    $this->audioPlayerConfigs[$sanitizedApConfigId] = $apConfigSettings;

  }


  /**
   * mutates its items
   * @param $its
   * @param $shortcodeOptions
   * @return string|void
   * @throws Exception
   */
  function get_its_items(&$its, $shortcodeOptions) {
    global $dzsap;
    // -- from @margs we need id

    if ($dzsap->mainoptions['playlists_mode'] == 'normal') {

      // -- try to get from reference term
      $tax = DZSAP_TAXONOMY_NAME_SLIDERS;


      $playlistId = '';
      if (isset($shortcodeOptions['playlist_id'])) {
        $playlistId = $shortcodeOptions['playlist_id'];
      }
      $reference_term = get_term_by('slug', $playlistId, $tax);


      if (!$reference_term) {
        // -- reference term does not exist..

        $directores = get_terms(DZSAP_TAXONOMY_NAME_SLIDERS);

        $args = $shortcodeOptions;
        $args['id'] = $directores[0]->slug;
        if ($shortcodeOptions['called_from'] != 'redo') {
          $args['called_from'] = 'redo';
          return $this->shortcode_playlist_main($args);
        }
        return '';
      }

      $selected_term_id = $reference_term->term_id;

      $term_meta = get_option("taxonomy_$selected_term_id");


      // -- main order
      if ($selected_term_id) {

        $args = array(
          'post_type' => 'dzsap_items',
          'numberposts' => -1,
          'posts_per_page' => -1,

          'orderby' => 'meta_value_num',
          'order' => 'ASC',

          'tax_query' => array(
            array(
              'taxonomy' => $tax,
              'field' => 'id',
              'terms' => $selected_term_id // Where term_id of Term 1 is "1".
            )
          ),
        );


        if (isset($term_meta['orderby'])) {
          if ($term_meta['orderby'] == 'rand') {
            $args['orderby'] = $term_meta['orderby'];
          }
          if ($term_meta['orderby'] == 'custom') {
            $args['meta_query'] = array(
              'relation' => 'OR',
              array(
                'key' => 'dzsap_meta_order_' . $selected_term_id,
                'compare' => 'EXISTS',
              ),
              array(
                'key' => 'dzsap_meta_order_' . $selected_term_id,
                'compare' => 'NOT EXISTS'
              )
            );
          }
          if ($term_meta['orderby'] == 'ratings_score') {
            $args['orderby'] = 'meta_value_num';

            $key = DZSAP_VIEW_PLAYER_FEATURE_RATINGS_METANAME_RATEINDEX;
            $args['meta_query'] = array(
              'relation' => 'OR',
              array(
                'key' => $key,
                'compare' => 'EXISTS',
              ),
              array(
                'key' => $key,
                'compare' => 'NOT EXISTS'
              )
            );
            $args['meta_type'] = 'NUMERIC';
            $args['order'] = 'DESC';

          }
          if ($term_meta['orderby'] == 'ratings_number') {
            $args['orderby'] = 'meta_value_num';

            $key = '_dzsap_rate_nr';
            $args['meta_query'] = array(
              'relation' => 'OR',
              array(
                'key' => $key,
                'compare' => 'EXISTS',
              ),
              array(
                'key' => $key,
                'compare' => 'NOT EXISTS'
              )
            );
            $args['meta_type'] = 'NUMERIC';
            $args['order'] = 'DESC';
          }
          if ($term_meta['orderby'] == 'alphabetical_ASC') {
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
          }
          if ($term_meta['orderby'] == 'alphabetical_DESC') {
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
          }
        }

        if (isset($shortcodeOptions['force_ids']) && $shortcodeOptions['force_ids']) {

          $args['post_type'] = 'any';
          $args['post_status'] = 'any';
          $args['post__in'] = explode(',', $shortcodeOptions['force_ids']);
          unset($args['tax_query']);
          unset($args['meta_query']);
        }
        $my_query = new WP_Query($args);


        foreach ($my_query->posts as $po) {


          $por = DZSZoomSoundsHelper::sanitize_to_gallery_item($po);

          array_push($its, $por);

        }
      }
    } else {
      // -- legacy mode

      if (isset($shortcodeOptions['playlist_id'])) {
        $playlistId = $shortcodeOptions['playlist_id'];
      }

      for ($i = 0; $i < count($dzsap->mainitems); $i++) {

        if (isset($dzsap->mainitems[$i]) && isset($dzsap->mainitems[$i]['settings'])) {

          if ((isset($playlistId)) && ($playlistId == $dzsap->mainitems[$i]['settings']['id'])) {
            $k = $i;
          }
        }
      }
      $its = $dzsap->mainitems[$k];
    }


  }


  function get_its_settings(&$its, $margs, $term_meta, $selected_term_id) {
    global $dzsap;

    $its_settings_default = array(
      'galleryskin' => 'skin-wave',
      'vpconfig' => 'default',
      'bgcolor' => 'transparent',
      'width' => '',
      'height' => '',
      'autoplay' => '',
      'autoplaynext' => 'on',
      'autoplay_next' => '',
      'menuposition' => 'bottom',
    );
    if ($dzsap->mainoptions['playlists_mode'] == 'normal') {
      $its_settings_default['id'] = $selected_term_id;
    }

    if (isset($its['settings']) == false) {
      $its['settings'] = array();
    }

    $its['settings'] = array_merge($its_settings_default, $its['settings']);


    if ($dzsap->mainoptions['playlists_mode'] == 'normal') {
      if (is_array($term_meta)) {

        foreach ($term_meta as $lab => $val) {
          if ($lab == 'autoplay_next') {

            $lab = 'autoplaynext';
          }
          $its['settings'][$lab] = $val;

        }
      }
    }
  }


  /**
   * @param array $singleItemInstance
   * @param string $pcmIdentifierId
   * @param string $pcmIdentifierSource
   * @return string
   */
  static function getPcmData($singleItemInstance = null, $pcmIdentifierId = '', $pcmIdentifierSource = '') {

    $lab_option_pcm = '';
    $stringPcm = '';

    if ($pcmIdentifierId) {
      $lab_option_pcm = 'dzsap_pcm_data_' . DZSZoomSoundsHelper::sanitize_toKey($pcmIdentifierId);
      $stringPcm = get_option($lab_option_pcm);
    }


    if ($pcmIdentifierSource && DzsapView::isPcmInvalid($stringPcm)) {
      $lab_option_pcm = 'dzsap_pcm_data_' . DZSZoomSoundsHelper::sanitize_toKey($pcmIdentifierSource);

      if (DZSZoomSoundsHelper::sanitize_toKey($pcmIdentifierSource)) {
        $stringPcm = get_option($lab_option_pcm);
      }
    }


    if ($singleItemInstance && DzsapView::isPcmInvalid($stringPcm)) {
      if (isset($singleItemInstance['linktomediafile'])) {
        if ($singleItemInstance['linktomediafile']) {
          $lab_option_pcm = 'dzsap_pcm_data_' . $singleItemInstance['linktomediafile'];
          $stringPcm = get_option($lab_option_pcm);
        }
      }
    }


    return $stringPcm;
  }

  static function isPcmInvalid($pcm) {
    return ($pcm == '' || $pcm == '[]' || strpos($pcm, ',') === false || $pcm === 'null');
  }

  /**
   * get meta used for shortcode from post id (WPPost)
   * @param WP_Post $it
   * @param $margs
   * @return array
   */
  static function getAudioItemMeta($it, $margs = array()) {

    $it_id = $it->ID;
    $audioSource = get_post_meta($it_id, DZSAP_META_WOOCOMMERCE_ZOOMSOUNDS_MP3, true);

    if (isset($margs['type']) && $margs['type'] == 'product') {
      if ($audioSource == '') {
        $aux = get_post_meta($it_id, '_downloadable_files', true);
        if ($aux && is_array($aux)) {
          $aux = array_values($aux);
          if (isset($aux[0]) && isset($aux[0]['file']) && strpos(strtolower($aux[0]['file']), '.mp3') !== false) {
            $audioSource = $aux[0]['file'];
          }
        }
      }
    }

    $type = 'audio';


    if ($margs['type'] == DZSAP_REGISTER_POST_TYPE_NAME) {
      $audioSource = get_post_meta($it_id, DZSAP_META_ITEM_OPTION_PREFIX . 'source', true);
      $type = get_post_meta($it_id, DZSAP_META_OPTION_PREFIX . 'type', true);
    }

    if ($margs['type'] == 'attachment') {
      $audioSource = $it->guid;
    }

    $buy_link = site_url() . '/cart/?add-to-cart=' . $it_id;


    $buy_link = DZSHelpers::remove_query_arg(dzs_curr_url(), '0');
    $buy_link = DZSHelpers::remove_query_arg($buy_link, 'dzswtl_action');
    $buy_link = add_query_arg(array(
      'add-to-cart' => $it_id

    ), $buy_link);

    if (strpos($buy_link, '?') === false) {
      $buy_link = str_replace('&add-to-cart', '?add-to-cart', $buy_link);
    }

    if (get_post_meta($it_id, 'dzsap_woo_custom_link', true)) {
      $buy_link = get_post_meta($it_id, 'dzsap_woo_custom_link', true);
    }


    $price = get_post_meta($it_id, DZSAP_META_ITEM_OPTION_PREFIX . 'price', true);

    if ($margs['type'] == 'product') {
      if (get_post_meta($it_id, '_regular_price', true)) {
        $price = '';
        if (function_exists('get_woocommerce_currency_symbol')) {
          $price .= get_woocommerce_currency_symbol();
        }
        $price .= get_post_meta($it_id, '_regular_price', true);
      }
    }


    $thumb_url = wp_get_attachment_image_src(get_post_thumbnail_id($it_id), 'large');
    if (is_array($thumb_url) && isset($thumb_url[0])) {
      $thumb_url = $thumb_url[0];
    }
    if ($margs['type'] == 'attachment' && get_post_meta($it_id, '_dzsap-thumb', true)) {
      $thumb_url = get_post_meta($it_id, '_dzsap-thumb', true);
    }

    $user_info = get_userdata($it->post_author);
    $artistName = $user_info->data->display_name;


    if (isset($user_info->user_nicename) && $user_info->user_nicename) {
      $artistName = $user_info->user_nicename;
    } else {

      if (isset($user_info->first_name) && $user_info->first_name) {
        $artistName = $user_info->last_name . " " . $user_info->first_name;
      } else {
        if (isset($user_info->user_login) && $user_info->user_login) {
          $artistName = $user_info->user_login;
        }
      }
    }

    if (get_post_meta($it_id, DZSAP_META_OPTION_PREFIX . 'artistname', true)) {
      $artistName = get_post_meta($it_id, DZSAP_META_OPTION_PREFIX . 'artistname', true);
    }
    if (get_post_meta($it_id, DZSAP_META_OPTION_PREFIX . 'replace_artistname', true)) {
      $artistName = get_post_meta($it_id, DZSAP_META_OPTION_PREFIX . 'replace_artistname', true);
    }

    if ($artistName == 'none') {
      $artistName = '';
    }


    return array(
      'audioSource' => $audioSource,
      'buy_link' => $buy_link,
      'type' => $type,
      'price' => $price,
      'thumb_url' => $thumb_url,
      'artistName' => $artistName,
    );


  }

}