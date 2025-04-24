<?php
/**
 * Core: Data Schema
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Core\Schemas;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use AffiliateWP_Affiliate_Portal\Core\Traits\Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the structure, and other relevant information to render structured data.
 *
 * @since 1.0.0
 */
abstract class Data_Schema {

	use Error_Handler;

	/**
	 * Schema identifier.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $name;

	/**
	 * Associated control ID.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $control_id;

	/**
	 * Schema data.
	 *
	 * @since 1.0.0
	 * @var   Controls\Schema_Data_Control[]
	 */
	private $schema = array();

	/**
	 * Data callback.
	 *
	 * @since 1.0.0
	 * @var   callable
	 */
	private $data_callback;

	/**
	 * Parsed arguments.
	 *
	 * @var array
	 */
	protected $parsed_args = array();

	/**
	 * Data control to use with this schema.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $data_control = '';

	/**
	 * Data schema constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_id Control ID.
	 * @param array  $args       {
	 *     Data schema to use. Can be a reference to a Data_Schema class, or an
	 *     array of schema args.
	 *
	 *     @type string   $name                Schema name.
	 *     @type callable $page_count_callback Callback to retrieve the total number of pages.
	 *     @type callable $data_callback       Callback to retrieve the data for this schema.
	 *     @type array    $schema              {
	 *         Array of schema columns keyed by the data set identifier.
	 *
	 *         @type string   $title           The dataset title to display.
	 *         @type callable $render_callback The callback to render an individual piece of data. Callback is expected
	 *                                         to return a control object. Callback signature is as follows:
	 *                                         `( $row, $table_control_id ) : Base_Control`.
	 *         @type int      $priority        Priority for ordering the dataset within the control output.
	 *     }
	 * }
	 */
	public function __construct( $control_id, $args = array() ) {
		$this->set_up_errors();

		$this->control_id = $control_id;

		if ( ! is_array( $args ) ) {
			$args = array( $args );
		}

		$defaults = array_merge( $this->get_global_defaults( $control_id ), $this->get_defaults() );

		$this->parsed_args = wp_parse_args( $args, $defaults );

		$this->name          = $this->parsed_args['name'];
		$this->data_callback = $this->parsed_args['data_callback'];

		$this->process_schema( $this->parsed_args['schema'] );
	}

	/**
	 * Retrieves the schema-specific default arguments.
	 *
	 * If a schema is defining custom arguments, they should be supplied as key/value pairs to the array
	 * returned from this method.
	 *
	 * @since 1.0.0
	 *
	 * @return array Key/value pairs for default arguments.
	 */
	public function get_defaults() {
		return array();
	}

	/**
	 * Retrieves the control ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string Control ID.
	 */
	public function get_control_id() {
		return $this->control_id;
	}

	/**
	 * Retrieves the default arguments global to all schemas.
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_id Control ID.
	 * @return array Key/value pairs for global default arguments.
	 */
	private function get_global_defaults( $control_id  ) {
		return array(
			'name'          => $control_id,
			'data_callback' => false,
			'schema'        => array(),
		);
	}

	/**
	 * Retrieves the data using the data callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments to pass to the data callback.
	 *
	 * @return array|\WP_Error List of data objects or WP_Error object if there was a problem.
	 */
	public function get_data( $args ) {
		if ( ! is_callable( $this->data_callback ) ) {
			return new \WP_Error( 'invalid_callback',
				sprintf( 'The %s schema data_callback is invalid.', $this->name ),
				$args
			);
		}

		return call_user_func( $this->data_callback, $args );
	}

	/**
	 * Retrieves the array of keyed schema data controls.
	 *
	 * @since 1.0.0
	 *
	 * @return Controls\Schema_Data_Control[] Schema data control information.
	 */
	public function get_schema() {
		return $this->schema;
	}

	/**
	 * Adds data control to the schema.
	 *
	 * @since 1.0.0
	 * @since 1.1.1 The `$parent_control` parameter was added.
	 *
	 * @param string                       $key            Schema data key.
	 * @param Controls\Schema_Data_Control $control        Schema data control.
	 * @param Controls\Table_Control       $parent_control Optional. Parent Table_Control object. Default null (unused).
	 */
	public function add_data_control( $key, $control, $parent_control = null ) {
		$column_to_replace = $control->get_argument( 'replaces_column' );

		if ( ! empty( $column_to_replace ) && ( $parent_control instanceof Controls\Table_Control ) ) {
			if ( true === $parent_control->get_argument( 'allow_column_replace', false ) ) {
				$key = $column_to_replace;
			}
		}

		$this->schema[ $key ] = $control;
	}

	/**
	 * Retrieves the rendered data output for the specified data set.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $key  The dataset key from which the callback should be used.
	 * @param object $data The data to pass to the callback.
	 * @return string Rendered data markup if valid, otherwise an empty string.
	 */
	public function render_data( $key, $data ) {
		$result = '';
		$schema = $this->get_schema();

		// Bail early if the schema key does not exist.
		if ( ! isset( $schema[ $key ] ) ) {
			return '';
		}

		$control_object = $schema[ $key ];

		$callback = $control_object->get_argument( 'render_callback' );

		// Render the data, if it is a valid control.
		if ( is_callable( $callback ) ) {
			/** @var Controls\Base_Control $data_output */
			$data_output = call_user_func_array( $callback, array( $data, $this->get_control_id() ) );

			// Only set the result if the called data function returns a Base_Control instance.
			if ( $data_output instanceof Controls\Base_Control && ! $data_output->has_errors() ) {
				$result = $data_output->render( false );
			} else {
				$data_output->log_errors( "{$this->name}_schema" );
			}

		} else {
			$errors = $this->get_errors();

			$error_code = "schema_{$key}_data_error";

			// Prevent duplication for every data in every row.
			if ( ! $errors->get_error_code( "schema_{$key}_data_error" ) ) {
				$this->add_error( $error_code,
					sprintf( 'There was a problem rendering the contents of the data in the \'%1$s\' dataset in the \'%2$s\' schema control.',
						$key,
						$this->control_id
					),
					$data
				);
			}

		}

		return $result;
	}

	/**
	 * Prepares the schema by including any extra data controls.
	 *
	 * @since 1.0.0
	 */
	public function prepare() {
		$controls_registry = Controls_Registry::instance();

		$control = $controls_registry->get( $this->get_control_id() );

		$external_controls = array();

		if ( false !== $control ) {
			$external_controls = $controls_registry->query( array(
				'view_id' => $control->get_view_id(),
				'type'    => 'schema_data_column',
				'parent'  => $this->get_control_id(),
			) );

			foreach ( $external_controls as $key => $control ) {
				$this->prepare_data_control( $key, $control );
			}
		}
	}

	/**
	 * Processes the schema data to import the controls.
	 *
	 * @since 1.0.0
	 *
	 * @param array $schema Data schema.
	 */
	public function process_schema( $schema ) {
		foreach ( $schema as $key => $control ) {
			$this->prepare_data_control( $key, $control );
		}
	}

	/**
	 * Retrieves the controls as structured data from the schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of columns and their names.
	 */
	public function get_data_controls() {
		$controls = array();
		$schema   = $this->get_schema();

		// If the schema is empty, bail.
		if ( empty( $schema ) ) {
			return $controls;
		}

		foreach ( $schema as $key => $control ) {
			$args = $control->get_arguments();

			if ( ! $control->can_render() ) {
				continue;
			}

			$controls[] = array(
				'id'       => $key,
				'title'    => isset( $args['title'] ) ? esc_html( $args['title'] ) : '',
				'priority' => isset( $args['priority'] ) ? intval( $args['priority'] ) : 10,
			);
		}

		return $controls;
	}

	/**
	 * Prepares and adds single data control to the schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string                             $key        Column ID.
	 * @param array|Controls\Schema_Data_Control $attributes Column attributes or table column control object.
	 */
	public function prepare_data_control( $key, $attributes ) {

		$controls_registry = Controls_Registry::instance();
		$parent_control    = $controls_registry->get( $this->get_control_id() );

		$schema = $this->get_schema();

		if ( isset( $schema[ $key ] ) ) {

			$this->add_error( 'duplicate_schema_data_column',
				sprintf( 'The \'%1$s\' column already exists in the \'%2$s\' table control schema.',
					$key,
					$this->name
				),
				$schema

			);

		} elseif ( ! isset( $schema[ $key ] )
			&& ! ( $attributes instanceof Controls\Schema_Data_Control )
			&& ! is_array( $attributes )
		) {

			$this->add_error( 'invalid_column_data',
				sprintf( 'The \'%1$s\' column for the \'%2$s\' table control must either be an array of attributes or a fully qualified Schema_Data_Control object.',
					$key,
					$this->name
				),
				$attributes
			);


		} elseif ( $attributes instanceof Controls\Schema_Data_Control ) {

			$attributes->set_prop( 'parent', $this->get_control_id() );

			$this->add_data_control( $key, $attributes, $parent_control );

		} else {

			$defaults = array(
				'id'              => '',
				'render_callback' => '',
				'priority'        => 10,
			);

			$attributes = wp_parse_args( $attributes, $defaults );

			$column = new $this->data_control( array(
				'id'     => "{$this->name}_{$key}_column",
				'parent' => $this->get_control_id(),
				'args'   => $attributes,
			) );

			$this->add_data_control( $key, $column, $parent_control );
		}

		$schema = $this->get_schema();

		// Sort schema columns by priority.
		uasort( $schema, function ( $a, $b ) {
			if ( $a->get_argument( 'priority' ) == $b->get_argument( 'priority' ) ) {
				return 0;
			} else if ( $a->get_argument( 'priority' ) < $b->get_argument( 'priority' ) ) {
				return -1;
			} else {
				return 1;
			}
		} );


	}

	/**
	 * Builds datasets.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The arguments to pass to the control's callback.
	 * @return array List of data sets structured and rendered by the control's schema.
	 */
	abstract public function build_sets( $args );

}
