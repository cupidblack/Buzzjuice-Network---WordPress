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
function pause( length ) {
	return new Promise( resolve => setTimeout( resolve, length ) )
}

/**
 * Adds a trailing slash to the input value, if it does not already have one.
 *
 * @since 1.0.0
 * @param input {string} The value to append a slash.
 *
 * @returns {string} The appended string.
 */
function trailingslashit( input ) {
	if( typeof input !== 'string' || input.endsWith( '/' ) ) {
		return input;
	}

	return `${input}/`;
}

export { pause, trailingslashit}