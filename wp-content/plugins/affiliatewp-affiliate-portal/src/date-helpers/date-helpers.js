/**
 * Date Helpers.
 *
 * Helper functions specific to dates.
 *
 * @author Alex Standiford
 * @since 1.0.0
 *
 */

/**
 * External dependencies
 */
import { format, parseISO } from 'date-fns';

/**
 * Readable Date.
 * Formats a date in a readable format.
 *
 * @since 1.0.0
 * @param date The raw date to format.
 * @returns {string} Formatted readable date.
 */
function readableDate( date ) {
	return format( parseISO( date ), 'LLLL do, yyyy' );
}

export {readableDate};