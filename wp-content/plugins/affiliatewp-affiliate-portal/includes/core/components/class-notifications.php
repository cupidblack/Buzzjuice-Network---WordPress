<?php
/**
 * Components: Notifications API
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core/Components
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use function AffiliateWP_Affiliate_Portal\html;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

#[\AllowDynamicProperties]

/**
 * Class used to implement a Notifications panel in the affiliate portal.
 *
 * @since 1.0.0
 */
class Notifications {

	/**
	 * Whether to allow notifications.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $allow_notifications = false;

	/**
	 * Current affiliate ID.
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	public $affiliate_id;

	/**
	 * Sets up the class.
	 *
	 * @since 1.0.0
	*/
	public function __construct() {
		$this->affiliate_id = affwp_get_affiliate_id();

		/*
		 * For now, the only instance where we will allow notifications is if
		 * the Payouts Service is enabled.
		 */
		if ( true === affiliate_wp()->affiliates->payouts->service_register->is_service_enabled() ) {
			$this->allow_notifications = true;
		}

	}

	/**
	 * Renders the Payouts Service notice.
	 *
	 * @since 1.0.0
	 */
	public function notice_payouts_service() {

		$payouts_service_notice = affiliate_wp()->settings->get( 'payouts_service_notice', '' );
		$payouts_service_account_meta = affwp_get_affiliate_meta( $this->affiliate_id, 'payouts_service_account', true );

		if ( ! $payouts_service_account_meta && $payouts_service_notice ) : ?>
			<div>
				<p class="text-sm"><?php echo wp_kses_post( nl2br( $payouts_service_notice ) ); ?></p>
			</div>
		<?php endif;

		return;
	}

	/**
	 * Renders the Notifications panel.
	 *
	 * @since 1.0.0
	 */
	public function display() {

		// Exit early if no notifications are allowed.
		if ( ! $this->allow_notifications ) {
			return;
		}

		?>
		<div x-show="notificationsOpen" class="" style="display: none;">
			<div class="fixed inset-0 flex z-40">

			<div @click="notificationsOpen = false" x-show="notificationsOpen" x-description="Off-canvas menu overlay, show/hide based on off-canvas menu state." x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0" style="display: none;">
					<div class="absolute inset-0"></div>
				</div>

				<div x-show="notificationsOpen" x-description="Off-canvas menu, show/hide based on off-canvas menu state." x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="inset-y-0 right-0 absolute flex-1 flex flex-col max-w-xs w-full pt-5b pb-4 bg-white shadow-xl" style="display: none;">

					<div class="absolute top-0 left-0 -ml-14 p-1">
						<button x-show="notificationsOpen" @click="notificationsOpen = false" class="flex items-center justify-center h-12 w-12 rounded-full focus:outline-none focus:bg-gray-600" aria-label="Close sidebar" style="display: none;">
							<?php
							$close_icon = new Controls\Icon_Control( array(
								'id'   => 'close-icon',
								'args' => array(
									'name'  => 'x',
									'color' => 'black',
									'size'  => 6,
								),
							) );

							if ( ! $close_icon->has_errors() ) {
								$close_icon->render();
							} else {
								$close_icon->log_errors( 'notifications' );
							}
							?>
						</button>
					</div>

					<div class="mt-5 flex-1 h-0 overflow-y-auto p-5">
						<?php
							// Only show payouts notice for now.
							echo $this->notice_payouts_service();
						?>
					</div>

				</div>
				<div class="flex-shrink-0 w-14">
					<?php
					/**
					 * Dummy element to force sidebar to shrink to fit close icon.
					 */
					?>
				</div>
			</div>
		</div>
		<?php
	}
}
