/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@affiliatewp-portal/alpine-table":
/*!*************************************************!*\
  !*** external ["AFFWP","portal","alpineTable"] ***!
  \*************************************************/
/***/ ((module) => {

module.exports = window["AFFWP"]["portal"]["alpineTable"];

/***/ }),

/***/ "@affiliatewp-portal/sdk":
/*!*****************************************!*\
  !*** external ["AFFWP","portal","sdk"] ***!
  \*****************************************/
/***/ ((module) => {

module.exports = window["AFFWP"]["portal"]["sdk"];

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
/*!****************************!*\
  !*** ./src/table/index.js ***!
  \****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _affiliatewp_portal_alpine_table__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @affiliatewp-portal/alpine-table */ "@affiliatewp-portal/alpine-table");
/* harmony import */ var _affiliatewp_portal_alpine_table__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_affiliatewp_portal_alpine_table__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @affiliatewp-portal/sdk */ "@affiliatewp-portal/sdk");
/* harmony import */ var _affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2__);
/**
 * Table
 *
 * Works with tables to handle data population, pagination, and filtering.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global table
 *
 */

/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */



/**
 * Table handler for visits.
 *
 * Works referrals table to handle data population, pagination, and filtering.
 *
 * @since 1.0.0
 * @access private
 * @global visitsTable
 * @arguments table
 *
 * @returns object The visits table AlpineJS object.
 */
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((args = {}) => {
  const result = {
    ...(_affiliatewp_portal_alpine_table__WEBPACK_IMPORTED_MODULE_1___default()),
    ...args
  };
  result.setupColumns = async function (page) {
    const control = await (0,_affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2__.portalSchemaColumns)(this.type);
    this.schema = control.columns;
    const rows = [control.columns.reduce((acc, column, key) => {
      if (0 === key) {
        acc[column.id] = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Loading...", 'affiliatewp-affiliate-portal');
      } else {
        acc[column.id] = '';
      }
      return acc;
    }, {})];

    // If after constructing the loading state we still have not obtained table rows, actually set the loading state.
    if (true === this.isLoading) {
      this.rows = rows;
    }
  };
  return result;
});
((window.AFFWP = window.AFFWP || {}).portal = window.AFFWP.portal || {}).table = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=table.js.map