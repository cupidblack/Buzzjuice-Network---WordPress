<?php
/**
 * Email for user notification of role granted
 *
 * @author  YITH <plugins@yithemes.com>
 * @package yith-woocommerce-automatic-role-changer.premium\templates\emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$rules    = $email->object;
$user_id  = $email->user_id;
$order_id = $email->order_id;

?>

<?php

do_action( 'woocommerce_email_header', $email_heading, $email );

$user          = new WP_User( $user_id );
$current_order = wc_get_order( $order_id );

if ( $rules ) {
	// Count the total number of roles granted.
	$roles_count = 0;
	foreach ( $rules as $rule_id => $rule ) {
		if ( 'add' === $rule['rule_type'] && ! empty( $rule['role_selected'] && is_countable( $rule['role_selected'] ) ) ) {
			$roles_count = $roles_count + count( $rule['role_selected'] );
		} elseif ( 'replace' === $rule['rule_type'] && ! empty( $rule['replace_roles'] ) ) {
			++$roles_count;
		}
	}

	$order_url  = $current_order->get_view_order_url();
	$order_link = '<a href="' . $order_url . '">#' . $order_id . '</a>';

	echo '<p>';
	esc_html_e( 'Hi, thanks for your purchase.', 'yith-automatic-role-changer-for-woocommerce' );
	echo '</p>';
	echo '<p>';
	/* translators: %1$s is replaced with curent order */
	$msg = esc_html( _n( "You've earned the following role with your order %s: ", "You've earned the following roles with your order %s: ", $roles_count, 'yith-automatic-role-changer-for-woocommerce' ) );
	printf( esc_html( $msg ), esc_url( $order_link ) );
	echo '</p>';

	foreach ( $rules as $rule_id => $rule ) {
		if ( 'add' === $rule['rule_type'] && ! empty( $rule['role_selected'] ) ) {
			foreach ( $rule['role_selected'] as $single_role ) {
				$role_name = wp_roles()->roles[ $single_role ]['name'];
				echo '<div class="ywarc_metabox_gained_role"><span class="ywarc_metabox_role_name">' .
					esc_html( $role_name ) . '</span>';
				do_action( 'ywarc_after_metabox_content', $rule );
				echo '</div>';
			}
		} elseif ( 'replace' === $rule['rule_type'] && ! empty( $rule['replace_roles'] ) ) {
			$role_name = wp_roles()->roles[ $rule['replace_roles'][1] ]['name'];
			echo '<div class="ywarc_metabox_gained_role"><span class="ywarc_metabox_role_name">' .
				esc_html( $role_name ) . '</span>';
			do_action( 'ywarc_after_metabox_content', $rule );
			echo '</div>';
		}
	}
}

do_action( 'woocommerce_email_footer' );
