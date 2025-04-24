/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@affiliatewp-portal/alpine-chart":
/*!*************************************************!*\
  !*** external ["AFFWP","portal","alpineChart"] ***!
  \*************************************************/
/***/ ((module) => {

module.exports = window["AFFWP"]["portal"]["alpineChart"];

/***/ }),

/***/ "@affiliatewp-portal/sdk":
/*!*****************************************!*\
  !*** external ["AFFWP","portal","sdk"] ***!
  \*****************************************/
/***/ ((module) => {

module.exports = window["AFFWP"]["portal"]["sdk"];

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
  !*** ./src/chart/index.js ***!
  \****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _affiliatewp_portal_alpine_chart__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @affiliatewp-portal/alpine-chart */ "@affiliatewp-portal/alpine-chart");
/* harmony import */ var _affiliatewp_portal_alpine_chart__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_affiliatewp_portal_alpine_chart__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @affiliatewp-portal/sdk */ "@affiliatewp-portal/sdk");
/* harmony import */ var _affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_1__);
/**
 * Internal dependencies
 */


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (args => {
  const result = {
    ...(_affiliatewp_portal_alpine_chart__WEBPACK_IMPORTED_MODULE_0___default()),
    ...args
  };

  // Fetch datasets via REST before
  result.fetchPortalData = async function (dateQueryType) {
    return new Promise(async (res, rej) => {
      const control = await (0,_affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_1__.portalSchemaRows)(this.type, {
        range: dateQueryType
      });
      this.label = control.x_label_key;
      res(control.rows.map(row => {
        return {
          label: row.title,
          borderColor: row.color,
          data: row.data,
          borderWidth: 3,
          backgroundColor: 'transparent'
        };
      }));
    });
  };
  return result;
});
((window.AFFWP = window.AFFWP || {}).portal = window.AFFWP.portal || {}).chart = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=chart.js.map