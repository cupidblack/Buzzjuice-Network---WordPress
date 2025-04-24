<?php
/**
 * Controls: Base Form Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use AffiliateWP_Affiliate_Portal\Core\Interfaces\Prepared_REST_Data;
use AffiliateWP_Affiliate_Portal\Core\Traits;

/**
 * Base control middleware for activating form-specific features within the context of a view.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
abstract class Form_Control extends Base_Control implements Prepared_REST_Data {

	use Traits\Data_Getter, Traits\Data_Setter {
		Traits\Data_Getter::get_control insteadof Traits\Data_Setter;
	}

	/**
	 * Whether the current control posts data (via form submission).
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	private $posts_data = true;

	/**
	 * Sets up the control.
	 *
	 * @param array $metadata  {
	 *     Metadata for setting up the current control. Arguments are optional unless otherwise stated.
	 *
	 *     @type string $id       Required. Globally-unique ID for the current control.
	 *     @type string $view_id  Required unless `$section` is also omitted. View ID to associate a registered
	 *                            control with.
	 *     @type string $section  Required unless `$view_id` is also omitted. Section to associate a registered
	 *                            control with.
	 *     @type int    $priority Priority within the section to display the control. Default 25.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $args     {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type string               $label         Label text for the control.
	 *         @type string|array         $label_class   Class attribute for the element's label. Default empty.
	 *         @type bool                 $posts_data    Signifies whether the control posts any data (via form submission).
	 *                                                   Default true.
	 *         @type callable             $get_callback  Custom callback for retrieving the control data. Must be defined
	 *                                                   if `$save_callback` is defined. Signature: `( $affiliate_id ) : mixed`.
	 *                                                   Default callback is `Form_Control::get_data()`.
	 *         @type callable             $save_callback Custom callback for saving the control data. Must be defined
	 *                                                   if `$get_callback` is defined. Signature: `( $data, $affiliate_id )`.
	 *                                                   Default callback is `Form_Control::save_data()`.
	 *         @type Validation_Control[] $validations   Array of Validation_Control objects to use when validating this control.
	 *     }
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type string $name     Hard-coded to the control ID for everything but Hidden_Control.
	 *         @type array  $aria     Array of aria attributes.
	 *         @type bool   $readonly Whether the control should be read only. Default false.
	 *         @type bool   $disabled Whether the control should be disabled. Default false.
	 *     }
	 * }
	 * @param bool   $validate Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		$this->set_up_errors();

		$control_id      = isset( $metadata['id'] ) ? $metadata['id'] : '';
		$input_type      = isset( $metadata['atts']['type'] ) ? $metadata['atts']['type'] : '';
		$radio_with_name = ( 'radio' === $input_type && ! empty( $metadata['atts']['name'] ) );

		// TODO maybe add a key map for payouts service names to skip this special case.
		$ps_control = 0 === strpos( $control_id, 'payouts-service' ) && ! empty( $metadata['atts']['name'] );

		if ( 'hidden' !== $input_type && ! $radio_with_name && ! $ps_control && ! empty( $control_id ) ) {
			$metadata['atts']['name'] = $control_id;
		}

		if ( isset( $metadata['args']['posts_data'] ) ) {
			$this->posts_data = (bool) $metadata['args']['posts_data'];
		}

		parent::__construct( $metadata, $validate );

		$this->validate_callbacks();
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_rest_object( $args = array() ) {
		$defaults = array(
			'validate' => false,
			'data'     => '',
		);

		$query = wp_parse_args( $args['query'], $defaults );

		if ( false !== $query['validate'] ) {
			$this->validations = $this->validate_data( $query['data'] );
		}
	}

	/**
	 * Loops through validations, and registers each.
	 *
	 * @since 1.0.0
	 */
	public function register_validations() {
		$validations = $this->get_argument( 'validations', array() );
		$registry    = Controls_Registry::instance();
		foreach ( $validations as $validation ) {

			// If this is not a Validation Control, log an error and bail.
			if ( ! $validation instanceof Validation_Control ) {
				$this->add_error(
					'invalid_validation_control',
					sprintf( 'A validation for the \'%1$s\' control is not of a valid type.', $this->get_id() ),
					array( 'control_type' => gettype( $validation ) ) );
				continue;
			}

			$registry->add_control( $validation );
		}
	}

	/**
	 * Loops through validations and determines if there are any errors.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data Field data to validate.
	 * @return array Array containing passed and failed validations.
	 */
	public function validate_data( $data ) {
		$validations = $this->get_argument( 'validations', array() );
		$result      = array(
			'passed' => array(),
			'failed' => array(),
		);
		foreach ( $validations as $validation ) {

			// Bail early if this validation is not a control.
			if ( ! $validation instanceof Validation_Control ) {
				$this->add_error(
					'invalid_validation',
					sprintf( 'A validation for the \'%1$s\' control is not of a valid type.', $this->get_id() ),
					array( 'received' => gettype( $validation ),
				) );
				continue;
			}
			$id      = $validation->get_id();
			$message = $validation->get_argument( 'message', '' );
			$valid   = $validation->validate_control_data( $data );

			if ( true === $valid ) {
				$id                             = $validation->get_id();
				$result['passed'][] = array(
					'id'      => $id,
					'message' => $validation->get_argument( 'message', '' ),
				);
			} else {
				$result['failed'][] = array(
					'id'      => $id,
					'message' => $validation->get_argument( 'message', '' ),
				);
			}
		}

		return $result;
	}

	/**
	 * Retrieves the saved data for the current control.
	 *
	 * Controls using standard get/save callbacks can expect to interact with affiliate meta keys using
	 * the following naming scheme: {type}_{control_id}.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id Current affiliate ID.
	 * @return mixed Data for the current control.
	 */
	public function get_data( $affiliate_id ) {
		$meta_key = $this->get_meta_key();

		return affwp_get_affiliate_meta( $affiliate_id, $meta_key, true );
	}

	/**
	 * Saves the data for the current control.
	 *
	 * Controls using standard get/set callbacks can expect to interact with affiliate meta keys using
	 * the following naming scheme: {type}_{control_id}.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data         Data to save against the control.
	 * @param int   $affiliate_id Current affiliate ID.
	 * @return bool True if the data was successfully saved, otherwise false.
	 */
	public function save_data( $data, $affiliate_id ) {
		$data = sanitize_text_field( $data );

		$meta_key = $this->get_meta_key();

		return affwp_update_affiliate_meta( $affiliate_id, $meta_key, $data );
	}

	/**
	 * Retrieves the meta get used for standard set/get operations.
	 *
	 * @since 1.0.0
	 *
	 * @return string Meta key.
	 */
	public function get_meta_key() {
		$type       = $this->get_type();
		$control_id = $this->get_id();

		return "{$type}_{$control_id}";
	}

	/**
	 * Signifies whether this is a form control.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public function form_control() {
		return true;
	}

	/**
	 * Signifies whether this control posts data (via form submission).
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public function posts_data() {
		return $this->posts_data;
	}

	/**
	 * @inheritDoc
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'name', 'aria', 'readonly', 'disabled', );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'label', 'label_class', 'posts_data', 'validations' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * Validates custom get/save callbacks for the form control.
	 *
	 * @since 1.0.0
	 */
	private function validate_callbacks() {
		$control_id = $this->get_id();

		if ( 'invalid' === $control_id ) {
			return;
		}

		$get_callback  = $this->get_argument( 'get_callback' );
		$save_callback = $this->get_argument( 'save_callback' );

		if ( empty( $get_callback ) && ! empty( $save_callback )
			|| ( ! empty( $get_callback ) && empty( $save_callback ) )
		) {
			$this->add_error( 'missing_value_callback',
				sprintf( 'Both get_callback and save_callback arguments must be defined to customize value storage for the \'%1$s\' control.',
					$control_id
				),
				$this->to_array( array( 'args' => $this->get_arguments() ) )
			);
		}

		if ( ! empty( $get_callback ) && ! is_callable( $get_callback ) ) {
			$this->add_error( 'invalid_get_callback',
				sprintf( 'The get_callback \'%1$s\' for the \'%2$s\' control is invalid.',
					$get_callback,
					$control_id
				),
				$this->to_array( array( 'args' => $this->get_arguments() ) )
			);
		}

		if ( ! empty( $save_callback ) && ! is_callable( $save_callback ) ) {
			$this->add_error( 'invalid_save_callback',
				sprintf( 'The save_callback \'%1$s\' for the \'%2$s\' control is invalid.',
					$save_callback,
					$control_id
				),
				$this->to_array( array( 'args' => $this->get_arguments() ) )
			);
		}

	}
}
