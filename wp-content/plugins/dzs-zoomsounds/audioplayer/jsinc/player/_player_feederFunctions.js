import {ajax_submit_views} from "../_dzsap_ajax";


export function player_sourcePlayerPauseVisual(selfClass){
  if (selfClass._sourcePlayer.get(0) && selfClass._sourcePlayer.get(0).api_pause_media_visual) {
    selfClass._sourcePlayer.get(0).api_pause_media_visual({
      'call_from': 'pause_media in child player'
    });
  }
}
export function player_sourcePlayerPlayVisual(selfClass){
  if (selfClass._sourcePlayer.get(0) && selfClass._sourcePlayer.get(0).api_play_media_visual) {
    selfClass._sourcePlayer.get(0).api_play_media_visual({
      'api_report_play_media': false
    });
  }
}
export function player_actualPlayerPlay(selfClass, margs, seek_to){

  const cthis = selfClass.cthis;
  const o = selfClass.initOptions;

  const output = {
    itShouldReturn : false
  }
  // -- the actual player is the footer player

  let args = {
    type: selfClass.actualDataTypeOfMedia,
    fakeplayer_is_feeder: 'on',
    call_from: 'play_media_audioplayer'
  }

  try {
    if (margs.called_from === 'click_playpause') {
      // -- let us reset up the playlist


      if (o.parentgallery) {
        o.parentgallery.get(0).api_regenerate_sync_players_with_this_playlist();
        selfClass._actualPlayer.get(0).api_regenerate_playerlist_inner();
      }

    }

    if (selfClass._actualPlayer && selfClass._actualPlayer.length && selfClass._actualPlayer.data('feeding-from') !== cthis.get(0)) {

      args.called_from = 'play_media from player 22 ' + cthis.attr('data-source') + ' < -  ' + 'old call from - ' + margs.called_from;

      if (selfClass._actualPlayer.get(0).api_change_media) {
        selfClass._actualPlayer.get(0).api_change_media(cthis, args);
      }

      if (!cthis.hasClass('first-played')) {
        if (cthis.data('promise-to-play-footer-player-from')) {
          seek_to(cthis.data('promise-to-play-footer-player-from'));
          setTimeout(function () {
            cthis.data('promise-to-play-footer-player-from', '');
          }, 1000);
        }
      }

    }
    setTimeout(function () {
      if (selfClass._actualPlayer.get(0) && selfClass._actualPlayer.get(0).api_play_media) {
        selfClass._actualPlayer.get(0).api_play_media({
          'called_from': '[feed_to_feeder]'
        });
      }
    }, 100);


    if (selfClass.ajax_view_submitted === 'off') {
      (ajax_submit_views.bind(selfClass))();
    }

    output.itShouldReturn = true;


  } catch (err) {
    console.log('no fake player..', err);
  }

  return output;
}
export function player_actualPlayerPause(selfClass){

  const cthis = selfClass.cthis;


  let args = {
    type: selfClass.actualDataTypeOfMedia,
    fakeplayer_is_feeder: 'on'
  }
  if (selfClass._actualPlayer && selfClass._actualPlayer.length && selfClass._actualPlayer.data('feeding-from') !== cthis.get(0)) {
    args.called_from = 'pause_media from player ' + cthis.attr('data-source');
    selfClass._actualPlayer.get(0).api_change_media(cthis, args);
  }
  setTimeout(function () {
    if (selfClass._actualPlayer.get(0) && selfClass._actualPlayer.get(0).api_pause_media) {
      selfClass._actualPlayer.get(0).api_pause_media();
    }
  }, 100);
}
export function player_feederSeekTo(selfClass, margs, targetTimeMediaScrub){

  const cthis = selfClass.cthis;

  let args = {
    type: selfClass.actualDataTypeOfMedia,
    fakeplayer_is_feeder: 'on'
  }
  if (selfClass._actualPlayer.length && selfClass._actualPlayer.data('feeding-from') !== cthis.get(0)) {
    // -- the actualPlayer is not rendering this feed player
    if (margs.call_from !== 'handle_end' && margs.call_from !== 'from_playfrom' && margs.call_from !== 'last_pos' && margs.call_from !== 'playlist_seek_from_0') {
      // -- if it is not user action, ( handle_end or anything else )
      args.called_from = 'seek_to from player source->' + (cthis.attr('data-source')) + ' < -  ' + 'old call from - ' + margs.call_from;
      if (selfClass._actualPlayer.get(0).api_change_media) {
        selfClass._actualPlayer.get(0).api_change_media(cthis, args);
      } else {
        console.log('not inited ? ', selfClass._actualPlayer);
      }
    } else {
      // -- NORMAL call

      cthis.data('promise-to-play-footer-player-from', targetTimeMediaScrub);
    }
  }


  setTimeout(function () {

    if (selfClass._actualPlayer.get(0) && selfClass._actualPlayer.get(0).api_pause_media) {
      if (margs.call_from !== 'from_playfrom' && margs.call_from !== 'last_pos') {
        selfClass._actualPlayer.get(0).api_seek_to(targetTimeMediaScrub, {
          'call_from': 'from_feeder_to_feed'
        });
      }
    }
  }, 50);
}
