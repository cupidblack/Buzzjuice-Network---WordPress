<?php
/**
 * Core: Report Types Registry
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core;

#[\AllowDynamicProperties]

/**
 * Implements a reports registry class.
 * Used to fetch reports data.
 *
 * @since 1.0.0
 *
 * @see   \AffWP\Utils\Registry
 */
class Reports_Registry extends \AffWP\Utils\Registry {

	use Traits\Static_Registry, Traits\Error_Handler, Traits\Registry_Filter;

	/**
	 * Sets up the registry.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->register_reports();
	}

	/**
	 * Registers REST routes.
	 *
	 * @since 1.0.0
	 */
	public function register_reports() {

		$this->add_report_type( 'referralsCount', array(
			'callback' => function( $args ) {
				$defaults = array(
					'status'       => array(),
					'date'         => array(),
					'affiliate_id' => 0,
				);

				$args = wp_parse_args( $args, $defaults );

				return affwp_count_referrals( $args['affiliate_id'], $args['status'], $args['date'] );
			},
		) );

		$this->add_report_type( 'visitsCount', array(
			'callback' => function( $args ) {
				$defaults = array(
					'date'         => array(),
					'affiliate_id' => 0,
				);

				$args = wp_parse_args( $args, $defaults );

				return affwp_count_visits( $args['affiliate_id'], $args['date'] );
			},
		) );

		$this->add_report_type( 'conversionRate', array(
			'callback' => function( $args ) {
				$defaults = array(
					'affiliate_id' => 0,
					'date'         => array(),
					'formatted'    => true,
				);

				$args = wp_parse_args( $args, $defaults );

				$rate = 0;

				$referrals = affwp_count_referrals( $args['affiliate_id'], array( 'paid', 'unpaid' ), $args['date'] );
				$visits    = affwp_count_visits( $args['affiliate_id'], $args['date'] );

				if ( $visits > 0 ) {
					$rate = affwp_calculate_percentage( $referrals, $visits );
				}

				if ( true === $args['formatted'] ) {
					$rate = affwp_format_percentage( $rate, 2 );
				}

				return $rate;
			},
		) );

		$this->add_report_type( 'earnings', array(
			'callback' => function( $args ) {
				$defaults = array(
					'affiliate_id' => 0,
					'formatted'    => true,
					'status'       => '',
				);

				$args = wp_parse_args( $args, $defaults );

				switch ( $args['status'] ) {
					case 'paid':
						$earnings = affwp_get_affiliate_earnings( $args['affiliate_id'], (bool) $args['formatted'] );
						break;
					case 'unpaid':
						$earnings = affwp_get_affiliate_unpaid_earnings( $args['affiliate_id'], (bool) $args['formatted'] );
						break;
					default:
						$paid     = affwp_get_affiliate_earnings( $args['affiliate_id'] );
						$unpaid   = affwp_get_affiliate_unpaid_earnings( $args['affiliate_id'] );
						$earnings = $paid + $unpaid;
						if ( true === $args['formatted'] ) {
							$earnings = affwp_currency_filter( affwp_format_amount( $earnings ) );
						}
				}

				return $earnings;
			},
		) );

		$this->add_report_type( 'commissionRate', array(
			'callback' => function( $args ) {
				$defaults = array(
					'affiliate_id' => 0,
					'formatted'    => true,
				);

				$args = wp_parse_args( $args, $defaults );

				return affwp_get_affiliate_rate( $args['affiliate_id'], (bool) $args['formatted'] );
			},
		) );
	}

	/**
	 * Adds an report to the registry.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $report_slug Report slug (unique).
	 * @param array   $attributes  {
	 *     Attributes associated with the report.
	 *     @type callable $callback    Callback to fire when this report type is requested.
	 * }
	 * @return true|\WP_Error True on success, otherwise \WP_Error object.
	 */
	public function add_report_type( $report_slug, $attributes ) {

		if ( $this->offsetExists( $report_slug ) ) {
			$this->add_error( 'duplicate_report_slug',
				sprintf( 'The %s report type already exists.', $report_slug ),
				$attributes
			);
		}

		if ( ! isset( $attributes['callback'] ) || ! is_callable( $attributes['callback'] ) ) {
			$this->add_error( 'invalid_callback',
				sprintf( 'The %s report callback is invalid.', $report_slug ),
				$attributes
			);
		}

		if ( $this->has_errors() ) {
			return $this->get_errors();
		}

		return parent::add_item( $report_slug, $attributes );
	}
}
