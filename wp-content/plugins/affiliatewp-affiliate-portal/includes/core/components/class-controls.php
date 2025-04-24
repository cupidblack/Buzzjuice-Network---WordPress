<?php
/**
 * Components: Controls API
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core/Components
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls as Core_Controls;
use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use AffiliateWP_Affiliate_Portal\Core\Traits;
use function AffiliateWP_Affiliate_Portal\html;

#[\AllowDynamicProperties]

/**
 * Controls class.
 *
 * @since 1.0.0
 *
 * @see \AffiliateWP_Affiliate_Portal\Core\Controls_Registry
 */
class Controls {

	use Traits\REST_Support;

	/**
	 * Controls registry instance.
	 *
	 * @since 1.0.0
	 * @var   Controls_Registry
	 */
	private $registry;

	/**
	 * Initializes the controls API.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Set up REST support.
		$this->bootstrap_rest_support();

		add_action( 'affwp_portal_controls_registry_init', array( $this, 'register_core_controls' ) );

		$controls_registry = Controls_Registry::instance();

		$controls_registry->init();
	}

	/**
	 * Registers core one-off controls.
	 *
	 * @since 1.0.0
	 *
	 * @param Controls_Registry $registry Controls registry.
	 */
	public function register_core_controls( $registry ) {
		$this->registry = $registry;

		$this->register_payouts_service_payouts_columns();
	}

	/**
	 * Registers Payouts Service columns for the Payouts table if enabled.
	 *
	 * @since 1.0.0
	 */
	public function register_payouts_service_payouts_columns() {
		if ( ! affwp_is_payouts_service_enabled() ) {
			return;
		}

		$payouts_columns = array(
			new Core_Controls\Table_Column_Control( array(
				'id'     => 'ps_account_column',
				'parent' => 'payouts-table',
				'args'   => array(
					'title'           => __( 'Payout Account', 'affiliatewp-affiliate-portal' ),
					'priority'        => 16,
					'render_callback' => function( \AffWP\Affiliate\Payout $row, $table_control_id ) {
						return Core_Controls\Text_Control::create( "{$table_control_id}_ps_account_column", $row->service_account );
					},
				),
			) ),
			new Core_Controls\Table_Column_Control( array(
				'id'     => 'ps_arrival_date_column',
				'parent' => 'payouts-table',
				'args'   => array(
					'title'           => __( 'Estimated Arrival Date', 'affiliatewp-affiliate-portal' ),
					'priority'        => 21,
					'render_callback' => function( \AffWP\Affiliate\Payout $row, $table_control_id ) {
						if ( 'paid' !== $row->status && ! empty( $row->service_account ) ) {
							$arrival_date = strtotime( $row->date . '+ 14 days' );
							$arrival_date_text = affwp_date_i18n( $arrival_date );
						} else {
							$arrival_date_text = '';
						}

						return Core_Controls\Text_Control::create( "{$table_control_id}_ps_arrival_date_column", $arrival_date_text );
					},
				),
			) ),
		);

		foreach ( $payouts_columns as $column ) {
			$this->registry->add_control( $column );
		}
	}

	/**
	 * Registers REST routes.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {

		// affwp/v2/portal/controls
		register_rest_route( $this->namespace, 'controls', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_controls' ),
				'args'                => $this->get_rest_collection_params( 'controls' ),
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
			),
			'schema' => array( $this, 'get_control_schema' ),
		) );

		// affwp/v2/portal/controls/control
		register_rest_route( $this->namespace, 'controls/(?P<control>[\w\-_]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_control' ),
				'args'                => $this->get_rest_collection_params( 'control' ),
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
			),
			'schema' => array( $this, 'get_control_schema' ),
		) );

	}

	/**
	 * Retrieves registered controls.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error Registered controls.
	 */
	public function get_controls( $request ) {
		$registry = Controls_Registry::instance();

		return $registry->get_controls( 'rest' );
	}

	/**
	 * Retrieves a registered control.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error Registered control.
	 */
	public function get_control( $request ) {
		$control = $request->get_param( 'control' );
		$rows    = (bool) $request->get_param( 'rows' );
		$columns = (bool) $request->get_param( 'columns' );
		$query   = $request->get_params();

		// Convert conflicting param with REST context.
		if ( isset( $query['affwp_context'] ) ) {
			$query['context'] = $query['affwp_context'];
			unset( $query['affwp_context'] );
		} else {
			unset( $query['context'] );
		}

		unset( $query['control'] );
		unset( $query['rows'] );
		unset( $query['columns'] );

		$registry = Controls_Registry::instance();

		return $registry->get_rest_item( 'control', $control, compact( 'rows', 'columns', 'query' ) );
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

		return $params;
	}

	/**
	 * Retrieves the schema for a single control, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_control_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'affwp_ad_control',
			'type'       => 'object',
			// Base properties for every control.
			'properties' => array(),
		);

		// TODO implement additional controls support.
		return $schema;
	}

}
