import {formatTime} from "../_dzsap_helpers";
import {view_calculateTargetPositionOnScrub} from "../view/player/_view_dimensionHelpers";

export function player_scrubbarSetup(selfClass, $, o, view_drawCurrentTime, viewCalculateScrubbarWidth, play_media, seek_to) {
  const cthis = selfClass.cthis;
  selfClass._scrubbar.on('mousemove', handleMouseOnScrubbar);
  selfClass._scrubbar.on('mouseleave', handleMouseOnScrubbar);
  selfClass._scrubbar.on('click', handleMouseOnScrubbar);


  function handleMouseOnScrubbar(e) {
    var mousex = e.pageX;


    if ($(e.target).hasClass('sample-block-start') || $(e.target).hasClass('sample-block-end')) {
      return false;
    }

    if (e.type === 'mousemove') {
      selfClass._scrubbar.children('.scrubBox-hover').css({
        'left': (mousex - selfClass._scrubbar.offset().left)
      });


      if (o.scrub_show_scrub_time === 'on') {


        if (selfClass.timeModel.getVisualTotalTime()) {
          var aux = (mousex - selfClass._scrubbar.offset().left) / selfClass._scrubbar.outerWidth() * selfClass.timeModel.getVisualTotalTime();


          if (selfClass.$currTime) {
            selfClass.$currTime.html(formatTime(aux));
            selfClass.$currTime.addClass('scrub-time');

          }

          selfClass.isScrubShowingCurrentTime = true;
        }
      }

    }
    if (e.type === 'mouseleave') {

      selfClass.isScrubShowingCurrentTime = false;

      if (selfClass.$currTime) {
        selfClass.$currTime.removeClass('scrub-time');

      }

      view_drawCurrentTime();

    }
    if (e.type === 'click') {


      if (cthis.hasClass('prevent-bubble')) {
        if (e && e.stopPropagation) {
          e.preventDefault();
          e.stopPropagation();
        }
      }

      viewCalculateScrubbarWidth();
      let targetPositionOnScrub = view_calculateTargetPositionOnScrub(selfClass, selfClass.scrubbarWidth, e);

      if (selfClass._actualPlayer) {
        setTimeout(function () {
          if (selfClass._actualPlayer.get(0) && selfClass._actualPlayer.get(0).api_pause_media) {

            selfClass._actualPlayer.get(0).api_seek_to_perc(targetPositionOnScrub / selfClass.timeModel.getVisualTotalTime(), {
              'call_from': 'from_feeder_to_feed'
            });
          }
        }, 50);
      }


      seek_to(targetPositionOnScrub, {
        'call_from': 'handleMouseOnScrubbar'
      });

      if (o.autoplay_on_scrub_click === 'on') {
        if (!selfClass.isPlayerPlaying) {
          play_media({
            'called_from': 'handleMouseOnScrubbar'
          });
        }
      }

      if (cthis.hasClass('from-wc_loop')) {
        return false;
      }
    }

  }

}
