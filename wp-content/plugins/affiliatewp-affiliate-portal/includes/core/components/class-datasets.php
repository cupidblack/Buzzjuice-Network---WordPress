<?php
/**
 * Components: Datasets API
 *
 * @since       1.0.0
 * @subpackage  Core/Components
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Traits;
use AffiliateWP_Affiliate_Portal\Core\Datasets_Registry;

#[\AllowDynamicProperties]

/**
 * Datasets set up class.
 *
 * @since 1.0.0
 */
class Datasets {

	use Traits\Error_Handler, Traits\REST_Support;

	/**
	 * Initializes the Datasets API.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		Datasets_Registry::instance()->init();

		$this->bootstrap_rest_support();
	}

	/**
	 * Registers REST endpoint(s) for the component.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {

		// affwp/v2/portal/datasets
		register_rest_route( $this->namespace, 'datasets', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'args'                => $this->get_rest_collection_params( 'datasets' ),
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
				'callback'            => function( \WP_REST_Request $request ) {
					$datasets     = $request->get_param( 'datasets' );
					$defaults     = $request->get_param( 'defaults' );
					$affiliate_id = $request->get_param( 'affiliate_id' );

					if ( ! is_array( $defaults ) ) {
						$defaults = array();
					}

					$defaults['affiliate_id'] = $affiliate_id;

					$results = $this->get_datasets( $datasets, $defaults );

					return rest_ensure_response( $results );
				},
			),
			'schema' => array( $this, 'get_dataset_schema' ),
		) );
	}

	/**
	 * Retrieves the data from the specified dataset type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $dataset_type The dataset type to retrieve.
	 * @param array  $args         Optional. The arguments to pass to the dataset's callback. Default empty array.
	 * @return mixed|\WP_Error    The dataset data, or a WP_Error object explaining what went wrong.
	 */
	public function get_dataset_type( $dataset_type, $args = array() ) {
		$datasets = Datasets_Registry::instance();
		$dataset  = $datasets->get( $dataset_type );

		if ( ! is_array( $args ) ) {
			$args = array( $args );
		}

		if ( false === $dataset ) {
			$this->add_error(
				'dataset_does_not_exist',
				'The provided dataset does not exist.',
				array(
					'dataset_slug'  => $dataset_type,
					'valid_types' => array_keys( $datasets->get_items() ),
				)
			);
		}

		if ( ! isset( $dataset['callback'] ) || ! is_callable( $dataset['callback'] ) ) {
			$this->add_error(
				'invalid_dataset_callback',
				'The provided dataset callback is invalid.',
				array(
					'dataset_slug' => $dataset_type,
					'dataset'      => $dataset,
				)
			);
		}

		// Bail early if we have errors.
		if ( $this->has_errors() ) {
			return $this->get_errors();
		}

		return $dataset['callback']( $args );
	}

	/**
	 * Runs a series of named datasets.
	 *
	 * @since 1.0.0
	 *
	 * @param array $datasets         {
	 *     Dataset specification.
	 *
	 *     @type string $name The name to give this dataset on-return. Defaults to the dataset type.
	 *     @type string $type The dataset type to call.
	 *     @type array  $args The dataset args.
	 * }
	 * @param array $global_defaults Optional. Lst of default parameters to pass into all datasets,
	 *                               keyed by their value. Default empty array.
	 * @return array The dataset data, keyed by the specified dataset name.
	 */
	public function get_datasets( $datasets, $global_defaults = array() ) {
		$results = array();

		if ( ! is_array( $datasets ) ) {
			$datasets = array( $datasets );
		}

		// Loop through each requested dataset, and attempt to get a result.
		foreach ( $datasets as $dataset_args ) {
			$type = isset( $dataset_args['type'] ) ? $dataset_args['type'] : '';
			$name = isset( $dataset_args['name'] ) ? sanitize_key( $dataset_args['name'] ) : $dataset_args['type'];

			unset( $dataset_args['type'] );
			unset( $dataset_args['name'] );

			// Merge global default args
			$args = wp_parse_args( $dataset_args, $global_defaults );

			// Run the callback, and append the results
			$results[ $name ] = $this->get_dataset_type( $type, $args );
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
			case 'datasets':
			case 'dataset':
				// TODO define the collection params for dataset(s).
				$params = array_merge( $params, array() );
				break;

			default:
				break;
		}

		return $params;
	}

	/**
	 * Retrieves the schema for a single dataset, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Dataset schema data.
	 */
	public function get_dataset_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'affwp_ad_dataset',
			'type'       => 'object',
			// Base properties for every dataset.
			// TODO define the schema for a single dataset.
			'properties' => array(),
		);

		// TODO implement additional fields support.
		return $schema;
	}
}
