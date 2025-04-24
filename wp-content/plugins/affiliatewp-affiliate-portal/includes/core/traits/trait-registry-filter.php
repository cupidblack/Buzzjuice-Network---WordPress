<?php
/**
 * Traits: Registry Filter
 *
 * @package   Core/Traits
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implements filter functionality for registries.
 *
 * @since 1.0.0
 */
trait Registry_Filter {

	/**
	 * Queries the given registry fields against a set of specific parameters.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     List of field values keyed by the registered key. Fields can be appended with __in or __not_in
	 *     to automatically filter by a list of values.
	 *
	 *     @type string $key           Item key to explicitly query items for. `$key` represents the key name.
	 *     @type array  $key__in       Item keys to include.
	 *     @type array  $field__in     Dynamic filter where the `$field` portion of the argument name represents the
	 *                                 field name and the value(s) represent the values to query matching items for.
	 *     @type array  $field__not_in Dynamic filter where the `$field` portion of the argument name represents the
	 *                                 field name and the value(s) represent the values used to exclude matching items.
	 * }
	 * @param bool  $original_format Optional. Whether to return items in their original format. Default true.
	 * @return array Fields filtered by the specified parameters.
	 */
	public function query( $args = array(), $original_format = true ) {
		$results = array();

		$all_items = $this->get_items();
		$items     = $this->get_prepared_items();

		// Filter out IDs before starting.
		if ( isset( $args['key__in'] ) ) {
			$items = array_intersect_key( $items, array_flip( $args['key__in'] ) );
			unset( $args['key__in'] );
		}

		// Loop through filtered items, and get final set of items.
		foreach ( $items as $item_key => $item_value ) {
			$valid = true;

			foreach ( $args as $key => $arg ) {
				// Process the argument key
				$processed = explode( '__', $key );

				// Set the field type to the first item in the array.
				$field = $processed[0];

				// If there was some specificity after a __, use it.
				$type = count( $processed ) > 1 ? $processed[1] : 'in';

				// Bail early if this field is not in this item.
				if ( ! isset( $item_value[ $field ] ) ) {
					$valid = false;
					continue;
				}

				$object_field = $item_value[ $field ];

				// Convert argument to an array. This allows us to always use array functions for checking.
				if ( ! is_array( $arg ) ) {
					$arg = array( $arg );
				}

				// Convert field to array. This allows us to always use array functions to check.
				if ( ! is_array( $object_field ) ) {
					$object_field = array( $object_field );
				}

				// Run the intersection.
				$fields = array_intersect( $arg, $object_field );

				// Check based on type.
				switch ( $type ) {
					case 'not_in':
						$valid = empty( $fields );
						break;
					case 'and':
						$valid = count( $fields ) === count( $arg );
						break;
					default:
						$valid = ! empty( $fields );
						break;
				}

				if ( false === $valid ) {
					break;
				}
			}

			if ( true === $valid ) {
				$results[ $item_key ] = $item_value;
			}
		}

		if ( true === $original_format ) {
			foreach ( $results as $item_id => $item_value ) {
				$results[ $item_id ] = $all_items[ $item_id ];
			}
		}

		return $results;
	}

	/**
	 * Retrieves registry items prepared to be queried.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Items.
	 */
	public function get_prepared_items() {
		$all_items = $this->get_items();

		$items = array();

		foreach ( $all_items as $item_id => $item ) {
			if ( is_array( $item ) ) {
				$items[ $item_id ] = $item;
			} elseif ( is_object( $item ) && method_exists( $item, 'to_array' ) ) {
				$items[ $item_id ] = $item->to_array();
			} else {
				$items[ $item_id ] = (array) $item;
			}
		}

		return $items;
	}
}