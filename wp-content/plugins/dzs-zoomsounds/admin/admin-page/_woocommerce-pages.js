export function dzsap_initWooCommercePages(){

  const $ = jQuery;
  $(document).on('click', ' .btn-dzsap-create-playlist-for-woo', handle_mouse);


  function handle_mouse(e) {

    const _t = ($(this));

    if (e.type === 'click') {


      if (_t.hasClass('btn-dzsap-create-playlist-for-woo')) {


        var term_name = 'zoomsounds-product-playlist-' + _t.attr('data-playerid');
        var data = {
          action: 'dzsap_create_playlist'
          , term_name: term_name
        };

        _t.attr('disabled', true);
        _t.prop('disabled', true);

        _t.addClass('playlist-opened');


        $.ajax({
          type: "POST",
          url: window.ajaxurl,
          data: data,
          success: function (response) {

            if (response) {

              $('input[name="dzsap_woo_product_track"]').val(term_name);
              _t.parent().parent().parent().after('<iframe class="dzsap-woo-playlist-iframe" src="' + window.dzsap_settings.admin_url + ('term.php?taxonomy=dzsap_sliders&tag_ID=' + response + '&post_type=dzsap_items&dzs_css=remove_wp_menu') + '" width="100%" height="400"></iframe>')
            }
          },
          error: function (arg) {
            console.log('got error: ' + arg, arg);
            ;
          }
        });


        return false;


      }

    }
  }
}