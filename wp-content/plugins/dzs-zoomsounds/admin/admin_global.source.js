'use strict';
import * as dzsap_admin_helpers from "./js_common/_helper_admin";
import {setup_autoClick} from "./js_common/_helper_admin";
import {nag_intro_tooltip} from './js_common/_nag_intro_tooltip';
import {systemCheck_waves_check} from "./jsinc/_systemCheck_waves_check";
import {init_dzsDependencyFunctions} from "./js_common/_dependency-functionality";
import {init_query_arg_globals} from "./js_common/_query_arg_func";
import {vpconfigs_init} from "./jsinc/_vpconfigs";
import {wave_regenerate_init} from "./jsinc/_wave_regenerate";
import {feedbacker_init} from "./jsinc/_feedbacker";
import {mainoptions_init} from "./jsinc/_mainoptions";
import {dzsap_getThumbInit} from './admin-page/_getThumb';
import {dzsap_initWooCommercePages} from './admin-page/_woocommerce-pages';
import {init_dzsColorPickers} from "./js_common/_dzs_colorPickers";
import {init_dzsapEditFormsFunctionality} from "./js_common/_editFormsFunctionality";
import {dzsap_initDzsaApPlayerPreview} from "./admin-page/_vpconfigs_dzsapPlayerPreview";

window.waves_fieldtaget = null;
window.waves_filename = null;

window.inter_dzs_check_dependency_settings = 0;

 
init_dzsDependencyFunctions();
init_query_arg_globals();

jQuery(document).ready(function ($) {

  const currentAdminPage = get_query_arg(window.location.href, 'page');
  const _wrap = $('.wrap').eq(0);
  const main_settings = window.dzsap_settings;


  // -- Create the media frame.

  dzsap_admin_helpers.adminPageWaveformChecker_init()


  vpconfigs_init();

  mainoptions_init();


  feedbacker_init();


  dzsap_admin_helpers.addGutenbergButtons();
  dzsap_admin_helpers.addUploaderButtons();

  if (currentAdminPage === 'dzsap-system-check-waves' || currentAdminPage === 'dzsap-mo') {
    wave_regenerate_init();
  }

  if (currentAdminPage === 'dzsap-system-check-waves' || currentAdminPage === 'dzsap-mo') {
    systemCheck_waves_check();
  }
  setup_autoClick()


  nag_intro_tooltip({...main_settings, prefix: 'dzsap'});


  window.dzs_dependency_on_document_ready();
  dzsap_initDzsaApPlayerPreview()


  dzsap_getThumbInit();

  init_dzsapEditFormsFunctionality()
  $('input[name=source]').trigger('change');
  setTimeout(function () {

    $('input[name=source]').trigger('change');
  }, 1000);


  init_dzsColorPickers();


  dzsap_initWooCommercePages()


  if (_wrap.hasClass('wrap-for-generator-player')) {
  }
});

dzsap_admin_helpers.reskin_select_setup();



