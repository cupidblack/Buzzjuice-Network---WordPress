export function dzsap_initDzsaApPlayerPreview(){


    if (get_query_arg(window.location.href, 'dzsap_preview_player')) {
      setTimeout(() => {

        // /**
        //  *
        //  * @param {HTMLElement} canvas
        //  */
        // function renderCanvas(canvas) {
        //   canvas.classList.add('zoomsounds-canvas-for-preview')
        //   document.body.prepend(canvas);
        // }
        // window.html2canvas(document.querySelector(".wrap-for-player-preview")).then(renderCanvas);
      }, 3000);
    }

}