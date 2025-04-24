<?php
/**
 * Controls: Chart Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Dashboard
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Interfaces\Prepared_REST_Data;
use AffiliateWP_Affiliate_Portal\Core\Schemas\Chart_Schema;
use AffiliateWP_Affiliate_Portal\Core\Schemas\Data_Schema;
use function AffiliateWP_Affiliate_Portal\html;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

#[\AllowDynamicProperties]

/**
 * Implements a chart.
 *
 * @since 1.0.0
 *
 * @see   Base_Control
 */
class Chart_Control extends Base_Control implements Prepared_REST_Data {

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
	 *         @type array|string $schema {
	 *             Table schema to use. Can be a reference to a Table class, or an array of schema args.
	 *
	 *             @type callable $data_callback Callback to retrieve the data for this table.
	 *             @type array    $schema        {
	 *                 Array of schema data controls keyed by the data identifier.
	 *
	 *                 @type string   $title           The data title to display.
	 *                 @type callable $render_callback The callback to render an individual piece of data. Callback is expected
	 *                                                 to return a control object. Callback signature is as follows:
	 *                                                 `( $row, $table_control_id ) : Base_Control`.
	 *                 @type callable $color           CSS-compatible color value for the current schema data.
	 *                 @type int      $priority        Priority for ordering the data within the chart.
	 *             }
	 *         }
	 *         @type string|array $header {
	 *             Optional. Header text or array of header attributes.
	 *
	 *             @type string $text  Optional. The table header text. Leave blank to exclude.
	 *             @type int    $level Optional. Header level to use if `$text` is defined. Default 2 (h2).
	 *         }
	 *         @type string|array $desc  {
	 *             Description for the text input or an array containing directives and text key value pairs.
	 *             Default empty string.
	 *
	 *             @type string $text       Text to display inside the description element.
	 *             @type array  $directives Directives to pass along to the description element.
	 *             @type string $position   Position of the description. Accepts 'before' or 'after'. Default 'after'.
	 *         }
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * }
	 *
	 * @param bool  $validate  Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'schema', 'header', 'desc' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_rest_object( $args = array() ) {
		$defaults = array(
			'rows'  => false,
			'query' => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$schema = $this->get_argument( 'schema', false );

		if ( false === $schema ) {
			return;
		}

		if ( false !== $args['rows'] ) {
			$this->rows        = array();
			$this->pages       = 1;
			$this->x_label_key = $schema->x_label_key;

			if ( $schema instanceof Data_Schema ) {
				$this->rows = $schema->build_sets( $args['query'] );
			}
		}
	}

	/**
	 * Retrieves a given argument if set, otherwise a default value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $argument Argument key.
	 * @param mixed  $default  Default argument value.
	 *
	 * @return mixed Argument value.
	 */
	public function get_argument( $argument, $default = '' ) {
		$result = parent::get_argument( $argument, $default );

		// If the argument is the schema, maybe make an instance.
		if ( 'schema' === $argument ) {
			// If this is not currently an instance of Table_Schema, create an instance.
			if ( ! is_subclass_of( $result, Data_Schema::class ) && ! $result instanceof Data_Schema ) {
				$result = new Chart_Schema( $this->get_id(), $result );

				$this->set_argument( 'schema', $result );
			}
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$header      = $this->get_argument( 'header', array() );
		$description = $this->get_argument( 'desc', array() );

		$id_base = $this->get_id_base();
		$result  = "";

		if ( ! empty( $header )
		     && ( is_string( $header ) || ( is_array( $header ) && ! empty( $header['text'] ) ) )
		) {
			if ( is_string( $header ) ) {
				$text = $header;
			} else {
				$text = $header['text'];
			}

			$level = empty( $header['level'] ) ? 2 : (int) $header['level'];

			$heading = new Heading_Control( array(
				'id'   => "{$id_base}-head",
				'args' => array(
					'text'  => sanitize_text_field( $text ),
					'level' => $level,
				),
			) );

			if ( ! $heading->has_errors() ) {
				$result .= $heading->render( false );
			} else {
				$heading->log_errors( $this->get_view_id() );
			}

		}

		$desc_text       = is_string( $description ) ? $description : '';
		$desc_position   = 'after';
		$desc_directives = array();
		$desc_classes    = array();

		if ( ! empty( $description ) ) {
			$desc_classes = array( 'mb-2', 'text-sm', 'leading-5', 'text-gray-500' );

			if ( is_array( $description ) ) {
				$desc_text       = ! empty( $description['text'] )       ? $description['text']       : $desc_text;
				$desc_directives = ! empty( $description['directives'] ) ? $description['directives'] : $desc_directives;
				$desc_position   = ! empty( $description['position'] )   ? $description['position']   : $desc_position;
			}
		}

		$desc_control = new Paragraph_Control( array(
			'id'     => "{$id_base}-desc",
			'atts'   => array(
				'class'  => $desc_classes,
			),
			'args'   => array(
				'text'   => esc_html( $desc_text ),
			),
			'alpine' => $desc_directives,
		) );

		if ( ! empty( $description ) && 'before' === $desc_position ) {
			if ( ! $desc_control->has_errors() ) {
				$result .= $desc_control->render( false );
			} else {
				$desc_control->log_errors();
			}
		}

		if ( ! empty( $desc ) ) {
			$description = new Paragraph_Control( array(
				'id' => "{$id_base}_description",
				'atts' => array(
					'class' => array( 'text-sm', 'leading-5', 'mt-2', 'text-gray-500' ),
				),
				'args' => array(
					'text' => $desc,
				),
			) );

			if ( ! $description->has_errors() ) {
				$result .= $description->render( false );
			} else {
				$description->log_errors( $this->get_view_id() );
			}
		}

		// Chart Wrapper Start
		$result .= html()->div_start( array(
			'directives' => array(
				'x-data' => "AFFWP.portal.chart.default({ type: '$id_base' })",
				'x-init' => 'init()',
				'x-show' => '!isLoading',
			),
			'class'      => array(
				'w-full',
			),
		), false );

		$result .= html()->form_start( array(
			'directives' => array(
				'@submit' => 'handleSubmit($event)',
			),
		), false );

		$result .= html()->div_start( array( 'class' => 'sm:flex' ), false );

		$result .= html()->div_start( array(
			'class' => array(
				'max-w-xs',
				'rounded-md',
				'shadow-sm',
				'w-64',
				'sm:mr-2',
			),
		), false );

		$result .= html()->element_start( 'select', array(
			'class'      => array(
				'affwp-graphs-date-options',
				'block',
				'form-select',
				'w-full',
				'transition2',
				'duration-1502',
				'ease-in-out2',
				'sm:text-sm',
				'sm:leading-5',
			),
			'directives' => array(
				'@change' => 'handleSelectChange($event)',
			),
		), false );

		$result .= html()->element_start( 'template', array(
			'directives' => array(
				'x-for' => '( dateQuery, index ) in dateQueries',
				':key'  => 'index',
			),
		), false );

		$result .= html()->element_start( 'option', array(
			'x-text'       => 'dateQuery.label',
			'x-bind:value' => 'dateQuery.key',
		), false );

		$result .= html()->element_end( 'template', false );

		$result .= html()->element_end( 'select', false );
		$result .= html()->div_end( false );

		$result .= html()->element_start( 'span', array(
			'class' => array( 'inline-flex', 'rounded-md', 'shadow-sm' ),
		), false );

		$filter_button = new Submit_Button_Control( array(
			'id'   => 'filter',
			'atts' => array(
				'value' => __( 'Filter', 'affiliatewp-affiliate-portal' ),
				'class' => array(
					'inline-flex',
					'items-center',
					'px-4',
					'py-2',
					'border',
					'border-transparent',
					'text-sm',
					'leading-5',
					'font-medium',
					'rounded-md',
					'text-white',
					'bg-indigo-600',
					'hover:bg-indigo-500',
					'focus:outline-none',
					'focus:border-indigo-700',
					'focus:shadow-outline-indigo',
					'active:bg-indigo-700',
					'transition',
					'ease-in-out',
					'duration-150',
				),
			),
		) );

		if ( ! $filter_button->has_errors() ) {
			$result .= $filter_button->render( false );
		}

		$result .= html()->element_end( 'span', false );
		$result .= html()->div_end( false );
		$result .= html()->form_end( false );

		$result .= html()->element( 'canvas', array(
			'directives' => array(
				'x-ref' => 'canvas',
				'id'    => 'chart-canvas',
			),
		), false );

		// Chart Wrapper End
		$result .= html()->div_end( false );

		if ( ! empty( $description ) && 'after' === $desc_position ) {
			if ( ! $desc_control->has_errors() ) {
				$result .= $desc_control->render( false );
			} else {
				$desc_control->log_errors();
			}
		}


		if ( true === $echo ) {
			echo $result;
		} else {
			return $result;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'chart_control';
	}

}
