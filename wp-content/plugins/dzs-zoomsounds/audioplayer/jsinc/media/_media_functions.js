import {assignPcmData, dzsap_is_mobile, is_android, is_ios, player_view_addMetaLoaded} from '../_dzsap_helpers';
import {ConstantsDzsAp} from "../../configs/_constants";
import {setupTooltip} from "../../js_common/_dzs_helpers";
import {volume_setVolume} from "../player/_player_volume";
import {player_initSpectrumOnUserAction} from "../player/_player_scrubModeWave_spectrum";

/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param callbackFn
 * @param errorFn
 */
export const media_tryToPlay = function (selfClass, callbackFn, errorFn) {
  async function async_media_tryToPlay() {
    function tryToPlay(resolve, reject) {
      if (selfClass.cthis.attr('data-original-type')) {
        // -- then this player is feeding
      } else {

        if (selfClass.$mediaNode_) {
          if (selfClass.$mediaNode_.play) {

            // -- no audioCtx_buffer

            if (is_ios() && selfClass.spectrum_audioContext !== null && typeof selfClass.spectrum_audioContext == 'object') {
              // todo: ios not playing nice.. with audio context

              selfClass.spectrum_audioContextBufferSource = selfClass.spectrum_audioContext.createBufferSource();
              selfClass.spectrum_audioContextBufferSource.buffer = selfClass.spectrum_audioContext_buffer;
              selfClass.spectrum_audioContextBufferSource.connect(selfClass.spectrum_audioContext.destination);

              selfClass.spectrum_audioContextBufferSource.start(0, selfClass.lastTimeInSeconds);
              resolve({
                'resolve_type': 'playing_context'
              })
            } else {

              selfClass.cthis.addClass('zoomsounds-player--media--is-loading');

              try {

                selfClass.$mediaNode_.play().then(r => {
                  resolve({
                    'resolve_type': 'started_playing'
                  })
                }).catch(err => {
                  reject({
                    'error_type': 'did not want to play',
                    'error_message': err
                  });
                }).finally(() => {

                  selfClass.cthis.removeClass('zoomsounds-player--media--is-loading');
                });
              } catch (e) {
                selfClass.$mediaNode_.play();
                resolve({
                  'resolve_type': 'started_playing'
                })
              }

            }
          } else {
            if (selfClass._actualPlayer == null) {
              selfClass.isPlayPromised = true;
            }

          }
        } else {
          if (selfClass._actualPlayer == null) {
            selfClass.isPlayPromised = true;
          }
        }


      }

    }

    return new Promise((resolve, reject) => {

      tryToPlay(resolve, reject);

    })
  }

  async_media_tryToPlay().then((r) => {
    callbackFn(r);
  }).catch((err) => {
    errorFn(err);
  })

}


/**
 *
 * @param {DzsApPlaylist | null} $parentPlaylistForSource
 * @param {DzsAudioPlayer} selfClass
 * @returns {string}
 */
const getAutoplayNextMethod = ($parentPlaylistForSource, selfClass) => {

  let autoplayNextMethod = 'default';

  // -- if it's part of a playlist
  if ($parentPlaylistForSource) {
    if ($parentPlaylistForSource.initOptions.autoplayNext === 'on') {
      autoplayNextMethod = 'nextInSourcePlayist';
    }
  }

  if (autoplayNextMethod === 'default') {
    const tryGetAutoPlayMethod = () => {
      if (window.dzsap_syncList_players && window.dzsap_syncList_players.length > 0) {
        if (selfClass.cthis.hasClass('is-single-player') || (selfClass._sourcePlayer && selfClass._sourcePlayer.hasClass('is-single-player'))) {
          // -- called on handle end
          return  'syncPlayerNext';
        }
      }
      if (selfClass._sourcePlayer && (selfClass._sourcePlayer.hasClass('is-single-player') || selfClass._sourcePlayer.hasClass('feeded-whole-playlist'))) {
        return 'handleEndForSourcePlayer';
      }

      return 'nothingAfter';
    }
    autoplayNextMethod = tryGetAutoPlayMethod();
  }

  return autoplayNextMethod;
}



/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param {object} pargs
 * @param {boolean} media_isLoopActive
 * @param {function} seek_to
 * @param {function} play_media
 * @param {function} pause_media
 * @param {object} o
 * @param {function} syncPlayers_gotoNext
 * @param {function} action_audio_end
 * @returns {void}
 */
export const media_handleEventEnd = (selfClass, pargs, media_isLoopActive, seek_to, play_media, pause_media, o, syncPlayers_gotoNext, action_audio_end) => {


  var margs = {
    'called_from': 'default'
  }


  if (pargs) {
    margs = Object.assign(margs, pargs);
  }
  if (selfClass.isMediaEnded) {
    return;
  }

  selfClass.isMediaEnded = true;

  selfClass.inter_isEnded = setTimeout(function () {
    selfClass.isMediaEnded = false;
  }, 1000);


  if (selfClass._sourcePlayer) {

    media_isLoopActive = selfClass._sourcePlayer.get(0).api_get_media_isLoopActive();
  }
  if (selfClass._actualPlayer && margs.call_from !== 'fake_player') {
    // -- lets leave fake player handle handle_end
    return;
  }


  seek_to(0, {
    'call_from': 'handle_end'
  });

  if (media_isLoopActive) {
    play_media({
      'called_from': 'track_end'
    });
    return;
  } else {
    pause_media();
  }

  if (o.parentgallery) {
    o.parentgallery.get(0).api_gallery_handle_end();
  }



  let $parentPlaylistForSource = null;
  if (selfClass._sourcePlayer && selfClass._sourcePlayer.get(0) && selfClass._sourcePlayer.get(0).SelfInstance && selfClass._sourcePlayer.get(0).SelfInstance.$parentPlaylist) {
    // -- called on handle end
    $parentPlaylistForSource = selfClass._sourcePlayer.get(0).SelfInstance.$parentPlaylist.get(0).SelfPlaylist;

  }

  const autoplayNextMethod = getAutoplayNextMethod($parentPlaylistForSource, selfClass);



  // -- decided autoplayNextMethod

  if (autoplayNextMethod === 'nextInSourcePlayist') {

    if ($parentPlaylistForSource && $parentPlaylistForSource.argThis) {
      $parentPlaylistForSource.argThis.api_gallery_handle_end();
    }
  }
  if (autoplayNextMethod === 'syncPlayerNext') {
    setTimeout(function () {
      syncPlayers_gotoNext(selfClass);
    }, 100);
  }

  if (autoplayNextMethod === 'handleEndForSourcePlayer') {
    setTimeout(function () {
      selfClass._sourcePlayer.get(0).api_handle_end({
        'call_from': 'handle_end() fake_player'
      });
    }, 200);
  }

  if (action_audio_end) {
    setTimeout(function () {
      let args = {};
      action_audio_end(selfClass.cthis, args);
    }, 200);
  }
}

export const media_removeMediaInside = (selfClass) => {

  selfClass.$theMedia.children().remove();
  selfClass.$mediaNode_ = null;
}

export const setupMediaElement = (selfClass, stringAudioElementHtml = '', stringAudioTagSource = '') => {


  media_removeMediaInside(selfClass);

  if (stringAudioTagSource) {
    if (selfClass.$mediaNode_) {

      jQuery(selfClass.$mediaNode_).append(stringAudioTagSource);
      if (selfClass.$mediaNode_.load) {
        selfClass.$mediaNode_.load();
      }

    } else {
      setupMediaElement(selfClass, stringAudioElementHtml);
      return false;
    }
  } else {
    selfClass.$theMedia.append(stringAudioElementHtml);
  }
  selfClass.$mediaNode_ = (selfClass.$theMedia.children('audio').get(0));


}


/**
 *
 * @param {DzsAudioPlayer} selfClass
 */
export function destroy_cmedia(selfClass) {
  // -- destroy cmedia
  const $ = selfClass.$;

  $(selfClass.$mediaNode_).remove();
  selfClass.$mediaNode_ = null;
  selfClass.isSetupedMedia = false;
  selfClass.isPlayerLoaded = false;
}

export function destroy_media(selfClass, pause_media) {
  const o = selfClass.initOptions;
  pause_media();


  if (selfClass.$mediaNode_) {

    if (selfClass.$mediaNode_.children) {

    }

    if (o.type === 'audio') {
      selfClass.$mediaNode_.innerHTML = '';
      selfClass.$mediaNode_.load();
    }
  }

  if (selfClass.$theMedia) {

    selfClass.$theMedia.children().remove();
    selfClass.$mediaNode_ = null;
    selfClass.isPlayerLoaded = false;
  }

  destroy_cmedia(selfClass);

}

export const setupMediaListeners = function (selfClass, setupMediaAttrs, action_initLoaded, volume_lastVolume, volume_setVolume) {

  let attempt_reload = 0;


  if (typeof (selfClass.$mediaNode_) !== "undefined" && selfClass.$mediaNode_) {
    selfClass.$mediaNode_.addEventListener('error', handleAudioError, true);
    selfClass.$mediaNode_.addEventListener('loadedmetadata', handleMediaMetaLoaded, true);
    if (selfClass.$mediaNode_.nodeName === 'AUDIO') {
      selfClass.$mediaNode_.addEventListener('ended', media_handleEnd);
    }
  }


  function media_handleEnd(pargs) {
    selfClass.handle_end(pargs);
  }

  function handleAudioError(e) {

    const $audioElement_ = this;

    var noSourcesLoaded = ($audioElement_.networkState === HTMLMediaElement.NETWORK_NO_SOURCE);
    if (noSourcesLoaded && dzsap_is_mobile() === false) {
      if (selfClass.cthis.hasClass(ConstantsDzsAp.ERRORED_OUT_CLASS) === false) {

        if (attempt_reload < ConstantsDzsAp.ERRORED_OUT_MAX_ATTEMPTS) {
          setTimeout(function (earg) {
            if (selfClass.$mediaNode_) {
              selfClass.$mediaNode_.src = '';
            }


            setTimeout(function () {
              if (selfClass.$mediaNode_) {
                selfClass.$mediaNode_.src = selfClass.data_source;
                selfClass.$mediaNode_.load();
              }
            }, 1000)
          }, 1000, e)
          attempt_reload++;
        } else {

          // -- IT FAILED LOADING

          if (selfClass.initOptions.notice_no_media === 'on') {
            selfClass.cthis.addClass(ConstantsDzsAp.ERRORED_OUT_CLASS);
            var txt = 'error - file does not exist...';
            if (e.target.error) {
              txt = e.target.error.message;
            }
            selfClass.cthis.append(setupTooltip({
              tooltipConClass: ' feedback-tooltip-con',
              tooltipIndicatorText: '<span class="player-but"><span class="the-icon-bg" style="background-color: #912c2c"></span><span class="svg-icon dzsap-color-ui-inverse" >&#x2139;</span></span>',
              tooltipInnerHTML: 'cannot load - ( ' + selfClass.data_source + ' ) - error: ' + txt,
            }));
          }
        }
      }
    }
  }


  function handleMediaMetaLoaded(e) {


    player_view_addMetaLoaded(selfClass);

    /** @type {HTMLAudioElement} */
    const $audio_ = e.currentTarget;

    if (isValidTotalTime($audio_.duration)) {
      selfClass.timeModel.actualTotalTime = Math.ceil($audio_.duration);
    }
    selfClass.service_checkIfWeShouldUpdateTotalTime();


    if (setupMediaAttrs.called_from === 'change_media') {

      action_initLoaded({
        'call_from': 'force_reload_change_media'
      })
    }

    if (setupMediaAttrs.called_from === 'change_media' || selfClass._sourcePlayer) {
      if (volume_lastVolume) {
        setTimeout(() => {
          volume_setVolume(selfClass, volume_lastVolume, {
            call_from: "change_media"
          });
        }, 50);
      }
    }

    if (selfClass._sourcePlayer) {
      if (isValidTotalTime($audio_.duration)) {
        selfClass._sourcePlayer.get(0).api_set_timeVisualTotal($audio_.duration)

      }
    }


    selfClass.view_drawCurrentTime();
  }

}


export const buildAudioElementHtml = function (selfClass, type_normal_stream_type, calledFrom) {

  var stringAudioTagOpen = '';
  var stringAudioTagSource = '';
  var stringAudioTagClose = '';
  var o = selfClass.initOptions;


  if (selfClass.data_source) {
    if (selfClass.data_source.indexOf('get_track_source') > -1) {
      o.preload_method = 'none';
    }
  }

  stringAudioTagOpen += '<audio';
  stringAudioTagOpen += ' id="' + selfClass.uniqueId + '-audio"';
  stringAudioTagOpen += ' preload="' + o.preload_method + '"';
  if (o.skinwave_enableSpectrum === 'on') {
    stringAudioTagOpen += ' crossOrigin="anonymous"';

  }

  if (is_ios()) {
    if (calledFrom === 'change_media') {
      stringAudioTagOpen += ' autoplay';
    }
  }

  stringAudioTagOpen += '>';
  stringAudioTagSource = '';


  if (selfClass.data_source) {

    if (!selfClass.data_source && type_normal_stream_type !== 'icecast') {
      selfClass.data_source = selfClass.cthis.attr('data-source');
    }


    if (selfClass.data_source !== 'fake') {
      stringAudioTagSource += '<source src="' + selfClass.data_source + '" type="audio/mpeg">';
      if (selfClass.cthis.attr('data-sourceogg') !== undefined) {
        stringAudioTagSource += '<source src="' + selfClass.cthis.attr('data-sourceogg') + '" type="audio/ogg">';
      }
    } else {
      selfClass.cthis.addClass('meta-loaded meta-fake');
    }
  }
  stringAudioTagClose += '</audio>';


  return {
    stringAudioElementHtml: stringAudioTagOpen + stringAudioTagSource + stringAudioTagClose,
    stringAudioTagSource
  };

}

export const makeMediaPreloadInTheFuture = function (selfClass, stringAudioElementHtml) {

  setTimeout(function () {
    if (selfClass.$mediaNode_) {
      jQuery(selfClass.$mediaNode_).attr('preload', 'metadata');
    }
  }, (Number(window.dzsap_player_index) * 10000));
}
export const repairMediaElement = function (selfClass, stringAudioElementHtml) {

  var o = selfClass.initOptions;
  setTimeout(function () {

    if (selfClass.$theMedia.children().eq(0).get(0) && selfClass.$theMedia.children().eq(0).get(0).nodeName === "AUDIO") {

      return false;
    }
    selfClass.$theMedia.html('');
    selfClass.$mediaNode_ = null;
    selfClass.$theMedia.append(stringAudioElementHtml);

    var isWasPlaying = selfClass.isPlayerPlaying;

    selfClass.pause_media();
    selfClass.$mediaNode_ = (selfClass.$theMedia.children('audio').get(0));


    if (isWasPlaying) {
      setTimeout(function () {

        selfClass.play_media({
          'called_from': 'aux_was_playing'
        });
      }, 20);
    }
  }, o.failsafe_repair_media_element);

  o.failsafe_repair_media_element = '';
}

/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param callbackFn
 */
export const media_pause = function (selfClass, callbackFn) {

  var $ = selfClass.$;


  if (selfClass.audioType === 'youtube') {


    if (selfClass.$mediaNode_ && selfClass.$mediaNode_.pauseVideo) {
      selfClass.$mediaNode_.pauseVideo();
    }
  }
  if (selfClass.audioType === 'selfHosted') {

    if (0) {
    } else {
      if (selfClass.$mediaNode_) {

        if (selfClass.initOptions.pause_method == 'stop') {

          selfClass.$mediaNode_.pause();
          selfClass.$mediaNode_.src = '';


          selfClass.destroy_cmedia();
          $(selfClass.$mediaNode_).remove();
          selfClass.$mediaNode_ = null;
        } else {

          if (selfClass.$mediaNode_.pause) {
            selfClass.$mediaNode_.pause();
          }
        }
      }

    }


  }

  callbackFn();

}

export const isValidTotalTime = (duration) => {
  return Boolean(duration && duration !== Infinity);
}


const mediaSetupElement = (setupMediaAttrs, selfClass, stringAudioElement, stringAudioElementHtml) => {

  const stringAudioTagSource = stringAudioElement.stringAudioTagSource;
  if (setupMediaAttrs.called_from === 'change_media' || setupMediaAttrs.called_from === 'nonce generated') {

    if (is_ios() || is_android()) {

      // -- we append only the source to mobile devices as we need the thing to autoplay without user action
      setupMediaElement(selfClass, stringAudioElementHtml, stringAudioTagSource);

    } else {
      // -- normal desktop
      if (!(setupMediaAttrs.called_from === 'nonce generated' && selfClass._actualPlayer)) {

        setupMediaElement(selfClass, stringAudioElementHtml);
      }
    }
    // -- end change media
  } else {
    setupMediaElement(selfClass, stringAudioElementHtml);

  }
}

/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param {object} pargs
 * @param init_loaded
 * @param pause_media
 * @param init_checkIfReady
 * @param handleClick_playPause
 * @returns {void}
 */
export const mediaSetup = (selfClass, pargs, init_loaded, pause_media, init_checkIfReady, handleClick_playPause) => {
  var stringAudioElementHtml = '';

  var setupMediaAttrs = {

    'do_not_autoplay': false,
    'called_from': 'default',
    'newSource': '',
  };

  var $ = jQuery;
  var o = selfClass.initOptions;
  var cthis = selfClass.cthis;

  if (pargs) {
    setupMediaAttrs = $.extend(setupMediaAttrs, pargs);
  }

  if (selfClass.cthis && selfClass.cthis.attr('data-pcm')) {
    assignPcmData(selfClass);
  }

  // -- these types should not exist
  if (selfClass.audioType === 'icecast' || selfClass.audioType === 'shoutcast') {
    selfClass.audioType = 'selfHosted';
  }

  if (o.cueMedia === 'off') {
    if (selfClass.ajax_view_submitted === 'auto') {
      // -- why is view submitted ?
      selfClass.increment_views = 1;
      selfClass.ajax_view_submitted = 'off';
    }
  }

  if (selfClass.isPlayerLoaded) {
    return;
  }
  if (cthis.attr('data-original-type') === 'youtube') {
    return;
  }


  if (selfClass.audioType === 'youtube') {
    dzsap_youtube_setupMedia(selfClass, setupMediaAttrs);
  }
  // -- END youtube


  if (setupMediaAttrs.newSource) {
    selfClass.data_source = setupMediaAttrs.newSource;
  }

  if (is_ios()) {
    o.preload_method = 'metadata';
  }

  const stringAudioElement = buildAudioElementHtml(selfClass, selfClass.audioTypeSelfHosted_streamType, 'setup_media');
  stringAudioElementHtml = stringAudioElement.stringAudioElementHtml;

  if (selfClass.audioType === 'selfHosted' || selfClass.audioType === 'soundcloud') {

    mediaSetupElement(setupMediaAttrs, selfClass, stringAudioElement, stringAudioElementHtml);

    if (selfClass.$mediaNode_ && selfClass.$mediaNode_.addEventListener && selfClass.cthis.attr('data-source') !== 'fake') {
      setupMediaListeners(selfClass, setupMediaAttrs, init_loaded, selfClass.volume_lastVolume, volume_setVolume)
    }

  }

  selfClass.cthis.addClass('media-setuped');


  if (setupMediaAttrs.called_from === 'change_media') {
    return;
  }

  if (selfClass.audioType !== 'youtube') {
    if (selfClass.cthis.attr('data-source') === 'fake') {
      if (is_ios() || is_android()) {
        init_loaded(setupMediaAttrs);
      }
    } else {
      if (is_ios()) {

        setTimeout(function () {
          init_loaded(setupMediaAttrs);
        }, 1000);


      } else {

        // -- check_ready() will fire init_loaded()
        selfClass.inter_checkReady = setInterval(function () {
          init_checkIfReady(setupMediaAttrs);
        }, 50);
      }

    }


    if (o.preload_method === 'none') {
      makeMediaPreloadInTheFuture(selfClass);
    }


    if (o.design_skin === 'skin-customcontrols' || o.design_skin === 'skin-customhtml') {
      cthis.find('.custom-play-btn,.custom-pause-btn').off('click');
      cthis.find('.custom-play-btn,.custom-pause-btn').on('click', handleClick_playPause);
    }

    if (o.failsafe_repair_media_element) {
      repairMediaElement(selfClass, stringAudioElementHtml);

    }
  }

  if (o.skinwave_enableSpectrum === 'on') {
    player_initSpectrumOnUserAction(selfClass);
  }

  selfClass.isSetupedMedia = true;
}

