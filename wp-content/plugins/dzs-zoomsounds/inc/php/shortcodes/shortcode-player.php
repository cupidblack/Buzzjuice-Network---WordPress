<?php
include_once DZSAP_BASE_PATH . 'inc/php/view-functions/player/view-special-buttons.php';
include_once DZSAP_BASE_PATH . 'inc/php/player/player-shortcode-functions.php';

/**
 * [zoomsounds_player source="pathto.mp3" artistname="" songname=""]
 * @param array $argsShortcodePlayer
 * @param string $content
 * @param DzsAudioPlayer $dzsap
 * @return string
 */
function dzsap_view_shortcode_player($argsShortcodePlayer = array(), $content = '', $dzsap = null) {

  global $post;

  $associatedCpt = null;
  $fout = '';

  // -- initial sanitize
  if (!(isset($argsShortcodePlayer) && is_array($argsShortcodePlayer))) {
    $argsShortcodePlayer = array();
  }



  DZSZoomSoundsHelper::enqueueMainScrips();




  $shortcodeSettings = dzsap_player_generateItemSettingsFromShortcode($content, $argsShortcodePlayer);

  $defaultShortcodeAttributes = $shortcodeSettings['defaultShortcodeAttributes'];
  $shortcodePlayerAtts = $shortcodeSettings['shortcodePlayerAtts'];


  $original_player_margs = array_merge($shortcodePlayerAtts, array());
  $original_source = $shortcodePlayerAtts['source'];


  $embedMargs = array();


  // -- embed margs
  foreach ($shortcodePlayerAtts as $lab => $shortcodePlayerAtt) {
    if (isset($shortcodePlayerAtt)) {
      if (!isset($defaultShortcodeAttributes[$lab]) || $shortcodePlayerAtt !== $defaultShortcodeAttributes[$lab]) {
        $embedMargs[$lab] = $shortcodePlayerAtt;
      }
    }
  }
  if (isset($embedMargs['cat_feed_data'])) {
    unset($embedMargs['cat_feed_data']);
  }


  $playerid = '';


  $player_post = null;



  $configSettings = DZSZoomSoundsHelper::getVpSettings($shortcodePlayerAtts['config'], $shortcodePlayerAtts);


  if (isset($shortcodePlayerAtts['embedded']) && $shortcodePlayerAtts['embedded'] == 'on') {

    $configSettings['enable_embed_button'] = 'off';
    $configSettings['menu_right_enable_multishare'] = 'off';
  }


  if (!(isset($shortcodePlayerAtts['playerid']) && $shortcodePlayerAtts['playerid'])) {
    $shortcodePlayerAtts['playerid'] = DZSZoomSoundsHelper::media_getPlayerId($shortcodePlayerAtts);
  }


  if ($configSettings['settings']['skin_ap'] == 'null') {
    $configSettings['settings']['skin_ap'] = 'skin-wave';
  }


  // -- start its
  $its = array(
    0 => $shortcodePlayerAtts,
    'settings' => $configSettings['settings'],
    'playerConfigSettings' => $configSettings['settings']
  );


  if ($shortcodePlayerAtts['enable_views'] == 'on') {
    $its['settings']['enable_views'] = 'on';
  }


  $settingsForParsePlayer = array_merge($configSettings['settings'], $shortcodePlayerAtts);


  // -- lets overwrite some settings that we forced from shortcode args


  if (isset($argsShortcodePlayer['enable_embed_button']) && $argsShortcodePlayer['enable_embed_button']) {

    $settingsForParsePlayer['enable_embed_button'] = $argsShortcodePlayer['enable_embed_button'];
  }


  if (isset($settingsForParsePlayer['cat_feed_data'])) {
    include_once DZSAP_BASE_PATH . "class_parts/powerpress_cat_feed_data.php";
  }


  $settingsForParsePlayer['extra_html'] = DZSZoomsoundsHelper::sanitizeForShortcodeAttr($settingsForParsePlayer['extra_html'], $settingsForParsePlayer);

  $encodedMargs = base64_encode(json_encode($embedMargs));


  $embed_code = '';


  if ($encodedMargs) {

    // -- if it not in a loop
    if (is_single()) {

      if (isset($settingsForParsePlayer['enable_embed_button']) && ($settingsForParsePlayer['enable_embed_button'] == 'in_lightbox' || $settingsForParsePlayer['enable_embed_button'] == 'in_extra_html')) {

        $embed_code = DZSZoomSoundsHelper::generate_embed_code(array(
          'call_from' => 'shortcode_player',
          'enc_margs' => $encodedMargs,
        ));
        $settingsForParsePlayer['feed_embed_code'] = $embed_code;
      }
    }
  }

  $settingsForParsePlayer['embed_code'] = $embed_code;


  if ($settingsForParsePlayer['itunes_link']) {
    if (!isset($its[0]['extra_html'])) {
      $its[0]['extra_html'] = '';
    }
    $its[0]['extra_html'] .= '  <a rel="nofollow" href="' . $settingsForParsePlayer['itunes_link'] . '" target="_blank" class=" btn-zoomsounds btn-itunes "><span class="the-icon"><i class="fa fa-apple"></i></span><span class="the-label ">iTunes</span></a>';
  }


  $settingsForParsePlayer['the_content'] = $content;

  if ($settingsForParsePlayer['songname'] && $settingsForParsePlayer['songname'] != 'default') {

    if (!isset($its[0]['menu_songname']) || !($its[0]['menu_songname'] && $its[0]['menu_songname'] != 'default')) {

      $its[0]['menu_songname'] = $settingsForParsePlayer['songname'];
    }
  }
  if ($settingsForParsePlayer['artistname'] && $settingsForParsePlayer['artistname'] != 'default') {

    if (!isset($its[0]['menu_artistname']) || !($its[0]['menu_artistname'] && $its[0]['menu_artistname'] != 'default')) {

      $its[0]['menu_artistname'] = $settingsForParsePlayer['artistname'];
    }
  }


  $lab = 'title_is_permalink';
  if (isset($settingsForParsePlayer[$lab]) && $settingsForParsePlayer[$lab]) {
    $its[0][$lab] = $settingsForParsePlayer[$lab];
  }
  if (isset($settingsForParsePlayer['product_id']) && $settingsForParsePlayer['product_id']) {

    $pid = $settingsForParsePlayer['product_id'];

    if (get_post_meta($pid, 'dzsap_meta_replace_artistname', true)) {

      $its[0]['artistname'] = get_post_meta($pid, 'dzsap_meta_replace_artistname', true);
    }
  }


  $dzsapSettingsArrayString = dzsap_generate_audioplayer_settings(array(
    'call_from' => 'shortcode_player',
    'enc_margs' => $encodedMargs,
    'extra_init_settings' => $settingsForParsePlayer['extra_init_settings'],
  ), $configSettings, $its, $settingsForParsePlayer);


  if (!(isset($settingsForParsePlayer['open_in_ultibox']) && $settingsForParsePlayer['open_in_ultibox'] == 'on')) {


    if ($settingsForParsePlayer['init_player'] == 'on') {
      if ($dzsap->mainoptions['init_javascript_method'] != 'script') {
        $settingsForParsePlayer['auto_init_player'] = 'on';
      }
      $settingsForParsePlayer['auto_init_player_options'] = $dzsapSettingsArrayString;
    }


    // -- player
    $fout .= dzsap_view_parseItems($its, $settingsForParsePlayer, array(), $dzsap->classView);

  } else {

    $fout .= '<div   class="ultibox-item fullwidth" data-type="" data-source="' . $settingsForParsePlayer['source'] . '"  data-box-bg="#FFFFFF"  >';
    $fout .= $content;
    $fout .= '</div>';

    DZSZoomSoundsHelper::enqueueUltibox();
  }

  $player_id = $settingsForParsePlayer['playerid'];

  // -- normal mode
  if ($shortcodePlayerAtts['init_player'] == 'on') {
    DZSZoomSoundsHelper::enqueueMainScrips();
  }


  $extra_buttons_html = '';

  if ($dzsap->mainoptions['analytics_enable'] == 'on') {
    if (current_user_can(DZSAP_PERMISSION_ULTIMATE)) {
      if ($shortcodePlayerAtts['called_from'] != 'footer_player') {
        // -- the stats
        $extra_buttons_html .= dzsap_player_generateButtonStats($settingsForParsePlayer, $shortcodePlayerAtts);
        // -- some portal delete button : todo: complete
        DZSZoomSoundsHelper::enqueuePart('part-stats-btn');
      }
      DZSZoomSoundsHelper::enqueueAudioPlayerShowcase();
      wp_enqueue_style('fontawesome', DZSAP_URL_FONTAWESOME_EXTERNAL);
    }
  }

  if ($shortcodePlayerAtts['called_from'] != 'footer_player') {

    if (DZSZoomSoundsHelper::isTheTrackHasFromCurrentUser($settingsForParsePlayer['playerid'])) {

      $extra_buttons_html .= DZSZoomSoundsHelper::generateExtraButtonsForPlayerDeleteEdit($settingsForParsePlayer['playerid']);

    }
  }

  if ($extra_buttons_html && $shortcodePlayerAtts['called_from'] != 'playlist_showcase') {
    if ($dzsap->mainoptions['enable_aux_buttons'] === 'on') {
      $fout .= '<div class="extra-btns-con">';
      $fout .= $extra_buttons_html;
      $fout .= '</div>';
    }
  }


  // -- this fixes some & being converted to &#038;
  remove_filter('the_content', 'wptexturize');

  DZSZoomSoundsHelper::enqueueMainScrips();
  return $fout;
}
