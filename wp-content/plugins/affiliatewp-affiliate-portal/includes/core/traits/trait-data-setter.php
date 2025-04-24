<?php
/**
 * Traits: Data Setter
 *
 * @package   Core/Traits
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Traits;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

/**
 * Implements logic for a class to handle setting/saving data from a standardized setter.
 *
 * @since 1.0.0
 */
trait Data_Setter {

	use Control_Bootstrap;

	/**
	 * Sets/saves the control data.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data         Data to save.
	 * @param int   $affiliate_id Optional. Affiliate ID. Default is the current affiliate ID.
	 */
	public function save_control_data( $data, $affiliate_id = 0 ) {
		$control = $this->get_control();

		$save_callback = $control->get_argument( 'save_callback', array( $this, 'save_data' ) );

		if ( 0 === $affiliate_id ) {
			$affiliate_id = $control->get_affiliate_id();
		}

		if ( is_callable( $save_callback ) ) {
			call_user_func_array( $save_callback, array( $data, $affiliate_id ) );
		}
	}

	/**
	 * Sets/saves the data for the current control.
	 *
	 * @since 1.0.0
	 *
	 * @see get_control()
	 *
	 * @param mixed $data         Data to save.
	 * @param int   $affiliate_id Current affiliate ID.
	 * @return mixed Data for the current control.
	 */
	abstract public function save_data( $data, $affiliate_id );

}
