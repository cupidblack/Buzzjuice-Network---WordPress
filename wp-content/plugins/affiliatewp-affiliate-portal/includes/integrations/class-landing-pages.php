<?php
/**
 * Integrations: Landing Pages add-on
 *
 * @package     AffiliateWP Affiliate Dashboard
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Integrations;

use AffiliateWP_Affiliate_Portal\Core\Interfaces;
use AffiliateWP_Affiliate_Portal\Core\Sections_Registry;
use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

/**
 * Class for integrating the Landing Pages add-on.
 *
 * @since 1.0.0
 */
class Landing_Pages implements Interfaces\Integration {

	/**
	 * @inheritDoc
	 */
	public function init() {
		add_action( 'plugins_loaded', array( $this, 'register_landing_pages_section' ), 105 );
	}

	/**
	 * Registers Landing Pages section.
	 *
	 * @since 1.0.0
	 */
	public function register_landing_pages_section() {
		if ( ! affwp_alp_is_enabled() ) {
			return;
		}

		// Get landing pages.
		$affiliate_id        = affwp_get_affiliate_id();
		$affiliate_user_name = affwp_get_affiliate_username( $affiliate_id );
		$landing_page_ids    = affwp_alp_get_landing_page_ids( $affiliate_user_name );

		// Check if the affiliate has any landing pages.
		if ( empty( $landing_page_ids ) ) {
			return;
		}

		// Register section.
		$sections_registry = Sections_Registry::instance();
		$sections_registry->register_section( 'landing-pages-urls', array(
			'label'    => __( 'Landing pages', 'affiliatewp-affiliate-portal' ),
			'desc'     => __( 'Landing pages allow you to share the page URL without needing to use your affiliate link.', 'affiliatewp-affiliate-portal' ),
			'priority' => 10,
			'view_id'  => 'urls',
			'wrapper'  => true,
		) );

		// Register controls.
		$controls_registry = Controls_Registry::instance();
		$controls          = array();

		// Landing Pages header.
		$controls[] = new Controls\Text_Control( array(
			'id'      => 'landing-pages-urls-title',
			'view_id' => 'urls',
			'section' => 'landing-pages-urls',
			'args'    => array(
				'text' => __( 'Your landing pages:', 'affiliatewp-affiliate-portal' ),
			),
		) );

		// Create landing page urls with copy controls.
		foreach ( $landing_page_ids as $id ) {
			$controls[] = new Controls\Div_With_Copy_Control( array(
				'id'      => "landing-page-url-$id",
				'view_id' => 'urls',
				'section' => 'landing-pages-urls',
				'args'    => array(
					'get_callback' => function( $affiliate_id ) use ( $id ) {
						return get_permalink( $id );
					},
				),
			) );
		}

		// Register all controls.
		foreach ( $controls as $control ) {
			$controls_registry->add_control( $control );
		}
	}

}
