(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=exports.DZSAP_SCRIPT_SELECTOR_KEYBOARD=exports.DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS=exports.DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS=exports.DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED=exports.DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM=exports.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM=exports.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW=exports.DZSAP_PLAYER_CLASS_LOADED=exports.DZSAP_PLAYER_CLASS_FOOTER_PLAYER=exports.ConstantsDzsAp=void 0;const ConstantsDzsAp={PLAYLIST_TRANSITION_DURATION:300,DEBUG_STYLE_1:"background-color: #aaa022; color: #222222;",DEBUG_STYLE_2:"background-color: #7c3b8e; color: #ffffff;",DEBUG_STYLE_3:"background-color: #3a696b; color: #eeeeee;",DEBUG_STYLE_ERROR:"background-color: #3a696b; color: #eeeeee;",URL_WAVESURFER_HELPER_BACKUP:"https://zoomthe.me/assets/dzsap-wave-generate.js",WAVESURFER_MAX_TIMEOUT:2e4,URL_JQUERY:"https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js",DEBUG_STYLE_PLAY_FUNCTIONS:"background-color: #daffda; color: #222222;",ERRORED_OUT_CLASS:"errored-out",ERRORED_OUT_MAX_ATTEMPTS:5};exports.ConstantsDzsAp=ConstantsDzsAp;const DZSAP_PLAYER_CLASS_FOOTER_PLAYER="dzsap_footer";exports.DZSAP_PLAYER_CLASS_FOOTER_PLAYER="dzsap_footer";const DZSAP_PLAYER_CLASS_LOADED="dzsap-loaded";exports.DZSAP_PLAYER_CLASS_LOADED="dzsap-loaded";const DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW="audioplayer-loaded";exports.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW="audioplayer-loaded";const DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom";exports.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom";const DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom-placeholder";exports.DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom-placeholder";const DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED="fake";exports.DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED="fake";const DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS="last";exports.DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS="last";const DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS="#dzsap-main-settings";exports.DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS="#dzsap-main-settings";const DZSAP_SCRIPT_SELECTOR_KEYBOARD="#dzsap-keyboard-controls";exports.DZSAP_SCRIPT_SELECTOR_KEYBOARD="#dzsap-keyboard-controls";const DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=500;exports.DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=500;
},{}],2:[function(require,module,exports){
"use strict";

var _constants = require("../../../configs/_constants");
/**
 *
 * @param {DzsAudioPlayer} selfClass
 */


function stickyPlayer_show(selfClass) {
  selfClass.$stickToBottomContainer.addClass(_constants.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW);
  const $dzsapStickToBottomPlaceholder = jQuery(`.${_constants.DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM}`);
  selfClass.$stickToBottomContainer.addClass(_constants.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW);
  $dzsapStickToBottomPlaceholder.eq(0).addClass('active');
  $dzsapStickToBottomPlaceholder.css({
    height: selfClass.$stickToBottomContainer.outerHeight() + 'px'
  });
}

window.dzsap_stickyPlayer_show = stickyPlayer_show;

},{"../../../configs/_constants":1}]},{},[2])


//# sourceMappingURL=sticky-player.js.map