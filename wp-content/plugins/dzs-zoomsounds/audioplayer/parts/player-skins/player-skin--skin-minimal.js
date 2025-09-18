'use strict';

var DESIGN_SKIN = 'skin-minimal';
window.dzsap_skin_minimal_inited = true;



if(!window.dzsap_skinFunctions_handleResize){
  window.dzsap_skinFunctions_handleResize = {};
}

window.dzsap_skinFunctions_handleResize[DESIGN_SKIN] = function(selfClass){
  console.log('called');

  var skin_minimal_button_size = selfClass._apControls.width();
  if (selfClass.skin_minimal_canvasplay) {
    selfClass.skin_minimal_canvasplay.style.width = skin_minimal_button_size;
    selfClass.skin_minimal_canvasplay.width = skin_minimal_button_size;
    selfClass.skin_minimal_canvasplay.style.height = skin_minimal_button_size;
    selfClass.skin_minimal_canvasplay.height = skin_minimal_button_size;

    jQuery(selfClass.skin_minimal_canvasplay).css({
      'width': skin_minimal_button_size
      , 'height': skin_minimal_button_size
    });
  }

}
/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param {jQuery} $conPlayPause
 * @param {function} seek_to_perc
 * @param {function} handleEnterFrame
 * @param {MouseEvent} e
 */
window.dzsap_view_player_skinMinimal_onClickPlayPause = function (selfClass, $conPlayPause, seek_to_perc, handleEnterFrame, e, togglePlayPause) {


  var skin_minimal_button_size = selfClass._apControls.width();
  const center_x = $conPlayPause.offset().left + skin_minimal_button_size / 2;
  const center_y = $conPlayPause.offset().top + skin_minimal_button_size / 2;
  const mouse_x = e.pageX;
  const mouse_y = e.pageY;

  let perc = -(mouse_x - center_x - (skin_minimal_button_size / 2)) * 0.005;
  if (mouse_y < center_y) {
    perc = 0.5 + (0.5 - perc)
  }

  // -- if between outer scrub
  if (Math.abs(mouse_y - center_y) > 20 || Math.abs(mouse_x - center_x) > 20) {

    seek_to_perc(perc, {
      call_from: "skin_minimal_scrub"
    })

    handleEnterFrame({
      'fire_only_once': true
    });
  }

  togglePlayPause();
}
window.dzsap_view_player_skinMinimal_drawFrame = function (selfClass, scrubbarProgX) {


  if (selfClass.isPlayerPlaying || selfClass.isCanvasFirstDrawn === false) {


    var ctx_minimal = selfClass.skin_minimal_canvasplay.getContext('2d');

    var ctx_w = selfClass.skin_minimal_canvasplay.width;
    var ctx_h = selfClass.skin_minimal_canvasplay.height;

    var pw = ctx_w / 100;
    var ph = ctx_h / 100;

    if (selfClass._actualPlayer) {

    }
    scrubbarProgX = Math.PI * 2 * (selfClass.timeModel.getVisualCurrentTime() / selfClass.timeModel.getVisualTotalTime());

    if (isNaN(scrubbarProgX)) {
      scrubbarProgX = 0;
    }
    if (scrubbarProgX > Math.PI * 2) {
      scrubbarProgX = Math.PI * 2;
    }

    ctx_minimal.clearRect(0, 0, ctx_w, ctx_h);


    // -- use design_wave_color_progress for drawing skin-minimal circle


    ctx_minimal.beginPath();
    ctx_minimal.arc((50 * pw), (50 * ph), (40 * pw), 0, Math.PI * 2, false);
    ctx_minimal.fillStyle = "rgba(0,0,0,0.1)";
    ctx_minimal.fill();


    ctx_minimal.beginPath();
    ctx_minimal.arc((50 * pw), (50 * ph), (34 * pw), 0, scrubbarProgX, false);
    ctx_minimal.lineWidth = (10 * pw);
    ctx_minimal.strokeStyle = 'rgba(0,0,0,0.3)';
    ctx_minimal.stroke();


    selfClass.isCanvasFirstDrawn = true;


  }
}
