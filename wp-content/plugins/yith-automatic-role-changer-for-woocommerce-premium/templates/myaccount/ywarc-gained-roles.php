<?php
/**
 * Notification when added or replaced a role for user.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package yith-woocommerce-automatic-role-changer.premium\templates\myaccount
 */

$rules = get_post_meta( $order_id, '_ywarc_rules_granted', true );
if ( ! $rules ) {
	return;
}
?>

<section class="ywarc_roles_gained">
	<style>
		.ywarc_metabox_gained_role {
			border: #dcdada solid 1px;
			padding: 15px;
			text-align: center;
			width: 270px;
		}
		.ywarc_metabox_role_name {
			font-size: 24px;
			color: grey;
		}
		.ywarc_metabox_dates {
			font-size: 12px;
			margin-top: 10px;
		}
	</style>
	<?php
	echo '<h2 class="ywarc_roles_gained__title">' . esc_html__( 'Roles gained', 'yith-automatic-role-changer-for-woocommerce' ) . '</h2>';
	foreach ( $rules as $rule_id => $rule ) {
		if ( 'add' === $rule['rule_type'] && ! empty( $rule['role_selected'] ) ) {
			foreach ( $rule['role_selected'] as $single_role ) {
				$role_name = wp_roles()->roles[ $single_role ]['name'];
				echo '<div class="ywarc_metabox_gained_role"><span class="ywarc_metabox_role_name">' . esc_html( $role_name ) . '</span>';
				do_action( 'ywarc_after_metabox_content', $rule );
				echo '</div>';
			}
		} elseif ( 'replace' === $rule['rule_type'] && ! empty( $rule['replace_roles'] ) ) {
			$role_name = wp_roles()->roles[ $rule['replace_roles'][1] ]['name'];
			echo '<div class="ywarc_metabox_gained_role"><span class="ywarc_metabox_role_name">' . esc_html( $role_name ) . '</span>';
			do_action( 'ywarc_after_metabox_content', $rule );
			echo '</div>';
		}
	}
	?>
</section>
