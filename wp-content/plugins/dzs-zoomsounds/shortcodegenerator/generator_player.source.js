'use strict';

import {view_generatorSampleImport_init} from "./js_inc/view_generatorSampleImport";
import {tinymce_add_content} from "./js_shared/view_tinyMceAddContent";
import {view_dzsSelector_init} from "./js_shared/view_dzsSelector";

function htmlEncode(arg) {
  return jQuery('<div/>').text(arg).html();
}

function htmlDecode(value) {
  return jQuery('<div/>').html(arg).text();
}


function get_shortcode_attr(arg, argtext) {


  var regex_aattr = new RegExp(' ' + arg + '="(.*?)"');


  if (arg && arg.indexOf('html') > -1) {
    regex_aattr = new RegExp(' ' + arg + '=\'(.*?)\'');


  }


  var aux = regex_aattr.exec(argtext);

  if (arg == 'cat') {


  }
  if (aux) {

    aux[1] = sanitize_from_shortcode_attr(aux[1]);
    var foutobj = {'full': aux[0], 'val': aux[1]};
    return foutobj;
  }


  return false;
}


function sanitize_from_shortcode_attr(val) {

  if (val) {

    val = val.replace(/{{lsqb}}/g, '[');
    val = val.replace(/{{rsqb}}/g, ']');
  }

  return val;

}

jQuery(document).ready(function ($) {

  var fout = '';

  var media0 = $('.dzsap-feed--media-0').text();
  var mediaIds = $('.dzsap-feed--media-ids').text();


  window.sg1_shortcode = '[zoomsounds_player source="https://digitalzoomstudio.net/links/sample-mp3.php" config="skinwave-with-comments" playerid="' + media0 + '" autoplay="on" cue="on" enable_likes="off" enable_views="off" songname="Track 1 from stephaniequinn.com" artistname="Steph"]';
  window.sg3_shortcode = '[zoomsounds_player source="https://digitalzoomstudio.net/links/sample-mp3.php" config="sample--skin-aria" playerid="' + media0 + '" thumb="" autoplay="on" cue="on" enable_likes="off" enable_views="off" songname="Track 1 from stephaniequinn.com" artistname="Steph"]';
  window.sg2_shortcode = '[dzsap_woo_grid type="attachment" style="style1" faketarget=".dzsap_footer" ids="' + mediaIds + '" ]';


  if (window.dzsap_standard_options) {

  } else {

    window.dzsap_standard_options = [];
  }


  $('.shortcode-field').each(function () {
    var _t = $(this);

    window.dzsap_standard_options.push(_t.attr('name'));
  })


  var shortcodeStartSetup = '';

  if (window.dzsap_startinit) {
    shortcodeStartSetup = window.dzsap_startinit;
  } else {

    if ($('.dzsap-startinit').length) {
      shortcodeStartSetup = $('.dzsap-startinit').eq(0).html();
    }
  }


  if (shortcodeStartSetup) {


    $('.dzsap-admin').append('<div class="misc-initSetup"><h5>Start Setup</h5></h5><p>' + htmlEncode(shortcodeStartSetup) + '</p></div>');


    var res;
    var lab = '';


    for (const key in window.dzsap_standard_options) {


      lab = window.dzsap_standard_options[key];
      res = get_shortcode_attr(lab, shortcodeStartSetup);

      if (res) {
        if (lab == 'id') {
          lab = 'dzsap_selectid';
        }
        if (lab == 'db') {
          lab = 'dzsap_selectdb';
        }
        if (lab == 'cat') {
          var res_arr = String(res['val']).split(',');


          $('*[name="' + lab + '[]"').each(function () {
            var _t2 = $(this);


            for (var ij in res_arr) {


              if (_t2.val() == res_arr[ij]) {
                _t2.prop('checked', true);
                _t2.trigger('change');
              }
            }
            _t2.parent().attr('data-init_categories', res['val']);
          })


        } else {


          if (lab == 'type') {

          }

          const $labForName = $('*[name="' + lab + '"]');
          $labForName.val(res['val']);
          $labForName.trigger('change');
        }
      }
    }
  }

  view_dzsSelector_init()
  $('.submit-shortcode').on('click', click_insert_tests);
  $(document).on('click', '.insert-sample-tracks,.remove-sample-tracks, button.sg-1,.dzs-player-example, button.sg-2, button.sg-3', handle_mouse);

  view_generatorSampleImport_init();
  $('#insert_single_player').on('click', click_insert_single_player);


  var auxselectors = '*[name=extrahtml_in_bottom_controls_from_player],*[name=extrahtml_in_float_right_from_player]';
  $(document).on('change', auxselectors, handle_change);

  setTimeout(function () {
    $(auxselectors).trigger('change');
  }, 1000);


  function handle_change(e) {

    var _t = $(this);

    if (e.type == 'change') {
      if (_t.attr('name') == 'extrahtml_in_bottom_controls_from_player' || _t.attr('name') == 'extrahtml_in_float_right_from_player') {


        var data = {
          action: 'dzsap_parse_content_to_shortcode'
          , postdata: _t.val()
        };


        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: data,
          success: function (response) {


            if (_t.attr('name') == 'extrahtml_in_bottom_controls_from_player') {

              $('.bottom-buttons-area').html(response);
            }

            if (_t.attr('name') == 'extrahtml_in_float_right_from_player') {

              $('.bottom-right-buttons-area').html(response);
            }


          },
          error: function (arg) {
            console.log('Got this from the server: ' + arg, arg);
            ;
          }
        });

        return false;
      }
    }
  }


  function handle_mouse(e) {
    var _t = $(this);

    if (e.type == 'click') {



    }
  }



  function click_insert_tests() {
    prepare_fout_single();
    tinymce_add_content(fout);
    return false;
  }




  function prepare_fout_single() {
    fout = '';


    fout += '[zoomsounds_player';


    for (var i2 in dzsap_standard_options) {


      fout += add_attribute_to_shortcode(dzsap_standard_options[i2]);
    }


    fout += ']';


    if (add_attribute_to_shortcode('content', {attribute_type: 'content'})) {
      fout += add_attribute_to_shortcode('content', {
        attribute_type: 'content'
      });


      fout += '[/zoomsounds_player]'

    }
  }


  function add_attribute_to_shortcode(lab, pargs) {


    var margs = {
      'call_from': 'default'
      , 'attribute_type': 'attr'
    }

    if (pargs) {
      margs = $.extend(margs, pargs);
    }

    var _c = $('*[name=' + lab + ']');
    var _par = null;


    if (_c.parent().hasClass('setting')) {
      _par = _c.parent();

    }
    if (_c.parent().parent().hasClass('setting')) {
      _par = _c.parent().parent();

    }
    if (_c.parent().parent().parent().hasClass('setting')) {
      _par = _c.parent().parent().parent();

    }
    if (_c.parent().parent().parent().parent().hasClass('setting')) {
      _par = _c.parent().parent().parent().parent();

    }


    if (_par) {
      if (_par.css('display') == 'none') {
        return '';
      }
    }


    var fout2 = '';


    var val = _c.val();


    if (val) {

      val = val.replace(/\[/g, '{{lsqb}}');
      val = val.replace(/\]/g, '{{rsqb}}');
    }

    if (margs.attribute_type == 'attr') {

      if (val) {

        if (lab.indexOf('html') > -1 || lab.indexOf('post_content') > -1) {

          fout2 += ' ' + lab + '=\'' + val + '\'';
        } else {

          fout2 += ' ' + lab + '="' + val + '"';
        }
      }
    }
    if (margs.attribute_type == 'content') {


      var ed = null;
      if (window.tinymce) {
        ed = window.tinymce.get('content');


      }


      if (ed) {

        if (ed) {
          fout2 += (ed.getContent({format: 'raw'}));
        }
      } else {

        if (val) {
          fout2 += '' + val + '"';
        }
      }
    }

    return fout2;
  }

  function click_insert_single_player() {

    prepare_fout_single();
    tinymce_add_content(fout);
    return false;
  }

});