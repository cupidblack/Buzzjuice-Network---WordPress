import {dzs_clean_string, is_android, is_ios, player_reinit_findIfPcmNeedsGenerating} from "../_dzsap_helpers";
import {string_curateClassName} from "../../js_common/_dzs_helpers";
import {svg_share_icon} from "../_dzsap_svgs";

/**
 *
 * @param {jQuery} cthis
 * @param {object} o
 * @param {DzsAudioPlayer} selfClass
 */
export function configureAudioPlayerOptionsInitial(cthis, o, selfClass) {


  selfClass.cthis.addClass('preload-method-' + o.preload_method);

  o.wavesurfer_pcm_length = Number(o.wavesurfer_pcm_length);


  o.settings_trigger_resize = parseInt(o.settings_trigger_resize, 10);


  if (isNaN(parseInt(o.design_thumbh, 10)) === false) {
    o.design_thumbh = parseInt(o.design_thumbh, 10);
  }

  if (o.skinwave_wave_mode === '') {
    o.skinwave_wave_mode = 'canvas';
  }
  if (o.skinwave_enableSpectrum === 'on') {
    o.skinwave_wave_mode = 'canvas';
  }
  if (o.skinwave_wave_mode_canvas_normalize === '') {
    o.skinwave_wave_mode_canvas_normalize = 'on';
  }
  if (o.skinwave_wave_mode_canvas_waves_number === '' || isNaN(Number(o.skinwave_wave_mode_canvas_waves_number))) {
    o.skinwave_wave_mode_canvas_waves_number = 3;
  }


  if (o.autoplay === 'on' && o.cue === 'on') {
    o.preload_method = 'auto';
  }

  cthis.addClass(o.extra_classes_player)

  if (o.design_skin === '') {
    o.design_skin = 'skin-default';
  }


  if (selfClass.cthis.find('.feed-dzsap--embed-code').length) {

    selfClass.feedEmbedCode = selfClass.cthis.find('.feed-dzsap--embed-code').eq(0).html();
  } else {
    if (o.embed_code !== '') {
      selfClass.feedEmbedCode = o.embed_code;
    }
  }

  if (is_ios()) {

    if (selfClass.initOptions.skinwave_enableSpectrum === 'on') {
      selfClass.initOptions.skinwave_enableSpectrum = 'off';
    }

  }

  const skin_regex = / skin-/g;


  if (skin_regex.test(cthis.attr('class'))) {

  } else {

    cthis.addClass(o.design_skin);
  }


  if (cthis.hasClass('skin-default')) {
    o.design_skin = 'skin-default';
  }
  if (cthis.hasClass('skin-wave')) {
    o.design_skin = 'skin-wave';
  }
  if (cthis.hasClass('skin-justthumbandbutton')) {
    o.design_skin = 'skin-justthumbandbutton';
  }
  if (cthis.hasClass('skin-pro')) {
    o.design_skin = 'skin-pro';
  }
  if (cthis.hasClass('skin-aria')) {
    o.design_skin = 'skin-aria';
  }
  if (cthis.hasClass('skin-silver')) {
    o.design_skin = 'skin-silver';
  }
  if (cthis.hasClass('skin-redlights')) {
    o.design_skin = 'skin-redlights';
  }
  if (cthis.hasClass('skin-steel')) {
    o.design_skin = 'skin-steel';
  }
  if (cthis.hasClass('skin-customcontrols')) {
    o.design_skin = 'skin-customcontrols';
  }


  if (o.design_skin === 'skin-wave') {
    if (o.scrubbar_type === 'auto') {
      o.scrubbar_type = 'wave';
    }
  }
  if (o.scrubbar_type === 'auto') {
    o.scrubbar_type = 'bar';
  }

  if (o.settings_php_handler === 'wpdefault') {
    o.settings_php_handler = window.ajaxurl;
  }
  if (o.action_received_time_total === 'wpdefault') {
    o.action_received_time_total = window.dzsap_send_total_time;
  }
  if (o.action_video_contor_60secs === 'wpdefault') {
    o.action_video_contor_60secs = window.action_video_contor_60secs;
  }


  if (is_ios() || is_android()) {
    o.autoplay = 'off';
    o.disable_volume = 'on';
    if (o.cueMedia === 'off') {
      o.cueMedia = 'on';
    }
    o.cueMedia = 'on';
  }

  if (cthis.attr('data-viewsubmitted') === 'on') {
    selfClass.ajax_view_submitted = 'on';


  }
  if (cthis.attr('data-userstarrating')) {
    selfClass.starrating_alreadyrated = Number(cthis.attr('data-userstarrating'));
  }

  if (cthis.attr('data-loop') === 'on') {
    selfClass.initOptions.loop = 'on';
  }


  if (cthis.hasClass('skin-minimal')) {
    o.design_skin = 'skin-minimal';
    if (o.disable_volume === 'default') {
      o.disable_volume = 'on';
    }

    if (o.disable_scrub === 'default') {
      o.disable_scrub = 'on';
    }
    o.disable_timer = 'on';
  }
  if (cthis.hasClass('skin-minion')) {
    o.design_skin = 'skin-minion';
    if (o.disable_volume === 'default') {
      o.disable_volume = 'on';
    }

    if (o.disable_scrub === 'default') {
      o.disable_scrub = 'on';
    }

    o.disable_timer = 'on';
  }


  if (o.design_color_bg) {
    o.design_wave_color_bg = o.design_color_bg;
  }


  if (o.design_color_highlight) {
    o.design_wave_color_progress = o.design_color_highlight;
  }


  if (o.design_skin === 'skin-justthumbandbutton') {
    if (o.design_thumbh === 'default') {
      o.design_thumbh = '';

    }
    o.disable_timer = 'on';
    o.disable_volume = 'on';

    if (o.design_animateplaypause === 'default') {
      o.design_animateplaypause = 'on';
    }
  }
  if (o.design_skin === 'skin-redlights') {
    o.disable_timer = 'on';
    o.disable_volume = 'off';
    if (o.design_animateplaypause === 'default') {
      o.design_animateplaypause = 'on';
    }

  }
  if (o.design_skin === 'skin-steel') {
    if (o.disable_timer === 'default') {

      o.disable_timer = 'off';
    }
    o.disable_volume = 'on';
    if (o.design_animateplaypause === 'default') {
      o.design_animateplaypause = 'on';
    }


    if (o.disable_scrub === 'default') {
      o.disable_scrub = 'on';
    }

  }
  if (o.design_skin === 'skin-customcontrols') {
    if (o.disable_timer === 'default') {

      o.disable_timer = 'on';
    }
    o.disable_volume = 'on';
    if (o.design_animateplaypause === 'default') {
      o.design_animateplaypause = 'on';
    }


    if (o.disable_scrub === 'default') {
      o.disable_scrub = 'on';
    }

  }

  if (o.skinwave_wave_mode_canvas_mode === 'reflecto') {
    o.skinwave_wave_mode_canvas_reflection_size = 0.5;


  }

  if (o.skinwave_wave_mode_canvas_mode === 'reflecto') {
    o.skinwave_wave_mode_canvas_reflection_size = 0.5;

  }


  if (o.embed_code === '') {
    if (cthis.find('div.feed-embed-code').length > 0) {
      o.embed_code = cthis.find('div.feed-embed-code').eq(0).html();
    }
  }

  if (o.design_animateplaypause === 'default') {
    o.design_animateplaypause = 'off';
  }

  if (o.design_animateplaypause === 'on') {
    cthis.addClass('design-animateplaypause');
  }

  if (window.dzsap_settings) {
    if (window.dzsap_settings.ajax_url) {
      if (!o.settings_php_handler) {

        o.settings_php_handler = window.dzsap_settings.ajax_url;
      }
    }
  }

  if (o.settings_php_handler) {
    selfClass.urlToAjaxHandler = o.settings_php_handler;
  } else {
    if (window.dzsap_settings && window.dzsap_settings.php_handler) {

      selfClass.urlToAjaxHandler = window.dzsap_settings.php_handler;
    }
  }


  player_reinit_findIfPcmNeedsGenerating(selfClass);

}

export function player_detect_skinwave_mode() {

  const selfClass = this;


  selfClass.skinwave_mode = selfClass.initOptions.skinwave_mode;

  if (selfClass.cthis.hasClass('skin-wave-mode-small')) {
    selfClass.skinwave_mode = 'small'
  }
  if (selfClass.cthis.hasClass('skin-wave-mode-alternate')) {
    selfClass.skinwave_mode = 'alternate'
  }
  if (selfClass.cthis.hasClass('skin-wave-mode-bigwavo')) {
    selfClass.skinwave_mode = 'bigwavo'
  }
}



export function player_viewApplySkinWaveModes(selfClass) {


  var o = selfClass.initOptions;

  selfClass.cthis.removeClass('skin-wave-mode-normal');

  if (o.design_skin === 'skin-wave') {
    selfClass.cthis.addClass('skin-wave-mode-' + selfClass.skinwave_mode);


    selfClass.cthis.addClass('skin-wave-wave-mode-' + o.skinwave_wave_mode);
    if (o.skinwave_enableSpectrum === 'on') {
      selfClass.cthis.addClass('skin-wave-is-spectrum');
    }
    selfClass.cthis.addClass('skin-wave-wave-mode-canvas-mode-' + o.skinwave_wave_mode_canvas_mode);
  }


}

export function player_determineHtmlAreas(selfClass) {

  var o = selfClass.initOptions;


  let extraHtmlBottom = '';
  let extraHtmlControlsLeft = '';
  let extraHtmlControlsRight = '';


  if (selfClass.cthis.children('.feed-dzsap--extra-html').length > 0 && o.settings_extrahtml === '') {
    selfClass.cthis.children('.feed-dzsap--extra-html').each(function () {

      let newExtraHtmlSectionClassName = string_curateClassName(this.className);
      extraHtmlBottom += `<section class="dzsap-extra-html--section-bottom ${newExtraHtmlSectionClassName}">${this.innerHTML}</section>`;
    })
    selfClass.cthis.children('.feed-dzsap--extra-html').remove();
  } else {
    if (o.settings_extrahtml) {
      extraHtmlBottom = o.settings_extrahtml;
    }
  }


  if (selfClass.cthis.children('.feed-dzsap--extra-html-in-controls-left').length > 0) {
    extraHtmlControlsLeft = selfClass.cthis.children('.feed-dzsap--extra-html-in-controls-left').eq(0).html();
  }
  if (selfClass.cthis.children('.feed-dzsap--extra-html-in-controls-right').length > 0) {
    extraHtmlControlsRight = selfClass.cthis.children('.feed-dzsap--extra-html-in-controls-right').eq(0).html();
  }
  if (selfClass.cthis.children('.feed-dzsap--extra-html-bottom').length > 0) {
    extraHtmlBottom = selfClass.cthis.children('.feed-dzsap--extra-html-bottom').eq(0).html();
  }


  selfClass.extraHtmlAreas.controlsLeft = extraHtmlControlsLeft;
  selfClass.extraHtmlAreas.controlsRight = extraHtmlControlsRight;
  selfClass.extraHtmlAreas.bottom = extraHtmlBottom;


  if (selfClass.extraHtmlAreas.controlsRight) {
    selfClass.extraHtmlAreas.controlsRight = String(selfClass.extraHtmlAreas.controlsRight).replace(/{{svg_share_icon}}/g, svg_share_icon);
  }


  for (var key in selfClass.extraHtmlAreas) {

    selfClass.extraHtmlAreas[key] = String(selfClass.extraHtmlAreas[key]).replace('{{heart_svg}}', '\t&hearts;');
    selfClass.extraHtmlAreas[key] = String(selfClass.extraHtmlAreas[key]).replace('{{embed_code}}', selfClass.feedEmbedCode);
  }
}


export function player_determineActualPlayer(selfClass) {

  var $ = jQuery;
  var $fakePlayer = $(selfClass.cthis.attr('data-fakeplayer'));


  if ($fakePlayer.length === 0) {
    $fakePlayer = $(String(selfClass.cthis.attr('data-fakeplayer')).replace('#', '.'));
    if ($fakePlayer.length) {
      selfClass._actualPlayer = $(String(selfClass.cthis.attr('data-fakeplayer')).replace('#', '.'));
      selfClass.cthis.attr('data-fakeplayer', String(selfClass.cthis.attr('data-fakeplayer')).replace('#', '.'));
    }
  }

  if ($fakePlayer.length === 0) {
    selfClass.cthis.attr('data-fakeplayer', '');
  } else {
    selfClass.cthis.addClass('player-is-feeding is-source-player-for-actual-player');
    if (selfClass.cthis.attr('data-type')) {
      selfClass._actualPlayer = $(selfClass.cthis.attr('data-fakeplayer')).eq(0);
      selfClass._actualPlayer.addClass('player-is-feeded');
      selfClass.actualDataTypeOfMedia = selfClass.cthis.attr('data-type');
      selfClass.cthis.attr('data-original-type', selfClass.actualDataTypeOfMedia);
    }
  }
}


export function player_adjustIdentifiers(selfClass) {

  selfClass.identifier_pcm = selfClass.the_player_id;


  var _feed_obj = null;

  if (selfClass.$feed_fakeButton) {
    _feed_obj = selfClass.$feed_fakeButton;
  } else {
    if (selfClass._sourcePlayer) {
      _feed_obj = selfClass._sourcePlayer;
    } else {
      _feed_obj = null;
    }
  }


  if (selfClass.identifier_pcm === 'dzs_footer') {
    selfClass.identifier_pcm = dzs_clean_string(selfClass.cthis.attr('data-source'));
  }

  if (_feed_obj) {
    if (_feed_obj.attr('data-playerid')) {
      selfClass.identifier_pcm = _feed_obj.attr('data-playerid');
    } else {
      if (_feed_obj.attr('data-source')) {
        selfClass.identifier_pcm = _feed_obj.attr('data-source');
      }
    }
  }

  if (typeof selfClass.identifier_pcm === 'string') {
    selfClass.identifier_pcm = selfClass.identifier_pcm.replace('ap', '');
  }

}
