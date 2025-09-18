export class DzsUploaderPreview{
  constructor($elem_) {


    const $elem = jQuery($elem_);

    this.$elem = $elem;

    if(!$elem.hasClass('inited')){

      this.init();
    }



    setTimeout(function () {
      $elem.trigger('change');
    }, 500);
  }

  init(){
    const {$elem} = this;

    $elem.addClass('inited');


    $elem.off('change');

    $elem.on('change', function () {


      var inputVal = $elem.val();
      let $previewer = null;

      if ($elem.prev().hasClass('uploader-preview')) {
        $previewer = $elem.prev();
      }

      if ($previewer) {




        if (inputVal && isNaN(Number(inputVal)) == false) {


          var data = {
            action: 'dzs_get_attachment_src',
            id: inputVal
          };


          jQuery.ajax({
            type: "POST",
            url: window.ajaxurl,
            data: data,
            success: function (response) {


              if (response && (response.indexOf('.jpg') > -1 || response.indexOf('.jpeg') > -1)) {

                $previewer.css('background-image', 'url(' + response + ')')
                $previewer.html(' ');
                $previewer.removeClass('empty');
              } else {

                $previewer.html('');
                $previewer.addClass('empty');
              }
            },
            error: function (arg) {
              ;
            }
          });
        } else {

          $previewer.css('background-image', 'url(' + inputVal + ')')
          $previewer.html(' ');
          $previewer.removeClass('empty');


        }

        if (inputVal == '') {

          $previewer.html('');
          $previewer.addClass('empty');
        }


      }
    });
  }
}

export function dzsUploaderTargetInit(){

  jQuery('.uploader-target').each(function (){
    new DzsUploaderPreview(this);
    return this;
  })
}