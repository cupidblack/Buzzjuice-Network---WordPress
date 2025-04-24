<?php
class AffiliateWP_Recurring_Admin {

	/**
	 * Field names used for the section.
	 *
	 * @since 1.9.2
	 *
	 * @var array|string[]
	 */
	private array $section_fields = [
		'recurring_referral_limit_enabled',
		'recurring_rate',
		'recurring_rate_type',
		'recurring_referral_limit',
	];

	/**
	 * Get things started
	 *
	 * @access public
	 * @since  1.0
	 * @since 1.9.2 Added support for sections.
	 * @return void
	 */
	public function __construct() {

		// Keeps compatibility with older versions of AffiliateWP.
		if ( version_compare( get_option( 'affwp_version' ), '2.18.0', '<' ) ) {
			add_filter( 'affwp_settings_tabs', [ $this, 'setting_tab' ] );
			add_filter( 'affwp_settings', [ $this, 'register_settings_legacy' ] );
		} else {
			add_filter( 'affwp_settings_commissions', [ $this, 'register_settings' ] );
			add_filter( 'affiliatewp_register_section_addon_recurring_referrals', [ $this, 'update_section_fields' ] );
		}

		add_filter( 'affwp_settings_sanitize', [ $this, 'sanitize_recurring_rate' ], 10, 2 );
	}
	/**
	 * Register the new settings tab
	 *
	 * @access public
	 * @since  1.5
	 * @return array
	 */
	public function setting_tab( $tabs ) {
		$tabs['recurring'] = __( 'Recurring Referrals', 'affiliate-wp-recurring-referrals' );
		return $tabs;
	}

	/**
	 * Retrieve the array of settings.
	 *
	 * @since 1.9.2
	 *
	 * @param array $filter_by If supplied, will return only the specified settings.
	 *
	 * @return array The array of settings.
	 */
	private function get_settings( array $filter_by = array() ) : array {

		$settings = array(
			'recurring' => array(
				'name' => __( 'Enable Recurring Referrals', 'affiliate-wp-recurring-referrals' ),
				'desc' => __( 'Check this box to enable referral tracking on all subscription payments', 'affiliate-wp-recurring-referrals' ),
				'type' => 'checkbox'
			),
			'recurring_referral_limit_enabled' => array(
				'name'    => __( 'Enable Recurring Referral Limits', 'affiliate-wp-recurring-referrals' ),
				'desc'    => __( 'Check this box to enable recurring referral limits on all subscription payments', 'affiliate-wp-recurring-referrals' ),
				'type' => 'checkbox',
			),
			'recurring_rate' => array(
				'name' => __( 'Recurring Rate', 'affiliate-wp-recurring-referrals' ),
				'desc' => __( 'Enter the commission rate for recurring payments. If no rate is entered, the affiliate\'s standard rate will be used.', 'affiliate-wp-recurring-referrals' ),
				'type' => 'number',
				'min'  => 0,
				'step' => '0.01',
				'size' => 'small',
			),
			'recurring_rate_type' => array(
				'name'    => __( 'Recurring Rate Type', 'affiliate-wp-recurring-referral' ),
				'desc'    => __( 'Select the commission rate type for recurring payments. If no rate type is entered, the affiliate\'s standard rate type will be used.', 'affiliate-wp-recurring-referrals' ),
				'type'    => 'select',
				'options' => affwp_get_affiliate_rate_types(),
			),
			'recurring_referral_limit' => array(
				'name'    => __( 'Recurring Referral Limit', 'affiliate-wp-recurring-referral' ),
				'desc'    => __( 'Set the recurring referral limit for recurring payments. Set to 0 for unlimited recurring referrals. This global recurring referral limit will be used, unless a recurring referral limit is entered for an affiliate.', 'affiliate-wp-recurring-referrals' ),
				'type'    => 'number',
				'step'    => '1',
				'size'    => 'small',
				'default' => 0,
			),
		);

		if ( class_exists( 'AffiliateWP_Tiered_Rates' ) ) {

			$settings['recurring_rate_tiered_rates_enabled'] = array(
				'name' => __( 'Enable Tiered Rates', 'affiliate-wp-recurring-referrals' ),
				'type' => 'checkbox',
				'desc' => sprintf(
					__( 'Check this box to allow <a href="%1$s">tiered rates</a> to be used for recurring payments.', 'affiliate-wp-recurring-referrals' ),
					admin_url( 'admin.php?page=affiliate-wp-settings&tab=rates' )
				),
			);
		}

		if ( empty( $filter_by ) ) {
			return $settings; // No filters, return all settings.
		}

		// Return only specific settings.
		return array_intersect_key( $settings, array_flip( $filter_by ) );
	}

	/**
	 * Register our settings.
	 *
	 * @since 1.9.2
	 *
	 * @param array $settings The array of settings.
	 *
	 * @return array
	 */
	public function register_settings_legacy( array $settings = [] ) : array {

		return array_merge_recursive(
			$settings,
			[
				'recurring' => $this->get_settings(),
			]
		);
	}

	/**
	 * Register settings to use with sections.
	 *
	 * @since 1.0.0
	 * @since 1.9.2 Register settings for sections instead of tabs.
	 *
	 * @return array The array of settings.
	 */
	public function register_settings( $settings = [] ) : array {

		return array_merge_recursive(
			$settings,
			array_map(
				function ( $setting ) {
					return array_merge_recursive(
						$setting,
						[
							'class' => affiliate_wp()->settings->get( 'recurring' ) ? '' : 'affwp-hidden',
							'visibility' => [
								'required_field' => 'recurring',
								'value' => true,
							]
						]
					);
				},
				$this->get_settings( $this->section_fields )
			)
		);
	}

	/**
	 * Whitelist the fields that should be used in the section.
	 *
	 * @since 1.9.2
	 *
	 * @param array $settings The section settings.
	 *
	 * @return array The updated array of settings.
	 */
	public function update_section_fields( array $settings ) : array {
		return array_merge_recursive( $settings, $this->section_fields );
	}

	/**
	 * Sanitize the recurring rate on save.
	 *
	 * @since 1.7.0
	 * @return string
	 */
	public function sanitize_recurring_rate( $value = '', $key = '' ) {

		if ( 'recurring_rate' === $key ) {

			if ( empty( $value ) ) {

				$value = '';

			}

		}

		return $value;
	}

}
new AffiliateWP_Recurring_Admin();
