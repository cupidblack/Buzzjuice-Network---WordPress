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
import {
	getAuthority,
	getFragment,
	getPath,
	getProtocol,
	getQueryString,
	addQueryArgs
} from "@wordpress/url";

/**
 * Internal dependencies
 */
import {trailingslashit} from '@affiliatewp-portal/helpers';

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
function appendUrl( url, append ) {
	// Remove the slash at the beginning of append, if it was mistakenly added.
	if( append.startsWith( '/' ) ) {
		append = append.substr( 1 );
	}

	// Define the parts of the URL.
	return constructUrl( url, [
		'protocol',
		'authority',
		'path',
		trailingslashit( append ),
		'querystring',
		'fragment',
	] );

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
function constructUrl( url, parts ) {
	const urlObject = {

		/**
		 * Get Protocol.
		 * Retrieves the protocol from the URL.
		 *
		 * @since 1.0.0
		 * @returns {string}
		 */
		getProtocol: () => {
			return `${getProtocol( url )}//`;
		},

		/**
		 * Get Authority.
		 * Retrieves the authority from the URL.
		 *
		 * @since 1.0.0
		 * @returns {string}
		 */
		getAuthority: () => {
			return trailingslashit( getAuthority( url ) )
		},

		/**
		 * Get Path.
		 * Retrieves the path from the URL.
		 *
		 * @since 1.0.0
		 * @returns {string}
		 */
		getPath: () => {
			return trailingslashit( getPath( url ) )
		},

		/**
		 * Get Query String.
		 * Retrieves the querytstring from the URL.
		 *
		 * @since 1.0.0
		 * @returns {string}
		 */
		getQuerystring: () => {
			const queryString = getQueryString( url );
			return queryString ? `?${getQueryString( url )}` : '';
		},

		/**
		 * Get Fragment.
		 * Retrieves the fragment from the URL.
		 *
		 * @since 1.0.0
		 * @returns {string}
		 */
		getFragment: () => {
			return getFragment( url )
		},
	}

	return parts.reduce( ( acc, part ) => {

		const isValidUrlPart = ['protocol', 'authority', 'path', 'querystring', 'fragment'].includes( part.toLowerCase() );

		if( !isValidUrlPart && typeof part === 'string' ) {
			return acc + part;
		}else if( !isValidUrlPart ) {
			return acc;
		}
		const callback = urlObject['get' + part.charAt( 0 ).toUpperCase() + part.slice( 1 ).toLowerCase()];
		const urlPart = callback();

		if( undefined === urlPart ) {
			return acc;
		}

		return acc + urlPart;
	}, '' );
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
function authoritiesMatch( url, baseAuthority ) {
	const inputAuthority = getAuthority( url );

	// Return true if the authorities match.
	if( inputAuthority === baseAuthority ) {
		return true;
	}

	// Return true if inputAuthority is a subdomain of baseAuthority.
	const regex = new RegExp("\\w\\." + baseAuthority + "$");
	return regex.test( inputAuthority );
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
function hasValidProtocol( url ) {
	const protocol = getProtocol( url );

	return ['https:', 'http:'].includes( protocol );
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
function getPage( url ) {
	const path = getPath( url );

	const search = path.match( paginationRegex );

	// If no page was found, we are on page 1.
	if( null === search ) {
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
function paginateUrl( url, args ) {
	getPage( url );
	const path = trailingslashit( getPath( url ) ).replace( paginationRegex, '/' );

	// Strip out any existing pagination from the path.
	const urlParts = ['protocol', 'authority', path];

	// Append the page number, if we have a page to append.
	if( args.page ) {
		if( args.page > 1 ) {
			urlParts.push( args.page + '/' );
		}
		delete args.page
	}

	// Construct the URL using the provided URL parts.
	const result = constructUrl( url, urlParts );

	// Append query args to the resulting URL.
	return addQueryArgs( result, args );
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
function validateUrl( url ) {
	return /\.\w\w.*/.test( url );
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
function getStablePath( path ) {
	const splitted = path.split( '?' );
	const query = splitted[ 1 ];
	const base = splitted[ 0 ];
	if ( ! query ) {
		return base;
	}

	// 'b=1&c=2&a=5'
	return (
		base +
		'?' +
		query
			// [ 'b=1', 'c=2', 'a=5' ]
			.split( '&' )
			// [ [ 'b, '1' ], [ 'c', '2' ], [ 'a', '5' ] ]
			.map( ( entry ) => entry.split( '=' ) )
			// [ [ 'a', '5' ], [ 'b, '1' ], [ 'c', '2' ] ]
			.sort( ( a, b ) => a[ 0 ].localeCompare( b[ 0 ] ) )
			// [ 'a=5', 'b=1', 'c=2' ]
			.map( ( pair ) => pair.join( '=' ) )
			// 'a=5&b=1&c=2'
			.join( '&' )
	);
}

export {paginateUrl, getPage, appendUrl, authoritiesMatch, hasValidProtocol, constructUrl, validateUrl, getStablePath};
