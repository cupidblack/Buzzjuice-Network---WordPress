<?php
/**
 * Core: Requests Controller
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

#[\AllowDynamicProperties]

/**
 * Handler for a variety of core requests including form submissions.
 *
 * @since 1.0.0
 */
class Requests {

	/**
	 * Sets up callbacks used to capture and handle a variety of requests.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'capture_submissions' ) );
	}

	/**
	 * Captures view section form submissions.
	 *
	 * @since 1.0.0
	 */
	public function capture_submissions() {

		$data = $_REQUEST;

		// If there's no section ID, bail early.
		if ( ! isset( $data['section_id'] ) ) {
			return;
		} else {
			$section_id = sanitize_text_field( $data['section_id'] );
		}

		$nonce_action = "{$section_id}-save-nonce";

		$nonce = isset( $data[ $nonce_action ] ) ? $data[ $nonce_action ] : false;

		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			return;
		} else {
			if ( isset( $data['affiliate_id'] ) ) {
				$affiliate_id = intval( $data['affiliate_id'] );
			} else {
				$affiliate_id = affwp_get_affiliate_id();
			}

			$this->save_controls( $data, $section_id, $affiliate_id );
		}
	}

	/**
	 * Handles saving control values for a given view ID.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $data         Form data.
	 * @param string $section_id   Section ID.
	 * @param int    $affiliate_id Affiliate ID.
	 */
	public function save_controls( $data, $section_id, $affiliate_id ) {

		if ( ! empty( $data ) ) {
			$controls_registry = Controls_Registry::instance();

			$radio_controls = array();

			$section_controls = $controls_registry->query( array(
				'section' => $section_id,
			) );

			if ( ! empty( $section_controls ) ) {
				foreach ( $section_controls as $control_id => $control ) {
					if ( ! $control->form_control() || ! $control->posts_data() ) {
						continue;
					}

					if ( ! $control instanceof Controls\Input_Control ) {
						continue;
					}

					$type = $control->get_input_type();

					if ( ! in_array( $type, array( 'checkbox', 'radio' ) ) ) {
						continue;
					}

					if ( 'checkbox' === $type ) {
						$control_id = $control->get_id();

						if ( ! isset( $data[ $control_id ] ) ) {
							$data[ $control_id ] = 'off';
						}
					}

					if ( 'radio' === $type ) {
						$name = $control->get_attribute( 'name' );

						$radio_controls[ $name ][] = $control;
					}
				}
			}

			// Handle radio button groups.
			if ( ! empty( $radio_controls ) ) {
				foreach ( $radio_controls as $name => $controls ) {
					if ( is_array( $controls ) ) {
						$checked_control = $data[ $name ];

						// Unset the name to prevent collisions.
						unset( $data[ $name ] );

						foreach ( $controls as $control ) {
							$control_id = $control->get_id();

							if ( $control_id === $checked_control ) {
								$data[ $control_id ] = 'on';
							} else {
								$data[ $control_id ] = 'off';
							}
						}
					}
				}
			}

			foreach ( $data as $key => $data_value ) {
				$control = $controls_registry->get( $key );

				if ( false !== $control && ( $control->form_control() ) ) {
					$control->save_control_data( $data_value, $affiliate_id );
				}
			}
		}

	}

}
