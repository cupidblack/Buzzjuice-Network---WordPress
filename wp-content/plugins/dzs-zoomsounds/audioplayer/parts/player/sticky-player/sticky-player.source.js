import {
  DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW,
  DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM
} from "../../../configs/_constants";


/**
 *
 * @param {DzsAudioPlayer} selfClass
 */
function stickyPlayer_show(selfClass) {

  selfClass.$stickToBottomContainer.addClass(DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW);

  const $dzsapStickToBottomPlaceholder = jQuery(`.${DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM}`);
  selfClass.$stickToBottomContainer.addClass(DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW)

  $dzsapStickToBottomPlaceholder.eq(0).addClass('active');
  $dzsapStickToBottomPlaceholder.css({
    height: (selfClass.$stickToBottomContainer.outerHeight()) + 'px'
  })
}


window.dzsap_stickyPlayer_show = stickyPlayer_show;
