<?php
/**
 * View: Course Pricing Closed With No Price and Url.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var Product $product Product model.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Product;

?>
<div class="ld-enrollment__pricing ld-enrollment__pricing--closed">
	<span class="ld-enrollment__pricing-price">
		<?php esc_html_e( 'Closed', 'learndash' ); ?>
	</span>

	<span class="ld-enrollment__pricing-label">
		<?php
		printf(
			// translators: placeholder: course label.
			esc_html_x( 'This %s is currently closed', 'placeholder: Course label', 'learndash' ),
			esc_html( $product->get_type_label( true ) )
		);
		?>
	</span>
</div>
