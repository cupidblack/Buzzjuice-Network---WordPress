<?php
/**
 * Core: Base View Template
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
use AffiliateWP_Affiliate_Portal\Core\Components\Portal;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use function AffiliateWP_Affiliate_Portal\html;

$affiliate_id      = affwp_get_affiliate_id();
$affiliate_user_id = affwp_get_affiliate_user_id();

$feedback_enabled = affiliate_wp()->settings->get( 'portal_allow_affiliate_feedback' );

if ( ! $feedback_enabled && 'feedback' === Portal::get_current_view_slug() || ! Portal::view_can_render() ) {
	wp_redirect( Portal::get_page_url( 'home' ) );
	exit;
}

$logo = affiliate_wp()->settings->get( 'portal_logo' );

?>
<html class="no-js" <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php wp_head(); ?>
	</head>

	<body class="antialiased font-sans">

		<div class="portal h-screen flex bg-gray-100" x-data="{ sidebarOpen: false, notificationsOpen: false }" @keydown.window.escape="{ sidebarOpen = false, notificationsOpen = false }">
			<?php
			/**
			 * Off-canvas menu for mobile.
			 */
			?>
			<div x-show="sidebarOpen" class="md:hidden" style="display: none;">
				<div class="fixed inset-0 flex z-40">
					<div @click="sidebarOpen = false" x-show="sidebarOpen" x-description="Off-canvas menu overlay, show/hide based on off-canvas menu state." x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0" style="display: none;">
						<div class="absolute inset-0 bg-gray-600 opacity-75"></div>
					</div>
					<div x-show="sidebarOpen" x-description="Off-canvas menu, show/hide based on off-canvas menu state." x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex-1 flex flex-col max-w-xs w-full pt-5b pb-4 bg-gray-800" style="display: none;">
						<div class="absolute top-0 right-0 -mr-14 p-1">
							<button x-show="sidebarOpen" @click="sidebarOpen = false" class="flex items-center justify-center h-12 w-12 rounded-full focus:outline-none focus:bg-gray-600" aria-label="Close sidebar" style="display: none;">
								<?php
								$close_sidebar_icon = new Controls\Icon_Control( array(
									'id'   => 'close',
									'args' => array(
										'name'  => 'x',
										'color' => 'white',
										'size'  => 6,
									),
								) );

								if ( ! $close_sidebar_icon->has_errors() ) {
									$close_sidebar_icon->render();
								} else {
									$close_sidebar_icon->log_errors( 'template' );
								}
								?>
							</button>
						</div>

						<?php if ( ! empty( get_bloginfo( 'name' ) ) || ! empty( $logo ) ) : ?>
							<div class="items-center h-16 flex-shrink-0 flex px-4 bg-gray-800">
								<?php if ( ! empty( $logo ) ) : ?>
									<!-- Logo -->
									<div class="w-full h-full bg-contain bg-left bg-no-repeat mt-2" style="background-image: url(<?php echo esc_url( $logo ); ?>)"></div>
								<?php elseif ( ! empty( get_bloginfo( 'name' ) ) ) : ?>
									<!-- Site name -->
									<span class="text-white">
										<?php echo get_bloginfo( 'name' ); ?>
									</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="bg-gray-800 px-2 <?php echo empty( get_bloginfo( 'name' )  ) ? 'pt-4' : ''; ?>">
							<?php
							$bts_link = new Controls\Link_Control( array(
								'id'   => 'back-to-site-link',
								'atts' => array(
									'class' => array(
										'mt-1',
										'group',
										'flex',
										'items-center',
										'px-2',
										'py-2',
										'font-medium',
										'focus:outline-none',
										'transition',
										'ease-in-out',
										'duration-150',
										'text-sm',
										'leading-5',
										'hover:text-gray-300',
										'text-gray-400',
									),
									'href'  => home_url(),
								),
								'args' => array(
									'label'         => __( 'Back to site', 'affiliatewp-affiliate-portal' ),
									'icon_position' => 'before',
									'icon'          => new Controls\Icon_Control( array(
										'id' => 'back-to-site-link-icon',
										'args' => array(
											'name'  => 'arrow-circle-left',
										),
									) ),
								),
							) );

							if ( ! $bts_link->has_errors() ) {
								$bts_link->render();
							} else {
								$bts_link->log_errors( 'template' );
							}
							?>
						</div>

						<div class="mt-5 flex-1 h-0 overflow-y-auto">
							<nav class="px-2">
								<?php Portal::get_navigation( true );  ?>
							</nav>
						</div>
					</div>
					<div class="flex-shrink-0 w-14">
						<?php
						/**
						 * Dummy element to force sidebar to shrink to fit close icon
						 */
						?>
					</div>
				</div>
			</div>

			<?php
			/**
			 * Notifications.
			 */
			?>
			<?php echo affiliatewp_affiliate_portal()->notifications->display(); ?>

			<?php
			/**
			 * Static sidebar for desktop.
			 */
			?>
			<div class="hidden md:flex md:flex-shrink-0">
				<div class="flex flex-col w-64">
					<?php if ( ! empty( get_bloginfo( 'name' ) ) || ! empty( $logo ) ) : ?>
						<div class="items-center h-16 flex-shrink-0 flex px-4 bg-gray-800">
							<?php if ( ! empty( $logo ) ) : ?>
								<!-- Logo -->
								<div class="w-full h-full bg-contain bg-left bg-no-repeat mt-2" style="background-image: url(<?php echo esc_url( $logo ); ?>)"></div>
							<?php elseif ( ! empty( get_bloginfo( 'name' ) ) ) : ?>
								<!-- Site name -->
								<span class="text-white">
									<?php echo get_bloginfo( 'name' ); ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="bg-gray-800 pl-2 <?php echo empty( get_bloginfo( 'name' )  ) ? 'pt-4' : ''; ?>">
						<?php
						if ( ! $bts_link->has_errors() ) {
							$bts_link->render();
						} else {
							$bts_link->log_errors( 'template' );
						}
						?>
					</div>
					<div class="h-0 flex-1 flex flex-col overflow-y-auto">
						<nav class="flex-1 px-2 py-4 bg-gray-800">
							<?php Portal::get_navigation(); ?>
						</nav>
					</div>
				</div>
			</div>
			<div class="flex flex-col w-0 flex-1 overflow-hidden">
				<div class="relative z-10 flex-shrink-0 flex h-16 bg-white">
					<button @click.stop="sidebarOpen = true" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:bg-gray-100 focus:text-gray-600 md:hidden" aria-label="Open sidebar">
						<?php
						$open_sidebar_icon = new Controls\Icon_Control( array(
							'id'   => 'open_sidebar',
							'args' => array(
								'name' => 'menu-alt-2',
								'size' => 6,
							),
						) );

						if ( ! $open_sidebar_icon->has_errors() ) {
							$open_sidebar_icon->render();
						} else {
							$open_sidebar_icon->log_errors( 'template' );
						}
						?>
					</button>
					<div class="flex-1 px-4 flex justify-between">
						<div class="flex-1 flex">
							<?php
							/**
							 * This will be utilized in the future for affiliate functionality.
							 */
							?>
						</div>
						<div class="ml-4 flex items-center md:ml-6">

							<?php
								$allow_feedback = affiliate_wp()->settings->get( 'portal_allow_affiliate_feedback' );
								if ( $allow_feedback ) :
							?>
							<div class="relative p-1 text-gray-400 relative p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:shadow-outline focus:text-gray-500">
								<?php
								$feedback_icon = new Controls\Link_Control( array(
									'id'   => 'feedback-link',
									'args' => array(
										'icon_position' => 'after',
										'icon'          => new Controls\Icon_Control( array(
											'id'   => 'feedback-link-icon',
											'args' => array(
												'name' => 'chat-alt',
												'color' => 'bg-gray-100',
												'size'  => 6,
											),
										) ),
									),
									'atts' => array(
										'href'   => esc_url( Portal::get_page_url( 'feedback' ) ),
									),
								) );

								if ( ! $feedback_icon->has_errors() ) {
									$feedback_icon->render();
								} else {
									$feedback_icon->log_errors( 'template' );
								}
								?>
							</div>
							<?php endif; ?>

							<?php
							$payouts_service_notice = affiliate_wp()->settings->get( 'payouts_service_notice', '' );
							$payouts_service_account_meta = affwp_get_affiliate_meta( $affiliate_id, 'payouts_service_account', true );

							/**
							 * Notifications icon.
							 *
							 * Currently only shown if the Payouts Service is enabled.
							 */
							if ( affiliate_wp()->affiliates->payouts->service_register->is_service_enabled()
								&& ! $payouts_service_account_meta && $payouts_service_notice ) : ?>

								<button @click.stop="notificationsOpen = true" class="relative p-1 text-gray-400 rounded-full hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:shadow-outline focus:text-gray-500" aria-label="Notifications">
									<span class="flex absolute h-3 w-3 top-0 right-0 -mt-2b -mr-2b">
										<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
										<span class="relative inline-flex rounded-full h-3 w-3 bg-pink-500"></span>
									</span>

									<?php
									$notify_icon = new Controls\Icon_Control( array(
										'id'   => 'notify',
										'args' => array(
											'name' => 'bell',
											'size' => 6,
										),
									) );

									if ( ! $notify_icon->has_errors() ) {
										$notify_icon->render();
									} else {
										$notify_icon->log_errors( 'template' );
									}
									?>
								</button>
							<?php endif; ?>

							<?php
							/**
							 * Profile dropdown
							 */
							?>
							<div @click.away="open = false" class="ml-3 relative" x-data="{ open: false }">
								<div>
									<button @click="open = !open" class="max-w-xs flex items-center text-sm rounded-full focus:outline-none focus:shadow-outline" id="user-menu" aria-label="User menu" aria-haspopup="true" x-bind:aria-expanded="open" aria-expanded="false">
										<?php echo get_avatar( $affiliate_user_id, '', '', '', array('class' => 'h-8 w-8 rounded-full'));

										$sort_down_icon = new Controls\Icon_Control( array(
											'id'   => 'sort_down',
											'args' => array(
												'name'  => 'chevron-down',
												'type'  => 'solid',
												'color' => 'gray-500',
												'size'  => 5,
											),
											'atts' => array(
												'class' => array( 'ml-1' ),
											),
										) );

										if ( ! $sort_down_icon->has_errors() ) {
											$sort_down_icon->render();
										} else {
											$sort_down_icon->log_errors( 'template' );
										}
										?>
									</button>
								</div>

								<div x-show="open" x-description="Profile dropdown panel, show/hide based on dropdown state." x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 min-w-150 rounded-md shadow-lg" style="display: none;">

									<div class="rounded-md bg-white shadow-xs">
										<div class="px-4 py-3">
											<p class="text-sm leading-5">
												<?php _e( 'Signed in as', 'affiliatewp-affiliate-portal' ); ?>
											</p>
											<p class="text-sm leading-5 font-medium text-gray-900">
												<?php echo esc_html( affwp_get_affiliate_email( $affiliate_id ) ); ?>
											</p>
										</div>
										<div class="border-t border-gray-100"></div>
										<div class="py-1">
											<a class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:bg-gray-100 focus:text-gray-900" href="<?php echo esc_url( Portal::get_page_url( 'settings' ) ); ?>"><?php _e( 'Settings', 'affiliatewp-affiliate-portal' ); ?></a>
										</div>
										<div class="border-t border-gray-100"></div>
										<div class="py-1">
											<a class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:bg-gray-100 focus:text-gray-900" href="<?php echo esc_url(affwp_get_logout_url()); ?>">
												<?php _e( 'Sign out', 'affiliatewp-affiliate-portal' ); ?>
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>

				<?php
				/**
				 * Content area.
				 */
				?>
				<main id="portal-content-wrap" class="flex-1 relative overflow-y-auto py-6 focus:outline-none" tabindex="0" x-data="" x-init="$el.focus()">
					<?php echo Portal::get_current_view(); ?>
				</main>
			</div>
		</div>
	</body>

	<?php if ( version_compare( AFFILIATEWP_VERSION, '2.17.2', '>=' ) ) : ?>
		<div class="qr-code-scripts">
			<?php // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript -- We need to manually load this below because we can't use wp_footer() here. ?>
			<div id="affwp-modal-container"></div>
			<script>window.affiliatewpCreativesData = {};</script>
			<script src="<?php echo esc_url( plugins_url( '/assets/js/affiliatewp.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
			<script src="<?php echo esc_url( plugins_url( '/assets/js/affiliatewp-modal.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
			<script src="<?php echo esc_url( plugins_url( '/assets/js/affiliatewp-infinite-scroll.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
			<script src="<?php echo esc_url( plugins_url( '/assets/js/vendor/crypto/md5.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
			<script src="<?php echo esc_url( plugins_url( '/assets/js/affiliatewp-crypto.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
			<script src="<?php echo esc_url( plugins_url( '/assets/js/vendor/qrcode/node-qrcode-build.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
			<script src="<?php echo esc_url( plugins_url( '/assets/js/affiliatewp-qrcode.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
			<script src="<?php echo esc_url( plugins_url( '/assets/js/affiliatewp-creatives.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>

			<?php if ( defined( 'AFFWP_MTC_PLUGIN_URL' ) ) : ?>
				<script src="<?php echo esc_url( plugins_url( '/assets/js/vendor/popper/popper.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
				<script src="<?php echo esc_url( plugins_url( '/assets/js/vendor/tippy/tippy.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
				<script src="<?php echo esc_url( plugins_url( '/assets/js/affiliatewp-tooltip.min.js', AFFILIATEWP_PLUGIN_FILE ) ); ?>"></script>
				<script src="<?php echo esc_url( sprintf( '%1$sassets/js/affiliatewp-mtc%2$s.js', AFFWP_MTC_PLUGIN_URL, affiliate_wp()->scripts->get_suffix() ) ); ?>"></script>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</html>
