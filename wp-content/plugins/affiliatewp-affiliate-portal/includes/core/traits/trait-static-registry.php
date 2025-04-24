<?php
/**
 * Traits: Static Registry
 *
 * @package   Core/Traits
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Traits;

/**
 * Implements logic for handling a static instance.
 *
 * @since 1.0.0
 */
trait Static_Registry {

	/**
	 * The one true registry instance.
	 *
	 * @since 1.0.0
	 * @var   \AffWP\Utils\Registry
	 */
	private static $instance;

	/**
	 * Retrieves the one true registry instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \AffWP\Utils\Registry Registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
