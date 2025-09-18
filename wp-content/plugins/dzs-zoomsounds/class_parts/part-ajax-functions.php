<?php


if ($_GET['dzsap_action'] == DZSAP_AJAX_DELETE_WAVEFORM) {

  if (DZSZoomSoundsHelper::isTheTrackHasFromCurrentUser($_GET['trackid'])) {

    // -- todo: action
    error_log('delete waveforms', print_r($_GET, true));


    dzsap_delete_waveform($_GET['trackid']);
    dzsap_delete_waveform($_GET['sanitized_source']);
  }
}


if ($_GET['dzsap_action'] == DZSAP_AJAX_DELETE_TRACK) {

  if (DZSZoomSoundsHelper::isTheTrackHasFromCurrentUser($_GET['track_id'])) {
    wp_delete_post($_GET['track_id']);
  }
}



