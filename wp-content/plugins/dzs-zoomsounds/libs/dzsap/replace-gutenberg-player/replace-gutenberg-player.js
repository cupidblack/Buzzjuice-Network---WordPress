
function documentReady(callback) {
  new Promise((resolutionFunc, rejectionFunc) => {
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
      resolutionFunc('interactive')
    }
    document.addEventListener('DOMContentLoaded', () => {
      resolutionFunc('DOMContentLoaded')
    }, false);
    setTimeout(() => {
      resolutionFunc('timeout')
    }, 35000);
  }).then(resolution => {
    callback(resolution);
  }).catch(err => {
    callback(err)
  });
}

documentReady(() => {


  const wrapElementInHtml = () => {

  }


  const $wpBlocks = document.querySelectorAll('.wp-block-audio:not(.dzsap-treated)');

  $wpBlocks.forEach(($node) => {

    const src = $node.querySelector('audio').getAttribute('src');
    const preloadMethod = $node.querySelector('audio').getAttribute('preload');
    const isAutoplay = $node.querySelector('audio').autoplay;
    const isLoop = $node.querySelector('audio').loop;
    $node.classList.add('dzsap-treated');

    let playerOptions = {
      loop: isLoop ? 'on': 'off',
      autoplay: isAutoplay ? 'on': 'off',
      preload_method: preloadMethod,
    };



    let $feed_dzsapConfigs = null;
    const $feed_dzsapConfigsAll = document.querySelectorAll('.dzsap-feed--dzsap-configs');
    if ($feed_dzsapConfigsAll.length) {
      $feed_dzsapConfigs = $feed_dzsapConfigsAll[$feed_dzsapConfigsAll.length-1];
    }
    if ($feed_dzsapConfigs) {
      window.dzsap_apconfigs = JSON.parse($feed_dzsapConfigs.innerHTML);
    }






    let $feed_dzsapMainSettings = null;
    const $feed_dzsapMainSettingsAll = document.querySelectorAll('.dzsap-main-settings');
    if ($feed_dzsapMainSettingsAll.length) {
      $feed_dzsapMainSettings = $feed_dzsapMainSettingsAll[$feed_dzsapMainSettingsAll.length-1];
    }
    if ($feed_dzsapMainSettings) {
      window.dzsap_settings = JSON.parse($feed_dzsapMainSettings.innerHTML);
    }




    const view_replace_audio_shortcode = String(window.dzsap_settings.view_replace_audio_shortcode);

    const sanitizedKey = view_replace_audio_shortcode.replace(/[^a-zA-Z0-9\-]+/g, '');


    if(sanitizedKey && window.dzsap_apconfigs[sanitizedKey]){

      playerOptions = Object.assign(window.dzsap_apconfigs[sanitizedKey], playerOptions);
    }





    let newHtml = `<div  class="audioplayer-tobe  apconfig-${sanitizedKey} ${playerOptions.skin_ap} auto-init`;


    if(playerOptions.button_aspect){
      newHtml+=` ${playerOptions.button_aspect}`;
    }

    newHtml+=`"  data-options='${JSON.stringify(playerOptions)}'   data-type="audio"
               data-source="${src}"  >
          </div>`;

    $node.innerHTML = newHtml;


    setTimeout(()=>{

      if (window.jQuery && window.dzsap_init_allGalleries) {
        window.dzsap_init_allGalleries(window.jQuery)
      }
      if (window.jQuery && window.dzsap_init_allPlayers) {
        window.dzsap_init_allPlayers(window.jQuery)
      }
    },10);

  })
})