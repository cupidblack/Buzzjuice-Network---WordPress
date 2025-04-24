/**
 * Copy.
 *
 * Attempts to copy the specified content to the user's clipboard
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function copy( content ) {
	return new Promise( ( res, rej ) => {
		// Check for clipboard API
		if ( undefined === typeof navigator.clipboard || undefined === typeof navigator.clipboard.writeText ) {
			rej( 'Could not find a valid clipboard library.' );
		} else {
			res( navigator.clipboard.writeText( content ) )
		}
	} );
}

/**
 * Copy Node.
 *
 * Attempts to copy the content from the specified node.
 * @since 1.0.0
 * @param {Node} target The DOM Node content to copy.
 * @return {Promise}
 */
function copyNode( target ) {
	return new Promise( ( res, rej ) => {
		if ( typeof target !== 'object' || ( typeof target.innerText !== 'string' && typeof target.value !== 'string' ) ) {
			rej( 'Target is not a valid HTML node.' );
		}

		let value = '';

		// Try to get an input value if it's set first.
		if ( typeof target.value === 'string' ) {
			value = target.value;

			// Fallback to the innerText
		} else if ( typeof target.innerText === 'string' ) {
			value = target.innerText;

			// If all-else fails, reject.
		} else {
			rej( 'Could not find valid text to copy' );
		}

		res( copy( value ) );
	} );
}


export { copy, copyNode }