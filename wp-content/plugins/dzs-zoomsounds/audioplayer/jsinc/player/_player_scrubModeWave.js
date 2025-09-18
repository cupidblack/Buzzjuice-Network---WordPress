

export function scrubbar_modeWave_clearObsoleteCanvas(selfClass) {
  if (selfClass._scrubbar) {
    selfClass._scrubbar.find('.scrubbar-type-wave--canvas.transitioning-out').remove();
  }
}


export function scrubbar_modeWave_setupCanvas_context(_canvas) {
  if(_canvas.get(0)){

    const _canvasContext = _canvas.get(0).getContext("2d");

    _canvasContext.imageSmoothingEnabled = false;
    _canvasContext.imageSmoothing = false;
    _canvasContext.imageSmoothingQuality = "high";
    _canvasContext.webkitImageSmoothing = false;
  }
}

export function scrubbar_modeWave_setupCanvas(pargs, selfClass) {

  var margs = {
    prepare_for_transition_in: false
  }

  if (pargs) {
    margs = Object.assign(margs, pargs);
  }


  var struct_scrubBg_str = '';
  var struct_scrubProg_str = '';
  var aux_selector = '';
  var o = selfClass.initOptions;


  struct_scrubBg_str = '<canvas class="scrubbar-type-wave--canvas scrub-bg-img';
  struct_scrubBg_str += '" ></canvas>';

  struct_scrubProg_str = '<canvas class="scrubbar-type-wave--canvas scrub-prog-img';
  struct_scrubProg_str += '" ></canvas>';


  selfClass._scrubbar.find('.scrub-bg').eq(0).append(struct_scrubBg_str);
  selfClass._scrubbar.find('.scrub-prog').eq(0).append(struct_scrubProg_str);


  selfClass._scrubbarbg_canvas = selfClass._scrubbar.find('.scrub-bg-img').last();
  selfClass._scrubbarprog_canvas = selfClass._scrubbar.find('.scrub-prog-img').last();

  if (o.skinwave_enableSpectrum === 'on') {
    selfClass._scrubbarprog_canvas.hide();
  }


  scrubbar_modeWave_setupCanvas_context(selfClass._scrubbarbg_canvas);
  scrubbar_modeWave_setupCanvas_context(selfClass._scrubbarprog_canvas);


  if (margs.prepare_for_transition_in) {
    selfClass._scrubbarbg_canvas.addClass('preparing-transitioning-in');
    selfClass._scrubbarprog_canvas.addClass('preparing-transitioning-in');
    setTimeout(function () {
      selfClass._scrubbarbg_canvas.addClass('transitioning-in');
      selfClass._scrubbarprog_canvas.addClass('transitioning-in');
    }, 20);
  }
}


export function view_player_scrubModeWaveAdjustCurrTimeAndTotalTime(selfClass){

  const cthis = selfClass.cthis;

  if (selfClass.scrubbarProgX < 0) {
    selfClass.scrubbarProgX = 0;
  }
  selfClass.scrubbarProgX = parseInt(selfClass.scrubbarProgX, 10);


  if (selfClass.scrubbarProgX < 2 && cthis.data('promise-to-play-footer-player-from')) {
    return false;
  }

  // -- move currTime
  selfClass.$currTime.css({
    'left': selfClass.scrubbarProgX
  });

  if (selfClass.scrubbarProgX > selfClass.scrubbarWidth - selfClass.currTime_outerWidth) {
    selfClass.$currTime.css({
      'left': selfClass.scrubbarWidth - selfClass.currTime_outerWidth
    })
  }
  if (selfClass.scrubbarProgX > selfClass.scrubbarWidth - 30 && selfClass.scrubbarWidth) {
    selfClass.$totalTime.css({
      'opacity': 1 - (((selfClass.scrubbarProgX - (selfClass.scrubbarWidth - 30)) / 30))
    });
  } else {
    if (selfClass.$totalTime.css('opacity') !== '1') {
      selfClass.$totalTime.css({
        'opacity': ''
      });
    }
  }
}
