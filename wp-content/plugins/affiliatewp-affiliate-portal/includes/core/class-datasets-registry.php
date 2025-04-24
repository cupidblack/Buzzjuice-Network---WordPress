<?php
/**
 * Core: Dataset Types Registry
 *
 * @since       1.0.0
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core;

use AffiliateWP_Affiliate_Portal\Utilities\Dataset_Parser;

#[\AllowDynamicProperties]

/**
 * Implements a datasets registry class.
 *
 * Used to fetch datasets data.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Utils\Registry
 */
class Datasets_Registry extends \AffWP\Utils\Registry {

	use Traits\Static_Registry, Traits\Error_Handler, Traits\Registry_Filter;

	/**
	 * Sets up the registry.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->register_datasets();
	}

	/**
	 * Registers datasets.
	 *
	 * @since 1.0.0
	 */
	public function register_datasets() {

		$this->add_dataset_type( 'referralEarnings', array(
			'callback' => function( $args ) {
				$defaults = array(
					'orderby'      => 'date',
					'order'        => 'ASC',
					'fields'       => 'date',
					'sum_fields'   => 'amount',
					'groupby'      => 'formatted_date',
					'number'       => -1,
					'affiliate_id' => 0,
				);

				$args = wp_parse_args( $args, $defaults );

				$dataset_parser = new Dataset_Parser( $args );

				$args['date']        = $dataset_parser->date_query;
				$args['date_format'] = $dataset_parser->mysql_date_format;

				$referrals = affiliate_wp()->referrals->get_referrals( $args );

				return $dataset_parser->backfill_data( $referrals );
			},
		) );

	}

	/**
	 * Adds an dataset to the registry.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $dataset_slug Dataset slug (unique).
	 * @param array   $attributes   {
	 *     Attributes associated with the dataset.
	 *
	 *     @type callable $callback Callback to fire when this dataset type is requested.
	 * }
	 * @return true|\WP_Error True on success, otherwise \WP_Error object.
	 */
	public function add_dataset_type( $dataset_slug, $attributes ) {

		if ( $this->offsetExists( $dataset_slug ) ) {
			$this->add_error( 'duplicate_dataset_slug',
				sprintf( 'The %s dataset type already exists.', $dataset_slug ),
				$attributes
			);
		}

		if ( ! isset( $attributes['callback'] ) || ! is_callable( $attributes['callback'] ) ) {
			$this->add_error( 'invalid_callback',
				sprintf( 'The %s dataset callback is invalid.', $dataset_slug ),
				$attributes
			);
		}

		if ( $this->has_errors() ) {
			return $this->get_errors();
		}

		return parent::add_item( $dataset_slug, $attributes );
	}
}
