<?php
/**
 * Core: Integration Interface
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Interfaces;

/**
 * Interface for defining basic structure for an integration.
 *
 * @since 1.0.0
 */
interface Integration {

	/**
	 * Sets up initialization of the integration code.
	 *
	 * Used instead of constructor so methods within the implementing class can
	 * be externally accessible without re-registering hooks or running one-time
	 * setup code.
	 *
	 * @since 1.0.0
	 */
	public function init();

}
