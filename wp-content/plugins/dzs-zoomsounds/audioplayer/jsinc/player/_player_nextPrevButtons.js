import {player_syncPlayers_gotoItem} from "../_dzsap_helpers";

export const player_nextPrevButtonsSetup = (selfClass, cthis, o) => {

  cthis.on('click', '.prev-btn,.next-btn', handle_mouse);


  function handle_mouse(e) {
    let _target;
    const $t = jQuery(this);

    if (e.type === 'click') {
      if ($t.hasClass('prev-btn')) {
        handleClick_prevBtn();
      }
      if ($t.hasClass('next-btn')) {
        handleClick_nextBtn();
      }
    }
  }


  function handleClick_prevBtn() {
    if (o.parentgallery && (o.parentgallery.get(0))) {
      o.parentgallery.get(0).api_goto_prev();
    } else {

      syncPlayers_gotoPrev(selfClass);
    }
  }

  function handleClick_nextBtn() {
    if (o.parentgallery && (o.parentgallery.get(0))) {
      o.parentgallery.get(0).api_goto_next();
    } else {

      syncPlayers_gotoNext(selfClass);
    }
  }
}


export function syncPlayers_gotoPrev(selfClass) {


  if (selfClass._actualPlayer) {
    selfClass._actualPlayer.get(0).api_sync_players_goto_prev();

    return false;
  }


  syncPlayers_gotoItem(selfClass, -1);

}


/**
 * go to next inner playlistItem for single player
 * @returns {boolean}
 */
export function syncPlayers_gotoNext(selfClass) {

  if (selfClass._actualPlayer) {
    selfClass._actualPlayer.get(0).api_sync_players_goto_next();

    return false;
  }
  syncPlayers_gotoItem(selfClass, 1);
}

/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param targetIncrement
 */
export function syncPlayers_gotoItem(selfClass, targetIncrement = 0) {


  let targetIndex = 0;
  if (selfClass.classFunctionalityInnerPlaylist) {
    // -- playlist Inner

    targetIndex = selfClass.playlist_inner_currNr + targetIncrement;
    if (targetIndex >= 0) {
      selfClass.classFunctionalityInnerPlaylist.playlistInner_gotoItem(targetIndex, {
        'call_from': 'api_sync_players_prev'
      });
    }
  } else {
    if (window.dzsap_syncList_players && window.dzsap_syncList_players.length > 0) {
      player_syncPlayers_gotoItem(selfClass, targetIncrement);
    } else {
      console.log('[dzsap] [syncPlayers] no players found')
    }
  }

  if (window.dzsap_syncList_players && window.dzsap_syncList_index >= window.dzsap_syncList_players.length) {
    window.dzsap_syncList_index = 0;
  }
}
