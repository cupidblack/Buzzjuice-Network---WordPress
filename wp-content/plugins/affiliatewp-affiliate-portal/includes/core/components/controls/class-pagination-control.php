<?php
/**
 * Controls: Pagination
 *
 * @since 1.2.2
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 *
 * phpcs:disable PEAR.Functions.FunctionCallSignature.ContentAfterOpenBracket -- Opening parenthesis of a multi-line function call must be the last content on the line.
 * phpcs:disable PEAR.Functions.FunctionCallSignature.MultipleArguments -- Only one argument is allowed per line in a multi-line function call.
 * phpcs:disable PEAR.Functions.FunctionCallSignature.CloseBracketLine -- Closing parenthesis of a multi-line function call must be on a line by itself.
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;

/**
 * Pagination Control
 *
 * @since 1.2.2
 *
 * @see Base_Control
 */
final class Pagination_Control extends Base_Control {

	/** phpcs:ignore -- This is documented in includes/core/components/controls/class-base-control.php. */
	public function __construct( $metadata, $validate = true ) {

		$this->hooks();
		$this->set_args( $metadata );
		$this->validate_args();

		parent::__construct( $metadata, $validate );
	}

	/** phpcs:ignore -- This is documented in includes/core/components/controls/class-base-control.php. */
	public function get_type() {
		return 'pagination';
	}

	/**
	 * Hooks
	 *
	 * @since 1.2.2
	 */
	private function hooks() : void {

		static $hook_once = false;

		if ( $hook_once ) {
			return;
		}

		// Whitelist our own control the easy way (with a filter).
		add_filter( 'affwp_portal_control_types_whitelist', array( $this, 'whitelist_control' ) );

		$hook_once = true;
	}

	/** phpcs:ignore -- This is documented in includes/core/components/controls/class-base-control.php. */
	public function render( $echo = true ) {

		ob_start();

		$current_page = intval( $this->get_argument( 'current_page' ) );
		$pages        = ceil( intval( $this->get_argument( 'total' ) ) / intval( $this->get_argument( 'per_page' ) ) );
		$base_url     = untrailingslashit( $this->get_argument( 'base_url' ) ); // Used to form pagination URLs.

		?>

		<div class="px-4 py-3 flex items-center justify-between sm:px-6">
			<nav class="affwp-pagination relative z-0 inline-flex shadow-sm">

				<!-- Prev -->
				<a class="prev page-numbers flex items-center <?php echo esc_attr( intval( $current_page ) === 1 ? 'disabled' : '' ); ?>"
					href="<?php echo esc_url( $base_url ); ?>/1"
					id="creatives-table-prev-link"
					role="button">

					<svg class="ml-2 mr-2 h-5 w-5"
						id="creatives-table-prev-link-icon"
						viewbox="0 0 20 20">

						<path clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" fill-rule="evenodd"></path>
					</svg>
				</a>

				<!-- Pages -->
				<?php for ( $page = 1; $page <= $pages; $page ++ ) : ?>

					<a class="page-numbers <?php echo esc_attr( intval( $current_page ) === intval( $page ) ? 'disabled' : '' ); ?>"
						href="<?php echo esc_url( $base_url ); ?>/<?php echo absint( $page ); ?>"
						id="creatives-table-page-numbers"
						role="button">
							<?php echo absint( $page ); ?>
					</a>
				<?php endfor; ?>

				<!-- Next -->
				<a class="next page-numbers flex items-center <?php echo esc_attr( intval( $current_page ) === intval( $pages ) ? 'disabled' : '' ); ?>"
					href="<?php echo esc_url( $base_url ); ?>/<?php echo absint( $pages ); ?>"
					id="creatives-table-next-link"
					role="button">

					<svg class="ml-2 mr-2 h-5 w-5"
						id="creatives-table-next-link-icon"
						viewbox="0 0 20 20">

						<path clip-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" fill-rule="evenodd"></path>
					</svg>
				</a>
			</nav>
		</div>

		<?php

		if ( true !== $echo ) {
			return ob_get_clean();
		}

		// Can't use wp_kses_post, etc so just bypassing WPCS by sending the string unchanged through filter_var().
		echo filter_var( ob_get_clean(), FILTER_UNSAFE_RAW );
	}

	/**
	 * Set all the passed arguments.
	 *
	 * @since 1.2.2
	 *
	 * @param array $metadata Metadata from the construct.
	 */
	private function set_args( array $metadata ) : void {
		foreach ( isset( $metadata['args'] ) && is_array( $metadata['args'] ) ? $metadata['args'] : array() as $arg => $value ) {
			$this->set_argument( $arg, $value );
		}
	}

	/**
	 * Whitelist this control.
	 *
	 * @since 1.2.2
	 *
	 * @param array $whitelist Current whitelist.
	 *
	 * @return array
	 */
	public function whitelist_control( array $whitelist = array() ) : array {
		return array_merge(
			is_array( $whitelist )
				? $whitelist
				: array(),
			array( __CLASS__ )
		);
	}

	/**
	 * Validate arguments.
	 *
	 * @since 1.2.2
	 *
	 * @throws \InvalidArgumentException If an argument is found to be invalid.
	 */
	private function validate_args() {
		foreach ( array(
			array(
				'error_message' => "Argument 'per_page' must be a positive numeric value.",
				'test'          => ( is_int( $this->get_argument( 'per_page' ) ) && $this->get_argument( 'per_page' ) > 0 ),
			),
			array(
				'error_message' => "Argument 'current_page' must be a positive numeric value.",
				'test'          => ( is_int( $this->get_argument( 'current_page' ) ) && $this->get_argument( 'current_page' ) > 0 ),
			),
			array(
				'error_message' => "Argument 'total' must be a positive numeric value.",
				'test'          => ( is_int( $this->get_argument( 'total' ) ) && $this->get_argument( 'total' ) > 0 ),
			),
			array(
				'error_message' => "Argument 'base_url' must be a valid URL.",
				'test'          => filter_var( $this->get_argument( 'base_url' ), FILTER_VALIDATE_URL ),
			),
		) as $result ) {

			if ( false !== $result['test'] ) {
				continue;
			}

			throw new \InvalidArgumentException( $result['error_message'] );
		}
	}
}
