<?php

include_once DZSAP_BASE_PATH.'inc/php/dzs-shared/utils_color.php';
/**
 * @param array $pargs
 * @return string
 * @throws Exception
 */
function dzsap_view_generateCssPlayerCustomColors($pargs = array()) {


  $margs = array(
    'colorhighlight' => '',
    'skin_ap' => '',
    'selector' => '', // -- used for playlists

    'config' => array(),
    'configId' => '',
    'call_from' => '',
  );

  $margs = array_merge($margs, $pargs);


  $colorUi = '';
  $colorUiAlternate = '';
  $colorHighlight = '';
  $colorText = '';
  $colorWaveColorBg = '';
  $colorWaveColorProg = '';

  $config = $margs['config'];
  if (isset($config['color_ui'])) {
    $colorUi = DZSZoomSoundsHelper::sanitizeToHexNonHash($config['color_ui']);
    if ($config['skin_ap'] == 'skin-wave' && $config['skinwave_mode'] == 'normal') {
      if (!(isset($config['color_ui_alternate']) && $config['color_ui_alternate'])) {
        $config['color_ui_alternate'] = dzs_hexInvert($config['color_ui']);
      }
    }
  }
  if (isset($config['color_ui_alternate'])) {
    $colorUiAlternate = DZSZoomSoundsHelper::sanitizeToHexNonHash($config['color_ui_alternate']);
  }
  if (isset($config['colorhighlight'])) {
    $colorHighlight = DZSZoomSoundsHelper::sanitizeToHexNonHash($config['colorhighlight']);
  }
  if (isset($config['color_text'])) {
    $colorText = DZSZoomSoundsHelper::sanitizeToHexNonHash($config['color_text']);
  }
  if (isset($config['design_wave_color_bg'])) {
    $colorWaveColorBg = DZSZoomSoundsHelper::sanitizeToHexNonHash($config['design_wave_color_bg']);
  }
  if (isset($config['design_wave_color_progress'])) {
    $colorWaveColorProg = DZSZoomSoundsHelper::sanitizeToHexNonHash($config['design_wave_color_progress']);
  }

  $fout = '';

  global $dzsap;


  array_push($dzsap->extraCssConsumedConfigurations, $margs['configId']);


  if (!$colorHighlight && !$colorUi && !$colorText && $colorUiAlternate) {
    return '';
  }


  $selectorApConfig = '.audioplayer' . DZSAP_VIEW_APCONFIG_PREFIX . DZSZoomSoundsHelper::string_sanitizeToCssClass($margs['configId']);

  $colorsFromColorsCss = '';


//  $colorsFromColorsCss .= '##CSS_AUDIOPLAYER_SELECTOR##:not(.scrubbar-type-wave) .ap-controls .scrubbar .scrub-bg{background-color:#828080} ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .meta-artist .the-name,##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .meta-artist .the-name>a,      ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir.button-aspect-noir--filled .playbtn .the-icon-bg,##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir.button-aspect-noir--filled .pausebtn .the-icon-bg,##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir.button-aspect-noir--filled i.svg-icon{color:#ddd} ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir.button-aspect-noir--filled .svg-icon path{fill:#ddd} ##CSS_AUDIOPLAYER_SELECTOR##.skin-aria .ap-controls .ap-controls-left .con-playpause{background-color:#996b99}##CSS_AUDIOPLAYER_SELECTOR##.skin-aria .ap-controls .ap-controls-left .con-playpause path{fill:#ddd}##CSS_AUDIOPLAYER_SELECTOR##.skin-aria .ap-controls .ap-controls-right{background-color:#e7e5e5}body .dzsap-color_inverse_ui_fill:not(.a) path{fill:#ddd}##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .svg-icon path{fill:#ddd} ';

  if ($colorUi) {
    $colorsFromColorsCss .= ' ##CSS_AUDIOPLAYER_SELECTOR## .player-but .svg-icon path{fill:--COLOR_UI_PLACEHOLDER--}        body .controls-volume.controls-volume-vertical .volume-holder{background-color:--COLOR_UI_PLACEHOLDER--;}    ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .player-but .the-icon-bg{background-color:--COLOR_UI_PLACEHOLDER--;}    ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .volume_static:before{background-color:--COLOR_UI_PLACEHOLDER--;} ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .meta-artist .the-name object>a{color:--COLOR_UI_PLACEHOLDER--;}  ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .volumeicon:before{border-right-color:--COLOR_UI_PLACEHOLDER--}  ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .volumeicon{background:--COLOR_UI_PLACEHOLDER--} ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir i.svg-icon{color:--COLOR_UI_PLACEHOLDER--}   ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir .con-playpause .the-icon-bg{border-color:--COLOR_UI_PLACEHOLDER--;}   ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir .player-but .the-icon-bg{background-color:transparent;border-color:--COLOR_UI_PLACEHOLDER--;}   ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir.button-aspect-noir--filled .player-but .the-icon-bg{background-color:--COLOR_UI_PLACEHOLDER--;}  ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir.button-aspect-noir--stroked i.svg-icon{color:--COLOR_UI_PLACEHOLDER--}      ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir.button-aspect-noir--stroked .svg-icon path{stroke:--COLOR_UI_PLACEHOLDER--}    ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir .svg-icon path{fill:--COLOR_UI_PLACEHOLDER--; } ';

    // -- skin-silver
    $colorsFromColorsCss .= ' ##CSS_AUDIOPLAYER_SELECTOR##.skin-silver .ap-controls .ap-controls-right .controls-volume .volumeicon::before{ border-right-color:--COLOR_UI_PLACEHOLDER--; } ##CSS_AUDIOPLAYER_SELECTOR##.skin-silver .ap-controls .ap-controls-right .controls-volume .volumeicon {  background-color:--COLOR_UI_PLACEHOLDER--;  } ';
    // -- skin-default
    $colorsFromColorsCss .= ' ##CSS_AUDIOPLAYER_SELECTOR##.skin-default .ap-controls .ap-controls-right .controls-volume .volumeicon::before{ border-right-color:--COLOR_UI_PLACEHOLDER--; } ##CSS_AUDIOPLAYER_SELECTOR##.skin-default .ap-controls .ap-controls-right .controls-volume .volumeicon {  background-color:--COLOR_UI_PLACEHOLDER--;  } ';
  }
  if ($colorText) {
    $colorsFromColorsCss .= ' 
    .audioplayer.skin-wave .meta-artist .the-name, .audioplayer.skin-wave .meta-artist .the-name>a, .audioplayer.skin-wave .meta-artist .the-name object>a{
    color:--COLOR_UI_TEXT_PLACEHOLDER--;  } 
    ';

    // -- skin-silver
    $colorsFromColorsCss .= '##CSS_AUDIOPLAYER_SELECTOR##.skin-silver .ap-controls .meta-artist-con{color:--COLOR_UI_TEXT_PLACEHOLDER--}   ##CSS_AUDIOPLAYER_SELECTOR##.skin-silver .ap-controls .curr-time,  ##CSS_AUDIOPLAYER_SELECTOR##.skin-silver .ap-controls .ap-controls-right .total-time{color:--COLOR_UI_TEXT_PLACEHOLDER--} ';
    // -- skin-pro
    $colorsFromColorsCss .= ' ##CSS_AUDIOPLAYER_SELECTOR##.skin-pro:not(.a) .ap-controls .scrubbar .scrub-bg{ background-color: --COLOR_UI_TEXT_PLACEHOLDER--; }';


    // -- skin-aria
    $colorsFromColorsCss .= '##CSS_AUDIOPLAYER_SELECTOR##.skin-aria .ap-controls .ap-controls-left{background-color:--COLOR_UI_TEXT_PLACEHOLDER--}  ##CSS_AUDIOPLAYER_SELECTOR##.skin-aria .ap-controls .ap-controls-right .meta-artist-con{color:--COLOR_UI_TEXT_PLACEHOLDER--}';
  }

  if ($colorHighlight) {

    $colorsFromColorsCss .= '##CSS_AUDIOPLAYER_SELECTOR## .volume_active{background-color:--COLOR_HIGHLIGHT_PLACEHOLDER--}   body .btn-zoomsounds.btn-like:hover .the-icon>svg path,body .btn-zoomsounds.btn-like.active .the-icon>svg path{fill:--COLOR_HIGHLIGHT_PLACEHOLDER--}  ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .volume_active{background-color:--COLOR_HIGHLIGHT_PLACEHOLDER--}  ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave .meta-artist .the-artist{color:--COLOR_HIGHLIGHT_PLACEHOLDER--}    ';
    // -- skin-silver
    $colorsFromColorsCss .= ' ##CSS_AUDIOPLAYER_SELECTOR##.skin-silver a{color:--COLOR_HIGHLIGHT_PLACEHOLDER--}    ##CSS_AUDIOPLAYER_SELECTOR##.skin-silver{line-height:1}  body .dzsap-color_inverse_ui_text{color:--COLOR_HIGHLIGHT_PLACEHOLDER--}';

  }

  if ($colorUiAlternate) {
    $colorsFromColorsCss .= '##CSS_AUDIOPLAYER_SELECTOR## .player-but .svg-icon path{fill:--COLOR_UI_ALT_PLACEHOLDER--;}';
    $colorsFromColorsCss .= '##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir.button-aspect-noir--filled .svg-icon path{fill:--COLOR_UI_ALT_PLACEHOLDER--;} ##CSS_AUDIOPLAYER_SELECTOR##.skin-wave.button-aspect-noir.button-aspect-noir--filled i.svg-icon{ color: --COLOR_UI_ALT_PLACEHOLDER--; }';
  }


  if ($colorWaveColorBg) {

    // -- skin-pro
    $colorsFromColorsCss .= ' ##CSS_AUDIOPLAYER_SELECTOR##.skin-pro:not(.a) .ap-controls .scrubbar .scrub-bg{ background-color: --COLOR_SCRUB_BG_PLACEHOLDER--; }';


  }
  if ($colorWaveColorProg) {
    $colorsFromColorsCss .= '##CSS_AUDIOPLAYER_SELECTOR##.scrubbar-type-bar .scrubbar .scrub-prog{ background-color:--COLOR_SCRUB_PROG_PLACEHOLDER--; }';
  }



  $colorsFromColorsCss = str_replace('##CSS_AUDIOPLAYER_SELECTOR##', $selectorApConfig, $colorsFromColorsCss);

  $colorsFromColorsCss = str_replace('--COLOR_UI_PLACEHOLDER--', '#' . $colorUi, $colorsFromColorsCss);
  $colorsFromColorsCss = str_replace('--COLOR_UI_TEXT_PLACEHOLDER--', '#' . $colorText, $colorsFromColorsCss);
  $colorsFromColorsCss = str_replace('--COLOR_HIGHLIGHT_PLACEHOLDER--', '#' . $colorHighlight, $colorsFromColorsCss);
  $colorsFromColorsCss = str_replace('--COLOR_UI_ALT_PLACEHOLDER--', '#' . $colorUiAlternate, $colorsFromColorsCss);
  $colorsFromColorsCss = str_replace('--COLOR_SCRUB_BG_PLACEHOLDER--', '#' . $colorWaveColorBg, $colorsFromColorsCss);
  $colorsFromColorsCss = str_replace('--COLOR_SCRUB_PROG_PLACEHOLDER--', '#' . $colorWaveColorProg, $colorsFromColorsCss);


  $fout .= $colorsFromColorsCss;


  return $fout;
}