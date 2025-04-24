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

/***/ "./src/url-helpers/url-helpers.js":
/*!****************************************!*\
  !*** ./src/url-helpers/url-helpers.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   appendUrl: () => (/* binding */ appendUrl),
/* harmony export */   authoritiesMatch: () => (/* binding */ authoritiesMatch),
/* harmony export */   constructUrl: () => (/* binding */ constructUrl),
/* harmony export */   getPage: () => (/* binding */ getPage),
/* harmony export */   getStablePath: () => (/* binding */ getStablePath),
/* harmony export */   hasValidProtocol: () => (/* binding */ hasValidProtocol),
/* harmony export */   paginateUrl: () => (/* binding */ paginateUrl),
/* harmony export */   validateUrl: () => (/* binding */ validateUrl)
/* harmony export */ });
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _affiliatewp_portal_helpers__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @affiliatewp-portal/helpers */ "./src/helpers/helpers.js");
/**
 * URL Helper Functions.
 *
 * Helper functions that extend the @wordpress/url library.
 *
 * @author Alex Standiford
 * @since 1.0.0
 */

/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */

const paginationRegex = /\/([^\/a-zA-Z-_]+)\/?$/;

/**
 * Append URL.
 *
 * Appends the provided path to the end of the provided URL's path.
 *
 * @since      1.0.0
 * @access     protected
 * @param {string} url The URL to append to.
 * @param {string} append The string to append to the URL.
 *
 * @return {string} URL with path appended.
 */
function appendUrl(url, append) {
  // Remove the slash at the beginning of append, if it was mistakenly added.
  if (append.startsWith('/')) {
    append = append.substr(1);
  }

  // Define the parts of the URL.
  return constructUrl(url, ['protocol', 'authority', 'path', (0,_affiliatewp_portal_helpers__WEBPACK_IMPORTED_MODULE_1__.trailingslashit)(append), 'querystring', 'fragment']);
}

/**
 * Construct URL.
 *
 * Constructs a URL from a URL and specified parts.
 *
 * @since      1.0.0
 * @access     protected
 * @param {string} url The url to construct parts from.
 * @param {array} parts List of parts to construct, in the order they should be constructed.
 *                This can be any of the following: 'protocol', 'authority', 'path', 'querystring', 'fragment'
 *                If an arbitrary string is passed, that string will be inserted in the URL.
 *
 * @return {string} constructed URL
 */
function constructUrl(url, parts) {
  const urlObject = {
    /**
     * Get Protocol.
     * Retrieves the protocol from the URL.
     *
     * @since 1.0.0
     * @returns {string}
     */
    getProtocol: () => {
      return `${(0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getProtocol)(url)}//`;
    },
    /**
     * Get Authority.
     * Retrieves the authority from the URL.
     *
     * @since 1.0.0
     * @returns {string}
     */
    getAuthority: () => {
      return (0,_affiliatewp_portal_helpers__WEBPACK_IMPORTED_MODULE_1__.trailingslashit)((0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getAuthority)(url));
    },
    /**
     * Get Path.
     * Retrieves the path from the URL.
     *
     * @since 1.0.0
     * @returns {string}
     */
    getPath: () => {
      return (0,_affiliatewp_portal_helpers__WEBPACK_IMPORTED_MODULE_1__.trailingslashit)((0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getPath)(url));
    },
    /**
     * Get Query String.
     * Retrieves the querytstring from the URL.
     *
     * @since 1.0.0
     * @returns {string}
     */
    getQuerystring: () => {
      const queryString = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getQueryString)(url);
      return queryString ? `?${(0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getQueryString)(url)}` : '';
    },
    /**
     * Get Fragment.
     * Retrieves the fragment from the URL.
     *
     * @since 1.0.0
     * @returns {string}
     */
    getFragment: () => {
      return (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getFragment)(url);
    }
  };
  return parts.reduce((acc, part) => {
    const isValidUrlPart = ['protocol', 'authority', 'path', 'querystring', 'fragment'].includes(part.toLowerCase());
    if (!isValidUrlPart && typeof part === 'string') {
      return acc + part;
    } else if (!isValidUrlPart) {
      return acc;
    }
    const callback = urlObject['get' + part.charAt(0).toUpperCase() + part.slice(1).toLowerCase()];
    const urlPart = callback();
    if (undefined === urlPart) {
      return acc;
    }
    return acc + urlPart;
  }, '');
}

/**
 * Authorities Match.
 *
 * Returns true if the provided url matches the specified base authority.
 *
 * @since      1.0.0
 * @access     protected
 * @param url {string} The URL to check.
 * @param baseAuthority {string} The base authority to check against.
 *
 * @return {boolean} true if authorities match, otherwise false.
 */
function authoritiesMatch(url, baseAuthority) {
  const inputAuthority = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getAuthority)(url);

  // Return true if the authorities match.
  if (inputAuthority === baseAuthority) {
    return true;
  }

  // Return true if inputAuthority is a subdomain of baseAuthority.
  const regex = new RegExp("\\w\\." + baseAuthority + "$");
  return regex.test(inputAuthority);
}

/**
 * Has valid protocol.
 *
 * Returns true if the provided URL has a valid URL protocol for a typical web request.
 *
 * @since      1.0.0
 * @access     protected
 * @param url {string} The URL to check.
 *
 * @returns {boolean} true if valid, otherwise false.
 */
function hasValidProtocol(url) {
  const protocol = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getProtocol)(url);
  return ['https:', 'http:'].includes(protocol);
}

/**
 * Get Page.
 *
 * Fetches the page from the provided URL
 *
 * @since     1.0.0
 * @access    protected
 * @param url {string} The URL from which the page number should be retrieved.
 *
 * @returns {string} The page number
 */
function getPage(url) {
  const path = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getPath)(url);
  const search = path.match(paginationRegex);

  // If no page was found, we are on page 1.
  if (null === search) {
    return '1';
  }

  // Otherwise, get the page number.
  return search[1];
}

/**
 * Paginate URL.
 *
 * Appends the URL with the provided query args, and formats for pretty pagination.
 *
 * @since     1.0.0
 * @access    protected
 * @param url {string} The URL to paginate.
 * @param args {object} List of query param values keyed by their key.
 *                      If a page is passed, it will be formatted for pagination.
 *
 * @returns {string} The page number
 */
function paginateUrl(url, args) {
  getPage(url);
  const path = (0,_affiliatewp_portal_helpers__WEBPACK_IMPORTED_MODULE_1__.trailingslashit)((0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getPath)(url)).replace(paginationRegex, '/');

  // Strip out any existing pagination from the path.
  const urlParts = ['protocol', 'authority', path];

  // Append the page number, if we have a page to append.
  if (args.page) {
    if (args.page > 1) {
      urlParts.push(args.page + '/');
    }
    delete args.page;
  }

  // Construct the URL using the provided URL parts.
  const result = constructUrl(url, urlParts);

  // Append query args to the resulting URL.
  return (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(result, args);
}

/**
 * Validates a given URL.
 *
 * Simple validation of an url.
 *
 * @since     1.0.0
 * @access    protected
 * @param url {string} The URL to validate.
 *
 * @returns {bool}
 */
function validateUrl(url) {
  return /\.\w\w.*/.test(url);
}

/**
 * Given a path, returns a normalized path where equal query parameter values
 * will be treated as identical, regardless of order they appear in the original
 * text.
 *
 * @param {string} path Original path.
 *
 * @return {string} Normalized path.
 */
function getStablePath(path) {
  const splitted = path.split('?');
  const query = splitted[1];
  const base = splitted[0];
  if (!query) {
    return base;
  }

  // 'b=1&c=2&a=5'
  return base + '?' + query
  // [ 'b=1', 'c=2', 'a=5' ]
  .split('&')
  // [ [ 'b, '1' ], [ 'c', '2' ], [ 'a', '5' ] ]
  .map(entry => entry.split('='))
  // [ [ 'a', '5' ], [ 'b, '1' ], [ 'c', '2' ] ]
  .sort((a, b) => a[0].localeCompare(b[0]))
  // [ 'a=5', 'b=1', 'c=2' ]
  .map(pair => pair.join('='))
  // 'a=5&b=1&c=2'
  .join('&');
}


/***/ }),

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
/*!***********************************************!*\
  !*** ./src/integrations/direct-link/index.js ***!
  \***********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _affiliatewp_portal_url_helpers__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @affiliatewp-portal/url-helpers */ "./src/url-helpers/url-helpers.js");
/**
 * Direct Link Tracking view Handler.
 *
 * Works with the Direct Link Tracking screen in the affiliate portal to handle link operations.
 *
 * @since 1.0.0
 *
 */

/**
 * Internal dependencies
 */


/**
* Direct Link Tracking view screen AlpineJS handler.
*
* Works with the Direct Link Tracking screen in the affiliate portal to handle link operations.
*
* @since 1.0.0
* @access public
*
* @returns object The AlpineJS object.
*/
function settings() {
  return {
    /**
     * Is Loading.
     *
     * Determines if the app is loading.
     *
     * @since  1.0.0
     * @access public
     *
     * @type boolean
     */
    isLoading: false,
    /**
     * Is form valid.
     *
     * Determines if the form is valid.
     *
     * @since  1.0.0
     * @access public
     *
     * @type boolean
     */
    valid: false,
    /**
     * Current links Items.
     *
     * Array containing the current affiliate direct links.
     *
     * @since  1.0.0
     * @access public
     *
     * @type array
     */
    links: [],
    /**
     * Max number of links allowed.
     *
     * The max number of links an affiliate can register.
     *
     * @since  1.0.0
     * @access public
     *
     * @type int
     */
    maxLinks: 0,
    /**
     * Rejected domains.
     *
     * HTML string with list of rejected domains to show to the affiliate.
     *
     * @since  1.0.4
     * @access public
     *
     * @type string
     */
    rejected: '',
    /**
     * Showing success message.
     *
     * Shows success message when the form is submitted
     *
     * @since  1.0.0
     * @access public
     *
     * @type boolean
     */
    showingSuccessMessage: false,
    /**
     * Shows update notice.
     *
     * Shows notice to the user when links were updated.
     *
     * @since  1.0.0
     * @access public
     *
     * @type boolean
     */
    showUpdateNotice: false,
    /**
     * Shows invalid submission.
     *
     * Shows to the user when invalid links were submitted.
     *
     * @since  1.0.0
     * @access public
     *
     * @type boolean
     */
    showInvalidSubmission: false,
    /**
     * Is dismissing notice.
     *
     * Determines if the app is dismissing the notice.
     *
     * @since  1.0.0
     * @access public
     *
     * @type boolean
     */
    isDismissingNotice: false,
    /**
     * Init.
     *
     * Initializes the AlpineJS instance.
     *
     * @since      1.0.0
     * @access     public
     *
     * @return void
     */
    async init() {
      // Fetch list of direct links.
      const response = await AFFWP.portal.core.fetch({
        path: 'affwp/v2/portal/integrations/direct-link-tracking/get-links',
        cacheResult: false
      });

      // Add some extra flags to each link.
      this.links = response.links.map(link => {
        link.timer = false;
        link.isValidatingUrl = false;
        link.isRemoving = false;
        return link;
      });
      this.rejected = response.rejected.join('<br>');

      // Add one default domain if no links saved.
      if (this.links.length === 0) {
        this.addDomain();
      }
      this.checkValid();
      this.isLoading = false;
    },
    /**
     * Adds a new direct link domain.
     *
     * Adds a new domain to the list of links.
     *
     * @since  1.0.0
     * @access public
     *
     * @returns void
     */
    addDomain() {
      if (this.links.length + 1 <= this.maxLinks) {
        this.links.push({
          url_id: '',
          url: '',
          errors: {}
        });

        // New link is empty so the form should be invalid.
        this.valid = false;
      }
    },
    /**
     * Get Link Object.
     *
     * Attempts to retrieve the Link object from the list of links.
     *
     * @since      1.0.0
     * @access     public
     * 
     * @param index {int} index of link on links array.
     * @return {linkObject|boolean} linkObject instance, if it is set. Otherwise false.
     */
    getLinkObject(index) {
      // Bail if the index is not set.
      if (undefined === this.links[index]) {
        return false;
      }
      return this.links[index];
    },
    /**
     * Get Link Param.
     *
     * Attempts to retrieve the param from the specified link object.
     *
     * @since      1.0.0
     * @access     public
     * 
     * @param index {index} Index of link on links array.
     * @param param {string} Param Link object param to retrieve.
     *
     * @return {*} The param value.
     */
    getLinkParam(index, param) {
      const object = this.getLinkObject(index);

      /*
      * If the Link index doesn't exist, or the param cannot be found, bail with an empty string
      * Empty string is used here because this method is frequently called in the DOM.
      * Returning false would cause the DOM elements to display "false" in various inputs.
       */
      if (false === object || undefined === object[param]) {
        return '';
      }
      return object[param];
    },
    /**
     * Removes direct link domain.
     *
     * Removes a link from the list of ids by url id.
     *
     * @since  1.0.0
     * @access public
     *
     * @param linkIndex {int} Index of link on links array.
     * @returns void
     */
    async removeLink(linkIndex) {
      const linkToDelete = this.getLinkObject(linkIndex);
      const urlId = linkToDelete.url_id;
      if (urlId) {
        linkToDelete.isRemoving = true;
        await AFFWP.portal.core.fetch({
          path: `affwp/v2/portal/integrations/direct-link-tracking/links/${urlId}`,
          method: 'DELETE',
          data: {}
        });
      }
      this.links.splice(linkIndex, 1);
    },
    /**
     * Submit links.
     *
     * Calls the REST API to save the links and get the new list of links and notices.
     *
     * @since  1.0.0
     * @access public
     *
     * @returns void
     */
    async submit() {
      // Bail if form not valid.
      if (!this.valid) {
        return;
      }
      this.isLoading = true;

      // Post list of links and links to delete.
      const response = await AFFWP.portal.core.fetch({
        path: 'affwp/v2/portal/integrations/direct-link-tracking/save-links',
        method: 'POST',
        data: {
          links: this.links
        }
      });
      this.showInvalidSubmission = !response.success;
      this.links = response.links;
      this.rejected = response.rejected.join('<br>');
      this.showUpdateNotice = true;
      this.isLoading = false;
    },
    /**
     * Dismiss notice.
     *
     * Calls the REST API to dismiss the notice and get the new list of links and notices.
     *
     * @since  1.0.0
     * @access public
     *
     * @param url_id {int} URL ID.
     * @returns void
     */
    async dismiss(url_id) {
      // trigger dismiss only once at a time.
      if (this.isDismissingNotice) {
        return;
      }
      this.isDismissingNotice = true;
      this.isLoading = true;

      // Call REST API to dismiss the notice for this url id.
      await AFFWP.portal.core.fetch({
        path: 'affwp/v2/portal/integrations/direct-link-tracking/dismiss-notice',
        method: 'POST',
        data: {
          url_id
        }
      });

      // reload data.
      this.init();
    },
    /**
     * Has Error.
     *
     * Determines if the specified error is set for a certain link.
     *
     * @since  1.0.0
     * @access public
     *
     * @param link {linkObject} Link object.
     * @param error {string} Type of error.
     * @returns {boolean} True if the error is true. Otherwise false.
     */
    hasError(link, error) {
      return link.errors && true === link.errors[error];
    },
    /**
     * Has Errors.
     *
     * Determines if the link has any errors.
     *
     * @since  1.0.0
     * @access public
     *
     * @param link {linkObject} Link object.
     * @returns {boolean} True if the error is true. Otherwise false.
     */
    hasErrors(link) {
      return link.errors && Object.keys(link.errors).length > 0;
    },
    /**
     * Checks if valid.
     *
     * Determines if there are errors on any of the links.
     *
     * @since  1.0.0
     * @access public
     *
     * @returns {boolean} True if the error is true. Otherwise false.
     */
    checkValid() {
      let valid = true;
      const linkInvalid = this.links.find(link => link.errors && Object.keys(link.errors).length > 0);
      if (linkInvalid) {
        valid = false;
      }
      this.valid = valid;
    },
    /**
     * Validates links on the frontend.
     *
     * Determines if a link is valid just using client-side validations.
     *
     * @since  1.0.0
     * @access public
     *
     * @param linkIndex {int} Index of link on links array.
     * @returns void
     */
    validateFrontend(linkIndex) {
      const currentLink = this.getLinkObject(linkIndex);

      // Bail if link not found.
      if (false === currentLink) {
        return;
      }
      const url = currentLink.url;

      // Clear backend validation timeout, url has changed.
      clearTimeout(currentLink.timer);

      // Reset errors.
      let foundErrors = false;
      currentLink.errors = [];

      // Check if empty.
      if ('' === url.trim()) {
        currentLink.errors.empty = true;
        foundErrors = true;
      } else {
        // Check if duplicated.
        const duplicated = this.links.find((link, index) => index !== linkIndex && link.url === url);
        if (duplicated) {
          currentLink.errors.duplicated = true;
          foundErrors = true;
        }

        // Check if valid url (simple url validation).
        if (!(0,_affiliatewp_portal_url_helpers__WEBPACK_IMPORTED_MODULE_0__.validateUrl)(url)) {
          currentLink.errors.invalid = true;
          foundErrors = true;
        }
      }
      if (foundErrors) {
        this.checkValid();
      } else {
        // No client-side errors, let's check on backend with add-on validation.
        this.valid = false;
        // Wait 500ms before submitting the url.
        currentLink.timer = setTimeout(this.validateBackend.bind(this, linkIndex), 500);
      }
    },
    /**
     * Validates links on the backend.
     *
     * Determines if a link is valid just using client-side validations.
     *
     * @since  1.0.0
     * @access public
     *
     * @param linkIndex {int} Index of link on links array.
     * @returns void
     */
    async validateBackend(linkIndex) {
      const currentLink = this.getLinkObject(linkIndex);

      // Bail if link not found.
      if (false === currentLink) {
        return;
      }
      const url = currentLink.url;
      currentLink.isValidatingUrl = true;
      const response = await AFFWP.portal.core.fetch({
        path: 'affwp/v2/portal/integrations/direct-link-tracking/validate',
        method: 'POST',
        data: {
          url
        }
      });
      currentLink.isValidatingUrl = false;

      // url has changed, ignore this validation.
      if (url !== currentLink.url) {
        return;
      }
      if (!response.success) {
        currentLink.errors.addon = true;
        currentLink.errors.addonReason = response.error;
      }
      this.checkValid();
    }
  };
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (settings);
((window.AFFWP = window.AFFWP || {}).portal = window.AFFWP.portal || {}).directLink = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=direct-link.js.map