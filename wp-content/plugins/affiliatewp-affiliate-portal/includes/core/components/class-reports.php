<?php
/**
 * Components: Reports API
 *
 * @since       1.0.0
 * @subpackage  Core/Components
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Traits;
use AffiliateWP_Affiliate_Portal\Core\Reports_Registry;

#[\AllowDynamicProperties]

/**
 * Reports set up class.
 *
 * @since 1.0.0
 */
class Reports {

	use Traits\Error_Handler, Traits\REST_Support;

	/**
	 * Initializes the Reports API.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		Reports_Registry::instance()->init();

		$this->set_up_errors();
		$this->bootstrap_rest_support();
	}

	/**
	 * Registers REST endpoint(s) for the component.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {

		// affwp/v2/portal/reports
		register_rest_route( $this->namespace, 'reports', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'args'                => $this->get_rest_collection_params( 'reports' ),
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
				'callback'            => function( \WP_REST_Request $request ) {
					$reports      = $request->get_param( 'reports' );
					$defaults     = $request->get_param( 'defaults' );
					$affiliate_id = $request->get_param( 'affiliate_id' );

					if ( ! is_array( $defaults ) ) {
						$defaults = array();
					}

					$defaults['affiliate_id'] = $affiliate_id;

					$results = $this->get_reports( $reports, $defaults );

					return rest_ensure_response( $results );
				},
			),
			'schema' => array( $this, 'get_report_schema' ),
		) );
	}

	/**
	 * Retrieves the data from the specified report type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $report_type The report type to retrieve.
	 * @param array  $args        Optional. The arguments to pass to the report's callback.
	 *                            Default empty array.
	 * @return mixed|\WP_Error    The report data, or a WP_Error object explaining what went wrong.
	 */
	public function get_report_type( $report_type, $args = array() ) {
		$reports = Reports_Registry::instance();
		$report  = $reports->get( $report_type );

		if ( ! is_array( $args ) ) {
			$args = array( $args );
		}

		if ( false === $report ) {
			$this->add_error(
				'report_does_not_exist',
				'The provided report does not exist.',
				array(
					'report_slug' => $report_type,
					'valid_types' => array_keys( $reports->get_items() ),
				)
			);
		}

		if ( ! isset( $report['callback'] ) || ! is_callable( $report['callback'] ) ) {
			$this->add_error(
				'invalid_report_callback',
				'The provided report callback is invalid.',
				array(
					'report_slug' => $report_type,
					'report'      => $report,
				)
			);
		}

		// Bail early if we have errors.
		if ( $this->has_errors() ) {
			return $this->get_errors();
		}

		return $report['callback']( $args );
	}

	/**
	 * Runs a series of named reports.
	 *
	 * @since 1.0.0
	 *
	 * @param array $reports {
	 *     Report specification.
	 *
	 *     @type string $name The name to give this report on-return. Defaults to the report type.
	 *     @type string $type The report type to call.
	 *     @type array  $args The report args.
	 * }
	 * @param array $global_defaults Optional. List of default parameters to pass into all reports,
	 *                               keyed by their value. Default empty array.
	 * @return array The report data, keyed by the specified report name.
	 */
	public function get_reports( $reports, $global_defaults = array() ) {
		$results = array();

		if ( ! is_array( $reports ) ) {
			$reports = array( $reports );
		}

		// Loop through each requested report, and attempt to get a result.
		foreach ( $reports as $report_args ) {
			$type = isset( $report_args['type'] ) ? $report_args['type'] : '';
			$name = isset( $report_args['name'] ) ? sanitize_key( $report_args['name'] ) : $report_args['type'];

			unset( $report_args['type'] );
			unset( $report_args['name'] );

			// Merge global default args
			$args = wp_parse_args( $report_args, $global_defaults );

			// Run the callback, and append the results
			$results[ $name ] = $this->get_report_type( $type, $args );
		}

		return $results;
	}

	/**
	 * Retrieves parameters for the given collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $collection Collection to retrieve parameters for.
	 * @return array Collection parameters (if any), otherwise an empty array.
	 */
	public function get_rest_collection_params( $collection ) {
		$params = array(
			'context' => array(
				'default' => 'view'
			),
		);

		switch ( $collection ) {
			case 'reports':
			case 'report':
				// TODO define the collection params for report(s).
				$params = array_merge( $params, array() );
				break;

			default:
				break;
		}

		return $params;
	}

	/**
	 * Retrieves the schema for a single report, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Report schema data.
	 */
	public function get_report_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'affwp_ad_report',
			'type'       => 'object',
			// Base properties for every report.
			// TODO define the schema for a single report.
			'properties' => array(),
		);

		// TODO implement additional fields support.
		return $schema;
	}
}
