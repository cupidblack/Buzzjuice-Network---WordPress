<?php
/**
 * Tab for adding new rules
 *
 * @author  YITH <plugins@yithemes.com>
 * @package yith-woocommerce-automatic-role-changer.premium\templates\admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<table class="form-table">
	<tbody>
	<tr>
		<td>
			<p>
				<i>
				<?php
				esc_html_e(
					'Here you can create and configure your own rules for automatic role switch.',
					'yith-automatic-role-changer-for-woocommerce'
				);
				?>
				</i>
			</p>
			<p>
				<i>
				<?php
				esc_html_e(
					"To start, click on 'Add new rule', name your rule and set it up as you wish. Don't forget to save the settings when you're finished.",
					'yith-automatic-role-changer-for-woocommerce'
				);
				?>
				</i>
			</p>
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" class="ywarc_new_rule_title" value=""
				placeholder="<?php esc_html_e( 'Name the rule...', 'yith-automatic-role-changer-for-woocommerce' ); ?>">
			<a href="" id="yith_ywarc_add_rule_button" class="button-secondary">
			<?php
				esc_html_e( 'Add new rule', 'yith-automatic-role-changer-for-woocommerce' );
			?>
				</a>
			<span class="ywarc_creating_rule">&nbsp;</span>
			<span class="ywarc_force_apply_rules_row">
				<?php $tip = __( 'This will check all your current orders and search for those matching your current rules. This will set as many user roles as many that it will find. This procedure cannot be undone. Use with caution.', 'yith-automatic-role-changer-for-woocommerce' ); ?>
				<?php echo wc_help_tip( $tip ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?>
				<a href="" id="ywarc_force_apply_rules" class="button" title="<?php echo esc_attr( $tip ); ?>">
					<span><?php esc_html_e( 'Force apply rules', 'yith-automatic-role-changer-for-woocommerce' ); ?></span>
				</a>
				<a href="" id="ywarc_force_apply_rules_set_dates_button"
					title="<?php esc_html_e( 'Set a date range to filter the search', 'yith-automatic-role-changer-for-woocommerce' ); ?>">
					<span><?php esc_html_e( 'Set dates', 'yith-automatic-role-changer-for-woocommerce' ); ?></span>
				</a>
			</span>
			<span class="ywarc_creating_rule ywarc_force_apply_rules_row">&nbsp;</span>
		</td>
	</tr>
	<tr class="ywarc_force_apply_rules_dates">
		<td>
			<div style="margin-bottom: 25px;">
				<span><?php esc_html_e( 'Search for orders by:', 'yith-automatic-role-changer-for-woocommerce' ); ?></span>
				<span class="ywarc_force_apply_rules_set_date_type">
					<input type="radio" name="ywarc_force_apply_rules_set_date_type" id="ywarc_force_apply_rules_date_created" value="created" checked>
					<label for="ywarc_force_apply_rules_date_created"><?php esc_html_e( 'Created date', 'yith-automatic-role-changer-for-woocommerce' ); ?></label>
				</span>
				<span class="ywarc_force_apply_rules_set_date_type">
					<input type="radio" name="ywarc_force_apply_rules_set_date_type" id="ywarc_force_apply_rules_date_completed" value="completed">
					<label for="ywarc_force_apply_rules_date_completed"><?php esc_html_e( 'Completed date', 'yith-automatic-role-changer-for-woocommerce' ); ?></label>
				</span>
				<span class="ywarc_force_apply_rules_set_date_type">
					<input type="radio" name="ywarc_force_apply_rules_set_date_type" id="ywarc_force_apply_rules_date_paid" value="paid">
					<label for="ywarc_force_apply_rules_date_paid"><?php esc_html_e( 'Paid date', 'yith-automatic-role-changer-for-woocommerce' ); ?></label>
				</span>
			</div>
			<div>
				<?php
				$date_format = esc_html_x(
					'YYYY-MM-DD',
					'Date format. (Do not change the format to local format, the format must be YYYY-MM-DD always)',
					'yith-automatic-role-changer-for-woocommerce'
				);
				?>
				<span>
					<span><?php esc_html_e( 'From:', 'yith-automatic-role-changer-for-woocommerce' ); ?></span>
					<input id="ywarc_force_apply_rules_from_date" class="ywarc_force_apply_rules_date" size="15" type="text"
						placeholder="<?php echo esc_html( $date_format ); ?>" maxlength="10" title="<?php echo esc_html( $date_format ); ?>">
				</span>
				<span>
					<span><?php esc_html_e( 'To:', 'yith-automatic-role-changer-for-woocommerce' ); ?></span>
					<input id="ywarc_force_apply_rules_to_date" class="ywarc_force_apply_rules_date" size="15" type="text"
						placeholder="<?php echo esc_html( $date_format ); ?>" maxlength="10" title="<?php echo esc_html( $date_format ); ?>">
				</span>
			</div>
		</td>
	</tr>
	<?php do_action( 'ywarc_print_rules' ); ?>
	</tbody>
</table>
