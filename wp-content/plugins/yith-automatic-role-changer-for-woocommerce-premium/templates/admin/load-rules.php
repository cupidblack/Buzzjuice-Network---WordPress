<?php
/**
 * List of all rules.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package yith-woocommerce-automatic-role-changer.premium\templates\admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<tr id="ywarc_new_rules_row" class="no_rules">
	<td>
		<div class="ywarc_new_rules">

		</div>
	</td>
</tr>
<tr>
	<td>
		<div class="ywarc_rules_group">
			<?php
			$title_class = '';
			$rules       = get_option( 'ywarc_rules' );
			?>
			<div id="my_rules_header" class="<?php echo $rules ? '' : 'no_rules'; ?>">
				<span class="my_rules_text"><?php esc_html_e( 'MY RULES', 'yith-automatic-role-changer-for-woocommerce' ); ?></span>
				<?php
				$a = '<a href="#" id="show_all_rules"><i>' .
					_x( 'Show', 'toggle button label.', 'yith-automatic-role-changer-for-woocommerce' ) . '</i></a>';
				$b = '<a href="#" id="hide_all_rules"><i>' .
					_x( 'Hide', 'toggle button label.', 'yith-automatic-role-changer-for-woocommerce' ) . '</i></a>';
				$c = '<a href="#" id="delete_all_rules"><i>' .
					_x( 'Delete', 'toggle button label.', 'yith-automatic-role-changer-for-woocommerce' ) . '</i></a>';
				?>

				<span class="my_rules_show_hide_rules">
				<?php
					printf(
						/* translators: %1$s,  %2$s, %3$s is replaced with show/hide/delete */
						esc_html_x(
							'( %1$s / %2$s / %3$s all rules )',
							'"Show" / "Hide" / "Delete" all rules',
							'yith-automatic-role-changer-for-woocommerce'
						),
						// Those messages have HTML Code, that's why we put ignore here.
						$a, // phpcs:ignore WordPress.Security.EscapeOutput
						$b, // phpcs:ignore WordPress.Security.EscapeOutput
						$c  // phpcs:ignore WordPress.Security.EscapeOutput
					);
					?>
						</span>
			</div>
			<?php
			if ( $rules ) {
				foreach ( $rules as $rule_id => $rule ) {
					$new_rule = false;
					include YITH_WCARC_TEMPLATE_PATH . 'admin/add-rule.php';
				}
			}
			?>
		</div>
	</td>
</tr>
