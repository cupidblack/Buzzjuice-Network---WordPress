import {statistics_view__remove_containers} from "./_view_trackAnalytics";

/**
 *
 * @param {AudioPlayerShowcase} audioPlayerShowcase
 */
export function init_statsBtn(audioPlayerShowcase) {

  var $ = jQuery;


  $(document).on('click', '.stats-btn', handle_mouse);


  function handle_mouse(e) {

    var $t = jQuery(this);

    if($t.hasClass('stats-btn')){


      var _con = $t.parent();

      if ($t.hasClass('disabled')) {
        return false;
      }
      $t.addClass('disabled');
      setTimeout(function () {
        $t.removeClass('disabled')
      }, 2000)

      if (_con.find('.stats-container').length) {
        statistics_view__remove_containers(_con);
      } else {

        $t.addClass('active');
        audioPlayerShowcase.load_statistics(_con);
      }
    }
    }
}