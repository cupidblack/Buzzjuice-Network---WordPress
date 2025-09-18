
export function view_dzsSelector_init() {


  setTimeout(reskin_select, 10);
  function reskin_select() {

    const $select = jQuery('select');
    for (let i = 0; i < $select.length; i++) {
      var _cache = $select.eq(i);


      if (_cache.hasClass('styleme') == false || _cache.parent().hasClass('select_wrapper') || _cache.parent().hasClass('dzs--select-wrapper')) {
        continue;
      }
      const sel = (_cache.find(':selected'));
      _cache.wrap('<div class="dzs--select-wrapper"></div>')
      _cache.parent().prepend('<span>' + sel.text() + '</span>')
    }
    const $selectWrapperSelect = jQuery('.select-wrapper select');
    $selectWrapperSelect.unbind();
    $selectWrapperSelect.on('change', change_select);
  }



  function change_select() {
    var selval = (jQuery(this).find(':selected').text());
    jQuery(this).parent().children('span').text(selval);
  }
}
