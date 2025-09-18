<?php

function dzsap_player_generateButtonStats($settingsForParsePlayer, $shortcodePlayerAtts){
  return '<span class="btn-zoomsounds stats-btn" data-playerid="' . $settingsForParsePlayer['playerid'] . '"  data-sanitized_source="' . DZSZoomSoundsHelper::sanitize_toKey($shortcodePlayerAtts['source']) . '" data-url="' . dzs_curr_url() . '" ><span class="the-icon"><i class="fa fa-tachometer" aria-hidden="true"></i></span><span class="btn-label">' . esc_html__('Stats', DZSAP_ID) . '</span></span>';
}