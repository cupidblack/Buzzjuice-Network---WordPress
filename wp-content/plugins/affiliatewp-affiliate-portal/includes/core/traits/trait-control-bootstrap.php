<?php
/**
 * Traits: Control Bootstrap
 *
 * @package   Core/Traits
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Traits;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

/**
 * Implements bootstrap logic for a control.
 *
 * @since 1.0.0
 */
trait Control_Bootstrap {

	/**
	 * Retrieves the control object.
	 *
	 * @since 1.0.0
	 *
	 * @return Controls\Base_Control $this Control object.
	 */
	public function get_control() {
		/** @var Controls\Base_Control $this */
		return $this;
	}

}
