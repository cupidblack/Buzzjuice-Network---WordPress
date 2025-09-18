<?php

function dzsap_player_generateItemSettingsFromShortcode($content, $argsShortcodePlayer) {

  global $dzsap;


  $dzsap->sliders__player_index++;
  $player_idx = $dzsap->sliders__player_index;


  $shortcodePlayerAtts = array_merge(DZSAP_VIEW_DEFAULT_SHORTCODE_PLAYER_ATTS, array(
    'player_index' => $player_idx,
  ));


  /** @var $defaultShortcodeAttributes  object for compare */
  $defaultShortcodeAttributes = array_merge(array(), $shortcodePlayerAtts);


  if (is_int(intval($shortcodePlayerAtts['source']))) {
    $associatedCpt = get_post($shortcodePlayerAtts['source']);

    if ($associatedCpt) {
      if ($associatedCpt->post_type == DZSAP_REGISTER_POST_TYPE_NAME) {
        $shortcodePlayerAtts['post_content'] = $associatedCpt->post_content;
      }

      $shortcodeAttsFromMeta = array();
      foreach (DZSAP_PLAYER_ATTRIBUTES as $PLAYER_ATTRIBUTE) {
        if (get_post_meta($associatedCpt->ID, DZSAP_META_OPTION_PREFIX . $PLAYER_ATTRIBUTE, true)) {
          $shortcodeAttsFromMeta[$PLAYER_ATTRIBUTE] = get_post_meta($associatedCpt->ID, DZSAP_META_OPTION_PREFIX . $PLAYER_ATTRIBUTE, true);
        }

      }

      $argsShortcodePlayer = array_merge($shortcodeAttsFromMeta, $argsShortcodePlayer);


    }
  }
  $shortcodePlayerAtts = array_merge($shortcodePlayerAtts, $argsShortcodePlayer);


  // -- processing


  if (isset($argsShortcodePlayer['source'])) {
    $shortcodePlayerAtts['source'] = DZSZoomSoundsHelper::player_parseItems_getSource($argsShortcodePlayer['source'], $argsShortcodePlayer);
  }


  if ($content) {
    $shortcodePlayerAtts['content_inner'] = $content;
  }


  if (isset($shortcodePlayerAtts['the_post_title']) && $shortcodePlayerAtts['the_post_title'] && (!($shortcodePlayerAtts['songname']))) {
    $shortcodePlayerAtts['songname'] = $shortcodePlayerAtts['the_post_title'];
  }


  if ($shortcodePlayerAtts['play_target'] == 'footer') {
    if (isset($shortcodePlayerAtts['faketarget']) && $shortcodePlayerAtts['faketarget']) {

    } else {
      $shortcodePlayerAtts['faketarget'] = '.' . DZSAP_VIEW_STICKY_PLAYER_ID;
    }
  }


  if ($shortcodePlayerAtts['source']) {
    if ($dzsap->get_track_source($shortcodePlayerAtts['source'], $playerid, $shortcodePlayerAtts) != $shortcodePlayerAtts['source']) {

      if (is_numeric($shortcodePlayerAtts['source'])) {
        if (!isset($shortcodePlayerAtts['playerid']) || $shortcodePlayerAtts['playerid'] == '') {
          $shortcodePlayerAtts['playerid'] = $shortcodePlayerAtts['source'];
        }
      }
      $shortcodePlayerAtts['source'] = $dzsap->get_track_source($shortcodePlayerAtts['source'], $playerid, $shortcodePlayerAtts);
    }
  }


  return array(
    'shortcodePlayerAtts' => $shortcodePlayerAtts,
    'defaultShortcodeAttributes' => $defaultShortcodeAttributes,
  );
}