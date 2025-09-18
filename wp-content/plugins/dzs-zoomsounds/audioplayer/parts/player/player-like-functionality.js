(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.DZSAP_AJAX_ACTION_LIKE=exports.DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=exports.DZSAP_SCRIPT_SELECTOR_KEYBOARD=exports.DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS=exports.DZSAP_CLASS_NAME_AJAX_LIKE=exports.DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS=exports.DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED=exports.DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM=exports.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM=exports.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW=exports.DZSAP_PLAYER_CLASS_LOADED=exports.DZSAP_PLAYER_CLASS_FOOTER_PLAYER=exports.ConstantsDzsAp=void 0;const ConstantsDzsAp={PLAYLIST_TRANSITION_DURATION:300,DEBUG_STYLE_1:"background-color: #aaa022; color: #222222;",DEBUG_STYLE_2:"background-color: #7c3b8e; color: #ffffff;",DEBUG_STYLE_3:"background-color: #3a696b; color: #eeeeee;",DEBUG_STYLE_ERROR:"background-color: #3a696b; color: #eeeeee;",URL_WAVESURFER_HELPER_BACKUP:"https://zoomthe.me/assets/dzsap-wave-generate.js",WAVESURFER_MAX_TIMEOUT:2e4,URL_JQUERY:"https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js",DEBUG_STYLE_PLAY_FUNCTIONS:"background-color: #daffda; color: #222222;",ERRORED_OUT_CLASS:"errored-out",ERRORED_OUT_MAX_ATTEMPTS:5};exports.ConstantsDzsAp=ConstantsDzsAp;const DZSAP_PLAYER_CLASS_FOOTER_PLAYER="dzsap_footer";exports.DZSAP_PLAYER_CLASS_FOOTER_PLAYER="dzsap_footer";const DZSAP_PLAYER_CLASS_LOADED="dzsap-loaded";exports.DZSAP_PLAYER_CLASS_LOADED="dzsap-loaded";const DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW="audioplayer-loaded";exports.DZSAP_PLAYER_CLASS_FOR_STICK_TO_BOTTOM_SHOW="audioplayer-loaded";const DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom";exports.DZSAP_PLAYER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom";const DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom-placeholder";exports.DZSAP_PLAYER_PLACEHOLDER_CLASS_STICK_TO_BOTTOM="dzsap-sticktobottom-placeholder";const DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED="fake";exports.DZSAP_PLAYER_TYPE_FOR_ACCEPTING_FEED="fake";const DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS="last";exports.DZSAP_PLAYER_ATTR_PLAY_FROM_LAST_POS="last";const DZSAP_CLASS_NAME_AJAX_LIKE="btn-like";exports.DZSAP_CLASS_NAME_AJAX_LIKE="btn-like";const DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS="#dzsap-main-settings";exports.DZSAP_SCRIPT_SELECTOR_MAIN_SETTINGS="#dzsap-main-settings";const DZSAP_SCRIPT_SELECTOR_KEYBOARD="#dzsap-keyboard-controls";exports.DZSAP_SCRIPT_SELECTOR_KEYBOARD="#dzsap-keyboard-controls";const DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=500;exports.DZSAP_VIEW_PLAYER_PLAYING_EASED_TIMEOUT=500;const DZSAP_AJAX_ACTION_LIKE="dzsap_submit_like";exports.DZSAP_AJAX_ACTION_LIKE="dzsap_submit_like";
},{}],2:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.documentReady = documentReady;
exports.$es = void 0;
/**
 * v1.0.0
 * @type {esJquery}
 */

let esJquery = class {
  /**
   *
   * @param {string|esJquery} selector
   */
  constructor(selector) {
    if (typeof selector == 'string') {
      this.$el = document.querySelector(selector);
      this.$els = document.querySelectorAll(selector);
    } else {
      this.$el = selector;

      if (isNodeList(selector)) {
        this.$el = selector[0];
      }

      this.$els = [];

      if (isNodeList(selector)) {
        selector.forEach(_el => {
          this.$els.push(_el);
        });
      } else {
        this.$els.push(this.$el);
      }
    }
  }
  /**
   *
   * @param {number} indexNr
   * @returns {esJquery}
   */


  eq(indexNr) {
    if (this.$els.length < indexNr - 1) {
      return $es(this.$els[this.$els.length - 1]);
    }

    return $es(this.$els[indexNr]);
  }

  length() {
    return this.$els.length;
  }

  hide() {
    this.$els.forEach(function (el) {
      el.style.display = 'none';
    });
  }

  clone() {
    // -- @only works on first element
    const cln = this.$el.cloneNode(true);
    this.$el.parentNode.appendChild(cln);
    return cln;
  }

  show() {
    this.$els.forEach(function (el) {
      el.style.display = '';
    });
  }
  /**
   *
   * @param {string} arg
   * @return {esJquery}
   */


  addClass(arg) {
    this.$els.forEach(function (el3) {
      if (el3) {
        el3.classList.add(arg);
      }
    });
    return this;
  }
  /**
   *
   * @param {string} arg
   * @return {esJquery}
   */


  removeClass(arg) {
    this.$els.forEach(function (el3) {
      const classesToRemove = arg.split(' ');
      classesToRemove.forEach(classToRemove => {
        if (el3) {
          el3.classList.remove(classToRemove);
        }
      });
    });
    return this;
  }
  /**
   *
   * @param {string} html
   * @return {esJquery}
   */


  prepend(html) {
    if (typeof html == 'string') {
      const _tempKid = document.createElement("div");

      this.$els.forEach(function (el) {
        try {
          el.appendChild(_tempKid);
          el.insertBefore(_tempKid, el.firstChild);
          _tempKid.outerHTML = html;
        } catch (err) {
          console.error('something went wrong .. ', err, this, el);
        }
      });
    }
  }
  /**
   *
   * @param {number} index
   * @returns {Element}
   */


  get(index) {
    if (index === 0) {
      return this.$el;
    }
  }
  /**
   *
   * @param {function} callback
   */


  each(callback) {
    this.$els.forEach(el => {
      callback.bind(el)($es(el));
    });
  }

  text() {
    if (arguments.length === 0) {
      return this.$el.textContent;
    } else {
      this.$el.textContent = arguments[0];
      return this;
    }
  }
  /**
   *
   * @param {string} selector
   * @return {esJquery}
   */


  find(selector) {
    if (this.$el) {
      return new esJquery(this.$el.querySelectorAll(selector));
    }

    return new esJquery(this.$el);
  }
  /**
   *
   * @param {string} html
   * @return {esJquery}
   */


  append(html) {
    if (typeof html == 'string') {
      const _tempKid = document.createElement("div");

      _tempKid.innerHTML = html;
      this.$els.forEach(function (el) {
        try {
          el.appendChild(_tempKid);
        } catch (err) {
          console.error('something went wrong .. ', err, this, el);
        }
      });
    }
  }
  /**
   *
   * @param {string} actionType
   */


  trigger(actionType) {
    var event = new CustomEvent(actionType, {
      bubbles: true,
      detail: 'event'
    });
    this.$el.dispatchEvent(event);
  }
  /**
   *
   * @param {string} eventName
   * @param {string|function} sel
   * @param {string|function} handler
   * @return {esJquery}
   */


  on(eventName, sel, handler) {
    const isSelTheHandler = typeof sel === 'function';
    const theHandler = isSelTheHandler ? sel : handler;

    if (this.$el) {
      this.$el.addEventListener(eventName, function (event) {
        if (isSelTheHandler) {
          theHandler.call(this.$el, event);
          return;
        }

        let t = event.target;

        while (t && t !== this) {
          if (t.matches(sel)) {
            theHandler.call(t, event);
          }

          t = t.parentNode;
        }
      });
    }

    return $es(this);
  }
  /**
   *
   * @param {string} arg
   * @return {esJquery}
   */


  hasClass(arg) {
    if (this.$el) {
      return this.$el.classList.contains(arg);
    }

    return $es(null);
  }

  html() {
    const attrArgs = arguments;

    if (attrArgs.length === 0) {
      return this.$el.innerHTML;
    }

    if (attrArgs.length === 1) {
      if (this.$el) {
        this.$el.innerHTML = attrArgs[0];
      }

      return this;
    }
  }

  getLast() {
    if (this.$els.length) {
      return $es(this.$els[this.$els.length - 1]);
    }

    return null;
  }

  val() {
    const attrArgs = arguments;

    if (attrArgs.length === 0) {
      return this.$el.value ? this.$el.value : '';
    }

    if (attrArgs.length === 1) {
      this.$els.forEach(function (el) {
        if (el) {
          console.log('arguments - ', attrArgs);
          el.value = attrArgs[0];
        }
      });
    }

    return this;
  }

  attr() {
    const attrArgs = arguments;

    if (attrArgs.length === 0) {
      console.log('no attrArgs.. ');
    }

    if (attrArgs.length === 1) {
      return this.$el.getAttribute(attrArgs[0]);
    }

    if (attrArgs.length === 2) {
      this.$els.forEach(function (el) {
        if (el) {
          el.setAttribute(attrArgs[0], attrArgs[1]);
        }
      });
    }

    return $es(this.$el);
  }

  css() {
    const self = this;
    const attrArgs = arguments;

    if (attrArgs.length === 0) {
      console.log('no attrArgs.. ');
    }

    if (attrArgs.length === 1) {
      return this.$el.getAttribute(attrArgs[0]);
    }

    if (attrArgs.length === 2) {
      this.$els.forEach(function (el) {
        if (el) {
          console.log('toCamel case - ', self._toCamelCase(attrArgs[0]), attrArgs[1]);
          el.style[self._toCamelCase(attrArgs[0])] = attrArgs[1];
        }
      });
      console.log(this.$el);
    }

    return $es(this.$el);
  }

  _toCamelCase(s) {
    return s.replace(/-./g, x => x[1].toUpperCase());
  }

  remove() {
    this.$el.remove();
  }

  parent() {
    if (this.$el) {
      return $es(this.$el.parentNode);
    }

    return $es(null);
  }

  prev() {
    return $es(this.$el.previousElementSibling);
  }
  /**
   *
   * @returns {Element}
   */


  getEl() {
    return this.$el;
  }

  children() {
    return $es(this.$el.childNodes);
  }

  serialize() {
    var form = this.$el;
    var field,
        query = '';

    if (typeof form == 'object' && form.nodeName === "FORM") {
      for (let i = 0; i <= form.elements.length - 1; i++) {
        field = form.elements[i];

        if (field.name && field.type !== 'file' && field.type !== 'reset') {
          if (field.type === 'select-multiple') {
            for (let j = 0; j <= form.elements[i].options.length - 1; j++) {
              if (field.options[j].selected) {
                query += '&' + field.name + "=" + encodeURIComponent(field.options[j].value).replace(/%20/g, '+');
              }
            }
          } else {
            if (field.type !== 'submit' && field.type !== 'button') {
              if (field.type !== 'checkbox' && field.type !== 'radio' || field.checked) {
                query += '&' + field.name + "=" + encodeURIComponent(field.value).replace(/%20/g, '+');
              }
            }
          }
        }
      }
    }

    return query.substr(1);
  }

};

function isNodeList(nodes) {
  var stringRepr = Object.prototype.toString.call(nodes);
  return typeof nodes === 'object' && /^\[object (HTMLCollection|NodeList|Object)\]$/.test(stringRepr) && typeof nodes.length === 'number' && (nodes.length === 0 || typeof nodes[0] === "object" && nodes[0].nodeType > 0);
}
/**
 * jQuery, but in es6
 * @returns {esJquery} esjQuery
 * @param arg
 */


const $es = arg => {
  return new esJquery(arg);
};

exports.$es = $es;

function documentReady(callback) {
  new Promise((resolutionFunc, rejectionFunc) => {
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
      resolutionFunc('interactive');
    }

    document.addEventListener('DOMContentLoaded', () => {
      resolutionFunc('DOMContentLoaded');
    }, false);
    setTimeout(() => {
      resolutionFunc('timeout');
    }, 5000);
  }).then(resolution => {
    callback(resolution);
  }).catch(err => {
    callback(err);
  });
}

window.es_document_ready = documentReady;
/** call ajax function
 * {
    'type':'GET',
    'url':'/',
    'data':{},
    'success':null
  }
 */

window.es_ajax = function (pargs) {
  var margs = {
    'method': 'GET',
    'url': '/',
    'data': {},
    'success': null
  };

  if (pargs) {
    margs = Object.assign(margs, pargs);
  }

  let xhr = new XMLHttpRequest();
  xhr.open(margs.method, margs.url);
  var form_data = new FormData();

  for (var key in margs.data) {
    form_data.append(key, margs.data[key]);
  }

  xhr.send(form_data);
  xhr.addEventListener('load', handle_loaded);

  function handle_loaded(e) {
    if (xhr.status !== 200) {
      if (margs.error) {
        margs.error(e, this);
      }
    } else {
      // show the result
      if (margs.success) {
        margs.success(e, this);
      }
    }
  }
};

window.get_query_arg = function (purl, key) {
  if (purl.indexOf(key + '=') > -1) {
    var regexS = "[?&]" + key + "(.+?)(?=&|$)";
    var regex = new RegExp(regexS);
    var regtest = regex.exec(purl);

    if (regtest != null) {
      if (regtest[1]) {
        return regtest[1].replace(/=/g, '');
      } else {
        return '';
      }
    }
  }
};
/**
 *
 * @param {string} purl
 * @param {string} key
 * @param {string} value
 * @returns {*}
 */


window.add_query_arg = function (purl, key, value) {
  key = encodeURIComponent(key);
  value = encodeURIComponent(value);
  var s = purl;
  var pair = key + "=" + value;
  var r = new RegExp("(&|\\?)" + key + "=[^\&]*");
  s = s.replace(r, "$1" + pair);
  var addition = '';

  if (s.indexOf(key + '=') > -1) {} else {
    if (s.indexOf('?') > -1) {
      addition = '&' + pair;
    } else {
      addition = '?' + pair;
    }

    s += addition;
  }

  if (value === 'NaN') {
    var regex_attr = new RegExp('[\?|\&]' + key + '=' + value);
    s = s.replace(regex_attr, '');
  }

  return s;
};

},{}],3:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.ajax_submit_like = void 0;

var _esjquery = require("../../js_common/_esjquery");

var _constants = require("../../configs/_constants");

const ajax_submit_like = function (argp) {
  var selfClass = this;
  var $ = jQuery; //only handles ajax call + result

  var mainarg = argp;
  var data = {
    action: _constants.DZSAP_AJAX_ACTION_LIKE,
    postdata: mainarg,
    playerid: selfClass.the_player_id,
    playerSource: selfClass.data_source
  };
  selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).addClass('disabled');

  if (selfClass.urlToAjaxHandler || selfClass.cthis.hasClass('is-preview')) {
    $.ajax({
      type: "POST",
      url: selfClass.urlToAjaxHandler,
      data: data,
      success: function (response) {
        selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).addClass('active');
        selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('disabled');
        var auxlikes = selfClass.cthis.find('.counter-likes .the-number').html();
        auxlikes = parseInt(auxlikes, 10);
        auxlikes++;
        selfClass.cthis.find('.counter-likes .the-number').html(auxlikes);
      },
      error: function (arg) {
        selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).addClass('active');
        selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('disabled');
        var auxlikes = selfClass.cthis.find('.counter-likes .the-number').html();
        auxlikes = parseInt(auxlikes, 10);
        auxlikes++;
        selfClass.cthis.find('.counter-likes .the-number').html(auxlikes);
      }
    });
  }
};

exports.ajax_submit_like = ajax_submit_like;

window.dzsap_retract_like = function (argp, e) {
  var mainarg = argp;
  var data = {
    action: 'dzsap_retract_like',
    playerid: argp
  };
  var _t = null;

  if (e) {
    _t = jQuery(e.currentTarget);
  }

  if (window.dzsap_settings && window.dzsap_settings.ajax_url) {
    jQuery.ajax({
      type: "POST",
      url: window.dzsap_settings.ajax_url,
      data: data,
      success: function (response) {
        if (_t) {
          var htmlaux = _t.html();

          _t.html(htmlaux.replace('fa-heart', 'fa-heart-o'));
        }
      },
      error: function (arg) {}
    });
  }
};

const ajax_retract_like = function (argp) {
  var selfClass = this;
  var $ = jQuery; //only handles ajax call + result

  var data = {
    action: 'dzsap_retract_like',
    postdata: argp,
    playerid: selfClass.the_player_id
  };
  selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).addClass('disabled');

  if (selfClass.urlToAjaxHandler) {
    $.ajax({
      type: "POST",
      url: selfClass.urlToAjaxHandler,
      data: data,
      success: function (response) {
        selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('active');
        selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('disabled');
        var auxlikes = selfClass.cthis.find('.counter-likes .the-number').html();
        auxlikes = parseInt(auxlikes, 10);
        auxlikes--;
        selfClass.cthis.find('.counter-likes .the-number').html(auxlikes);
      },
      error: function (arg) {
        selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('active');
        selfClass.cthis.find(`.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`).removeClass('disabled');
        var auxlikes = selfClass.cthis.find('.counter-likes .the-number').html();
        auxlikes = parseInt(auxlikes, 10);
        auxlikes--;
        selfClass.cthis.find('.counter-likes .the-number').html(auxlikes);
      }
    });
  }
};

function initPlayersForLike() {
  (0, _esjquery.$es)('.audioplayer:not(.ap-like-btn-inited)').each($arg => {
    $arg.addClass('ap-like-btn-inited');
    /** {DzsAudioPlayer} */

    const $selfInstance = $arg.$el.SelfInstance;
    $selfInstance.cthis.off('click', `.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`);
    $selfInstance.cthis.on('click', `.${_constants.DZSAP_CLASS_NAME_AJAX_LIKE}`, handleClickLike);

    function handleClickLike() {
      var _t = jQuery(this);

      if ($selfInstance.cthis.has(_t).length === 0) {
        return;
      }

      if (_t.hasClass('active')) {
        ajax_retract_like.bind($selfInstance)();
      } else {
        ajax_submit_like.bind($selfInstance)();
      }
    }
  });
}

window.dzsap_submit_like = function (argp, e) {
  var mainarg = argp;
  var data = {
    action: _constants.DZSAP_AJAX_ACTION_LIKE,
    playerid: argp
  };
  var _t = null;

  if (e) {
    _t = jQuery(e.currentTarget);
  }

  if (window.dzsap_settings && window.dzsap_settings.ajax_url) {
    jQuery.ajax({
      type: "POST",
      url: window.dzsap_settings.ajax_url,
      data: data,
      success: function (response) {
        if (_t) {
          var htmlaux = _t.html();

          _t.html(htmlaux.replace('fa-heart-o', 'fa-heart'));
        }
      },
      error: function (arg) {}
    });
  }
};

const DELAY_WAIT_FOR_AP_INIT = 500;
(0, _esjquery.documentReady)(() => {
  setTimeout(() => {
    const $ = jQuery;
    $(document).on('click', '.dzsap-like-but', function (e) {
      var _t = $(this);

      var playerid = _t.attr('data-post_id');

      if (playerid && playerid != '0') {} else {
        if (_t.parent().parent().parent().parent().parent().hasClass('audioplayer')) {
          playerid = _t.parent().parent().parent().parent().parent().attr('data-feed-playerid');
        }
      }

      window.dzsap_submit_like(playerid, e);

      _t.removeClass('dzsap-like-but').addClass('dzsap-retract-like-but');

      return false;
    });
    $(document).on('click', '.dzsap-retract-like-but', function (e) {
      var _t = $(this);

      var playerid = _t.attr('data-post_id');

      if (playerid && playerid != '0') {} else {
        if (_t.parent().parent().parent().parent().parent().hasClass('audioplayer')) {
          playerid = _t.parent().parent().parent().parent().parent().attr('data-feed-playerid');
        }
      }

      window.dzsap_retract_like(playerid, e);

      _t.addClass('dzsap-like-but').removeClass('dzsap-retract-like-but');

      return false;
    });
    initPlayersForLike();
  }, DELAY_WAIT_FOR_AP_INIT);
});

},{"../../configs/_constants":1,"../../js_common/_esjquery":2}]},{},[3])


//# sourceMappingURL=player-like-functionality.js.map