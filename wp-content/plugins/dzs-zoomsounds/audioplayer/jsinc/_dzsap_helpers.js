import {isValid} from "../js_common/_dzs_helpers";
import {dzsap_keyboardSetup} from "./player/_player_keyboard";
import {
  DZSAP_PLAYER_CLASS_FOOTER_PLAYER,
  DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM,
  DZSAP_VIEW_SONG_CHANGER_CLASS
} from "../configs/_constants";


export function formatTime(arg) {

  var s = Math.round(arg);
  var m = 0;
  var h = 0;
  if (s > 0) {
    while (s > 3599 && s < 3000000 && isFinite(s)) {
      h++;
      s -= 3600;
    }
    while (s > 59 && s < 3000000 && isFinite(s)) {
      m++;
      s -= 60;
    }
    if (h) {

      return String((h < 10 ? "0" : "") + h + ":" + String((m < 10 ? "0" : "") + m + ":" + (s < 10 ? "0" : "") + s));
    }
    return String((m < 10 ? "0" : "") + m + ":" + (s < 10 ? "0" : "") + s);
  } else {
    return "00:00";
  }
}

export function can_history_api() {
  return !!(window.history && history.pushState);
}

export function dzs_clean_string(arg) {

  if (arg) {

    arg = arg.replace(/[^A-Za-z0-9\-]/g, '');

    arg = arg.replace(/\./g, '');
    return arg;
  }

  return '';


}


export function get_query_arg(purl, key) {
  if (purl) {

    if (String(purl).indexOf(key + '=') > -1) {

      var regexS = "[?&]" + key + "=.+";
      var regex = new RegExp(regexS);
      var regtest = regex.exec(purl);


      if (regtest != null) {
        var splitterS = regtest[0];
        if (splitterS.indexOf('&') > -1) {
          var aux = splitterS.split('&');
          splitterS = aux[1];
        }

        var splitter = splitterS.split('=');


        return splitter[1];

      }

    }

  } else {
  }
}

export function add_query_arg(purl, key, value) {

  key = encodeURIComponent(key);
  value = encodeURIComponent(value);

  if (!(purl)) {
    purl = '';
  }
  var s = purl;
  var pair = key + "=" + value;

  var r = new RegExp("(&|\\?)" + key + "=[^\&]*");

  s = s.replace(r, "$1" + pair);

  if (s.indexOf(key + '=') > -1) {


  } else {
    if (s.indexOf('?') > -1) {
      s += '&' + pair;
    } else {
      s += '?' + pair;
    }
  }


  if (value === 'NaN') {
    var regex_attr = new RegExp('[\?|\&]' + key + '=' + value);
    s = s.replace(regex_attr, '');


    if (s.indexOf('?') === -1 && s.indexOf('&') > -1) {
      s = s.replace('&', '?');
    }
  }

  return s;
}


export function dzsap_is_mobile() {


  return is_ios() || is_android();
}

export function is_ios() {

  return ((navigator.platform.indexOf("iPhone") !== -1) || (navigator.platform.indexOf("iPod") !== -1) || (navigator.platform.indexOf("iPad") !== -1));
}


export function can_canvas() {

  var oCanvas = document.createElement("canvas");
  return !!oCanvas.getContext("2d");

}

export function is_safari() {
  return Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
}


export function is_android() {
  var ua = navigator.userAgent.toLowerCase();

  return (ua.indexOf("android") > -1);
}

export function select_all(el) {
  if (typeof window.getSelection !== "undefined" && typeof document.createRange !== "undefined") {
    var range = document.createRange();
    range.selectNodeContents(el);
    var sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
  } else if (typeof document.selection !== "undefined" && typeof document.body.createTextRange !== "undefined") {
    var textRange = document.body.createTextRange();
    textRange.moveToElementText(el);
    textRange.select();
  }
}

export function is_android_good() {


}

export function htmlEncode(arg) {
  return jQuery('<div/>').text(arg).html();
}

export function dzsap_generate_keyboard_tooltip(keyboard_controls, lab) {


  let structureDzsTooltipCommentAfterSubmit = '<span class="dzstooltip color-dark-light talign-start transition-slidein arrow-bottom style-default" style="width: auto;  white-space:nowrap;"><span class="dzstooltip--inner">' + 'Shortcut' + ': ' + keyboard_controls[lab] + '</span></span>';

  structureDzsTooltipCommentAfterSubmit = structureDzsTooltipCommentAfterSubmit.replace('32', 'space');
  structureDzsTooltipCommentAfterSubmit = structureDzsTooltipCommentAfterSubmit.replace('27', 'escape');

  return structureDzsTooltipCommentAfterSubmit;


}


/**
 *
 * @param hex
 * @param {number|null} targetAlpha 0-1
 * @returns {string}
 */
export function hexToRgb(hex, targetAlpha = null) {
  var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  var str = '';
  if (result) {
    result = {
      r: parseInt(result[1], 16),
      g: parseInt(result[2], 16),
      b: parseInt(result[3], 16)
    };


    var alpha = 1;

    if (targetAlpha !== null) {
      alpha = targetAlpha;
    }


    str = 'rgba(' + result.r + ',' + result.g + ',' + result.b + ',' + alpha + ')';
  }


  return str;


}

export function assignHelperFunctionsToJquery($) {


  Math.easeIn = function (t, b, c, d) {
    return -c * (t /= d) * (t - 2) + b;
  };


  /**
   *
   * @param {string} argfind
   * @param {string} arg
   * @returns {string}
   */
  const checkIfHasClass = (argfind, arg) => {

    if (!argfind) {
      var regex = new RegExp('class="(.*?)"');
      var auxarr = regex.exec(arg);


      if (auxarr && auxarr[1]) {
        argfind = '.' + auxarr[1];
      }
    }

    return argfind;
  }
  $.fn.prependOnce = function (arg, argfind) {
    var _t = $(this)


    argfind = checkIfHasClass(argfind, arg);


    if (_t.children(argfind).length < 1) {
      _t.prepend(arg);
      return true;
    }
    return false;
  };
  $.fn.appendOnce = function (arg, argfind) {
    var _t = $(this)

    argfind = checkIfHasClass(argfind, arg);

    if (_t.children(argfind).length < 1) {
      _t.append(arg);
      return true;
    }
    return false;
  };
};


export function registerTextWidth($) {

  $.fn.textWidth = function () {
    var _t = jQuery(this);
    var html_org = _t.html();
    if (_t[0].nodeName === 'INPUT') {
      html_org = _t.val();
    }
    var html_calcS = '<span class="forcalc">' + html_org + '</span>';
    jQuery('body').append(html_calcS);
    var _lastspan = jQuery('span.forcalc').last();

    _lastspan.css({
      'font-size': _t.css('font-size'),
      'font-family': _t.css('font-family')
    })
    var width = _lastspan.width();

    _lastspan.remove();
    return width;
  };
}

export function player_checkIfWeShouldShowAComment(selfClass, real_time_curr, real_time_total) {

  var $ = jQuery;
  var timer_curr_perc = Math.round((real_time_curr / real_time_total) * 100) / 100;
  if (selfClass.audioType === 'fake') {
    timer_curr_perc = Math.round((selfClass.timeCurrent / selfClass.timeTotal) * 100) / 100;
  }
  if (selfClass._commentsHolder) {
    selfClass._commentsHolder.children().each(function () {
      var _t = $(this);
      if (_t.hasClass('dzstooltip-con')) {
        var _t_posx = _t.offset().left - selfClass._commentsHolder.offset().left;


        var aux = Math.round((parseFloat(_t_posx) / selfClass._commentsHolder.outerWidth()) * 100) / 100;


        if (aux) {

          if (Math.abs(aux - timer_curr_perc) < 0.02) {
            selfClass._commentsHolder.find('.dzstooltip').removeClass('active');
            _t.find('.dzstooltip').addClass('active');
          } else {
            _t.find('.dzstooltip').removeClass('active');
          }
        }
      }
    })
  }
}


export function sanitizeObjectForChangeMediaArgs(_sourceForChange) {

  var changeMediaArgs = {};
  var _feed_fakePlayer = _sourceForChange;

  var lab = '';

  if (_sourceForChange.data('original-settings')) {
    return _sourceForChange.data('original-settings');
  }


  changeMediaArgs.source = null;
  if (_feed_fakePlayer.attr('data-source')) {
    changeMediaArgs.source = _feed_fakePlayer.attr('data-source')
  } else {

    if (_feed_fakePlayer.attr('href')) {
      changeMediaArgs.source = _feed_fakePlayer.attr('href');
    }
  }

  if (_feed_fakePlayer.attr('data-pcm')) {
    changeMediaArgs.pcm = _feed_fakePlayer.attr('data-pcm');
  }


  lab = 'thumb';
  if (_feed_fakePlayer.attr('data-' + lab)) {
    changeMediaArgs[lab] = _sourceForChange.attr('data-' + lab);
  }

  lab = 'playerid';
  if (_feed_fakePlayer.attr('data-' + lab)) {
    changeMediaArgs[lab] = _sourceForChange.attr('data-' + lab);
  }
  lab = 'type';
  if (_feed_fakePlayer.attr('data-' + lab)) {
    changeMediaArgs[lab] = _sourceForChange.attr('data-' + lab);
  }


  if (_feed_fakePlayer.attr('data-thumb_link')) {
    changeMediaArgs.thumb_link = _sourceForChange.attr('data-thumb_link');
  }


  if (_sourceForChange.find('.meta-artist').length > 0 || _sourceForChange.find('.meta-artist-con').length > 0) {

    changeMediaArgs.artist = _sourceForChange.find('.the-artist').eq(0).html();
    changeMediaArgs.song_name = _sourceForChange.find('.the-name').eq(0).html();
  }


  if (_sourceForChange.attr('data-thumb_for_parent')) {
    changeMediaArgs.thumb = _sourceForChange.attr('data-thumb_for_parent');
  }


  if (_sourceForChange.find('.feed-song-name').length > 0 || _sourceForChange.find('.feed-artist-name').length > 0) {
    changeMediaArgs.artist = _sourceForChange.find('.feed-artist-name').eq(0).html();
    changeMediaArgs.song_name = _sourceForChange.find('.feed-song-name').eq(0).html();
  }


  return changeMediaArgs;
}


export function utils_sanitizeToColor(colorString) {
  if (colorString.indexOf('#') === -1 && colorString.indexOf('rgb') === -1 && colorString.indexOf('hsl') === -1) {
    return '#' + colorString;
  }
  return colorString;
}

export function dzsapInitjQueryRegisters() {
}

export function player_radio_isNameUpdatable(selfClass, radio_update_song_name, targetKey) {

  if (selfClass._metaArtistCon.find(targetKey).length && selfClass._metaArtistCon.find(targetKey).eq(0).text().length > 0) {

    if (selfClass._metaArtistCon.find(targetKey).eq(0).html().indexOf('{{update}}') > -1) {
      return true;
    }
  }


  return false;
}

export function playerRegisterWindowFunctions() {


  window['dzsap_functions'] = {};
  window['dzsap_functions']['send_total_time'] = function (argtime, argcthis) {


    if (argtime && argtime !== Infinity) {
      const data = {
        action: 'dzsap_send_total_time_for_track',
        id_track: argcthis.attr('data-playerid'),
        postdata: Math.ceil(argtime)
      };
      jQuery.post(window.dzsap_ajaxurl, data, function (response) {
      });
    }

  }


  window.dzs_open_social_link = function (arg, argthis) {
    var leftPosition, topPosition;
    var w = 500, h = 500;

    leftPosition = (window.screen.width / 2) - ((w / 2) + 10);

    topPosition = (window.screen.height / 2) - ((h / 2) + 50);
    var windowFeatures = "status=no,height=" + h + ",width=" + w + ",resizable=yes,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no";


    arg = arg.replace('{{replacewithcurrurl}}', encodeURIComponent(window.location.href));

    if (argthis && argthis.attr) {
      arg = arg.replace(/{{replacewithdataurl}}/g, argthis.attr('data-url'));
    }

    const locationHref = window.location.href;


    const auxa = locationHref.split('?');

    let cid = '';
    let cid_gallery = '';


    if (argthis) {

    } else {
      if (window.dzsap_currplayer_from_share) {

        argthis = window.dzsap_currplayer_from_share;
      }
    }


    if (argthis) {

      const $ = jQuery;

      if ($(argthis).hasClass('audioplayer')) {
        cid = $(argthis).parent().children().index(argthis);
        cid_gallery = $(argthis).parent().parent().parent().attr('id');
      } else {
        if (jQuery(argthis).parent().parent().attr('data-menu-index')) {

          cid = jQuery(argthis).parent().parent().attr('data-menu-index');
        }
        if (jQuery(argthis).parent().parent().attr('data-gallery-id')) {

          cid_gallery = jQuery(argthis).parent().parent().attr('data-gallery-id');
        }
      }

    }


    var shareurl = encodeURIComponent(auxa[0] + '?fromsharer=on&audiogallery_startitem_' + cid_gallery + '=' + cid + '');
    arg = arg.replace('{{shareurl}}', shareurl);


    window.open(arg, "sharer", windowFeatures);
  }


  window.dzsap_wp_send_contor_60_secs = function (argcthis, argtitle) {

    var data = {
      video_title: argtitle

      , video_analytics_id: argcthis.attr('data-playerid')
      , curr_user: window.dzsap_curr_user
    };
    var theajaxurl = 'index.php?action=ajax_dzsap_submit_contor_60_secs';

    if (window.dzsap_settings.dzsap_site_url) {

      theajaxurl = dzsap_settings.dzsap_site_url + theajaxurl;
    }


    jQuery.ajax({
      type: "POST",
      url: theajaxurl,
      data: data,
      success: function (response) {


      },
      error: function (arg) {

      }
    });
  }


  window.dzsap_init_multisharer = function () {


  }


}


/**
 *
 * @param {DzsAudioPlayer} selfClass
 */
export function assignPcmData(selfClass) {

  selfClass.pcmSource = string_jsonConvertToArray(selfClass.cthis.attr('data-pcm'));
  if (selfClass.initOptions.skinwave_enableSpectrum === 'on') {
    var pcmSourceLen = selfClass.pcmSource.length;
    var desiredSize = 256;

    for (var i = 0; i < pcmSourceLen; i++) {
      if (i > desiredSize) {
        continue;
      }
      selfClass.pcmSource[i] = selfClass.pcmSource[i * pcmSourceLen / desiredSize] * desiredSize;
    }
    selfClass.pcmSource.splice(desiredSize, pcmSourceLen - desiredSize); // removes 3rd element of array
  }
}

export function string_jsonConvertToArray(ar_str) {
  let waves = [];
  if (typeof (ar_str) == 'object') {
    waves = ar_str;
  } else {
    try {
      waves = JSON.parse(ar_str);
    } catch (err) {

    }
  }

  return waves;
}

/**
 * should be called only once on init
 */
export function dzsap_singleton_ready_calls() {

  window.dzsap_singleton_ready_calls_is_called = true;


  let $feed_dzsapMainSettings = null;
  const $feed_dzsapMainSettingsAll = document.querySelectorAll('.dzsap-main-settings');
  if ($feed_dzsapMainSettingsAll.length) {
    $feed_dzsapMainSettings = $feed_dzsapMainSettingsAll[$feed_dzsapMainSettingsAll.length - 1];
  }
  if ($feed_dzsapMainSettings) {
    window.dzsap_settings = JSON.parse($feed_dzsapMainSettings.innerHTML);
    window.ajaxurl = window.dzsap_settings.ajax_url;
    window.dzsap_curr_user = window.dzsap_settings.dzsap_curr_user;
  }


  jQuery('body').append('<style class="dzsap--style"></style>');


  window.dzsap__style = jQuery('.dzsap--style');


  jQuery('audio.zoomsounds-from-audio').each(function () {
    var _t = jQuery(this);
    _t.after('<div class="audioplayer-tobe auto-init skin-silver" data-source="' + _t.attr('src') + '"></div>');
    _t.remove();
  })

  jQuery(document).on('focus.dzsap', 'input', function () {
    window.dzsap_currplayer_focused = null;
  })

  registerTextWidth(jQuery);

  dzsap_keyboardSetup();
}

export function jQueryAuxBindings($) {


  function handleClick_onGlobalZoomSoundsButton(e) {
    const $t = $(this);


    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    if ($t.hasClass(DZSAP_VIEW_SONG_CHANGER_CLASS)) {
      const _c = $($t.attr('data-fakeplayer')).eq(0);


      if (_c && _c.get(0) && _c.get(0).api_change_media) {
        _c.get(0).api_change_media($t, {
          'feeder_type': 'button'
          , 'call_from': 'changed ' + DZSAP_VIEW_SONG_CHANGER_CLASS
        });
      }

      return false;
    }


  }


  $(document).off('click.dzsap_metas')
  $(document).on('click.dzsap_metas', '.' + DZSAP_VIEW_SONG_CHANGER_CLASS, handleClick_onGlobalZoomSoundsButton)


}


/**
 * for .zoomsounds-wrapper-bg-center
 * @param selfClass
 */
export function view_player_playMiscEffects(selfClass) {

  selfClass.$conPlayPause.addClass('playing');

  if (selfClass.cthis.parent().hasClass('zoomsounds-wrapper-bg-center')) {
    selfClass.cthis.parent().addClass('is-playing');
  }
}


export function view_player_globalDetermineSyncPlayersIndex(selfClass) {

  if (selfClass._sourcePlayer === null && window.dzsap_syncList_players) {
    window.dzsap_syncList_players.forEach(($syncPlayer, index) => {
      if (selfClass.cthis.attr('data-playerid') == $syncPlayer.attr('data-playerid')) {
        window.dzsap_syncList_index = index;
      }
    })
  }
}


export function player_view_addMetaLoaded(selfClass) {

  selfClass.cthis.addClass('meta-loaded');
  selfClass.cthis.removeClass('meta-fake');
  if (selfClass._sourcePlayer) {
    selfClass._sourcePlayer.addClass('meta-loaded');
    selfClass._sourcePlayer.removeClass('meta-fake');
  }
  if (selfClass.$totalTime) {

    selfClass.timeModel.refreshTimes();
    selfClass.$totalTime.html(formatTime(selfClass.timeModel.getVisualTotalTime()));
  }
  if (selfClass._sourcePlayer) {
    selfClass._sourcePlayer.addClass('meta-loaded');
  }
}


function htmlEntities(str) {
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

export function waitForScriptToBeAvailableThenExecute(verifyVar, callbackFn) {

  new Promise((resolve, reject) => {

    let checkInterval = 0;

    function checkIfVarExists() {
      if (verifyVar) {
        clearInterval(checkInterval);
        resolve('var exists');
      }
    }

    checkIfVarExists()
    checkInterval = setInterval(checkIfVarExists, 300);
    setTimeout(() => {
      reject('timeout');
    }, 5000);

  }).then((resolve => {
    callbackFn(resolve);
  })).catch((err) => {
  })
}


export function player_reinit_findIfPcmNeedsGenerating(selfClass) {
  const o = selfClass.initOptions;

  if (selfClass.cthis.attr('data-pcm')) {
    selfClass.hasInitialPcmData = true;
  }

  if (!selfClass.hasInitialPcmData && o.skinwave_wave_mode === 'canvas' && (o.design_skin === 'skin-wave' || selfClass.cthis.attr('data-fakeplayer'))) {
    selfClass.isPcmRequiredToGenerate = true;
  }


  if (o.scrubbar_type === 'wave') {
    if (o.is_inited_from_playlist) {

      selfClass.cthis.addClass('scrubbar-type-wave--has-reveal-animation');
    }
    if (o.scrubbar_type_wave__has_reveal_animation === 'on') {

      selfClass.cthis.addClass('scrubbar-type-wave--has-reveal-animation');
    }
    if (selfClass.isPcmRequiredToGenerate) {
      selfClass.cthis.addClass('scrubbar-type-wave--has-reveal-animation');
    }
  }
}


export function playerFunctions(selfClass, functionType) {
  var o = selfClass.initOptions;

  if (functionType === 'detectIds') {


    if (o.skinwave_comments_playerid === '') {
      if (typeof (selfClass.cthis.attr('id')) !== 'undefined') {
        selfClass.the_player_id = selfClass.cthis.attr('id');
      }
    }


    if (selfClass.the_player_id == '') {

      if (selfClass.cthis.attr('data-computed-playerid')) {
        selfClass.the_player_id = selfClass.cthis.attr('data-computed-playerid');
      }
      if (selfClass.cthis.attr('data-real-playerid')) {
        selfClass.the_player_id = selfClass.cthis.attr('data-real-playerid');
      }
    }
    selfClass.uniqueId = selfClass.the_player_id;

    if (typeof selfClass.uniqueId === 'string') {
      selfClass.uniqueId = selfClass.uniqueId.replace('ap', '');
    }
    selfClass.identifier_pcm = selfClass.uniqueId;


    if (selfClass.uniqueId === '') {
      o.skinwave_comments_enable = 'off';
    }

  }
}

export function player_delete(selfClass) {

  var _con = null;
  if (selfClass.cthis.parent().parent().hasClass('dzsap-sticktobottom')) {
    _con = selfClass.cthis.parent().parent();
  }
  if (_con) {
    if (_con.prev().hasClass("dzsap-sticktobottom-placeholder")) {
      _con.prev().remove();
    }
    _con.remove();
  }
  selfClass.cthis.remove();
  return false;
}


export function sanitizeToHexColor(hexcolor) {
  if (hexcolor.indexOf('#') === -1) {
    hexcolor = '#' + hexcolor;
  }
  return hexcolor;
}

export function player_identifySource(selfClass) {

  selfClass.data_source = selfClass.cthis.attr('data-source') || '';
}

export function player_identifyTypes(selfClass) {


  var o = selfClass.initOptions;
  const cthis = selfClass.cthis;
  if (cthis.attr('data-type') === 'youtube') {
    o.type = 'youtube';
    selfClass.audioType = 'youtube';
  }
  if (cthis.attr('data-type') === 'soundcloud') {
    o.type = 'soundcloud';
    selfClass.audioType = 'soundcloud';

    o.skinwave_enableSpectrum = 'off';
    cthis.removeClass('skin-wave-is-spectrum');
  }
  if (cthis.attr('data-type') === 'mediafile') {
    o.type = 'audio';
    selfClass.audioType = 'audio';
  }

  if (cthis.attr('data-type') === 'shoutcast') {
    o.type = 'shoutcast';
    selfClass.audioType = 'audio';
    o.disable_timer = 'on';
    o.skinwave_enableSpectrum = 'off';

    if (!cthis.attr('data-streamtype')) {

      selfClass.audioTypeSelfHosted_streamType = 'shoutcast';
    }


    if (o.design_skin === 'skin-default') {
      o.disable_scrub = 'on';
    }

  }


  if (selfClass.audioType === 'audio' || selfClass.audioType === 'normal' || selfClass.audioType === '') {
    selfClass.audioType = 'selfHosted';
  }


  if (String(selfClass.data_source).indexOf('https://soundcloud.com/') > -1) {
    selfClass.audioType = 'soundcloud';
  }
}


export function player_getPlayFromTime(selfClass) {

  selfClass.playFrom = selfClass.initOptions.playfrom;

  if (isValid(selfClass.cthis.attr('data-playfrom'))) {
    selfClass.playFrom = selfClass.cthis.attr('data-playfrom');
  }

  if (isNaN(parseInt(selfClass.playFrom, 10)) === false) {
    selfClass.playFrom = parseInt(selfClass.playFrom, 10);
  }


  if (selfClass.playFrom === 'off' || selfClass.playFrom === '') {
    if (get_query_arg(window.location.href, 'audio_time')) {
      selfClass.playFrom = sanitizeToIntFromPointTime(get_query_arg(window.location.href, 'audio_time'));
    }
  }

  if (selfClass.timeModel.sampleTimeStart) {
    if (selfClass.playFrom < selfClass.timeModel.sampleTimeStart) {
      selfClass.playFrom = selfClass.timeModel.sampleTimeStart;
    }
    if (typeof selfClass.playFrom === 'string') {
      selfClass.playFrom = selfClass.timeModel.sampleTimeStart;
    }
  }
}


export function sanitizeToIntFromPointTime(arg) {


  arg = String(arg).replace('%3A', ':');
  arg = String(arg).replace('#', '');

  if (arg && String(arg).indexOf(':') > -1) {

    var arr = /(\d.*):(\d.*)/g.exec(arg);


    var m = parseInt(arr[1], 10);
    var s = parseInt(arr[2], 10);


    return (m * 60) + s;
  } else {
    return Number(arg);
  }
}


/**
 * called in player init()
 * @param {DzsAudioPlayer} selfClass
 */
export function player_determineStickToBottomContainer(selfClass) {

  let $conTest = selfClass.cthis.parent();


  if ($conTest.hasClass(DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM)) {
    selfClass.$stickToBottomContainer = $conTest;
    selfClass.isStickyPlayer = true;

  }
  $conTest = selfClass.cthis.parent().parent();
  if ($conTest.hasClass(DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM)) {
    selfClass.$stickToBottomContainer = $conTest;
    selfClass.isStickyPlayer = true;
  }
}


/**
 *
 * todo: maybe move
 * @param {DzsAudioPlayer} selfClass
 */
export function player_stickToBottomContainerDetermineClasses(selfClass) {

  if (selfClass.$stickToBottomContainer) {
    if (selfClass.cthis.hasClass('theme-dark')) {
      selfClass.$stickToBottomContainer.addClass('theme-dark');
    }

    setTimeout(function () {

      selfClass.$stickToBottomContainer.addClass('inited');
    }, 500)


  }

}

export function player_service_getSourceProtection(selfClass) {

  return new Promise((resolve, reject) => {

    jQuery.ajax({
      type: "POST",
      url: selfClass.data_source,
      data: {},
      success: function (response) {
        resolve(response);
      },
      error: function (err) {
        reject(err);
      }
    });
  })
}

export function player_isGoingToSetupMediaNow(selfClass) {
  return selfClass.audioTypeSelfHosted_streamType !== 'icecast' && selfClass.audioType !== 'youtube';
}


export function player_stopOtherPlayers(dzsap_list, selfClass) {

  let i = 0;
  for (i = 0; i < dzsap_list.length; i++) {

    if (dzsap_list[i].get(0) && dzsap_list[i].get(0).api_pause_media && (dzsap_list[i].get(0) != selfClass.cthis.get(0))) {


      if (dzsap_list[i].data('type_audio_stop_buffer_on_unfocus') && dzsap_list[i].data('type_audio_stop_buffer_on_unfocus') === 'on') {
        dzsap_list[i].get(0).api_destroy_for_rebuffer();
      } else {
        dzsap_list[i].get(0).api_pause_media({
          'audioapi_setlasttime': false
        });
      }
    }
  }
}


export function player_syncPlayers_gotoItem(selfClass, targetIncrement) {
  if (window.dzsap_settings.syncPlayers_autoplayEnabled) {

    for (let keySyncPlayer in window.dzsap_syncList_players) {
      let $playerInSyncList = selfClass.cthis;

      if (selfClass._sourcePlayer) {
        $playerInSyncList = selfClass._sourcePlayer;
      }


      if (window.dzsap_syncList_players[keySyncPlayer].get(0) === $playerInSyncList.get(0)) {

        keySyncPlayer = parseInt(keySyncPlayer, 10);
        let targetIndex = window.dzsap_syncList_index + targetIncrement;
        if (targetIndex >= 0 && targetIndex < window.dzsap_syncList_players.length) {
          const $currentSyncPlayer_ = window.dzsap_syncList_players[targetIndex].get(0);


          if ($currentSyncPlayer_ && $currentSyncPlayer_.api_play_media) {
            setTimeout(function () {
              $currentSyncPlayer_.api_play_media({
                'called_from': 'api_sync_players_prev'
              });
            }, 200);

          }
        }
      }
    }
  }

}

export function player_syncPlayers_buildList() {

  if (!window.syncPlayers_isDzsapListBuilt) {

    window.dzsap_syncList_players = [];

    window.syncPlayers_isDzsapListBuilt = true;

    jQuery('.audioplayer.is-single-player,.audioplayer-tobe.is-single-player').each(function () {
      const _t23 = jQuery(this);


      if (_t23.hasClass(DZSAP_PLAYER_CLASS_FOOTER_PLAYER)) {
        return;
      }


      if (_t23.attr('data-do-not-include-in-list') !== 'on') {
        window.dzsap_syncList_players.push(_t23);
      }
    })


    clearTimeout(window.syncPlayers_buildTimeout);

    window.syncPlayers_buildTimeout = setTimeout(function () {
      window.syncPlayers_isDzsapListBuilt = false;
    }, 500);

  }

}


export function convertPluginOptionsToFinalOptions(elThis, defaultOptions, argOptions = null, searchedAttr = 'data-options', $es) {

  var finalOptions = null;
  var tempOptions = {};
  var isSetFromJson = false;

  if ($es === undefined) {
    $es = jQuery;
  }


  var $elThis = $es(elThis);

  const isArgOptionsDefinedViaJs = Boolean(argOptions && typeof argOptions === 'object' && Object.keys(argOptions).length);


  if (isArgOptionsDefinedViaJs) {
    tempOptions = argOptions;
  } else {
    if ($elThis.attr(searchedAttr)) {
      try {
        tempOptions = JSON.parse($elThis.attr(searchedAttr));
        isSetFromJson = true;
      } catch (err) {

      }
    }
    if (!isSetFromJson) {
      if (typeof $elThis.attr(searchedAttr) != 'undefined' && $elThis.attr('data-options') != '') {
        let aux = $elThis.attr(searchedAttr);
        aux = 'var aux_opts = ' + aux;
        eval(aux);
        tempOptions = Object.assign({}, argOptions);
      }
    }
  }
  finalOptions = Object.assign(defaultOptions, tempOptions);

  return finalOptions;
}

export function generateFakeArrayForPcm() {


  var maxlen = 256;

  var arr = [];

  for (let it1 = 0; it1 < maxlen; it1++) {
    arr[it1] = Math.random() * 100;

  }

  return arr;
}
