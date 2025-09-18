
function dzsap_setShortcodeAttribute(args) {
  var $ = jQuery;
  for (var lab in args) {
    var valarg = args[lab];


    $('*[name="' + lab + '"]').val(valarg).trigger('change');
  }
}

export const view_generatorSampleImport_init = () => {

  const $ = jQuery;
  $(document).on('click', '.insert-sample-tracks,.remove-sample-tracks, button.sg-1,.dzs-player-example, button.sg-2, button.sg-3', handle_mouse);



  function import_sample(arg) {


    var $ = jQuery;
    if (arg && arg.getAttribute('data-sample-name')) {


      var theName = arg.getAttribute('data-sample-name');
      postAjax(dzsap_settings.siteurl + '?dzsap_action=dzsap_import_vp_config', 'name=' + theName, (arg) => {


        dzsap_setShortcodeAttribute()


        $('select[name="config"]').append($('<option>', {
          value: theName,
          text: theName
        }));


        dzsap_setShortcodeAttribute({source: 'https://zoomthe.me/tests/sound-electric.mp3'});
        dzsap_setShortcodeAttribute({config: theName});
        dzsap_setShortcodeAttribute({artistname: theName});


        if (theName === 'sample--boxed-inside') {
          dzsap_setShortcodeAttribute({wrapper_image_type: "zoomsounds-wrapper-bg-bellow"});
          dzsap_setShortcodeAttribute({wrapper_image: "https://zoomthe.me/tests/bg_blur.jpg"});
        }


        setTimeout(function () {

          $('.submit-shortcode').trigger('click');
        }, 500);
      });
    }
  }


  function handle_mouse(e) {
    var _t = $(this);
    let fout = '';

    if (e.type == 'click') {
      if (_t.hasClass('dzs-player-example')) {
        import_sample(_t.get(0));
      }
      if (_t.hasClass('insert-sample-tracks')) {


        let data = {
          action: 'ajax_dzsap_insert_sample_tracks'
        };


        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: data,
          success: function (response) {
            window.location.reload();

          },
          error: function (arg) {
            console.log('Got this from the server: ' + arg, arg);

            ;
          }
        });

        return false;
      }
      if (_t.hasClass('remove-sample-tracks')) {


        let data = {
          action: 'ajax_dzsap_remove_sample_tracks'
        };


        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: data,
          success: function (response) {
            window.location.reload();

          },
          error: function (arg) {
            console.log('Got this from the server: ' + arg, arg);
            ;
          }
        });

        return false;
      }


      if (_t.hasClass('sg-1')) {


        fout = window.sg1_shortcode;

        tinymce_add_content(fout);

      }
      if (_t.hasClass('sg-3')) {


        fout = window.sg3_shortcode;

        tinymce_add_content(fout);

      }


      if (_t.hasClass('sg-2')) {


        fout = window.sg2_shortcode;

        if (parent.dzsap_prepare_footer_player) {
          parent.dzsap_prepare_footer_player();
        }

        tinymce_add_content(fout);

      }
    }
  }

}



function postAjax(url, data, success) {
  var params = typeof data == 'string' ? data : Object.keys(data).map(
    function (k) {
      return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
    }
  ).join('&');

  var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
  xhr.open('POST', url);
  xhr.onreadystatechange = function () {
    if (xhr.readyState > 3 && xhr.status == 200) {
      success(xhr.responseText);
    }
  };
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.send(params);
  return xhr;
}


;