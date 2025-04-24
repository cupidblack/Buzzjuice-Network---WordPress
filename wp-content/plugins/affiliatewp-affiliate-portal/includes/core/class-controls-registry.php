<?php
/**
 * Core: Controls Registry
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 *
 * phpcs:disable PEAR.Functions.FunctionCallSignature.ContentAfterOpenBracket -- Opening parenthesis of a multi-line function call must be the last content on the line.
 * phpcs:disable PEAR.Functions.FunctionCallSignature.MultipleArguments -- Only one argument is allowed per line in a multi-line function call.
 * phpcs:disable PEAR.Functions.FunctionCallSignature.CloseBracketLine -- Closing parenthesis of a multi-line function call must be on a line by itself.
 */

namespace AffiliateWP_Affiliate_Portal\Core;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

#[\AllowDynamicProperties]

/**
 * Implements a controls registry class.
 *
 * @since 1.0.0
 *
 * @see Registry
 *
 * @method \AffiliateWP_Affiliate_Portal\Core\Controls_Registry instance()
 * @method Controls\Base_Control|false get()
 * @method Controls\Base_Control[] query()
 */
class Controls_Registry extends Registry {

	use Traits\Static_Registry, Traits\Registry_Filter;

	/**
	 * Initializes the controls registry.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		/**
		 * Fires during instantiation of the controls registry.
		 *
		 * @since 1.0.0
		 *
		 * @param Controls_Registry $this Registry instance.
		 */
		do_action( 'affwp_portal_controls_registry_init', self::instance() );
	}

	/**
	 * Retrieves the list of control types supported by the controls registry.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of supported control types.
	 */
	private function control_types_whitelist() {

		/**
		 * Add controls to whitelist.
		 *
		 * Now you no longer have to add them manually below, you can
		 * add a filter in your control class that will add it.
		 *
		 * This also reduces having to release a new version of the portal
		 * addon just to whitelist a control possibly added by another addon,
		 * core or a third party plugin.
		 *
		 * @since 1.2.2
		 *
		 * @param $whitelist array Whitelist of control classes.
		 */
		return apply_filters( 'affwp_portal_control_types_whitelist', array(
			Controls\Button_Control::class,
			Controls\Card_Control::class,
			Controls\Card_Group_Control::class,
			Controls\Chart_Control::class,
			Controls\Checkbox_Control::class,
			Controls\Code_Block_Control::class,
			Controls\Copy_Button_Control::class,
			Controls\Creative_Card_Control::class,
			Controls\Date_Control::class,
			Controls\Div_With_Copy_Control::class,
			Controls\Email_Control::class,
			Controls\Heading_Control::class,
			Controls\Hidden_Control::class,
			Controls\Icon_Control::class,
			Controls\Image_Control::class,
			Controls\Label_Control::class,
			Controls\Link_Control::class,
			Controls\List_Control::class,
			Controls\Modal_Control::class,
			Controls\Number_Control::class,
			Controls\Paragraph_Control::class,
			Controls\Password_Control::class,
			Controls\Radio_Control::class,
			Controls\Select_Control::class,
			Controls\Status_Control::class,
			Controls\Submit_Button_Control::class,
			Controls\Table_Column_Control::class,
			Controls\Table_Control::class,
			Controls\Template_Control::class,
			Controls\Text_Control::class,
			Controls\Text_Input_Control::class,
			Controls\Textarea_Control::class,
			Controls\Validation_Control::class,
			Controls\Vanity_Coupon_Control::class,
			Controls\Network_Tree_Control::class,
			Controls\Wrapper_Control::class,
		) );
	}

	/**
	 * Registers a new control.
	 *
	 * @since 1.0.0
	 *
	 * @param Controls\Base_Control $control Control object.
	 * @return true|\WP_Error True on successful registration, otherwise \WP_Error object.
	 */
	public function add_control( $control ) {

		if ( $this->offsetExists( $control->get_id() ) ) {
			$this->add_error( 'duplicate_control_id',
				sprintf( 'The \'%s\' control id already exists.', $control->get_id() ),
				$control
			);
		}

		// Validate control type.
		if ( ! $this->valid_control_type( $control ) ) {
			/* translators: 1: control type being registered */
			$message = sprintf( 'The control type \'%s\' was not registered. Invalid control type passed.',
				$control->get_type()
			);

			$this->add_error(
				'affwp_ad_invalid_control_type',
				$message,
				array( 'invalid_control_type' => $control->get_type() )
			);
		}

		// Every control must be associated with a view.
		if ( '' === $control->get_view_id() ) {
			$this->add_error(
				'affwp_ad_missing_control_view_id',
				sprintf( 'The \'%s\' control must be associated with a view_id', $control->get_id() ),
				$control
			);
		}

		// Every control must define a section.
		if ( '' === $control->get_prop( 'section' ) && 'wrapper' !== $control->get_type() ) {
			$this->add_error(
				'affwp_ad_missing_control_section',
				sprintf( 'The \'%s\' control must define a section.', $control->get_id() ),
				$control
			);
		}

		if ( $this->has_errors() ) {
			return $this->get_errors();
		}

		// Expose some identifiers for use during render.
		$attributes = array(
			'viewId'      => $control->get_view_id(),
			'type'        => $control->get_type(),
			'section'     => $control->get_prop( 'section' ),
			'priority'    => $control->get_prop( 'priority' ),
			'parent'      => $control->get_prop( 'parent' ),
			'formControl' => $control->form_control() && $control->posts_data() ? 1 : 0,
		);

		foreach ( $attributes as $key => $value ) {
			$control->$key = $value;
		}

		$success = parent::add_item( $control->get_id(), $control );

		// Register validations (if exist).
		$validations = $control->get_argument( 'validations', array() );
		foreach ( $validations as $validation ) {
			$validation->set_prop( 'view_id', $control->get_view_id() );
			$validation->set_prop( 'section', $control->get_prop( 'section' ) );
			$validation->set_prop( 'priority', $control->get_prop( 'priority' ) );
			$this->add_control( $validation );
		}

		return $success;
	}

	/**
	 * Retrieves controls from the registry using optional filters.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filter Optional. Filters to use when returning controls. Default empty.
	 * @return array|\WP_REST_Response|\WP_Error (Maybe filtered) controls.
	 */
	public function get_controls( $filter = '' ) {
		$controls = $this->get_items();

		if ( 'rest' === $filter ) {
			$controls = $this->get_rest_items( 'control' );
		}

		return $controls;
	}

	/**
	 * Retrieves a given control from the registry using an optional filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_id Control ID.
	 * @return Controls\Base_Control|\WP_Error (Maybe filtered) control.
	 */
	public function get_control( $control_id ) {
		if ( ! $this->offsetExists( $control_id ) ) {
			$this->add_error( 'invalid_control', sprintf( 'The \'%s\' control does not exist.', $control_id ) );
		} else {
			$control = $this->get( $control_id );
		}

		if ( $this->has_errors() ) {
			return $this->get_errors();
		} else {
			return $control;
		}
	}

	/**
	 * Checks to see if the specified type is an allowed type.
	 *
	 * @since 1.0.0
	 *
	 * @param Controls\Base_Control $control Control.
	 * @return bool True if valid control type, false otherwise.
	 */
	private function valid_control_type( $control ) {
		$types = $this->control_types_whitelist();

		$validated = false;

		foreach ( $types as $class ) {
			if ( $control instanceof $class ) {
				$validated = true;

				break;
			}
		}

		return $validated;
	}
}
