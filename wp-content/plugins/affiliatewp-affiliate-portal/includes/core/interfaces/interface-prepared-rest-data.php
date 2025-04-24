<?php
/**
 * Core: Prepared REST Data Interface
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core/Interfaces
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Core\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extends Registry items to include a method to prepare rest objects.
 *
 * @since 1.0.0
 */
interface Prepared_REST_Data {

	/**
	 * Prepares this instance for output via REST.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args    {
	 *     Optional. Any arguments needed for preparing an item for a REST response.
	 *
	 *     @type array $query   Arguments to pass along to the object query for populating a Table_Control.
	 *     @type bool  $columns Whether to retrieve the columns for a Table_Control request.
	 *     @type bool  $rows    Whether to retrieve the rows for a Table_Control request.
	 * }
	 */
	public function prepare_rest_object( $args = array() );

}
