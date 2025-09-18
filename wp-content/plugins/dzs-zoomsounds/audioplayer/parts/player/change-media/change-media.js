(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=exports.DZSAP_SCRIPT_SELECTOR_KEYBOARD=exports.DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS=exports.DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS=exports.DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED=exports.DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM=exports.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM=exports.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW=exports.DZSAP_PLAYER_CLASS_LOADED=exports.DZSAP_PLAYER_CLASS_FOOTER_PLAYER=exports.ConstantsDzsAp=void 0;const ConstantsDzsAp={PLAYLIST_TRANSITION_DURATION:300,DEBUG_STYLE_1:"background-color: #aaa022; color: #222222;",DEBUG_STYLE_2:"background-color: #7c3b8e; color: #ffffff;",DEBUG_STYLE_3:"background-color: #3a696b; color: #eeeeee;",DEBUG_STYLE_ERROR:"background-color: #3a696b; color: #eeeeee;",URL_WAVESURFER_HELPER_BACKUP:"https://zoomthe.me/assets/dzsap-wave-generate.js",WAVESURFER_MAX_TIMEOUT:2e4,URL_JQUERY:"https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js",DEBUG_STYLE_PLAY_FUNCTIONS:"background-color: #daffda; color: #222222;",ERRORED_OUT_CLASS:"errored-out",ERRORED_OUT_MAX_ATTEMPTS:5};exports.ConstantsDzsAp=ConstantsDzsAp;const DZSAP_PLAYER_CLASS_FOOTER_PLAYER="dzsap_footer";exports.DZSAP_PLAYER_CLASS_FOOTER_PLAYER="dzsap_footer";const DZSAP_PLAYER_CLASS_LOADED="dzsap-loaded";exports.DZSAP_PLAYER_CLASS_LOADED="dzsap-loaded";const DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW="audioplayer-loaded";exports.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW="audioplayer-loaded";const DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom";exports.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom";const DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom-placeholder";exports.DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom-placeholder";const DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED="fake";exports.DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED="fake";const DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS="last";exports.DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS="last";const DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS="#dzsap-main-settings";exports.DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS="#dzsap-main-settings";const DZSAP_SCRIPT_SELECTOR_KEYBOARD="#dzsap-keyboard-controls";exports.DZSAP_SCRIPT_SELECTOR_KEYBOARD="#dzsap-keyboard-controls";const DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=500;exports.DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=500;
},{}],2:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.dzsap_jQueryInit = dzsap_jQueryInit;
exports.string_curateClassName = string_curateClassName;
exports.simpleStringify = simpleStringify;
exports.getRelativeX = getRelativeX;
exports.isValid = exports.isInt = exports.setupTooltip = exports.sanitizeToCssPx = exports.getBaseUrl = exports.loadScriptIfItDoesNotExist = exports.decode_json = void 0;

var _constants = require("../configs/_constants");

const decode_json = function (arg) {
  var fout = {};

  if (arg) {
    try {
      fout = JSON.parse(arg);
    } catch (err) {
      return null;
    }
  }

  return fout;
};

exports.decode_json = decode_json;

async function dzsap_jQueryInit(callback, reject) {
  return new Promise((resolve, reject) => {
    if (window.jQuery) {
      resolve('jQuery loaded');
    } else {
      const script = document.createElement('script');

      script.onload = function () {
        if (window.jQuery) {
          resolve('jQuery loaded');
        } else {
          reject('error loading');
        }
      };

      script.src = _constants.ConstantsDzsAp.URL_JQUERY;
      document.head.appendChild(script);
    }

    setTimeout(() => {
      reject('error loading');
    }, 15000);
  });
}
/**
 *
 * @param {string} arg
 * @return {string}
 */


function string_curateClassName(arg) {
  arg = arg.replace('feed-dzsap', '');
  arg = arg.replace('feed-dzsap--extra-html', '');
  return arg;
}

function simpleStringify(object) {
  if (object && typeof object === 'object') {
    object = copyWithoutCircularReferences([object], object);
  }

  return JSON.stringify(object);

  function copyWithoutCircularReferences(references, object) {
    const cleanObject = {};
    Object.keys(object).forEach(function (key) {
      var value = object[key];

      if (value && typeof value === 'object') {
        if (references.indexOf(value) < 0) {
          references.push(value);
          cleanObject[key] = copyWithoutCircularReferences(references, value);
          references.pop();
        } else {
          cleanObject[key] = '###_Circular_###';
        }
      } else if (typeof value !== 'function') {
        cleanObject[key] = value;
      }
    });
    return cleanObject;
  }
}

const loadScriptIfItDoesNotExist = (scriptSrc, checkForVar) => {
  return new Promise((resolve, reject) => {
    if (checkForVar) {
      resolve('loadfromvar');
      return;
    }

    var script = document.createElement('script');

    script.onload = function () {
      resolve('loadfromload');
    };

    script.onerror = function () {
      reject();
    };

    script.src = scriptSrc;
    document.head.appendChild(script);
  });
};

exports.loadScriptIfItDoesNotExist = loadScriptIfItDoesNotExist;

const getBaseUrl = (baseUrlVar, scriptName) => {
  if (window[baseUrlVar]) {
    return window[baseUrlVar];
  }

  let scripts = document.getElementsByTagName("script");

  for (var scriptKey in scripts) {
    if (scripts[scriptKey] && scripts[scriptKey].src && String(scripts[scriptKey].src).indexOf(scriptName) > -1) {
      break;
    }
  }

  var baseUrl_arr = String(scripts[scriptKey].src).split('/');
  baseUrl_arr.splice(-1, 1);
  const result = string_addTrailingSlash(baseUrl_arr.join('/'));
  window[baseUrlVar] = result + '/';
  return result;
};

exports.getBaseUrl = getBaseUrl;

function string_addTrailingSlash(url) {
  var lastChar = url.substr(-1); // Selects the last character

  if (lastChar != '/') {
    // If the last character is not a slash
    url = url + '/'; // Append a slash to it.
  }

  return url;
}

const sanitizeToCssPx = arg => {
  if (String(arg).indexOf('%') > -1 || String(arg).indexOf('em') > -1 || String(arg).indexOf('px') > -1 || String(arg).indexOf('auto') > -1) {
    return arg;
  }

  return arg + 'px';
};

exports.sanitizeToCssPx = sanitizeToCssPx;

const setupTooltip = args => {
  var mainArgs = Object.assign({
    tooltipInnerHTML: '',
    tooltipIndicatorText: '',
    tooltipConClass: ''
  }, args);
  return `<div class="dzstooltip-con ${mainArgs.tooltipConClass}"><span class="dzstooltip main-tooltip   talign-end arrow-bottom style-rounded color-dark-light  dims-set transition-slidedown " style="width: 280px;"><span class="dzstooltip--inner">${mainArgs.tooltipInnerHTML}</span> </span></span><span class="tooltip-indicator">${mainArgs.tooltipIndicatorText}</span></div>`;
};

exports.setupTooltip = setupTooltip;

const isInt = function (n) {
  return typeof n == 'number' && Math.round(n) % 1 == 0;
};

exports.isInt = isInt;

const isValid = function (n) {
  return typeof n != 'undefined' && n != '';
};

exports.isValid = isValid;

function getRelativeX(mouseX, $el_) {
  if (jQuery) {
    return mouseX - jQuery($el_).offset().left;
  }
}

},{"../configs/_constants":1}],3:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.formatTime = formatTime;
exports.can_history_api = can_history_api;
exports.dzs_clean_string = dzs_clean_string;
exports.get_query_arg = get_query_arg;
exports.add_query_arg = add_query_arg;
exports.dzsap_is_mobile = dzsap_is_mobile;
exports.is_ios = is_ios;
exports.can_canvas = can_canvas;
exports.is_safari = is_safari;
exports.is_android = is_android;
exports.select_all = select_all;
exports.is_android_good = is_android_good;
exports.htmlEncode = htmlEncode;
exports.dzsap_generate_keyboard_tooltip = dzsap_generate_keyboard_tooltip;
exports.hexToRgb = hexToRgb;
exports.assignHelperFunctionsToJquery = assignHelperFunctionsToJquery;
exports.registerTextWidth = registerTextWidth;
exports.player_checkIfWeShouldShowAComment = player_checkIfWeShouldShowAComment;
exports.sanitizeObjectForChangeMediaArgs = sanitizeObjectForChangeMediaArgs;
exports.utils_sanitizeToColor = utils_sanitizeToColor;
exports.dzsapInitjQueryRegisters = dzsapInitjQueryRegisters;
exports.player_radio_isNameUpdatable = player_radio_isNameUpdatable;
exports.playerRegisterWindowFunctions = playerRegisterWindowFunctions;
exports.assignPcmData = assignPcmData;
exports.string_jsonConvertToArray = string_jsonConvertToArray;
exports.dzsap_singleton_ready_calls = dzsap_singleton_ready_calls;
exports.jQueryAuxBindings = jQueryAuxBindings;
exports.view_player_playMiscEffects = view_player_playMiscEffects;
exports.view_player_globalDetermineSyncPlayersIndex = view_player_globalDetermineSyncPlayersIndex;
exports.player_view_addMetaLoaded = player_view_addMetaLoaded;
exports.waitForScriptToBeAvailableThenExecute = waitForScriptToBeAvailableThenExecute;
exports.player_reinit_findIfPcmNeedsGenerating = player_reinit_findIfPcmNeedsGenerating;
exports.playerFunctions = playerFunctions;
exports.player_delete = player_delete;
exports.sanitizeToHexColor = sanitizeToHexColor;
exports.player_identifySource = player_identifySource;
exports.player_identifyTypes = player_identifyTypes;
exports.player_getPlayFromTime = player_getPlayFromTime;
exports.sanitizeToIntFromPointTime = sanitizeToIntFromPointTime;
exports.player_determineStickToBottomContainer = player_determineStickToBottomContainer;
exports.player_stickToBottomContainerDetermineClasses = player_stickToBottomContainerDetermineClasses;
exports.player_service_getSourceProtection = player_service_getSourceProtection;
exports.player_isGoingToSetupMediaNow = player_isGoingToSetupMediaNow;
exports.player_stopOtherPlayers = player_stopOtherPlayers;
exports.player_syncPlayers_gotoItem = player_syncPlayers_gotoItem;
exports.player_syncPlayers_buildList = player_syncPlayers_buildList;
exports.convertPluginOptionsToFinalOptions = convertPluginOptionsToFinalOptions;
exports.generateFakeArrayForPcm = generateFakeArrayForPcm;

var _dzs_helpers = require("../js_common/_dzs_helpers");

var _player_keyboard = require("./player/_player_keyboard");

var _constants = require("../configs/_constants");

function formatTime(arg) {
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

function can_history_api() {
  return !!(window.history && history.pushState);
}

function dzs_clean_string(arg) {
  if (arg) {
    arg = arg.replace(/[^A-Za-z0-9\-]/g, '');
    arg = arg.replace(/\./g, '');
    return arg;
  }

  return '';
}

function get_query_arg(purl, key) {
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
  } else {}
}

function add_query_arg(purl, key, value) {
  key = encodeURIComponent(key);
  value = encodeURIComponent(value);

  if (!purl) {
    purl = '';
  }

  var s = purl;
  var pair = key + "=" + value;
  var r = new RegExp("(&|\\?)" + key + "=[^\&]*");
  s = s.replace(r, "$1" + pair);

  if (s.indexOf(key + '=') > -1) {} else {
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

function dzsap_is_mobile() {
  return is_ios() || is_android();
}

function is_ios() {
  return navigator.platform.indexOf("iPhone") !== -1 || navigator.platform.indexOf("iPod") !== -1 || navigator.platform.indexOf("iPad") !== -1;
}

function can_canvas() {
  var oCanvas = document.createElement("canvas");
  return !!oCanvas.getContext("2d");
}

function is_safari() {
  return Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
}

function is_android() {
  var ua = navigator.userAgent.toLowerCase();
  return ua.indexOf("android") > -1;
}

function select_all(el) {
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

function is_android_good() {}

function htmlEncode(arg) {
  return jQuery('<div/>').text(arg).html();
}

function dzsap_generate_keyboard_tooltip(keyboard_controls, lab) {
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


function hexToRgb(hex, targetAlpha = null) {
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

function assignHelperFunctionsToJquery($) {
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
  };

  $.fn.prependOnce = function (arg, argfind) {
    var _t = $(this);

    argfind = checkIfHasClass(argfind, arg);

    if (_t.children(argfind).length < 1) {
      _t.prepend(arg);

      return true;
    }

    return false;
  };

  $.fn.appendOnce = function (arg, argfind) {
    var _t = $(this);

    argfind = checkIfHasClass(argfind, arg);

    if (_t.children(argfind).length < 1) {
      _t.append(arg);

      return true;
    }

    return false;
  };
}

;

function registerTextWidth($) {
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
    });

    var width = _lastspan.width();

    _lastspan.remove();

    return width;
  };
}

function player_checkIfWeShouldShowAComment(selfClass, real_time_curr, real_time_total) {
  var $ = jQuery;
  var timer_curr_perc = Math.round(real_time_curr / real_time_total * 100) / 100;

  if (selfClass.audioType === 'fake') {
    timer_curr_perc = Math.round(selfClass.timeCurrent / selfClass.timeTotal * 100) / 100;
  }

  if (selfClass._commentsHolder) {
    selfClass._commentsHolder.children().each(function () {
      var _t = $(this);

      if (_t.hasClass('dzstooltip-con')) {
        var _t_posx = _t.offset().left - selfClass._commentsHolder.offset().left;

        var aux = Math.round(parseFloat(_t_posx) / selfClass._commentsHolder.outerWidth() * 100) / 100;

        if (aux) {
          if (Math.abs(aux - timer_curr_perc) < 0.02) {
            selfClass._commentsHolder.find('.dzstooltip').removeClass('active');

            _t.find('.dzstooltip').addClass('active');
          } else {
            _t.find('.dzstooltip').removeClass('active');
          }
        }
      }
    });
  }
}

function sanitizeObjectForChangeMediaArgs(_sourceForChange) {
  var changeMediaArgs = {};
  var _feed_fakePlayer = _sourceForChange;
  var lab = '';

  if (_sourceForChange.data('original-settings')) {
    return _sourceForChange.data('original-settings');
  }

  changeMediaArgs.source = null;

  if (_feed_fakePlayer.attr('data-source')) {
    changeMediaArgs.source = _feed_fakePlayer.attr('data-source');
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

function utils_sanitizeToColor(colorString) {
  if (colorString.indexOf('#') === -1 && colorString.indexOf('rgb') === -1 && colorString.indexOf('hsl') === -1) {
    return '#' + colorString;
  }

  return colorString;
}

function dzsapInitjQueryRegisters() {}

function player_radio_isNameUpdatable(selfClass, radio_update_song_name, targetKey) {
  if (selfClass._metaArtistCon.find(targetKey).length && selfClass._metaArtistCon.find(targetKey).eq(0).text().length > 0) {
    if (selfClass._metaArtistCon.find(targetKey).eq(0).html().indexOf('{{update}}') > -1) {
      return true;
    }
  }

  return false;
}

function playerRegisterWindowFunctions() {
  window['dzsap_functions'] = {};

  window['dzsap_functions']['send_total_time'] = function (argtime, argcthis) {
    if (argtime && argtime !== Infinity) {
      const data = {
        action: 'dzsap_send_total_time_for_track',
        id_track: argcthis.attr('data-playerid'),
        postdata: Math.ceil(argtime)
      };
      jQuery.post(window.dzsap_ajaxurl, data, function (response) {});
    }
  };

  window.dzs_open_social_link = function (arg, argthis) {
    var leftPosition, topPosition;
    var w = 500,
        h = 500;
    leftPosition = window.screen.width / 2 - (w / 2 + 10);
    topPosition = window.screen.height / 2 - (h / 2 + 50);
    var windowFeatures = "status=no,height=" + h + ",width=" + w + ",resizable=yes,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no";
    arg = arg.replace('{{replacewithcurrurl}}', encodeURIComponent(window.location.href));

    if (argthis && argthis.attr) {
      arg = arg.replace(/{{replacewithdataurl}}/g, argthis.attr('data-url'));
    }

    const locationHref = window.location.href;
    const auxa = locationHref.split('?');
    let cid = '';
    let cid_gallery = '';

    if (argthis) {} else {
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
  };

  window.dzsap_wp_send_contor_60_secs = function (argcthis, argtitle) {
    var data = {
      video_title: argtitle,
      video_analytics_id: argcthis.attr('data-playerid'),
      curr_user: window.dzsap_curr_user
    };
    var theajaxurl = 'index.php?action=ajax_dzsap_submit_contor_60_secs';

    if (window.dzsap_settings.dzsap_site_url) {
      theajaxurl = dzsap_settings.dzsap_site_url + theajaxurl;
    }

    jQuery.ajax({
      type: "POST",
      url: theajaxurl,
      data: data,
      success: function (response) {},
      error: function (arg) {}
    });
  };

  window.dzsap_init_multisharer = function () {};
}
/**
 *
 * @param {DzsAudioPlayer} selfClass
 */


function assignPcmData(selfClass) {
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

function string_jsonConvertToArray(ar_str) {
  let waves = [];

  if (typeof ar_str == 'object') {
    waves = ar_str;
  } else {
    try {
      waves = JSON.parse(ar_str);
    } catch (err) {}
  }

  return waves;
}
/**
 * should be called only once on init
 */


function dzsap_singleton_ready_calls() {
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
  });
  jQuery(document).on('focus.dzsap', 'input', function () {
    window.dzsap_currplayer_focused = null;
  });
  registerTextWidth(jQuery);
  (0, _player_keyboard.dzsap_keyboardSetup)();
}

function jQueryAuxBindings($) {
  function handleClick_onGlobalZoomSoundsButton(e) {
    var $t = $(this);
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    if ($t.hasClass('audioplayer-song-changer')) {
      var _c = $($t.attr('data-fakeplayer')).eq(0);

      if (_c && _c.get(0) && _c.get(0).api_change_media) {
        _c.get(0).api_change_media($t, {
          'feeder_type': 'button',
          'call_from': 'changed audioplayer-song-changer'
        });
      }

      return false;
    }
  }

  $(document).off('click.dzsap_metas');
  $(document).on('click.dzsap_metas', '.audioplayer-song-changer', handleClick_onGlobalZoomSoundsButton);
}
/**
 * for .zoomsounds-wrapper-bg-center
 * @param selfClass
 */


function view_player_playMiscEffects(selfClass) {
  selfClass.$conPlayPause.addClass('playing');

  if (selfClass.cthis.parent().hasClass('zoomsounds-wrapper-bg-center')) {
    selfClass.cthis.parent().addClass('is-playing');
  }
}

function view_player_globalDetermineSyncPlayersIndex(selfClass) {
  if (selfClass._sourcePlayer === null && window.dzsap_syncList_players) {
    window.dzsap_syncList_players.forEach(($syncPlayer, index) => {
      if (selfClass.cthis.attr('data-playerid') == $syncPlayer.attr('data-playerid')) {
        window.dzsap_syncList_index = index;
      }
    });
  }
}

function player_view_addMetaLoaded(selfClass) {
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

function waitForScriptToBeAvailableThenExecute(verifyVar, callbackFn) {
  new Promise((resolve, reject) => {
    let checkInterval = 0;

    function checkIfVarExists() {
      if (verifyVar) {
        clearInterval(checkInterval);
        resolve('var exists');
      }
    }

    checkIfVarExists();
    checkInterval = setInterval(checkIfVarExists, 300);
    setTimeout(() => {
      reject('timeout');
    }, 5000);
  }).then(resolve => {
    callbackFn(resolve);
  }).catch(err => {});
}

function player_reinit_findIfPcmNeedsGenerating(selfClass) {
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

function playerFunctions(selfClass, functionType) {
  var o = selfClass.initOptions;

  if (functionType === 'detectIds') {
    if (o.skinwave_comments_playerid === '') {
      if (typeof selfClass.cthis.attr('id') !== 'undefined') {
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

function player_delete(selfClass) {
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

function sanitizeToHexColor(hexcolor) {
  if (hexcolor.indexOf('#') === -1) {
    hexcolor = '#' + hexcolor;
  }

  return hexcolor;
}

function player_identifySource(selfClass) {
  selfClass.data_source = selfClass.cthis.attr('data-source') || '';
}

function player_identifyTypes(selfClass) {
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

function player_getPlayFromTime(selfClass) {
  selfClass.playFrom = selfClass.initOptions.playfrom;

  if ((0, _dzs_helpers.isValid)(selfClass.cthis.attr('data-playfrom'))) {
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

function sanitizeToIntFromPointTime(arg) {
  arg = String(arg).replace('%3A', ':');
  arg = String(arg).replace('#', '');

  if (arg && String(arg).indexOf(':') > -1) {
    var arr = /(\d.*):(\d.*)/g.exec(arg);
    var m = parseInt(arr[1], 10);
    var s = parseInt(arr[2], 10);
    return m * 60 + s;
  } else {
    return Number(arg);
  }
}
/**
 * called in player init()
 * @param {DzsAudioPlayer} selfClass
 */


function player_determineStickToBottomContainer(selfClass) {
  let $conTest = selfClass.cthis.parent();

  if ($conTest.hasClass(_constants.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM)) {
    selfClass.$stickToBottomContainer = $conTest;
    selfClass.isStickyPlayer = true;
  }

  $conTest = selfClass.cthis.parent().parent();

  if ($conTest.hasClass(_constants.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM)) {
    selfClass.$stickToBottomContainer = $conTest;
    selfClass.isStickyPlayer = true;
  }
}
/**
 *
 * todo: maybe move
 * @param {DzsAudioPlayer} selfClass
 */


function player_stickToBottomContainerDetermineClasses(selfClass) {
  if (selfClass.$stickToBottomContainer) {
    if (selfClass.cthis.hasClass('theme-dark')) {
      selfClass.$stickToBottomContainer.addClass('theme-dark');
    }

    setTimeout(function () {
      selfClass.$stickToBottomContainer.addClass('inited');
    }, 500);
  }
}

function player_service_getSourceProtection(selfClass) {
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
  });
}

function player_isGoingToSetupMediaNow(selfClass) {
  return selfClass.audioTypeSelfHosted_streamType !== 'icecast' && selfClass.audioType !== 'youtube';
}

function player_stopOtherPlayers(dzsap_list, selfClass) {
  let i = 0;

  for (i = 0; i < dzsap_list.length; i++) {
    if (dzsap_list[i].get(0) && dzsap_list[i].get(0).api_pause_media && dzsap_list[i].get(0) != selfClass.cthis.get(0)) {
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

function player_syncPlayers_gotoItem(selfClass, targetIncrement) {
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

function player_syncPlayers_buildList() {
  if (!window.syncPlayers_isDzsapListBuilt) {
    window.dzsap_syncList_players = [];
    window.syncPlayers_isDzsapListBuilt = true;
    jQuery('.audioplayer.is-single-player,.audioplayer-tobe.is-single-player').each(function () {
      var _t23 = jQuery(this);

      if (_t23.hasClass(_constants.DZSAP_PLAYER_CLASS_FOOTER_PLAYER)) {
        return;
      }

      if (_t23.attr('data-do-not-include-in-list') !== 'on') {
        window.dzsap_syncList_players.push(_t23);
      }
    });
    clearTimeout(window.syncPlayers_buildTimeout);
    window.syncPlayers_buildTimeout = setTimeout(function () {
      window.syncPlayers_isDzsapListBuilt = false;
    }, 500);
  }
}

function convertPluginOptionsToFinalOptions(elThis, defaultOptions, argOptions = null, searchedAttr = 'data-options', $es) {
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
      } catch (err) {}
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

function generateFakeArrayForPcm() {
  var maxlen = 256;
  var arr = [];

  for (let it1 = 0; it1 < maxlen; it1++) {
    arr[it1] = Math.random() * 100;
  }

  return arr;
}

},{"../configs/_constants":1,"../js_common/_dzs_helpers":2,"./player/_player_keyboard":7}],4:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.retrieve_soundcloud_url = retrieve_soundcloud_url;

var _constants = require("../configs/_constants");

function retrieve_soundcloud_url(selfClass, pargs) {
  var o = selfClass.initOptions;

  if (o.soundcloud_apikey == '') {
    alert('soundcloud api key not defined, read docs!');
  }

  var aux = 'https://api.' + 'soundcloud.com' + '/resolve?url=' + selfClass.data_source + '&format=json&consumer_key=' + o.soundcloud_apikey;
  aux = encodeURIComponent(aux);
  var soundcloud_retriever = o.php_retriever + '?scurl=' + aux;
  jQuery.ajax({
    type: "GET",
    url: soundcloud_retriever,
    data: {},
    async: true,
    dataType: 'text',
    error: function (err, q, t) {},
    success: function (response) {
      var data = [];
      let newSource = '';

      try {
        data = JSON.parse(response);
        selfClass.audioType = 'selfHosted';

        if (data == '') {
          selfClass.cthis.addClass(_constants.ConstantsDzsAp.ERRORED_OUT_CLASS);
          selfClass.cthis.append('<div class="feedback-text">soundcloud track does not seem to serve via api</div>');
        }

        selfClass.original_real_mp3 = selfClass.cthis.attr('data-source');

        if (data.stream_url) {
          newSource = data.stream_url + '?consumer_key=' + o.soundcloud_apikey + '&origin=localhost';
          selfClass.cthis.attr('data-source', newSource);

          if (selfClass.$feed_fakeButton) {
            selfClass.$feed_fakeButton.attr('data-source', newSource);
          }

          if (selfClass._sourcePlayer) {
            selfClass._sourcePlayer.attr('data-source', newSource);
          }
        } else {
          selfClass.cthis.addClass(_constants.ConstantsDzsAp.ERRORED_OUT_CLASS);
          selfClass.cthis.append('<div class="feedback-text ">this soundcloud track does not allow streaming  </div>');
        }

        selfClass.data_source = newSource;

        if (selfClass.cthis.attr('data-pcm')) {
          selfClass.isAlreadyHasRealPcm = true;
        }

        if (o.design_skin == 'skin-wave') {
          if (o.skinwave_wave_mode == 'canvas') {
            if (selfClass.isAlreadyHasRealPcm == false) {
              if ((o.pcm_data_try_to_generate == 'on' && o.pcm_data_try_to_generate_wait_for_real_pcm == 'on') == false) {
                window.scrubModeWave__initGenerateWaveData(selfClass, {
                  'call_from': 'soundcloud init(), pcm not real..'
                });
              }
            }
          }
        }

        selfClass.setup_media({
          'called_from': 'change_media'
        });
        setTimeout(function () {
          if (selfClass.isPlayPromised) {
            selfClass.play_media({
              'call_from': 'change_media'
            });
            selfClass.isPlayPromised = false;
          }
        }, 300);
      } catch (err) {
        console.log('soduncloud parse error -', response, ' - ', soundcloud_retriever);
      }
    }
  });
}

},{"../configs/_constants":1}],5:[function(require,module,exports){
"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.svg_embed_btn=exports.svg_menu_state=exports.svg_next_btn=exports.svg_prev_btn=exports.svg_share_icon=exports.pausebtn_svg=exports.playbtn_svg=void 0;const playbtn_svg='<svg class="svg-icon" version="1.2" baseProfile="tiny" id="Layer_1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="25px" height="30px" viewBox="0 0 25 30" xml:space="preserve"> <path d="M24.156,13.195L2.406,0.25C2.141,0.094,1.867,0,1.555,0C0.703,0,0.008,0.703,0.008,1.562H0v26.875h0.008 C0.008,29.297,0.703,30,1.555,30c0.32,0,0.586-0.109,0.875-0.266l21.727-12.93C24.672,16.375,25,15.727,25,15 S24.672,13.633,24.156,13.195z"/> </svg>';exports.playbtn_svg=playbtn_svg;const pausebtn_svg='<svg class="svg-icon" version="1.1" id="Layer_3" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="12px" height="13px" viewBox="0 0 13.415 16.333" enable-background="new 0 0 13.415 16.333" xml:space="preserve"> <path fill="#D2D6DB" d="M4.868,14.59c0,0.549-0.591,0.997-1.322,0.997H2.2c-0.731,0-1.322-0.448-1.322-0.997V1.618 c0-0.55,0.592-0.997,1.322-0.997h1.346c0.731,0,1.322,0.447,1.322,0.997V14.59z"/> <path fill="#D2D6DB" d="M12.118,14.59c0,0.549-0.593,0.997-1.324,0.997H9.448c-0.729,0-1.322-0.448-1.322-0.997V1.619 c0-0.55,0.593-0.997,1.322-0.997h1.346c0.731,0,1.324,0.447,1.324,0.997V14.59z"/> </svg>';exports.pausebtn_svg=pausebtn_svg;const svg_share_icon='<svg class="svg-icon" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 511.626 511.627" style="enable-background:new 0 0 511.626 511.627;" xml:space="preserve"> <g> <path d="M506.206,179.012L360.025,32.834c-3.617-3.617-7.898-5.426-12.847-5.426s-9.233,1.809-12.847,5.426 c-3.617,3.619-5.428,7.902-5.428,12.85v73.089h-63.953c-135.716,0-218.984,38.354-249.823,115.06C5.042,259.335,0,291.03,0,328.907 c0,31.594,12.087,74.514,36.259,128.762c0.57,1.335,1.566,3.614,2.996,6.849c1.429,3.233,2.712,6.088,3.854,8.565 c1.146,2.471,2.384,4.565,3.715,6.276c2.282,3.237,4.948,4.859,7.994,4.859c2.855,0,5.092-0.951,6.711-2.854 c1.615-1.902,2.424-4.284,2.424-7.132c0-1.718-0.238-4.236-0.715-7.569c-0.476-3.333-0.715-5.564-0.715-6.708 c-0.953-12.938-1.429-24.653-1.429-35.114c0-19.223,1.668-36.449,4.996-51.675c3.333-15.229,7.948-28.407,13.85-39.543 c5.901-11.14,13.512-20.745,22.841-28.835c9.325-8.09,19.364-14.702,30.118-19.842c10.756-5.141,23.413-9.186,37.974-12.135 c14.56-2.95,29.215-4.997,43.968-6.14s31.455-1.711,50.109-1.711h63.953v73.091c0,4.948,1.807,9.232,5.421,12.847 c3.62,3.613,7.901,5.424,12.847,5.424c4.948,0,9.232-1.811,12.854-5.424l146.178-146.183c3.617-3.617,5.424-7.898,5.424-12.847 C511.626,186.92,509.82,182.636,506.206,179.012z" fill="#696969"/> </g></svg> ';exports.svg_share_icon=svg_share_icon;const svg_prev_btn='<svg class="svg-icon" version="1.1" id="Layer_2" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="14px" height="14px" viewBox="0 0 12.5 12.817" enable-background="new 0 0 12.5 12.817" xml:space="preserve"> <g> <g> <g> <path fill="#D2D6DB" d="M2.581,7.375c-0.744-0.462-1.413-0.94-1.486-1.061C1.021,6.194,1.867,5.586,2.632,5.158l2.35-1.313 c0.765-0.427,1.505-0.782,1.646-0.789s0.257,1.03,0.257,1.905V7.87c0,0.876-0.051,1.692-0.112,1.817 C6.711,9.81,5.776,9.361,5.032,8.898L2.581,7.375z"/> </g> </g> </g> <g> <g> <g> <path fill="#D2D6DB" d="M6.307,7.57C5.413,7.014,4.61,6.441,4.521,6.295C4.432,6.15,5.447,5.42,6.366,4.906l2.82-1.577 c0.919-0.513,1.809-0.939,1.979-0.947s0.309,1.236,0.309,2.288v3.493c0,1.053-0.061,2.033-0.135,2.182S10.144,9.955,9.25,9.4 L6.307,7.57z"/> </g> </g> </g> </svg>';exports.svg_prev_btn=svg_prev_btn;const svg_next_btn='<svg class="svg-icon" version="1.1" id="Layer_2" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="14px" height="14px" viewBox="0 0 12.5 12.817" enable-background="new 0 0 12.5 12.817" xml:space="preserve"> <g> <g> <g> <path fill="#D2D6DB" d="M9.874,5.443c0.744,0.462,1.414,0.939,1.486,1.06c0.074,0.121-0.771,0.729-1.535,1.156L7.482,8.967 C6.719,9.394,5.978,9.75,5.837,9.756C5.696,9.761,5.581,8.726,5.581,7.851V4.952c0-0.875,0.05-1.693,0.112-1.816 c0.062-0.124,0.995,0.326,1.739,0.788L9.874,5.443z"/> </g> </g> </g> <g> <g> <g> <path fill="#D2D6DB" d="M6.155,5.248c0.893,0.556,1.696,1.129,1.786,1.274c0.088,0.145-0.928,0.875-1.847,1.389l-2.811,1.57 c-0.918,0.514-1.808,0.939-1.978,0.947c-0.169,0.008-0.308-1.234-0.308-2.287V4.66c0-1.052,0.061-2.034,0.135-2.182 s1.195,0.391,2.089,0.947L6.155,5.248z"/> </g> </g> </g> </svg>';exports.svg_next_btn=svg_next_btn;const svg_menu_state='<svg class="svg-icon" version="1.1" id="Layer_2" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="13.25px" height="13.915px" viewBox="0 0 13.25 13.915" enable-background="new 0 0 13.25 13.915" xml:space="preserve"> <path d="M1.327,4.346c-0.058,0-0.104-0.052-0.104-0.115V2.222c0-0.063,0.046-0.115,0.104-0.115H11.58 c0.059,0,0.105,0.052,0.105,0.115v2.009c0,0.063-0.046,0.115-0.105,0.115H1.327z"/> <path d="M1.351,8.177c-0.058,0-0.104-0.051-0.104-0.115V6.054c0-0.064,0.046-0.115,0.104-0.115h10.252 c0.058,0,0.105,0.051,0.105,0.115v2.009c0,0.063-0.047,0.115-0.105,0.115H1.351z"/> <path d="M1.351,12.182c-0.058,0-0.104-0.05-0.104-0.115v-2.009c0-0.064,0.046-0.115,0.104-0.115h10.252 c0.058,0,0.105,0.051,0.105,0.115v2.009c0,0.064-0.047,0.115-0.105,0.115H1.351z"/> </svg>';exports.svg_menu_state=svg_menu_state;const svg_embed_btn='<svg class="svg-icon" version="1.2" baseProfile="tiny" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="15px" height="15px" viewBox="0 0 15 15" xml:space="preserve"> <g id="Layer_1"> <polygon fill="#E6E7E8" points="1.221,7.067 0.494,5.422 4.963,1.12 5.69,2.767 "/> <polygon fill="#E6E7E8" points="0.5,5.358 1.657,4.263 3.944,10.578 2.787,11.676 "/> <polygon fill="#E6E7E8" points="13.588,9.597 14.887,8.34 12.268,2.672 10.969,3.93 "/> <polygon fill="#E6E7E8" points="14.903,8.278 14.22,6.829 9.714,11.837 10.397,13.287 "/> </g> <g id="Layer_2"> <rect x="6.416" y="1.713" transform="matrix(0.9663 0.2575 -0.2575 0.9663 2.1699 -1.6329)" fill="#E6E7E8" width="1.805" height="11.509"/> </g> </svg>';exports.svg_embed_btn=svg_embed_btn;
},{}],6:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.configureAudioPlayerOptionsInitial = configureAudioPlayerOptionsInitial;
exports.player_detect_skinwave_mode = player_detect_skinwave_mode;
exports.player_viewApplySkinWaveModes = player_viewApplySkinWaveModes;
exports.player_determineHtmlAreas = player_determineHtmlAreas;
exports.player_determineActualPlayer = player_determineActualPlayer;
exports.player_adjustIdentifiers = player_adjustIdentifiers;

var _dzsap_helpers = require("../_dzsap_helpers");

var _dzs_helpers = require("../../js_common/_dzs_helpers");

var _dzsap_svgs = require("../_dzsap_svgs");
/**
 *
 * @param {jQuery} cthis
 * @param {object} o
 * @param {DzsAudioPlayer} selfClass
 */


function configureAudioPlayerOptionsInitial(cthis, o, selfClass) {
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

  cthis.addClass(o.extra_classes_player);

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

  if ((0, _dzsap_helpers.is_ios)()) {
    if (selfClass.initOptions.skinwave_enableSpectrum === 'on') {
      selfClass.initOptions.skinwave_enableSpectrum = 'off';
    }
  }

  const skin_regex = / skin-/g;

  if (skin_regex.test(cthis.attr('class'))) {} else {
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

  if ((0, _dzsap_helpers.is_ios)() || (0, _dzsap_helpers.is_android)()) {
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

  (0, _dzsap_helpers.player_reinit_findIfPcmNeedsGenerating)(selfClass);
}

function player_detect_skinwave_mode() {
  const selfClass = this;
  selfClass.skinwave_mode = selfClass.initOptions.skinwave_mode;

  if (selfClass.cthis.hasClass('skin-wave-mode-small')) {
    selfClass.skinwave_mode = 'small';
  }

  if (selfClass.cthis.hasClass('skin-wave-mode-alternate')) {
    selfClass.skinwave_mode = 'alternate';
  }

  if (selfClass.cthis.hasClass('skin-wave-mode-bigwavo')) {
    selfClass.skinwave_mode = 'bigwavo';
  }
}

function player_viewApplySkinWaveModes(selfClass) {
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

function player_determineHtmlAreas(selfClass) {
  var o = selfClass.initOptions;
  let extraHtmlBottom = '';
  let extraHtmlControlsLeft = '';
  let extraHtmlControlsRight = '';

  if (selfClass.cthis.children('.feed-dzsap--extra-html').length > 0 && o.settings_extrahtml === '') {
    selfClass.cthis.children('.feed-dzsap--extra-html').each(function () {
      let newExtraHtmlSectionClassName = (0, _dzs_helpers.string_curateClassName)(this.className);
      extraHtmlBottom += `<section class="dzsap-extra-html--section-bottom ${newExtraHtmlSectionClassName}">${this.innerHTML}</section>`;
    });
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
    selfClass.extraHtmlAreas.controlsRight = String(selfClass.extraHtmlAreas.controlsRight).replace(/{{svg_share_icon}}/g, _dzsap_svgs.svg_share_icon);
  }

  for (var key in selfClass.extraHtmlAreas) {
    selfClass.extraHtmlAreas[key] = String(selfClass.extraHtmlAreas[key]).replace('{{heart_svg}}', '\t&hearts;');
    selfClass.extraHtmlAreas[key] = String(selfClass.extraHtmlAreas[key]).replace('{{embed_code}}', selfClass.feedEmbedCode);
  }
}

function player_determineActualPlayer(selfClass) {
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

function player_adjustIdentifiers(selfClass) {
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
    selfClass.identifier_pcm = (0, _dzsap_helpers.dzs_clean_string)(selfClass.cthis.attr('data-source'));
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

},{"../../js_common/_dzs_helpers":2,"../_dzsap_helpers":3,"../_dzsap_svgs":5}],7:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.handle_keypresses = handle_keypresses;
exports.dzsap_keyboardSetup = exports.dzsap_generate_keyboard_controls = void 0;

var _constants = require("../../configs/_constants");

const dzsap_generate_keyboard_controls = function () {
  let keyboard_controls = {
    'play_trigger_step_back': 'off',
    'step_back_amount': '5',
    'step_back': '37',
    'step_forward': '39',
    'sync_players_goto_next': '',
    'sync_players_goto_prev': '',
    'pause_play': '32',
    'show_tooltips': 'off'
  };
  const $keyboardControlsInfo = jQuery(_constants.DZSAP_SCRIPT_SELECTOR_KEYBOARD);

  if ($keyboardControlsInfo.length) {
    window.dzsap_keyboard_controls = JSON.parse($keyboardControlsInfo.html());
  }

  if (window.dzsap_keyboard_controls) {
    keyboard_controls = jQuery.extend(keyboard_controls, window.dzsap_keyboard_controls);
  }

  keyboard_controls.step_back_amount = Number(keyboard_controls.step_back_amount);
  return keyboard_controls;
};

exports.dzsap_generate_keyboard_controls = dzsap_generate_keyboard_controls;

function handle_keypresses(e) {
  if (window.dzsap_isTextFieldFocused) {
    return;
  }

  function isKeyPressed(checkKeyCode) {
    let isKeyPressed = false;

    if (checkKeyCode.indexOf('ctrl+') > -1) {
      if (e.ctrlKey) {
        checkKeyCode = checkKeyCode.replace('ctrl+', '');

        if (e.keyCode === Number(checkKeyCode)) {
          isKeyPressed = true;
        }
      }
    } else {
      if (e.keyCode === Number(checkKeyCode)) {
        isKeyPressed = true;
      }
    }

    return isKeyPressed;
  }

  var $ = jQuery;
  const keyboard_controls = $.extend({}, dzsap_generate_keyboard_controls());

  if (dzsap_currplayer_focused && dzsap_currplayer_focused.api_pause_media) {
    if (isKeyPressed(keyboard_controls.pause_play)) {
      if (!$(dzsap_currplayer_focused).hasClass('comments-writer-active')) {
        if ($(dzsap_currplayer_focused).hasClass('is-playing')) {
          dzsap_currplayer_focused.api_pause_media();
        } else {
          dzsap_currplayer_focused.api_play_media();
        }

        if (window.dzsap_mouseover) {
          e.preventDefault();
          return false;
        }
      }
    }

    if (isKeyPressed(keyboard_controls.step_back)) {
      dzsap_currplayer_focused.api_step_back(keyboard_controls.step_back_amount);
    }

    if (isKeyPressed(keyboard_controls.step_forward)) {
      dzsap_currplayer_focused.api_step_forward(keyboard_controls.step_back_amount);
    }

    if (isKeyPressed(keyboard_controls.sync_players_goto_next)) {
      dzsap_currplayer_focused.api_sync_players_goto_next();
    }

    if (isKeyPressed(keyboard_controls.sync_players_goto_prev)) {
      dzsap_currplayer_focused.api_sync_players_goto_prev();
    }
  }
}
/**
 * called in singleton
 */


const dzsap_keyboardSetup = () => {
  let $ = jQuery;
  window.dzsap_isTextFieldFocused = false;
  $(document).off('keydown.dzsapkeyup');
  $(document).on('keydown.dzsapkeyup', handle_keypresses);
  $(document).on('focus blur', 'textarea,input', function (e) {
    if (e.type == 'focusin' || e.type == 'focus') {
      window.dzsap_isTextFieldFocused = true;
    }

    if (e.type == 'focusout' || e.type == 'blur') {
      window.dzsap_isTextFieldFocused = false;
    }
  });
  $(document).on('keydown blur', '.zoomsounds-search-field', function (e) {
    const _t = $(e.currentTarget);

    setTimeout(function () {
      if (_t) {
        let $audioGallery = $('.audiogallery').eq(0);

        if (_t.attr('data-target')) {
          $audioGallery = $(_t.attr('data-target'));
        }

        if ($audioGallery.get(0) && $audioGallery.get(0).api_filter) {
          $audioGallery.get(0).api_filter('title', _t.val());
        }
      }
    }, 100);
  });
};

exports.dzsap_keyboardSetup = dzsap_keyboardSetup;

},{"../../configs/_constants":1}],8:[function(require,module,exports){
"use strict";function scrubbar_modeWave_clearObsoleteCanvas(a){a._scrubbar&&a._scrubbar.find(".scrubbar-type-wave--canvas.transitioning-out").remove()}function scrubbar_modeWave_setupCanvas_context(a){if(a.get(0)){const r=a.get(0).getContext("2d");r.imageSmoothingEnabled=!1,r.imageSmoothing=!1,r.imageSmoothingQuality="high",r.webkitImageSmoothing=!1}}function scrubbar_modeWave_setupCanvas(a,r){var s={prepare_for_transition_in:!1};a&&(s=Object.assign(s,a));var e="",b="",t=r.initOptions;e='<canvas class="scrubbar-type-wave--canvas scrub-bg-img',e+='" ></canvas>',b='<canvas class="scrubbar-type-wave--canvas scrub-prog-img',b+='" ></canvas>',r._scrubbar.find(".scrub-bg").eq(0).append(e),r._scrubbar.find(".scrub-prog").eq(0).append(b),r._scrubbarbg_canvas=r._scrubbar.find(".scrub-bg-img").last(),r._scrubbarprog_canvas=r._scrubbar.find(".scrub-prog-img").last(),"on"===t.skinwave_enableSpectrum&&r._scrubbarprog_canvas.hide(),scrubbar_modeWave_setupCanvas_context(r._scrubbarbg_canvas),scrubbar_modeWave_setupCanvas_context(r._scrubbarprog_canvas),s.prepare_for_transition_in&&(r._scrubbarbg_canvas.addClass("preparing-transitioning-in"),r._scrubbarprog_canvas.addClass("preparing-transitioning-in"),setTimeout(function(){r._scrubbarbg_canvas.addClass("transitioning-in"),r._scrubbarprog_canvas.addClass("transitioning-in")},20))}function view_player_scrubModeWaveAdjustCurrTimeAndTotalTime(a){const r=a.cthis;if(a.scrubbarProgX<0&&(a.scrubbarProgX=0),a.scrubbarProgX=parseInt(a.scrubbarProgX,10),a.scrubbarProgX<2&&r.data("promise-to-play-footer-player-from"))return!1;a.$currTime.css({left:a.scrubbarProgX}),a.scrubbarProgX>a.scrubbarWidth-a.currTime_outerWidth&&a.$currTime.css({left:a.scrubbarWidth-a.currTime_outerWidth}),a.scrubbarProgX>a.scrubbarWidth-30&&a.scrubbarWidth?a.$totalTime.css({opacity:1-(a.scrubbarProgX-(a.scrubbarWidth-30))/30}):"1"!==a.$totalTime.css("opacity")&&a.$totalTime.css({opacity:""})}Object.defineProperty(exports,"__esModule",{value:!0}),exports.scrubbar_modeWave_clearObsoleteCanvas=scrubbar_modeWave_clearObsoleteCanvas,exports.scrubbar_modeWave_setupCanvas_context=scrubbar_modeWave_setupCanvas_context,exports.scrubbar_modeWave_setupCanvas=scrubbar_modeWave_setupCanvas,exports.view_player_scrubModeWaveAdjustCurrTimeAndTotalTime=view_player_scrubModeWaveAdjustCurrTimeAndTotalTime;
},{}],9:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.scrubModeWave__checkIfWeShouldTryToGetPcm = scrubModeWave__checkIfWeShouldTryToGetPcm;
exports.view_drawCanvases = view_drawCanvases;
exports.scrubModeWave__view_transitionIn = scrubModeWave__view_transitionIn;
exports.draw_canvas = draw_canvas;

var _dzsap_helpers = require("../_dzsap_helpers");

var _constants = require("../../configs/_constants");

var _dzs_helpers = require("../../js_common/_dzs_helpers");

var _player_scrubModeWave = require("../player/_player_scrubModeWave");

window.dzsap_wavesurfer_load_attempt = 0;
window.dzsap_wavesurfer_is_trying_to_generate = null;
var dzsapWaveRender = void 0;
/**
 * called on init_loaded
 * @param selfClass
 * @param pargs
 * @returns {boolean}
 */

function scrubModeWave__checkIfWeShouldTryToGetPcm(selfClass, pargs) {
  var margs = {
    'call_from': 'default',
    'call_attempt': 0
  };

  if (pargs) {
    margs = jQuery.extend(margs, pargs);
  } // -- retry


  if (window.dzsap_wavesurfer_is_trying_to_generate) {
    setTimeout(function () {
      margs.call_attempt++;

      if (margs.call_attempt < 10) {
        scrubModeWave__checkIfWeShouldTryToGetPcm(selfClass, margs);
        ;
      }
    }, 1000);
    return false;
  }

  if (selfClass.isPcmRequiredToGenerate) {
    if (isWeCanGeneratePcm(selfClass)) {
      window.dzsap_wavesurfer_is_trying_to_generate = selfClass;
      window.dzsap_get_base_url();
      let wavesurferUrl = window.dzsap_base_url ? window.dzsap_base_url + 'parts/wavesurfer/dzsap-wave-generate.js' : _constants.ConstantsDzsAp.URL_WAVESURFER_HELPER_BACKUP;
      window.scrubModeWave__view_transitionIn = scrubModeWave__view_transitionIn;
      (0, _dzs_helpers.loadScriptIfItDoesNotExist)(wavesurferUrl, window.scrubModeWave__initedGenerateWave).then(resolveStr => {
        scrubModeWave__initGenerateWaveData(selfClass);
      });
    }
  }
}

function isWeCanGeneratePcm(selfClass) {
  if (selfClass.isAlreadyHasRealPcm) {
    return false;
  }

  return selfClass.data_source != 'fake';
}

function view_drawCanvases(selfClass, argpcm, calledFrom) {
  var o = selfClass.initOptions;
  draw_canvas(selfClass._scrubbarbg_canvas.get(0), argpcm, "#" + o.design_wave_color_bg, {
    call_from: calledFrom + '_bg',
    selfClass,
    'skinwave_wave_mode_canvas_waves_number': parseInt(o.skinwave_wave_mode_canvas_waves_number, 10),
    'skinwave_wave_mode_canvas_waves_padding': parseInt(o.skinwave_wave_mode_canvas_waves_padding, 10)
  });
  draw_canvas(selfClass._scrubbarprog_canvas.get(0), argpcm, '#' + o.design_wave_color_progress, {
    call_from: calledFrom + '_prog',
    selfClass,
    'skinwave_wave_mode_canvas_waves_number': parseInt(o.skinwave_wave_mode_canvas_waves_number, 10),
    'skinwave_wave_mode_canvas_waves_padding': parseInt(o.skinwave_wave_mode_canvas_waves_padding, 10)
  }, true);
}
/**
 * called on isPcmRequiredToGenerate ( init_loaded )  / change_media
 * @param selfClass
 * @param argpcm
 */


function scrubModeWave__view_transitionIn(selfClass, argpcm) {
  selfClass._scrubbar.find('.scrub-bg-img,.scrub-prog-img').removeClass('transitioning-in');

  selfClass._scrubbar.find('.scrub-bg-img,.scrub-prog-img').addClass('transitioning-out');

  ;
  (0, _player_scrubModeWave.scrubbar_modeWave_setupCanvas)({
    'prepare_for_transition_in': true
  }, selfClass);
  view_drawCanvases(selfClass, argpcm, 'canvas_generate_wave_data_animate_pcm');
  setTimeout(() => {
    (0, _player_scrubModeWave.scrubbar_modeWave_clearObsoleteCanvas)(selfClass);
  }, 300); // -- warning - not always real pcm

  selfClass.isAlreadyHasRealPcm = true;
  selfClass.scrubbar_reveal();
}
/**
 * aggregate wave array based on max
 * @param waveArrayTemp
 * @returns {array}
 */


function waveCalculateWaveArray(waveArrayTemp) {
  let max = 0;
  let waveArrayNew = [];
  let barIndex = 0;
  let waveArray = (0, _dzsap_helpers.string_jsonConvertToArray)(waveArrayTemp); // -- normalizing

  for (barIndex = 0; barIndex < waveArray.length; barIndex++) {
    if (waveArray[barIndex] > max) {
      max = waveArray[barIndex];
    }
  }

  for (barIndex = 0; barIndex < waveArray.length; barIndex++) {
    waveArrayNew[barIndex] = parseFloat(Math.abs(waveArray[barIndex]) / Number(max));
  } // -- end normalize


  waveArray = waveArrayNew;
  return waveArray;
}

function view_drawBars(_canvasContext, isReflection, isProgressScrubContext, hexcolor, barCount, waveArray, widthCanvas, heightCanvas, reflection_size, playerOptions, margs, selfClass, bar_space) {
  let isWithinSample = false;
  var searched_index = null;
  var lastBarHeight = 0;
  var gradient = null;
  var spectrum_isBarWithinProgress = false; // -- color the bar in progress colors

  hexcolor = (0, _dzsap_helpers.sanitizeToHexColor)(hexcolor); // -- left right gradient

  var temp_hex = hexcolor;
  temp_hex = temp_hex.replace('#', '');
  var hexcolors = []; // -- hex colors array

  if (temp_hex.indexOf(',') > -1) {
    hexcolors = temp_hex.split(',');
  }

  if (barCount == 1) {
    barCount = widthCanvas / barCount;
  }

  if (barCount == 2) {
    barCount = widthCanvas / 2;
  }

  if (barCount == 3) {
    barCount = widthCanvas / 3;
  }

  if (widthCanvas / barCount < 1) {
    barCount = Math.ceil(barCount / 3);
  }

  var widthBar = Math.ceil(widthCanvas / barCount);
  var sizeRatioNormal = 1 - reflection_size;

  if (widthBar == 0) {
    widthBar = 1;
    bar_space = 0;
  }

  if (widthBar == 1) {
    bar_space = bar_space / 2;
  }

  var progress_hexcolor = '';
  var progress_hexcolors = '';

  if (margs.call_from == 'spectrum') {
    progress_hexcolor = playerOptions.design_wave_color_progress;
    progress_hexcolor = progress_hexcolor.replace('#', '');
    progress_hexcolors = []; // -- hex colors array

    if (progress_hexcolor.indexOf(',') > -1) {
      progress_hexcolors = progress_hexcolor.split(',');
    }
  }

  for (let barIndex = 0; barIndex < barCount; barIndex++) {
    isWithinSample = false;

    _canvasContext.save();

    searched_index = Math.ceil(barIndex * (waveArray.length / barCount)); // -- we'll try to prevent

    if (barIndex < barCount / 5) {
      if (waveArray[searched_index] < 0.1) {
        waveArray[searched_index] = 0.1;
      }
    }

    if (waveArray.length > barCount * 2.5 && barIndex > 0 && barIndex < waveArray.length - 1) {
      waveArray[searched_index] = Math.abs(waveArray[searched_index] + waveArray[searched_index - 1] + waveArray[searched_index + 1]) / 3;
    } // -- normalize end


    let targetRatio = sizeRatioNormal;

    if (isReflection) {
      targetRatio = reflection_size;
    }

    let barHeight = Math.abs(waveArray[searched_index] * heightCanvas * targetRatio);

    if (playerOptions.skinwave_wave_mode_canvas_normalize == 'on') {
      if (isNaN(lastBarHeight)) {
        lastBarHeight = 0;
      }

      barHeight = barHeight / 1.5 + lastBarHeight / 2.5;
    }

    lastBarHeight = barHeight;
    _canvasContext.lineWidth = 0;
    barHeight = Math.floor(barHeight);
    const barPositionTop = isReflection ? heightCanvas * sizeRatioNormal : Math.ceil(heightCanvas * targetRatio - barHeight);

    _canvasContext.beginPath();

    _canvasContext.rect(barIndex * widthBar, barPositionTop, widthBar - bar_space, barHeight);

    if (margs.call_from == 'spectrum') {
      if (barIndex / barCount < selfClass.timeCurrent / selfClass.timeTotal) {
        spectrum_isBarWithinProgress = true;
      } else {
        spectrum_isBarWithinProgress = false;
      }
    }

    if (selfClass.isSample) {
      isWithinSample = isBeforeOrAfterSample(barIndex, barCount, selfClass);
    }

    if (spectrum_isBarWithinProgress) {
      if (isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto') {
        _canvasContext.fillStyle = (0, _dzsap_helpers.hexToRgb)(progress_hexcolor, 0.25);
      } else {
        _canvasContext.fillStyle = isWithinSample ? (0, _dzsap_helpers.hexToRgb)(progress_hexcolor, 0.5) : '#' + progress_hexcolor;
      }

      if (progress_hexcolors.length) {
        const startColor = isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto' ? (0, _dzsap_helpers.hexToRgb)('#' + progress_hexcolors[0], 0.25) : '#' + progress_hexcolors[0];
        const endColor = isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto' ? (0, _dzsap_helpers.hexToRgb)('#' + progress_hexcolors[1], 0.25) : '#' + progress_hexcolors[1];
        gradient = _canvasContext.createLinearGradient(0, 0, 0, heightCanvas);
        gradient.addColorStop(0, startColor);
        gradient.addColorStop(1, endColor);
        _canvasContext.fillStyle = gradient;
      }
    } else {
      /**
       * normal
       */
      if (isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto') {
        _canvasContext.fillStyle = (0, _dzsap_helpers.hexToRgb)(hexcolor, 0.25);
      } else {
        _canvasContext.fillStyle = isWithinSample ? (0, _dzsap_helpers.hexToRgb)(hexcolor, 0.5) : '' + hexcolor;
      } // -- if we have gradient


      if (hexcolors.length) {
        const startColor = isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto' ? (0, _dzsap_helpers.hexToRgb)((0, _dzsap_helpers.utils_sanitizeToColor)(hexcolors[0]), 0.25) : '' + (0, _dzsap_helpers.utils_sanitizeToColor)(hexcolors[0]);
        const endColor = isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto' ? (0, _dzsap_helpers.hexToRgb)((0, _dzsap_helpers.utils_sanitizeToColor)(hexcolors[1]), 0.25) : '' + (0, _dzsap_helpers.utils_sanitizeToColor)(hexcolors[1]);
        gradient = _canvasContext.createLinearGradient(0, 0, 0, heightCanvas);
        gradient.addColorStop(0, startColor);
        gradient.addColorStop(1, endColor);
        _canvasContext.fillStyle = gradient;
      }
    }

    if (!(isWithinSample && isProgressScrubContext)) {
      _canvasContext.fill();

      _canvasContext.closePath();
    }

    _canvasContext.restore();
  }
}
/**
 * draw with different color
 * @returns {boolean}
 * @param {number} currBarIndex
 * @param {number} barCount
 * @param {DzsAudioPlayer} selfClass
 */


function isBeforeOrAfterSample(currBarIndex, barCount, selfClass) {
  if (currBarIndex / barCount < selfClass.timeModel.sampleTimeStart / selfClass.timeModel.sampleTimeTotal || currBarIndex / barCount > selfClass.timeModel.sampleTimeEnd / selfClass.timeModel.sampleTimeTotal) {
    return true;
  }

  return false;
}
/**
 * draw canvas here
 * @param $canvas_
 * @param pcm_arr
 * @param hexcolor
 * @param pargs
 * @param {boolean} isProgressScrubContext
 * @returns {boolean}
 */


function draw_canvas($canvas_, pcm_arr, hexcolor, pargs, isProgressScrubContext = false) {
  let margs = {
    'call_from': 'default',
    'is_sample': false,
    'selfClass': null,
    'sample_time_start': 0,
    'sample_time_end': 0,
    'sample_time_total': 0,
    'skinwave_wave_mode_canvas_waves_number': 2,
    'skinwave_wave_mode_canvas_waves_padding': 1
  };
  const $ = jQuery;

  if (pargs) {
    margs = Object.assign(margs, pargs);
  }

  var _canvas = $($canvas_);

  var __canvas = $canvas_;

  if (_canvas.get(0)) {
    var {
      selfClass,
      skinwave_wave_mode_canvas_waves_number,
      skinwave_wave_mode_canvas_waves_padding
    } = margs;
    let playerOptions = {};

    var _canvasContext = _canvas.get(0).getContext("2d");

    var waveArrayTemp = pcm_arr;
    var widthCanvas;
    var heightCanvas;
    let waveArray = []; // -- sanitize

    if (isNaN(Number(skinwave_wave_mode_canvas_waves_number))) {
      skinwave_wave_mode_canvas_waves_number = 2;
    }

    if (isNaN(Number(skinwave_wave_mode_canvas_waves_padding))) {
      if (skinwave_wave_mode_canvas_waves_number !== 1) {
        skinwave_wave_mode_canvas_waves_padding = 1;
      } else {
        skinwave_wave_mode_canvas_waves_padding = 0;
      }
    }

    if (selfClass) {
      playerOptions = selfClass.initOptions;
    }

    let barCount = skinwave_wave_mode_canvas_waves_number;
    let bar_space = skinwave_wave_mode_canvas_waves_padding;

    if (_canvas && _canvas.get(0)) {} else {
      return false;
    }

    if (selfClass && selfClass._scrubbar) {
      if (selfClass._scrubbarprog_canvas) {
        selfClass._scrubbarbg_canvas.width(selfClass._scrubbar.width());

        selfClass._scrubbarprog_canvas.width(selfClass._scrubbar.width());

        $canvas_.width = selfClass._scrubbar.width();
        $canvas_.height = selfClass._scrubbar.height();
      }
    }

    waveArray = waveCalculateWaveArray(waveArrayTemp);

    if (selfClass) {
      __canvas.width = selfClass._scrubbar.width();
    }

    widthCanvas = __canvas.width;
    heightCanvas = __canvas.height;
    const reflection_size = parseFloat(playerOptions.skinwave_wave_mode_canvas_reflection_size); // -- left right gradient END

    _canvasContext.clearRect(0, 0, widthCanvas, heightCanvas);

    view_drawBars(_canvasContext, false, isProgressScrubContext, hexcolor, barCount, waveArray, widthCanvas, heightCanvas, reflection_size, playerOptions, margs, selfClass, bar_space);

    if (reflection_size > 0) {
      view_drawBars(_canvasContext, true, isProgressScrubContext, hexcolor, barCount, waveArray, widthCanvas, heightCanvas, reflection_size, playerOptions, margs, selfClass, bar_space);
    } // -- reflection


    setTimeout(function () {
      selfClass.scrubbar_reveal();
    }, 100);
  }
}

},{"../../configs/_constants":1,"../../js_common/_dzs_helpers":2,"../_dzsap_helpers":3,"../player/_player_scrubModeWave":8}],10:[function(require,module,exports){
"use strict";

var _dzsap_helpers = require("../../../jsinc/_dzsap_helpers");

var _constants = require("../../../configs/_constants");

var _waveRenderFunctions = require("../../../jsinc/wave-render/_wave-render-functions");

var _player_config = require("../../../jsinc/player/_player_config");

var _dzsap_misc = require("../../../jsinc/_dzsap_misc");
/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param {jQuery} $
 * @returns {function(*, *=): (boolean|undefined)}
 */


function media_changeMedia(selfClass, $) {
  /**
   * change media source for the player / change_media("song.mp3", {type:"audio", fakeplayer_is_feeder:"off"});
   * @param {HTMLElement|string} $playerSource - can be player dom element
   * @param pargs - {type:"audio", fakeplayer_is_feeder:"off"}
   * @returns {boolean}
   */
  return function ($playerSource, pargs = {}) {
    const changeMediaArgsDefaults = {
      type: '',
      fakeplayer_is_feeder: 'off' // -- this is OFF in case there is a button feeding it, and on if it's an actual player
      ,
      called_from: 'default',
      source: 'default',
      pcm: '',
      artist: "",
      song_name: "",
      thumb: "",
      thumb_link: "",
      autoplay: "on",
      cue: "on",
      feeder_type: "player",
      source_player_do_not_update: "off",
      playerid: ""
    };
    selfClass.ajax_view_submitted = 'on'; // -- view submitted from caller

    var handle_resize_delay = 500;
    let changeMediaArgs = { ...changeMediaArgsDefaults,
      ...pargs
    };
    var o = selfClass.initOptions;
    const $sourceForChange = $playerSource; // -- let us decide if we pause old player

    var isGoingToPauseTheOldPlayer = true; // pause previous player

    var isGoingToPauseTheActualPlayer = false; // pause if

    var isSourceAStringSource = false;
    var isSourceAZoomSoundsAudioplayer = false;
    let isSourceA$Object = false;
    let newSource = '';

    if ($sourceForChange && $sourceForChange.attr) {
      isSourceA$Object = true;
    }

    selfClass.reinit_resetMetrics();
    selfClass.reinit_beforeChangeMedia();

    if (typeof $sourceForChange === 'string') {
      isSourceAStringSource = true;
    }

    if (!isSourceAStringSource) {
      if ($playerSource.hasClass('audioplayer') || $playerSource.hasClass('audioplayer-tobe')) {
        isSourceAZoomSoundsAudioplayer = true;
      }
    }

    $('.current-feeder-for-parent-player').removeClass('current-feeder-for-parent-player');

    if (selfClass.$reflectionVisualObject) {
      selfClass.$reflectionVisualObject.removeClass('is-playing');
    }

    if (isSourceAStringSource) {
      newSource = $sourceForChange;
    }

    if (isSourceA$Object) {
      newSource = $sourceForChange.attr('data-source');
      selfClass.$reflectionVisualObject = $sourceForChange;
      changeMediaArgs = { ...changeMediaArgs,
        ...(0, _dzsap_helpers.sanitizeObjectForChangeMediaArgs)($sourceForChange)
      };
    }

    if (changeMediaArgs.source && changeMediaArgs.source != 'default') {
      newSource = changeMediaArgs.source;
    }

    if (selfClass.data_source === newSource) {
      isGoingToPauseTheOldPlayer = false;
    } // -- old feed fake player


    if (isGoingToPauseTheOldPlayer && selfClass._sourcePlayer) {
      selfClass._sourcePlayer.get(0).api_pause_media_visual({
        'call_from': 'change_media'
      });

      selfClass._sourcePlayer.get(0).api_set_timeVisualTotal(0);
    } // -- we are in one mode, so we need to preserve the originalSettings of the first item


    if (!selfClass.cthis.data('original-settings') && selfClass.data_source !== 'fake') {
      selfClass.cthis.data('original-settings', (0, _dzsap_helpers.sanitizeObjectForChangeMediaArgs)(selfClass.cthis));
    }

    const oldSource = selfClass.data_source;
    selfClass.data_source = newSource;
    const isSourceHasClassForDomSource = !isSourceAStringSource && !!($sourceForChange.hasClass('audioplayer') || $sourceForChange.hasClass('is-zoomsounds-source-player'));

    if (isSourceHasClassForDomSource || changeMediaArgs.fakeplayer_is_feeder === 'on') {
      selfClass.set_sourcePlayer($sourceForChange);

      if (selfClass._sourcePlayer) {
        selfClass.cthis.data('feeding-from', selfClass._sourcePlayer.get(0));

        selfClass._sourcePlayer.addClass('current-feeder-for-parent-player');
      }
    }

    if (!isSourceAStringSource && $sourceForChange) {
      if ($sourceForChange && $sourceForChange.attr('data-playerid')) {
        selfClass.cthis.attr('data-feed-playerid', $sourceForChange.attr('data-playerid'));
      } else {
        selfClass.cthis.attr('data-feed-playerid', '');
        changeMediaArgs.playerid = '';
      }
    }

    if (oldSource === newSource) {
      if (selfClass.cthis.hasClass('is-playing')) {
        selfClass.pause_media();
      } else {
        selfClass.play_media();
      }

      return false;
    }

    if (changeMediaArgs.type === 'detect' || changeMediaArgs.type === 'audio' || changeMediaArgs.type === 'normal') {
      changeMediaArgs.type = 'selfHosted';
    }

    selfClass.cthis.removeClass('meta-loaded'); // -- footer placeholder

    if (selfClass.cthis.parent().hasClass('audioplayer-was-loaded')) {
      selfClass.cthis.parent().addClass('audioplayer-loaded');
      $('body').addClass('footer-audioplayer-loaded');
      selfClass.cthis.parent().removeClass('audioplayer-was-loaded');
    }

    if (selfClass.$stickToBottomContainer) {
      selfClass.$stickToBottomContainer.addClass('audioplayer-loaded');
    }

    selfClass.cthis.removeClass(_constants.ConstantsDzsAp.ERRORED_OUT_CLASS);
    selfClass.destroy_media();
    selfClass.cthis.attr('data-source', changeMediaArgs.source);
    const original_type = changeMediaArgs.type;

    if (changeMediaArgs.type === 'mediafile') {
      changeMediaArgs.type = 'audio';
    }

    if (changeMediaArgs.type) {
      if (changeMediaArgs.type === 'soundcloud') {
        changeMediaArgs.type = 'audio';
      }

      if (changeMediaArgs.type === 'album_part') {
        changeMediaArgs.type = 'audio';
      }

      selfClass.cthis.attr('data-type', changeMediaArgs.type);
      selfClass.audioType = changeMediaArgs.type;
      o.type = changeMediaArgs.type;
    }

    if (o.design_skin === 'skin-wave') {
      if (o.skinwave_wave_mode === 'canvas') {
        if (selfClass._sourcePlayer) {
          selfClass.data_source = $sourceForChange.attr('data-source');
        } else {
          if (typeof $sourceForChange === 'string') {
            selfClass.data_source = $sourceForChange;
          }
        }

        if ($sourceForChange && changeMediaArgs.pcm) {
          if (selfClass.initOptions.scrubbar_type === 'wave') {
            selfClass.cthis.attr('data-pcm', $sourceForChange.attr('data-pcm'));
            (0, _dzsap_helpers.assignPcmData)(selfClass);
            (0, _waveRenderFunctions.scrubModeWave__view_transitionIn)(selfClass, $sourceForChange.attr('data-pcm'));
          }
        } else {
          (0, _dzsap_helpers.player_reinit_findIfPcmNeedsGenerating)(selfClass);
          (0, _waveRenderFunctions.scrubModeWave__checkIfWeShouldTryToGetPcm)(selfClass, {});
        }
      } // -- inside skin-wave


      if (changeMediaArgs.thumb) {
        if (selfClass.cthis.find('.the-thumb').length) {
          selfClass.cthis.find('.the-thumb').css('background-image', 'url(' + changeMediaArgs.thumb + ')');
        } else {
          selfClass.cthis.attr('data-thumb', changeMediaArgs.thumb);
          selfClass.setupStructure_thumbnailCon();
        }
      }
    }

    if (changeMediaArgs.thumb) {
      if (selfClass.cthis.find('.the-thumb').length) {
        selfClass.cthis.find('.the-thumb').css('background-image', 'url(' + changeMediaArgs.thumb + ')');
      } else {
        selfClass.cthis.attr('data-thumb', changeMediaArgs.thumb);
        selfClass.setupStructure_thumbnailCon();
      }

      selfClass.cthis.removeClass('does-not-have-thumb');
      selfClass.cthis.addClass('has-thumb');
    } else {
      selfClass.cthis.addClass('does-not-have-thumb');
      selfClass.cthis.removeClass('has-thumb');
    }

    if (changeMediaArgs.pcm === '') {
      selfClass.setup_pcm_random_for_now();
    }

    (0, _player_config.player_adjustIdentifiers)(selfClass);
    handle_resize_delay = 100;

    if (!isSourceAStringSource && $sourceForChange) {
      // -- .feed-dzsap-for-extra-html-right will be appended to the footer player
      var selector = '';
      var $feedExtraHtmlRightFromSource = null;

      if ($sourceForChange.find('.feed-dzsap-for-extra-html-right').length) {
        $feedExtraHtmlRightFromSource = $sourceForChange.find('.feed-dzsap-for-extra-html-right').eq(0);
      } else {
        // -- we use this for Shop Builder
        if (selfClass._sourcePlayer) {
          if (selfClass._sourcePlayer.attr('data-playerid') && $(document).find('.feed-dzsap-for-extra-html-right[data-playerid="' + selfClass._sourcePlayer.attr('data-playerid') + '"]').length) {
            $feedExtraHtmlRightFromSource = $(document).find('.feed-dzsap-for-extra-html-right[data-playerid="' + selfClass._sourcePlayer.attr('data-playerid') + '"]').eq(0);
          }
        }
      }

      if ($feedExtraHtmlRightFromSource) {
        selfClass.classMetaParts.set_extraHtmlFloatRight($feedExtraHtmlRightFromSource.html());
      }
    }

    if (changeMediaArgs.artist) {
      selfClass.cthis.find('.the-artist').html(changeMediaArgs.artist);
    }

    if (changeMediaArgs.song_name) {
      selfClass.cthis.find('.the-name').html(changeMediaArgs.song_name);
    }

    if (changeMediaArgsDefaults.source_player_do_not_update === 'on') {
      selfClass.set_sourcePlayer(null);
    }

    if (original_type === 'soundcloud' && changeMediaArgs.source.indexOf('api.soundcloud') === -1) {
      selfClass.data_source = changeMediaArgs.source;
      selfClass.isPlayPromised = true;
      setTimeout(function () {
        selfClass.isPlayPromised = true;
      }, 501);
      (0, _dzsap_misc.retrieve_soundcloud_url)(selfClass);
    } else {
      // -- setup media for all sources
      // -- make sure source is not fake
      selfClass.setup_media({
        'called_from': 'change_media'
      });
    }

    selfClass.timeModel.getSampleTimesFromDom(selfClass._sourcePlayer);

    if (selfClass.audioType === 'fake') {
      return false;
    }

    if (selfClass.initOptions.action_audio_change_media) {
      selfClass.initOptions.action_audio_change_media(changeMediaArgs.source, changeMediaArgs);
    }

    if (changeMediaArgs.autoplay === 'on') {
      selfClass.play_media_visual();

      if (!isSourceAZoomSoundsAudioplayer) {
        setTimeout(function () {
          selfClass.play_media({
            'called_from': 'changeMediaArgs.autoplay'
          });
        }, 100);
      }
    }

    selfClass.reinit();

    if (o.skinwave_enableSpectrum === 'on') {
      selfClass.spectrum_analyser = null;
    }

    setTimeout(function () {
      selfClass.handleResize(null, {
        called_from: 'change_media'
      });
    }, handle_resize_delay);
  };
}
/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param {jQuery} $
 */


window.dzsap_part_mediaChange_init = function (selfClass, $) {
  selfClass.cthis.get(0).api_change_media = media_changeMedia(selfClass, $); // -- change the media file from the API
};

window.dzsap_part_mediaChange_loaded = true;
window.media_changeMedia = media_changeMedia;

},{"../../../configs/_constants":1,"../../../jsinc/_dzsap_helpers":3,"../../../jsinc/_dzsap_misc":4,"../../../jsinc/player/_player_config":6,"../../../jsinc/wave-render/_wave-render-functions":9}]},{},[10])


//# sourceMappingURL=change-media.js.map