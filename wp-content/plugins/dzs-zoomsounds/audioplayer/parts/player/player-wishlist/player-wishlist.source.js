


function dzsap_wishlist_player_init(){

  jQuery(document).on('click.dzsap_metas', '.dzsap-wishlist-but', handle_mouse);

  function handle_mouse(){
    var $ = jQuery;
    var $t = jQuery(this);
    if ($t.hasClass('dzsap-wishlist-but')) {


      var data = {
        action: 'dzsap_add_to_wishlist',
        playerid: $t.attr('data-post_id'),
        wishlist_action: 'add',
      };


      if ($t.find('.svg-icon').hasClass('fa-star')) {
        data.wishlist_action = 'remove';
      }


      if (window.dzsap_lasto.settings_php_handler) {
        $.ajax({
          type: "POST",
          url: window.dzsap_lasto.settings_php_handler,
          data: data,
          success: function (response) {


            if ($t.find('.svg-icon').hasClass('fa-star-o')) {
              $t.find('.svg-icon').eq(0).attr('class', 'svg-icon fa fa-star');
            } else {

              $t.find('.svg-icon').eq(0).attr('class', 'svg-icon fa fa-star-o');
            }

          },
          error: function (arg) {

          }
        });
      }

      return false;


    }

  }
}


