export const player_menuStateSetup = (cthis,o) => {

  cthis.find('.btn-menu-state').eq(0).on('click', handleClickMenuState);


  function handleClickMenuState(e) {
    if (o.parentgallery && typeof (o.parentgallery.get(0)) !== "undefined") {
      o.parentgallery.get(0).api_toggle_menu_state();
    }
  }
}
