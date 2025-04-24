/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
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
  !*** ./src/storage/storage.js ***!
  \********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
const storage = {};

/**
 * Storage instance.
 *
 * Provides access to the storage.
 *
 * @type {{set(*, *): *, get(*, *=): boolean|*, remove(*=): boolean}}
 */
const store = {
  /**
   * Set.
   *
   * Sets a value to storage.
   *
   * @param key The key to set
   * @param value The value to set
   * @returns {*} The value
   */
  set(key, value) {
    storage[key] = value;
    return storage[key];
  },
  /**
   * Get.
   *
   * Retrieves an item from storage, if possible.
   *
   * @param key
   * @param fallback
   * @returns {boolean|*}
   */
  get(key, fallback = false) {
    return undefined === storage[key] ? fallback : storage[key];
  },
  /**
   * Remove.
   *
   * Removes an item from storage, if it exists.
   *
   * @param key
   * @returns {*|boolean}
   */
  remove(key) {
    const value = this.get(key);
    if (undefined !== value) {
      delete storage[key];
    }
    return value;
  }
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (store);
((window.AFFWP = window.AFFWP || {}).portal = window.AFFWP.portal || {}).storage = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=storage.js.map