/*
 * Author: Audio Player with Playlist
 * Website: https://digitalzoomstudio.net/
 * Portfolio: https://bit.ly/nM4R6u
 * Version: 6.41
 * */
"use strict";

import {
  assignHelperFunctionsToJquery,
  convertPluginOptionsToFinalOptions,
  dzsap_is_mobile,
  dzsap_singleton_ready_calls,
  formatTime,
  is_android,
  is_ios,
  is_safari,
  jQueryAuxBindings,
  player_delete,
  player_determineStickToBottomContainer,
  player_getPlayFromTime,
  player_identifySource,
  player_identifyTypes,
  player_isGoingToSetupMediaNow,
  player_service_getSourceProtection,
  player_stickToBottomContainerDetermineClasses,
  player_stopOtherPlayers,
  player_syncPlayers_buildList,
  player_view_addMetaLoaded,
  playerFunctions,
  playerRegisterWindowFunctions,
  sanitizeToIntFromPointTime,
  view_player_globalDetermineSyncPlayersIndex,
  view_player_playMiscEffects,
  waitForScriptToBeAvailableThenExecute
} from './jsinc/_dzsap_helpers';
import {dzsap_jQueryInit, getBaseUrl, isInt, loadScriptIfItDoesNotExist} from './js_common/_dzs_helpers';
import {
  destroy_cmedia,
  destroy_media,
  media_handleEventEnd,
  media_pause,
  media_tryToPlay,
  mediaSetup,
} from "./jsinc/media/_media_functions";
import {PlayerTime} from './jsinc/_dzsap_time_model';
import {ajax_submit_download, ajax_submit_total_time, ajax_submit_views} from "./jsinc/_dzsap_ajax";

import {
  DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS,
  DZSAP_PLAYER_CLASS_LOADED, DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED,
  DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT
} from "./configs/_constants";
import {_ClassMetaParts} from "./jsinc/helper-classes/_ClassMetaParts";
import {pausebtn_svg, playbtn_svg, svg_share_icon} from "./jsinc/_dzsap_svgs";
import {retrieve_soundcloud_url} from "./jsinc/_dzsap_misc";
import {setup_structure} from "./jsinc/components/_structure";
import {
  scrubModeWave__checkIfWeShouldTryToGetPcm,
  view_drawCanvases,
} from "./jsinc/wave-render/_wave-render-functions";
import {defaultPlayerOptions} from "./configs/_settingsPlayer";
import {dzsap_oneTimeSetups} from "./jsinc/player/_player-one-time-setups";
import {dzsap_generate_keyboard_controls} from "./jsinc/player/_player_keyboard";
import {checkIfNeedsSongNameRefresh} from "./jsinc/player/features/_zoomsounds-radio-features";
import {player_initSpectrum, view_spectrum_drawMeter} from "./jsinc/player/_player_scrubModeWave_spectrum";
import {player_commentsSelectorInit} from "./jsinc/player/_player_commentsSelector";
import {player_setupMiscButtons} from "./jsinc/player/_player_setupMiscButtons";
import {dzsap_player_touchFunctionalitySetup} from "./jsinc/player/_player_touchFunctionality";
import {player_menuStateSetup} from "./jsinc/player/_player_menuState";
import {
  player_nextPrevButtonsSetup,
  syncPlayers_gotoNext,
  syncPlayers_gotoPrev
} from "./jsinc/player/_player_nextPrevButtons";
import {
  getDefaultVolume,
  player_volumeSetup,
  volume_setOnlyVisual,
  volume_setVolume
} from "./jsinc/player/_player_volume";
import {
  player_actualPlayerPause,
  player_actualPlayerPlay,
  player_feederSeekTo,
  player_sourcePlayerPauseVisual,
  player_sourcePlayerPlayVisual
} from "./jsinc/player/_player_feederFunctions";
import {player_scrubbarSetup} from "./jsinc/player/_player_scrubbar";
import {
  scrubbar_modeWave_setupCanvas,
  view_player_scrubModeWaveAdjustCurrTimeAndTotalTime
} from "./jsinc/player/_player_scrubModeWave";
import {view_resizeEmbedded, view_resizeHandleTreatSkins} from "./jsinc/player/_player_viewSetup";
import {
  player_setupStructure_thumbnailCon,
} from "./jsinc/view/player/_view_playerStructure";
import {
  configureAudioPlayerOptionsInitial, player_adjustIdentifiers,
  player_detect_skinwave_mode, player_determineActualPlayer, player_determineHtmlAreas,
  player_viewApplySkinWaveModes
} from "./jsinc/player/_player_config";

/** list for autoplay -- this is for the players */
window.dzsap_list = [];

let dzsap_globalidind = 1;

window.dzsap_inited = true;
window.loading_multi_sharer = false;

window.dzsap_player_interrupted_by_dzsap = null;
window.dzsap_audio_ctx = null;
window.dzsap__style = null;
window.dzsap_sticktobottom_con = null;

window.dzsap_self_options = {};

window.dzsap_generating_pcm = false;
window.dzsap_box_main_con = null;
window.dzsap_lasto = null;

/** list for sync -- used for next .. prev .. footer playlist */
window.dzsap_syncList_players = [];
window.dzsap_syncList_index = 0; // -- used for next .. prev .. footer playlist
window.dzsap_base_url = '';

window.dzsap_player_index = 0; // -- the player index on the page


/** announcers can subscribe to this action */
if (!window.dzsap_action_play_listeners) {
  window.dzsap_action_play_listeners = [];
}

/**
 * @property {boolean} isAlreadyHasRealPcm
 * @property {boolean} isPcmRequiredToGenerate
 * @property {boolean} isMultiSharer
 * @property {string} identifier_pcm
 * @property {string} urlToAjaxHandler
 * @property {array<number>} pcmSource
 * @property {array<number>} last_lastArray
 * @property {object} last_lastArray
 * @property {AnalyserNode} spectrum_analyser
 * @property {HTMLElement} _sourcePlayer
 * @property {HTMLElement} $mediaNode_
 * @constructor
 * @public
 */
class DzsAudioPlayer {
  constructor(argThis, argOptions, $) {

    this.argThis = argThis;
    this.argOptions = Object.assign({}, argOptions);
    this.$ = $;
    this.cthis = null;

    this.ajax_view_submitted = 'auto';
    this.increment_views = 0;
    this.the_player_id = '';
    this.currIp = '127.0.0.1';
    this.index_extrahtml_toloads = 0;
    this.starrating_alreadyrated = -1;
    this.data_source = '';
    this.pcmSource = null;

    this.urlToAjaxHandler = null;


    this.playFrom = '';


    this._actualPlayer = null;
    this._audioplayerInner = null;
    this._metaArtistCon = null;
    this._conControls = null;
    this._conPlayPauseCon = null;
    this._apControls = null;
    this._apControlsLeft = null;
    this._apControlsRight = null;
    this._commentsHolder = null;
    this.$mediaNode_ = null;
    this._scrubbar = null;
    this._scrubbarbg_canvas = null;
    this._scrubBgCanvasCtx = null;
    this._scrubbarprog_canvas = null;
    this.$feed_fakeButton = null;
    this._sourcePlayer = null;
    this.$theMedia = null;
    this.$conPlayPause = null; // -- [selector] .con-playpause
    this.$conControls = null;
    this.$$scrubbProg = null;
    this.$controlsVolume = null;
    this.$currTime = null;
    this.$parentPlaylist = null;
    this.$totalTime = null;
    this.$commentsWritter = null;
    this.$commentsChildren = null;
    this.$commentsSelector = null;
    this.$embedButton = null;
    /** a reflection object for triggering the player from outside */
    this.$reflectionVisualObject = null;


    this.audioType = 'normal';
    this.audioTypeSelfHosted_streamType = '';
    this.skinwave_mode = 'normal';
    this.action_audio_comment = null; // -- set a outer ended function ( for example for tracking your analytics

    this.commentPositionPerc = 0;// -- the % at which the comment will be placed

    this.spectrum_audioContext = null;
    this.spectrum_audioContextBufferSource = null;
    this.spectrum_audioContext_buffer = null;
    this.spectrum_mediaElementSource = null;
    this.spectrum_analyser = null;
    this.spectrum_gainNode = null;

    this.isMultiSharer = false;
    this.hasInitialPcmData = false;

    this.lastArray = null;
    this.last_lastArray = null;

    this.isPlayerPlaying = false;
    /** leaves a easing before ending play */
    this.isPlayerPlayingEased = false;
    this.isPlayerPlayingEasedInterval = null;

    this.actualDataTypeOfMedia = 'audio'; // "audio" or

    this.youtube_retryPlayTimeout = 0;
    this.lastTimeInSeconds = 0;
    this.inter_checkReady = 0;
    this.cthisWidth = 0;

    // -- sticky player

    this.isStickyPlayer = false;
    this.$stickToBottomContainer = null;


    // -- pcm
    this.uniqueId = '';
    this.identifier_pcm = ''; // -- can be either player id or source if player id is not set
    this.isAlreadyHasRealPcm = false;
    this.isPcmTryingToGenerate = false;
    this.isPlayPromised = false // -- we are promising generating on meta load
    this.isCanvasFirstDrawn = false // -- the first draw on canvas
    this.isPlayerLoaded = false;

    this.original_real_mp3 = '' // -- this is the original real mp3 for sainvg and identifying in the database

    // -- theme specific
    this.skin_minimal_canvasplay = null;

    this.classFunctionalityInnerPlaylist = null;
    this.feedEmbedCode = '';

    this.youtube_currentId = 0;
    this.youtube_isInited = false;

    this.extraHtmlAreas = {
      bottom: '',
      afterArtist: '',
      controlsLeft: '',
      controlsRight: '',
    }

    // -- time vars
    this.sample_time_start = 0;
    this.sample_time_end = 0;
    this.sample_time_total = 0;

    this.playlist_inner_currNr = 0
    this.canvasWidth = null;
    this.heightCanvas = null;

    this.timeCurrent = 0; // -- *deprecated
    this.timeModel = new PlayerTime(this);

    this.isSample = false;
    this.isSafeToChangeTrack = false // -- only after 2 seconds of init is it safe to change track
    this.isMediaEnded = false;
    /** is first setuped media */
    this.isSetupedMedia = false;
    this.isSentCacheTotalTime = false;
    this.isPcmRequiredToGenerate = false;
    this.radio_isGoingToUpdateSongName = false;
    this.radio_isGoingToUpdateArtistName = false;
    this.mediaProtectionStatus = 'unprotected';

    this.classMetaParts = new _ClassMetaParts(this);

    this.inter_isEnded = 0;


    argThis.SelfInstance = this;
    this.classInit();
  }

  set_sourcePlayer($arg) {
    if ($arg) {
      if ($arg.get(0) != this.cthis.get(0)) {
        this._sourcePlayer = $arg;
      }
    } else {
      this._sourcePlayer = $arg;
    }
  }


  reinit_beforeChangeMedia() {
    this.hasInitialPcmData = false;
    this.isPcmRequiredToGenerate = false;
    this.isAlreadyHasRealPcm = false;
    this.cthis.attr('data-pcm', '');
  }

  reinit_resetMetrics() {
    this.isPlayerLoaded = false;
  }


  service_checkIfWeShouldUpdateTotalTime() {
    ajax_submit_total_time(this);
  }

  classInit() {

    const $ = this.$;
    const o = this.argOptions;
    let cthis = $(this.argThis);

    const selfClass = this;


    selfClass.cthis = cthis;
    selfClass.initOptions = o;

    selfClass.volume_lastVolume = 1;
    selfClass.isScrubShowingCurrentTime = false;
    selfClass.scrubbarWidth = 0;
    selfClass.scrubbarProgX = 0;
    selfClass.currTime_outerWidth = 0;

    var cthisId = 'ap1'
    ;
    var ww, th // -- controls width
    ;
    var
      isDestroyed = false,
      isDestroyedForRebuffer = false
      , media_isLoopActive = false // -- if loop_active the track will loop
      , curr_time_first_set = false
      , isListenersSetup = false
    ;
    var last_time_total = 0
      , player_index_in_gallery = -1 // -- the player index in gallery
    ;


    var inter_60_secs_contor = 0
      , inter_trigger_resize;
    var data_station_main_url = ''
    ;

    var is_under_400 = false


    ; // resize thumb height


    var skin_minimal_button_size = 0;


    var action_audio_end = null
      , action_audio_play = null
      , action_audio_play2 = null
      , action_audio_pause = null


    let isRenderingFrame = false,
      isRenderingFrameInterval = null,
      spectrum_fakeItMode = 'auto'
    ;


    var draw_canvas_inter = 0;


    window.dzsap_player_index++;


    selfClass.timeModel.getSampleTimesFromDom();


    init();

    function init() {


      if (cthis.hasClass('dzsap-inited')) {
        return false;
      }

      selfClass.play_media_visual = play_media_visual;
      selfClass.play_media = play_media;
      selfClass.pause_media = pause_media;
      selfClass.pause_media_visual = pause_media_visual;
      selfClass.seek_to = seek_to;
      selfClass.reinit = reinit;

      selfClass.handle_end = media_handleEnd;
      selfClass.init_loaded = init_loaded;
      selfClass.scrubbar_reveal = scrubbar_reveal;
      selfClass.calculate_dims_time = calculate_dims_time;
      selfClass.check_multisharer = check_multisharer;
      selfClass.setup_structure_scrub = setup_structure_scrub;
      selfClass.setup_structure_sanitizers = setup_structure_sanitizers;
      selfClass.destroy_cmedia = () => {
        destroy_cmedia(selfClass);
      };
      selfClass.view_drawCurrentTime = view_drawCurrentTime;
      selfClass.setupStructure_thumbnailCon = setupStructure_thumbnailCon;
      selfClass.setup_pcm_random_for_now = view_wave_setupRandomPcm;
      selfClass.handleResize = view_handleResize;
      selfClass.destroy_media = () => {
        destroy_media(selfClass, pause_media);
      };

      cthis.css({'opacity': ''});
      cthis.addClass('dzsap-inited');
      window.dzsap_player_index++;


      if (o.parentgallery) {
        selfClass.$parentPlaylist = o.parentgallery;
      }
      selfClass.keyboard_controls = dzsap_generate_keyboard_controls();

      configureAudioPlayerOptionsInitial(cthis, o, selfClass);

      if (o.loop === 'on') {
        media_isLoopActive = true;
      }


      (player_detect_skinwave_mode.bind(selfClass))()


      if (dzsap_is_mobile()) {
        $('body').addClass('is-mobile');
        if (o.mobile_delete === 'on') {
          player_delete(selfClass);
        }
        // -- disable fakeplayer on mobile for some reason
        if (o.mobile_disable_fakeplayer === 'on') {
          selfClass.cthis.attr('data-fakeplayer', '');
        }
      }


      player_viewApplySkinWaveModes(selfClass);


      playerFunctions(selfClass, 'detectIds');


      if (cthis.attr('data-fakeplayer')) {
        player_determineActualPlayer(selfClass);
      }

      selfClass.cthis.addClass('scrubbar-type-' + o.scrubbar_type);


      player_determineHtmlAreas(selfClass);


      // -- syncPlayers build
      if (window.dzsap_settings.syncPlayers_buildList === 'on') {
        player_syncPlayers_buildList()
      }


      player_getPlayFromTime(selfClass);


      player_adjustIdentifiers(selfClass);
      player_identifySource(selfClass);
      player_identifyTypes(selfClass);


      if (selfClass.audioType === 'youtube') {
        window.dzsap_get_base_url();
        const scriptUrl = window.dzsap_base_url ? window.dzsap_base_url + '/parts/youtube/dzsap-youtube-functions.js' : '';
        loadScriptIfItDoesNotExist(scriptUrl, window.dzsap_youtube_functions_inited).then((resolveStr) => {
          window.dzsap_youtube_functions_init(selfClass);
        });
      }


      selfClass.audioTypeSelfHosted_streamType = '';


      if (selfClass.audioType === 'selfHosted') {
        if (cthis.attr('data-streamtype') && cthis.attr('data-streamtype') !== 'off') {
          selfClass.audioTypeSelfHosted_streamType = cthis.attr('data-streamtype');
          data_station_main_url = selfClass.data_source;
          cthis.addClass('is-radio-type');
        } else {
          selfClass.audioTypeSelfHosted_streamType = '';
        }
      }

      // -- no shoutcast autoupdate at the moment 2 3 4 5 6 7 8
      if (selfClass.audioTypeSelfHosted_streamType === 'shoutcast') {

        // -- todo: we
      }


      // -- we disable the function if audioplayer inited
      if (cthis.hasClass('audioplayer')) {
        return;
      }

      if (cthis.attr('id') !== undefined) {
        cthisId = cthis.attr('id');
      } else {
        cthisId = 'ap' + dzsap_globalidind++;
      }


      selfClass.youtube_currentId = 'ytplayer_' + cthisId;


      cthis.removeClass('audioplayer-tobe');
      cthis.addClass('audioplayer');

      view_drawScrubProgress();
      setTimeout(function () {
        view_drawScrubProgress()
      }, 1000);


      // -- ios does not support volume controls so just let it die
      // -- .. or autoplay FORCE STAFF


      if (o.cueMedia === 'off') {

        // -- cue is forcing autoplay on
        cthis.addClass('cue-off');
        o.autoplay = 'on';
      }


      //====sound cloud INTEGRATION //
      if (selfClass.audioType === 'soundcloud') {
        retrieve_soundcloud_url(selfClass);
      }
      // -- END soundcloud INTEGRATION//


      setup_structure(selfClass, {}); //  -- inside init()

      // --   trying to access the youtube api with ios did not work


      if (o.scrubbar_type === 'wave' && (selfClass.audioType === 'selfHosted' || selfClass.audioType === 'soundcloud' || selfClass.audioType === 'fake') && o.skinwave_comments_enable === 'on') {

        waitForScriptToBeAvailableThenExecute(window.dzsap_part_comments_loaded, () => {
          window.comments_setupCommentsInitial(selfClass);
        })
      }


      if (o.autoplay === 'on' && o.cueMedia === 'on') {
        selfClass.increment_views = 1;
      }


      // -- soundcloud will setupmedia when api done

      if (o.cueMedia === 'on' && selfClass.audioType !== 'soundcloud') {
        if (is_android() || is_ios()) {
          cthis.find('.playbtn').on('click', play_media);
        }


        if (selfClass.data_source && selfClass.data_source.indexOf('{{generatenonce}}') > -1) {


          selfClass.mediaProtectionStatus = 'fetchingProtection';
          player_service_getSourceProtection(selfClass).then((response) => {
            if (response) {
              cthis.attr('data-source', response);
              setup_media({'called_from': 'nonce generated', 'newSource': response});

              selfClass.mediaProtectionStatus = 'protectedMode';
            }

          });
        } else {

          const isGoingToSetupMediaNow = player_isGoingToSetupMediaNow(selfClass);

          if (selfClass.audioType === 'selfHosted') {
            checkIfNeedsSongNameRefresh(selfClass);
          }

          if (isGoingToSetupMediaNow) {
            setup_media({'called_from': 'normal setup media .. --- icecast must wait'});
          }

        }


      } else {


        cthis.find('.playbtn').on('click', handleClickForSetupMedia);
        cthis.find('.scrubbar').on('click', handleClickForSetupMedia);
        view_handleResize(null, {
          called_from: 'init'
        });
      }


      // -- we call the api functions here


      player_determineStickToBottomContainer(selfClass);
      player_stickToBottomContainerDetermineClasses(selfClass);


      selfClass.timeModel.initObjects();

      // -- api calls
      selfClass.setup_media = setup_media;

      cthis.get(0).classInstance = selfClass;

      cthis.get(0).api_init_loaded = init_loaded; // -- force resize event
      cthis.get(0).api_destroy = destroy_it; // -- destroy the player and the listeners

      cthis.get(0).api_play = play_media; // -- play the media

      cthis.get(0).api_set_volume = (arg, pargs) => {
        volume_setVolume(selfClass, arg, pargs);
      }; // -- set a volume
      cthis.get(0).api_get_last_vol = volume_getLast; // -- play the media

      cthis.get(0).api_get_source = () => {
        return selfClass.data_source;
      }; // -- play the media

      cthis.get(0).api_click_for_setup_media = handleClickForSetupMedia; // -- play the media

      cthis.get(0).api_handleResize = view_handleResize; // -- force resize event
      cthis.get(0).api_set_playback_speed = set_playback_speed; // -- set the playback speed, only works for local hosted mp3
      cthis.get(0).api_seek_to_perc = seek_to_perc; // -- seek to percentage ( for example seek to 0.5 skips to half of the song )
      cthis.get(0).api_seek_to = seek_to; // -- seek to percentage ( for example seek to 0.5 skips to half of the song )
      cthis.get(0).api_seek_to_visual = seek_to_visual; // -- seek to perchange but only visually ( does not actually skip to that ) , good for a fake player
      cthis.get(0).api_visual_set_volume = (arg, pargs) => {
        volume_setOnlyVisual(selfClass, arg, pargs);
      } // -- set a volume
      cthis.get(0).api_destroy_listeners = destroy_listeners; // -- set a volume

      cthis.get(0).api_pause_media = pause_media; // -- pause the media
      cthis.get(0).api_get_media_isLoopActive = () => {
        return media_isLoopActive;
      }; // -- pause the media
      cthis.get(0).api_media_toggleLoop = media_toggleLoop; // -- pause the media
      cthis.get(0).api_pause_media_visual = pause_media_visual; // -- pause the media, but only visually
      cthis.get(0).api_play_media = play_media; // -- play the media
      cthis.get(0).api_play_media_visual = play_media_visual; // -- play the media, but only visually
      cthis.get(0).api_handle_end = media_handleEnd; // -- play the media, but only visually
      cthis.get(0).api_change_visual_target = change_visual_target; // -- play the media, but only visually
      cthis.get(0).api_change_design_color_highlight = view_updateColorHighlight; // -- play the media, but only visually
      cthis.get(0).api_draw_scrub_prog = view_drawScrubProgress; // -- render the scrub progress
      cthis.get(0).api_draw_curr_time = view_drawCurrentTime; // -- render the current time
      cthis.get(0).api_get_times = selfClass.timeModel.refreshTimes; // -- refresh the current time
      cthis.get(0).api_check_time = handleEnterFrame; // -- do actions required in the current frame
      cthis.get(0).api_sync_players_goto_next = () => {
        syncPlayers_gotoNext(selfClass);
      }; // -- in the sync playlist, go to the next song
      cthis.get(0).api_sync_players_goto_prev = () => {
        syncPlayers_gotoPrev(selfClass);
      }; // -- in the sync playlist, go to the previous song


      cthis.get(0).api_step_back = function (nrSeconds) {
        if (!nrSeconds) {
          nrSeconds = selfClass.keyboard_controls.step_back_amount;
        }
        seek_to(selfClass.timeCurrent - nrSeconds);
      }
      cthis.get(0).api_step_forward = function (nrSeconds) {

        if (nrSeconds) {

        } else {
          nrSeconds = selfClass.keyboard_controls.step_back_amount;
        }
        seek_to(selfClass.timeCurrent + nrSeconds);
      } // --
      /**
       *
       * @param {number} argSpeed  - 0 to 1
       */
      cthis.get(0).api_playback_speed = function (argSpeed) {
        if (selfClass.$mediaNode_ && selfClass.$mediaNode_.playbackRate) {
          selfClass.$mediaNode_.playbackRate = argSpeed;
        }
      } // -- slow to 2/3 of the current song


      cthis.get(0).api_set_action_audio_play = function (arg) {
        action_audio_play = arg;
      }; // -- set action on audio play
      cthis.get(0).api_set_action_audio_pause = function (arg) {
        action_audio_pause = arg;
      }; // -- set action on audio pause
      cthis.get(0).api_set_action_audio_end = function (arg) {
        action_audio_end = arg;
        cthis.data('has-action-end', 'on');
      }; // -- set action on audio end
      cthis.get(0).api_set_action_audio_comment = function (arg) {
        selfClass.action_audio_comment = arg;
      }; // -- set the function to call on audio song comment
      cthis.get(0).api_try_to_submit_view = service_submitView; // -- try to submit a new play analytic


      waitForScriptToBeAvailableThenExecute(window.dzsap_part_mediaChange_loaded, () => {
        window.dzsap_part_mediaChange_init(selfClass, $);
      })


      if (o.action_audio_play) {
        action_audio_play = o.action_audio_play;
      }

      if (o.action_audio_pause) {
        action_audio_pause = o.action_audio_pause;
      }

      if (o.action_audio_play2) {
        action_audio_play2 = o.action_audio_play2;
      }


      if (o.action_audio_end) {
        action_audio_end = o.action_audio_end;
        cthis.data('has-action-end', 'on');
      }


      handleEnterFrame({
        'fire_only_once': true
      });


      if (o.design_skin === 'skin-minimal') {
        handleEnterFrame({
          'fire_only_once': true
        });
      }


      player_setupMiscButtons(selfClass, $, seek_to)
      cthis.on('click', '.dzsap-loop-button', handle_mouse);
      cthis.on('mouseenter', handle_mouse);
      cthis.on('mouseleave', handle_mouse);


      selfClass.$conPlayPause.on('click', handleClick_playPause);


      $(window).on('resize.dzsap', view_handleResize);
      view_handleResize(null, {
        called_from: 'init'
      });

      dzsap_player_touchFunctionalitySetup(selfClass, seek_to_perc, $)

      if (o.skinwave_comments_mode_outer_selector) {
        waitForScriptToBeAvailableThenExecute(window.dzsap_part_comments_loaded, () => {

          window.player_commentsSelectorInit(selfClass, $, cthis, o);
        })
      }


      waitForScriptToBeAvailableThenExecute(window.dzsap_part_starRatings_loaded, function () {
        window.dzsap_init_starRatings_from_dzsap(selfClass);
      })


      setTimeout(function () {

        view_handleResize(null, {
          called_from: 'init_timeout'
        });


        if (o.skinwave_wave_mode === 'canvas') {

          calculate_dims_time();

          setTimeout(function () {
            calculate_dims_time();


          }, 100)
        }

      }, 100)


      player_menuStateSetup(cthis, o);

      player_nextPrevButtonsSetup(selfClass, cthis, o);
    }


    function calculate_dims_time() {
      var reflection_size = parseFloat(o.skinwave_wave_mode_canvas_reflection_size);

      reflection_size = 1 - reflection_size;

      var scrubbarh = selfClass._scrubbar.height();
      if (selfClass.initOptions.scrubbar_type === 'wave') {
        if (selfClass.skinwave_mode === 'small') {
          scrubbarh = 60;
        }

        if (selfClass._commentsHolder) {
          if (reflection_size === 0) {
            selfClass._commentsHolder.css('top', selfClass._scrubbar.offset().top - cthis.offset().top + scrubbarh * reflection_size - selfClass._commentsHolder.height());
          } else {
            selfClass._commentsHolder.css('top', selfClass._scrubbar.offset().top - selfClass._scrubbar.parent().offset().top + scrubbarh * reflection_size);
            selfClass.$commentsWritter.css('top', selfClass._scrubbar.offset().top - selfClass._scrubbar.parent().offset().top + scrubbarh * reflection_size);
          }
        }

        if (o.design_skin === 'skin-wave') {
          if (selfClass.$currTime) {
            selfClass.$currTime.css('top', scrubbarh * reflection_size - selfClass.$currTime.outerHeight());
          }
          if (selfClass.$totalTime) {
            selfClass.$totalTime.css('top', scrubbarh * reflection_size - selfClass.$totalTime.outerHeight());
          }
        }
      }

      cthis.attr('data-reflection-size', reflection_size);
    }

    /**
     * change the visual target, the main is the main player selfClass.isPlayerPlaying and the visual target is the player which is a visual representation of this
     * @param arg
     * @param pargs
     */
    function change_visual_target(arg, pargs) {

      var margs = {}

      if (pargs) {
        margs = $.extend(margs, pargs);
      }


      if (selfClass._sourcePlayer && selfClass._sourcePlayer.get(0) && selfClass._sourcePlayer.get(0).api_pause_media_visual) {
        selfClass._sourcePlayer.get(0).api_pause_media_visual({
          'call_from': 'change_visual_target'
        });
      }

      selfClass.set_sourcePlayer(arg);

      var $sourcePlayer_ = selfClass._sourcePlayer.get(0);
      if (selfClass.isPlayerPlaying) {
        if (selfClass._sourcePlayer && $sourcePlayer_ && $sourcePlayer_.api_play_media_visual) {
          $sourcePlayer_.api_play_media_visual();
        }
      }

      if ($sourcePlayer_ && $sourcePlayer_.api_draw_curr_time) {


        $sourcePlayer_.api_set_timeVisualCurrent(selfClass.timeCurrent);
        $sourcePlayer_.api_get_times({
          'call_from': ' change visual target .. in api '
        });
        $sourcePlayer_.api_check_time({
          'fire_only_once': true
        });
        $sourcePlayer_.api_draw_curr_time();
        $sourcePlayer_.api_draw_scrub_prog();
      }

      setTimeout(function () {

        if ($sourcePlayer_ && $sourcePlayer_.api_draw_curr_time) {
          $sourcePlayer_.api_get_times();
          $sourcePlayer_.api_check_time({
            'fire_only_once': true
          });
          $sourcePlayer_.api_draw_curr_time();
          $sourcePlayer_.api_draw_scrub_prog();
        }
      }, 800);

    }

    function view_updateColorHighlight(arg) {

      o.design_wave_color_progress = arg;
      if (o.skinwave_wave_mode === 'canvas') {
        view_drawCanvases(selfClass, cthis.attr('data-pcm'), 'canvas_change_pcm');
      }

    }

    /**
     * called in init_loaded()
     */
    function reinit() {

      if (selfClass.audioType === 'normal' || selfClass.audioType === 'detect' || selfClass.audioType === 'audio') {
        selfClass.audioType = 'selfHosted';
      }
    }


    function destroy_listeners() {


      if (isDestroyed) {
        return false;
      }


      isRenderingFrame = false;

    }


    function destroy_it() {


      if (isDestroyed) {
        return false;
      }

      if (selfClass.isPlayerPlaying) {
        pause_media();
      }


      $(window).off('resize.dzsap');

      cthis.remove();
      cthis = null;

      isDestroyed = true;
    }

    function handleClickForSetupMedia(e, pargs) {

      var margs = {

        'do_not_autoplay': false
      };

      if (pargs) {
        margs = $.extend(margs, pargs);
      }

      cthis.find('.playbtn').unbind('click', handleClickForSetupMedia);
      cthis.find('.scrubbar').unbind('click', handleClickForSetupMedia);

      setup_media(margs);


      if (is_android() || is_ios()) {
        play_media({
          'called_from': 'click_for_setup_media'
        });
      }
    }


    function init_checkIfReady(pargs) {
      var margs = {

        'do_not_autoplay': false
      };

      if (selfClass._actualPlayer && is_ios()) {
        return false;
      }


      if (pargs) {
        margs = $.extend(margs, pargs);
      }

      if (selfClass.audioType === 'youtube') {
        init_loaded(margs);
      } else {
        if (typeof (selfClass.$mediaNode_) !== 'undefined' && selfClass.$mediaNode_) {


          if (selfClass.$mediaNode_.nodeName !== "AUDIO" || o.type === 'shoutcast') {
            init_loaded(margs);
          } else {
            if (is_safari()) {

              if (selfClass.$mediaNode_.readyState >= 1) {

                if (selfClass.isPlayerLoaded === false) {
                }

                init_loaded(margs);
                clearInterval(selfClass.inter_checkReady);

                if (o.action_audio_loaded_metadata) {
                  o.action_audio_loaded_metadata(cthis);
                }
              }
            } else {
              if (selfClass.$mediaNode_.readyState >= 2) {
                if (selfClass.isPlayerLoaded === false) {
                }
                init_loaded(margs);
                clearInterval(selfClass.inter_checkReady);

                if (o.action_audio_loaded_metadata) {
                  o.action_audio_loaded_metadata(cthis);
                }
              }
            }

          }
        }

      }

    }

    function scrubbar_reveal() {
      setTimeout(function () {
        selfClass.cthis.addClass('scrubbar-loaded');
      }, 1000);
    }


    function setupStructure_thumbnailCon() {
      player_setupStructure_thumbnailCon(selfClass);
    }


    function setup_structure_sanitizers() {

      if (cthis.hasClass('zoomsounds-wrapper-bg-bellow') && cthis.find('.dzsap-wrapper-buts').length === 0) {

        cthis.append('<div class="temp-wrapper"></div>');
        cthis.find('.temp-wrapper').append(selfClass.extraHtmlAreas.controlsRight);
        cthis.find('.temp-wrapper').children('*:not(.dzsap-wrapper-but)').remove();
        cthis.find('.temp-wrapper > .dzsap-wrapper-but').unwrap();
        cthis.find('.dzsap-wrapper-but').each(function () {
          var aux = $(this).html();

          aux = aux.replace('{{heart_svg}}', '\t&hearts;');
          aux = aux.replace('{{svg_share_icon}}', svg_share_icon);


          if ($(this).get(0) && $(this).get(0).outerHTML.indexOf('dzsap-multisharer-but') > -1) {
            selfClass.isMultiSharer = true;

          }

          $(this).html(aux);
        }).wrapAll('<div class="dzsap-wrapper-buts"></div>');
      }

      if (o.design_skin === 'skin-customcontrols') {
        cthis.html(String(cthis.html()).replace('{{svg_play_icon}}', playbtn_svg));
        cthis.html(String(cthis.html()).replace('{{svg_pause_icon}}', pausebtn_svg));
      }
    }


    /**
     * called if we have .dzsap-multisharer-but in html
     */
    function check_multisharer() {

      // -- we setup a box main here as a child of body

      selfClass.cthis.find('.dzsap-multisharer-but').data('cthis', cthis);
      selfClass.cthis.data('embed_code', selfClass.feedEmbedCode);
    }

    function view_wave_setupRandomPcm(pargs) {


      var margs = {
        call_from: 'default'
      }


      if (pargs) {
        margs = $.extend(margs, pargs);
      }

      var default_pcm = [];

      if (!(o.pcm_data_try_to_generate === 'on' && o.pcm_data_try_to_generate_wait_for_real_pcm === 'on')) {
        for (var i3 = 0; i3 < 200; i3++) {
          default_pcm[i3] = Number(Math.random()).toFixed(2);
        }
        default_pcm = JSON.stringify(default_pcm);

        cthis.addClass('rnd-pcm-for-now')
        cthis.attr('data-pcm', default_pcm);
      }


      scrubbar_modeWave_setupCanvas({}, selfClass);


    }


    /**
     * called from setup_structure
     */
    function setup_structure_scrub() {


      if (o.skinwave_enableSpectrum === 'on') {


        // -- spectrum ON
        scrubbar_modeWave_setupCanvas({}, selfClass);
        // -- old spectrum code
        selfClass._scrubbarbg_canvas = selfClass.cthis.find('.scrub-bg-img').eq(0);
        selfClass._scrubBgCanvasCtx = selfClass._scrubbarbg_canvas.get(0).getContext("2d");
      } else {

        if (o.skinwave_wave_mode === 'canvas') {
          if (cthis.attr('data-pcm')) {
            scrubbar_modeWave_setupCanvas({}, selfClass);
          } else {
            view_wave_setupRandomPcm();
          }
        }
      }

    }
    ;


    /**
     * order -> init, setup_media, init_loaded
     * called from init() if not icecast or soundcloud
     * @param pargs
     * @returns {boolean}
     */
    function setup_media(pargs) {
      mediaSetup(selfClass, pargs, init_loaded, pause_media, init_checkIfReady, handleClick_playPause);
    }

    function setup_listeners() {


      if (isListenersSetup) {
        return false;
      }

      isListenersSetup = true;


      // -- adding scrubbar listeners
      selfClass._scrubbar.unbind('mousemove');
      selfClass._scrubbar.unbind('mouseleave');
      selfClass._scrubbar.unbind('click');
      player_scrubbarSetup(selfClass, $, o, view_drawCurrentTime, viewCalculateScrubbarWidth, play_media, seek_to);

      player_volumeSetup(selfClass, selfClass.volume_lastVolume, $, o);

      cthis.find('.playbtn').unbind('click');

      setTimeout(view_handleResize, 300);
      setTimeout(view_handleResize, 2000);

      if (o.settings_trigger_resize > 0) {
        inter_trigger_resize = setInterval(view_handleResize, o.settings_trigger_resize);
      }

      cthis.addClass('listeners-setuped');

      return false;
    }


    function volume_getLast() {
      return selfClass.volume_lastVolume;
    }

    /**
     * init laoded
     * @param pargs
     */
    function init_loaded(pargs) {
      if (cthis.hasClass('dzsap-loaded')) {
        return;
      }

      let margs = {
        'do_not_autoplay': false,
        'call_from': 'init'
      };
      if (pargs) {
        margs = $.extend(margs, pargs);
      }

      setTimeout(function () {
        selfClass.isSafeToChangeTrack = true;
      }, 5000);


      clearInterval(selfClass.inter_checkReady);
      clearTimeout(selfClass.inter_checkReady);
      setup_listeners();


      setTimeout(function () {
        if (selfClass.$currTime && selfClass.$currTime.outerWidth() > 0) {
          selfClass.currTime_outerWidth = selfClass.$currTime.outerWidth();
        }
      }, 1000);


      // -- this comes from cue off, no pcm data


      if (selfClass.isPcmRequiredToGenerate) {
        scrubModeWave__checkIfWeShouldTryToGetPcm(selfClass, {
          called_from: 'init_loaded()'
        });
      }

      if (selfClass.audioType !== 'fake' && margs.call_from !== 'force_reload_change_media') {
        if (o.settings_exclude_from_list !== 'on' && dzsap_list && dzsap_list.indexOf(cthis) === -1) {
          if (selfClass._actualPlayer === null) {
            dzsap_list.push(cthis);
          }
        }
        if (o.type_audio_stop_buffer_on_unfocus === 'on') {
          cthis.data('type_audio_stop_buffer_on_unfocus', 'on');
          cthis.get(0).api_destroy_for_rebuffer = function () {
            if (o.type === 'audio') {
              selfClass.playFrom = selfClass.$mediaNode_.currentTime;
            }
            destroy_media(selfClass, pause_media);
            isDestroyedForRebuffer = true;
          }

        }
      }

      if (selfClass.ajax_view_submitted === 'auto') {
        setTimeout(function () {
          if (selfClass.ajax_view_submitted === 'auto') {
            selfClass.ajax_view_submitted = 'off';
          }
        }, 1000);
      }

      selfClass.isPlayerLoaded = true;


      if (selfClass.data_source !== DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED) {
        cthis.addClass(DZSAP_PLAYER_CLASS_LOADED);


        if (selfClass.isStickyPlayer) {

          if (window['dzsap_stickyPlayer_show']) {
            setTimeout(() => {
              dzsap_stickyPlayer_show(selfClass);
            }, 500);
          }
        }
      }


      volume_setVolume(selfClass, getDefaultVolume(o, selfClass), {
        call_from: "from_init_loaded"
      });


      if (isInt(selfClass.playFrom)) {
        seek_to(selfClass.playFrom, {
          call_from: 'from_playfrom'
        });
      }


      if (selfClass.playFrom === DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS) {
        // -- here we save last position
        if (typeof Storage !== 'undefined') {
          setTimeout(function () {
            selfClass.playFrom_ready = true;
          })


          if (typeof localStorage['dzsap_' + selfClass.the_player_id + '_lastpos'] !== 'undefined') {
            seek_to(localStorage['dzsap_' + selfClass.the_player_id + '_lastpos'], {
              'call_from': 'last_pos'
            });
          }
        }
      }

      if (margs.do_not_autoplay !== true) {
        if (o.autoplay === 'on' && o.cueMedia === 'on') {
          play_media({
            'called_from': 'do not autoplay not true ( init_loaded() ) '
          });
        }

      }

      if (selfClass.$mediaNode_ && selfClass.$mediaNode_.duration) {
        player_view_addMetaLoaded(selfClass);
      }

      reinit();

      handleEnterFrame({
        'fire_only_once': false
      });

      if (o.autoplay === 'off') {
        isRenderingFrame = false;
      }

      cthis.addClass('init-loaded');

      setTimeout(function () {

        selfClass.timeModel.refreshTimes({
          'call_from': 'set timeout 500'
        });
        handleEnterFrame({
          'fire_only_once': true
        });

        cthis.find('.wave-download').on('click', handle_mouse);
      }, 500);

      setTimeout(function () {
        selfClass.timeModel.refreshTimes({
          'call_from': 'set timeout 1000'
        });

        handleEnterFrame({
          'fire_only_once': true
        });


      }, 1000);


      if (inter_60_secs_contor === 0 && o.action_video_contor_60secs) {
        inter_60_secs_contor = setInterval(count_60secs, 30000);
      }
    }


    function count_60secs() {
      if (o.action_video_contor_60secs && cthis.hasClass('is-playing')) {
        o.action_video_contor_60secs(cthis, '');
      }
    }

    /**
     *
     * @param {boolean} isGoingToActivate
     */
    function media_toggleLoop(isGoingToActivate) {
      media_isLoopActive = isGoingToActivate;
    }

    function handle_mouse(e) {
      const $t = $(this);

      if (e.type === 'click') {
        if ($t.hasClass('wave-download')) {
          (ajax_submit_download.bind(selfClass))();
        }
        if ($t.hasClass('dzsap-loop-button')) {
          if ($t.hasClass('active')) {
            $t.removeClass('active');
            media_isLoopActive = false;
          } else {

            $t.addClass('active');
            media_isLoopActive = true;

          }
        }
      }
      if (e.type === 'mouseover') {
      }
      if (e.type === 'mouseenter') {

        if (o.preview_on_hover === 'on') {
          seek_to_perc(0);

          play_media({
            'called_from': 'preview_on_hover'
          });
        }

        window.dzsap_mouseover = true;
      }
      if (e.type === 'mouseleave') {

        if (o.preview_on_hover === 'on') {
          seek_to_perc(0);
          pause_media();
        }
        window.dzsap_mouseover = false;
      }
    }


    function view_drawCurrentTime() {

      // -- draw current time -- called onEnterFrame when playing

      let currentTime = selfClass.timeModel.getVisualCurrentTime();
      let totalTime = selfClass.timeModel.getVisualTotalTime();

      if (selfClass.initOptions.scrubbar_type === 'wave') {
        if (selfClass.initOptions.skinwave_enableSpectrum === 'on') {


          if (!selfClass.isPlayerPlayingEased) {
            return false;
          }
          view_spectrum_drawMeter(selfClass, selfClass._scrubbarbg_canvas, spectrum_fakeItMode, selfClass.heightCanvas);


          // -- end spectrum
        }

        if (selfClass.$currTime && selfClass.$currTime.length) {

          if (selfClass.initOptions.skinwave_timer_static !== 'on') {
            view_player_scrubModeWaveAdjustCurrTimeAndTotalTime(selfClass);

          }

        }
      }

      if (totalTime !== last_time_total) {
        view_updateTotalTime(totalTime)
      }

      if (selfClass.$currTime) {
        if (selfClass.isScrubShowingCurrentTime === false) {
          selfClass.$currTime.html(formatTime(currentTime));
        }

        if (selfClass.timeModel.getVisualTotalTime() && selfClass.timeModel.getVisualTotalTime() > -1) {
          selfClass.cthis.addClass('time-total-visible');
        }
      }


      if (selfClass.spectrum_audioContext) {
        if (selfClass.$totalTime) {
          selfClass.$totalTime.html(formatTime(totalTime));
        }
      }

    }


    function view_updateTotalTime(totalTime) {

      if (selfClass.$totalTime) {
        selfClass.$totalTime.html(formatTime(totalTime));
        selfClass.$totalTime.fadeIn('fast');
      }
    }

    /**
     * draw the scrub width
     * @returns {void}
     */
    function view_drawScrubProgress() {
      let currentTime = selfClass.timeModel.getVisualCurrentTime();
      let totalTime = selfClass.timeModel.getVisualTotalTime();

      selfClass.scrubbarProgX = (currentTime / totalTime) * selfClass.scrubbarWidth;

      if (isNaN(selfClass.scrubbarProgX)) {
        selfClass.scrubbarProgX = 0;
      }
      if (selfClass.scrubbarProgX > selfClass.scrubbarWidth) {
        selfClass.scrubbarProgX = selfClass.scrubbarWidth;
      }

      if (currentTime < 0) {
        selfClass.scrubbarProgX = 0;
      }

      if (totalTime === 0 || isNaN(totalTime)) {
        selfClass.scrubbarProgX = 0;
      }

      if (selfClass.scrubbarProgX < 2 && cthis.data('promise-to-play-footer-player-from')) {
        return;
      }

      if (selfClass.spectrum_audioContext_buffer === null) {
        if (selfClass.$$scrubbProg) {
          selfClass.$$scrubbProg.style.width = parseInt(selfClass.scrubbarProgX, 10) + 'px';
        }
      }

    }

    /**
     * fired on requestAnimationFrame
     * @param pargs
     * @returns {boolean}
     */
    function handleEnterFrame(pargs) {


      // -- enter frame
      const margs = $.extend({
        'fire_only_once': false
      }, pargs || {});

      if (isDestroyed) {
        return false;
      }


      selfClass.timeModel.refreshTimes({
        'call_from': 'checK_time'
      });

      if (selfClass.audioType === 'selfHosted') {

      }


      view_drawScrubProgress();


      selfClass.timeModel.processCurrentFrame();


      // -- skin minimal
      if (o.design_skin === 'skin-minimal') {
        waitForScriptToBeAvailableThenExecute(window.dzsap_view_player_skinMinimal_drawFrame, function () {
          window.dzsap_view_player_skinMinimal_drawFrame(selfClass, selfClass.scrubbarProgX);
        })
      }


      // -- enter_frame
      view_drawCurrentTime();


      if (margs.fire_only_once !== true) {
        requestAnimationFrame(handleEnterFrame);
      }
    }

    function handleClick_playPause(e) {

      if (cthis.hasClass('prevent-bubble')) {
        if (e && e.stopPropagation) {
          e.preventDefault();
          e.stopPropagation();
        }
      }

      const $conPlayPause = $(this);

      let isToggleCancelled = false;

      if (!cthis.hasClass('listeners-setuped')) {


        $(selfClass.$mediaNode_).attr('preload', 'auto');

        setup_listeners();
        init_loaded();


        const inter_checkTotalTime = setInterval(function () {

          if (selfClass.$mediaNode_ && selfClass.$mediaNode_.duration && isNaN(selfClass.$mediaNode_.duration) === false) {


            clearInterval(inter_checkTotalTime);
          }
        }, 1000);
      }


      if (o.design_skin === 'skin-minimal') {
        waitForScriptToBeAvailableThenExecute(window.dzsap_skin_minimal_inited, function () {
          window.dzsap_view_player_skinMinimal_onClickPlayPause(selfClass, $conPlayPause, seek_to_perc, handleEnterFrame, e, togglePlayPause);
        })

        isToggleCancelled = true;
      }

      if (!isToggleCancelled) {
        togglePlayPause()
      }


      return false;
    }

    function togglePlayPause() {
      if (!selfClass.isPlayerPlaying) {
        play_media({
          'called_from': 'click_playpause'
        });
      } else {
        pause_media();
      }
    }


    /**
     *
     * @param pargs
     * @returns {boolean|void}
     */
    function media_handleEnd(pargs) {

      media_handleEventEnd(selfClass, pargs, media_isLoopActive, seek_to, play_media, pause_media, o, syncPlayers_gotoNext, action_audio_end);
    }


    function view_handleResize(e, pargs) {


      if (cthis) {

      }

      ww = $(window).width();
      selfClass.cthisWidth = cthis.width();
      th = cthis.height();


      if (selfClass._scrubbarbg_canvas && typeof (selfClass._scrubbarbg_canvas.width) === 'function') {
        selfClass.canvasWidth = selfClass._scrubbarbg_canvas.width();
        selfClass.heightCanvas = selfClass._scrubbarbg_canvas.height();
      }


      if (selfClass.cthisWidth <= 720) {
        cthis.addClass('under-720');
      } else {

        cthis.removeClass('under-720');
      }
      if (selfClass.cthisWidth <= 500) {
        // -- width under 500

        cthis.addClass('under-500');


      } else {
        // -- width under 500


        cthis.removeClass('under-500');
      }


      view_resizeHandleTreatSkins(selfClass);


      // -- display none + overflow hidden hack does not work .. yeah

      if (cthis.css('display') !== 'none') {
        selfClass._scrubbar.find('.scrub-bg-img').eq(0).css({
          'width': selfClass._scrubbar.children('.scrub-bg').width()
        });
        selfClass._scrubbar.find('.scrub-prog-img').eq(0).css({
          'width': selfClass._scrubbar.children('.scrub-bg').width()
        });
      }

      cthis.removeClass('under-240 under-400');
      if (selfClass.cthisWidth <= 240) {
        cthis.addClass('under-240');
      }
      if (selfClass.cthisWidth <= 500) {
        cthis.addClass('under-400');

        if (is_under_400 === false) {
          is_under_400 = true;
        }
        if (selfClass.$controlsVolume) {
        }

      } else {


        if (is_under_400 === true) {
          is_under_400 = false;
        }
      }


      var aux2 = 50;

      // -- skin-wave
      if (o.design_skin === 'skin-wave') {
        // ---------- calculate dims small
        if (selfClass.skinwave_mode === 'small') {
          selfClass.scrubbarWidth = selfClass._scrubbar.width();
        }


        if (o.skinwave_wave_mode === 'canvas') {
          if (cthis.attr('data-pcm')) {
            if (selfClass._scrubbarbg_canvas.width() === 100) {
              selfClass._scrubbarbg_canvas.width(selfClass._scrubbar.width());
            }
            if (selfClass.data_source !== 'fake') {
              // -- if inter definied then clear timeout and call
              if (draw_canvas_inter) {
                clearTimeout(draw_canvas_inter);
                draw_canvas_inter = setTimeout(draw_canvas_inter_func, 300);
              } else {
                draw_canvas_inter_func();
                draw_canvas_inter = 1;
              }
            }
          }
        }
      }


      if (['skin-minimal', 'skin-minion'].indexOf(o.design_skin) > -1) {

        if (window.dzsap_skinFunctions_handleResize[o.design_skin]) {
          window.dzsap_skinFunctions_handleResize[o.design_skin](selfClass);
        }

      }


      if (o.design_skin === 'skin-default') {
        if (selfClass.$currTime) {

          if (selfClass._metaArtistCon.css('display') === 'none') {
            selfClass._metaArtistCon_w = 0;
          }
          if (isNaN(selfClass._metaArtistCon_l)) {
            selfClass._metaArtistCon_l = 20;
          }
        }

      }


      if (o.embedded === 'on') {
        view_resizeEmbedded(selfClass);
      }


      view_drawScrubProgress();

      if (o.settings_trigger_resize > 0) {
        if (o.parentgallery && $(o.parentgallery).get(0) !== undefined && $(o.parentgallery).get(0).api_handleResize !== undefined) {
          $(o.parentgallery).get(0).api_handleResize();
        }
      }

    }


    function draw_canvas_inter_func() {
      view_drawCanvases(selfClass, cthis.attr('data-pcm'), 'canvas_normal_pcm');

      draw_canvas_inter = 0;
    }


    function viewCalculateScrubbarWidth() {


      if (selfClass.scrubbarWidth === 0) {
        selfClass.scrubbarWidth = selfClass._scrubbar.width();
      }
      if (selfClass.scrubbarWidth === 0) {
        selfClass.scrubbarWidth = 300;
      }
    }

    function seek_to_perc(argperc, pargs) {
      seek_to((argperc * selfClass.timeModel.getVisualTotalTime()), pargs);
    }

    /**
     * seek to seconds
     * @param targetTimeMediaScrub - number of seconds
     * @param pargs -- optiona arguments
     * @returns {boolean}
     */
    function seek_to(targetTimeMediaScrub, pargs = {}) {

      var margs = {
        'call_from': 'default'
        , 'deeplinking': 'off' // -- default or "auto" or "user action"
        , 'call_from_type': 'default' // -- default or "auto" or "user action"
      };

      if (pargs) {
        margs = $.extend(margs, pargs);
      }

      if (margs.call_from === 'from_feeder_to_feed') {

      }

      if (margs.deeplinking === 'on') {
        var newlink = add_query_arg(window.location.href, 'audio_time', targetTimeMediaScrub);


        var stateObj = {foo: "bar"};
        history.pushState(stateObj, null, newlink);
      }

      targetTimeMediaScrub = sanitizeToIntFromPointTime(targetTimeMediaScrub);

      targetTimeMediaScrub = selfClass.timeModel.getActualTargetTime(targetTimeMediaScrub);

      if (selfClass._actualPlayer) {
        player_feederSeekTo(selfClass, margs, targetTimeMediaScrub);

        return false;
      }


      if (selfClass.audioType === 'youtube') {
        try {
          selfClass.$mediaNode_.seekTo(targetTimeMediaScrub);
        } catch (err) {

        }
      }

      handleEnterFrame({
        'fire_only_once': true
      })
      setTimeout(function () {
        handleEnterFrame({
          'fire_only_once': true
        })
      }, 20);


      if (selfClass.audioType === 'selfHosted') {
        if (0) {

          selfClass.lastTimeInSeconds = targetTimeMediaScrub;

          pause_media({
            'audioapi_setlasttime': false
          });
          play_media({
            'called_from': 'audio_buffer ( seek_to() )'
          });
        } else {

          if (selfClass.$mediaNode_ && typeof (selfClass.$mediaNode_.currentTime) !== 'undefined') {

            try {
              selfClass.$mediaNode_.currentTime = targetTimeMediaScrub;
            } catch (e) {
            }

          }

          return false;

        }

      }


    }

    /**
     * seek to ( only visual )
     * @param argperc
     */
    function seek_to_visual(argperc) {


      curr_time_first_set = true;


      handleEnterFrame({
        'fire_only_once': true
      })
      setTimeout(function () {
        handleEnterFrame({
          'fire_only_once': true
        })
      }, 20);
    }

    /**
     * playback speed
     * @param {float} arg 0 - 10
     */
    function set_playback_speed(arg) {

      if (selfClass.audioType === 'youtube') {
        selfClass.$mediaNode_.setPlaybackRate(arg);
      }
      if (selfClass.audioType === 'selfHosted') {
        selfClass.$mediaNode_.playbackRate = arg;

      }

    }


    function pause_media_visual(pargs) {


      var margs = {
        'call_from': 'default'
      };


      if (pargs) {
        margs = $.extend(margs, pargs);
      }

      selfClass.$conPlayPause.removeClass('playing');
      cthis.removeClass('is-playing');
      selfClass.isPlayerPlaying = false;
      clearTimeout(selfClass.isPlayerPlayingEasedInterval);
      selfClass.isPlayerPlayingEasedInterval = setTimeout(() => {
        selfClass.isPlayerPlayingEased = false;
      }, DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT);


      if (cthis.parent().hasClass('zoomsounds-wrapper-bg-center')) {
        cthis.parent().removeClass('is-playing');
      }
      if (selfClass.$reflectionVisualObject) {
        selfClass.$reflectionVisualObject.removeClass('is-playing');
      }

      if (o.parentgallery) {
        o.parentgallery.removeClass('player-is-playing');
      }


      clearTimeout(isRenderingFrameInterval);
      isRenderingFrameInterval = setTimeout(() => {

        isRenderingFrame = false;
      }, DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT);


      if (action_audio_pause) {
        action_audio_pause(cthis);
      }
    }

    function pause_media(pargs) {

      var margs = {
        'audioapi_setlasttime': true,
        'donot_change_media': false,
        'call_actual_player': true,
      };

      if (isDestroyed) {
        return false;
      }

      if (pargs) {
        margs = $.extend(margs, pargs);
      }


      pause_media_visual({
        'call_from': 'pause_media'
      });


      if (margs.call_actual_player && margs.donot_change_media !== true) {
        if (selfClass._actualPlayer !== null) {
          player_actualPlayerPause(selfClass);

          return;
        }
      }


      media_pause(selfClass, () => {
        if (selfClass._sourcePlayer) {
          player_sourcePlayerPauseVisual(selfClass);
        }
      })


    }

    function play_media_visual(margs) {


      selfClass.isPlayerPlaying = true;
      clearTimeout(selfClass.isPlayerPlayingEasedInterval);
      selfClass.isPlayerPlayingEased = true;
      clearTimeout(isRenderingFrameInterval);
      isRenderingFrame = true;

      cthis.addClass('is-playing');
      cthis.addClass('first-played');

      if (selfClass.$reflectionVisualObject) {
        selfClass.$reflectionVisualObject.addClass('is-playing');
      }
      if (o.parentgallery) {
        o.parentgallery.addClass('player-is-playing');
      }

      if (selfClass.classFunctionalityInnerPlaylist) {
        selfClass.classFunctionalityInnerPlaylist.player_determineSyncPlayersIndex(selfClass, selfClass._sourcePlayer);
      }
      view_player_globalDetermineSyncPlayersIndex(selfClass);

      view_player_playMiscEffects(selfClass);


      if (selfClass.isStickyPlayer) {


        if (window['dzsap_stickyPlayer_show']) {
          dzsap_stickyPlayer_show(selfClass);
        }
      }


      if (action_audio_play) {
        action_audio_play(cthis);
      }
      if (action_audio_play2) {
        action_audio_play2(cthis);
      }


    }

    function play_media(pargs) {

      var margs = {
        'api_report_play_media': true,
        'called_from': 'default',
        'retry_call': 0
      }
      if (pargs) {
        margs = $.extend(margs, pargs)
      }

      if (!selfClass.isSetupedMedia) {
        setup_media({'called_from': margs.called_from + '[play_media .. not setuped]'});
      }


      if (margs.called_from === 'api_sync_players_prev') {

        player_index_in_gallery = cthis.parent().children('.audioplayer,.audioplayer-tobe').index(cthis);

        if (o.parentgallery && o.parentgallery.get(0) && o.parentgallery.get(0).api_goto_item) {
          o.parentgallery.get(0).api_goto_item(player_index_in_gallery);
        }
      }

      if ((is_ios() && selfClass.spectrum_audioContext_buffer === 'waiting') || selfClass.mediaProtectionStatus === 'fetchingProtection') {
        setTimeout(function () {
          if (!pargs) {
            pargs = {};
          }
          pargs.call_from_aux = 'waiting audioCtx_buffer or ios';
          play_media(pargs);
        }, 500);
        return false;
      }

      if (margs.called_from === 'click_playpause') {
        // -- lets setup the playlist
      }


      if (!cthis.hasClass('media-setuped') && selfClass._actualPlayer === null) {
        console.log('[dzsap][warning] media not setuped, there might be issues', cthis.attr('id'));
      }


      if (margs.called_from.indexOf('feed_to_feeder') > -1) {
        if (!cthis.hasClass('dzsap-loaded')) {
          init_loaded();
          let delay = 300;
          if (is_android()) {
            delay = 0;
          }
          if (margs.call_from_aux !== 'with delay') {
            if (delay) {
              setTimeout(function () {
                margs.call_from_aux = 'with delay';
                play_media(margs);
              }, delay);
            } else {
              play_media(margs);
            }
            return false;
          }

        }
      }

      player_stopOtherPlayers(dzsap_list, selfClass);


      if (isDestroyedForRebuffer) {
        setup_media({
          'called_from': 'play_media() .. destroyed for rebuffer'
        });
        if (isInt(selfClass.playFrom)) {
          seek_to(selfClass.playFrom, {
            'call_from': 'destroyed_for_rebuffer_playfrom'
          });
        }
        isDestroyedForRebuffer = false;
      }


      // -- media functions

      if (selfClass._sourcePlayer) {
        player_sourcePlayerPlayVisual(selfClass);
      }


      if (selfClass._actualPlayer) {
        var output = player_actualPlayerPlay(selfClass, margs, seek_to);
        if (output.itShouldReturn) {
          return;
        }
      }


      if (selfClass.audioType === 'youtube') {
        dzsap_youtube_playMedia(selfClass, margs, selfClass.youtube_currentId);
      }
      if (selfClass.audioType === 'selfHosted') {


      }


      if (selfClass.audioType === 'youtube') {
        play_media_visual(margs);
      }

      media_tryToPlay(selfClass, () => {

        play_media_visual(margs);
        if (o.skinwave_enableSpectrum === 'on') {
          player_initSpectrum(selfClass);
        }

        if (selfClass._sourcePlayer) {
          window.dzsap_currplayer_focused = selfClass._sourcePlayer.get(0);
          if (selfClass._sourcePlayer.get(0) && selfClass._sourcePlayer.get(0).api_pause_media_visual) {
            selfClass._sourcePlayer.get(0).api_try_to_submit_view();
          }
        } else {
          window.dzsap_currplayer_focused = cthis.get(0);
          service_submitView();
        }


        if (selfClass.keyboard_controls.play_trigger_step_back === 'on') {
          if (dzsap_currplayer_focused) {
            dzsap_currplayer_focused.api_step_back(selfClass.keyboard_controls.step_back_amount);
          }
        }
      }, (err) => {
        console.log('error autoplay playing -  ', err);
        setTimeout(() => {
          pause_media();
          console.log('trying to pause')
        }, 30);
      })


    }


    function service_submitView() {
      if (selfClass.ajax_view_submitted === 'auto') {
        selfClass.ajax_view_submitted = 'off';
      }
      if (selfClass.ajax_view_submitted === 'off') {

        (ajax_submit_views.bind(selfClass))();
      }
    }


  }
}


function register_dzsap_plugin() {
  if (!window.dzsap_settings) {
    window.dzsap_settings = {};
  }
  (function ($) {


    assignHelperFunctionsToJquery($);


    // -- define player here
    $.fn.audioplayer = function (argOptions) {
      let finalOptions = {};
      const defaultOptions = Object.assign({}, defaultPlayerOptions);
      finalOptions = convertPluginOptionsToFinalOptions(this, defaultOptions, argOptions);


      this.each(function () {
        return new DzsAudioPlayer(this, Object.assign({}, finalOptions), $);
      })
    }
  })(jQuery);
}

window.dzsap_singleton_ready_calls_is_called = false;


/**
 * not reliable
 */
window.dzsap_get_base_url = function () {

  window.dzsap_base_url = (window.dzsap_settings && window.dzsap_settings.pluginurl) ? window.dzsap_settings.pluginurl : getBaseUrl('dzsap_base_url', 'audioplayer.js');

}


window.dzsap_currplayer_focused = null;
window.dzsap_currplayer_from_share = null;
window.dzsap_mouseover = false;


window.dzsap_init_allPlayers = function ($) {

  const $feed_dzsapConfigs = $('.dzsap-feed--dzsap-configs');
  if ($feed_dzsapConfigs.length) {
    window.dzsap_apconfigs = JSON.parse($feed_dzsapConfigs.last().html());
  }

  $('.audioplayer.auto-init,.audioplayer-tobe.auto-init').each(function () {
    const $playerTarget = $(this);
    if ($playerTarget.hasClass('audioplayer-tobe')) {
      if (window.dzsap_init) {
        dzsap_init($playerTarget);
      }
    }
  })
}


dzsap_jQueryInit().then(() => {
  register_dzsap_plugin();
  window.dzsap_get_base_url();
  jQuery(document).ready(function ($) {


    // -- defined gallery here
    // --
    // AUDIO GALLERY
    // --


    // -- main call

    if (!window.dzsap_singleton_ready_calls_is_called) {
      dzsap_singleton_ready_calls();
    }


    jQueryAuxBindings($);

    window.dzsap_init_allPlayers($)
  });


  if (!window.dzsap_player_isOneTimeSetuped) {
    dzsap_oneTimeSetups();
    window.dzsap_player_isOneTimeSetuped = true;
  }
}).catch((err) => {
  console.log(err);
})


window.dzsap_init = function (selector, settings) {
  jQuery(selector).audioplayer(Object.assign({}, settings));

  window.dzsap_lasto = settings;
};
playerRegisterWindowFunctions();
