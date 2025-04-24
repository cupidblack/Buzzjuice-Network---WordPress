<?php
/**
 * Core: Registry Middleware
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core;

use AffiliateWP_Affiliate_Portal\Core\Interfaces\Prepared_REST_Data;
use AffiliateWP_Affiliate_Portal\Core\Traits;

#[\AllowDynamicProperties]

/**
 * Implements Affiliate Portal middleware with AffiliateWP core's base registry class.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Utils\Registry
 */
abstract class Registry extends \AffWP\Utils\Registry {

	use Traits\Error_Handler;

	/**
	 * Retrieves REST-ready items from the registry.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Item type.
	 * @return array|\WP_REST_Response|\WP_Error (Maybe filtered) sections.
	 */
	public function get_rest_items( $type ) {
		$all_items = $this->get_items();

		$items = array();

		foreach ( $all_items as $item_id => $atts ) {
			if ( is_object( $atts ) ) {
				$id_prop        = "{$type}Id";
				$atts->$id_prop = $item_id;
				$items[]        = $atts;
			} else {
				$items[] = array_merge( array( "{$type}Id" => $item_id ), $atts );
			}
		}

		$items = rest_ensure_response( $items );

		return $items;
	}

	/**
	 * Retrieves a given REST-ready item from the registry.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type    Item type.
	 * @param string $item_id Item ID.
	 * @param array  $args    {
	 *     Optional. Any arguments needed for preparing an item for a REST response.
	 *
	 *     @type array $query   Arguments to pass along to the object query for populating a Table_Control.
	 *     @type bool  $columns Whether to retrieve the columns for a Table_Control request.
	 *     @type bool  $rows    Whether to retrieve the rows for a Table_Control request.
	 * }
	 * @return array|\WP_REST_Response|\WP_Error (Maybe filtered) section.
	 */
	public function get_rest_item( $type, $item_id, $args = array() ) {
		if ( ! $this->offsetExists( $item_id ) ) {
			$this->add_error( "invalid_{$type}",
				sprintf( 'The \'%1$s\' %2$s does not exist.', $item_id, $type ) );
		} else {
			$item = $this->get( $item_id );
			if ( is_object( $item ) ) {
				$id_prop        = "{$type}Id";
				$item->$id_prop = $item_id;

				// If necessary, prepare the rest response.
				if ( $item instanceof Prepared_REST_Data ) {
					if ( ! isset( $args['affiliate_id'] ) ) {
						$args['affiliate_id'] = affwp_get_affiliate_id();
					}

					$item->prepare_rest_object( $args );
				}

			} else {
				$item = array_merge( array( "{$type}Id" => $item_id ), $item );
			}

			$item = rest_ensure_response( $item );
		}

		if ( $this->has_errors() ) {
			return $this->get_errors();
		} else {
			return $item;
		}
	}
}
