/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/url":
/*!*****************************!*\
  !*** external ["wp","url"] ***!
  \*****************************/
/***/ ((module) => {

module.exports = window["wp"]["url"];

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
/*!************************!*\
  !*** ./src/sdk/sdk.js ***!
  \************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   portalAffiliate: () => (/* binding */ portalAffiliate),
/* harmony export */   portalControl: () => (/* binding */ portalControl),
/* harmony export */   portalDataset: () => (/* binding */ portalDataset),
/* harmony export */   portalSchemaColumns: () => (/* binding */ portalSchemaColumns),
/* harmony export */   portalSchemaRows: () => (/* binding */ portalSchemaRows),
/* harmony export */   portalSection: () => (/* binding */ portalSection),
/* harmony export */   portalSectionFields: () => (/* binding */ portalSectionFields),
/* harmony export */   portalSettings: () => (/* binding */ portalSettings),
/* harmony export */   portalView: () => (/* binding */ portalView),
/* harmony export */   submitSection: () => (/* binding */ submitSection),
/* harmony export */   validateControl: () => (/* binding */ validateControl)
/* harmony export */ });
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_0__);
/**
 * AffiliateWP Affiliate Portal SDK.
 *
 * Functions for interacting with AffiliateWP Affiliate Portal REST endpoints.
 *
 * @author Alex Standiford
 * @since 1.0.0
 */

/**
 * WordPress dependencies
 */


/**
 * Portal Affiliate Endpoint.
 *
 * Fetches the data for the provided affiliate.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalAffiliate(args = {}) {
  let affiliate;
  if (undefined === args.affiliate) {
    affiliate = affwp_portal_vars.affiliate_id;
  } else {
    affiliate = args.affiliate;
    delete args.affiliate;
  }
  return AFFWP.portal.core.fetch({
    path: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(`/affwp/v1/affiliates/${affiliate}`, args),
    skipAffiliateId: true,
    cacheResult: true
  });
}

/**
 * Portal Settings Endpoint.
 *
 * Fetches the affiliate portal settings data.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSettings() {
  return AFFWP.portal.core.fetch({
    path: '/affwp/v2/portal/settings',
    cacheResult: true
  });
}

/**
 * Portal Referrals Endpoint.
 *
 * Fetches referrals.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSchemaRows(type, args = {}) {
  const requestArgs = {
    ...args,
    ...{
      rows: true
    }
  };

  // Translate page into offset
  if (requestArgs.page) {
    requestArgs.offset = requestArgs.number ? (requestArgs.page - 1) * requestArgs.number : 20;
  }
  return AFFWP.portal.core.fetch({
    path: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(`/affwp/v2/portal/controls/${type}`, requestArgs),
    cacheResult: true
  });
}

/**
 * Portal Referrals Endpoint.
 *
 * Fetches referrals.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSchemaColumns(type) {
  return AFFWP.portal.core.fetch({
    path: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(`/affwp/v2/portal/controls/${type}`, {
      columns: true
    }),
    cacheResult: true
  });
}

/**
 * Portal Datasets Endpoint.
 *
 * Fetches datasets.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalDataset(args = {}) {
  return AFFWP.portal.core.fetch({
    path: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(`/affwp/v2/portal/datasets`, args),
    cacheResult: true
  });
}

/**
 * Portal View Endpoint.
 *
 * Fetches Portal view.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalView(view) {
  return AFFWP.portal.core.fetch({
    path: `/affwp/v2/portal/views/${view}`,
    cacheResult: true
  });
}

/**
 * Portal Section Endpoint.
 *
 * Fetches a section.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSection(section) {
  return AFFWP.portal.core.fetch({
    path: `/affwp/v2/portal/sections/${section}`,
    cacheResult: true
  });
}

/**
 * Portal Section Endpoint.
 *
 * submits a section form.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSectionFields(section) {
  return AFFWP.portal.core.fetch({
    path: `/affwp/v2/portal/sections/${section}/fields`
  });
}

/**

 * Portal Section Endpoint.
 *
 * submits a section form.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function submitSection(section, data) {
  return AFFWP.portal.core.fetch({
    method: 'POST',
    path: `/affwp/v2/portal/sections/${section}/submit`,
    data
  });
}

/**
 * Portal Controls Endpoint.
 *
 * Fetches a single control.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalControl(control) {
  return AFFWP.portal.core.fetch({
    path: `/affwp/v2/portal/controls/${control}`,
    cacheResult: true
  });
}

/**
 * Validate Control.
 *
 * Runs field validations against a single control.
 *
 * @since 1.0.0
 * @access protected
 *
 * @param {string} control The control ID
 * @param {object} data The data to validate, keyed by the field ID
 * @returns {object} The control API response.
 */
function validateControl(control, data) {
  return AFFWP.portal.core.fetch({
    path: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(`/affwp/v2/portal/controls/${control}`, {
      validate: true,
      data
    }),
    cacheResult: true
  });
}

((window.AFFWP = window.AFFWP || {}).portal = window.AFFWP.portal || {}).sdk = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=sdk.js.map