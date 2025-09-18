/**
 *
 * @param {DzsAudioPlayer} selfClass
 */
export const view_resizeHandleTreatSkins = (selfClass) => {
  var isResolveThumbHeight = false

  var cthis = selfClass.cthis;
  var o = selfClass.initOptions;
  selfClass.scrubbarWidth = selfClass._scrubbar.outerWidth(false);
  if (o.design_skin === 'skin-default') {
    selfClass.scrubbarWidth = selfClass.cthisWidth;
  }
  if (o.design_skin === 'skin-pro') {
    selfClass.scrubbarWidth = selfClass.cthisWidth;
  }
  if (o.design_skin === 'skin-silver' || o.design_skin === 'skin-aria') {
    selfClass.scrubbarWidth = selfClass.cthisWidth;
    selfClass.scrubbarWidth = selfClass._scrubbar.width();
  }


  if (o.design_skin === 'skin-justthumbandbutton') {
    selfClass.cthisWidth = cthis.children('.audioplayer-inner').outerWidth();
    selfClass.scrubbarWidth = selfClass.cthisWidth;
  }
  if (o.design_skin === 'skin-redlights' || o.design_skin === 'skin-steel') {
    selfClass.scrubbarWidth = selfClass._scrubbar.width();
  }

  if (o.design_skin === 'skin-wave') {
    selfClass.scrubbarWidth = selfClass._scrubbar.outerWidth(false);

    if (selfClass._commentsHolder) {

      selfClass._commentsHolder.css({
        'width': selfClass.scrubbarWidth
      })
      selfClass._commentsHolder.addClass('active');
    }

  }

}
export const view_resizeEmbedded = (selfClass) => {

  var cthis = selfClass.cthis;
  if (window.frameElement) {

    let args = {
      height: cthis.outerHeight()
    };


    if (o.embedded_iframe_id) {

      args.embedded_iframe_id = o.embedded_iframe_id;
    }


    var message = {
      name: "resizeIframe",
      params: args
    };
    window.parent.postMessage(message, '*');
  }
}
