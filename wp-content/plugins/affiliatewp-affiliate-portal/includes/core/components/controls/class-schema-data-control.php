<?php
/**
 * Controls: Schema Data Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Dashboard
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

/**
 * Implements a data control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
abstract class Schema_Data_Control extends Base_Control {

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
	 *     @type string $parent   Required. Parent control ID. Unused if not set.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $args     {
	 *         Arguments for setting up the data.
	 *
	 *         @type string   $id              ID for the data element (must be able to pass sanitize_key()).
	 *         @type string   $title           Data element title. Generally gets displayed as a column header for
	 *                                         a table or a data header for a chart.
	 *         @type callable $render_callback The callback to render an individual piece of data.
	 *         @type callable $data_callback   Data callback to retrieve data to supply to the schema.
	 *         @type int      $priority        Priority order to display a data element. Default 10.
	 *     }
	 *     @type array  $atts Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                        the control-specific attributes whitelist during validation.
	 * }
	 * @param bool  $validate  Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true.
	 */
	public function __construct( $metadata, $validate = true ) {

		if ( ! isset( $metadata['args']['priority'] ) ) {
			$metadata['args']['priority'] = 10;
		}

		parent::__construct( $metadata, $validate );

		$parent = $this->get_prop( 'parent' );
		$args   = $this->get_arguments();

		if ( empty( $parent ) ) {
			$this->add_error( 'missing_parent_id',
				sprintf( 'The parent metadata is missing for the \'%1$s\' control. It must be set for the data to be eligible for display.',
					$this->get_id()
				),
				$metadata
			);
		}

		if ( empty( $args['data_callback'] ) || ! empty( $args['data_callback'] && ! is_callable( $args['data_callback'] ) && ! is_string( $args['data_callback'] ) ) ) {
			$this->add_error( 'invalid_data_callback',
				sprintf( 'The data callback for the \'%1$s\' control in the \'%2$s\' view must be a valid callback.',
					$this->get_id(),
					$this->get_view_id()
				),
				$args
			);
		}

	}

	/**
	 * Fetches the dataset from this schema.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments to pass to the data callback.
	 * @return array|\WP_Error Array of dataset data, or WP_Error.
	 */
	public function get_dataset_data( $args ) {
		if ( ! is_array( $args ) ) {
			$args = array( $args );
		}

		$data_callback   = $this->get_argument( 'data_callback' );
		$render_callback = $this->get_argument( 'render_callback' );

		if ( ! is_callable( $data_callback ) ) {
			$this->add_error(
				'invalid_dataset_callback',
				'The provided dataset callback is invalid.'
			);
		}

		if ( ! is_callable( $render_callback ) ) {
			$this->add_error(
				'invalid_dataset_render_callback',
				'The provided dataset render callback is invalid.'
			);
		}

		// Bail early if we have errors.
		if ( $this->has_errors() ) {
			return $this->get_errors();
		}

		$data = call_user_func( $data_callback, $args );

		// Render data.
		foreach ( $data as $key => $datum ) {
			$control = $render_callback( $datum );

			if ( ! $control instanceof Base_Control ) {
				$control = Text_Control::create( "{$key}_datum", $control );
			}

			$data[ $key ]->data = $control->render( false );
		}

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'schema_data_column';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'id', 'title', 'priority', 'render_callback', 'data_callback', 'color' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {}
}