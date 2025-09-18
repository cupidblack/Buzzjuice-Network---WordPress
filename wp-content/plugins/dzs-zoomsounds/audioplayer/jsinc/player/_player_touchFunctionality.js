export const dzsap_player_touchFunctionalitySetup = (selfClass, seek_to_perc, $) => {
  // -- touch controls
  var scrubbar_moving = false
    , scrubbar_moving_x = 0
    , aux3 = 0
  ;


  if (selfClass._scrubbar && selfClass._scrubbar.get(0)) {

    selfClass._scrubbar.get(0).addEventListener('touchstart', function (e) {
      if (selfClass.isPlayerPlaying) {
        scrubbar_moving = true;
      }
    }, {passive: true})
  }

  $(document).on('touchmove', function (e) {
    if (scrubbar_moving) {
      scrubbar_moving_x = e.originalEvent.touches[0].pageX;


      aux3 = scrubbar_moving_x - selfClass._scrubbar.offset().left;

      if (aux3 < 0) {
        aux3 = 0;
      }
      if (aux3 > selfClass._scrubbar.width()) {
        aux3 = selfClass._scrubbar.width();
      }

      seek_to_perc(aux3 / selfClass._scrubbar.width(), {
        call_from: "touch move"
      });


      return false;

    }
  });

  $(document).on('touchend', function (e) {
    scrubbar_moving = false;
  });
}
