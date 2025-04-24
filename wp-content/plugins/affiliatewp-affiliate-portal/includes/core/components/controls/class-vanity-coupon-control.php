<?php
/**
 * Controls: Vanity Coupon Code Control
 *
 * @since       1.1.1
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Awesome Motive Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use function AffiliateWP_Affiliate_Portal\html;
use AffiliateWP_Affiliate_Portal\Integrations;
use ACFWF\Models\Objects\Advanced_Coupon;

/**
 * Implements a single vanity coupon code control.
 *
 * @since 1.1.1
 *
 * @see Base_Control
 */
final class Vanity_Coupon_Control extends Base_Control {

	/**
	 * Sets up the control.
	 *
	 * @since 1.1.1
	 *
	 * @param array $metadata {
	 *     Metadata for setting up the current control. Arguments are optional unless otherwise stated.
	 *
	 *     @type string $id       Required. Globally-unique ID for the current control.
	 *     @type string $view_id  Required unless `$section` is also omitted. View ID to associate a registered
	 *                            control with.
	 *     @type string $section  Required unless `$view_id` is also omitted. Section to associate a registered
	 *                            control with.
	 *     @type int    $priority Priority within the section to display the control. Default 25.
	 *     @type string $parent   Parent (card group) control ID. Unused if not set.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 *     @type array    $args                {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type object $coupon_data Data for the current coupon.
	 *     }
	 * }
	 * @param bool   $validate   Optional. Whether to validate the attributes (and split off any arguments).
	 *                           Default true;
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'vanity_coupon';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'coupon_data' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base      = $this->get_id_base();
		$affiliate_id = affwp_get_affiliate_id();

		// Bail if the Vanity Coupon Codes add-on isn't present.
		if ( ! function_exists( 'affiliatewp_vanity_coupon_codes' ) ) {
			return;
		}

		$coupon = $this->get_argument( 'coupon_data', false );

		$output = '';

		if ( isset( $coupon->code ) ) {
			$output = $coupon->code;
		}

		// Bail if the coupon is empty or not an object.
		if ( empty( $coupon ) || ! isset( $coupon->id ) ) {
			if ( true === $echo ) {
				echo $output;
			} else {
				return $output;
			}
		}

		$coupon_id = $coupon->id;

		$pending = affiliatewp_vanity_coupon_codes()->db->is_pending( $coupon_id );

		ob_start();
		?>
		<div id="coupon-<?php echo esc_attr( $coupon_id ); ?>" x-data="{}" x-init="updateDisplayWhenPending('affwp-vcc-<?php echo esc_attr( $coupon_id ); ?>-pending')">
			<span><?php echo $coupon->code; ?></span>
			<!-- Edit link text added via Vanity Coupon Code Plugin JS -->
			<span><a id="affwp-vcc-<?php echo esc_attr( $coupon_id ); ?>" @click.stop.prevent="clickEditLink('affwp-vcc-<?php echo esc_attr( $coupon_id ); ?>');" class="affwp-vcc-edit-link not-sr-only font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150" href="#"></a></span>

			<div id="affwp-vcc-<?php echo esc_attr( $coupon_id ); ?>-form" class="affwp-vcc-coupon-form sr-only">
				<div class="block sm:flex my-2">
					<?php wp_nonce_field( 'request_vanity_coupon_code', 'vanity_coupon_codes_nonce'); ?>
					<div class="rounded-md shadow-sm w-full sm:w-64 sm:mr-2 mt-2">
						<input type="text" id="affwp-vcc-<?php echo esc_attr( $coupon_id ); ?>-input" class="affwp-vcc-coupon-code form-input block sm:text-sm sm:leading-5 w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out" name="vanity-coupon" data-id="<?php echo esc_attr( $coupon_id ); ?>" placeholder="<?php esc_attr_e( 'Enter a new coupon code', 'affiliatewp-affiliate-portal' ); ?>" data-affiliate="<?php echo esc_attr( $affiliate_id ); ?>" data-code="<?php echo esc_attr( $coupon->code ); ?>" data-integration="<?php echo esc_attr( $coupon->integration ); ?>" data-type="<?php echo esc_attr( $coupon->type ); ?>">
					</div>
					<div class="inline-flex rounded-md shadow-sm mt-2 w-full sm:w-1/5 md:w-24">
						<button id="affwp-vcc-<?php echo esc_attr( $coupon_id ); ?>-submit" class="py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md shadow-sm focus:outline-none focus:shadow-outline-blue transition duration-150 ease-in-out text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-600 w-full" type="submit" value="<?php echo esc_attr( _x( 'Request', 'Request vanity coupon code', 'affiliatewp-affiliate-portal' ) ); ?>" @click.stop.prevent="checkNewCode('affwp-vcc-<?php echo esc_attr( $coupon_id ); ?>-input')"><?php echo esc_attr( _x( 'Request', 'Request vanity coupon code', 'affiliatewp-affiliate-portal' ) ); ?></button>
					</div>
				</div>
				<div id="affwp-vcc-<?php echo esc_attr( $coupon_id ); ?>-error" class="affwp-vcc-error block text-red-600 setting text-control mt-1 text-sm"></div>
			</div>

			<div id="affwp-vcc-<?php echo esc_attr( $coupon_id ); ?>-pending" class="affwp-vcc-pending-code">
				<?php
				// Display the vanity codes awaiting review.
				if ( ! empty( $pending->vanity_code ) ) {
					/* translators: Vanity coupon code string */
					printf( __( 'Awaiting review: %s', 'affiliatewp-affiliate-portal' ), $pending->vanity_code );
				}
				?>
			</div>
		</div>
		<?php
		$output = ob_get_clean();

		// Support URL Coupons via the Advanced Coupons addon if it's active.
		if ( class_exists( 'ACFWF', false ) ) {
			$output = $this->support_coupon_url( $output, $coupon );
		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Support URL Coupons via the Advanced Coupons addon if it's active.
	 *
	 * @since 1.3.0
	 *
	 * @param string $output The output.
	 * @param object $coupon The coupon object.
	 *
	 * @return string The output.
	 */
	private function support_coupon_url( $output = '', $coupon ){
		// Bail if the Advanced Coupons addon isn't active.
		if ( ! class_exists( 'ACFWF', false ) ) {
			return $output;
		}

		// Bail if not a manual WooCommerce coupon.
		if ( 'woocommerce' !== $coupon->integration || 'manual' !== $coupon->type ) {
			return $output;
		}

		$acfw_integration = ( new Integrations\Advanced_Coupons );

		// Get coupon URL.
		$coupon_url = $acfw_integration->get_valid_coupon_url( $coupon );

		// Bail if the coupon URL is not valid.
		if ( false === $coupon_url ) {
			return $output;
		}

		// Set up the coupon URL control.
		$coupon_url_section = $acfw_integration->maybe_create_coupon_url_control( $coupon, $this->get_id_base(), $coupon_url );

		// If the coupon URL doesn't have errors, add it to the output.
		if ( ! $coupon_url_section->has_errors() ) {
			$output .= $coupon_url_section->render( false );
		}

		return $output;
	}

}
