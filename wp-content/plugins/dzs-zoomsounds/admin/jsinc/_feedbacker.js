var _feedbacker = null;
export const feedbacker_init = () => {

  var $ = jQuery;
  _feedbacker = $('.feedbacker');
  _feedbacker.fadeOut('fast');
}
export const feedbacker_show_message = (arg) => {

  _feedbacker.html(arg);
  _feedbacker.fadeIn('fast').delay(2000).fadeOut('fast');
}
