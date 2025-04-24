<?php
/**
 * Traits: Data Validator
 *
 * @package   Core/Traits
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Core\Traits;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

/**
 * Implements logic for a class to handle validating data from a standardized validator.
 *
 * @since 1.0.0
 */
trait Data_Validator {

	use Control_Bootstrap;

	/**
	 * Validates the control data.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data Data to validate.
	 *
	 * @return boolean true if valid, otherwise false.
	 */
	public function validate_control_data( $data ) {
		$control      = $this->get_control();
		$affiliate_id = $this->get_affiliate_id();

		$validate_callback = $control->get_argument( 'validate_callback', array( $this, 'validate_data' ) );

		if ( is_callable( $validate_callback ) ) {
			$valid = call_user_func_array( $validate_callback, array( $data, $affiliate_id ) );
		} else {
			$valid = false;
		}

		return (bool) $valid;
	}

}
