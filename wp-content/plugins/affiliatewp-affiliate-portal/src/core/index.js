import { default as apiFetch } from '@wordpress/api-fetch';
import { addQueryArgs, hasQueryArg } from '@wordpress/url';
import store from '@affiliatewp-portal/storage';
import MD5 from "md5";
import { copy } from '@affiliatewp-portal/clipboard-helpers';
import { __ } from '@wordpress/i18n';
import { pause } from '@affiliatewp-portal/helpers';
import { getStablePath } from '@affiliatewp-portal/url-helpers';

/**
 * External depdenencies
 */
import 'alpinejs';

// Set up fetch.
const fetch = apiFetch;

// Set up REST URL middleware.
fetch.use( apiFetch.createRootURLMiddleware( affwp_portal_vars.rest_url ) );

// Set up Nonce middleware.
fetch.use( apiFetch.createNonceMiddleware( affwp_portal_vars.nonce ) );

// Set up Affiliate ID middleware.
fetch.use( ( options, next ) => {

	// Bail early if the affiliate ID was explicitly skipped.
	if ( true === options.skipAffiliateId ) {
		return next( options );
	}

	// If an affiliate ID was specified, skip this.
	if ( hasQueryArg( options.path ) || ( options.data && options.data.affiliate_id ) ) {
		return next( options );
	}

	// If the default affiliate ID was not provided, skip this.
	if ( !affwp_portal_vars.affiliate_id ) {
		return next( options )
	}

	// If this is a get method, append the affiliate ID to the path.
	if ( typeof options.method === 'undefined' || 'get' === options.method.toLowerCase() ) {
		options.path = addQueryArgs( options.path, { affiliate_id: affwp_portal_vars.affiliate_id } );
		// Otherwise, provide the affiliate ID in the options.
	} else {
		options.data.affiliate_id = affwp_portal_vars.affiliate_id;
	}

	return next( options );
} );

// Set up caching middleware.
fetch.use( ( options, next ) => {

	// Bail early if storage was explicitly skipped.
	if ( true !== options.cacheResult ) {
		return next( options );
	}

	const stableOptions = options;

	// Sort the path params
	if ( undefined !== stableOptions.path ) {
		stableOptions.path = getStablePath( stableOptions.path );
	}

	// Sort the options object
	const sortedOptions = Object.keys( stableOptions ).sort().reduce( ( acc, key ) => {
			acc[key] = stableOptions[key];
			return acc;
		}, {} );

	// Create the hash from the sorted option data.
	const storageHash = `apifetch-${MD5( JSON.stringify( sortedOptions ) )}`;

	// Attempt to fetch the data from the store.
	const storageValue = store.get( storageHash, false );

	// If we found the item in storage, return the result directly.
	if ( false !== storageValue ) {
		return storageValue;
	}

	// Otherwise, capture the response and save it in storage.
	const result = next( options );

	// Store the response in the cache for later use.
	store.set( storageHash, result );

	return result;
} )

/**
 * Copy to clipboard AlpineJS handler.
 *
 * Provides a way to copy content to the clipboard.
 *
 * @since 1.0.0
 */
const copyToClipboard = () => {
	return {
		defaultCopyMessage: __( 'Copy', 'affiliatewp-affiliate-portal' ),
		copyMessage: __( 'Copy', 'affiliatewp-affiliate-portal' ),
		async copy(content) {
			// Try to copy the link to clipboard. Set an error message if something went wrong.
			try {
				await copy( content );
				this.copyMessage = `🎉 ${__( 'Copied!', 'affiliatewp-affiliate-portal' )}`;
			}catch {
				this.copyMessage = __( 'Could not copy to clipboard', 'affiliatewp-affiliate-portal' );
			}

			// After 2 seconds, reset the message.
			await pause( 2000 );
			this.copyMessage = this.defaultCopyMessage;
		}
	}
}

export { fetch, store, copyToClipboard }