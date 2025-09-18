(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";function dzsap_getThumbInit(){var a=jQuery;a(document).on("change.dzsap_get_thumb",'*[name="dzsap_meta_source_attachment_id"]',function(){var e=a(this),t=null;if(e.parent().parent().parent().parent().parent().hasClass("dzstooltip--content")&&(t=e.parent().parent().parent().parent().parent()),t){var n=t.find('*[name="dzsap_meta_item_thumb"]');if(n&&""==n.val()){var r={action:"dzsap_get_thumb_from_meta",postdata:e.val()},o=n;jQuery.ajax({type:"POST",url:window.ajaxurl,data:r,success:function(a){0==a.indexOf("image data - ")?(a=a.replace("image data - ",""))&&""==o.val()&&"none"!=o.val()&&(o.val("data:image/jpeg;base64,"+a),o.trigger("change")):""==o.val()&&"none"!=o.val()&&(o.val(a),o.trigger("change"))},error:function(a){console.log("got error: "+a)}})}}})}Object.defineProperty(exports,"__esModule",{value:!0}),exports.dzsap_getThumbInit=dzsap_getThumbInit;
},{}],2:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.dzsap_initDzsaApPlayerPreview = dzsap_initDzsaApPlayerPreview;

function dzsap_initDzsaApPlayerPreview() {
  if (get_query_arg(window.location.href, 'dzsap_preview_player')) {
    setTimeout(() => {// /**
      //  *
      //  * @param {HTMLElement} canvas
      //  */
      // function renderCanvas(canvas) {
      //   canvas.classList.add('zoomsounds-canvas-for-preview')
      //   document.body.prepend(canvas);
      // }
      // window.html2canvas(document.querySelector(".wrap-for-player-preview")).then(renderCanvas);
    }, 3000);
  }
}

},{}],3:[function(require,module,exports){
"use strict";function dzsap_initWooCommercePages(){function a(a){const t=e(this);if("click"===a.type&&t.hasClass("btn-dzsap-create-playlist-for-woo")){var s="zoomsounds-product-playlist-"+t.attr("data-playerid"),o={action:"dzsap_create_playlist",term_name:s};return t.attr("disabled",!0),t.prop("disabled",!0),t.addClass("playlist-opened"),e.ajax({type:"POST",url:window.ajaxurl,data:o,success:function(a){a&&(e('input[name="dzsap_woo_product_track"]').val(s),t.parent().parent().parent().after('<iframe class="dzsap-woo-playlist-iframe" src="'+window.dzsap_settings.admin_url+"term.php?taxonomy=dzsap_sliders&tag_ID="+a+'&post_type=dzsap_items&dzs_css=remove_wp_menu" width="100%" height="400"></iframe>'))},error:function(a){console.log("got error: "+a,a)}}),!1}}const e=jQuery;e(document).on("click"," .btn-dzsap-create-playlist-for-woo",a)}Object.defineProperty(exports,"__esModule",{value:!0}),exports.dzsap_initWooCommercePages=dzsap_initWooCommercePages;
},{}],4:[function(require,module,exports){
'use strict';

var dzsap_admin_helpers = _interopRequireWildcard(require("./js_common/_helper_admin"));

var _nag_intro_tooltip = require("./js_common/_nag_intro_tooltip");

var _systemCheck_waves_check = require("./jsinc/_systemCheck_waves_check");

var _dependencyFunctionality = require("./js_common/_dependency-functionality");

var _query_arg_func = require("./js_common/_query_arg_func");

var _vpconfigs = require("./jsinc/_vpconfigs");

var _wave_regenerate = require("./jsinc/_wave_regenerate");

var _feedbacker = require("./jsinc/_feedbacker");

var _mainoptions = require("./jsinc/_mainoptions");

var _getThumb = require("./admin-page/_getThumb");

var _woocommercePages = require("./admin-page/_woocommerce-pages");

var _dzs_colorPickers = require("./js_common/_dzs_colorPickers");

var _editFormsFunctionality = require("./js_common/_editFormsFunctionality");

var _vpconfigs_dzsapPlayerPreview = require("./admin-page/_vpconfigs_dzsapPlayerPreview");

function _getRequireWildcardCache() {
  if (typeof WeakMap !== "function") return null;
  var cache = new WeakMap();

  _getRequireWildcardCache = function () {
    return cache;
  };

  return cache;
}

function _interopRequireWildcard(obj) {
  if (obj && obj.__esModule) {
    return obj;
  }

  if (obj === null || typeof obj !== "object" && typeof obj !== "function") {
    return {
      default: obj
    };
  }

  var cache = _getRequireWildcardCache();

  if (cache && cache.has(obj)) {
    return cache.get(obj);
  }

  var newObj = {};
  var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor;

  for (var key in obj) {
    if (Object.prototype.hasOwnProperty.call(obj, key)) {
      var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null;

      if (desc && (desc.get || desc.set)) {
        Object.defineProperty(newObj, key, desc);
      } else {
        newObj[key] = obj[key];
      }
    }
  }

  newObj.default = obj;

  if (cache) {
    cache.set(obj, newObj);
  }

  return newObj;
}

window.waves_fieldtaget = null;
window.waves_filename = null;
window.inter_dzs_check_dependency_settings = 0;
(0, _dependencyFunctionality.init_dzsDependencyFunctions)();
(0, _query_arg_func.init_query_arg_globals)();
jQuery(document).ready(function ($) {
  const currentAdminPage = get_query_arg(window.location.href, 'page');

  const _wrap = $('.wrap').eq(0);

  const main_settings = window.dzsap_settings; // -- Create the media frame.

  dzsap_admin_helpers.adminPageWaveformChecker_init();
  (0, _vpconfigs.vpconfigs_init)();
  (0, _mainoptions.mainoptions_init)();
  (0, _feedbacker.feedbacker_init)();
  dzsap_admin_helpers.addGutenbergButtons();
  dzsap_admin_helpers.addUploaderButtons();

  if (currentAdminPage === 'dzsap-system-check-waves' || currentAdminPage === 'dzsap-mo') {
    (0, _wave_regenerate.wave_regenerate_init)();
  }

  if (currentAdminPage === 'dzsap-system-check-waves' || currentAdminPage === 'dzsap-mo') {
    (0, _systemCheck_waves_check.systemCheck_waves_check)();
  }

  (0, dzsap_admin_helpers.setup_autoClick)();
  (0, _nag_intro_tooltip.nag_intro_tooltip)({ ...main_settings,
    prefix: 'dzsap'
  });
  window.dzs_dependency_on_document_ready();
  (0, _vpconfigs_dzsapPlayerPreview.dzsap_initDzsaApPlayerPreview)();
  (0, _getThumb.dzsap_getThumbInit)();
  (0, _editFormsFunctionality.init_dzsapEditFormsFunctionality)();
  $('input[name=source]').trigger('change');
  setTimeout(function () {
    $('input[name=source]').trigger('change');
  }, 1000);
  (0, _dzs_colorPickers.init_dzsColorPickers)();
  (0, _woocommercePages.dzsap_initWooCommercePages)();

  if (_wrap.hasClass('wrap-for-generator-player')) {}
});
dzsap_admin_helpers.reskin_select_setup();

},{"./admin-page/_getThumb":1,"./admin-page/_vpconfigs_dzsapPlayerPreview":2,"./admin-page/_woocommerce-pages":3,"./js_common/_dependency-functionality":5,"./js_common/_dzs_colorPickers":6,"./js_common/_editFormsFunctionality":7,"./js_common/_helper_admin":8,"./js_common/_nag_intro_tooltip":9,"./js_common/_query_arg_func":10,"./jsinc/_feedbacker":12,"./jsinc/_mainoptions":13,"./jsinc/_systemCheck_waves_check":14,"./jsinc/_vpconfigs":16,"./jsinc/_wave_regenerate":17}],5:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.init_dzsDependencyFunctions = init_dzsDependencyFunctions;

function init_dzsDependencyFunctions() {
  window.dzs_check_dependency_settings = function (pargs) {
    // -- this checks for all dependencies .. lets make a timer
    if (window.inter_dzs_check_dependency_settings) {
      clearTimeout(window.inter_dzs_check_dependency_settings);
    }

    window.inter_dzs_check_dependency_settings = setTimeout(function () {
      dzs_check_dependency_settings_real(pargs);
    }, 100);
  };

  window.dzs_checkDependency = window.dzs_check_dependency_settings;

  window.dzs_check_dependency_settings_real = function (pargs) {
    var margs = {
      target_attribute: 'name'
    };
    var $ = jQuery;
    $('*[data-dependency]').each(function () {
      var $targetSetting = $(this);
      var dependencyObject = {};
      var dependencySerialized = $targetSetting.attr('data-dependency');

      if (dependencySerialized.indexOf('"') == 0) {
        dependencySerialized = dependencySerialized.substr(1, dependencySerialized.length);
        dependencySerialized = dependencySerialized.substr(0, dependencySerialized.length - 1);
      }

      dependencySerialized = dependencySerialized.replace(/{quotquot}/g, '"');

      try {
        dependencyObject = JSON.parse(dependencySerialized);

        if (dependencyObject[0]) {
          var $sourceDependency = null;
          var target_attribute = margs.target_attribute;
          var $targetContainer = $(document);

          if ($targetSetting.hasClass('check-label')) {
            target_attribute = 'data-label';
          }

          if ($targetSetting.hasClass('check-parent1')) {
            $targetContainer = $targetSetting.parent();
          }

          if ($targetSetting.hasClass('check-parent2')) {
            $targetContainer = $targetSetting.parent().parent();
          }

          if ($targetSetting.hasClass('check-parent3')) {
            $targetContainer = $targetSetting.parent().parent().parent();
          }

          if (dependencyObject[0].lab) {
            $sourceDependency = $targetContainer.find('*[' + target_attribute + '="' + dependencyObject[0].lab + '"]:not(.fake-input)').eq(0);

            if ($sourceDependency.length == 0 && dependencyObject[0].lab == 'name') {
              $sourceDependency = $targetContainer.find('*[name="0-settings-' + dependencyObject[0].lab + '"]:not(.fake-input)').eq(0);
            }
          }

          if (dependencyObject[0].label) {
            $sourceDependency = $targetContainer.find('*[' + target_attribute + '="' + dependencyObject[0].label + '"]:not(.fake-input)').eq(0);

            if ($sourceDependency.length == 0 && dependencyObject[0].label == 'name') {
              $sourceDependency = $targetContainer.find('*[name="0-settings-' + dependencyObject[0].label + '"]:not(.fake-input)').eq(0);
            }
          }

          if (dependencyObject[0].element) {
            // -- if it's player generator there is no dzsap_meta_
            if ($('body').hasClass('zoomsounds_page_dzsap-mo')) {
              dependencyObject[0].element = String(dependencyObject[0].element).replace('dzsap_meta_', '');
            }

            if ($targetSetting.attr('data-option-name') === 'dzsap_meta_download_custom_link') {}

            $sourceDependency = $targetContainer.find('*[' + target_attribute + '="' + dependencyObject[0].element + '"]:not(.fake-input)').eq(0);
          }

          if (dependencyObject[0].element && dependencyObject[0].element == 'dzsap_meta_download_custom_link_enable') {}

          var cval = $sourceDependency.val();

          if ($sourceDependency.attr('type') == 'checkbox') {
            if ($sourceDependency.prop('checked')) {} else {
              cval = '';
            }
          }

          let isShowing = false;

          if (dependencyObject[0].val) {
            for (let i3 in dependencyObject[0].val) {
              if (cval == dependencyObject[0].val[i3]) {
                isShowing = true;
                break;
              }
            }
          }

          if (dependencyObject.relation) {
            for (var depObjKey in dependencyObject) {
              if (depObjKey == 'relation') {
                continue;
              }

              if (dependencyObject[depObjKey].value) {
                if (dependencyObject.relation == 'AND') {
                  isShowing = false;
                }

                if (dependencyObject[0].element) {
                  $sourceDependency = $targetContainer.find('*[' + target_attribute + '="' + dependencyObject[depObjKey].element + '"]:not(.fake-input)').eq(0);
                }

                for (let dependencyKey in dependencyObject[depObjKey].value) {
                  if ($targetSetting.attr('data-option-name') === 'dzsap_meta_wrapper_image_type') {}

                  if ($sourceDependency.val() == dependencyObject[depObjKey].value[dependencyKey]) {
                    if ($sourceDependency.attr('type') == 'checkbox') {
                      if ($sourceDependency.val() == dependencyObject[depObjKey].value[dependencyKey] && $sourceDependency.prop('checked')) {
                        isShowing = true;
                      }
                    } else {
                      isShowing = true;
                    }

                    break;
                  }

                  if (dependencyObject[depObjKey].value[dependencyKey] == 'anything_but_blank' && cval) {
                    isShowing = true;
                    break;
                  }
                }
              }
            }
          } else {
            if (dependencyObject[0].value) {
              for (var i3 in dependencyObject[0].value) {
                if ($sourceDependency.val() == dependencyObject[0].value[i3]) {
                  if ($sourceDependency.attr('type') == 'checkbox') {
                    if ($sourceDependency.val() == dependencyObject[0].value[i3] && $sourceDependency.prop('checked')) {
                      isShowing = true;
                    }
                  } else {
                    isShowing = true;
                  }

                  break;
                }

                if (dependencyObject[0].value[i3] == 'anything_but_blank' && cval) {
                  isShowing = true;
                  break;
                }
              }
            }
          }

          if ($targetSetting.attr('data-option-name') === 'dzsap_meta_wrapper_image_type') {}

          if (isShowing) {
            $targetSetting.show();
          } else {
            $targetSetting.hide();
          }
        }
      } catch (err) {
        console.info('cannot parse depedency json', "'", dependencySerialized, "'", err, $targetSetting);
      }
    });
  };

  window.dzs_handle_submit_dependency_field = function (e) {
    var _t = jQuery(this);

    if (e.type == 'change') {
      if (_t.hasClass('dzs-dependency-field')) {
        dzs_check_dependency_settings();
      }
    }
  };

  window.dzs_dependency_on_document_ready = function () {
    if (window.dzsdepe_isInited) {
      return;
    }

    window.dzsdepe_isInited = true;
    var $ = jQuery;
    $(document).off('change.dzsdepe', '.dzs-dependency-field', dzs_handle_submit_dependency_field);
    $(document).on('change.dzsdepe', '.dzs-dependency-field', dzs_handle_submit_dependency_field);
    setTimeout(function () {
      $('.dzs-dependency-field').trigger('change');
    }, 800);
  };
}

},{}],6:[function(require,module,exports){
"use strict";function init_dzsColorPickers(){var o=jQuery;o(".with_colorpicker").each(function(){var t=o(this);t.hasClass("treated")||(o.fn.farbtastic&&t.next().find(".picker").farbtastic(t),t.addClass("treated"),t.bind("change",function(){jQuery("#customstyle_body").html("body{ background-color:"+o("input[name=color_bg]").val()+"} .dzsportfolio, .dzsportfolio a{ color:"+o("input[name=color_main]").val()+"} .dzsportfolio .portitem:hover .the-title, .dzsportfolio .selector-con .categories .a-category.active { color:"+o("input[name=color_high]").val()+" }")}),t.trigger("change"),t.bind("click",function(){t.next().hasClass("picker-con")&&t.next().find(".the-icon").eq(0).trigger("click")}))})}Object.defineProperty(exports,"__esModule",{value:!0}),exports.init_dzsColorPickers=init_dzsColorPickers;
},{}],7:[function(require,module,exports){
"use strict";function init_dzsapEditFormsFunctionality(){var a=jQuery;a(document).on("change.dzsap_global",".edit_form_line input[name=source], .wrap input[name=source],input[name=playerid]",function(){var e=a(this),i=!0;isNaN(Number(e.val()))&&""==a("input[name=playerid]").eq(0).val()||(i=!1);var t=a('*[name="dzsap_meta_source_attachment_id"]').eq(0);isNaN(Number(t.val()))&&""==t.val()||(i=!1),t.trigger("change"),i?(a('div[data-label="playerid"],*[data-vc-shortcode-param-name="playerid"]').show(),a(".notice-for-playerid").show()):a(".notice-for-playerid").hide()})}Object.defineProperty(exports,"__esModule",{value:!0}),exports.init_dzsapEditFormsFunctionality=init_dzsapEditFormsFunctionality;
},{}],8:[function(require,module,exports){
"use strict";function setup_autoClick(){var e=jQuery;e(".dzs-auto-click-after-1000").each(function(){var t=e(this);setTimeout(function(){t.trigger("click")},1e3)})}function addUploaderButtons(){var e=jQuery;e(document).off("click.dzswup",".dzs-wordpress-uploader"),e(document).on("click.dzswup",".dzs-wordpress-uploader",function(t){var a=e(this),r=a.prev();a.parent().hasClass("upload-for-target-con")?r=a.parent().find("input").eq(0):a.parent().parent().parent().hasClass("upload-for-target-con")&&(r=a.parent().parent().parent().find("input").eq(0));var n="";r.hasClass("upload-type-audio")&&(n="audio"),r.hasClass("upload-type-video")&&(n="video"),r.hasClass("upload-type-image")&&(n="image");var s=wp.media.frames.dzsp_addimage=wp.media({title:"Insert Media",library:{type:n},button:{text:"Insert Media",close:!0}});return s.on("select",function(){var e=s.state().get("selection").first(),t=e&&e.attributes&&e.attributes.filename?e.attributes.filename:"",n=e.attributes?e.attributes.id:0,i=e.attributes.url;a.hasClass("insert-id")&&(i=e.attributes.id),r.val(i),r.trigger("change"),t&&t.length>5&&(t.indexOf(".mp3")>t.length-5||t.indexOf(".wav")>t.length-5||t.indexOf(".m4a")>t.length-5)&&window.dzsap_waveform_autogenerateWithId(n);var o=null;r.parent().parent().hasClass("tab-content")&&(o=r.parent().parent()),"dzsap_meta_item_source"==r.attr("name")&&o&&setTimeout(function(e){if(e.attributes.title){var t="the_post_title";""==o.find('*[name="'+t+'"]').eq(0).val()&&o.find('*[name="'+t+'"]').eq(0).val(e.attributes.title),setTimeout(function(e){e.trigger("change")},500,o.find('*[name="'+t+'"]').eq(0))}if(e.attributes.artist){var t="artistname";""==o.find('*[name="'+t+'"]').eq(0).val()&&o.find('*[name="'+t+'"]').eq(0).val(e.attributes.artist),setTimeout(function(e){e.trigger("change")},500,o.find('*[name="'+t+'"]').eq(0))}},500,e),(r.attr("name").indexOf("item_source")>-1||"source"==r.attr("name"))&&r.parent().find('*[name="dzsap_meta_source_attachment_id"]').eq(0).val(e.attributes.id)}),s.open(),t.stopPropagation(),t.preventDefault(),!1}),e(document).off("click",".dzs-btn-add-media-att"),e(document).on("click",".dzs-btn-add-media-att",function(){var t=e(this),a={title:"Add Item",button:{text:"Select"},multiple:!1};t.attr("data-library_type")&&(a.library={type:t.attr("data-library_type")});var r=wp.media.frames.downloadable_file=wp.media(a);return r.on("select",function(){var e=r.state().get("selection");e=e.toJSON();var a=0;for(a=0;a<e.length;a++){var n=e[a];void 0!=n.id&&(t.hasClass("button-setting-input-url")?t.parent().parent().find("input").eq(0).val(n.url):t.parent().parent().find("input").eq(0).val(n.id),t.parent().parent().find("input").eq(0).trigger("change"))}}),r.open(),!1}),(0,_uploaderPreview.dzsUploaderTargetInit)()}function addGutenbergButtons(){return setInterval(function(){if(window.tinyMCE)for(var e in window.tinyMCE.editors){var t=window.tinyMCE.editors[e],a=t.$();a.hasClass("wp-block-freeform")&&(0===a.parent().parent().parent().find(".wp-content-media-buttons").length&&a.parent().parent().parent().find(".block-library-classic__toolbar").append('<div class="wp-content-media-buttons"></div>'),window.dzsap_add_media_buttons())}},2e3)}function reskin_select_setup(){reskin_select(),setTimeout(reskin_select,10),setTimeout(function(){jQuery("select.vpconfig-select").trigger("change")},1e3)}function reskin_select(){function e(){var e=jQuery(this).find(":selected").text();jQuery(this).parent().children("span").text(e)}for(var t=0;t<jQuery("select").length;t++){var a=jQuery("select").eq(t);if(!(0==a.hasClass("styleme")||a.parent().hasClass("select_wrapper")||a.parent().hasClass("select-wrapper")||a.parent().hasClass("dzs--select-wrapper"))){var r=a.find(":selected");a.wrap('<div class="dzs--select-wrapper"></div>'),a.parent().prepend("<span>"+r.text()+"</span>")}}jQuery(document).off("change.dzsselectwrap"),jQuery(document).on("change.dzsselectwrap",".dzs--select-wrapper select",e)}function adminPageWaveformChecker_init(){jQuery(document).on("change","#dzsap_is_admin_systemCheck_waves--search",function(){jQuery(".dzs-big-search--con").trigger("submit")})}function show_feedback(e,t){var a={extra_class:""};t&&(a=jQuery.extend(a,t));var r=jQuery(".feedbacker").eq(0),n="feedbacker "+a.extra_class;""==a.extra_class&&(0==e.indexOf("success - ")&&(e=e.substr(10)),0==e.indexOf("error - ")&&(e=e.substr(8),n="feedbacker is-error")),r.attr("class",n),r.html(e),r.fadeIn("fast"),setTimeout(function(){r.fadeOut("slow")},2e3)}function parse_response(e){var t={};if("object"==typeof e)return e;try{t=JSON.parse(e)}catch(t){console.log("did not parse",e)}return t}function dzs_init_sliders(e,t,a){jQuery.fn.slider&&jQuery("#sliderId_slider").slider({range:"max",min:e,max:t,value:a,stop:function(e,t){jQuery("*[name=sliderId]").val(t.value),jQuery("*[name=sliderId]").trigger("change")}})}function dzs_init_colorpickers(e,t,a){jQuery.fn.farbtastic&&jQuery(".with_colorpicker").each(function(){var e=jQuery(this);e.hasClass("treated")||(jQuery.fn.farbtastic?e.next().find(".picker").farbtastic(e):window.console&&console.info("declare farbtastic..."),e.addClass("treated"),e.bind("change",function(){jQuery("#customstyle_body").html("body{ background-color:"+$("input[name=color_bg]").val()+"} .dzsportfolio, .dzsportfolio a{ color:"+$("input[name=color_main]").val()+"} .dzsportfolio .portitem:hover .the-title, .dzsportfolio .selector-con .categories .a-category.active { color:"+$("input[name=color_high]").val()+" }")}),e.trigger("change"),e.bind("click",function(){e.next().hasClass("picker-con")&&e.next().find(".the-icon").eq(0).trigger("click")}))})}Object.defineProperty(exports,"__esModule",{value:!0}),exports.setup_autoClick=setup_autoClick,exports.addUploaderButtons=addUploaderButtons,exports.addGutenbergButtons=addGutenbergButtons,exports.reskin_select_setup=reskin_select_setup,exports.reskin_select=reskin_select,exports.adminPageWaveformChecker_init=adminPageWaveformChecker_init,exports.show_feedback=show_feedback,exports.parse_response=parse_response,exports.dzs_init_sliders=dzs_init_sliders,exports.dzs_init_colorpickers=dzs_init_colorpickers;var _uploaderPreview=require("./_uploaderPreview");
},{"./_uploaderPreview":11}],9:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.nag_intro_tooltip = nag_intro_tooltip;

function nag_intro_tooltip(main_settings) {
  jQuery(document).on('click', '.dzs--close-btn', function () {
    var $t = jQuery(this);
    $t.parent().parent().remove();
  });
  jQuery(document).on('click', '.dzs-ajax--hide-tips', function () {
    var $t = jQuery(this);
    jQuery.ajax({
      method: 'POST',
      url: window.ajaxurl,
      data: {
        action: 'dzsap_admin_nag_disable_all'
      },
      success: response => {}
    });
    $t.parent().parent().parent().parent().remove();
  }); // -- nag

  if (main_settings && main_settings.nag_intro_data && localStorage.getItem(main_settings.prefix + '_nag_intro_seen') !== 'on') {
    if (main_settings.nag_intro_data == 'on') {
      jQuery('#toplevel_page_dzsap_menu .wp-menu-name').wrap(`<span class="dzs--nag-intro-tooltip dzstooltip-con js " style="z-index: 555555"><span class="tooltip-indicator" style="color: inherit;"></span><span class=" dzstooltip active talign-end arrow-left style-rounded color-dark-light  dims-set transition-slidedown " style="width: 530px"><span class="dzstooltip--inner text-align-center">
<h4>${main_settings.translate_nag_intro_title}</h4>
<p>${main_settings.translate_nag_intro_1}</p>
<span class="dzs--close-btn"><span class="dashicons dashicons-no"></span></span>
<span class="dzs-row">
<span class="dzs-col-md-6"><h6>${main_settings.translate_nag_intro_title_1}</h6>
<p>${main_settings.translate_nag_intro_col1}</p>
<div style="background-image:url(https://i.imgur.com/Ec6Tpf5.jpg);"  class="fullwidth divimage"></div>
</span>

<span class="dzs-col-md-6"><h6>${main_settings.translate_nag_intro_title_2}</h6>
<p>${main_settings.translate_nag_intro_col2}</p>
<div style="background-image:url(https://i.imgur.com/LorVNVf.jpg);"  class="fullwidth divimage"></div>
</span>
</span>
</span> </span></span>`);
      jQuery('.toplevel_page_dzsap_menu').css({
        display: 'flex',
        alignItems: 'center'
      });
      jQuery('.toplevel_page_dzsap_menu .wp-menu-name').css({
        paddingLeft: 0
      });
    }

    jQuery(document).on('click', '.dzs--close-btn', function () {
      var _t = jQuery(this);

      _t.parent().parent().hide();

      var data = {
        action: 'dzsap_hide_intro_nag',
        postdata: 'on'
      };
      jQuery.ajax({
        type: "POST",
        url: window.ajaxurl,
        data: data
      });
      return false;
    });
    localStorage.setItem(main_settings.prefix + '_nag_intro_seen', 'on');
  }
}

},{}],10:[function(require,module,exports){
"use strict";function init_query_arg_globals(){window.get_query_arg=function(e,n){if(e.indexOf(n+"=")>-1){var r="[?&]"+n+"(.+?)(?=&|$)",i=new RegExp(r),o=i.exec(e);if(null!=o){if(o[1]){return o[1].replace(/=/g,"")}return""}}},window.add_query_arg=function(e,n,r){n=encodeURIComponent(n),r=encodeURIComponent(r);var i=e,o=n+"="+r,t=new RegExp("(&|\\?)"+n+"=[^&]*");return i=i.replace(t,"$1"+o),i.indexOf(n+"=")>-1||(i.indexOf("?")>-1?i+="&"+o:i+="?"+o),i}}Object.defineProperty(exports,"__esModule",{value:!0}),exports.init_query_arg_globals=init_query_arg_globals;
},{}],11:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.dzsUploaderTargetInit = dzsUploaderTargetInit;
exports.DzsUploaderPreview = void 0;

class DzsUploaderPreview {
  constructor($elem_) {
    const $elem = jQuery($elem_);
    this.$elem = $elem;

    if (!$elem.hasClass('inited')) {
      this.init();
    }

    setTimeout(function () {
      $elem.trigger('change');
    }, 500);
  }

  init() {
    const {
      $elem
    } = this;
    $elem.addClass('inited');
    $elem.off('change');
    $elem.on('change', function () {
      var inputVal = $elem.val();
      let $previewer = null;

      if ($elem.prev().hasClass('uploader-preview')) {
        $previewer = $elem.prev();
      }

      if ($previewer) {
        if (inputVal && isNaN(Number(inputVal)) == false) {
          var data = {
            action: 'dzs_get_attachment_src',
            id: inputVal
          };
          jQuery.ajax({
            type: "POST",
            url: window.ajaxurl,
            data: data,
            success: function (response) {
              if (response && (response.indexOf('.jpg') > -1 || response.indexOf('.jpeg') > -1)) {
                $previewer.css('background-image', 'url(' + response + ')');
                $previewer.html(' ');
                $previewer.removeClass('empty');
              } else {
                $previewer.html('');
                $previewer.addClass('empty');
              }
            },
            error: function (arg) {
              ;
            }
          });
        } else {
          $previewer.css('background-image', 'url(' + inputVal + ')');
          $previewer.html(' ');
          $previewer.removeClass('empty');
        }

        if (inputVal == '') {
          $previewer.html('');
          $previewer.addClass('empty');
        }
      }
    });
  }

}

exports.DzsUploaderPreview = DzsUploaderPreview;

function dzsUploaderTargetInit() {
  jQuery('.uploader-target').each(function () {
    new DzsUploaderPreview(this);
    return this;
  });
}

},{}],12:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.feedbacker_show_message = exports.feedbacker_init = void 0;
var _feedbacker = null;

const feedbacker_init = () => {
  var $ = jQuery;
  _feedbacker = $('.feedbacker');

  _feedbacker.fadeOut('fast');
};

exports.feedbacker_init = feedbacker_init;

const feedbacker_show_message = arg => {
  _feedbacker.html(arg);

  _feedbacker.fadeIn('fast').delay(2000).fadeOut('fast');
};

exports.feedbacker_show_message = feedbacker_show_message;

},{}],13:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.mainoptions_init = void 0;

var _feedbacker = require("./_feedbacker");

const mainoptions_init = function () {
  var $ = jQuery;
  var $tabs_ = document.querySelector('#dzs-tabs--main-options');
  const $saveMainOptions = $('.save-mainoptions');
  var inter_debounce_search = 0;
  $saveMainOptions.on('click', dzs_admin_mainOptions_saveAll);

  function dzs_admin_mainOptions_saveAll() {
    jQuery('#save-ajax-loading').css('visibility', 'visible');
    const mainarray = jQuery('.mainsettings').serialize();
    const data = {
      action: 'dzsap_ajax_mo',
      postdata: mainarray
    };
    $saveMainOptions.prop('disabled', true);
    $saveMainOptions.attr('disabled', 'disabled');
    jQuery.post(ajaxurl, data, function (response) {
      (0, _feedbacker.feedbacker_show_message)('Options saved.');
      $saveMainOptions.prop('disabled', false);
      $saveMainOptions.removeAttr('disabled');
      jQuery('#save-ajax-loading').css('visibility', 'hidden');
    });
    return false;
  }

  $(document).on('keyup change mouseup', '#dzs--settings-search', function (e) {
    clearTimeout(inter_debounce_search);
    inter_debounce_search = setTimeout(handle_change_settings_search, 400, e);
  });

  function search_for_setting_name(arg) {
    $('.setting').each(function (e) {
      var $t = $(this);
      var searchedSettingName = arg.toLowerCase();
      var currentSettingLabel = $t.find('.setting-label').length ? String($t.find('.setting-label').eq(0).text()).toLowerCase() : '';
      var currentSettingSidenote = $t.find('.sidenote').length ? String($t.find('.sidenote').eq(0).text()).toLowerCase() : '';

      if (searchedSettingName) {
        if (currentSettingLabel.indexOf(searchedSettingName) > -1 || currentSettingSidenote.indexOf(searchedSettingName) > -1) {
          $t.show();
        } else {
          $t.hide();
        }
      } else {
        $t.show();
      }
    });
    check_which_tabs_have_settings_visible(arg);
    setTimeout(() => {
      $tabs_.api_handle_resize();
    }, 300);
  }

  function check_which_tabs_have_settings_visible(arg) {
    $('.tab-menu-con').each(function (e) {
      var $t = $(this);
      var isHasAnyDisplayOtherThanNone = false;
      $t.find('.setting').each(function (e) {
        var $setting = $(this);

        if ($setting.css('display') !== 'none') {
          isHasAnyDisplayOtherThanNone = true;
        }
      });
      isHasAnyDisplayOtherThanNone ? $t.css('display', '') : $t.hide();

      if (arg === '') {
        $t.css('display', '');
      }
    });
  }

  function handle_change_settings_search(e) {
    var $t = $(e.currentTarget);
    var val = $t.val();

    if (val) {
      $tabs_.api_set_fixed_mode('toggle');
      $tabs_.classList.add('dzstaa-all-tabs-content-visible');
    } else {
      $tabs_.api_set_fixed_mode('');
      $tabs_.classList.remove('dzstaa-all-tabs-content-visible');
    }

    search_for_setting_name(val);
  }
};

exports.mainoptions_init = mainoptions_init;

},{"./_feedbacker":12}],14:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.systemCheck_waves_check = systemCheck_waves_check;

function systemCheck_waves_check() {
  var $ = jQuery;
  $(document).on('click', '.systemCheck_waves_check-regenerate-waveform', function (e) {
    let $t = $(this);
    const iframeCode = '<iframe class="regenerate-waveform-iframe" src="' + window.dzsap_settings.admin_url + 'admin.php?page=dzsap-mo&dzsap_wave_regenerate=on&disable_isShowTrackInfo=on&track_id=' + encodeURIComponent($t.attr('data-file_id')) + '&track_url=' + encodeURIComponent($t.attr('data-track_url')) + '" width="100%" height="400"></iframe>';
    $t.get(0).outerHTML = iframeCode;
    return false;
  });
  $(document).on('change', '#dzsap_is_admin_systemCheck_waves', function (e) {
    let $t = $(this);
    const COOKIE_LAB = 'dzsap_is_admin_systemCheck_waves';

    if ($t.prop('checked')) {
      document.cookie = COOKIE_LAB + "=on";
    } else {
      document.cookie = COOKIE_LAB + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
    }

    setTimeout(function () {
      window.location.reload();
    }, 100);
  });
}

},{}],15:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports._vpconfigsPreviewFunctionality = void 0;
/**
 * in admin global
 */

const _vpconfigsPreviewFunctionality = () => {
  var $ = jQuery;
  var inter_updatePreview = 0;
  $(document).on('change', '.mainsetting', updatePreview_debounce);
  $(document).on('click', '.bigoption', updatePreview_debounce);
  $(document).on('click', '.btn-refresh-preview', handle_mouse);

  function updatePreview_debounce() {
    clearTimeout(inter_updatePreview);
    inter_updatePreview = setTimeout(updatePreview, 150);
  }

  function updatePreview() {
    $('.btn-refresh-preview').trigger('click');
  }

  function handle_mouse(e) {
    var _t = $(this);

    if (e.type === 'click') {
      if (_t.hasClass('btn-refresh-preview')) {
        // -- save the config as temporary
        setTimeout(() => {
          $('.preview-player-iframe').addClass('preview-iframe-hidden');
        }, 2);
        var mainarray = currSlider.serializeAnything();
        var data = {
          action: 'dzsap_save_configs',
          postdata: mainarray,
          called_from: 'btn-refresh-preview',
          slider_name: 'called_from_vpconfig_admin_preview',
          currdb: dzsap_settings.currdb
        };
        jQuery.post(ajaxurl, data, function (response) {
          // -- let us refresh the iframe
          setTimeout(() => {
            $('.preview-player-iframe').attr('src', dzsap_settings.site_url + '/wp-admin/admin.php?page=dzsap-mo&dzsap_preview_player=on&config=called_from_vpconfig_admin_preview');
          }, 100);
          setTimeout(() => {
            $('.preview-iframe-hidden').removeClass('preview-iframe-hidden');
          }, 1500);
        });
        return false;
      }
    }
  }
};

exports._vpconfigsPreviewFunctionality = _vpconfigsPreviewFunctionality;

},{}],16:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.vpconfigs_init = void 0;

var _vpconfigsPreviewFunctionality2 = require("./_vpconfigs--previewFunctionality");
/**
 * in admin global
 */


const vpconfigs_init = () => {
  var $ = jQuery;
  $(document).on('change', 'select.vpconfig-select', change_vpconfig);
  (0, _vpconfigsPreviewFunctionality2._vpconfigsPreviewFunctionality)();

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
};

exports.vpconfigs_init = vpconfigs_init;

},{"./_vpconfigs--previewFunctionality":15}],17:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.wave_regenerate_init = void 0;

var _helper_admin = require("../js_common/_helper_admin");

const wave_regenerate_init = function () {
  var $ = jQuery;

  function updateFields() {
    if (document.querySelector('*[name=track_id]')) {
      $('*[name=wavedata_track_url]').val($('*[name=track_url]').val());
      $('*[name=wavedata_track_id]').val($('*[name=track_id]').val());
      $('*[name=wavedata_track_url_id]').val($('*[name=track_url_id]').val());
    } else {
      $('*[name=wavedata_track_url]').val(window.get_query_arg(window.location.href, 'track_url'));
      $('*[name=wavedata_track_id]').val(window.get_query_arg(window.location.href, 'track_id'));
      $('*[name=wavedata_track_url_id]').val(window.get_query_arg(window.location.href, 'track_url_id'));
    }
  }

  updateFields();
  $('*[name=track_url],*[name=track_id],*[name=track_url_id]').on('keyup', function () {
    updateFields();
  });
  window.dzsap_admin_waveforms_submitPcmData = submitPcmData;
  $(document).on('submit', '.track-waveform-meta', handle_submit);
  $(document).on('click', '.regenerate-waveform', handle_mouse);

  function handle_mouse(e) {
    var _t = $(this);

    if (e.type === 'click') {
      if (_t.hasClass('regenerate-waveform')) {
        _t.attr('data-player-source', $('#dzsap_woo_product_track').val()); // -- tbc


        var regenerateUrl = dzsap_settings.admin_url + 'admin.php';
        regenerateUrl = add_query_arg(regenerateUrl, 'page', 'dzsap-mo');
        regenerateUrl = add_query_arg(regenerateUrl, 'dzsap_wave_regenerate', 'on');
        regenerateUrl = add_query_arg(regenerateUrl, 'track_id', _t.attr('data-playerid'));
        regenerateUrl = add_query_arg(regenerateUrl, 'track_url', _t.attr('data-player-source'));

        _t.after('<iframe class="regenerate-waveform-iframe" src="' + regenerateUrl + '" width="100%" height="540"></iframe>');

        return false;
      }
    }
  }

  function submitPcmData($form_) {
    var $form = jQuery($form_);
    updateFields();
    var trackUrl = $form.find('*[name="wavedata_track_url"]').eq(0).val();
    var trackId = $form.find('*[name="wavedata_track_id"]').eq(0).val();
    var trackUrlId = $form.find('*[name="wavedata_url_id"]').eq(0).val();
    var pcmData = $form.find('*[name="wavedata_pcm"]').eq(0).val();
    var data = {
      action: 'dzsap_submit_pcm',
      playerid: trackId,
      source: trackUrl,
      postdata: pcmData,
      forceUrlTrackId: trackUrlId
    };
    $.ajax({
      type: "POST",
      url: window.ajaxurl,
      data: data,
      success: function (response) {
        response = (0, _helper_admin.parse_response)(response);
        let responseText = 'Success!';
        const isSuccess = response.success;

        if (!isSuccess) {
          responseText = 'Failed.';

          if (response.data) {
            responseText = response.data;
          }
        }

        if (response.data.report_message) {
          responseText = response.data.report_message;
        }

        if (window) {
          (0, _helper_admin.show_feedback)(responseText);
        }
      },
      error: function (arg) {
        console.log('Got this from the server: ' + arg, arg);
        ;
      }
    });
  }

  function handle_submit() {
    submitPcmData(this);
    return false;
  }
};

exports.wave_regenerate_init = wave_regenerate_init;

window.dzsap_waveform_autogenerateWithId = function (attachmentId) {
  var data = {
    action: 'dzsap_get_pcm',
    source: attachmentId
  };
  jQuery.ajax({
    type: "POST",
    url: window.ajaxurl,
    data: data,
    success: function (response) {
      if (!response) {
        const iframeCode = '<iframe class="regenerate-waveform-iframe" style="opacity: 0; pointer-events: none; position:absolute; bottom:0; right:0; width: 100px; height: 100px;" src="' + window.dzsap_settings.admin_url + 'admin.php?page=dzsap-mo&dzsap_wave_regenerate=on&dzsap_wave_generate_auto=on&disable_isShowTrackInfo=off&track_id=' + encodeURIComponent(attachmentId) + '" width="100%" height="400"></iframe>';
        jQuery('body').append(iframeCode);
      }
    },
    error: function (arg) {
      if (typeof window.console != "undefined") {
        console.warn('Got this from the server: ' + arg);
      }

      ;
    }
  });
};

},{"../js_common/_helper_admin":8}]},{},[4])


//# sourceMappingURL=admin_global.js.map