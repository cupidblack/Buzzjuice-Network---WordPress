'use strict';

export function dzsap_getThumbInit() {
  var $ = jQuery;
  $(document).on('change.dzsap_get_thumb', '*[name="dzsap_meta_source_attachment_id"]', function () {


    var _t = $(this);


    var _con = null;

    if (_t.parent().parent().parent().parent().parent().hasClass('dzstooltip--content')) {
      _con = _t.parent().parent().parent().parent().parent();
    }


    if (_con) {
      var _c = _con.find('*[name="dzsap_meta_item_thumb"]');
      if (_c) {


        if (_c.val() == '') {

          var data = {
            action: 'dzsap_get_thumb_from_meta'
            , postdata: _t.val()
          };


          var _mainThumb = _c;


          jQuery.ajax({
            type: "POST",
            url: window.ajaxurl,
            data: data,
            success: function (response) {


              if (response.indexOf('image data - ') == 0) {


                response = response.replace('image data - ', '');


                if (response) {


                  if (_mainThumb.val() == '' && _mainThumb.val() != 'none') {
                    _mainThumb.val('data:image/jpeg;base64,' + response);
                    _mainThumb.trigger('change');
                  }
                }

              } else {


                if (_mainThumb.val() == '' && _mainThumb.val() != 'none') {
                  _mainThumb.val(response);
                  _mainThumb.trigger('change');
                }
              }

            },
            error: function (arg) {
              console.log('got error: ' + arg);
              ;
            }
          });
        }
      }
    }

  })
}