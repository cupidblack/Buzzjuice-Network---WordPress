'use strict';

var DESIGN_SKIN = 'skin-minion';
window.dzsap_skin_minimal_inited = true;



if(!window.dzsap_skinFunctions_handleResize){
  window.dzsap_skinFunctions_handleResize = {};
}

window.dzsap_skinFunctions_handleResize[DESIGN_SKIN] = function(selfClass){
  let aux2 = parseInt(selfClass.$conControls.find('.con-playpause').eq(0).offset().left, 10) - parseInt(selfClass.$conControls.eq(0).offset().left, 10) - 18;
  selfClass.$conControls.find('.prev-btn').eq(0).css({
    'top': 0,
    'left': aux2
  })
  aux2 += 36;
  selfClass.$conControls.find('.next-btn').eq(0).css({
    'top': 0,
    'left': aux2
  })

}
