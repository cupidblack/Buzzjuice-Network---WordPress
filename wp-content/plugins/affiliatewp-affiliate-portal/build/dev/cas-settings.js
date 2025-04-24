/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@affiliatewp-portal/portal-form":
/*!************************************************!*\
  !*** external ["AFFWP","portal","portalForm"] ***!
  \************************************************/
/***/ ((module) => {

module.exports = window["AFFWP"]["portal"]["portalForm"];

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
/*!************************************************!*\
  !*** ./src/integrations/cas-settings/index.js ***!
  \************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _affiliatewp_portal_portal_form__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @affiliatewp-portal/portal-form */ "@affiliatewp-portal/portal-form");
/* harmony import */ var _affiliatewp_portal_portal_form__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_affiliatewp_portal_portal_form__WEBPACK_IMPORTED_MODULE_0__);
/**
 * Custom Affiliate Slugs Settings Handler.
 *
 * Works with the settings page template to handle slug validation.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global CASSettings
 *
 */

/**
 * Internal Dependencies
 */


/**
 * Custom Affiliate Slugs Settings screen AlpineJS handler.
 *
 * Works with the settings page template to handle slug validation.
 *
 * @since 1.0.0
 * @access public
 * @global CASSettings
 *
 * @returns object The AlpineJS object.
 */
function settings() {
  const form = _affiliatewp_portal_portal_form__WEBPACK_IMPORTED_MODULE_0___default()();
  return {
    ...form,
    ...{
      /**
       * Section ID.
       *
       * The section ID that contains the form fields.
       *
       * @since 1.0.0
       *
       * @type {string} The section ID
       */
      sectionId: 'custom-affiliate-slugs-settings',
      /**
       * Original Slug
       *
       * The original slug that was provided on page load.
       *
       * @since  1.0.0
       * @access public
       *
       * @type string
       */
      originalSlug: '',
      /**
       * Show Confirm Field.
       *
       * Returns true if the confirm setting field should be visible.
       *
       * @since      1.0.0
       * @access     public
       *
       * @returns {boolean} true if visible, otherwise false.
       */
      showConfirmField() {
        const slug = this.getField('custom-affiliate-slug-setting');
        if (false === slug) {
          return false;
        }
        if (this.originalSlug === slug.value || "" === slug.value) {
          return false;
        }
        return true;
      },
      /**
       * Reset Confirmations.
       *
       * Resets the delete checkbox, the confirm slug, and their validations.
       *
       * @since      1.0.0
       * @access     public
       *
       * @returns {Promise<void>}
       */
      async resetConfirmations() {
        // Reset confirmation values
        this.updateFieldValue("custom-affiliate-slug-confirm-delete", false);
        this.updateFieldValue("custom-affiliate-slug-confirm", '');
        this.isValidating = true;

        // Reset confirmations.
        this.removeErrors(['custom-affiliate-slug-confirm', 'custom-affiliate-slug-confirm-delete']);
        await Promise.all([this.validateControl('custom-affiliate-slug-confirm'), this.validateControl('custom-affiliate-slug-confirm-delete')]);
        this.isValidating = false;
      },
      /**
       * Validate Control.
       *
       * Validates a control by the provided ID, and sets the error if so
       *
       * @since 1.0.0
       * @access public
       * @param {String} id Control ID.
       *
       * @returns {Promise<void>}
       */
      async validateControl(id) {
        const validateControl = form.validateControl.bind(this);
        if ('custom-affiliate-slug-setting' === id) {
          await this.resetConfirmations();
        }
        validateControl(id);
      },
      /**
       * Show Confirm Delete Field.
       *
       * Returns true if the confirm delete setting checkbox should be visible.
       *
       * @since      1.0.0
       * @access     public
       *
       * @returns {boolean} true if visible, otherwise false.
       */
      showConfirmDeleteField() {
        if (true === this.isLoading) {
          return false;
        }
        const slug = this.getField('custom-affiliate-slug-setting');
        if (false === slug) {
          return false;
        }
        if ('' === this.originalSlug || "" !== slug.value) {
          return false;
        }
        return true;
      },
      /**
       * Submit Form.
       *
       * Actions that should be taken when the form is submitted.
       *
       * @since      1.0.0
       * @access     public
       *
       * @returns {Promise<void>}
       */
      async submitForm() {
        const submitForm = form.submitForm.bind(this);
        await submitForm();
        this.resetConfirmations();
        this.resetSlug();
      },
      /**
       * Reset Slug.
       *
       * Resets the original slug value to whatever the current slug setting value is.
       *
       * @since 1.0.0
       *
       * @returns {Promise<void>}
       */
      resetSlug() {
        // Just after setup is complete, get the field value.
        const slug = this.getField('custom-affiliate-slug-setting');
        if (false !== slug) {
          this.originalSlug = slug.value;
        }
      },
      /**
       * Init.
       *
       * Fires when this object is set up.
       *
       * @since      1.0.0
       * @access     public
       *
       * @returns {Promise<void>}
       */
      async init() {
        const init = form.init.bind(this);
        await init();
        this.resetSlug();
      }
    }
  };
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (settings);
((window.AFFWP = window.AFFWP || {}).portal = window.AFFWP.portal || {}).casSettings = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=cas-settings.js.map