<?php

include_once(DZSAP_BASE_PATH . 'inc/php/view-parseItems/structure/item-structure.php');


function dzsap_view_parseItems__normalize(&$singleItemInstance, &$fakeplayer_attr, $its, &$thumb_link_attr) {


  if (isset($singleItemInstance['replace_songname']) && $singleItemInstance['replace_songname']) {
    $singleItemInstance['songname'] = $singleItemInstance['replace_songname'];
  }

  // -- try to compute songname
  if ($singleItemInstance['songname'] == 'default' || $singleItemInstance['songname'] == '{{id3}}') {
    $compute_songName = DZSZoomSoundsHelper::view_getSongNameFromComputed($singleItemInstance['source'], $singleItemInstance);
    if ($compute_songName) {
      $singleItemInstance['songname'] = $compute_songName;
    }
  }

  if ($singleItemInstance['songname'] == 'default') {
    $singleItemInstance['songname'] = '';
  }


  if ($singleItemInstance['artistname'] == 'none') {
    $singleItemInstance['artistname'] = '';
  }


  if ($singleItemInstance['songname'] == 'none') {
    $singleItemInstance['songname'] = '';
  }


  if ($singleItemInstance['artistname'] == 'default') {
    $singleItemInstance['artistname'] = '';
  }


  if ($singleItemInstance['songname'] == 'default' || $singleItemInstance['songname'] == '{{id3}}') {
    $singleItemInstance['songname'] = '';
  }


  if (isset($singleItemInstance['player_id']) && $singleItemInstance['player_id'] == DZSAP_VIEW_STICKY_PLAYER_ID) {
    $singleItemInstance['menu_artistname'] = ' ';
    $singleItemInstance['menu_songname'] = ' ';
  }


  $singleItemInstance['thumb'] = DZSZoomSoundsHelper::getThumbnailFromItemInstance($singleItemInstance);


  if (isset($singleItemInstance['play_in_footer_player']) && ($singleItemInstance['play_in_footer_player'] == 'default' || $singleItemInstance['play_in_footer_player'] === '')) {
    $singleItemInstance['play_in_footer_player'] = 'off';

  }
  if (isset($its['settings']['gallery_play_in_footer_player']) && $its['settings']['gallery_play_in_footer_player'] == 'on') {
    $singleItemInstance['play_in_footer_player'] = $its['settings']['gallery_play_in_footer_player'];
  }


  if (isset($singleItemInstance['play_in_footer_player']) && $singleItemInstance['play_in_footer_player'] == 'on') {
    $fakeplayer_attr = ' data-fakeplayer=".' . DZSAP_VIEW_STICKY_PLAYER_ID . '"';
  };


  if (isset($singleItemInstance['faketarget']) && $singleItemInstance['faketarget']) {
    $fakeplayer_attr = ' data-fakeplayer="' . $singleItemInstance['faketarget'] . '"';
  }


  if (isset($singleItemInstance['thumb_link']) && $singleItemInstance['thumb_link']) {
    $thumb_link_attr .= ' data-thumb_link="' . $singleItemInstance['thumb_link'] . '"';
  };


  if (isset($_COOKIE['dzsap_ratesubmitted-' . $singleItemInstance['playerId_computed']])) {
    $singleItemInstance['menu_extrahtml'] = str_replace('download-after-rate', 'download-after-rate active', $singleItemInstance['menu_extrahtml']);
  } else {
    if (isset($_COOKIE['commentsubmitted-' . $singleItemInstance['playerId_computed']])) {
      $singleItemInstance['menu_extrahtml'] = str_replace('download-after-rate', 'download-after-rate active', $singleItemInstance['menu_extrahtml']);
    };
  }

}

/**
 * returns only the html5 gallery items
 * @param $its
 * @param array $argSinglePlayerOptions - playerShortcode and Settings
 * @param array $argPlaylistOptions
 * @param DzsapView $dzsapView
 * @return string
 */
function dzsap_view_parseItems($its, $argSinglePlayerOptions = array(), $argPlaylistOptions = array(), $dzsapView = null) {

  // --

  global $post;

  $dzsap = $dzsapView->dzsap;


  $fout = '';
  $start_nr = 0; // -- the i start nr
  $end_nr = 0; // --  the i start nr

  $singlePlayerOptions = array(
    'menu_facebook_share' => 'auto',
    'menu_like_button' => 'auto',
    'gallery_skin' => 'skin-wave',
    'called_from' => 'skin-wave',
    'skinwave_mode' => 'normal',
    'is_single' => 'off',
    'auto_init_player' => 'off',
    'auto_init_player_options' => '',
    'wrapper_image' => '',
    'extraattr' => '',
    'extra_classes' => '',
    'wrapper_image_type' => '', // zoomsounds-wrapper-bg-bellow or zoomsounds-wrapper-bg-center ( set in item options )
  );

  $playlistOptions = null;

  if ($argPlaylistOptions && is_array($argPlaylistOptions) && count($argPlaylistOptions)) {
    $playlistOptions = $argPlaylistOptions;
  }

  $singlePlayerOptions = array_merge($singlePlayerOptions, $argSinglePlayerOptions);
  $vpConfig = $its['playerConfigSettings'];


  // -- count
  foreach ($its as $key => $val) {
    if (is_numeric($key)) {
      $end_nr++;
    }
  }


  dzsap_view_parseItemsInitialSettingsSetup($its, $singlePlayerOptions);

  if ($singlePlayerOptions['called_from'] == 'gallery') {
  }


  for ($i = $start_nr; $i < $end_nr; $i++) {


    $i_fout = '';
    $singleItemInstance = array(
      'menu_artistname' => 'default',
      'menu_songname' => 'default',
      'menu_extrahtml' => '',
      'extra_html' => '',
      'called_from' => '',
      'songname' => '',
      'artistname' => '',
      'show_tags' => 'off',
      'playerid' => '', // -- playerid for database *deprecated .. transition to wpPlayerPostId
      'wpPlayerPostId' => '', // --  database id
    );

    /** might be fake @var number | string $computedPlayerId */
    $computedPlayerId = '';
    $isPlayerIdFake = false; // -- if we assign a random number here , then it is fake

    if (!is_array($its[$i])) {
      $its[$i] = array();
    }

    $singleItemInstance = array_merge($singleItemInstance, $its[$i]);


    if (!isset($singleItemInstance['source']) || $singleItemInstance['source'] == '' || $singleItemInstance['source'] == ' ') {
      if (isset($singleItemInstance['item_source'])) {
        $singleItemInstance['source'] = $singleItemInstance['item_source'];
      }
    }
    if (!isset($singleItemInstance['thumb']) || $singleItemInstance['thumb'] == '') {
      if (isset($singleItemInstance['item_thumb'])) {
        $singleItemInstance['thumb'] = $singleItemInstance['item_thumb'];
      }
    }
    DZSZoomSoundsHelper::player_parseItems_generateSinglePlayerIds($isPlayerIdFake, $singleItemInstance, $singlePlayerOptions);


    $singleItemInstance = DZSZoomSoundsHelper::sanitize_item_for_parse_items($i, $singleItemInstance, $its);

    if ($singleItemInstance['show_tags'] == 'on') {
      $i_fout .= DZSZoomSoundsHelper::player_parseItems_generateTags($singleItemInstance['playerId_computed']);
    }


    $type = 'audio';
    if (isset($singleItemInstance['type']) && $singleItemInstance['type'] != '') {
      $type = $singleItemInstance['type'];
    }

    if ($type == 'inline') {
      continue;
    }


    if ($singleItemInstance['source'] == '' || $singleItemInstance['source'] == ' ') {
      continue;
    }


    if (isset($_GET['fromsharer']) && $_GET['fromsharer'] == 'on') {
      if (isset($_GET['audiogallery_startitem_ag1']) && $_GET['audiogallery_startitem_ag1']) {
        if ($i == $_GET['audiogallery_startitem_ag1']) {
          $dzsap->og_data = array(
            'title' => $singleItemInstance['menu_songname'],
            'image' => $singleItemInstance['thumb'],
            'description' => esc_html__("by", DZSAP_ID) . ' ' . $singleItemInstance['menu_artistname'],
          );
        }
      }
    }

    if (strpos($singleItemInstance['source'], 'soundcloud.com') !== false) {
      if (isset($singleItemInstance['soundcloud_track_id']) && isset($singleItemInstance['soundcloud_secret_token']) && $singleItemInstance['soundcloud_track_id'] && $singleItemInstance['soundcloud_secret_token']) {
        $singleItemInstance['source'] = DZSZoomSoundsHelper::get_soundcloud_track_source($singleItemInstance);
        if ($type == 'soundcloud') {
          $type = 'audio';
        }
      }
    }


    if (isset($its['playerConfigSettings'])) {
      $singleItemInstance['extra_html'] = DZSZoomSoundsHelper::parseItemDetermineExtraHtml($singleItemInstance['extra_html'], $its['playerConfigSettings']);
    }


    $singleItemInstance['extra_html'] = do_shortcode(dzsap_sanitize_from_extra_html_props($singleItemInstance['extra_html'], '', $singleItemInstance));


    if ($singleItemInstance['playerId_computed']) {
      if (isset($singleItemInstance['itunes_link']) && $singleItemInstance['itunes_link']) {

      } else {
        if ($singleItemInstance['wpPlayerPostId']) {
          if (get_post_meta($singleItemInstance['wpPlayerPostId'], 'dzsap_meta_itunes_link', true)) {
            $singleItemInstance['itunes_link'] = get_post_meta($singleItemInstance['wpPlayerPostId'], 'dzsap_meta_itunes_link', true);

          }

        }
      }
    }


    $extraHtmlInBottomControls = '';


    if ((isset($singleItemInstance['extra_html_in_bottom_controls']) && $singleItemInstance['extra_html_in_bottom_controls'])) {
      $extraHtmlInBottomControls = ($singleItemInstance['extra_html_in_bottom_controls']);
    }
    if ((isset($singleItemInstance['extrahtml_in_bottom_controls_from_player']) && $singleItemInstance['extrahtml_in_bottom_controls_from_player'])) {
      $extraHtmlInBottomControls .= ($singleItemInstance['extrahtml_in_bottom_controls_from_player']);
    }


    if ($extraHtmlInBottomControls) {
      $singleItemInstance['extra_html'] .= dzs_esc__(do_shortcode(dzsap_sanitize_from_extra_html_props($extraHtmlInBottomControls, '', $singleItemInstance)));
    }


    // -- we are going to now show non public tracks
    if ($dzsap->mainoptions['show_only_published'] == 'on') {
      if (isset($singleItemInstance['ID']) && $singleItemInstance['ID']) {
        if (($singleItemInstance['post_type'] != DZSAP_REGISTER_POST_TYPE_NAME) && get_post_status($singleItemInstance['ID']) !== 'publish') {
          continue;
        }
      }
    }


    // -- player


    $vpConfigId = DZSAP_DEFAULT_ZOOMSOUNDS_CONFIG;


    if (isset($its['playerConfigSettings']) && isset($its['playerConfigSettings']['id'])) {
      $vpConfigId = $its['playerConfigSettings']['id'];
    }


    dzsap_view_parseItemsAddFooterExtraStyling($dzsapView, $vpConfigId, $vpConfig, $its);

    $str_tw = '';
    if (isset($singlePlayerOptions['single']) && $singlePlayerOptions['single'] == 'on') {
      if (isset($singlePlayerOptions['width']) && isset($singlePlayerOptions['height'])) {
        // -- some sanitizing
        $tw = $singlePlayerOptions['width'];
        if ($tw != '') {
          if (strpos($tw, "%") === false && $tw != 'auto') {
            $str_tw = ' width: ' . $tw . 'px;';
          } else {
            $str_tw = ' width: ' . $tw . ';';
          }
        }
      }
    }


    $thumb_link_attr = '';
    $fakeplayer_attr = '';
    $meta_artist_html = '';
    $thumb_for_parent_attr = '';

    $pcmString = '';


    $skinwaveMode = DZSZoomSoundsHelper::viewGetScrubModeWaveMode($dzsap, $vpConfig);

    // -- we get data-pmc here
    if ($skinwaveMode == 'canvas') {
      $pcmString = $dzsapView->generate_pcm($singleItemInstance);
    }


    dzsap_view_parseItems__normalize($singleItemInstance, $fakeplayer_attr, $its, $thumb_link_attr);

    $audioplayerClasses = dzsap_view_parseItemsInitialClassSetup($its, $i, $post, $singleItemInstance, $singlePlayerOptions);


    $i_fout .= dzsap_view_parseItems__generateStructure($its, $singleItemInstance, $audioplayerClasses, $singlePlayerOptions, $str_tw, $dzsap, $thumb_for_parent_attr, $thumb_link_attr, $pcmString, $type, $fakeplayer_attr, $meta_artist_html, $playlistOptions, $isPlayerIdFake);

    if (isset($singleItemInstance['apply_script'])) {
    }


    if (isset($its['settings']) && $its['settings']['skin_ap'] && ($its['settings']['skin_ap'] == 'skin-customhtml')) {
      $i_fout = dzsap_view_player_generateCustomHtml($its, $singlePlayerOptions, $meta_artist_html, $pcmString, $fakeplayer_attr, $thumb_for_parent_attr, $thumb_link_attr);
    }

    $fout .= $i_fout;


    if ($fakeplayer_attr) {

      wp_enqueue_script('dzsap-player-' . 'change-media', DZSAP_BASE_URL . 'audioplayer/parts/player/change-media/change-media.js');
    }

  }


  return $fout;
}


function dzsap_view_parseItemsInitialClassSetup($its, $i, $post, $singleItemInstance, $singlePlayerOptions) {

  $audioplayerClasses = 'audioplayer-tobe ';


  $str_post_id = '';

  if ($post) {
    $str_post_id = '_' . $post->ID;
  }


  $audioplayerClasses .= ' playerid-' . $singleItemInstance['playerId_computed'];


  if (isset($its[$i]['player_index']) && $its[$i]['player_index']) {
    $audioplayerClasses .= ' ap_idx' . $str_post_id . '_' . $its[$i]['player_index'];
  }

  if (isset($singlePlayerOptions['is_single']) && $singlePlayerOptions['is_single'] == 'on') {
    $audioplayerClasses .= ' is-single-player';
  }


  if (isset($singleItemInstance['source'])) {
    if (strpos($singleItemInstance['source'], '{{generatenonce}}') !== false) {


    }
  }


  if ($singlePlayerOptions['called_from'] === 'footer_player') {
    $audioplayerClasses .= ' ' . DZSAP_VIEW_STICKY_PLAYER_ID;
  }

  if ($its && $its['settings'] && isset($its['settings']['vpconfig']) && $its['settings']['vpconfig']) {
    $aux = DZSZoomSoundsHelper::string_sanitizeToCssClass($its['settings']['vpconfig']);
    $audioplayerClasses .= ' apconfig-' . $aux;


    if (isset($singlePlayerOptions['skin_ap']) && $singlePlayerOptions['skin_ap']) {


      if ($singlePlayerOptions['called_from'] == 'gallery') {

        $audioplayerClasses .= ' ' . $singlePlayerOptions['skin_ap'];
      }


    }


    if (isset($its['settings']['button_aspect']) && $its['settings']['button_aspect'] != 'default') {
      $audioplayerClasses .= ' ' . $its['settings']['button_aspect'];

      if (isset($its['settings']['colorhighlight']) && $its['settings']['colorhighlight']) {
        // TODO: maybe force aspect noir filled ? if aspect noir is set


      }
    }
  }


  if (isset($singleItemInstance['wrapper_image_type']) && $singleItemInstance['wrapper_image_type']) {

    $audioplayerClasses .= ' ' . $singleItemInstance['wrapper_image_type'];
  }


  if (isset($singlePlayerOptions['extra_classes_player'])) {
    $audioplayerClasses .= ' ' . $singlePlayerOptions['extra_classes_player'];
  }

  if ($singlePlayerOptions['called_from'] == 'footer_player' || $singlePlayerOptions['called_from'] == 'player' || $singlePlayerOptions['called_from'] == 'gallery') {


    $audioplayerClasses .= ' ' . $singlePlayerOptions['skin_ap'];
  }


  if (isset($singlePlayerOptions['enable_alternate_layout']) && $singlePlayerOptions['skinwave_mode'] == 'normal' && $singlePlayerOptions['enable_alternate_layout'] == 'on') {
    $audioplayerClasses .= ' alternate-layout';
  }

  if (isset($its['settings']['extra_classes_player'])) {
    $audioplayerClasses .= ' ' . $its['settings']['extra_classes_player'];
  }
  if (isset($its['settings']['skinwave_mode'])) {

    if ($singlePlayerOptions['skinwave_mode'] == 'alternate') {
      $audioplayerClasses .= ' alternate-layout';
    }
    if ($singlePlayerOptions['skinwave_mode'] == 'nocontrols') {
      $audioplayerClasses .= ' skin-wave-mode-nocontrols';
    }
  }

  $audioplayerClasses .= ' ap' . $singleItemInstance['playerId_computed'];

  if (isset($its['settings']) && isset($its['settings']['disable_volume']) && $its['settings']['disable_volume'] == 'on') {
    $audioplayerClasses .= ' disable-volume';
  }

  if (isset($singleItemInstance['extra_classes']) && $singleItemInstance['extra_classes']) {
    $audioplayerClasses .= ' ' . $singleItemInstance['extra_classes'];
  }
  if (isset($singleItemInstance['embedded']) && $singleItemInstance['embedded'] == 'on') {
    $audioplayerClasses .= ' ' . ' is-in-embed-player';
  }

  if (isset($singlePlayerOptions['auto_init_player']) && $singlePlayerOptions['auto_init_player'] == 'on') {
    $audioplayerClasses .= ' auto-init';
  }

  return $audioplayerClasses;
}

function dzsap_view_parseItems_embedAdditionalScripts($playerConfigSettings) {


  if (isset($playerConfigSettings['skinwave_comments_enable']) && $playerConfigSettings['skinwave_comments_enable'] === 'on') {


    wp_enqueue_script('dzsap-player-' . 'skinwave_comments_enable', DZSAP_BASE_URL . 'audioplayer/parts/helper-functions/helper-functions.js');
    wp_enqueue_script('dzsap-part-player-comments', DZSAP_URL_AUDIOPLAYER . 'parts/player/player-comments/player-comments.js');
    wp_enqueue_style('dzsap-part-player-comments', DZSAP_URL_AUDIOPLAYER . 'parts/player/player-comments/player-comments.css');
  }

  $SKINS_THAT_HAVE_SEPARATE_STYLE = array('skin-minimal', 'skin-justthumbandbutton', 'skin-default', 'skin-aria', 'skin-redlights', 'skin-steel', 'skin-minion', 'skin-silver', 'skin-pro');
  if (in_array($playerConfigSettings['skin_ap'], $SKINS_THAT_HAVE_SEPARATE_STYLE)) {
    wp_enqueue_style('dzsap-player-' . $playerConfigSettings['skin_ap'], DZSAP_BASE_URL . 'audioplayer/parts/player-skins/player-skin--' . $playerConfigSettings['skin_ap'] . '.css');
  }
  $SKINS_THAT_HAVE_SEPARATE_SCRIPT = array('skin-minimal', 'skin-minion');
  if (in_array($playerConfigSettings['skin_ap'], $SKINS_THAT_HAVE_SEPARATE_SCRIPT)) {
    wp_enqueue_script('dzsap-player-' . $playerConfigSettings['skin_ap'], DZSAP_BASE_URL . 'audioplayer/parts/player-skins/player-skin--' . $playerConfigSettings['skin_ap'] . '.js');
  }

}

/**
 * @param DzsapView $dzsapView
 * @param string $vpConfigId
 * @param array $vpConfig
 * @param array $its
 */
function dzsap_view_parseItemsAddFooterExtraStyling($dzsapView, $vpConfigId, $vpConfig, $its) {
  global $dzsap;
  if (!in_array($vpConfigId, $dzsap->extraCssConsumedConfigurations)) {
    $dzsapView->footer_style .= '.audioplayer-tobe{  opacity:0; }';
    $dzsapView->footer_style .= dzsap_view_generateCssPlayerCustomColors(array(
      'configId' => $vpConfigId,
      'config' => $vpConfig,
    ));
    $dzsapView->footer_style .= '';


    if (isset($vpConfig['config_extra_css']) && $vpConfig['config_extra_css']) {

      if (in_array(DZSZoomSoundsHelper::sanitize_for_css_class($its['settings']['vpconfig']), $dzsapView->footer_style_configs) == false) {

        $vpConfig['config_extra_css'] = str_replace('$classmain', DZSAP_VIEW_APCONFIG_PREFIX . DZSZoomSoundsHelper::sanitize_for_css_class($vpConfigId), $vpConfig['config_extra_css']);
        $dzsapView->footer_style .= $vpConfig['config_extra_css'];

        array_push($dzsapView->footer_style_configs, DZSZoomSoundsHelper::sanitize_for_css_class($vpConfigId));
      }
    }
  }

}

function dzsap_view_parseItemsInitialSettingsSetup(&$its, &$playerOptions) {


  if (isset($playerOptions['single'])) {
    $playerOptions['is_single'] = $playerOptions['single'];
  }


  // -- sanitizing
  if ($playerOptions['wrapper_image'] == '') {
    if (isset($playerOptions['cover']) && $playerOptions['cover']) {
      $playerOptions['wrapper_image'] = $playerOptions['cover'];
    } else {
      $playerOptions['wrapper_image_type'] = '';
    }
  }


  if (isset($its['settings'])) {

    if (isset($its['settings']['enable_views']) == false) {
      $its['settings']['enable_views'] = 'off';
    }
    if (isset($its['settings']['enable_likes']) == false) {
      $its['settings']['enable_likes'] = 'off';
    }
    if (isset($its['settings']['enable_rates']) == false) {
      $its['settings']['enable_rates'] = 'off';
    }
    if (isset($its['settings']['enable_downloads_counter']) == false) {
      $its['settings']['enable_downloads_counter'] = 'off';
    }


    if (isset($playerOptions['enable_views']) && $playerOptions['enable_views'] === 'on') {
      $its['settings']['enable_views'] = 'on';
    }
    if (isset($playerOptions['enable_downloads_counter']) && $playerOptions['enable_downloads_counter'] === 'on') {
      $its['settings']['enable_downloads_counter'] = 'on';
    }

    if (isset($playerOptions['enable_likes']) && $playerOptions['enable_likes'] === 'on') {
      $its['settings']['enable_likes'] = 'on';
    }
    if (isset($playerOptions['enable_rates']) && $playerOptions['enable_rates'] === 'on') {
      $its['settings']['enable_rates'] = 'on';
    }
    if ($playerOptions['is_single'] == 'on' && isset($its['settings']['id']) && $its['settings']['id']) {
      $its['settings']['vpconfig'] = $its['settings']['id'];
    }


    if (isset($its['settings']['enable_alternate_layout']) && $its['settings']['enable_alternate_layout'] === 'on') {
      $playerOptions['enable_alternate_layout'] = 'on';
      $playerOptions['skinwave_mode'] = 'alternate';
    }
  }

  dzsap_view_parseItems_embedAdditionalScripts($its['playerConfigSettings']);


}
