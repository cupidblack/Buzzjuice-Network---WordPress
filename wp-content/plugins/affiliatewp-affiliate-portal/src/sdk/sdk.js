/**
 * AffiliateWP Affiliate Portal SDK.
 *
 * Functions for interacting with AffiliateWP Affiliate Portal REST endpoints.
 *
 * @author Alex Standiford
 * @since 1.0.0
 */

/**
 * WordPress dependencies
 */
import {addQueryArgs} from '@wordpress/url';

/**
 * Portal Affiliate Endpoint.
 *
 * Fetches the data for the provided affiliate.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalAffiliate( args = {} ) {
	let affiliate;
	if ( undefined === args.affiliate ) {
		affiliate = affwp_portal_vars.affiliate_id;
	} else {
		affiliate = args.affiliate;
		delete args.affiliate;
	}
	return AFFWP.portal.core.fetch( {
		path: addQueryArgs( `/affwp/v1/affiliates/${affiliate}`, args ),
		skipAffiliateId: true,
		cacheResult: true
	} );
}

/**
 * Portal Settings Endpoint.
 *
 * Fetches the affiliate portal settings data.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSettings() {
	return AFFWP.portal.core.fetch( { path: '/affwp/v2/portal/settings', cacheResult: true } );
}

/**
 * Portal Referrals Endpoint.
 *
 * Fetches referrals.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSchemaRows( type, args = {} ) {
	const requestArgs = {
		...args, ...{ rows: true }
	};

	// Translate page into offset
	if ( requestArgs.page ) {
		requestArgs.offset = requestArgs.number ? ( requestArgs.page - 1 ) * requestArgs.number : 20;
	}

	return AFFWP.portal.core.fetch( {
		path: addQueryArgs( `/affwp/v2/portal/controls/${type}`, requestArgs ),
		cacheResult: true
	} );
}

/**
 * Portal Referrals Endpoint.
 *
 * Fetches referrals.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSchemaColumns( type ) {
	return AFFWP.portal.core.fetch( {
		path: addQueryArgs( `/affwp/v2/portal/controls/${type}`, { columns: true } ),
		cacheResult: true
	} );
}

/**
 * Portal Datasets Endpoint.
 *
 * Fetches datasets.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalDataset( args = {} ) {
	return AFFWP.portal.core.fetch( {
		path: addQueryArgs( `/affwp/v2/portal/datasets`, args ),
		cacheResult: true
	} );
}

/**
 * Portal View Endpoint.
 *
 * Fetches Portal view.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalView( view ) {
	return AFFWP.portal.core.fetch( {
		path: `/affwp/v2/portal/views/${view}`,
		cacheResult: true
	} );
}

/**
 * Portal Section Endpoint.
 *
 * Fetches a section.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSection( section ) {
	return AFFWP.portal.core.fetch( {
		path: `/affwp/v2/portal/sections/${section}`,
		cacheResult: true
	} );
}

/**
 * Portal Section Endpoint.
 *
 * submits a section form.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalSectionFields( section ) {
	return AFFWP.portal.core.fetch( {
		path: `/affwp/v2/portal/sections/${section}/fields`
	} );
}

/**

 * Portal Section Endpoint.
 *
 * submits a section form.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function submitSection( section, data ) {
	return AFFWP.portal.core.fetch( {
		method: 'POST',
		path: `/affwp/v2/portal/sections/${section}/submit`,
		data
	} );
}

/**
 * Portal Controls Endpoint.
 *
 * Fetches a single control.
 *
 * @since      1.0.0
 * @access     protected
 *
 * @return {Promise}
 */
function portalControl( control ) {
	return AFFWP.portal.core.fetch( {
		path: `/affwp/v2/portal/controls/${control}`,
		cacheResult: true
	} );
}

/**
 * Validate Control.
 *
 * Runs field validations against a single control.
 *
 * @since 1.0.0
 * @access protected
 *
 * @param {string} control The control ID
 * @param {object} data The data to validate, keyed by the field ID
 * @returns {object} The control API response.
 */
function validateControl( control, data ) {
	return AFFWP.portal.core.fetch( {
		path: addQueryArgs( `/affwp/v2/portal/controls/${control}`, { validate: true, data } ),
		cacheResult: true
	} );
}

export {
	portalSchemaColumns,
	portalDataset,
	portalAffiliate,
	portalSettings,
	portalSchemaRows,
	portalView,
	submitSection,
	portalControl,
	portalSection,
	portalSectionFields,
	validateControl
};