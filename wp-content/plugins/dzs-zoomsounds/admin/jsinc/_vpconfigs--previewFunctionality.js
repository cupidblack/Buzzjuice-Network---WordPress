/**
 * in admin global
 */
export const _vpconfigsPreviewFunctionality = () => {

  var $ = jQuery;

  var inter_updatePreview = 0;


  $(document).on('change', '.mainsetting', updatePreview_debounce)
  $(document).on('click', '.bigoption', updatePreview_debounce)
  $(document).on('click', '.btn-refresh-preview', handle_mouse);


  function updatePreview_debounce() {

    clearTimeout(inter_updatePreview);
    inter_updatePreview = setTimeout(updatePreview, 150);
  }
  function updatePreview() {

    $('.btn-refresh-preview').trigger('click');
  }

  function handle_mouse(e) {

    var _t = ($(this));
    if (e.type === 'click') {
      if (_t.hasClass('btn-refresh-preview')) {
        // -- save the config as temporary

        setTimeout(() => {
          $('.preview-player-iframe').addClass('preview-iframe-hidden');
        }, 2)

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


}