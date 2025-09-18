(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";function setup_autoClick(){var e=jQuery;e(".dzs-auto-click-after-1000").each(function(){var t=e(this);setTimeout(function(){t.trigger("click")},1e3)})}function addUploaderButtons(){var e=jQuery;e(document).off("click.dzswup",".dzs-wordpress-uploader"),e(document).on("click.dzswup",".dzs-wordpress-uploader",function(t){var a=e(this),r=a.prev();a.parent().hasClass("upload-for-target-con")?r=a.parent().find("input").eq(0):a.parent().parent().parent().hasClass("upload-for-target-con")&&(r=a.parent().parent().parent().find("input").eq(0));var n="";r.hasClass("upload-type-audio")&&(n="audio"),r.hasClass("upload-type-video")&&(n="video"),r.hasClass("upload-type-image")&&(n="image");var s=wp.media.frames.dzsp_addimage=wp.media({title:"Insert Media",library:{type:n},button:{text:"Insert Media",close:!0}});return s.on("select",function(){var e=s.state().get("selection").first(),t=e&&e.attributes&&e.attributes.filename?e.attributes.filename:"",n=e.attributes?e.attributes.id:0,i=e.attributes.url;a.hasClass("insert-id")&&(i=e.attributes.id),r.val(i),r.trigger("change"),t&&t.length>5&&(t.indexOf(".mp3")>t.length-5||t.indexOf(".wav")>t.length-5||t.indexOf(".m4a")>t.length-5)&&window.dzsap_waveform_autogenerateWithId(n);var o=null;r.parent().parent().hasClass("tab-content")&&(o=r.parent().parent()),"dzsap_meta_item_source"==r.attr("name")&&o&&setTimeout(function(e){if(e.attributes.title){var t="the_post_title";""==o.find('*[name="'+t+'"]').eq(0).val()&&o.find('*[name="'+t+'"]').eq(0).val(e.attributes.title),setTimeout(function(e){e.trigger("change")},500,o.find('*[name="'+t+'"]').eq(0))}if(e.attributes.artist){var t="artistname";""==o.find('*[name="'+t+'"]').eq(0).val()&&o.find('*[name="'+t+'"]').eq(0).val(e.attributes.artist),setTimeout(function(e){e.trigger("change")},500,o.find('*[name="'+t+'"]').eq(0))}},500,e),(r.attr("name").indexOf("item_source")>-1||"source"==r.attr("name"))&&r.parent().find('*[name="dzsap_meta_source_attachment_id"]').eq(0).val(e.attributes.id)}),s.open(),t.stopPropagation(),t.preventDefault(),!1}),e(document).off("click",".dzs-btn-add-media-att"),e(document).on("click",".dzs-btn-add-media-att",function(){var t=e(this),a={title:"Add Item",button:{text:"Select"},multiple:!1};t.attr("data-library_type")&&(a.library={type:t.attr("data-library_type")});var r=wp.media.frames.downloadable_file=wp.media(a);return r.on("select",function(){var e=r.state().get("selection");e=e.toJSON();var a=0;for(a=0;a<e.length;a++){var n=e[a];void 0!=n.id&&(t.hasClass("button-setting-input-url")?t.parent().parent().find("input").eq(0).val(n.url):t.parent().parent().find("input").eq(0).val(n.id),t.parent().parent().find("input").eq(0).trigger("change"))}}),r.open(),!1}),(0,_uploaderPreview.dzsUploaderTargetInit)()}function addGutenbergButtons(){return setInterval(function(){if(window.tinyMCE)for(var e in window.tinyMCE.editors){var t=window.tinyMCE.editors[e],a=t.$();a.hasClass("wp-block-freeform")&&(0===a.parent().parent().parent().find(".wp-content-media-buttons").length&&a.parent().parent().parent().find(".block-library-classic__toolbar").append('<div class="wp-content-media-buttons"></div>'),window.dzsap_add_media_buttons())}},2e3)}function reskin_select_setup(){reskin_select(),setTimeout(reskin_select,10),setTimeout(function(){jQuery("select.vpconfig-select").trigger("change")},1e3)}function reskin_select(){function e(){var e=jQuery(this).find(":selected").text();jQuery(this).parent().children("span").text(e)}for(var t=0;t<jQuery("select").length;t++){var a=jQuery("select").eq(t);if(!(0==a.hasClass("styleme")||a.parent().hasClass("select_wrapper")||a.parent().hasClass("select-wrapper")||a.parent().hasClass("dzs--select-wrapper"))){var r=a.find(":selected");a.wrap('<div class="dzs--select-wrapper"></div>'),a.parent().prepend("<span>"+r.text()+"</span>")}}jQuery(document).off("change.dzsselectwrap"),jQuery(document).on("change.dzsselectwrap",".dzs--select-wrapper select",e)}function adminPageWaveformChecker_init(){jQuery(document).on("change","#dzsap_is_admin_systemCheck_waves--search",function(){jQuery(".dzs-big-search--con").trigger("submit")})}function show_feedback(e,t){var a={extra_class:""};t&&(a=jQuery.extend(a,t));var r=jQuery(".feedbacker").eq(0),n="feedbacker "+a.extra_class;""==a.extra_class&&(0==e.indexOf("success - ")&&(e=e.substr(10)),0==e.indexOf("error - ")&&(e=e.substr(8),n="feedbacker is-error")),r.attr("class",n),r.html(e),r.fadeIn("fast"),setTimeout(function(){r.fadeOut("slow")},2e3)}function parse_response(e){var t={};if("object"==typeof e)return e;try{t=JSON.parse(e)}catch(t){console.log("did not parse",e)}return t}function dzs_init_sliders(e,t,a){jQuery.fn.slider&&jQuery("#sliderId_slider").slider({range:"max",min:e,max:t,value:a,stop:function(e,t){jQuery("*[name=sliderId]").val(t.value),jQuery("*[name=sliderId]").trigger("change")}})}function dzs_init_colorpickers(e,t,a){jQuery.fn.farbtastic&&jQuery(".with_colorpicker").each(function(){var e=jQuery(this);e.hasClass("treated")||(jQuery.fn.farbtastic?e.next().find(".picker").farbtastic(e):window.console&&console.info("declare farbtastic..."),e.addClass("treated"),e.bind("change",function(){jQuery("#customstyle_body").html("body{ background-color:"+$("input[name=color_bg]").val()+"} .dzsportfolio, .dzsportfolio a{ color:"+$("input[name=color_main]").val()+"} .dzsportfolio .portitem:hover .the-title, .dzsportfolio .selector-con .categories .a-category.active { color:"+$("input[name=color_high]").val()+" }")}),e.trigger("change"),e.bind("click",function(){e.next().hasClass("picker-con")&&e.next().find(".the-icon").eq(0).trigger("click")}))})}Object.defineProperty(exports,"__esModule",{value:!0}),exports.setup_autoClick=setup_autoClick,exports.addUploaderButtons=addUploaderButtons,exports.addGutenbergButtons=addGutenbergButtons,exports.reskin_select_setup=reskin_select_setup,exports.reskin_select=reskin_select,exports.adminPageWaveformChecker_init=adminPageWaveformChecker_init,exports.show_feedback=show_feedback,exports.parse_response=parse_response,exports.dzs_init_sliders=dzs_init_sliders,exports.dzs_init_colorpickers=dzs_init_colorpickers;var _uploaderPreview=require("./_uploaderPreview");
},{"./_uploaderPreview":3}],2:[function(require,module,exports){
"use strict";function init_query_arg_globals(){window.get_query_arg=function(e,n){if(e.indexOf(n+"=")>-1){var r="[?&]"+n+"(.+?)(?=&|$)",i=new RegExp(r),o=i.exec(e);if(null!=o){if(o[1]){return o[1].replace(/=/g,"")}return""}}},window.add_query_arg=function(e,n,r){n=encodeURIComponent(n),r=encodeURIComponent(r);var i=e,o=n+"="+r,t=new RegExp("(&|\\?)"+n+"=[^&]*");return i=i.replace(t,"$1"+o),i.indexOf(n+"=")>-1||(i.indexOf("?")>-1?i+="&"+o:i+="?"+o),i}}Object.defineProperty(exports,"__esModule",{value:!0}),exports.init_query_arg_globals=init_query_arg_globals;
},{}],3:[function(require,module,exports){
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

},{}],4:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.init_checkSanity = init_checkSanity;
exports.init_generateSampleShortcode = init_generateSampleShortcode;
exports.prepare_send_queue_calls = prepare_send_queue_calls;
exports.getSliderItemContainerFromSettingField = getSliderItemContainerFromSettingField;

function init_checkSanity() {
  setTimeout(() => {
    if (!(window.wp && wp.media)) {
      console.log('[dzs-sliders] something wrong with wp.media');
    }
  }, 1000);
}

function init_generateSampleShortcode(selfInstance) {
  function handleChangeInTargetFields() {
    var slug = '';
    slug = jQuery('#slug').val();
    var sampleShortcode = selfInstance.SA_CONFIG.shortcode_sample;
    sampleShortcode = sampleShortcode.replace(/{{theslug}}/g, slug);
    jQuery('.dzssa--sample-shortcode-area--readonly').text(sampleShortcode);
  }

  jQuery('#slug').on('change', handleChangeInTargetFields);
  handleChangeInTargetFields();
}

function prepare_send_queue_calls(customdelay, selfInstance) {
  var delay;

  if (typeof customdelay == 'undefined') {
    delay = 2000;
  } else {
    delay = customdelay;
  }

  selfInstance.isSaving = true;
  clearTimeout(selfInstance.inter_send_to_ajax);
  selfInstance.inter_send_to_ajax = setTimeout(selfInstance.send_queue_calls, delay);
}

function getSliderItemContainerFromSettingField($t) {
  let $sliderItem = null;

  if ($t.parent().parent().parent().parent().hasClass('slider-item')) {
    $sliderItem = $t.parent().parent().parent().parent();
  }

  if ($t.parent().parent().parent().parent().parent().hasClass('slider-item')) {
    $sliderItem = $t.parent().parent().parent().parent().parent();
  }

  if ($t.parent().parent().parent().parent().parent().parent().hasClass('slider-item')) {
    $sliderItem = $t.parent().parent().parent().parent().parent().parent();
  }

  if ($t.parent().parent().parent().parent().parent().parent().parent().hasClass('slider-item')) {
    $sliderItem = $t.parent().parent().parent().parent().parent().parent().parent();
  }

  if ($t.parent().parent().parent().parent().parent().parent().parent().parent().hasClass('slider-item')) {
    $sliderItem = $t.parent().parent().parent().parent().parent().parent().parent().parent();
  }

  if ($t.parent().parent().parent().parent().parent().parent().parent().parent().parent().hasClass('slider-item')) {
    $sliderItem = $t.parent().parent().parent().parent().parent().parent().parent().parent().parent();
  }

  return $sliderItem;
}

},{}],5:[function(require,module,exports){
"use strict";function importFolderInit(e,t){function a(){var a=e(this);if(a.hasClass("btn-import-folder")){var o={action:"dzsap_import_folder",payload:e('*[data-aux-name="folder_location"]').val()};return a.parent().addClass("ajax-is-loading"),setTimeout(function(){a.parent().removeClass("ajax-is-loading")},1e3),jQuery.ajax({type:"POST",url:window.ajaxurl,data:o,success:function(a){if(a=(0,_helper_admin.parse_response)(a),a.report_message&&window&&(0,_helper_admin.show_feedback)(a.report_message),a.files)for(var o in a.files){var d=a.files[o],n={type:"create_item",term_id:r.attr("data-term_id"),term_slug:r.attr("data-term-slug"),dzsap_meta_item_type:"audio",dzsap_meta_item_path:d.path,post_title:d.name,create_source:"FROM_FOLDER_IMPORT",dzsap_meta_item_source:d.url},_=!0;e(".slider-item").each(function(){e(this).find('input[name="dzsap_meta_item_source"]').val()==d.url&&(_=!1)}),_&&(n["dzsap_meta_order_"+i]=1+s.children().length+0,window.dzs_slidersAdmin_ajaxQueue.push(n),t.prepare_send_queue_calls(10,t),setTimeout(function(){dzstaa_init(".dzs-tabs-meta-item",{init_each:!0}),dzssel_init("select.dzs-style-me",{init_each:!0}),e('*[data-aux-name="feed_mode"]').parent().find(".bigoption").eq(0).trigger("click")},1e3))}},error:function(e){console.warn("Got this from the server / error: "+e)}}),!1}}var i=0,r=e(".dzsap-sliders-con").eq(0),s=e(".dzsap-slider-items").eq(0),o="slider_single";"dzsap_sliders"!=get_query_arg(window.location.href,"taxonomy")||"dzsap_items"!=get_query_arg(window.location.href,"post_type")||void 0!==get_query_arg(window.location.href,"tag_ID")&&""!=typeof get_query_arg(window.location.href,"tag_ID")||(o="slider_multiple"),"slider_single"==o&&(i=r.attr("data-term_id"),r.attr("data-term-slug")),e(document).on("click.sliders_admin",".slider-item, .slider-item > .divimage, .add-btn-new, .add-btn-existing-media, .delete-btn,.clone-item-btn, .btn-import-folder, #import-options-link-wrap, .button-primary",a)}Object.defineProperty(exports,"__esModule",{value:!0}),exports.importFolderInit=importFolderInit;var _helper_admin=require("../js_common/_helper_admin");
},{"../js_common/_helper_admin":1}],6:[function(require,module,exports){
"use strict";

var _slidersAdminFunctions = require("./js_slidersAdmin/_slidersAdmin-functions");

var _slidersAdminImportFolder = require("./js_slidersAdmin/_slidersAdmin-importFolder");

var _query_arg_func = require("./js_common/_query_arg_func");

var _helper_admin = require("./js_common/_helper_admin");

window.dzs_slidersAdmin_ajaxQueue = [];
(0, _query_arg_func.init_query_arg_globals)();

class DzsSlidersAdmin {
  constructor($, SA_CONFIG) {
    this.$ = $;
    this.$slidersCon = null;
    this.isSaving = false;
    this.SA_CONFIG = SA_CONFIG;
    this.inter_send_to_ajax = 0;
    this.classInit();
  }

  classInit() {
    var selfInstance = this;
    var $ = this.$; // -- we ll create queue calls so that we send ajax only once

    var inter_queued_calls = 0;
    selfInstance.prepare_send_queue_calls = _slidersAdminFunctions.prepare_send_queue_calls;
    selfInstance.send_queue_calls = send_queue_calls;
    (0, _slidersAdminFunctions.init_generateSampleShortcode)(selfInstance);
    var term_id = 0;

    var _feedbacker = $('.feedbacker').eq(0);

    var $sliderItems = $('.dzsap-slider-items').eq(0);
    selfInstance.$slidersCon = $('.dzsap-sliders-con').eq(0);
    var queryArg_page = 'slider_single';

    if (get_query_arg(window.location.href, 'taxonomy') == selfInstance.SA_CONFIG['taxonomy'] && get_query_arg(window.location.href, 'post_type') == selfInstance.SA_CONFIG['post_type'] && (typeof get_query_arg(window.location.href, 'tag_ID') == 'undefined' || typeof get_query_arg(window.location.href, 'tag_ID') == '')) {
      queryArg_page = 'slider_multiple';
    }

    var slider_term_id = 0;
    var slider_term_slug = '';

    if (queryArg_page == 'slider_single') {
      slider_term_id = selfInstance.$slidersCon.attr('data-term_id');
      slider_term_slug = selfInstance.$slidersCon.attr('data-term-slug');
    }

    _feedbacker.fadeOut('fast');

    (0, _slidersAdminFunctions.init_checkSanity)();
    setTimeout(function () {
      $('body').addClass('sliders-loaded');
    }, 600);

    if (queryArg_page == 'slider_multiple') {
      $('body').addClass('page-slider-multiple');

      var _colContainer = $('#col-container');

      $('#submit').after($('.dzs--nag-intro-tooltip--sliders-admin'));

      _colContainer.before('<div class="sliders-con"></div>');

      _colContainer.after('<div class="add-slider-con"></div>');

      var $slidersConMultiple = _colContainer.prev();

      var addSliderCon = _colContainer.next();

      $slidersConMultiple.append(_colContainer.find('#col-right').eq(0));
      $('#footer-thankyou').hide();
      selfInstance.$slidersCon.hide();
      $slidersConMultiple.find('.row-actions > .edit > a').css('margin-right', '15px');
      $slidersConMultiple.find('.row-actions > .edit > a').wrapInner('<span class="the-text"></span>');
      $slidersConMultiple.find('.row-actions > .edit > a').addClass('dzs-button btn-style-default skinvariation-border-radius-more btn-padding-medium text-strong color-normal-highlight color-over-dark font-size-small');
      $('#screen-meta-links').prepend('<div id="import-options-link-wrap" class="hide-if-no-js screen-meta-toggle">\n' + '\t\t\t<button type="button" id="show-settings-link" class="button show-settings" aria-controls="screen-options-wrap" aria-expanded="false">Import</button>\n' + '\t\t\t</div>'); // -- end slider multiple

      $('#screen-options-wrap').after($('.import-slider-form'));
    }

    if (queryArg_page == 'slider_single') {
      $('body').addClass('page-slider-single');
      $('.dzsap-sliders').before($('#edittag').eq(0));
      $('#edittag').prepend($('#tabs-box').eq(0));
      $('.form-table:not(.custom-form-table)').addClass('sa-category-main');

      var _sa_categoryMain = $('.sa-category-main').eq(0);

      _sa_categoryMain.find('tr').eq(1).after('<div class="clear"></div>');

      _sa_categoryMain.find('.term-description-wrap').eq(0).after('<div class="clear"></div>');

      $('.tab-content-cat-main').append(_sa_categoryMain);
      dzstaa_init('#tabs-box');
      dzstaa_init('.dzs-tabs-meta-item', {
        init_each: true
      });
    }

    setTimeout(function () {
      $('.slider-status').removeClass('empty');
    }, 300);
    setTimeout(function () {
      $('.slider-status').removeClass('loading');
    }, 500);
    setTimeout(function () {
      // -- we place this here so that it won't fire with no reason ;)
      $(document).on('change', 'input.setting-field,select.setting-field,textarea.setting-field', handle_change);
      $(document).on('keyup', 'input.setting-field,select.setting-field,textarea.setting-field', handle_change);
      $('.slider-status').addClass('empty');
    }, 1000);
    $(document).on('change.sliders_admin', '*[name=the_post_title]', handle_change);
    $(document).on('click.sliders_admin', '.slider-item, .slider-item > .divimage, .add-btn-new, .add-btn-existing-media, .delete-btn,.clone-item-btn, #import-options-link-wrap, .button-primary', handle_mouse);
    (0, _slidersAdminImportFolder.importFolderInit)($, selfInstance);

    window.onbeforeunload = function () {
      if (selfInstance.isSaving) {
        return "Please do not close this windows until the changes are saved.";
      }
    };

    setTimeout(function () {
      if (queryArg_page == 'slider_single' && get_query_arg(window.location.href, 'taxonomy') == selfInstance.SA_CONFIG['taxonomy']) {
        $('.wrap').eq(0).append(selfInstance.$slidersCon);
      }

      $sliderItems.sortable({
        placeholder: "ui-state-highlight",
        items: ".slider-item",
        stop: function (event, ui) {
          var arr_order = [];
          var i = 1;
          $sliderItems.children().each(function () {
            var _t = $(this);

            var aux = {
              'id': _t.attr('data-id'),
              'order': i++
            };
            arr_order.push(aux);
          });
          let queue_call = {
            'type': 'set_meta_order',
            'items': arr_order,
            'term_id': slider_term_id
          };
          window.dzs_slidersAdmin_ajaxQueue.push(queue_call);
          (0, _slidersAdminFunctions.prepare_send_queue_calls)(undefined, selfInstance);
        }
      });
      $('#tabs-box').after($('.slidersAdmin--metaArea'));
    }, 500);

    function handle_change(e) {
      var $t = $(this);
      var $sliderItem = null;

      if (e.type == 'change' || e.type == 'keyup') {
        $sliderItem = (0, _slidersAdminFunctions.getSliderItemContainerFromSettingField)($t);

        if ($t.attr('name') == 'dzsap_meta_item_source') {
          setTimeout(function () {
            $t.parent().parent().find('*[name=dzsap_meta_source_attachment_id]').trigger('change');
          }, 200);
        }

        if ($t.attr('name') == 'the_post_title') {
          $sliderItem.find('.slider-item--title').html($t.val());
        } // -- change the thumbnail


        if (String($t.attr('name')).indexOf('item_thumb') > -1) {
          $sliderItem.find('.divimage').eq(0).css({
            'background-image': 'url(' + $t.val() + ')'
          });
        }

        if ($sliderItem) {
          var id = $sliderItem.attr('data-id');
          let queue_call = {
            'type': 'set_meta',
            'item_id': id,
            'lab': $t.attr('name'),
            'val': $t.val()
          };
          var sw_found_and_set = false;

          for (var lab in window.dzs_slidersAdmin_ajaxQueue) {
            var val = window.dzs_slidersAdmin_ajaxQueue[lab];

            if (val.type == 'set_meta') {
              if (val.item_id == id) {
                if (val.lab == $t.attr('name')) {
                  window.dzs_slidersAdmin_ajaxQueue[lab].val = $t.val();
                  sw_found_and_set = true;
                }
              }
            }
          }

          if (sw_found_and_set == false) {
            window.dzs_slidersAdmin_ajaxQueue.push(queue_call);
          }

          (0, _slidersAdminFunctions.prepare_send_queue_calls)(undefined, selfInstance);
        }
      }
    }

    function handle_mouse(e) {
      var $t = $(this);

      if (e.type == 'click') {
        if ($t.attr('id') == 'import-options-link-wrap') {
          if ($t.hasClass('active') == false) {
            $('.import-slider-form').show();
            $('#screen-meta').fadeIn('fast');
            $('#screen-options-link-wrap').fadeOut('fast');
            $t.addClass('active');
          } else {
            $('#screen-meta').fadeOut('fast');
            $('.import-slider-form').fadeOut('fast');
            $('#screen-options-link-wrap').fadeIn('fast');
            $t.removeClass('active');
          }
        }

        if ($t.hasClass('button-primary')) {
          if (window.dzs_slidersAdmin_ajaxQueue.length) {
            (0, _slidersAdminFunctions.prepare_send_queue_calls)(10, selfInstance);
            setTimeout(function () {
              $('.button-primary').trigger('click');
            }, 1000);
            return false;
          }
        }

        if ($t.hasClass('delete-btn')) {
          let queue_call = {
            'type': 'delete_item',
            'id': $t.parent().attr('data-id'),
            'term_slug': slider_term_slug
          };
          window.dzs_slidersAdmin_ajaxQueue.push(queue_call);
          (0, _slidersAdminFunctions.prepare_send_queue_calls)(10, selfInstance);
          $t.parent().remove();
          return false;
        }

        if ($t.hasClass('clone-item-btn')) {
          let queue_call = {
            'type': 'duplicate_item',
            'id': $t.parent().attr('data-id'),
            'term_slug': slider_term_slug
          };
          window.dzs_slidersAdmin_ajaxQueue.push(queue_call);
          (0, _slidersAdminFunctions.prepare_send_queue_calls)(10, selfInstance);
          return false;
        }

        if ($t.hasClass('add-btn--icon')) {
          let queue_call = {
            'type': 'create_item',
            'term_id': selfInstance.$slidersCon.attr('data-term_id'),
            'term_name': selfInstance.$slidersCon.attr('data-term-slug')
          };
          queue_call['dzsap_meta_order_' + slider_term_id] = 1 + $sliderItems.children().length + 0;
          window.dzs_slidersAdmin_ajaxQueue.push(queue_call);
          (0, _slidersAdminFunctions.prepare_send_queue_calls)(10, selfInstance);
        }

        if ($t.hasClass('add-btn-new')) {
          let queue_call = {
            'type': 'create_item',
            'create_source': 'FROM_MANUAL_ADD',
            'term_id': selfInstance.$slidersCon.attr('data-term_id'),
            'term_slug': selfInstance.$slidersCon.attr('data-term-slug')
          };
          queue_call['dzsap_meta_order_' + slider_term_id] = 1 + $sliderItems.children().length + 0;
          window.dzs_slidersAdmin_ajaxQueue.push(queue_call);
          (0, _slidersAdminFunctions.prepare_send_queue_calls)(10, selfInstance);
        }

        if ($t.hasClass('add-btn-existing-media')) {
          var _targetInput = $t.prev();

          var searched_type = '';

          if ($t.hasClass('upload-type-audio') || _targetInput.hasClass('upload-type-audio')) {
            searched_type = 'audio';
          }

          if (_targetInput.hasClass('upload-type-video')) {
            searched_type = 'video';
          }

          if (_targetInput.hasClass('upload-type-image')) {
            searched_type = 'image';
          }

          var frame = wp.media.frames.dzsp_addimage = wp.media({
            title: "Insert Media",
            multiple: true,
            library: {
              type: searched_type
            },
            // Customize the submit button.
            button: {
              // Set the text of the button.
              text: "Insert Media",
              close: true
            }
          }); // When an image is selected, run a callback.

          frame.on('select', function (arg1, arg2) {
            // Grab the selected attachment.
            // TODO: add code here
            var selection = frame.state().get('selection');
            var i_sel = 0;
            selection.map(function (attachment) {
              attachment = attachment.toJSON();
              let queue_call = {
                'type': 'create_item',
                'term_id': selfInstance.$slidersCon.attr('data-term_id'),
                'term_slug': selfInstance.$slidersCon.attr('data-term-slug'),
                'post_title': attachment.title,
                'dzsap_meta_item_source': attachment.url
              };
              queue_call['dzsap_meta_order_' + slider_term_id] = 1 + $sliderItems.children().length + i_sel;
              window.dzs_slidersAdmin_ajaxQueue.push(queue_call);
              i_sel++;
            });
            (0, _slidersAdminFunctions.prepare_send_queue_calls)(10, selfInstance);
          }); // Finally, open the modal.

          frame.open();
          e.stopPropagation();
          e.preventDefault();
          return false;
        }

        if ($t.hasClass('slider-item')) {
          if ($t.hasClass('tooltip-open')) {} else {
            $('.slider-item').removeClass('tooltip-open').find('.dzstooltip').removeClass('active');
            $t.addClass('tooltip-open');
            $t.find('.dzstooltip').addClass('active');
          }
        }

        if ($t.hasClass('divimage')) {
          if ($t.parent().hasClass('slider-item')) {
            var _par = $t.parent();

            if (_par.hasClass('tooltip-open')) {
              _par.removeClass('tooltip-open');

              _par.find('.dzstooltip').removeClass('active');

              return false;
            }
          }
        }
      }
    }

    function send_queue_calls() {
      $('.slider-status').removeClass('empty');
      var arg = JSON.stringify(window.dzs_slidersAdmin_ajaxQueue);
      var data = {
        action: 'dzsap_send_queue_from_sliders_admin',
        the_term_id: selfInstance.$slidersCon.attr('data-term-id'),
        postdata: arg
      };
      jQuery.ajax({
        type: "POST",
        url: window.ajaxurl,
        data: data,
        success: function (response) {
          response = (0, _helper_admin.parse_response)(response);

          if (response.report_message) {
            if (window) {
              (0, _helper_admin.show_feedback)(response.report_message);
            }
          }

          if (response.items) {
            for (var i in response.items) {
              var responseItem = response.items[i];

              if (responseItem.type == 'create_item') {
                if (responseItem.original_request == 'duplicate_item') {
                  $('.slider-item[data-id="' + responseItem.original_post_id + '"]').after(responseItem.str);
                } else {
                  $sliderItems.append(responseItem.str);
                }

                dzstaa_init('.dzs-tabs-meta-item', {
                  init_each: true
                });
                dzssel_init('select.dzs-style-me', {
                  init_each: true
                });
              }
            }
          }

          $('.slider-status').addClass('empty');
          selfInstance.isSaving = false;
          window.dzs_slidersAdmin_ajaxQueue = [];
        },
        error: function (arg) {
          console.log('Got this from the server / error: ' + arg);
          ;
        }
      });
    }
  }

}

jQuery(document).ready(function ($) {
  new DzsSlidersAdmin($, {
    taxonomy: 'dzsap_sliders',
    post_type: 'dzsap_items',
    shortcode_sample: '[zoomsounds id="{{theslug}}"]'
  });
});

},{"./js_common/_helper_admin":1,"./js_common/_query_arg_func":2,"./js_slidersAdmin/_slidersAdmin-functions":4,"./js_slidersAdmin/_slidersAdmin-importFolder":5}]},{},[6])


//# sourceMappingURL=sliders_admin.js.map