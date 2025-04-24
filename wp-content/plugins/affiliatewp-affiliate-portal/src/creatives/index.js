/**
 * Creatives.
 *
 * Works with the Creatives page template to handle copying, and modal states.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global creatives
 *
 */

/* eslint @wordpress/no-unused-vars-before-return: "off" */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { copyNode } from '@affiliatewp-portal/clipboard-helpers';
import { pause } from '@affiliatewp-portal/helpers';

/**
 * Creatives screen AlpineJS handler.
 *
 * Works with the Creatives page template to handle copying, and modal states.
 *
 * @since 1.0.0
 * @access private
 * @global creatives
 *
 * @returns object A creatives AlpineJS object.
 */
function creatives() {
	return {
		open: false,
		copying: false,

		/**
		 * Copy.
		 *
		 * Attempts to copy the creative text, and flashes a notification.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @param type event. The event this is firing against.
		 *
		 * @return void
		 */
		async copy( event ) {

			// Save the original HTML so we can use it to restore the original state of the button.
			const originalHTML = event.target.innerHTML;

			// Attempt to copy the content to the user's clipboard.
			await copyNode( this.$refs.creativeCode );

			// Flash the text
			this.copying = true;
			event.target.innerText = `🎉 ${__( 'Copied!', 'affiliatewp-affiliate-portal' )}`;
			await pause( 2000 );
			event.target.innerHTML = originalHTML;
			this.copying = false;
		},

		/**
		 * Fitler creatives by category.
		 *
		 * @since  [-NEXT-]
		 *
		 * @return {void} When we navigate away.
		 */
		async filter() {

			const selector = document.getElementById( 'filter' );

			if ( selector.length <= 0 ) {
				window.console.error( 'Unable to find <select> for slug' );
			}

			if ( false === selector.value ?? false ) {
				window.console.error( 'Unable to get slug from selector value.' );
			}

			// All categoriies (no filtering), navigate w/out the slug.
			if ( '' === selector.value ) {

				// Load the current page w/out the slug selector.
				window.location.href = `${selector.dataset.baseUrl}/`;
				return;
			}

			// Navigat to URL where selector.value is the slug for the filter.
			window.location.href = `${selector.dataset.baseUrl}/${selector.value}`;
		}
	}
}

export default creatives;