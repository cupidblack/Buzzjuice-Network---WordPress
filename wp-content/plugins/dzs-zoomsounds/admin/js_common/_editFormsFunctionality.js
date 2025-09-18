export function init_dzsapEditFormsFunctionality() {

  var $ = jQuery;
  $(document).on('change.dzsap_global', '.edit_form_line input[name=source], .wrap input[name=source],input[name=playerid]', function () {
    var _t = $(this);


    var isShowNotice = true;


    if (isNaN(Number(_t.val())) && $('input[name=playerid]').eq(0).val() == '') {

    } else {


      isShowNotice = false;


    }

    var _c = $('*[name="dzsap_meta_source_attachment_id"]').eq(0);
    if (isNaN(Number(_c.val())) && _c.val() == '') {

    } else {


      isShowNotice = false;


    }


    _c.trigger('change');


    if (isShowNotice) {

      $('div[data-label="playerid"],*[data-vc-shortcode-param-name="playerid"]').show();
      $('.notice-for-playerid').show();
    } else {

      $('.notice-for-playerid').hide();
    }


  })

}