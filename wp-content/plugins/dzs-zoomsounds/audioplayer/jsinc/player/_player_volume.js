export function player_volumeSetup(selfClass, volume_lastVolume, $, o) {

  let last_vol_before_mute = 1;
  var isMuted = false;
  let volume_dragging = false;


  const cthis = selfClass.cthis;


  selfClass.$controlsVolume.on('click', '.volumeicon', volume_handleClickMuteIcon);

  selfClass.$controlsVolume.on('mousemove', volume_handleMouse);
  selfClass.$controlsVolume.on('mousedown', volume_handleMouse);


  $(document).on('mouseup', window, volume_handleMouse);

  if (o.design_skin === 'skin-silver') {
    cthis.on('click', '.volume-holder', volume_handleMouse);
  }


  function volume_handleClickMuteIcon(e) {

    if (isMuted === false) {
      last_vol_before_mute = volume_lastVolume;
      volume_setVolume(selfClass, 0, {
        call_from: "from_mute"
      });
      isMuted = true;
    } else {
      volume_setVolume(selfClass, last_vol_before_mute, {
        call_from: "from_unmute"
      });
      isMuted = false;
    }
  }




  function volume_handleMouse(e) {
    var _t = $(this);
    /**
     * from 0 to 1
     * @type number
     */
    let mouseXRelativeToVolume = null;

    if (selfClass.$controlsVolume.find('.volume_static').length) {

      mouseXRelativeToVolume = Number((e.pageX - (selfClass.$controlsVolume.find('.volume_static').eq(0).offset().left)) / (selfClass.$controlsVolume.find('.volume_static').eq(0).width()));
    }

    if (!mouseXRelativeToVolume) {
      return false;
    }
    if (e.type === 'mousemove') {
      if (volume_dragging) {

        if (_t.parent().hasClass('volume-holder') || _t.hasClass('volume-holder')) {
        }

        if (o.design_skin === 'skin-redlights') {
          mouseXRelativeToVolume *= 10;
          mouseXRelativeToVolume = Math.round(mouseXRelativeToVolume);
          mouseXRelativeToVolume /= 10;
        }


        volume_setVolume(selfClass, mouseXRelativeToVolume, {
          call_from: "set_by_mousemove"
        });
        isMuted = false;
      }

    }
    if (e.type === 'mouseleave') {

    }
    if (e.type === 'click') {

      if (_t.parent().hasClass('volume-holder')) {


        mouseXRelativeToVolume = 1 - ((e.pageY - (selfClass.$controlsVolume.find('.volume_static').eq(0).offset().top)) / (selfClass.$controlsVolume.find('.volume_static').eq(0).height()));

      }
      if (_t.hasClass('volume-holder')) {
        mouseXRelativeToVolume = 1 - ((e.pageY - (selfClass.$controlsVolume.find('.volume_static').eq(0).offset().top)) / (selfClass.$controlsVolume.find('.volume_static').eq(0).height()));


      }

      volume_setVolume(selfClass, mouseXRelativeToVolume, {
        call_from: "set_by_mouseclick"
      });
      isMuted = false;
    }

    if (e.type === 'mousedown') {

      volume_dragging = true;
      cthis.addClass('volume-dragging');


      if (_t.parent().hasClass('volume-holder')) {


        mouseXRelativeToVolume = 1 - ((e.pageY - (selfClass.$controlsVolume.find('.volume_static').eq(0).offset().top)) / (selfClass.$controlsVolume.find('.volume_static').eq(0).height()));

      }

      volume_setVolume(selfClass, mouseXRelativeToVolume, {
        call_from: "set_by_mousedown"
      });
      isMuted = false;
    }
    if (e.type === 'mouseup') {

      volume_dragging = false;
      cthis.removeClass('volume-dragging');

    }

  }

}




export function volume_setOnlyVisual(selfClass, arg, margs) {
  const o = selfClass.initOptions;

  if (selfClass.$controlsVolume.hasClass('controls-volume-vertical')) {
    selfClass.$controlsVolume.find('.volume_active').eq(0).css({
      'height': (selfClass.$controlsVolume.find('.volume_static').eq(0).height() * arg)
    });
  } else {

    if (selfClass.initOptions.design_skin === 'skin-redlights') {

      selfClass.$controlsVolume.find('.volume_active').eq(0).css({
        'clip-path': 'inset(0% ' + (Math.abs(1 - arg) * 100) + '% 0% 0%)'
      });
    } else {

      selfClass.$controlsVolume.find('.volume_active').eq(0).css({
        'width': (selfClass.$controlsVolume.find('.volume_static').eq(0).width() * arg)
      });
    }
  }


  if (o.design_skin === 'skin-wave' && o.skinwave_dynamicwaves === 'on') {
    selfClass._scrubbar.find('.scrub-bg-img').eq(0).css({
      'transform': 'scaleY(' + arg + ')'
    })
    selfClass._scrubbar.find('.scrub-prog-img').eq(0).css({
      'transform': 'scaleY(' + arg + ')'
    })

  }


  if (localStorage !== null && selfClass.the_player_id) {

    localStorage.setItem('dzsap_last_volume_' + selfClass.the_player_id, arg);

  }

  selfClass.volume_lastVolume = arg;
}




/**
 * outputs a volume from 0 to 1
 * @param {DzsAudioPlayer} selfClass
 * @param {number} arg 0 <-> 1
 * @param pargs
 * @returns {boolean}
 */
export function volume_setVolume(selfClass, arg, pargs) {

  var margs = {

    'call_from': 'default'
  };

  if (pargs) {
    margs = Object.assign(margs, pargs);
  }

  if (arg > 1) {
    arg = 1;
  }
  if (arg < 0) {
    arg = 0;
  }

  var o = selfClass.initOptions;
  const $ = selfClass.$;
  let volume_isSetByUser = false;


  if (margs.call_from === 'from_fake_player_feeder_from_init_loaded') {
    // -- lets prevent call from the init_loaded set_volume if the volume has been changed
    if (selfClass._sourcePlayer) {
      if (o.default_volume !== 'default') {
        volume_isSetByUser = true;
      }
      if (volume_isSetByUser) {
        return false;
      } else {
        volume_isSetByUser = true;
      }
    }
  }

  if (margs.call_from === 'set_by_mouseclick' || margs.call_from === 'set_by_mousemove') {
    volume_isSetByUser = true;
  }

  if (selfClass.audioType === 'youtube') {
    if (selfClass.$mediaNode_ && selfClass.$mediaNode_.setVolume) {
      selfClass.$mediaNode_.setVolume(arg * 100);
    }
  }
  if (selfClass.audioType === 'selfHosted') {
    if (selfClass.$mediaNode_) {

      selfClass.$mediaNode_.volume = arg;
    } else {
      if (selfClass.$mediaNode_) {
        $(selfClass.$mediaNode_).attr('preload', 'metadata');
      }
    }
  }


  volume_setOnlyVisual(selfClass, arg, margs);

  if (selfClass._sourcePlayer) {
    margs.call_from = ('from_fake_player')
    if (selfClass._sourcePlayer.get(0) && selfClass._sourcePlayer.get(0).api_visual_set_volume(arg, margs)) {
      selfClass._sourcePlayer.get(0).api_visual_set_volume(arg, margs);
    }
  }

  if (selfClass._actualPlayer) {
    if (margs.call_from !== ('from_fake_player')) {
      if (margs.call_from === 'from_init_loaded') {

        margs.call_from = ('from_fake_player_feeder_from_init_loaded')
      } else {

        margs.call_from = ('from_fake_player_feeder')
      }
      if (selfClass._actualPlayer && selfClass._actualPlayer.get(0) && selfClass._actualPlayer.get(0).api_set_volume) {
        selfClass._actualPlayer.get(0).api_set_volume(arg, margs);
      }
    }
  }

}


export function getDefaultVolume(o, selfClass){

  let defaultVolume = 1;
  if (o.default_volume === 'default') {
    defaultVolume = 1;
  }

  if (isNaN(Number(o.default_volume)) === false) {
    defaultVolume = Number(o.default_volume);
  } else {
    if (o.default_volume === 'last') {
      if (localStorage !== null && selfClass.the_player_id) {
        if (localStorage.getItem('dzsap_last_volume_' + selfClass.the_player_id)) {
          defaultVolume = localStorage.getItem('dzsap_last_volume_' + selfClass.the_player_id);
        } else {
          defaultVolume = 1;
        }
      } else {
        defaultVolume = 1;
      }
    }
  }

  if (o.volume_from_gallery) {
    defaultVolume = o.volume_from_gallery;
  }

  return defaultVolume;
}
