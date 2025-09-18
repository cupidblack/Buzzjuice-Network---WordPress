import {dzs_clean_string, htmlEncode} from './_dzsap_helpers';
import {decode_json} from '../js_common/_dzs_helpers';
import {DZSAP_AJAX_ACTION_LIKE, DZSAP_CLASS_NAME_AJAX_LIKE} from "../configs/_constants";

export const ajax_submit_views = function (argp) {


  var selfClass = this;
  var $ = jQuery;
  var data = {
    action: 'dzsap_submit_views',
    postdata: 1,
    playerid: selfClass.the_player_id,
    currip: selfClass.currIp,
    playerSource: selfClass.data_source
  };



  if (selfClass.cthis.attr('data-playerid-for-views')) {
    data.playerid = selfClass.cthis.attr('data-playerid-for-views');
  }


  if (data.playerid == '') {
    data.playerid = dzs_clean_string(selfClass.data_source);
  }


  // -- submit view
  if (selfClass.urlToAjaxHandler) {
    $.ajax({
      type: "POST",
      url: selfClass.urlToAjaxHandler,
      data: data,
      success: function (response) {

        // -- increase number of hits
        let auxnr = selfClass.cthis.find('.counter-hits .the-number').html();
        auxnr = parseInt(auxnr, 10);
        if (selfClass.increment_views != 2) {
          auxnr++;
        }
        if (response) {
          if (decode_json(response)) {
            auxnr = decode_json(response)['number'];
          }
        }

        selfClass.cthis.find('.counter-hits .the-number').html(auxnr);

        selfClass.ajax_view_submitted = 'on';
      },
      error: function (arg) {


        var auxlikes = selfClass.cthis.find('.counter-hits .the-number').html();
        auxlikes = parseInt(auxlikes, 10);
        auxlikes++;
        selfClass.cthis.find('.counter-hits .the-number').html(auxlikes);

        selfClass.ajax_view_submitted = 'on';
      }
    });
    selfClass.ajax_view_submitted = 'on';
  }

}

export const ajax_submit_like = function (argp) {
  var selfClass = this;
  var $ = jQuery;

  //only handles ajax call + result
  var mainarg = argp;
  var data = {
    action: DZSAP_AJAX_ACTION_LIKE,
    postdata: mainarg,
    playerid: selfClass.the_player_id,
    playerSource: selfClass.data_source
  };


  selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).addClass('disabled');

  if (selfClass.urlToAjaxHandler || selfClass.cthis.hasClass('is-preview')) {

    $.ajax({
      type: "POST",
      url: selfClass.urlToAjaxHandler,
      data: data,
      success: function (response) {


        selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).addClass('active');
        selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('disabled');
        var auxlikes = selfClass.cthis.find('.counter-likes .the-number').html();
        auxlikes = parseInt(auxlikes, 10);
        auxlikes++;
        selfClass.cthis.find('.counter-likes .the-number').html(auxlikes);
      },
      error: function (arg) {


        selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).addClass('active');
        selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('disabled');
        var auxlikes = selfClass.cthis.find('.counter-likes .the-number').html();
        auxlikes = parseInt(auxlikes, 10);
        auxlikes++;
        selfClass.cthis.find('.counter-likes .the-number').html(auxlikes);
      }
    });
  }
}





export const ajax_submit_total_time = function (selfClass) {

  const isTheNeedToSendTotalTime = () => {

    return !selfClass.cthis.attr('data-sample_time_total') || (selfClass.cthis.attr('data-sample_time_total') && selfClass.timeModel.getActualTotalTime() && selfClass.timeModel.getActualTotalTime() != selfClass.cthis.attr('data-sample_time_total') && !selfClass.cthis.attr('data-sample_time_end'));
  }

  if (!selfClass.isSentCacheTotalTime) {
    if (isTheNeedToSendTotalTime()) {
      if (window.dzsap_settings && window.dzsap_settings.action_received_time_total) {
        selfClass.timeModel.refreshTimes();
        window['dzsap_functions'][window.dzsap_settings.action_received_time_total](selfClass.timeModel.getActualTotalTime(), selfClass.cthis);
      }
    }
    selfClass.isSentCacheTotalTime = true;
  }
}


export const ajax_submit_download = function (argp) {
  //only handles ajax call + result
  var mainarg = argp;
  var selfClass = this;
  var data = {
    action: 'dzsap_submit_download',
    postdata: mainarg,
    playerid: selfClass.the_player_id
  };

  if (selfClass.starrating_alreadyrated > -1) {
    return;
  }

  if (selfClass.urlToAjaxHandler) {

    jQuery.ajax({
      type: "POST",
      url: selfClass.urlToAjaxHandler,
      data: data,
      success: function (response) {


      },
      error: function (arg) {


      }
    });
  }
};

