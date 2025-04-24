<?php
/**
 * Views: Settings View
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Views;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use AffiliateWP_Affiliate_Portal\Core\Interfaces\View;

#[\AllowDynamicProperties]

/**
 * Sets up the Settings view.
 *
 * @since 1.0.0
 */
class Settings_View implements View {

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {
		$sections = array(
			'user-settings' => array(
				'label'        => __( 'User settings', 'affiliatewp-affiliate-portal' ),
				'priority'     => 5,
				'wrapper'      => true,
				'submit_label' => __( 'Save user settings', 'affiliatewp-affiliate-portal' ),
			),
		);

		return $sections;
	}

	/**
	 * Retrieves the view controls.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_controls() {

		$controls = array(
			new Controls\Wrapper_Control( array(
				'view_id' => 'settings',
				'section' => 'wrapper',
				'atts'    => array(
					'id' => 'affwp-affiliate-portal-urls',
				),
			) ),
			new Controls\Heading_Control( array(
				'id'       => 'payments-settings',
				'view_id'  => 'settings',
				'section'  => 'user-settings',
				'priority' => 1,
				'args'     => array(
					'text'  => __( 'Payments', 'affiliatewp-affiliate-portal' ),
					'level' => 3,
				),
			) ),
			new Controls\Email_Control( array(
				'id'       => 'payment-email',
				'view_id'  => 'settings',
				'section'  => 'user-settings',
				'priority' => 5,
				'args'     => array(
					'label' => __( 'Payment email', 'affiliatewp-affiliate-portal' ),
					'desc'  => __( 'This is your payment email. Used by PayPal and the likes', 'affiliatewp-affiliate-portal' ),
					'get_callback' => function( $affiliate_id ) {
						return affwp_get_affiliate_payment_email( $affiliate_id );
					},
					'save_callback' => function( $value, $affiliate_id ) {
						$value = sanitize_text_field( $value );

						$updated = affwp_update_affiliate( array(
							'affiliate_id'  => $affiliate_id,
							'payment_email' => $value
						) );

						return $updated;
					},
					'validations' => array(
						new Controls\Validation_Control( array(
							'id'   => 'invalid-email',
							'args' => array(
								'message'           => __( 'Invalid email address.', 'affiliatewp-affiliate-portal' ),
								'validate_callback' => function ( $data, $affiliate_id ) {
									return false !== is_email( $data['payment-email'] );
								},
							),
						) ),
					),
				),
			) ),
		);

		$notifications_controls = array(
			new Controls\Heading_Control( array(
				'id'                  => 'notifications-settings',
				'view_id'             => 'settings',
				'section'             => 'user-settings',
				'priority'            => 10,
				'permission_callback' => function( $control, $affiliate_id ) {
					return affwp_email_referral_notifications( $affiliate_id );
				},
				'args'                => array(
					'text'  => __( 'Notifications', 'affiliatewp-affiliate-portal' ),
					'level' => 3,
				),
			) ),
			new Controls\Checkbox_Control( array(
				'id'                  => 'referral-notifications',
				'view_id'             => 'settings',
				'section'             => 'user-settings',
				'priority'            => 11,
				'permission_callback' => function( $control, $affiliate_id ) {
					return affwp_email_referral_notifications( $affiliate_id );
				},
				'atts'                => array(
					'class'   => array(
						'form-checkbox',
						'h-4',
						'w-4',
						'text-indigo-600',
						'transition',
						'duration-150',
						'ease-in-out',
					),
				),
				'args'                => array(
					'label'       => __( 'Enable referral notifications', 'affiliatewp-affiliate-portal' ),
					'desc'        => __( 'Receive a notification when a referral is generated.', 'affiliatewp-affiliate-portal' ),
					'label_class' => array(
						'font-medium',
						'text-gray-700',
					),
					'get_callback'    => function( $affiliate_id ) {
						$user_id = affwp_get_affiliate_user_id( $affiliate_id );

						$enabled = 0;

						if ( $user_id ) {
							$enabled = get_user_meta( $user_id, 'affwp_referral_notifications', true );
						}

						return 1 === (int) $enabled;
					},
					'save_callback'   => function( $data, $affiliate_id ) {
						$user_id = affwp_get_affiliate_user_id( $affiliate_id );

						$enabled = 0;

						if ( $user_id && true === $data ) {
							$enabled = 1;
						}

						return update_user_meta( $user_id, 'affwp_referral_notifications', $enabled );
					},
				),
			) ),
		);

		$controls = array_merge( $controls, $notifications_controls );

		return $controls;
	}

}
