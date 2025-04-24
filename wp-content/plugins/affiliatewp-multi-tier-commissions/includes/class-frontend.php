<?php
/**
 * Multi Tier Commissions Frontend
 *
 * @package     AffiliateWP
 * @subpackage  AffiliateWP\MTC
 * @since       1.0.0
 * @copyright   Copyright (c) 2024, Awesome Motive, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP\MTC;

// Exit if accessed directly.
use AffiliateWP\Utils\Icons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multi Tier Commissions frontend business logic.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * Register callbacks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( ! affiliate_wp_mtc()->is_activated() ) {
			return; // Multi-Tier Commissions is disabled.
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		add_filter( 'affwp_affiliate_area_tabs', array( $this, 'add_tab' ) );
		add_filter( 'affwp_render_affiliate_dashboard_tab_network', array( $this, 'render_tab' ), 10, 2 );

		// Support shortcode.
		add_shortcode( 'affiliate_network', array( $this, 'render_network_shortcode' ) );
	}

	/**
	 * Load the styles and scripts necessary to render the Network.
	 *
	 * @since 1.0.0
	 */
	public function load_assets() {

		// This will be used straight away in front-end.
		wp_enqueue_style( 'affiliatewp-mtc' );

		// Enqueue the copy tool.
		affiliate_wp()->scripts->enqueue( 'affiliatewp-utils' );

		// Enqueue specific MTC scripts.
		affiliate_wp()->scripts->enqueue(
			'affiliatewp-mtc',
			array(
				'affiliatewp-tooltip',
			),
			sprintf(
				'%1$sassets/js/affiliatewp-mtc%2$s.js',
				AFFWP_MTC_PLUGIN_URL,
				affiliate_wp()->scripts->get_suffix()
			)
		);
	}

	/**
	 * Register and enqueue necessary assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {

		// We need to register, since this can be enqueued by other addons, like Portal.
		wp_register_style(
			'affiliatewp-mtc',
			sprintf(
				'%1$sassets/css/affiliatewp-mtc%2$s.css',
				AFFWP_MTC_PLUGIN_URL,
				affiliate_wp()->scripts->get_suffix()
			),
			array(),
			affiliate_wp()->scripts->get_version()
		);

		// Don't enqueue the scripts if is not the Affiliate Area.
		if ( ! affwp_is_affiliate_area() ) {
			return;
		}

		$this->load_assets();
	}

	/**
	 * Add the Network tab to the Affiliate Area tabs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs The array of registered tabs.
	 *
	 * @return array The updated tabs array.
	 */
	public function add_tab( array $tabs ) : array {

		$index = array_search( 'urls', array_keys( $tabs ), true ) + 1;

		return array_slice( $tabs, 0, $index, true ) +
			array( 'network' => esc_html__( 'Network', 'affiliatewp-multi-tier-commissions' ) ) +
			array_slice( $tabs, $index, null, true );
	}

	/**
	 * Render the Network tab content.
	 *
	 * @since 1.0.0
	 */
	public function render_tab() : string {

		ob_start();

		affiliate_wp()->templates->get_template_part( 'dashboard-tab', 'network' );

		return ob_get_clean();
	}

	/**
	 * Render the Network Link section.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Added affiliate link sharing and custom slug support.
	 */
	public function render_network_link() {
		$affiliate_id     = affwp_get_affiliate_id();
		$affiliate_url    = urldecode( affiliate_wp_mtc()->network->get_invitation_url() );
		$show_custom_slug = affiliate_wp()->settings->get( 'custom_affiliate_slugs_affiliate_show_slug' );
		$has_custom_slug  = function_exists( 'affiliatewp_custom_affiliate_slugs' ) ? ( affiliatewp_custom_affiliate_slugs()->base->get_slug( $affiliate_id ) ?? false ) : false;
		$has_links_class  = class_exists( 'AffiliateWP\Affiliate_Links' );
		?>

		<div class="affwp-card affwp-affiliate-link">

			<div class="affwp-card__header">
				<div>
					<h3>
						<?php esc_html_e( 'Your Network Link', 'affiliatewp-multi-tier-commissions' ); ?>
					</h3>
					<p>
						<?php esc_html_e( 'Invite other affiliates to your network using this link.', 'affiliatewp-multi-tier-commissions' ); ?>
					</p>
				</div>

				<?php
					$content = sprintf(
						'<p>%s</p>',
						esc_html__( 'Share this link to expand your network. New affiliates who join through your link can help boost your earnings by generating more referrals. Promote it on social media, in emails, or on your website.', 'affiliatewp-multi-tier-commissions' )
					);
				?>

				<?php if ( $has_links_class ) : // Tooltip needs updated version of Core. ?>
					<span class="affwp-card__tooltip" data-tippy-content="<?php echo esc_attr( $content ); ?>">
						<?php Icons::render( 'tooltip' ); ?>
					</span>
				<?php endif; ?>

			</div>

			<div class="affwp-card__content">
				<div class="affwp-affiliate-link__display">
					<input
						type="text"
						readonly
						class="affwp-affiliate-link__input"
						value="<?php echo esc_url( urldecode( $affiliate_url ) ); ?>"
						data-standard-url="<?php echo esc_url( urldecode( $affiliate_url ) ); ?>"
						data-slug-url="<?php echo esc_url( urldecode( affwp_get_affiliate_referral_url( array( 'format' => 'slug', 'base_url' => urldecode( $affiliate_url ) ) ) ) ); ?>"
					>
					<button
						id="affwp-network-copy-link"
						class="affwp-affiliate-link-copy-link button"
						data-content="<?php echo esc_attr( $affiliate_url ); ?>"
					><?php esc_html_e( 'Copy Link', 'affiliatewp-multi-tier-commissions' ); ?>
					</button>
				</div>
				<?php if ( 'slug' !== affwp_get_referral_format() && $has_custom_slug && $show_custom_slug ) : ?>
					<a href="#" class="affwp-affiliate-link__toggle" data-nonce="<?php echo esc_attr( wp_create_nonce( 'update-sharing-options_nonce' ) ); ?>">
						<?php esc_html_e( 'Use link with custom slug', 'affiliate-wp' ); ?>
					</a>
				<?php endif; ?>

				<?php

				// Sharing options from Core.
				if ( $has_links_class ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content already escaped.
					affiliate_wp()->affiliate_links->render_link_sharing_options( $affiliate_url );
				}

				?>
			</div>

		</div>

		<?php if ( ! $has_links_class ) : // Only use inline JS if there's no Affiliate_Links class. ?>
			<script>
				document.addEventListener( 'DOMContentLoaded', function() {
					affiliatewp.utils.copyButton( '#affwp-network-copy-link' );
				} );
			</script>
		<?php endif; ?>

		<?php

	}

	/**
	 * Render the Network Tree section.
	 *
	 * @since 1.0.0
	 */
	public function render_network() {

		$affiliate_id   = affwp_get_affiliate_id();
		$is_direct_view = 'direct' === filter_input( INPUT_GET, 'network_view', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		// We are temporarily disabling Forced Matrix actions.
		$is_forced_matrix = false;
		$has_links_class  = class_exists( 'AffiliateWP\Affiliate_Links' );
		?>

		<div class="affwp-card">

			<div class="affwp-card__header">
				<div>
					<h3>
						<?php esc_html_e( 'Your Network', 'affiliatewp-multi-tier-commissions' ); ?>
					</h3>
					<p>
						<?php esc_html_e( 'Connections between you and your recruited affiliates.', 'affiliatewp-multi-tier-commissions' ); ?>
					</p>
				</div>

				<?php
					$content = sprintf(
						'<p>%1$s</p><p>%2$s</p>',
						esc_html__( 'The network diagram shows the connections between you and your recruited affiliates, forming a branching, tree-like structure. It updates automatically as new affiliates join through your network link or the links of other affiliates in your network.', 'affiliatewp-multi-tier-commissions' ),
						esc_html__( 'Click and drag to explore, and hover over any affiliate to view additional details.', 'affiliatewp-multi-tier-commissions' )
					);
				?>

				<?php if ( $has_links_class ) : // Tooltip needs updated version of Core. ?>
					<span class="affwp-card__tooltip" data-tippy-content="<?php echo esc_attr( $content ); ?>">
						<?php Icons::render( 'tooltip' ); ?>
					</span>
				<?php endif; ?>

			</div>

			<div class="affwp-card__notices">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is safe.
				echo affiliate_wp_mtc()->network->get_forced_matrix_notices( $affiliate_id );
				?>
			</div>

			<?php if ( $is_forced_matrix ) : ?>

				<div class="affwp-card__actions">
					<?php
					if ( $is_direct_view ) {
						printf(
							'<a href="%s"><button>%s</button></a>',
							esc_url( affwp_get_affiliate_area_page_url( 'network' ) ),
							esc_html__( 'Display Expanded Network', 'affiliatewp-multi-tier-commissions' )
						);
					} else {
						printf(
							'<a href="%s"><button>%s</button></a>',
							esc_url( add_query_arg( [ 'network_view' => 'direct' ], affwp_get_affiliate_area_page_url( 'network' ) ) ),
							esc_html__( 'Display Direct Network Only', 'affiliatewp-multi-tier-commissions' )
						);
					}
					?>
				</div>

			<?php endif; ?>
			<div class="affwp-card__content affwp-network">
				<?php

				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Content already escaped.
				echo affiliate_wp_mtc()->network->convert_tree_to_html(
					affiliate_wp_mtc()->network->get_affiliate_tree(
						$affiliate_id,
						( $is_forced_matrix && ! $is_direct_view ) || ! $is_forced_matrix
					)
				);
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

				?>
			</div>
		</div>

		<script>
			document.addEventListener( 'DOMContentLoaded', function() {
				affiliatewp.mtc.initDraggable();
				<?php affiliate_wp_mtc()->network->render_network_tooltips_js( $affiliate_id ); ?>
			} );
		</script>

		<?php
	}

	/**
	 * Show the network for the logged in affiliate.
	 *
	 * [affiliate_mtc_network]
	 *
	 * @since  1.0.0
	 *
	 * @return string The shortcode results.
	 */
	public function render_network_shortcode() : string {

		// Ensure scripts and styles are loaded.
		$this->load_assets();

		return do_shortcode( $this->render_tab() );
	}
}

// Make Frontend object public available.
affiliate_wp_mtc()->frontend = new Frontend();
