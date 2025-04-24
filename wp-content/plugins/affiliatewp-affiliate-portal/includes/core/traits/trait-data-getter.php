<?php
/**
 * Traits: Data Getter
 *
 * @package   Core/Traits
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Traits;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

/**
 * Implements logic for a class to handle getting data from a standardized getter.
 *
 * @since 1.0.0
 */
trait Data_Getter {

	use Control_Bootstrap;

	/**
	 * Retrieves the control data.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id Optional. Affiliate ID. Default is the current affiliate ID.
	 * @return mixed Saved control data.
	 */
	public function get_control_data( $affiliate_id = 0 ) {
		$control = $this->get_control();

		$get_callback = $control->get_argument( 'get_callback', array( $this, 'get_data' ) );

		if ( 0 === $affiliate_id ) {
			$affiliate_id = $control->get_affiliate_id();
		}

		$data = '';

		if ( is_callable( $get_callback ) ) {
			$data = call_user_func_array( $get_callback, array( $affiliate_id ) );
		}

		return $data;
	}

	/**
	 * Retrieves the saved data for the current control.
	 *
	 * @since 1.0.0
	 *
	 * @see get_control()
	 *
	 * @param int $affiliate_id Current affiliate ID.
	 * @return mixed Data for the current control.
	 */
	abstract public function get_data( $affiliate_id );

}
