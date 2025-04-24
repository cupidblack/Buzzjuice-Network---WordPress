<?php
/**
 * Integrations: Lifetime Commissions add-on
 *
 * @package     AffiliateWP Affiliate Dashboard
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Integrations;

use AffiliateWP_Affiliate_Portal\Core;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces;

/**
 * Class for integrating the Store Credit add-on.
 *
 * @since 1.0.0
 */
class Lifetime_Commissions implements Interfaces\Integration {

	/**
	 * @inheritDoc
	 */
	public function init() {
		// Register Lifetime Commissions view.
		add_action( 'affwp_portal_views_registry_init', array( $this, 'register_lifetime_customers_view' ) );

		// Register Lifetime Customers Control on stats.
		add_action( 'affwp_portal_controls_registry_init', array( $this, 'register_lifetime_customers_stats_control' ) );

		// Register Lifetime Referral column on Referrals.
		add_action( 'affwp_portal_controls_registry_init', array( $this, 'register_lifetime_referral_column' ) );
	}

	/**
	 * Registers Lifetime Customers Add-on View.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Views_Registry $registry Views registry.
	 */
	public function register_lifetime_customers_view( $registry ) {
		// Get current affiliate ID.
		$affiliate_id = affwp_get_affiliate_id();

		// Check if affiliate has access to Lifetime Commissions view.
		$can_access    = affwp_get_affiliate_meta( $affiliate_id, 'affwp_lc_customers_access', true );
		$global_access = affiliate_wp()->settings->get( 'lifetime_commissions_customers_access', false );
		if ( ! $can_access && ! $global_access ) {
			return;
		}

		// Lifetime Customers Section.
		$sections = array(
			'lifetime-customers' => array(
				'view_id'  => 'lifetime-customers',
				'priority' => 5,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
			),
		);

		// Lifetime Customers Controls.
		$controls = array();

		// Wrapper control.
		$controls[] = new Controls\Wrapper_Control( array(
			'id'      => 'wrapper',
			'view_id' => 'lifetime-customers',
			'section' => 'wrapper',
		) );

		// Lifetime customers table.
		$hide_emails = affiliate_wp()->settings->get( 'lifetime_commissions_hide_customer_emails', false );
		$controls[]  = new Controls\Table_Control( array(
			'id'      => 'lifetime-customers-table',
			'view_id' => 'lifetime-customers',
			'section' => 'lifetime-customers',
			'args'    => array(
				'schema' => array(
					'name'                => 'lifetime-customers-table',
					'page_count_callback' => function ( $args ) {
						$number = isset( $args['number'] ) ? $args['number'] : 20;

						$customers = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate( $args['affiliate_id'] );
						$count     = count( $customers );

						return absint( ceil( $count / $number ) );
					},
					'data_callback'       => function ( $args ) {
						$customers = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate( $args['affiliate_id'] );
						$customers = array_filter(
							$customers,
							function( $customer ) {
								return $customer instanceof \AffWP\Customer;
							}
						);
						return $customers;
					},
					'schema' => array(
						'first_name' => array(
							'title'           => __( 'First Name', 'affiliatewp-affiliate-portal' ),
							'priority'        => 5,
							'render_callback' => function( \AffWP\Customer $row, $table_control_id ) {
								$first_name = empty( $row->first_name ) ? _x( 'Not provided', 'customer first name', 'affiliatewp-affiliate-portal' ) : $row->first_name;
								return Controls\Text_Control::create( "{$table_control_id}_first_name", $first_name );
							},
						),
						'last_name' => array(
							'title'           => __( 'Last Name', 'affiliatewp-affiliate-portal' ),
							'priority'        => 5,
							'render_callback' => function( \AffWP\Customer $row, $table_control_id ) {
								$last_name = empty( $row->last_name ) ? _x( 'Not provided', 'customer last name', 'affiliatewp-affiliate-portal' ) : $row->last_name;
								return Controls\Text_Control::create( "{$table_control_id}_last_name", $last_name );
							},
						),
						'email' => new Controls\Table_Column_Control( array(
							'id'                  => 'lifetime-customers-table_email_column',
							'parent'              => 'lifetime-customers-table',
							'permission_callback' => function( $control, $affiliate_id ) use ( $hide_emails ) {
								return ! $hide_emails;
							},
							'args'                => array(
								'title'           => __( 'Email', 'affiliatewp-affiliate-portal' ),
								'priority'        => 5,
								'render_callback' => function( \AffWP\Customer $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_email", $row->email );
								},
							),
						) ),
					),
				),
			),
		) );

		// Register Lifetime Commissions view.
		$registry->register_view( 'lifetime-customers', array(
			'label'    => __( 'Lifetime Customers', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'users',
			'sections' => $sections,
			'controls' => $controls,
		) );
	}

	/**
	 * Registers Lifetime Customers Control on Stats.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Controls_Registry $registry Controls registry.
	 */
	public function register_lifetime_customers_stats_control( $registry ) {
		// Create lifetime customers stats control.
		$lifetime_customers_control = new Controls\Card_Control( array(
			'id'      => 'affiliatewp-lifetime-customers',
			'view_id' => 'stats',
			'section' => 'stats',
			'parent'  => 'referrals_card_group',
			'permission_callback' => function( $control, $affiliate_id ) {
				// Check if lifetime commissions enabled.
				$lc_enabled = affiliate_wp_lifetime_commissions()->integrations->can_receive_lifetime_commissions( $affiliate_id );

				return $lc_enabled;
			},
			'args'    => array(
				'title'    => __( 'Lifetime customers', 'affiliatewp-affiliate-portal' ),
				'data_key' => 'lifetime_customers',
				'data'     => function ( $data_key, $affiliate_id ) {
					global $wpdb;

					$customers_count = 0;
					$table           = affiliate_wp_lifetime_commissions()->lifetime_customers->table_name;
					$customer_ids    = $wpdb->get_col( $wpdb->prepare( "SELECT affwp_customer_id FROM {$table} WHERE affiliate_id = %d AND affwp_customer_id != 0;", $affiliate_id ) );
					$customer_ids    = array_map( 'absint', $customer_ids );

					if ( ! empty( $customer_ids ) ) {
						$customers_count = count( array_unique( $customer_ids ) );
					}

					return $customers_count;
				},
			),
		) );

		// Add control to section.
		$registry->add_control( $lifetime_customers_control );
	}

	/**
	 * Registers Lifetime Referral column on Referrals.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Controls_Registry $registry Controls registry.
	 */
	public function register_lifetime_referral_column( $registry ) {
		// Create Lifetime Referral Table Column Control.
		$column = new Controls\Table_Column_Control( array(
			'id'     => 'lifetime_referral_column',
			'parent' => 'referrals-table',
			'permission_callback' => function( $control, $affiliate_id ) {
				// Check if lifetime commissions enabled.
				$lc_enabled = affiliate_wp_lifetime_commissions()->integrations->can_receive_lifetime_commissions( $affiliate_id );

				return $lc_enabled;
			},
			'args'   => array(
				'title'           => __( 'Lifetime Referral', 'affiliatewp-affiliate-portal' ),
				'priority'        => 30,
				'render_callback' => function( \AffWP\Referral $row, $table_control_id ) {
					$custom     = maybe_unserialize( $row->custom );
					$control_id = "{$table_control_id}_lifetime_referral_column";

					if ( is_array( $custom ) && in_array( 'lifetime_referral', $custom ) ) {
						// Is a lifetime referral.
						return new Controls\Icon_Control( array(
							'id'   => "{$table_control_id}_lifetime_referral_column",
							'args' => array(
								'name' => 'check',
							),
						) );
					} else {
						// Not a lifetime referral, show nothing.
						return Controls\Text_Control::create( $control_id );
					}
				},
			),
		) );

		$registry->add_control( $column );
	}

}
