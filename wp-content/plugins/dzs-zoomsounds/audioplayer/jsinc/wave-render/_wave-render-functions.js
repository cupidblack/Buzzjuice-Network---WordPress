import {hexToRgb, sanitizeToHexColor, string_jsonConvertToArray, utils_sanitizeToColor} from '../_dzsap_helpers';

import {ConstantsDzsAp} from "../../configs/_constants";
import {loadScriptIfItDoesNotExist} from "../../js_common/_dzs_helpers";
import {scrubbar_modeWave_clearObsoleteCanvas, scrubbar_modeWave_setupCanvas} from "../player/_player_scrubModeWave";

window.dzsap_wavesurfer_load_attempt = 0;
window.dzsap_wavesurfer_is_trying_to_generate = null;


var dzsapWaveRender = this;

/**
 * called on init_loaded
 * @param selfClass
 * @param pargs
 * @returns {boolean}
 */
export function scrubModeWave__checkIfWeShouldTryToGetPcm(selfClass, pargs) {


  var margs = {
    'call_from': 'default'
    , 'call_attempt': 0
  };
  if (pargs) {
    margs = jQuery.extend(margs, pargs);
  }


  // -- retry
  if (window.dzsap_wavesurfer_is_trying_to_generate) {
    setTimeout(function () {

      margs.call_attempt++;
      if (margs.call_attempt < 10) {
        scrubModeWave__checkIfWeShouldTryToGetPcm(selfClass, margs);
        ;
      }
    }, 1000)
    return false;
  }


  if (selfClass.isPcmRequiredToGenerate) {

    if (isWeCanGeneratePcm(selfClass)) {

      window.dzsap_wavesurfer_is_trying_to_generate = selfClass;


      window.dzsap_get_base_url();

      let wavesurferUrl = window.dzsap_base_url ? window.dzsap_base_url + 'parts/wavesurfer/dzsap-wave-generate.js' : ConstantsDzsAp.URL_WAVESURFER_HELPER_BACKUP;


      window.scrubModeWave__view_transitionIn = scrubModeWave__view_transitionIn;


      loadScriptIfItDoesNotExist(wavesurferUrl, window.scrubModeWave__initedGenerateWave).then((resolveStr) => {


        scrubModeWave__initGenerateWaveData(selfClass);
      });
    }
  }
}

function isWeCanGeneratePcm(selfClass) {

  if (selfClass.isAlreadyHasRealPcm) {
    return false;
  }
  return selfClass.data_source != 'fake';

}

export function view_drawCanvases(selfClass, argpcm, calledFrom) {
  var o = selfClass.initOptions;

  draw_canvas(selfClass._scrubbarbg_canvas.get(0), argpcm, "#" + o.design_wave_color_bg, {
    call_from: calledFrom + '_bg',
    selfClass,
    'skinwave_wave_mode_canvas_waves_number': parseInt(o.skinwave_wave_mode_canvas_waves_number, 10),
    'skinwave_wave_mode_canvas_waves_padding': parseInt(o.skinwave_wave_mode_canvas_waves_padding, 10)
  });
  draw_canvas(selfClass._scrubbarprog_canvas.get(0), argpcm, '#' + o.design_wave_color_progress, {
      call_from: calledFrom + '_prog',
      selfClass,
      'skinwave_wave_mode_canvas_waves_number': parseInt(o.skinwave_wave_mode_canvas_waves_number, 10),
      'skinwave_wave_mode_canvas_waves_padding': parseInt(o.skinwave_wave_mode_canvas_waves_padding, 10),
    },
    true);
}


/**
 * called on isPcmRequiredToGenerate ( init_loaded )  / change_media
 * @param selfClass
 * @param argpcm
 */
export function scrubModeWave__view_transitionIn(selfClass, argpcm) {


  selfClass._scrubbar.find('.scrub-bg-img,.scrub-prog-img').removeClass('transitioning-in');
  selfClass._scrubbar.find('.scrub-bg-img,.scrub-prog-img').addClass('transitioning-out');
  ;

  scrubbar_modeWave_setupCanvas({
    'prepare_for_transition_in': true
  }, selfClass);

  view_drawCanvases(selfClass, argpcm, 'canvas_generate_wave_data_animate_pcm');


  setTimeout(() => {
    scrubbar_modeWave_clearObsoleteCanvas(selfClass);
  }, 300);

  // -- warning - not always real pcm
  selfClass.isAlreadyHasRealPcm = true;
  selfClass.scrubbar_reveal();
}


/**
 * aggregate wave array based on max
 * @param waveArrayTemp
 * @returns {array}
 */
function waveCalculateWaveArray(waveArrayTemp) {
  let max = 0;
  let waveArrayNew = [];
  let barIndex = 0;

  let waveArray = string_jsonConvertToArray(waveArrayTemp);


  // -- normalizing
  for (barIndex = 0; barIndex < waveArray.length; barIndex++) {
    if ((waveArray[barIndex]) > max) {
      max = (waveArray[barIndex]);
    }
  }

  for (barIndex = 0; barIndex < waveArray.length; barIndex++) {
    waveArrayNew[barIndex] = parseFloat(Math.abs(waveArray[barIndex]) / Number(max));
  }
  // -- end normalize

  waveArray = waveArrayNew;

  return waveArray;

}


function view_drawBars(_canvasContext, isReflection, isProgressScrubContext, hexcolor, barCount, waveArray, widthCanvas, heightCanvas, reflection_size, playerOptions, margs, selfClass, bar_space) {
  let isWithinSample = false;
  var searched_index = null;
  var lastBarHeight = 0;
  var gradient = null;
  var spectrum_isBarWithinProgress = false; // -- color the bar in progress colors


  hexcolor = sanitizeToHexColor(hexcolor);
  // -- left right gradient
  var temp_hex = hexcolor;
  temp_hex = temp_hex.replace('#', '');
  var hexcolors = []; // -- hex colors array
  if (temp_hex.indexOf(',') > -1) {
    hexcolors = temp_hex.split(',');
  }


  if (barCount == 1) {
    barCount = widthCanvas / barCount;
  }
  if (barCount == 2) {
    barCount = widthCanvas / 2;
  }
  if (barCount == 3) {
    barCount = (widthCanvas) / 3;
  }


  if (widthCanvas / barCount < 1) {
    barCount = Math.ceil(barCount / 3);
  }


  var widthBar = Math.ceil(widthCanvas / barCount);
  var sizeRatioNormal = 1 - reflection_size;


  if (widthBar == 0) {
    widthBar = 1;
    bar_space = 0;
  }
  if (widthBar == 1) {
    bar_space = bar_space / 2;
  }


  var progress_hexcolor = '';
  var progress_hexcolors = '';


  if (margs.call_from == 'spectrum') {
    progress_hexcolor = playerOptions.design_wave_color_progress;
    progress_hexcolor = progress_hexcolor.replace('#', '');
    progress_hexcolors = []; // -- hex colors array
    if (progress_hexcolor.indexOf(',') > -1) {
      progress_hexcolors = progress_hexcolor.split(',');

    }
  }

  for (let barIndex = 0; barIndex < barCount; barIndex++) {
    isWithinSample = false;
    _canvasContext.save();


    searched_index = Math.ceil(barIndex * (waveArray.length / barCount));

    // -- we'll try to prevent
    if (barIndex < barCount / 5) {
      if (waveArray[searched_index] < 0.1) {
        waveArray[searched_index] = 0.1;
      }
    }
    if (waveArray.length > barCount * 2.5 && barIndex > 0 && barIndex < waveArray.length - 1) {
      waveArray[searched_index] = Math.abs(waveArray[searched_index] + waveArray[searched_index - 1] + waveArray[searched_index + 1]) / 3
    }
    // -- normalize end


    let targetRatio = sizeRatioNormal;
    if (isReflection) {
      targetRatio = reflection_size;
    }

    let barHeight = Math.abs(waveArray[searched_index] * heightCanvas * targetRatio);


    if (playerOptions.skinwave_wave_mode_canvas_normalize == 'on') {
      if (isNaN(lastBarHeight)) {
        lastBarHeight = 0;
      }
      barHeight = barHeight / 1.5 + lastBarHeight / 2.5;
    }
    lastBarHeight = barHeight;


    _canvasContext.lineWidth = 0;
    barHeight = Math.floor(barHeight);


    const barPositionTop = isReflection ? heightCanvas * sizeRatioNormal : Math.ceil(heightCanvas * targetRatio - barHeight);


    _canvasContext.beginPath();
    _canvasContext.rect(barIndex * widthBar, barPositionTop, widthBar - bar_space, barHeight);


    if (margs.call_from == 'spectrum') {
      if (barIndex / barCount < selfClass.timeCurrent / selfClass.timeTotal) {
        spectrum_isBarWithinProgress = true;
      } else {
        spectrum_isBarWithinProgress = false;
      }
    }


    if (selfClass.isSample) {
      isWithinSample = isBeforeOrAfterSample(barIndex, barCount, selfClass);
    }


    if (spectrum_isBarWithinProgress) {
      if (isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto') {
        _canvasContext.fillStyle = hexToRgb(progress_hexcolor, 0.25);
      } else {
        _canvasContext.fillStyle = isWithinSample ? hexToRgb(progress_hexcolor, 0.5) : '#' + progress_hexcolor;
      }


      if (progress_hexcolors.length) {

        const startColor = isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto' ? hexToRgb('#' + progress_hexcolors[0], 0.25) : '#' + progress_hexcolors[0];
        const endColor = isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto' ? hexToRgb('#' + progress_hexcolors[1], 0.25) : '#' + progress_hexcolors[1];

        gradient = _canvasContext.createLinearGradient(0, 0, 0, heightCanvas);
        gradient.addColorStop(0, startColor);
        gradient.addColorStop(1, endColor);
        _canvasContext.fillStyle = gradient;
      }

    } else {
      /**
       * normal
       */


      if (isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto') {
        _canvasContext.fillStyle = hexToRgb(hexcolor, 0.25);
      } else {

        _canvasContext.fillStyle = isWithinSample ? hexToRgb(hexcolor, 0.5) : '' + hexcolor;
      }

      // -- if we have gradient
      if (hexcolors.length) {
        const startColor = isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto' ? hexToRgb(utils_sanitizeToColor(hexcolors[0]), 0.25) : '' + utils_sanitizeToColor(hexcolors[0]);
        const endColor = isReflection && playerOptions.skinwave_wave_mode_canvas_mode !== 'reflecto' ? hexToRgb(utils_sanitizeToColor(hexcolors[1]), 0.25) : '' + utils_sanitizeToColor(hexcolors[1]);

        gradient = _canvasContext.createLinearGradient(0, 0, 0, heightCanvas);
        gradient.addColorStop(0, startColor);
        gradient.addColorStop(1, endColor);
        _canvasContext.fillStyle = gradient;
      }


    }


    if (!(isWithinSample && isProgressScrubContext)) {


      _canvasContext.fill();
      _canvasContext.closePath();
    }


    _canvasContext.restore();

  }

}


/**
 * draw with different color
 * @returns {boolean}
 * @param {number} currBarIndex
 * @param {number} barCount
 * @param {DzsAudioPlayer} selfClass
 */
function isBeforeOrAfterSample(currBarIndex, barCount, selfClass) {


  if ((currBarIndex / barCount < selfClass.timeModel.sampleTimeStart / selfClass.timeModel.sampleTimeTotal) || currBarIndex / barCount > selfClass.timeModel.sampleTimeEnd / selfClass.timeModel.sampleTimeTotal) {

    return true;
  }
  return false;
}

/**
 * draw canvas here
 * @param $canvas_
 * @param pcm_arr
 * @param hexcolor
 * @param pargs
 * @param {boolean} isProgressScrubContext
 * @returns {boolean}
 */
export function draw_canvas($canvas_, pcm_arr, hexcolor, pargs, isProgressScrubContext = false) {
  let margs = {
    'call_from': 'default',
    'is_sample': false,
    'selfClass': null,
    'sample_time_start': 0,
    'sample_time_end': 0,
    'sample_time_total': 0,
    'skinwave_wave_mode_canvas_waves_number': 2,
    'skinwave_wave_mode_canvas_waves_padding': 1,
  };

  const $ = jQuery;
  if (pargs) {
    margs = Object.assign(margs, pargs);
  }


  var _canvas = $($canvas_);
  var __canvas = $canvas_;

  if (_canvas.get(0)) {


    var {selfClass, skinwave_wave_mode_canvas_waves_number, skinwave_wave_mode_canvas_waves_padding} = margs;
    let playerOptions = {};
    var _canvasContext = _canvas.get(0).getContext("2d");
    var waveArrayTemp = pcm_arr;
    var widthCanvas;
    var heightCanvas;

    let waveArray = [];


    // -- sanitize
    if (isNaN(Number(skinwave_wave_mode_canvas_waves_number))) {
      skinwave_wave_mode_canvas_waves_number = 2;
    }

    if (isNaN(Number(skinwave_wave_mode_canvas_waves_padding))) {
      if (skinwave_wave_mode_canvas_waves_number !== 1) {
        skinwave_wave_mode_canvas_waves_padding = 1;
      } else {
        skinwave_wave_mode_canvas_waves_padding = 0;
      }
    }

    if (selfClass) {
      playerOptions = selfClass.initOptions;
    }


    let barCount = skinwave_wave_mode_canvas_waves_number;
    let bar_space = skinwave_wave_mode_canvas_waves_padding;


    if (_canvas && _canvas.get(0)) {

    } else {
      return false;
    }


    if (selfClass && selfClass._scrubbar) {
      if (selfClass._scrubbarprog_canvas) {
        selfClass._scrubbarbg_canvas.width(selfClass._scrubbar.width());
        selfClass._scrubbarprog_canvas.width(selfClass._scrubbar.width());
        $canvas_.width = selfClass._scrubbar.width();
        $canvas_.height = selfClass._scrubbar.height();
      }
    }


    waveArray = waveCalculateWaveArray(waveArrayTemp);


    if (selfClass) {
      __canvas.width = selfClass._scrubbar.width();
    }

    widthCanvas = __canvas.width;
    heightCanvas = __canvas.height;


    const reflection_size = parseFloat(playerOptions.skinwave_wave_mode_canvas_reflection_size);


    // -- left right gradient END


    _canvasContext.clearRect(0, 0, widthCanvas, heightCanvas);


    view_drawBars(_canvasContext, false, isProgressScrubContext, hexcolor, barCount, waveArray, widthCanvas, heightCanvas, reflection_size, playerOptions, margs, selfClass, bar_space)
    if (reflection_size > 0) {
      view_drawBars(_canvasContext, true, isProgressScrubContext, hexcolor, barCount, waveArray, widthCanvas, heightCanvas, reflection_size, playerOptions, margs, selfClass, bar_space)
    }

    // -- reflection
    setTimeout(function () {
      selfClass.scrubbar_reveal();
    }, 100)
  }


}
