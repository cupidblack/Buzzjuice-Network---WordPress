<?php
/**
 * Components: Sections API
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use AffiliateWP_Affiliate_Portal\Core\Sections_Registry;
use AffiliateWP_Affiliate_Portal\Core\Traits;
use function AffiliateWP_Affiliate_Portal\html;

#[\AllowDynamicProperties]

/**
 * Sections API.
 *
 * @since 1.0.0
 */
class Sections {

	use Traits\REST_Support;

	/**
	 * Bootstraps the Views API.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Set up REST support.
		$this->bootstrap_rest_support();

		// Initialize the sections registry and fire the hook.
		$sections_registry = Sections_Registry::instance();
		$sections_registry->init();
	}

	/**
	 * Registers REST routes.
	 *
	 * @since 1.0.0
	 *
	 * @see   register_rest_route()
	 */
	public function register_rest_routes() {

		$section_regex = '(?P<section>[\w\-_]+)';

		// affwp/v2/portal/sections
		register_rest_route( $this->namespace, 'sections', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_sections' ),
				'args'                => $this->get_rest_collection_params( 'sections' ),
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
			),
			'schema' => array( $this, 'get_section_schema' ),
		) );

		// affwp/v2/portal/sections/section
		register_rest_route( $this->namespace, "sections/{$section_regex}", array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_section' ),
				'args'                => $this->get_rest_collection_params( 'section' ),
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
			),
			'schema' => array( $this, 'get_section_schema' ),
		) );

		// affwp/v2/portal/sections/section/submit
		register_rest_route( $this->namespace, "sections/{$section_regex}/submit", array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'submit_section' ),
			'args'                => $this->get_rest_collection_params( 'submit_section' ),
			'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
		) );

		// affwp/v2/portal/sections/section/fields
		register_rest_route( $this->namespace, "sections/{$section_regex}/fields", array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_section_fields_callback' ),
			'args'                => $this->get_rest_collection_params( 'section_inputs' ),
			'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
		) );
	}

	/**
	 * Handles the submission for a section.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response The response.
	 */
	public function submit_section( \WP_REST_Request $request ) {
		$controls  = $this->get_section_inputs( $request );
		$submitted = false;

		$failed_validations = array();

		foreach ( $controls as $control ) {
			$control->prepare_rest_object( array( 'query' => array( 'data' => $request->get_params(), 'validate' => true ) ) );
			if ( ! empty( $control->validations['failed'] ) ) {
				$failed_validations = array_merge( $failed_validations, $control->validations['failed'] );
			}
		}

		// If all validations passed, save controls.
		if ( empty( $failed_validations ) ) {
			$submitted = true;
			foreach ( $controls as $control ) {
				$data = $request->get_param( $control->get_id() );
				$control->save_control_data( $data );
			}
		}

		return rest_ensure_response( array(
			'submitted'   => $submitted,
			'validations' => $failed_validations,
		) );
	}

	/**
	 * Retrieves the list of section fields.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function get_section_fields_callback( $request ) {

		$inputs = $this->get_section_inputs( $request );

		$fields = array();

		foreach ( $inputs as $input ) {

			if ( false === $input->get_argument( 'posts_data', true ) ) {
				$value = '';
			} else {
				$value = $input->get_control_data();
			}

			// Select controls default to the first value when they are not set.
			if ( 'select' === $input->get_input_type() && '' === $value ) {
				$options = array_keys( $input->get_argument( 'options', array() ) );

				if ( isset( $options[0] ) ) {
					$value = $options[0];
				}
			}

			$validations = $input->get_argument( 'validations', array() );

			if ( ! is_array( $validations ) ) {
				$has_validations = false;
			} else {
				$has_validations = count( $validations ) > 0;
			}

			$fields[] = array(
				'id'             => $input->get_id(),
				'value'          => $value,
				'name'           => $input->get_attribute( 'name' ),
				'type'           => $input->get_input_type(),
				'hasValidations' => $has_validations,
			);
		}

		return rest_ensure_response( array(
			'section' => $this->get_section( $request ),
			'fields'  => $fields,
		) );
	}

	/**
	 * Fetches the inputs for the section.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request REST request.
	 *
	 * @return array List of control inputs in this request's section.
	 */
	public function get_section_inputs( $request ) {
		$section_id = $request->get_param( 'section' );
		$registry   = Controls_Registry::instance();
		$query      = $registry->query( array(
			'section' => $section_id,
		) );

		// Filter out controls that are not form inputs
		$controls = array();
		foreach ( $query as $key => $control ) {
			if ( $control->form_control() ) {
				$controls[ $key ] = $control;
			}
		}

		return $controls;
	}

	/**
	 * Retrieves registered sections.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error Registered sections.
	 */
	public function get_sections( $request ) {
		$registry = Sections_Registry::instance();

		return $registry->get_sections( 'rest' );
	}

	/**
	 * Retrieves a registered section.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error Registered sections.
	 */
	public function get_section( $request ) {
		$section = $request->get_param( 'section' );

		$registry = Sections_Registry::instance();

		return $registry->get_section( $section, 'rest' );
	}

	/**
	 * Retrieves parameters for the given collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $collection Collection to retrieve parameters for.
	 *
	 * @return array Collection parameters (if any), otherwise an empty array.
	 */
	public function get_rest_collection_params( $collection ) {
		$params = array(
			'context' => array(
				'default' => 'view',
			),
		);

		switch ( $collection ) {
			case 'section':
			case 'sections':
				$params = array_merge( $params, array(
					'sectionId' => array(
						'description'       => __( 'Section ID as stored in the sections registry.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_string( $param );
						},
					),
					'wrapper'   => array(
						'description'       => __( 'Whether the section should be treated as a wrapper.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => '',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_bool( $param );
						},
					),
					'columns'   => array(
						'description'       => __( 'Column widths (1-5) to set for the header and content areas of the section.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => '',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_array( $param );
						},
					),
					'priority'  => array(
						'description'       => __( 'Priority to sort the section by during output of a view.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => '',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_int( $param );
						},
					),
				) );
				break;

			default:
				break;
		}

		return $params;
	}

	/**
	 * Retrieves the schema for a single section, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_section_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'affwp_ad_section',
			'type'       => 'object',
			// Base properties for every section.
			'properties' => array(
				'sectionId' => array(
					'description' => __( 'Section ID as stored in the sections registry.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'string',
				),
				'wrapper'   => array(
					'description' => __( 'Whether the section should be treated as a wrapper.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'boolean',
				),
				'columns'   => array(
					'description' => __( 'Column widths (1-5) to set for the header and content areas of the section.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'array',
				),
				'priority'  => array(
					'description' => __( 'Priority to sort the section by during output of a view.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'integer',
				),
			),
		);

		// TODO implement additional fields support.
		return $schema;
	}

}
