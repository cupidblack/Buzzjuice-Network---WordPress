import {
  assignPcmData,
  player_reinit_findIfPcmNeedsGenerating,
  sanitizeObjectForChangeMediaArgs
} from "../../../jsinc/_dzsap_helpers";
import {ConstantsDzsAp} from "../../../configs/_constants";
import {
  scrubModeWave__checkIfWeShouldTryToGetPcm,
  scrubModeWave__view_transitionIn
} from "../../../jsinc/wave-render/_wave-render-functions";
import {player_adjustIdentifiers} from "../../../jsinc/player/_player_config";
import {retrieve_soundcloud_url} from "../../../jsinc/_dzsap_misc";

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
      , called_from: 'default'
      , source: 'default'
      , pcm: ''
      , artist: ""
      , song_name: ""
      , thumb: ""
      , thumb_link: ""
      , autoplay: "on"
      , cue: "on"
      , feeder_type: "player"
      , source_player_do_not_update: "off"
      , playerid: ""
    };


    selfClass.ajax_view_submitted = 'on'; // -- view submitted from caller


    var handle_resize_delay = 500;
    let changeMediaArgs = {...changeMediaArgsDefaults, ...pargs};
    var o = selfClass.initOptions;


    const $sourceForChange = $playerSource;


    // -- let us decide if we pause old player
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
      changeMediaArgs = {...changeMediaArgs, ...sanitizeObjectForChangeMediaArgs($sourceForChange)};
    }
    if (changeMediaArgs.source && changeMediaArgs.source != 'default') {
      newSource = changeMediaArgs.source;
    }


    if (selfClass.data_source === newSource) {
      isGoingToPauseTheOldPlayer = false;
    }

    // -- old feed fake player

    if (isGoingToPauseTheOldPlayer && selfClass._sourcePlayer) {
      selfClass._sourcePlayer.get(0).api_pause_media_visual({
        'call_from': 'change_media'
      });
      selfClass._sourcePlayer.get(0).api_set_timeVisualTotal(0);
    }

    // -- we are in one mode, so we need to preserve the originalSettings of the first item
    if (!(selfClass.cthis.data('original-settings')) && selfClass.data_source !== 'fake') {
      selfClass.cthis.data('original-settings', sanitizeObjectForChangeMediaArgs(selfClass.cthis))
    }


    const oldSource = selfClass.data_source;
    selfClass.data_source = newSource;

    const isSourceHasClassForDomSource = !isSourceAStringSource && !!($sourceForChange.hasClass('audioplayer') || $sourceForChange.hasClass('is-zoomsounds-source-player'));


    if ((isSourceHasClassForDomSource) || changeMediaArgs.fakeplayer_is_feeder === 'on') {

      selfClass.set_sourcePlayer($sourceForChange)
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


    selfClass.cthis.removeClass('meta-loaded');


    // -- footer placeholder
    if (selfClass.cthis.parent().hasClass('audioplayer-was-loaded')) {

      selfClass.cthis.parent().addClass('audioplayer-loaded');
      $('body').addClass('footer-audioplayer-loaded');
      selfClass.cthis.parent().removeClass('audioplayer-was-loaded');
    }

    if (selfClass.$stickToBottomContainer) {
      selfClass.$stickToBottomContainer.addClass('audioplayer-loaded');
    }


    selfClass.cthis.removeClass(ConstantsDzsAp.ERRORED_OUT_CLASS);


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
            assignPcmData(selfClass);
            scrubModeWave__view_transitionIn(selfClass, $sourceForChange.attr('data-pcm'));
          }
        } else {

          player_reinit_findIfPcmNeedsGenerating(selfClass);

          scrubModeWave__checkIfWeShouldTryToGetPcm(selfClass, {});
        }

      }


      // -- inside skin-wave
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
        selfClass.setupStructure_thumbnailCon()
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


    player_adjustIdentifiers(selfClass);


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
      retrieve_soundcloud_url(selfClass);

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
    }, handle_resize_delay)
  }
}


/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param {jQuery} $
 */
window.dzsap_part_mediaChange_init = function(selfClass,$){
  selfClass.cthis.get(0).api_change_media = media_changeMedia(selfClass, $); // -- change the media file from the API
}

window.dzsap_part_mediaChange_loaded = true;
window.media_changeMedia = media_changeMedia;
