/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/clipboard-helpers/clipboard-helpers.js":
/*!****************************************************!*\
  !*** ./src/clipboard-helpers/clipboard-helpers.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   copy: () => (/* binding */ copy),
/* harmony export */   copyNode: () => (/* binding */ copyNode)
/* harmony export */ });
/**
 * Copy.
 *
 * Attempts to copy the specified content to the user's clipboard
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function copy(content) {
  return new Promise((res, rej) => {
    // Check for clipboard API
    if (undefined === typeof navigator.clipboard || undefined === typeof navigator.clipboard.writeText) {
      rej('Could not find a valid clipboard library.');
    } else {
      res(navigator.clipboard.writeText(content));
    }
  });
}

/**
 * Copy Node.
 *
 * Attempts to copy the content from the specified node.
 * @since 1.0.0
 * @param {Node} target The DOM Node content to copy.
 * @return {Promise}
 */
function copyNode(target) {
  return new Promise((res, rej) => {
    if (typeof target !== 'object' || typeof target.innerText !== 'string' && typeof target.value !== 'string') {
      rej('Target is not a valid HTML node.');
    }
    let value = '';

    // Try to get an input value if it's set first.
    if (typeof target.value === 'string') {
      value = target.value;

      // Fallback to the innerText
    } else if (typeof target.innerText === 'string') {
      value = target.innerText;

      // If all-else fails, reject.
    } else {
      rej('Could not find valid text to copy');
    }
    res(copy(value));
  });
}


/***/ }),

/***/ "./src/helpers/helpers.js":
/*!********************************!*\
  !*** ./src/helpers/helpers.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   pause: () => (/* binding */ pause),
/* harmony export */   trailingslashit: () => (/* binding */ trailingslashit)
/* harmony export */ });
/**
 * Helper Functions.
 *
 * Generic helper functions specific to AffilaiteWP Affiliate Portal.
 *
 * @author Alex Standiford
 * @since 1.0.0
 */

/**
 * Pause.
 *
 * Delays script execution for the specified amount of time.
 *
 * @since 1.0.0
 * @param length Amount of time to delay, in milliseconds.
 *
 * @returns {Promise} Resolved promise after specified length
 */
function pause(length) {
  return new Promise(resolve => setTimeout(resolve, length));
}

/**
 * Adds a trailing slash to the input value, if it does not already have one.
 *
 * @since 1.0.0
 * @param input {string} The value to append a slash.
 *
 * @returns {string} The appended string.
 */
function trailingslashit(input) {
  if (typeof input !== 'string' || input.endsWith('/')) {
    return input;
  }
  return `${input}/`;
}


/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
/*!********************************!*\
  !*** ./src/creatives/index.js ***!
  \********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _affiliatewp_portal_clipboard_helpers__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @affiliatewp-portal/clipboard-helpers */ "./src/clipboard-helpers/clipboard-helpers.js");
/* harmony import */ var _affiliatewp_portal_helpers__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @affiliatewp-portal/helpers */ "./src/helpers/helpers.js");
/**
 * Creatives.
 *
 * Works with the Creatives page template to handle copying, and modal states.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global creatives
 *
 */

/* eslint @wordpress/no-unused-vars-before-return: "off" */

/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */



/**
 * Creatives screen AlpineJS handler.
 *
 * Works with the Creatives page template to handle copying, and modal states.
 *
 * @since 1.0.0
 * @access private
 * @global creatives
 *
 * @returns object A creatives AlpineJS object.
 */
function creatives() {
  return {
    open: false,
    copying: false,
    /**
     * Copy.
     *
     * Attempts to copy the creative text, and flashes a notification.
     *
     * @since      1.0.0
     * @access     public
     * @param type event. The event this is firing against.
     *
     * @return void
     */
    async copy(event) {
      // Save the original HTML so we can use it to restore the original state of the button.
      const originalHTML = event.target.innerHTML;

      // Attempt to copy the content to the user's clipboard.
      await (0,_affiliatewp_portal_clipboard_helpers__WEBPACK_IMPORTED_MODULE_1__.copyNode)(this.$refs.creativeCode);

      // Flash the text
      this.copying = true;
      event.target.innerText = `🎉 ${(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Copied!', 'affiliatewp-affiliate-portal')}`;
      await (0,_affiliatewp_portal_helpers__WEBPACK_IMPORTED_MODULE_2__.pause)(2000);
      event.target.innerHTML = originalHTML;
      this.copying = false;
    },
    /**
     * Fitler creatives by category.
     *
     * @since  [-NEXT-]
     *
     * @return {void} When we navigate away.
     */
    async filter() {
      var _ref;
      const selector = document.getElementById('filter');
      if (selector.length <= 0) {
        window.console.error('Unable to find <select> for slug');
      }
      if ((_ref = false === selector.value) !== null && _ref !== void 0 ? _ref : false) {
        window.console.error('Unable to get slug from selector value.');
      }

      // All categoriies (no filtering), navigate w/out the slug.
      if ('' === selector.value) {
        // Load the current page w/out the slug selector.
        window.location.href = `${selector.dataset.baseUrl}/`;
        return;
      }

      // Navigat to URL where selector.value is the slug for the filter.
      window.location.href = `${selector.dataset.baseUrl}/${selector.value}`;
    }
  };
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (creatives);
((window.AFFWP = window.AFFWP || {}).portal = window.AFFWP.portal || {}).creatives = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=creatives.js.map