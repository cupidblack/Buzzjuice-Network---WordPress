<?php


/**
 * @param DZSAudioPlayer $dzsap
 * @param array $its
 * @return string
 */
function dzsap_view_shortcode_playlist_generateEmbedCode($dzsap, $its) {


  $deprecatedStringDb = '';
  if ($dzsap->currDb != '') {
    $deprecatedStringDb = '&db=' . $dzsap->currDb . '';
  }
  if (isset($shortcodeOptions['playlist_id']) && $shortcodeOptions['playlist_id'] == DZSAP_VIEW_SHOWCASE_PLAYLIST_ID) {
    $str = '<iframe src="' . site_url() . '?action=zoomsounds-embedtype=playlist&ids=' . $shortcodeOptions['for_embed_ids'] . '' . $deprecatedStringDb . '" width="100%" height="' . $its['settings']['height'] . '" style="overflow:hidden; transition: height 0.5s ease-out;" scrolling="no" frameborder="0"></iframe>';
  } else {
    $str = '<iframe src="' . site_url() . '?action=zoomsounds-embed&type=gallery&id=' . $its['settings']['id'] . '' . $deprecatedStringDb . '" width="100%" height="' . $its['settings']['height'] . '" style="overflow:hidden; transition: height 0.5s ease-out;" scrolling="no" frameborder="0"></iframe>';
  }


  $str = str_replace('"', "'", $str);

  return htmlentities($str, ENT_QUOTES);
}

/**
 * @param string $content
 * @param string $skinGallery
 * @param array $vpsettings
 * @param array $shortcodeOptions
 * @param array $its
 * @param $videoPlaylistSettingsMerged
 * @param DzsapView $dzsapView
 * @return string
 */
function dzsap_view_shortcode_playlist_generateItemsOut($content, $skinGallery, $vpsettings, $shortcodeOptions, $its, $videoPlaylistSettingsMerged, $dzsapView) {

  $iout = '';

  if ($content) {
    $iout .= do_shortcode($content);
    return $iout;
  }

  $playerOptionsArgs = array(
    'called_from' => 'gallery',
    'gallery_skin' => $skinGallery,
  );
  $playerOptionsArgs = array_merge($vpsettings['settings'], $playerOptionsArgs);
  $playerOptionsArgs = array_merge($playerOptionsArgs, $shortcodeOptions);


  $playerOptionsArgs['called_from'] = 'gallery';


  if ($its['playerConfigSettings']['enable_embed_button'] === 'in_lightbox' || $its['playerConfigSettings']['enable_embed_button'] === 'in_extra_html') {


    $embed_code = DZSZoomSoundsHelper::generate_embed_code(array(
      'call_from' => 'shortcode_player',
      'playlistId' => $shortcodeOptions['playlist_id'],
    ), false);
    $playerOptionsArgs['feed_embed_code'] = $embed_code;
  }

  $videoPlaylistOptionsForParseItems = array_merge($videoPlaylistSettingsMerged, $shortcodeOptions);

  $iout .= dzsap_view_parseItems($its, $playerOptionsArgs, $videoPlaylistOptionsForParseItems, $dzsapView);

  return $iout;

}

/**
 * [zoomsounds_player source="pathto.mp3" artistname="" songname=""]
 * @param array $argsPlaylist
 * @param string $content
 * @param DzsapView $dzsapView
 * @param DzsAudioPlayer $dzsap
 * @return string
 */
function dzsap_view_shortcode_playlist($argsPlaylist = array(), $content = '', $dzsapView = null, $dzsap = null) {


  global $current_user;
  $fout = '';
  $iout = ''; //items parse

  $shortcodeOptions = array(
    'playlist_id' => 'default'
  , 'db' => ''
  , 'category' => ''
  , 'extra_classes' => ''
  , 'fullscreen' => 'off'
  , 'settings_separation_mode' => 'normal'  // === normal ( no pagination ) or pages or scroll or button
  , 'settings_separation_pages_number' => '5'//=== the number of items per 'page'
  , 'settings_separation_paged' => '0'//=== the page number
  , 'return_onlyitems' => 'off' // ==return only the items ( used by pagination )
  , 'embedded' => 'off'
  , 'divinsteadofscript' => 'off'
  , 'width' => '-1'
  , 'height' => '-1'
  , 'embedded_in_zoombox' => 'off'
  , 'for_embed_ids' => ''
  , 'is_single' => 'off'
  , 'overwrite_only_its' => ''
  , 'called_from' => 'default'
  , 'play_target' => 'default'
  );

  if ($argsPlaylist == '') {
    $argsPlaylist = array();
  }

  $shortcodeOptions = array_merge($shortcodeOptions, $argsPlaylist);

  if ((!$shortcodeOptions['playlist_id'] || $shortcodeOptions['playlist_id'] == 'default') && isset($shortcodeOptions['id']) && $shortcodeOptions['id']) {
    $shortcodeOptions['playlist_id'] = $shortcodeOptions['id'];
  }

  // -- the id will get replaced so we can store the original id / slug
  $shortcodeOptions['original_id'] = $shortcodeOptions['playlist_id'];


  // -- setting up the db
  $currDb = '';
  if (isset($shortcodeOptions['db']) && $shortcodeOptions['db'] != '') {
    $dzsap->currDb = $shortcodeOptions['db'];
    $currDb = $dzsap->currDb;
  }

  $dzsap->dbs = get_option(DZSAP_DBNAME_LEGACY_DBS);
  $dzsap->db_read_mainitems();


  // -- setting up the db END


  $dzsap->sliders_index++;


  $its = array(
    'settings' => array(),
  );
  $selected_term_id = '';

  $term_meta = array();


  if ($shortcodeOptions['for_embed_ids']) {
    $shortcodeOptions['force_ids'] = $shortcodeOptions['for_embed_ids'];
  }
  $dzsapView->get_its_items($its, $shortcodeOptions);

  if ($dzsap->mainoptions['playlists_mode'] == 'normal') {
    $tax = DZSAP_TAXONOMY_NAME_SLIDERS;


    $reference_term = get_term_by('slug', $shortcodeOptions['playlist_id'], $tax);

    if ($reference_term) {

    } else {
      // -- reference term does not exist..

      $directores = get_terms(DZSAP_TAXONOMY_NAME_SLIDERS);

      $playerOptionsArgs = $shortcodeOptions;
      $playerOptionsArgs['playlist_id'] = $directores[0]->slug;
      if ($shortcodeOptions['called_from'] != 'redo') {
        $playerOptionsArgs['called_from'] = 'redo';
        return $dzsapView->shortcode_playlist_main($playerOptionsArgs);
      }
      return '';
    }


    $selected_term_id = $reference_term->term_id;

    $term_meta = get_option("taxonomy_$selected_term_id");
  }


  if ($shortcodeOptions['overwrite_only_its'] && is_array($shortcodeOptions['overwrite_only_its'])) {


    $new_its = array_merge(array(), $its);
    foreach ($its as $lab => $val) {
      if ($lab !== 'settings') {
        unset($new_its[$lab]);
      }
    }
    $new_its = array_merge($new_its, $shortcodeOptions['overwrite_only_its']);

    $its = $new_its;
  }


  $dzsapView->get_its_settings($its, $shortcodeOptions, $term_meta, $selected_term_id);
  // -- after settings


  $i = 0;

  $vpsettings = DZSZoomSoundsHelper::getVpSettings($its['settings']['vpconfig']);
  $sanitizedApConfigId = DZSZoomSoundsHelper::sanitizeToValidObjectLabel($vpsettings['settings']['id']);


  unset($vpsettings['settings']['id']);
  $its['settings'] = array_merge($its['settings'], $vpsettings['settings']);
  $its['playerConfigSettings'] = ($vpsettings['settings']);
  $its['playerConfigSettings']['id'] = $sanitizedApConfigId;


  $dzsapView->playlist_initialSetup($its);


  // -- some sanitizing
  $tw = $its['settings']['width'];
  $th = $its['settings']['height'];

  if ($shortcodeOptions['width'] != '-1') {
    $tw = $shortcodeOptions['width'];
  }
  if ($shortcodeOptions['height'] != '-1') {
    $th = $shortcodeOptions['height'];
  }
  $str_tw = '';
  $str_th = '';


  if ($tw != '') {
    $str_tw .= 'width: ';
    $str_tw .= DZSZoomSoundsHelper::sanitizeToPx($tw);
    $str_tw .= ';';
  }


  if ($th != '') {
    $str_th .= 'height: ';
    $str_tw .= DZSZoomSoundsHelper::sanitizeToPx($th);
    $str_th .= ';';
  }


  $skinGallery = 'skin-wave';

  if (isset($its['settings']['galleryskin'])) {
    $skinGallery = $its['settings']['galleryskin'];
  }


  $sanitizedApConfigId = DZSZoomSoundsHelper::sanitizeToValidObjectLabel($its['playerConfigSettings']['id']);

  $newSettings = array();
  if (isset($its['settings']['autoplaynext'])) {
    $newSettings['autoplay_next'] = $its['settings']['autoplaynext'];
  }
  $newSettings['embedded'] = $shortcodeOptions['embedded'];
  $newSettings['settings_ap'] = $sanitizedApConfigId;


  $videoPlaylistSettingsMerged = array_merge($its['settings'], $newSettings);


  if (isset($videoPlaylistSettingsMerged['settings_mode_showall_show_number'])) {
    if ($videoPlaylistSettingsMerged['settings_mode_showall_show_number'] && $videoPlaylistSettingsMerged['settings_mode_showall_show_number'] == 'on') {
      wp_enqueue_script('isotope', DZSAP_BASE_URL . 'libs/isotope/isotope.js');
    }
  }


  if (isset($its['settings']['settings_enable_linking'])) {
    if (isset($videoPlaylistSettingsMerged['enable_linking']) === false || $videoPlaylistSettingsMerged['enable_linking'] === '') {
      $videoPlaylistSettingsMerged['enable_linking'] = $its['settings']['settings_enable_linking'];
    }
  }

  if (isset($_GET['fromsharer']) && $_GET['fromsharer'] == 'on') {
    if (isset($_GET['audiogallery_startitem_ag1']) && $_GET['audiogallery_startitem_ag1'] !== '') {
      $videoPlaylistSettingsMerged['design_menu_state'] = 'closed';
    }
  }


  // -- playlist
  if (isset($its['playerConfigSettings']['colorhighlight']) && $its['playerConfigSettings']['colorhighlight']) {

    $audioGalleryCustomColorsCss = dzsap_view_generateCssPlayerCustomColors(array(
      'skin_ap' => $its['playerConfigSettings']['skin_ap'],
      'selector' => '.audiogallery#ag' . $dzsap->sliders_index . ' .audioplayer',
      'colorhighlight' => $its['playerConfigSettings']['colorhighlight'],
    ));
    wp_register_style('dzsap-hook-gallery-custom-styles', false);
    wp_enqueue_style('dzsap-hook-gallery-custom-styles');
    wp_add_inline_style('dzsap-hook-gallery-custom-styles', $audioGalleryCustomColorsCss);
  }


  if (isset($its['settings']['enable_bg_wrapper']) && $its['settings']['enable_bg_wrapper'] == 'on') {
    $fout .= '<div class="ap-wrapper"><div class="the-bg"></div>';
  }

  // -- main gallery div
  $fout .= '<div   id="ag' . $dzsap->sliders_index . '" class="audiogallery ag_slug_' . $shortcodeOptions['original_id'] . ' auto-init ' . $skinGallery . ' id_' . $its['settings']['id'] . ' ';


  if ($shortcodeOptions['extra_classes']) {
    $fout .= ' ' . $shortcodeOptions['extra_classes'];
  }


  $fout .= '" style="background-color:' . $its['settings']['bgcolor'] . ';' . $str_tw . '' . $str_th . '" data-options=\'' . json_encode(dzsap_generate_javascript_setting_for_playlist($videoPlaylistSettingsMerged)['foutArr']) . '\'>';

  $iout = dzsap_view_shortcode_playlist_generateItemsOut($content, $skinGallery, $vpsettings, $shortcodeOptions, $its, $videoPlaylistSettingsMerged, $dzsapView);


  $fout .= '<div class="items">';
  $fout .= $iout;


  $fout .= '</div>';
  $fout .= '</div>'; // -- end .audiogallery


  if (isset($its['settings']['enable_bg_wrapper']) && $its['settings']['enable_bg_wrapper'] == 'on') {
    $fout .= '</div>';
  }


  $playerSettingsFromGallery = array();


  if (isset($its['playerConfigSettings']['enable_embed_button']) && ($its['playerConfigSettings']['enable_embed_button'] != 'off')) {
    $playerSettingsFromGallery['embed_code'] = dzsap_view_shortcode_playlist_generateEmbedCode($dzsap, $its);
  }


  if (isset($its['settings']['enable_embed_button']) && ($its['settings']['enable_embed_button'] == 'on' || $vpsettings['settings']['enable_embed_button'] == 'in_player_controls')) {
    $playerSettingsFromGallery['enable_embed_button'] = 'on';
  }


  $dzsap->mainoptions['color_waveformbg'] = str_replace('#', '', $dzsap->mainoptions['color_waveformbg']);
  if ($dzsap->mainoptions['skinwave_wave_mode'] == 'canvas') {

    $playerSettingsFromGallery['pcm_data_try_to_generate'] = $dzsap->mainoptions['pcm_data_try_to_generate'];
    $playerSettingsFromGallery['pcm_notice'] = $dzsap->mainoptions['pcm_notice'];
    $playerSettingsFromGallery['notice_no_media'] = $dzsap->mainoptions['notice_no_media'];

  }


  $audioplayerSettingsMerged = array_merge($its['playerConfigSettings'], $playerSettingsFromGallery);

  $dzsapView->audioPlayerConfigsAdd($sanitizedApConfigId, dzsap_generate_javascript_setting_for_player($audioplayerSettingsMerged)['foutArr']);

  DZSZoomSoundsHelper::enqueueFontAwesome();


  // -- this fixes some & being converted to &#038;
  remove_filter('the_content', 'wptexturize');

  DZSZoomSoundsHelper::enqueueMainScrips();
  DZSZoomSoundsHelper::enqueueMainScrips('audioplaylist');

  if ($shortcodeOptions['return_onlyitems'] == 'on') {
    return $iout;
  }
  return $fout;
}
