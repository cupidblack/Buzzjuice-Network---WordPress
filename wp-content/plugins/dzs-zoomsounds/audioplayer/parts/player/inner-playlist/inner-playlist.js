(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=exports.DZSAP_SCRIPT_SELECTOR_KEYBOARD=exports.DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS=exports.DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS=exports.DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED=exports.DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM=exports.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM=exports.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW=exports.DZSAP_PLAYER_CLASS_LOADED=exports.DZSAP_PLAYER_CLASS_FOOTER_PLAYER=exports.ConstantsDzsAp=void 0;const ConstantsDzsAp={PLAYLIST_TRANSITION_DURATION:300,DEBUG_STYLE_1:"background-color: #aaa022; color: #222222;",DEBUG_STYLE_2:"background-color: #7c3b8e; color: #ffffff;",DEBUG_STYLE_3:"background-color: #3a696b; color: #eeeeee;",DEBUG_STYLE_ERROR:"background-color: #3a696b; color: #eeeeee;",URL_WAVESURFER_HELPER_BACKUP:"https://zoomthe.me/assets/dzsap-wave-generate.js",WAVESURFER_MAX_TIMEOUT:2e4,URL_JQUERY:"https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js",DEBUG_STYLE_PLAY_FUNCTIONS:"background-color: #daffda; color: #222222;",ERRORED_OUT_CLASS:"errored-out",ERRORED_OUT_MAX_ATTEMPTS:5};exports.ConstantsDzsAp=ConstantsDzsAp;const DZSAP_PLAYER_CLASS_FOOTER_PLAYER="dzsap_footer";exports.DZSAP_PLAYER_CLASS_FOOTER_PLAYER="dzsap_footer";const DZSAP_PLAYER_CLASS_LOADED="dzsap-loaded";exports.DZSAP_PLAYER_CLASS_LOADED="dzsap-loaded";const DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW="audioplayer-loaded";exports.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW="audioplayer-loaded";const DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom";exports.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom";const DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom-placeholder";exports.DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom-placeholder";const DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED="fake";exports.DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED="fake";const DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS="last";exports.DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS="last";const DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS="#dzsap-main-settings";exports.DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS="#dzsap-main-settings";const DZSAP_SCRIPT_SELECTOR_KEYBOARD="#dzsap-keyboard-controls";exports.DZSAP_SCRIPT_SELECTOR_KEYBOARD="#dzsap-keyboard-controls";const DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=500;exports.DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=500;
},{}],2:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.svg_footer_playlist = void 0;
var _constants = require("../../../configs/_constants");
window.dzsap_moving_playlist_item = false;
window.dzsap_playlist_con = null;
window.dzsap_playlist_item_moving = null;
window.dzsap_playlist_item_target = null;
window.dzsap_inner_playlist_is_loaded = true;
const svg_footer_playlist = '<svg class="svg-icon" version="1.1" id="Layer_2" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="13.25px" height="13.915px" viewBox="0 0 13.25 13.915" enable-background="new 0 0 13.25 13.915" xml:space="preserve"> <path d="M1.327,4.346c-0.058,0-0.104-0.052-0.104-0.115V2.222c0-0.063,0.046-0.115,0.104-0.115H11.58 c0.059,0,0.105,0.052,0.105,0.115v2.009c0,0.063-0.046,0.115-0.105,0.115H1.327z"/> <path d="M1.351,8.177c-0.058,0-0.104-0.051-0.104-0.115V6.054c0-0.064,0.046-0.115,0.104-0.115h10.252 c0.058,0,0.105,0.051,0.105,0.115v2.009c0,0.063-0.047,0.115-0.105,0.115H1.351z"/> <path d="M1.351,12.182c-0.058,0-0.104-0.05-0.104-0.115v-2.009c0-0.064,0.046-0.115,0.104-0.115h10.252 c0.058,0,0.105,0.051,0.105,0.115v2.009c0,0.064-0.047,0.115-0.105,0.115H1.351z"/> </svg>';
exports.svg_footer_playlist = svg_footer_playlist;
window.DzsapInnerPlaylist = class DzsapInnerPlaylist {
  /**
   *
   * @param {DzsAudioPlayer} dzsapClass
   */
  constructor(dzsapClass) {
    this.dzsapClass = dzsapClass;
    this.$playlistInner = null;
  }
  init() {
    /** @var {DzsAudioPlayer} selfClass  */
    const selfClass = this.dzsapClass;
    selfClass.cthis.get(0).api_regenerate_playerlist_inner = function () {
      // -- call with window.dzsap_generate_list_for_sync_players({'force_regenerate': true})
      if (selfClass.classFunctionalityInnerPlaylist) {
        selfClass.classFunctionalityInnerPlaylist.playlistInner_setupStructureInPlayer();
      }
    }; // -- regenerate the playlist innter

    const thisClass = this;
    selfClass._apControlsRight.append('<div class="btn-footer-playlist for-hover dzstooltip-con player-but"> <div class="tooltip-indicator tooltip-indicator--btn-footer-playlist"><div class="the-icon-bg"></div> ' + svg_footer_playlist + '    </div><div class="dzstooltip playlist-tooltip style-default color-light-dark arrow-bottom talign-end transition-scaleup active2"><div class="dzstooltip--inner"> </div></div></div>');
    thisClass.$playlistInner = selfClass.cthis.find('.playlist-tooltip');
    selfClass.cthis.on('mousedown', '.the-sort-handle', handle_mouse);
    selfClass.cthis.on('click', '.playlist-menu-item', handle_mouse);
    setTimeout(function () {
      thisClass.playlistInner_setupStructureInPlayer();
    }, 100);
    function handle_mouse(e) {
      var $t = jQuery(this);
      if (e.type === 'click') {
        if ($t.hasClass('playlist-menu-item')) {
          var ind = $t.parent().children().index($t);
          thisClass.playlistInner_gotoItem(ind, {
            'call_from': 'handle_mouse'
          });
        }
      }
      if (e.type === 'mousedown') {
        const _con = $t.parent();
        _con.parent().append(_con.clone().addClass('cloner'));
        const _clone = _con.parent().children('.cloner').eq(0);
        window.dzsap_playlist_con = _con.parent();
        window.dzsap_moving_playlist_item = true;
        window.dzsap_playlist_item_target = _con;
        window.dzsap_playlist_item_moving = _clone;
        _con.addClass('target-playlist-item');
      }
    }
  }
  playlistInner_setupStructureInPlayer(pargs) {
    // -- setup playlist for footer
    var $ = jQuery;
    var thisClass = this;
    var selfClass = this.dzsapClass;
    var margs = {
      'call_from': "default"
    };
    if (pargs) {
      margs = $.extend(margs, pargs);
    }
    if (thisClass.$playlistInner) {
      window.dzsap_syncList_players.length ? thisClass.$playlistInner.parent().removeClass('is-empty') : thisClass.$playlistInner.parent().addClass('is-empty'); // -- clear all before adding

      thisClass.$playlistInner.find('.dzstooltip--inner').html('');
      var stringPlaylistMenuItems = '';
      for (var keySyncPlayer in window.dzsap_syncList_players) {
        // -- setup inner playlist for sticky player
        var _c = window.dzsap_syncList_players[keySyncPlayer];
        if (_c.hasClass('number-wrapper') || _c.hasClass('for-number-wrapper')) {
          continue;
        }
        stringPlaylistMenuItems += '<div class="playlist-menu-item"';
        $.each(_c.get(0).attributes, function () {
          // -- we remember attributes in case the page has changed and we lost..
          if (this.specified && this.name && this.name !== 'id' && this.name !== 'style') {
            stringPlaylistMenuItems += ' ' + this.name + '=\'' + this.value + '\'';
          }
        });
        stringPlaylistMenuItems += '>';
        if (_c.attr('data-thumb')) {
          stringPlaylistMenuItems += '<div class="pi-thumb-con">';
          stringPlaylistMenuItems += '<div class="pi-thumb divimage" style="background-image: url(' + _c.attr('data-thumb') + ')">';
          stringPlaylistMenuItems += '</div>';
          stringPlaylistMenuItems += '</div>';
        }
        stringPlaylistMenuItems += '<div class="pi-meta-con">';
        stringPlaylistMenuItems += '<div class="pi-the-artist">';
        stringPlaylistMenuItems += _c.find('.the-artist').eq(0).text();
        stringPlaylistMenuItems += '</div>';
        stringPlaylistMenuItems += '<div class="pi-the-name">';
        stringPlaylistMenuItems += _c.find('.the-name').eq(0).text();
        stringPlaylistMenuItems += '</div>';
        stringPlaylistMenuItems += '</div>';
        stringPlaylistMenuItems += '<div class="the-sort-handle">';
        stringPlaylistMenuItems += '&#x2195;';
        stringPlaylistMenuItems += '</div>';
        stringPlaylistMenuItems += '</div>';
      }
      thisClass.$playlistInner.find('.dzstooltip--inner').append(stringPlaylistMenuItems);
      $(document).on('mousemove.dzsap_playlist_item', function (e) {
        if (window.dzsap_moving_playlist_item) {
          var my = e.clientY;
          my -= window.dzsap_playlist_con.offset().top;
          dzsap_playlist_item_moving.css('top', my - 20);
          dzsap_playlist_item_target.parent().children(':not(".target-playlist-item"):not(".cloner")').each(function () {
            var _t = $(this);
            var tmy = _t.offset().top - window.dzsap_playlist_con.offset().top;
            if (my > tmy) {
              _t.after(dzsap_playlist_item_target);
            }
          });
          if (my < 50) {
            dzsap_playlist_item_target.parent().prepend(dzsap_playlist_item_target);
          }
        }
      });
      $(document).on('mouseup.dzsap_playlist_item', function (e) {
        if (window.dzsap_moving_playlist_item) {
          window.dzsap_moving_playlist_item = false;
          window.dzsap_playlist_item_moving.parent().children('.cloner').remove();
          window.dzsap_playlist_item_target.removeClass('target-playlist-item');
          window.dzsap_playlist_item_moving.remove();
          window.dzsap_playlist_item_moving = null;
          window.dzsap_playlist_item_target = null;
        }
      });
    } else {
      console.error('no tooltip .. why, should be here?');
    }
  }
  player_determineSyncPlayersIndex(selfClass, $targetPlayer) {
    if (this.$playlistInner) {
      const $playlistTooltipInner = this.$playlistInner.children('.dzstooltip--inner').eq(0);
      $playlistTooltipInner.children().removeClass('current-playlist-item');
      $playlistTooltipInner.children().each(function () {
        var _t = jQuery(this);
        if (_t.attr('data-playerid') === $targetPlayer.attr('data-playerid')) {
          _t.addClass('current-playlist-item');
          selfClass.playlist_inner_currNr = _t.parent().children().index(_t);
        }
      });
    }
  }
  /**
   * this is the function called from playlist menu item ( footer )
   * @param arg
   * @param pargs
   */

  playlistInner_gotoItem(arg, pargs) {
    // --
    var $ = jQuery;
    var thisClass = this;
    var selfClass = this.dzsapClass;
    var margs = {
      'call_from': "default"
    };
    if (pargs) {
      margs = $.extend(margs, pargs);
    }
    var _cach_con = null;
    if (thisClass.$playlistInner) {
      _cach_con = thisClass.$playlistInner.find('.dzstooltip--inner');
      const $cach = _cach_con.children().eq(arg);
      const playerId = $cach.attr('data-playerid');
      const $targetPlayer = $('.audioplayer[data-playerid="' + playerId + '"],.audioplayer-tobe[data-playerid="' + playerId + '"]');
      if (playerId && $targetPlayer.length && $targetPlayer.eq(0).get(0) && $targetPlayer.eq(0).get(0).api_play_media) {
        $('.audioplayer[data-playerid="' + playerId + '"]').eq(0).get(0).api_play_media({
          'called_from': 'api_sync_players_prev'
        });
      } else {
        if ($targetPlayer.parent().parent().parent().hasClass('audiogallery')) {
          $targetPlayer.parent().parent().parent().get(0).api_goto_item(arg);
        } else {
          // -- in case we change the page ;)
          const $dzsapFooter = $(`.${_constants.DZSAP_PLAYER_CLASS_FOOTER_PLAYER}`);
          if ($dzsapFooter.length && $dzsapFooter.get(0).api_change_media) {
            $dzsapFooter.get(0).api_change_media($targetPlayer);
          }
        }
      }
      selfClass.playlist_inner_currNr = arg;
    }
  }
};

},{"../../../configs/_constants":1}]},{},[2])


//# sourceMappingURL=inner-playlist.js.map