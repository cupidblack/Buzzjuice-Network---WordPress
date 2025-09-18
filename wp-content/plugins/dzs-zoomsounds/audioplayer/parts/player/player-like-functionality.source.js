import {$es, documentReady} from '../../js_common/_esjquery';
import {DZSAP_AJAX_ACTION_LIKE, DZSAP_CLASS_NAME_AJAX_LIKE} from "../../configs/_constants";
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

window.dzsap_retract_like = function (argp, e) {

  var mainarg = argp;
  var data = {
    action: 'dzsap_retract_like',
    playerid: argp
  };

  var _t = null;

  if (e) {
    _t = jQuery(e.currentTarget);
  }


  if (window.dzsap_settings && window.dzsap_settings.ajax_url) {

    jQuery.ajax({
      type: "POST",
      url: window.dzsap_settings.ajax_url,
      data: data,
      success: function (response) {


        if (_t) {
          var htmlaux = _t.html();

          _t.html(htmlaux.replace('fa-heart', 'fa-heart-o'));
        }

      },
      error: function (arg) {

      }
    });
  }
}


const ajax_retract_like = function (argp) {
  var selfClass = this;
  var $ = jQuery;

  //only handles ajax call + result
  var data = {
    action: 'dzsap_retract_like',
    postdata: argp,
    playerid: selfClass.the_player_id
  };

  selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).addClass('disabled');


  if (selfClass.urlToAjaxHandler) {
    $.ajax({
      type: "POST",
      url: selfClass.urlToAjaxHandler,
      data: data,
      success: function (response) {

        selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('active');
        selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('disabled');
        var auxlikes = selfClass.cthis.find('.counter-likes .the-number').html();
        auxlikes = parseInt(auxlikes, 10);
        auxlikes--;
        selfClass.cthis.find('.counter-likes .the-number').html(auxlikes);
      },
      error: function (arg) {

        selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('active');
        selfClass.cthis.find(`.${DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('disabled');
        var auxlikes = selfClass.cthis.find('.counter-likes .the-number').html();
        auxlikes = parseInt(auxlikes, 10);
        auxlikes--;
        selfClass.cthis.find('.counter-likes .the-number').html(auxlikes);
      }
    });
  }
}


function initPlayersForLike(){

  $es('.audioplayer:not(.ap-like-btn-inited)').each(($arg)=>{
    $arg.addClass('ap-like-btn-inited');

    /** {DzsAudioPlayer} */
    const $selfInstance = $arg.$el.SelfInstance;


    $selfInstance.cthis.off('click', `.${DZSAP_CLASS_NAME_AJAX_LIKE}`);
    $selfInstance.cthis.on('click', `.${DZSAP_CLASS_NAME_AJAX_LIKE}`, handleClickLike);

    function handleClickLike() {
      var _t = jQuery(this);
      if ($selfInstance.cthis.has(_t).length === 0) {
        return;
      }


      if (_t.hasClass('active')) {
        (ajax_retract_like.bind($selfInstance))();
      } else {
        (ajax_submit_like.bind($selfInstance))();
      }
    }

  })

}

window.dzsap_submit_like = function (argp, e) {

  var mainarg = argp;
  var data = {
    action: DZSAP_AJAX_ACTION_LIKE,
    playerid: argp
  };

  var _t = null;

  if (e) {
    _t = jQuery(e.currentTarget);
  }


  if (window.dzsap_settings && window.dzsap_settings.ajax_url) {

    jQuery.ajax({
      type: "POST",
      url: window.dzsap_settings.ajax_url,
      data: data,
      success: function (response) {


        if (_t) {

          var htmlaux = _t.html();

          _t.html(htmlaux.replace('fa-heart-o', 'fa-heart'));
        }

      },
      error: function (arg) {

      }
    });
  }
}


const DELAY_WAIT_FOR_AP_INIT = 500;

documentReady(()=>{
  setTimeout(()=>{

    const $ = jQuery;

    $(document).on('click', '.dzsap-like-but', function (e) {

      var _t = $(this);


      var playerid = _t.attr('data-post_id');

      if (playerid && playerid != '0') {

      } else {
        if (_t.parent().parent().parent().parent().parent().hasClass('audioplayer')) {

          playerid = _t.parent().parent().parent().parent().parent().attr('data-feed-playerid');
        }
      }
      window.dzsap_submit_like(playerid, e);

      _t.removeClass('dzsap-like-but').addClass('dzsap-retract-like-but');

      return false;
    })


    $(document).on('click', '.dzsap-retract-like-but', function (e) {

      var _t = $(this);
      var playerid = _t.attr('data-post_id');

      if (playerid && playerid != '0') {

      } else {
        if (_t.parent().parent().parent().parent().parent().hasClass('audioplayer')) {

          playerid = _t.parent().parent().parent().parent().parent().attr('data-feed-playerid');
        }
      }


      window.dzsap_retract_like(playerid, e);
      _t.addClass('dzsap-like-but').removeClass('dzsap-retract-like-but');
      return false;
    })

    initPlayersForLike();
  }, DELAY_WAIT_FOR_AP_INIT)

})
