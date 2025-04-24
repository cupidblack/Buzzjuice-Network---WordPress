<?php
/**
 * Controls: Network Tree Code Control
 *
 * @since       1.3.1
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2024, Awesome Motive Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use function AffiliateWP\MTC\affiliate_wp_mtc;

/**
 * Implements the network tree code control.
 *
 * @since 1.1.1
 *
 * @see Base_Control
 */
final class Network_Tree_Control extends Base_Control {

	/**
	 * Sets up the control.
	 *
	 * @since 1.3.1
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
	 * }
	 * @param bool  $validate   Optional. Whether to validate the attributes (and split off any arguments). Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'network_tree';
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {

		$affiliate_id = affwp_get_affiliate_id();

		// Bail if the Multi-Tier Commissions is not active.
		if ( function_exists( 'affiliate_wp_mtc' ) ) {
			return;
		}

		ob_start();

		?>

		<script>
			document.addEventListener( 'DOMContentLoaded', function() {
				affiliatewp.mtc.initDraggable();
				<?php affiliate_wp_mtc()->network->render_network_tooltips_js( $affiliate_id ); ?>
			} );
		</script>

		<?php

		$output = sprintf(
			'<div class="affwp-network-notices">%1$s</div><div class="affwp-network">%2$s</div>%3$s',
			method_exists( affiliate_wp_mtc()->network, 'get_forced_matrix_notices' )
				? affiliate_wp_mtc()->network->get_forced_matrix_notices( $affiliate_id )
				: '',
			affiliate_wp_mtc()->network->convert_tree_to_html(
				affiliate_wp_mtc()->network->get_affiliate_tree( $affiliate_id )
			),
			ob_get_clean()
		);

		if ( false === $echo ) {
			return $output;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
		echo $output;
	}
}
