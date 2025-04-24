<?php
/**
 * Tab for adding a new rule.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package yith-woocommerce-automatic-role-changer.premium\templates\admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( isset( $new_rule ) && $new_rule ) {
	$new_rule = true;
	$rule     = false;
} else {
	$new_rule = false;
}
?>
<div class="rule_block" data-rule_id="<?php echo esc_attr( $rule_id ); ?>">
	<div class="rule_head">
		<label class="rule_title">
		<?php
		if ( $new_rule ) {
			echo esc_attr( $title );
		} else {
			echo esc_attr( $rule['title'] );
		}
		?>
			</label>
		<button type="button" class="arrow_button">
			<span class="toggle-indicator"></span>
		</button>
	</div>
	<div class="rule_options">
		<p>
			<b><?php esc_html_e( 'The user will:', 'yith-automatic-role-changer-for-woocommerce' ); ?></b>
		</p>
		<div class="rule_type_block block">
			<p>
				<label for="ywarc_rule_type_radio_add[<?php echo esc_attr( $rule_id ); ?>]">
					<input id="ywarc_rule_type_radio_add[<?php echo esc_attr( $rule_id ); ?>]"
						class="ywarc_rule_type_radio_button"
						name="ywarc_typo_rule_radio[<?php echo esc_attr( $rule_id ); ?>]" type="radio"
						value="add"
						<?php
						$rule_type = ! empty( $rule['rule_type'] ) ? $rule['rule_type'] : 'add';
						if ( ! $new_rule ) {
							echo checked( $rule_type, esc_attr( 'add' ), false );
						}
						?>
					><?php echo esc_html_x( 'gain the role', 'Complete sentence: The user will gain the role', 'yith-automatic-role-changer-for-woocommerce' ); ?>
				</label>
			</p>
			<p>
				<label for="ywarc_rule_type_radio_replace[<?php echo esc_attr( $rule_id ); ?>]">
					<input id="ywarc_rule_type_radio_replace[<?php echo esc_attr( $rule_id ); ?>]"
						class="ywarc_rule_type_radio_button"
						name="ywarc_typo_rule_radio[<?php echo esc_attr( $rule_id ); ?>]" type="radio"
						value="replace"
						<?php
						if ( ! $new_rule ) {
							echo checked( $rule_type, esc_attr( 'replace' ), false );
						}
						?>
					><?php echo esc_html_x( 'switch', 'Complete sentence: The user will switch from this role to this one', 'yith-automatic-role-changer-for-woocommerce' ); ?>
				</label>
			</p>
		</div>
		<div class="role_selector_block block">
			<select multiple class="ywarc_role_selector" name="ywarc_role_selected[<?php echo esc_attr( $rule_id ); ?>]" >
				<?php
					wp_dropdown_roles( ! empty( $rule['role_selected'][0] ) ? $rule['role_selected'][0] : '' );
				?>
			</select>
		</div>
		<div class="replace_role_block block">
			<p><?php echo esc_html_x( 'from this role:', 'Complete sentence: The user will switch from this role to this one', 'yith-automatic-role-changer-for-woocommerce' ); ?></p>
			<select multiple class="ywarc_replace_role_before">
			<?php
				wp_dropdown_roles( ! empty( $rule['replace_roles'][0] ) ? $rule['replace_roles'][0] : '' );
			?>
				</select>
			<p><?php echo esc_html_x( 'to this one:', 'Complete sentence: The user will switch from this role to this one', 'yith-automatic-role-changer-for-woocommerce' ); ?></p>
			<select multiple class="ywarc_replace_role_after">
			<?php
				wp_dropdown_roles( ! empty( $rule['replace_roles'][1] ) ? $rule['replace_roles'][1] : '' );
			?>
				</select>
		</div>


		<?php do_action( 'ywarc_before_specific_product_block', $new_rule, $rule, $rule_id ); ?>


		<div class="specific_product_block block">
			<p>
				<b><?php esc_html_e( 'Choose a product: ', 'yith-automatic-role-changer-for-woocommerce' ); ?></b>
			</p>
			<?php
			$data_selected = '';
			$product_id    = '';
			if ( ! $new_rule ) {
				$product_id = $rule['product_selected'];
				if ( $product_id ) {
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) {
						$product_name  = wp_kses_post(
							html_entity_decode(
								$product->get_formatted_name(),
								ENT_QUOTES,
								get_bloginfo( 'charset' )
							)
						);
						$data_selected = version_compare( WC()->version, '2.7', '<' )
							? $product_name
							: array( $product_id => esc_attr( $product_name ) );
					}
				}
			}

			$search_product_array = array(
				'type'             => 'hidden',
				'class'            => 'wc-product-search',
				'id'               => 'ywarc_product_selector[' . $rule_id . ']',
				'name'             => '',
				'data-placeholder' => __( 'Search for a product&hellip;', 'woocommerce' ),
				'data-allow_clear' => false,
				'data-selected'    => $data_selected,
				'data-multiple'    => false,
				'data-action'      => 'woocommerce_json_search_products_and_variations',
				'value'            => $product_id,
				'style'            => '',
			);
			yit_add_select2_fields( $search_product_array );
			?>
			</div>

		<?php do_action( 'ywarc_after_specific_product_block', $new_rule, $rule, $rule_id ); ?>


		<div class="submit_block block">
			<input class="button button-primary button-large" type="submit"
					value="<?php esc_html_e( 'Save rule', 'yith-automatic-role-changer-for-woocommerce' ); ?>"/>
			<span class="test">
				<a class="delete_rule" href="#">
				<?php
					esc_html_e( 'Delete', 'yith-automatic-role-changer-for-woocommerce' );
				?>
				</a>
			</span>
		</div>
		<?php wp_nonce_field( 'yith-ywarc-save_rule', 'yith-ywarc-save_rule-nonce' ); ?>
	</div>
</div>
