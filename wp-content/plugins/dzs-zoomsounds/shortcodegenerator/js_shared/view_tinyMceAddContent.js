
export function tinymce_add_content(arg) {


  if (top === window) {
    jQuery('.shortcode-output').text(arg);
  } else {


    if (top.dzsap_widget_shortcode) {
      top.dzsap_widget_shortcode.val(arg);

      top.dzsap_widget_shortcode = null;

      if (top.close_zoombox2) {
        top.close_zoombox2();
      }
    } else {

      if (typeof (top.dzsap_receiver) == 'function') {
        top.dzsap_receiver(arg);
      }
    }

  }
}
