<?php
/**
 * Integrations: Multi-Tier Commissions add-on
 *
 * @package     AffiliateWP Affiliate Dashboard
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2024, Awesome Motive, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
 */

namespace AffiliateWP_Affiliate_Portal\Integrations;

use AffiliateWP_Affiliate_Portal\Core;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces;

use function AffiliateWP\MTC\affiliate_wp_mtc;

/**
 * Class for integrating the Multi-Tier Commissions add-on.
 *
 * @since 1.3.1
 */
class Multi_Tier_Commissions implements Interfaces\Integration {

	/**
	 * Add the view content and scripts.
	 *
	 * @since 1.3.1
	 */
	public function init() {

		if ( ! affiliate_wp_mtc()->is_activated() ) {
			return; // Multi-Tier Commissions is disabled.
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), -10000 );
		add_action( 'affwp_portal_views_registry_init', array( $this, 'register_view' ) );
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.3.1
	 */
	public function enqueue_assets() {

		if ( ! ( affwp_is_portal_enabled() && affwp_is_affiliate_area() ) ) {
			return; // Don't enqueue if is not in the Portal.
		}

		wp_enqueue_style( 'affiliatewp-mtc' );
	}

	/**
	 * Registers Multi-Tier Commissions Add-on View.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Views_Registry $registry Views registry.
	 */
	public function register_view( $registry ) {

		$registry->register_view(
			'network',
			array(
				'label'    => __( 'Network', 'affiliatewp-affiliate-portal' ),
				'icon'     => 'user-group',
				'sections' => array(
					// Register the sections for both the network link and tree.
					'network_link' => array(
						'view_id'  => 'network',
						'priority' => 5,
						'wrapper'  => true,
						'columns'  => array(
							'header'  => 3,
							'content' => 3,
						),
					),
					'network_tree' => array(
						'view_id'  => 'network',
						'priority' => 5,
						'wrapper'  => true,
						'columns'  => array(
							'header'  => 3,
							'content' => 3,
						),
					),
				),
				'controls' => array(
					// Adds the controls to each section to render the copy button and network tree.
					new Controls\Wrapper_Control(
						array(
							'id'      => 'wrapper',
							'view_id' => 'network',
							'section' => 'wrapper',
						)
					),
					new Controls\Heading_Control( array(
						'id'                  => 'network-link-heading',
						'view_id'             => 'network',
						'section'             => 'network_link',
						'args'                => array(
							'text'  => __( 'Your Network Link', 'affiliatewp-affiliate-portal' ),
							'level' => 3,
						),
					) ),
					new Controls\Div_With_Copy_Control(
						array(
							'id'      => 'network-link-with-copy',
							'view_id' => 'network',
							'section' => 'network_link',
							'args'    => array(
								'desc'         => __( 'Invite other affiliates to your network using this link.', 'affiliatewp-affiliate-portal' ),
								'get_callback' => function( $affiliate_id ) {
									return affiliate_wp_mtc()->network->get_invitation_url( $affiliate_id );
								},
							),
						)
					),
					new Controls\Heading_Control( array(
						'id'                  => 'network-tree-heading',
						'view_id'             => 'network',
						'section'             => 'network_tree',
						'args'                => array(
							'text'  => __( 'Your Network', 'affiliatewp-affiliate-portal' ),
							'level' => 3,
						),
					) ),
					new Controls\Network_Tree_Control(
						array(
							'id'      => 'network-tree-diagram',
							'view_id' => 'network',
							'section' => 'network_tree',
						)
					),
				),
				'priority' => 1,
			)
		);
	}
}
