import {ajax_submit_download} from "../_dzsap_ajax";


/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param {jQuery} $
 * @param {function} seek_to
 */
export const player_setupMiscButtons = (selfClass, $, seek_to) => {
  var cthis = selfClass.cthis;

  cthis.on('click', '.dzsap-repeat-button,.btn-zoomsounds-download,.zoomsounds-btn-step-backward,.zoomsounds-btn-step-forward,.zoomsounds-btn-go-beginning,.zoomsounds-btn-slow-playback,.zoomsounds-btn-reset, .tooltip-indicator--btn-footer-playlist, .wave-download', handle_mouse);
  cthis.find('.wave-download').on('click', handle_mouse);



  cthis.on('click', '.skip-15-sec', function () {
    cthis.get(0).api_step_forward(15);
  });

  function handle_mouse(e) {
    let _target;
    const $t = $(this);

    // if ($t.hasClass('wave-download')) {
    //   (ajax_submit_download.bind(selfClass))();
    // }
    if ($t.hasClass('tooltip-indicator--btn-footer-playlist')) {

      $t.parent().find('.dzstooltip').toggleClass('active');
    }
    if ($t.hasClass('zoomsounds-btn-go-beginning')) {

      _target = cthis;
      if (selfClass._actualPlayer) {
        _target = selfClass._actualPlayer;
      }

      _target.get(0).api_seek_to(0);
    }
    if ($t.hasClass('zoomsounds-btn-step-backward')) {

      _target = cthis;
      if (selfClass._actualPlayer) {
        _target = selfClass._actualPlayer;
      }

      _target.get(0).api_step_back();
    }
    if ($t.hasClass('zoomsounds-btn-step-forward')) {

      _target = cthis;
      if (selfClass._actualPlayer) {
        _target = selfClass._actualPlayer;
      }

      _target.get(0).api_step_forward();
    }
    if ($t.hasClass('zoomsounds-btn-slow-playback')) {
      _target = cthis;
      if (selfClass._actualPlayer) {
        _target = selfClass._actualPlayer;
      }

      _target.get(0).api_playback_slow();
    }
    if ($t.hasClass('zoomsounds-btn-reset')) {
      _target = cthis;
      if (selfClass._actualPlayer) {
        _target = selfClass._actualPlayer;
      }

      _target.get(0).api_playback_reset();
    }
    if ($t.hasClass('btn-zoomsounds-download')) {
      (ajax_submit_download.bind(selfClass))();
    }
    if ($t.hasClass('dzsap-repeat-button')) {

      if (selfClass.isPlayerPlaying) {
      }
      seek_to(0, {
        call_from: "repeat"
      });
    }


  }

}
