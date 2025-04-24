/**
 * URL Generator.
 *
 * Works with the URLs page template to generate, validate, and copy URLs.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global urlGenerator
 *
 */

/**
 * External dependencies
 */
import 'alpinejs';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { addQueryArgs, safeDecodeURI, getAuthority } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { portalSettings, portalAffiliate } from '@affiliatewp-portal/sdk';
import { pause } from '@affiliatewp-portal/helpers';
import { copy } from '@affiliatewp-portal/clipboard-helpers';
import { appendUrl, authoritiesMatch, hasValidProtocol } from '@affiliatewp-portal/url-helpers';

/**
 * URL Generator AlpineJS handler.
 *
 * Works with the URLs page template to generate, validate, and copy URLs.
 *
 * @since 1.0.0
 * @access private
 * @global urlGenerator
 *
 * @returns object The urlGenerator AlpineJS object.
 */
function urlGenerator() {
	return {

		/**
		 * campaign name.
		 *
		 * The value of the campaign input from the DOM.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		campaign: '',

		/**
		 * URL input.
		 *
		 * The value of the URL input from the DOM.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		inputUrl: '',

		/**
		 * Base authority.
		 *
		 * The base authority of the site's affiliate base URL.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		baseAuthority: '',

		/**
		 * Page URL input label.
		 *
		 * Translate-able text displayed as the label text.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		pageUrlLabel: '',

		/**
		 * Affiliate ID.
		 *
		 * The affiliate ID to use in the portal.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type int
		 */
		affiliateId: 0,

		/**
		 * The value to append to referral vars.
		 *
		 * this will either be the affiliate ID or the username.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type mixed
		 */
		referralFormatValue: '',

		/**
		 * Pretty Affiliate URLs Setting.
		 *
		 * Set to true if pretty affiliate URLs are turned on in AffiliateWP settings.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type boolean
		 */
		prettyAffiliateUrls: false,

		/**
		 * Referral Var.
		 *
		 * The referral var, based on AffiliateWP settings.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		referralVar: '',

		/**
		 * Whether to allow bypassing the URL authority.
		 *
		 * @since  1.0.5
		 * @access public
		 *
		 * @type bool
		 */
		bypassUrlAuthority: false,

		/**
		 * Is Loading.
		 *
		 * Remains true until necessary data has been fetched from the server.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type boolean
		 */
		isLoading: true,

		/**
		 * Default copy message.
		 *
		 * The default message to display when text a link can be copied.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		defaultCopyMessage: __( 'Copy link', 'affiliatewp-affiliate-portal' ),


		/**
		 * Default copy error message.
		 *
		 * The default message to display when text a link cannot be copied.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		defaultErrorMessage: __( 'Invalid URL', 'affiliatewp-affiliate-portal' ),

		/**
		 * URL listing getter.
		 *
		 * List of URLs, keyed by the URL ID.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type object
		 */
		get urls() {
			return AFFWP.portal.core.store.get( 'urlGeneratorUrls', {} );
		},

		/**
		 * URL listing setter.
		 *
		 * Sets the URLs object.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type object
		 */
		set urls( value ) {
			AFFWP.portal.core.store.set( 'urlGeneratorUrls', { ...this.urls, ...value } );
		},

		/**
		 * Generate URL.
		 *
		 * Generates URL from DOM inputs.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @param type {string} type URL key to update.
		 *
		 * @return {string} The generated URL.
		 */
		generateUrl( type ) {

			const urlObject = this.getUrlObject( type );

			// If the URL is not set, return empty string for now.
			if( false === urlObject ) {
				return '';
			}

			let inputUrl = this.inputUrl;
			const args = {};

			// If we have a campaign, use it.
			if( this.campaign.length > 0 ) {
				args.campaign = this.campaign;
			}

			// If pretty affiliate URLs are being used, append the /ref/id to the end of the URL.
			if( true === this.prettyAffiliateUrls ) {
				inputUrl = appendUrl( inputUrl, `${this.referralVar}/${this.referralFormatValue}` );

				// Otherwise, set the referral var to the affiliate ID.
			}else {
				args[this.referralVar] = this.referralFormatValue;
			}

			// Re-encode the URL. Decode it first, just in-case it came in already-encoded.
			inputUrl = encodeURI( safeDecodeURI( inputUrl ) );

			// Set query paramaters based on generated argus.
			const url = addQueryArgs( inputUrl, args );

			// Re-instantiate the URL object. URL validation happens here.
			const urls = this.urls;
			urls[type] = this.urlObject( { url } );
			this.urls = urls;

			// Return the URL param
			return this.getUrlParam( type, 'url' );
		},

		/**
		 * Get URL Object.
		 *
		 * Attempts to retrieve the URL object from the list of objects.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @param type {string} type URL key to retrieve.
		 *
		 * @return {urlObject|boolean} urlObject instance, if it is set. Otherwise false.
		 */
		getUrlObject( type ) {

			// Bail if the URL type is not set.
			if( undefined === this.urls[type] ) {
				return false;
			}

			return this.urls[type];
		},

		/**
		 * Get URL Param.
		 *
		 * Attempts to retrieve the param from the specified URL object.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @param type {string} type URL key to retrieve.
		 * @param param {string} param URL object param to retrieve.
		 *
		 * @return {*} The param value.
		 */
		getUrlParam( type, param ) {
			const object = this.getUrlObject( type );

			/*
			* If the URL type doesn't exist, or the param cannot be found, bail with an empty string
			* Empty string is used here because this method is frequently called in the DOM.
			* Returning false would cause the DOM elements to display "false" in various inputs.
			 */
			if( false === object || undefined === object[param] ) {
				return '';
			}

			return object[param]
		},

		/**
		 * Set Copy.
		 *
		 * Attempts to copy the URL text, and flashes a notification.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @param type {string} URL object key to copy from.
		 *
		 * @return void
		 */
		async setCopy( type ) {
			const urlObject = this.getUrlObject( type );

			// If the URL Object type could not be found, bail.
			if( false === urlObject ) {
				return;
			}

			// Try to copy the link to clipboard. Set an error message if something went wrong.
			try {
				await copy( urlObject.url );
				urlObject.copyMessage = `🎉 ${__( 'Copied!', 'affiliatewp-affiliate-portal' )}`;
			}catch {
				urlObject.copyMessage = __( 'Could not copy to clipboard', 'affiliatewp-affiliate-portal' );
			}

			// After 2 seconds, reset the message.
			await pause( 2000 );
			urlObject.copyMessage = urlObject.defaultCopyMessage;
		},

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

			// Fetch the raw data.
			const [affiliate, settings] = await Promise.all( [
				portalAffiliate(),
				portalSettings()
			] );
			// Set object values from fetch'd data.
			this.baseAuthority = getAuthority( affiliate.base_url );
			this.inputUrl = affiliate.base_url;
			this.affiliateId = affiliate.affiliate_id;
			this.prettyAffiliateUrls = settings.pretty_affiliate_urls;
			this.referralFormatValue = settings.referral_format_value;
			this.referralVar = settings.referral_var;
			this.bypassUrlAuthority = settings.bypass_url_authority;
			this.pageUrlLabel = __( `Enter any valid page URL from ${affiliate.base_url}`, 'affiliatewp-affiliate-portal' );

			// Set initial URL Objects
			this.urls = {
				generated: this.urlObject( { url: affiliate.base_url } ),
				referral:  this.urlObject( { url: this.getUrlParam( 'generated', 'url' ) } )
			};

			Object.keys( this.urls ).forEach( ( url ) => {
				this.generateUrl( url )
			} );

			// All set, let the DOM know that the class is no-longer loading.
			this.isLoading = false;
		},

		/**
		 * URL Object Method.
		 *
		 * Validates, and instantiates a URL Object.
		 *
		 * @since      1.0.0
		 * @access     private
		 *
		 * @return {urlObject} URL Object instance.
		 */
		urlObject( args ) {

			// Apply defaults to the provided arguments.
			const result = {
				...{
					defaultCopyMessage: this.defaultCopyMessage,
					defaultErrorMessage: this.defaultErrorMessage,
					url: ''
				}, ...args
			};

			// Set the copy message, and error status of the URL.
			if( hasValidProtocol( result.url ) && ( authoritiesMatch( result.url, this.baseAuthority ) || true === this.bypassUrlAuthority ) ) {
				result.copyMessage = result.defaultCopyMessage;
				result.isError = false;
			}else {
				result.copyMessage = result.defaultErrorMessage;
				result.isError = true;
			}

			return result;
		}
	}
}

export default urlGenerator;