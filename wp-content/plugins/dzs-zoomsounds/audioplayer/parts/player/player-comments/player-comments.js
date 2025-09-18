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

},{"../configs/_constants":1,"../js_common/_dzs_helpers":2,"./player/_player_keyboard":4}],4:[function(require,module,exports){
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

},{"../../configs/_constants":1}],5:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.comments_selector_event = void 0;

var _dzs_helpers = require("../../../js_common/_dzs_helpers");

var _dzsap_helpers = require("../../../jsinc/_dzsap_helpers");

window.dzsap_part_comments_loaded = true;
const TOOLTIP_ALL_TALIGN_CLASSES = `talign-start talign-center talign-end`;
const STRUCTURE_COMMENTS_WRITER = `<div class="dzstooltip dzstooltip--comments-writer    talign-center arrow-top style-rounded color-dark-light    dims-set transition-slidedown " style="width: 330px;">  <div class="dzstooltip--inner"><div class="comments-writer"><div class="comments-writer-inner">
<div class="comments-writer--form">

                <div class="dzsap-comments--section">

                  <textarea name="comment-text" placeholder="Your comment.." type="text" class="comment-input"></textarea>

                </div>
                <div class="dzsap-comments--section">
                  <input placeholder="Your email.." name="comment-email" type="text" class="comment-input">
                </div>
                <div class="dzsap-comments--section overflow-and-fixed  ">

                  <div class="flex-grow-1   "><span
                    class="dzsap-comments--comment-form-label">commenting on </span> <span
                    class="dzsap-comments--comment-form-label-time">1:07</span></div>
                  <div class="flex-grow-0 margin-left-auto"><button class="submit-ap-comment dzs-button-dzsap float-right">&#10148; Submit</button></div>
                  <div class="clear"></div>

                </div>
              </div>

              <div class="comments-writer--avatar-con">
                <div class="comments-writer--avatar" style=""></div>
              </div>
              </div></div><span class="dzstooltip--close"><span
              class="label--x-button">&#10006;</span></span></div></div>`;
/**
 *
 * @param {DzsAudioPlayer} selfClass
 */

const hide_comments_writer = function (selfClass) {
  var $ = jQuery;
  selfClass.cthis.removeClass('comments-writer-active');

  selfClass._commentsHolder.find('.dzstooltip-con.placeholder').remove();

  selfClass.$commentsWritter.removeClass('active');
  selfClass.$commentsWritter.css({
    'height': 0
  });

  if (selfClass.initOptions.parentgallery && $(selfClass.initOptions.parentgallery).get(0) !== undefined && $(selfClass.initOptions.parentgallery).get(0).api_handleResize !== undefined) {
    $(selfClass.initOptions.parentgallery).get(0).api_handleResize();
  }

  setTimeout(function () {
    selfClass.cthis.find('.comments-writter-temp-css,.dzsap-style-comments').remove();
  }, 300);
};
/**
 *
 * @param {DzsAudioPlayer} selfClass
 */


window.comments_setupCommentsInitial = function (selfClass) {
  var $ = jQuery;
  var o = selfClass.initOptions;

  if (selfClass.cthis.find('.the-comments').length > 0 && selfClass.cthis.find('.the-comments').eq(0).children().length > 0) {
    selfClass.$commentsChildren = selfClass.cthis.find('.the-comments').eq(0).children();
  }

  var str_comments_holder = '<div class="comments-holder">';

  if (o.skinwave_comments_links_to) {
    str_comments_holder += '<a href="' + o.skinwave_comments_links_to + '" target="_blank" class="the-bg"></a>';
  } else {
    str_comments_holder += '<div class="the-comments-holder-bg"><div class="the-avatar comments-avatar--placeholder"></div></div>';
  }

  str_comments_holder += '</div><div class="clear"></div>' + STRUCTURE_COMMENTS_WRITER;

  selfClass._scrubbar.appendOnce(str_comments_holder);

  selfClass._commentsHolder = selfClass.cthis.find('.comments-holder').eq(0);
  selfClass.$commentsWritter = selfClass.cthis.find('.dzstooltip--comments-writer').eq(0);
  comments_setupCommentsHolder(selfClass);

  selfClass._commentsHolder.on('click', function (e) {
    comments_handleClickCommentsBg(selfClass, this, e);
  });

  selfClass._commentsHolder.on('mousemove', function (e) {
    selfClass._commentsHolder.find('.comments-avatar--placeholder').css('left', `${(0, _dzs_helpers.getRelativeX)(e.pageX, e.currentTarget) - 7}px`);
  });

  selfClass.$commentsWritter.find('.dzstooltip--close').on('click', e => {
    comments_handleClickCancel(selfClass, e);
  });
  selfClass.$commentsWritter.find('.submit-ap-comment').on('click', e => {
    comments_handleClickSubmit(selfClass, e);
  });
};
/**
 *
 * @param {DzsAudioPlayer} selfClass
 */


const comments_setupCommentsHolder = function (selfClass) {
  var $ = jQuery;
  var o = selfClass.initOptions;

  if (selfClass._commentsHolder && selfClass.$commentsChildren) {
    selfClass.$commentsChildren.each(function () {
      var _c = $(this);

      if (o.skinwave_comments_process_in_php === 'on') {
        if (_c && _c.hasClass && _c.hasClass('dzstooltip-con')) {
          if (_c.find('.dzstooltip > .dzstooltip--inner').length) {} else {
            _c.find('.dzstooltip').wrapInner('<div class="dzstooltip--inner"></div>');

            _c.find('.the-avatar').addClass('tooltip-indicator');

            _c.find('.dzstooltip').before(_c.find('.tooltip-indicator'));

            _c.find('.dzstooltip').addClass('talign-start style-rounded color-dark-light');
          }
        }
      }

      selfClass._commentsHolder.append(_c);
    });
  }
};

function comments_updateCommentHolderTimerWhenReady(selfClass, percClickFromScrubWidth) {
  if (selfClass.timeModel.getVisualTotalTime()) {
    selfClass.$commentsWritter.find('.dzsap-comments--comment-form-label-time').html((0, _dzsap_helpers.formatTime)(percClickFromScrubWidth * selfClass.timeModel.getVisualTotalTime()));
  } else {
    setTimeout(() => {
      comments_updateCommentHolderTimerWhenReady(selfClass, percClickFromScrubWidth);
    }, 100);
  }
}

const comments_handleClickCommentsBg = function (selfClass, argThis, e) {
  var $ = jQuery;
  var o = selfClass.initOptions;
  var $commentsHolder = $(argThis);
  var leftMouseX = parseInt(e.clientX, 10) - $commentsHolder.offset().left;
  let percClickFromScrubWidth = leftMouseX / $commentsHolder.width();
  selfClass.commentPositionPerc = `calc(${percClickFromScrubWidth * 100}% - 7px)`;
  comments_updateCommentHolderTimerWhenReady(selfClass, percClickFromScrubWidth);

  if (o.skinwave_comments_links_to) {
    return;
  }

  if (o.skinwave_comments_allow_post_if_not_logged_in === 'off' && !(window.dzsap_settings && window.dzsap_settings.comments_username)) {
    return false;
  } // -- start


  var sw = true;

  selfClass._commentsHolder.children().each(function () {
    var $commentElement = $(this);

    if ($commentElement.hasClass('placeholder') || $commentElement.hasClass('the-bg')) {
      return;
    }

    var lmx2 = $commentElement.offset().left - $commentsHolder.offset().left;

    if (Math.abs(lmx2 - leftMouseX) < 20) {
      selfClass._commentsHolder.find('.dzstooltip-con.placeholder').remove();

      sw = false;
      return false;
    }
  });

  if (!sw) {
    return false;
  } // todo: assign left on tooltip


  selfClass.$commentsWritter.css({
    'left': `${leftMouseX}px`
  });
  selfClass.$commentsWritter.css('top', parseInt(selfClass._commentsHolder.css('top'), 10) + 20 + 'px');

  if (selfClass.$commentsWritter.hasClass('active') === false) {
    selfClass.$commentsWritter.addClass('active');
    selfClass.cthis.addClass('comments-writer-active');
  }

  if (window.dzsap_settings && window.dzsap_settings.comments_username) {
    selfClass.cthis.find('input[name=comment-email]').remove();
  } else {
    selfClass.$commentsWritter.find('.comments-writer--avatar-con').remove();
  }

  add_comments_placeholder(selfClass);
};

const ajax_comment_publish = function (argp) {
  // -- only handles ajax call + result
  var selfClass = this;
  var $ = jQuery;
  var o = selfClass.initOptions;
  var mainarg = argp;
  var data = {
    action: 'dzsap_front_submitcomment',
    postdata: mainarg,
    playerid: selfClass.the_player_id,
    comm_position: selfClass.commentPositionPerc
  };

  if (window.dzsap_settings) {
    data.skinwave_comments_avatar = window.dzsap_settings.comments_avatar;
    data.skinwave_comments_account = window.dzsap_settings.comments_username;
  }

  if (selfClass.cthis.find('*[name=comment-email]').length > 0) {
    data.email = selfClass.cthis.find('*[name=comment-email]').eq(0).val();
  }

  if (selfClass.urlToAjaxHandler) {
    jQuery.ajax({
      type: "POST",
      url: selfClass.urlToAjaxHandler,
      data: data,
      success: function (response) {
        if (response.charAt(response.length - 1) == '0') {
          response = response.slice(0, response.length - 1);
        }

        var fout_tooltipData = ''; // -- process php

        fout_tooltipData = '';
        fout_tooltipData += `<div class="dzstooltip-con dzsap--comment" style="left:${selfClass.commentPositionPerc}"><span class="dzstooltip arrow-from-start transition-slidein  arrow-bottom talign-start style-rounded color-dark-light  " style="width: 250px;"><span class="dzstooltip--inner"><span class="the-comment-author">@${window.dzsap_settings.comments_username}</span> says:<br>${(0, _dzsap_helpers.htmlEncode)(data.postdata)}</span></span><fig class="the-avatar" style="background-image: url(${window.dzsap_settings.comments_avatar})"></fig></div>`;

        selfClass._commentsHolder.append(fout_tooltipData);

        if (selfClass.action_audio_comment) {
          selfClass.action_audio_comment(selfClass.cthis, fout_tooltipData);
        }
      },
      error: function (arg) {
        selfClass._commentsHolder.append(data.postdata);
      }
    });
  }
};
/**
 *
 * @param {DzsAudioPlayer} selfClass
 */


function add_comments_placeholder(selfClass) {
  var strCommentsAvatarUrl = '';

  if (window.dzsap_settings && window.dzsap_settings.comments_avatar) {
    strCommentsAvatarUrl = window.dzsap_settings.comments_avatar;
  }

  selfClass._commentsHolder.remove('.dzsap-style-comments');

  selfClass._commentsHolder.append('<style class="dzsap-style-comments">.dzstooltip-con:not(.placeholder) { opacity: 0.5; }</style>');

  selfClass._commentsHolder.find('.dzstooltip-con.placeholder').remove();

  selfClass._commentsHolder.append('<span class="dzstooltip-con placeholder" style="left:' + selfClass.commentPositionPerc + ';"><div class="the-avatar" style="background-image: url(' + strCommentsAvatarUrl + ')"></div></span>');
}

const comments_handleClickCancel = function (selfClass, e) {
  hide_comments_writer(selfClass);
};

function comment_submit(selfClass, comment_text, comment_email, comment_username) {
  var $ = jQuery;
  var o = selfClass.initOptions;
  var comm_author = '';

  if (comment_email) {
    const regex_mail = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    if (regex_mail.test(comment_email) == false) {
      alert('please insert email, your email is just used for gravatar. it will not be sent or stored anywhere');
      return false;
    }

    comm_author = String(comment_email).split('@')[0];

    if (selfClass.$commentsSelector) {
      selfClass.$commentsSelector.find('*[name=comment_email],*[name=comment_user]').remove();
    }

    if (!window.dzsap_settings) {
      window.dzsap_settings = {};
    }

    window.dzsap_settings.comments_username = comm_author;
    window.dzsap_settings.comments_avatar = 'https://secure.gravatar.com/avatar/' + window.MD5(String(selfClass.cthis.find('input[name=comment-email]').eq(0).val()).toLowerCase()) + '?s=20';
  }

  var aux = ''; // -- process php

  aux += comment_text;
  selfClass.cthis.find('*[name=comment-text]').eq(0).val('');
  selfClass.cthis.find('.comments-writter-temp-css,.dzsap-style-comments').remove();
  ajax_comment_publish.bind(selfClass)(aux);
  hide_comments_writer(selfClass);

  if (o.parentgallery && $(o.parentgallery).get(0) != undefined && $(o.parentgallery).get(0).api_player_commentSubmitted != undefined) {
    $(o.parentgallery).get(0).api_player_commentSubmitted();
  }
}
/**
 *
 * @param selfClass
 * @param e
 * @returns {boolean}
 */


const comments_handleClickSubmit = function (selfClass, e) {
  var comment_email = '';

  if (selfClass.cthis.find('input[name=comment-email]').length) {
    comment_email = selfClass.cthis.find('input[name=comment-email]').eq(0).val();
  }

  comment_submit(selfClass, selfClass.cthis.find('*[name=comment-text]').eq(0).val(), comment_email);
  return false;
};
/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param e
 * @returns {boolean}
 */


const comments_selector_event = function (selfClass, e) {
  const $ = jQuery;

  const _t = $(this);

  let _con = null;

  if (_t.parent().parent().hasClass('zoomsounds-comment-wrapper')) {
    _con = _t.parent().parent();
  }

  if (_t.parent().parent().parent().hasClass('zoomsounds-comment-wrapper')) {
    _con = _t.parent().parent().parent();
  }

  if (e.type == 'focusin') {
    var spx = selfClass.timeCurrent / selfClass.timeTotal * selfClass._commentsHolder.width();

    spx += 'px';
    selfClass.commentPositionPerc = `calc(${selfClass.timeCurrent / selfClass.timeTotal * 100}% - 7px)`;

    _con.addClass('active');

    add_comments_placeholder(selfClass);
  }

  if (e.type == 'focusout') {}

  if (e.type == 'click') {
    if (_t.hasClass('dzstooltip--close')) {
      _con.removeClass('active');

      _con.find('input').val('');
    }

    if (_t.hasClass('comments-btn-submit')) {
      var comment_email = '';

      if (_con.find('.comment_email').length) {
        comment_email = _con.find('.comment_email').eq(0).val();
      }

      comment_submit(selfClass, _con.find('.comment_text').eq(0).val(), comment_email);

      _con.removeClass('active');

      _con.find('input').val('');

      return false;
    }
  }
};

exports.comments_selector_event = comments_selector_event;

window.player_commentsSelectorInit = function (selfClass, $, cthis, o) {
  selfClass.$commentsSelector = jQuery(o.skinwave_comments_mode_outer_selector);

  if (selfClass.$commentsSelector.data) {
    selfClass.$commentsSelector.data('parent', cthis);

    if (window.dzsap_settings.comments_username) {
      selfClass.$commentsSelector.find('.comment_email,*[name=comment_user]').remove();
    }

    selfClass.$commentsSelector.on('click', '.dzstooltip--close,.comments-btn-submit', comments_selector_event);
    selfClass.$commentsSelector.on('focusin', 'input', comments_selector_event);
    selfClass.$commentsSelector.on('focusout', 'input', comments_selector_event);
  } else {
    console.log('%c, data not available .. ', 'color: #990000;', $(o.skinwave_comments_mode_outer_selector));
  }
};

},{"../../../js_common/_dzs_helpers":2,"../../../jsinc/_dzsap_helpers":3}]},{},[5])


//# sourceMappingURL=player-comments.js.map