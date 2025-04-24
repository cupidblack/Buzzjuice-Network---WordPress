/**
 * Internal dependencies
 */
import chart from '@affiliatewp-portal/alpine-chart'
import { portalSchemaRows } from '@affiliatewp-portal/sdk';

export default ( args ) => {
	const result = { ...chart, ...args };

	// Fetch datasets via REST before
	result.fetchPortalData = async function ( dateQueryType ) {
		return new Promise( async ( res, rej ) => {
			const control = await portalSchemaRows( this.type, { range: dateQueryType } );
			this.label = control.x_label_key;

			res( control.rows.map( ( row ) => {
				return {
					label: row.title,
					borderColor: row.color,
					data: row.data,
					borderWidth: 3,
					backgroundColor: 'transparent'
				}
			} ) );
		} );
	}

	return result;
};