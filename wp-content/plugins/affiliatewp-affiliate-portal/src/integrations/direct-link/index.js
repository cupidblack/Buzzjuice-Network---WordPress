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
import {validateUrl} from '@affiliatewp-portal/url-helpers';

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
			const response = await AFFWP.portal.core.fetch( {
				path: 'affwp/v2/portal/integrations/direct-link-tracking/get-links',
				cacheResult: false
			} );

			// Add some extra flags to each link.
			this.links = response.links.map( link => {
				link.timer = false;
				link.isValidatingUrl = false;
				link.isRemoving = false;
				return link;
			} );
			this.rejected = response.rejected.join('<br>');

			// Add one default domain if no links saved.
			if( this.links.length === 0 ) {
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
			if( this.links.length + 1 <= this.maxLinks ) {
				this.links.push( {
					url_id: '',
					url: '',
					errors: {}
				} );

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
		getLinkObject( index ) {

			// Bail if the index is not set.
			if( undefined === this.links[index] ) {
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
		getLinkParam( index, param ) {
			const object = this.getLinkObject( index );

			/*
			* If the Link index doesn't exist, or the param cannot be found, bail with an empty string
			* Empty string is used here because this method is frequently called in the DOM.
			* Returning false would cause the DOM elements to display "false" in various inputs.
			 */
			if( false === object || undefined === object[param] ) {
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
		async removeLink( linkIndex ) {
			const linkToDelete = this.getLinkObject( linkIndex );
			const urlId = linkToDelete.url_id;
			if( urlId ) {
				linkToDelete.isRemoving = true;
				await AFFWP.portal.core.fetch( {
					path: `affwp/v2/portal/integrations/direct-link-tracking/links/${urlId}`,
					method: 'DELETE',
					data: {},
				} );
			}
			this.links.splice( linkIndex, 1 );
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
			if( ! this.valid ) {
				return;
			}

			this.isLoading = true;

			// Post list of links and links to delete.
			const response = await AFFWP.portal.core.fetch( {
				path: 'affwp/v2/portal/integrations/direct-link-tracking/save-links',
				method: 'POST',
				data: {
					links: this.links,
				}
			} );

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
		async dismiss( url_id ) {
			// trigger dismiss only once at a time.
			if( this.isDismissingNotice ) {
				return;
			}

			this.isDismissingNotice = true;
			this.isLoading = true;

			// Call REST API to dismiss the notice for this url id.
			await AFFWP.portal.core.fetch( {
				path: 'affwp/v2/portal/integrations/direct-link-tracking/dismiss-notice',
				method: 'POST',
				data: {
					url_id,
				}
			} );

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
		hasError( link, error ) {
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
		hasErrors( link ) {
			return link.errors && Object.keys( link.errors ).length > 0;
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
			const linkInvalid = this.links.find( link => link.errors && Object.keys( link.errors ).length > 0 );
			if( linkInvalid ) {
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
		validateFrontend( linkIndex ) {
			const currentLink = this.getLinkObject( linkIndex );

			// Bail if link not found.
			if( false === currentLink ) {
				return;
			}

			const url = currentLink.url;

			// Clear backend validation timeout, url has changed.
			clearTimeout( currentLink.timer );

			// Reset errors.
			let foundErrors = false;
			currentLink.errors = [];

			// Check if empty.
			if( '' === url.trim() ) {
				currentLink.errors.empty = true;
				foundErrors = true;
			} else {
				// Check if duplicated.
				const duplicated = this.links.find( ( link, index ) => index !== linkIndex && link.url === url );
				if( duplicated ) {
					currentLink.errors.duplicated = true;
					foundErrors = true;
				}

				// Check if valid url (simple url validation).
				if( ! validateUrl( url ) ) {
					currentLink.errors.invalid = true;
					foundErrors = true;
				}
			}

			if( foundErrors ) {
				this.checkValid();
			} else {
				// No client-side errors, let's check on backend with add-on validation.
				this.valid = false;
				// Wait 500ms before submitting the url.
				currentLink.timer = setTimeout( this.validateBackend.bind(this, linkIndex), 500 );
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
		async validateBackend( linkIndex ) {
			const currentLink = this.getLinkObject( linkIndex );

			// Bail if link not found.
			if( false === currentLink ) {
				return;
			}

			const url = currentLink.url;

			currentLink.isValidatingUrl = true;
			const response = await AFFWP.portal.core.fetch( {
				path: 'affwp/v2/portal/integrations/direct-link-tracking/validate',
				method: 'POST',
				data: {
					url
				}
			} );

			currentLink.isValidatingUrl = false;

			// url has changed, ignore this validation.
			if( url !== currentLink.url ) {
				return;
			}

			if( ! response.success ) {
				currentLink.errors.addon = true;
				currentLink.errors.addonReason = response.error;
			}
			this.checkValid();
		}
	}
}

export default settings;
