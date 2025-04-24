<?php
/**
 * Integrations: Custom Affiliate Slugs add-on
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Integrations;

use AffiliateWP_Affiliate_Portal\Core;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces;
use AffiliateWP_Affiliate_Portal\Core\Sections_Registry;

/**
 * Class for integrating the Custom Affiliate Slugs add-on.
 *
 * @since 1.0.0
 */
class Custom_Affiliate_Slugs implements Interfaces\Integration {

	/**
	 * @inheritDoc
	 */
	public function init() {
		add_action( 'plugins_loaded', array( $this, 'register_ui' ), 105 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	/**
	 * Register Custom Affiliate Slug Affiliate Area Scripts
	 *
	 * @since 1.0.0
	 */
	public function register_scripts() {
		if ( affwp_is_affiliate_portal( 'settings' ) ) {
			affwp_enqueue_script( 'affwp-portal-cas-settings' );
		}
	}

	/**
	 * Registers AffiliateWP Custom Affiliate Slugs Controls.
	 *
	 * @since 1.0.0
	 */
	public function register_ui() {
		$affiliate_id = affwp_get_affiliate_id();
		// Bail early if slugs aren't allowed for this affiliate.

		$allow_affiliate_slugs = affiliatewp_custom_affiliate_slugs()->base->allow_affiliate_slugs( $affiliate_id );
		$custom_slug           = affiliatewp_custom_affiliate_slugs()->base->get_slug( $affiliate_id );
		$show_slug_enabled     = false !== $custom_slug && false !== affiliate_wp()->settings->get( 'custom_affiliate_slugs_affiliate_show_slug' );
		$slug_exists           = 'slug' === affwp_get_referral_format() && false !== $custom_slug;

		// Register sections
		$registry = Sections_Registry::instance();

		if ( true === $allow_affiliate_slugs ) {
			$registry->register_section( 'custom-affiliate-slugs-settings', array(
				'label'        => __( 'Custom affiliate slug', 'affiliatewp-affiliate-portal' ),
				'desc'         => __( 'A custom affiliate slug allows you to have a unique referral URL using your chosen slug.', 'affiliatewp-affiliate-portal' ),
				'priority'     => 10,
				'view_id'      => 'settings',
				'wrapper'      => true,
				'form_alpine'  => array(
					'x-data' => 'AFFWP.portal.casSettings.default()',
				),
				'submit_label' => __( 'Save custom slug', 'affiliatewp-affiliate-portal' ),
			) );
		}
		if ( true === $show_slug_enabled || true === $slug_exists ) {
			$registry->register_section( 'custom-affiliate-slugs', array(
				'label'    => __( 'Your custom slug', 'affiliatewp-affiliate-portal' ),
				'desc'     => __( 'A custom affiliate slug allows you to have a unique referral URL using your chosen slug.', 'affiliatewp-affiliate-portal' ),
				'priority' => 3,
				'wrapper'  => true,
				'view_id'  => 'urls',
			) );
		}

		// Register controls.
		$registry = Core\Controls_Registry::instance();

		if ( true === $allow_affiliate_slugs ) {

			$validation_class   = array( 'text-red-600' );
			$status_label_class = array( 'setting', 'text-control', 'mt-1', 'text-sm' );

			$affwp_cas_controls = array(
				new Controls\Text_Input_Control( array(
					'id'       => 'custom-affiliate-slug-setting',
					'view_id'  => 'settings',
					'section'  => 'custom-affiliate-slugs-settings',
					'priority' => 5,
					'args'     => array(
						'label'         => __( 'Custom slug', 'affiliatewp-affiliate-portal' ),
						'get_callback'  => function ( $affiliate_id ) {
							$slug = affiliatewp_custom_affiliate_slugs()->base->get_slug( $affiliate_id );

							return empty( $slug ) ? '' : $slug;
						},
						'save_callback' => function ( $value, $affiliate_id ) {
							$updated = false;

							// Maybe delete the custom slug.
							if ( empty( $value ) ) {
								$updated = affwp_delete_affiliate_meta( $affiliate_id, 'custom_slug' );
							}

							// If we have a value, try to update the slug.
							if ( ! empty( $value ) ) {
								$updated = affwp_update_affiliate_meta( $affiliate_id, 'custom_slug', $value );
							}

							return $updated;
						},
						'validations'   => array(
							new Controls\Validation_Control( array(
								'id'   => 'custom-affiliate-slug-validation-characters',
								'args' => array(
									'message'           => __( 'Slugs can only contain lowercase letters and numbers.', 'affiliatewp-affiliate-portal' ),
									'validate_callback' => function ( $data ) {
										$value = isset( $data['custom-affiliate-slug-setting'] ) ? $data['custom-affiliate-slug-setting'] : '';
										return ! preg_match( "/[^a-z0-9]/", $value );
									},
								),
							) ),
							new Controls\Validation_Control( array(
								'id'   => 'custom-affiliate-slug-validation-numbers',
								'args' => array(
									'message'           => __( 'Slugs cannot only contain numbers.', 'affiliatewp-affiliate-portal' ),
									'validate_callback' => function ( $data, $affiliate_id ) {
										$value = isset( $data['custom-affiliate-slug-setting'] ) ? $data['custom-affiliate-slug-setting'] : '';
										return ! is_numeric( $value );
									},
								),
							) ),
							new Controls\Validation_Control( array(
								'id'   => 'custom-affiliate-slug-validation-length',
								'args' => array(
									'message'           => __( 'Slugs cannot be longer than 60 characters.', 'affiliatewp-affiliate-portal' ),
									'validate_callback' => function ( $data, $affiliate_id ) {
										$value = isset( $data['custom-affiliate-slug-setting'] ) ? $data['custom-affiliate-slug-setting'] : '';
										return strlen( $value ) <= 60;
									},
								),
							) ),
							new Controls\Validation_Control( array(
								'id'   => 'custom-affiliate-slug-validation-generic',
								'args' => array(
									'message'           => __( 'That slug cannot be used.', 'affiliatewp-affiliate-portal' ),
									'validate_callback' => function ( $data, $affiliate_id ) {
										$value = isset( $data['custom-affiliate-slug-setting'] ) ? $data['custom-affiliate-slug-setting'] : '';

										// Slug is a username.
										if ( false !== get_user_by( 'login', $value ) ) {
											return false;
										}

										// This validation is checked elsewhere, but causes false negatives on affiliatewp_custom_affiliate_slugs()->base->check
										// So to get around this we bail early.
										if ( is_numeric( $value ) ) {
											return true;
										}

										// Slug already exists
										if ( true === affiliatewp_custom_affiliate_slugs()->base->check( $value ) ) {
											if ( affiliatewp_custom_affiliate_slugs()->base->get_affiliate_id_from_slug( $value ) !== $affiliate_id ) {
												return false;
											}
										}

										return true;
									},
								),
							) ),
						),
					),
				) ),
				new Controls\Text_Input_Control( array(
					'id'       => 'custom-affiliate-slug-confirm',
					'view_id'  => 'settings',
					'section'  => 'custom-affiliate-slugs-settings',
					'priority' => 15,
					'args'     => array(
						'posts_data'  => false,
						'wrapper'     => array(
							'directives' => array(
								'x-show' => 'showConfirmField()',
							),
						),
						'validations' => array(
							new Controls\Validation_Control( array(
								'id'   => 'custom-affiliate-slug-validation-slugs-match',
								'args' => array(
									'message'           => __( 'Slugs do not match.', 'affiliatewp-affiliate-portal' ),
									'validate_callback' => function ( $data, $affiliate_id ) {
										return $data['custom-affiliate-slug-setting'] === $data['custom-affiliate-slug-confirm'];
									},
									'wrapper'           => array(
										'directives' => array(
											'x-show' => 'showConfirmField()',
										),
									),
								),
							) ),
						),
					),
					'label'    => __( 'Confirm affiliate slug', 'affiliatewp-affiliate-portal' ),
				) ),
				new Controls\Text_Control( array(
					'id'       => 'custom-affilaite-slug-confirm-message',
					'view_id'  => 'settings',
					'section'  => 'custom-affiliate-slugs-settings',
					'priority' => 30,
					'atts'     => array(
						'class' => array( 'text-gray-500' ),
					),
					'args'     => array(
						'text'    => __( 'By changing your affiliate slug you acknowledge that any existing links using an older affiliate slug may no longer work. Type your new custom slug one more time to confirm.', 'affiliatewp-affiliate-portal' ),
						'wrapper' => array(
							'class'      => array( 'text-gray-600', 'mt-1', 'text-sm' ),
							'directives' => array(
								'x-show' => 'showConfirmField()',
							),
						),
					),
				) ),
				new Controls\Checkbox_Control( array(
					'id'       => 'custom-affiliate-slug-confirm-delete',
					'view_id'  => 'settings',
					'section'  => 'custom-affiliate-slugs-settings',
					'priority' => 35,
					'args'     => array(
						'validations'   => array(
							new Controls\Validation_Control( array(
								'id'   => 'custom-affiliate-slug-confirm-removal-checked',
								'args' => array(
									'message'           => __( 'You must confirm removal.', 'affiliatewp-affiliate-portal' ),
									'validate_callback' => function ( $data, $affiliate_id ) {

										$slug_setting = $data['custom-affiliate-slug-setting'];

										// Only test this if the slug setting is empty.
										if ( ! empty( $slug_setting ) ) {
											return true;
										}
										$checkbox = isset( $data['custom-affiliate-slug-confirm-delete'] ) ? $data['custom-affiliate-slug-confirm-delete'] : false;

										$confirmed = '1' === $checkbox || 'true' === $checkbox || true === $checkbox;
										return $confirmed;
									},
									'wrapper'           => array(
										'directives' => array(
											'x-show' => 'showConfirmDeleteField()',
										),
									),
								),
							) ),
						),
						'wrapper'       => array(
							'directives' => array(
								'x-show' => 'showConfirmDeleteField()',
							),
						),
						'label'         => __( 'Confirm Removal', 'affiliatewp-affiliate-portal' ),
						'desc'          => __( 'By removing your affiliate slug you acknowledge that any existing links using an older affiliate slug may no longer work.', 'affiliatewp-affiliate-portal' ),
						'get_callback'  => '__return_false',
						'save_callback' => '__return_false',
					),
				) ),
			);

			foreach ( $affwp_cas_controls as $control ) {
				$registry->add_control( $control );
			}
		}

		if ( true === $show_slug_enabled || true === $slug_exists ) {
			$registry->add_control( new Controls\Text_Control( array(
				'id'       => 'custom-affiliate-slug',
				'view_id'  => 'urls',
				'section'  => 'custom-affiliate-slugs',
				'priority' => 10,
				'args'     => array(
					'text' => $custom_slug,
				),
				'atts'     => array(
					'class' => array( 'md:h-full', 'block' ),
				),
			) ) );
		}
	}

	/**
	 * Retrieves parameters for the given collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $collection Collection to retrieve parameters for.
	 *
	 * @return array Collection parameters (if any), otherwise an empty array.
	 */
	public function get_rest_collection_params( $collection ) {
		return array();
	}

	/**
	 * Retrieves the schema for a single dataset, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Dataset schema data.
	 */
	public function get_dataset_schema() {
		return array();
	}

}
