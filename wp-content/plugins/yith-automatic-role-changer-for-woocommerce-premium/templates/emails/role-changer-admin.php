<?php
/**
 * Email for admin notification of role granted to user
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

$user = new WP_User( $user_id );

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

	// Get user name and link to order admin page.
	$username   = $user->display_name ? $user->display_name : $user->nickname;
	$order_url  = get_edit_post_link( $order_id );
	$order_link = '<a href="' . esc_attr( $order_url ) . '">#' . $order_id . '</a>';

	echo '<p>';
	/* translators: %1$s is replaced with user,  %2$s is replaced with Roles_count */
	$msg = esc_html( _n( 'The user %1$s gained the following role with the order %2$s: ', 'The user %1$s gained the following roles with the order %2$s: ', $roles_count, 'yith-automatic-role-changer-for-woocommerce' ) );
	printf( esc_html( $msg ), esc_html( $username ), esc_url( $order_link ) );
	echo '</p>';

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
}

do_action( 'woocommerce_email_footer' );
