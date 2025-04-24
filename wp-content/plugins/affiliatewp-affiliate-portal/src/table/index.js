/**
 * Table
 *
 * Works with tables to handle data population, pagination, and filtering.
 *
 * @author Alex Standiford
 * @since 1.0.0
 * @global table
 *
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import table from '@affiliatewp-portal/alpine-table'
import { portalSchemaColumns } from '@affiliatewp-portal/sdk';

/**
 * Table handler for visits.
 *
 * Works referrals table to handle data population, pagination, and filtering.
 *
 * @since 1.0.0
 * @access private
 * @global visitsTable
 * @arguments table
 *
 * @returns object The visits table AlpineJS object.
 */
export default ( args = {} ) => {
	const result = { ...table, ...args };
	result.setupColumns = async function ( page ) {
		const control = await portalSchemaColumns( this.type );
		this.schema = control.columns;

		const rows = [control.columns.reduce( ( acc, column, key ) => {

			if ( 0 === key ) {
				acc[column.id] = __( "Loading...", 'affiliatewp-affiliate-portal' );
			} else {
				acc[column.id] = '';
			}

			return acc;
		}, {} )];

		// If after constructing the loading state we still have not obtained table rows, actually set the loading state.
		if ( true === this.isLoading ) {
			this.rows = rows;
		}
	}


	return result;
};
