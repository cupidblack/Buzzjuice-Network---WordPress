import {_vpconfigsPreviewFunctionality} from "./_vpconfigs--previewFunctionality";

/**
 * in admin global
 */
export const vpconfigs_init = () => {

  var $ = jQuery;




  $(document).on('change', 'select.vpconfig-select', change_vpconfig);

  _vpconfigsPreviewFunctionality();

  function change_vpconfig() {
    var _t = $(this);

    var _con = null;


    if (_t.parent().hasClass('vpconfig-wrapper')) {

      _con = _t.parent();
    }
    if (_t.parent().parent().hasClass('vpconfig-wrapper')) {

      _con = _t.parent().parent();
    }


    if (_con) {

      var selopt = _t.children(':selected');

    }

  }








}