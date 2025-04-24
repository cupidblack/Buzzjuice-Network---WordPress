<?php
/**
 * Controls: Table Column Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @copyright   Copyright (c) 2021, Awesome Motive Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

/**
 * Marker class for implementing a single table column.
 *
 * @since 1.0.0
 *
 * @see Schema_Data_Control
 */
final class Table_Column_Control extends Schema_Data_Control {

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'replaces_column' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

}
