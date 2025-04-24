/**
 * DOM Helper Functions.
 *
 * Generic helper functions specific to AffilaiteWP Affiliate Portal.
 *
 * @author Alex Standiford
 * @since 1.0.0
 */

/**
 * Get Content Wrapper
 * Retrieves the portal content wrapper.
 *
 * @since 1.0.0
 *
 * @returns {Element}
 */
function getContentWrapper() {
	return document.querySelector( '#portal-content-wrap' );
}

/**
 * Scroll Wrapper To.
 * Scrolls the portal content wrapper to the specified x, y coordinates.
 *
 * @since 1.0.0
 * @param {int} x The x coordinate.
 * @param {int} y The y coordinate.
 */
function scrollWrapperTo( x, y ) {
	return getContentWrapper().scrollTo( x, y );
}

export {getContentWrapper, scrollWrapperTo}