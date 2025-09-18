import {generateFakeArrayForPcm} from "../_dzsap_helpers";
import {draw_canvas} from "../wave-render/_wave-render-functions";

/**
 *
 * @param {DzsAudioPlayer} selfClass
 */
export function player_initSpectrum(selfClass) {


  if (window.dzsap_audio_ctx === null) {
    if (typeof AudioContext !== 'undefined') {
      selfClass.spectrum_audioContext = new AudioContext();
    } else if (typeof webkitAudioContext !== 'undefined') {
      selfClass.spectrum_audioContext = new webkitAudioContext();
    } else {
      selfClass.spectrum_audioContext = null;
    }
    window.dzsap_audio_ctx = selfClass.spectrum_audioContext;
  } else {
    selfClass.spectrum_audioContext = window.dzsap_audio_ctx;
  }


  if (selfClass.spectrum_audioContext) {
    if (selfClass.spectrum_analyser === null) {

      selfClass.spectrum_analyser = selfClass.spectrum_audioContext.createAnalyser();
      selfClass.spectrum_analyser.smoothingTimeConstant = 0.3;
      selfClass.spectrum_analyser.fftSize = 512;

      if (selfClass.audioType === 'selfHosted') {
        selfClass.$mediaNode_.crossOrigin = "anonymous";
        selfClass.spectrum_mediaElementSource = selfClass.spectrum_audioContext.createMediaElementSource(selfClass.$mediaNode_);

        selfClass.spectrum_mediaElementSource.connect(selfClass.spectrum_analyser);
        if (selfClass.spectrum_audioContext.createGain) {
          selfClass.spectrum_gainNode = selfClass.spectrum_audioContext.createGain();
        }
        selfClass.spectrum_analyser.connect(selfClass.spectrum_audioContext.destination);

        selfClass.spectrum_gainNode.gain.value = 1;

        const frameCount = selfClass.spectrum_audioContext.sampleRate * 2.0;
        selfClass.spectrum_audioContext_buffer = selfClass.spectrum_audioContext.createBuffer(2, frameCount, selfClass.spectrum_audioContext.sampleRate);
      }
    }
  }


  if (selfClass._scrubbarbg_canvas) {

    selfClass.canvasWidth = selfClass._scrubbarbg_canvas.width();
    selfClass.heightCanvas = selfClass._scrubbarbg_canvas.height();


    if (selfClass.heightCanvas == 0) {
      selfClass.heightCanvas = 100;
    }

    selfClass._scrubbarbg_canvas.get(0).width = selfClass.canvasWidth;
    selfClass._scrubbarbg_canvas.get(0).height = selfClass.heightCanvas;
  }
}


/**
 *
 * @param {DzsAudioPlayer} selfClass
 */
export function player_initSpectrumOnUserAction(selfClass) {


  selfClass.cthis.get(0).addEventListener('mousedown', handleMouseDown, {once: true});
  selfClass.cthis.get(0).addEventListener('touchdown', handleMouseDown, {once: true});

  function handleMouseDown(e) {
    player_initSpectrum(selfClass);
  }


}


/**
 *
 * @param {DzsAudioPlayer} selfClass
 * @param $scrubBgCanvas
 * @param {string} spectrum_fakeItMode
 * @param {number} heightCanvas
 */
export const view_spectrum_drawMeter = function (selfClass, $scrubBgCanvas, spectrum_fakeItMode, heightCanvas) {


  // -- spectrum ON
  // -- easing

  let duration_viy = 20;
  let begin_viy = 0;
  let change_viy = 0;

  if (selfClass.initOptions.type === 'soundcloud' || spectrum_fakeItMode === 'on') {
    selfClass.lastArray = generateFakeArrayForPcm();

  } else {


    if (selfClass.spectrum_analyser) {

      if (selfClass.isPlayerPlaying) {
        selfClass.lastArray = new Uint8Array(selfClass.spectrum_analyser.frequencyBinCount);
        selfClass.spectrum_analyser.getByteFrequencyData(selfClass.lastArray);
      }


    }
  }


  // -- normalize if pcmSource exists
  if (selfClass.pcmSource) {
    for (let i = 0; i < selfClass.lastArray.length; i++) {
      selfClass.lastArray[i] = (selfClass.lastArray[i] * 3/4 + (selfClass.pcmSource[i] /4));
    }
  }


  if (selfClass.lastArray && selfClass.lastArray.length) {
    // -- fix when some sounds end the value still not back to zero
    const len = selfClass.lastArray.length;
    for (let i = len - 1; i >= 0; i--) {

      if (i < len / 2) {

        selfClass.lastArray[i] = selfClass.lastArray[i] / 255 * heightCanvas;
      } else {

        selfClass.lastArray[i] = selfClass.lastArray[len - i] / 255 * heightCanvas;
      }

    }
    ;


    if (!selfClass.isPlayerPlaying && selfClass.pcmSource) {
      selfClass.lastArray = [...selfClass.pcmSource];
    }

    // -- normalize
    if (selfClass.isPlayerPlayingEased && selfClass.last_lastarray) {
      for (let i3 = 0; i3 < selfClass.last_lastarray.length; i3++) {
        if (selfClass.last_lastarray[i3] > heightCanvas) {
          selfClass.last_lastarray[i3] = heightCanvas;
        }
        begin_viy = selfClass.last_lastarray[i3]; // -- last value
        change_viy = selfClass.lastArray[i3] - begin_viy; // -- target value - last value
        duration_viy = 6;
        selfClass.lastArray[i3] = Math.easeIn(1, begin_viy, change_viy, duration_viy);
      }
    }
    // -- easing END


    draw_canvas($scrubBgCanvas.get(0), selfClass.lastArray, '' + selfClass.initOptions.design_wave_color_bg, {
      'call_from': 'spectrum',
      selfClass,
      'skinwave_wave_mode_canvas_waves_number': parseInt(selfClass.initOptions.skinwave_wave_mode_canvas_waves_number),
      'skinwave_wave_mode_canvas_waves_padding': parseInt(selfClass.initOptions.skinwave_wave_mode_canvas_waves_padding)
    }, false)


    if (selfClass.lastArray) {
      selfClass.last_lastarray = selfClass.lastArray.slice();
    }
  }

};
