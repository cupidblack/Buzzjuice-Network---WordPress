/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

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

/***/ "@affiliatewp-portal/alpine-form":
/*!************************************************!*\
  !*** external ["AFFWP","portal","alpineForm"] ***!
  \************************************************/
/***/ ((module) => {

module.exports = window["AFFWP"]["portal"]["alpineForm"];

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
/*!****************************************!*\
  !*** ./src/portal-form/portal-form.js ***!
  \****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _affiliatewp_portal_alpine_form__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @affiliatewp-portal/alpine-form */ "@affiliatewp-portal/alpine-form");
/* harmony import */ var _affiliatewp_portal_alpine_form__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_affiliatewp_portal_alpine_form__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _affiliatewp_portal_helpers__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @affiliatewp-portal/helpers */ "./src/helpers/helpers.js");
/* harmony import */ var _affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @affiliatewp-portal/sdk */ "@affiliatewp-portal/sdk");
/* harmony import */ var _affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2__);
/**
 * Form.
 *
 * Works with forms to handle data validation and other form interactions.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global form
 *
 */

/**
 * Internal Dependencies
 */




/**
 * Form handler.
 *
 * Works with forms to handle field validation, and submission.
 *
 * @param {string} sectionId The Section ID from which the fields should be fetched.
 *
 * @since 1.0.0
 * @access private
 * @global form
 *
 * @returns object The form AlpineJS object.
 */
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (sectionId => {
  return {
    ...(_affiliatewp_portal_alpine_form__WEBPACK_IMPORTED_MODULE_0___default()),
    ...{
      /**
       * Section ID.
       *
       * The section ID that contains the form fields.
       *
       * @since  1.0.0
       * @access public
       *
       * @type {string} The section ID
       */
      sectionId,
      /**
       * Is Loading.
       *
       * Set to true if this item is loading.
       *
       * @since  1.0.0
       * @access public
       *
       * @type {boolean} True if loading, otherwise false.
       */
      isLoading: true,
      /**
       * Is Validating.
       *
       * Set to true if this item is validating fields.
       *
       * @since  1.0.0
       * @access public
       *
       * @type {boolean} True if loading, otherwise false.
       */
      isValidating: false,
      /**
       * Is Submitting.
       *
       * Set to true if this item is submitting the form.
       *
       * @since  1.0.0
       * @access public
       *
       * @type {boolean} True if loading, otherwise false.
       */
      isSubmitting: false,
      /**
       * Showing success message.
       *
       * Whether or not the success message is showing (during submission).
       *
       * @since  1.0.0
       * @access public
       *
       * @type boolean
       */
      showingSuccessMessage: false,
      /**
       * Export Fields.
       *
       * Converts Alpine form fields to key => value pairs for REST submissions & validation.
       *
       * @since  1.0.0
       * @access public
       *
       * @returns object Object of values keyed by the field ID.
       */
      exportFields() {
        return this.fields.reduce((acc, field) => {
          acc[field.id] = field.value;
          return acc;
        }, {});
      },
      /**
       * Has Validations.
       *
       * Returns true if the specified control has validations.
       *
       * @since  1.0.0
       * @access public
       *
       * @param {String} id Control ID.
       *
       * @returns {boolean} True if the field has validations, otherwise false.
       */
      hasValidations(id) {
        const field = this.getField(id);
        if (false === field) {
          return false;
        }
        return true === field.hasValidations;
      },
      /**
       * Validate Control.
       *
       * Validates a control by the provided ID, and sets the error if so
       *
       * @since  1.0.0
       * @access public
       *
       * @param {String} id Control ID.
       *
       * @returns {Promise<void>}
       */
      async validateControl(id) {
        // Bail early if this field has no validations.
        if (false === this.hasValidations(id)) {
          return;
        }
        this.isValidating = true;
        const response = await (0,_affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2__.validateControl)(id, this.exportFields());

        // Get the passed IDs.
        const passed = response.validations.passed.map(validation => validation.id);

        // Remove all errors that passed this time
        this.removeErrors(passed);

        // Add any errors that failed.
        this.addErrors(response.validations.failed);
        this.isValidating = false;
      },
      /**
       * Setup Submit.
       *
       * Sets up the default directives for the submit button. Intended to be called using Alpine's x-spread directive.
       *
       * @since  1.0.0
       * @access public
       *
       * @returns {object} Directives that should be applied to the submit button by default.
       */
      setupSubmit() {
        return {
          ['x-bind:disabled']() {
            return this.hasErrors() || this.isLoading || this.isValidating || this.isSubmitting;
          }
        };
      },
      /**
       * Default Directives.
       *
       * Sets up the default directives for a field.
       *
       * @since  1.0.0
       * @access public
       *
       * @param {string} id The control ID from which directives should be constructed.
       * @param {string} type The input type, such as text, or checkbox.
       *
       * @returns {object} Directives that should be applied to all inputs by default.
       */
      setupField(id, type = '', value = '') {
        // Bind the parent function to this instance. this is kind-of like running parent::function() in PHP.
        const setupControl = _affiliatewp_portal_alpine_form__WEBPACK_IMPORTED_MODULE_0___default().setupField.bind(this);

        // Get the default directives
        const parentDirectives = setupControl(id, type, value);
        const parentInput = parentDirectives['x-on:input'].bind(this);

        // A list of validations that should not have an input delay.
        const hasNoDelay = ['checkbox', 'select', 'radio'].includes(type);

        // AP-specific directives.
        const additionalDirectives = {
          ['x-on:input'](event) {
            parentInput(event);

            // Run field validations.
            if (hasNoDelay) {
              this.validateControl(id);
            } else {
              const fieldIndex = this.fields.findIndex(field => field.id === id);

              // Maybe reset the timeout, if it is already set.
              if (undefined !== this.fields[fieldIndex].validating) {
                window.clearTimeout(this.fields[fieldIndex].validating);
              }
              this.isLoading = true;
              this.fields[fieldIndex].validating = window.setTimeout(() => {
                this.validateControl(id);
                delete this.fields[fieldIndex].validating;
                this.isLoading = false;
              }, 200);
            }
          },
          ['x-on:blur']() {
            this.validateControl(id);
            this.isLoading = false;
          }
        };

        // Spread (combine) the two objects into a single object.
        return {
          ...parentDirectives,
          ...additionalDirectives
        };
      },
      /**
       * Submit Form.
       *
       * Actions that should be taken when the form is submitted.
       *
       * @since  1.0.0
       * @access public
       *
       * @returns {Promise<void>}
       */
      async submitForm() {
        this.isSubmitting = true;
        const response = await (0,_affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2__.submitSection)(this.sectionId, this.exportFields());
        // remove all errors.
        this.removeErrors(response.validations.passed);
        this.addErrors(response.validations.failed);
        this.isSubmitting = false;
        if (!this.hasErrors()) {
          this.flashSuccessMessage();
        }
      },
      /**
       * Flash Success Message.
       *
       * Flashes the success message.
       *
       * @since  1.0.0
       * @access public
       *
       * @returns {Promise<void>}
       */
      async flashSuccessMessage() {
        this.showingSuccessMessage = true;
        await (0,_affiliatewp_portal_helpers__WEBPACK_IMPORTED_MODULE_1__.pause)(1000);
        this.showingSuccessMessage = false;
      },
      /**
       * Sets up the form.
       *
       * @since  1.0.0
       * @access public
       *
       * @returns {Promise<void>}
       */
      setupForm() {
        return {
          async ['x-on:submit'](event) {
            event.preventDefault();
            this.submitForm();
          }
        };
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
        // simulate a fetch request.
        const response = await (0,_affiliatewp_portal_sdk__WEBPACK_IMPORTED_MODULE_2__.portalSectionFields)(this.sectionId);
        this.fields = response.fields.map(field => {
          if ('checkbox' === field.type) {
            if ('on' === field.value) {
              field.value = true;
            }
            if ('off' === field.value) {
              field.value = false;
            }
          }
          return field;
        });
        this.isLoading = false;
      }
    }
  };
});
((window.AFFWP = window.AFFWP || {}).portal = window.AFFWP.portal || {}).portalForm = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=portal-form.js.map