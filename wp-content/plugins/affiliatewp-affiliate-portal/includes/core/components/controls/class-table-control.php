<?php
/**
 * Controls: Table Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use AffiliateWP_Affiliate_Portal\Core\Schemas\Table_Schema;
use AffiliateWP_Affiliate_Portal\Core\Interfaces\Prepared_REST_Data;
use function AffiliateWP_Affiliate_Portal\html;

#[\AllowDynamicProperties]

/**
 * Implements a table control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Table_Control extends Base_Control implements Prepared_REST_Data {

	/**
	 * If true, this control's REST data will be preloaded.
	 *
	 * @since 1.0.4
	 *
	 * @var bool True if this should be preloaded. Otherwise false.
	 */
	protected $preload = true;

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

	 *         @type array|string $schema {
	 *             Table schema to use. Can be a reference to a Table class, or an array of schema args.

	 *             @type callable $page_count_callback Callback to retrieve the total number of pages.
	 *             @type callable $data_callback       Callback to retrieve the data for this table.
	 *             @type array    $schema              {
	 *                 Array of schema columns keyed by the column ID.
	 *
	 *                 @type string   $title               The table column to display.
	 *                 @type callable $render_callback     The callback to render an individual piece of data. Callback is expected
	 *                                                     to return a control object. Callback signature is as follows:
	 *                                                     `( $row, $table_control_id ) : Base_Control`.
	 *                 @type int      $priority Priority for ordering the column within the table.
	 *             }
	 *         }
	 *         @type array $data  {
	 *             Array of settings.

	 *             @type int    $perPage        The number of results to display per page. Default is the
	 *                                          items per page setting value.
	 *             @type string $order          The order to query the results. Default empty string.
	 *             @type string $orderby        The column to order by. Default asc.
	 *             @type bool   $showPagination Pagination will display if true, otherwise false. Default true.
	 *             @type bool   $allowSorting   Set to true to allow data to be sorted by column headers. Default true.
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
	 *         @type bool          $allow_column_replace Whether to allow column replacement. Default false.
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
	 * Prepares this instance for output via REST.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args    {
	 *     Optional. Any arguments needed for preparing an item for a REST response.
	 *
	 *     @type array $query   Arguments to pass along to the object query for populating a Table_Control.
	 *     @type bool  $columns Whether to retrieve the columns for a Table_Control request.
	 *     @type bool  $rows    Whether to retrieve the rows for a Table_Control request.
	 * }
	 */
	public function prepare_rest_object( $args = array() ) {
		if ( false !== $args['rows'] ) {
			$this->pages = 1;
		}
		$defaults = array(
			'columns' => false,
			'rows'    => false,
			'query'   => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$schema = $this->get_argument( 'schema', false );

		if ( false === $schema ) {
			return;
		}

		// Prepare the schema by including any externally-registered columns (if any).
		$schema->prepare();

		if ( false !== $args['columns'] ) {
			$this->columns = array();

			if ( $schema instanceof Table_Schema ) {
				$this->columns = $schema->get_data_controls();
			}
		}

		if ( false !== $args['rows'] ) {
			$this->rows  = array();
			$this->pages = 1;

			if ( $schema instanceof Table_Schema ) {
				$this->rows  = $schema->build_rows( $args['query'] );
				$this->pages = $schema->get_page_count( $args['query'] );
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
			if ( ! is_subclass_of( $result, Table_Schema::class ) && ! $result instanceof Table_Schema ) {
				$result = new Table_Schema( $this->get_id(), $result );

				$this->set_argument( 'schema', $result );
			}
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'table';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'data', 'header', 'schema', 'desc', 'allow_column_replace' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();

		$data        = $this->get_argument( 'data', array() );
		$header      = $this->get_argument( 'header', array() );
		$description = $this->get_argument( 'desc' );

		$output = '';

		// Set the schema type.
		$data['type'] = $id_base;

		$allowed_fields = array(
			'perPage'        => 'int',
			'order'          => 'string',
			'orderby'        => 'string',
			'showPagination' => 'boolean',
			'allowSorting'   => 'boolean',
			'type'           => 'string',
		);

		// Strip invalid fields
		$data = array_intersect_key( $data, $allowed_fields );

		// Cast types
		foreach ( array_keys( $data ) as $key ) {
			settype( $data[ $key ], $allowed_fields[ $key ] );
		}

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
				$output .= $heading->render( false );
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
				$output .= $desc_control->render( false );
			} else {
				$desc_control->log_errors();
			}
		}

		if ( empty( $data['perPage'] ) ) {
			// Set perPage to default to the items per page setting value.
			$items_per_page_setting = affiliate_wp()->settings->get( 'portal_items_per_page' );

			$data['perPage'] = ! empty( $items_per_page_setting ) ? $items_per_page_setting : 20;
		}

		$output .= html()->div_start( array(
			'class' => array(
				'max-w-7xl',
				'mx-auto',
				'px-4',
				'sm:px-6',
				'md:px-8',
				'mb-10',
			),
		), false );

		$output .= html()->div_start( array(
			'directives' => array(
				'x-data' => 'AFFWP.portal.table.default(' . json_encode( $data ) . ')',
				'x-init' => 'init()',
				'x-show' => '!isLoading',
			),
		), false );

		$output .= html()->div_start( array(
			'class' => array(
				'flex',
				'flex-col',
			),
		), false );

		$output .= html()->div_start( array(
			'class' => array(
				'-my-2',
				'py-2',
				'overflow-x-auto',
				'sm:-mx-6',
				'lg:-mx-8',
				'mt-4',
			),
		), false );

		$output .= html()->div_start( array(
			'class' => array(
				'align-middle',
				'inline-block',
				'min-w-full',
				'shadow',
				'overflow-hidden',
				'sm:rounded-lg',
				'border-b',
				'border-gray-200',
			),
		), false );

		$output .= html()->element_start( 'table', array(
			'class' => array(
				'min-w-full',
			),
		), false );

		$output .= html()->element_start( 'thead', array(), false );

		$output .= html()->element_start( 'tr', array(), false );

		$output .= html()->element_start( 'template', array(
			'directives' => array(
				'x-for' => '(heading, index) in schema',
				':key'  => 'index',
			),
		), false );

		$output .= html()->element_start( 'th', array(
			'directives' => array(
				'x-on:click' => 'handleOrderEvent( $event, heading.id )',
				':class'     => '{"cursor-pointer": true === allowSorting}',
			),
			'class'      => array(
				'px-6',
				'py-3',
				'border-b',
				'border-gray-200',
				'bg-white',
				'text-left',
				'text-xs',
				'leading-4',
				'font-medium',
				'text-gray-500',
				'uppercase',
				'tracking-wider',
			),
		), false );

		$output .= html()->div_start( array(
			'class' => array(
				'flex',
			),
		), false );

		$output .= html()->element_start( 'span', array(
			'directives' => array(
				'x-text' => 'heading.title',
			),
		), false );

		$output .= html()->element_end( 'span', false );

		$sort_desc_icon = new Icon_Control( array(
			'id'     => "{$id_base}-sort-desc-icon",
			'args'   => array(
				'name' => 'chevron-down',
				'type' => 'outline',
				'size' => 5,
			),
			'atts'   => array(
				'class' => array(
					'ml-1',
					'text-gray-500',
				),
			),
			'alpine' => array(
				'x-show' => "'desc' === getSortOrder(heading.id)",
			),
		) );

		if ( ! $sort_desc_icon->has_errors() ) {
			$output .= $sort_desc_icon->render( false );
		} else {
			$sort_desc_icon->log_errors( $this->get_view_id() );
		}

		$sort_asc_icon = new Icon_Control( array(
			'id'     => "{$id_base}-sort-asc-icon",
			'args'   => array(
				'name' => 'chevron-up',
				'type' => 'outline',
				'size' => 5,
			),
			'atts'   => array(
				'class' => array(
					'ml-1',
					'text-gray-500',
				),
			),
			'alpine' => array(
				'x-show' => "'asc' === getSortOrder(heading.id)",
			),
		) );

		if ( ! $sort_asc_icon->has_errors() ) {
			$output .= $sort_asc_icon->render( false );
		} else {
			$sort_asc_icon->log_errors();
		}

		$output .= html()->div_end( false );

		$output .= html()->element_end( 'th', false );

		$output .= html()->element_end( 'template', false );

		$output .= html()->element_end( 'tr', false );

		$output .= html()->element_end( 'thead', false );

		$output .= html()->element_start( 'tbody', array(
			'class' => array(
				'bg-white',
				'divide-y',
				'divide-gray-200',
			),
		), false );

		$output .= html()->element_start( 'template', array(
			'directives' => array(
				'x-for' => '(row, index) in rows',
				':key'  => 'index',
			),
		), false );

		$output .= html()->element_start( 'tr', array(), false );

		$output .= html()->element_start( 'template', array(
			'directives' => array(
				'x-for' => '(heading, headingIndex) in schema',
				':key'  => 'headingIndex',
			),
		), false );

		$output .= html()->element_start( 'td', array(
			'directives' => array(
				'x-html' => 'getCell(index, heading.id)',
			),
			'class'      => array(
				'px-6',
				'py-4',
				'whitespace-no-wrap',
				'text-sm',
				'leading-5',
				'font-medium',
				'text-gray-500',
			),
		), false );

		$output .= html()->element_end( 'td', false );

		$output .= html()->element_end( 'template', false );

		$output .= html()->element_end( 'tr', false );

		$output .= html()->element_end( 'template', false );

		$output .= html()->element_end( 'tbody', false );

		$output .= html()->element_end( 'table', false );

		$output .= html()->div_end( false );

		$output .= html()->div_end( false );

		$output .= html()->div_end( false );

		$output .= html()->div_start( array(
			'directives' => array(
				'x-show' => 'showPagination',
			),
			'class'      => array(
				'px-4',
				'py-3',
				'flex',
				'items-center',
				'justify-between',
				'sm:px-6',
			),
		), false );

		$output .= html()->element_start( 'nav', array(
			'class' => array(
				'affwp-pagination',
				'relative',
				'z-0',
				'inline-flex',
				'shadow-sm',
			),
		), false );

		$prev_page_link = new Link_Control( array(
			'id'     => "{$id_base}-prev-link",
			'atts'   => array(
				'class' => array(
					'prev',
					'page-numbers',
				),
				'role'  => 'button',
			),
			'args'   => array(
				'icon' => new Icon_Control( array(
					'id'   => "{$id_base}-prev-link-icon",
					'args' => array(
						'name'  => 'chevron-left',
						'type'  => 'solid',
						'class' => array( 'ml-1' ),
					),
				) ),
			),
			'alpine' => array(
				'x-bind:href' => 'urlForPage( previousPage )',
				'x-on:click'  => 'handlePageEvent( $event, previousPage )',
				':class'      => "{'disabled': currentPage <= 1}",
			),
		) );

		if ( ! $prev_page_link->has_errors() ) {
			$output .= $prev_page_link->render( false );
		} else {
			$prev_page_link->log_errors( $this->get_view_id() );
		}

		$output .= html()->element_start( 'template', array(
			'directives' => array(
				'x-for' => 'pageObject in getPages()',
				':key'  => 'pageObject.page',
			),
		), false );

		$page_numbers_link = new Link_Control( array(
			'id'     => "{$id_base}-page-numbers",
			'atts'   => array(
				'class' => 'page-numbers',
				'role'  => 'button',
			),
			'alpine' => array(
				'x-bind:href' => 'urlForPage( pageObject.page )',
				'x-on:click'  => 'handlePageEvent( $event, pageObject.page )',
				':class'      => "{'disabled': pageObject.disabled}",
				'x-text'      => 'pageObject.page',
			),
		) );

		if ( ! $page_numbers_link->has_errors() ) {
			$output .= $page_numbers_link->render( false );
		} else {
			$page_numbers_link->log_errors( $this->get_view_id() );
		}

		$output .= html()->element_end( 'template', false );

		$next_page_link = new Link_Control( array(
			'id'     => "{$id_base}-next-link",
			'atts'   => array(
				'class' => array(
					'next',
					'page-numbers',
				),
				'role'  => 'button',
			),
			'args'   => array(
				'icon' => new Icon_Control( array(
					'id'   => "{$id_base}-next-link-icon",
					'args' => array(
						'name'  => 'chevron-right',
						'type'  => 'solid',
						'class' => array( 'ml-1' ),
					),
				) ),
			),
			'alpine' => array(
				'x-bind:href' => 'urlForPage( nextPage )',
				'x-on:click'  => 'handlePageEvent( $event, nextPage )',
				':class'      => "{'disabled': currentPage === pages}",
			),
		) );

		if ( ! $next_page_link->has_errors() ) {
			$output .= $next_page_link->render( false );
		} else {
			$next_page_link->log_errors( $this->get_view_id() );
		}

		$output .= html()->element_end( 'nav', false );

		$output .= html()->div_end( false );

		$output .= html()->div_end( false );

		$output .= html()->div_end( false );

		if ( ! empty( $description ) && 'after' === $desc_position ) {
			if ( ! $desc_control->has_errors() ) {
				$output .= $desc_control->render( false );
			} else {
				$desc_control->log_errors();
			}
		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}

	}

	/**
	 * Retrieves REST endpoint preload data for this control.
	 *
	 * @since 1.0.4
	 *
	 * @return array|string List of prefetched data keyed by the preloaded REST endpoint, or a single endpoint.
	 */
	public function get_preload_routes() {
		return add_query_arg( array( 'columns' => 'true' ), '/affwp/v2/portal/controls/' . $this->get_id() );
	}

}
