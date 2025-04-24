<?php
/**
 * Core: Table_Schema
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
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
 * Defines the columns, and other relevant information to render a table.
 *
 * @since 1.0.0
 */
class Table_Schema extends Data_Schema {

	/**
	 * Data control to use with this schema.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $data_control = '\AffiliateWP_Affiliate_Portal\Core\Components\Controls\Table_Column_Control';

	/**
	 * Page count callback.
	 *
	 * @since 1.0.0
	 * @var   callable
	 */
	private $page_count_callback;

	/**
	 * @inheritDoc
	 */
	public function __construct( $control_id, $args = array() ) {
		parent::__construct( $control_id, $args );

		$this->page_count_callback = $this->parsed_args['page_count_callback'];
	}

	/**
	 * @inheritDoc
	 */
	public function get_defaults() {
		return array(
			'page_count_callback' => false,
		);
	}

	/**
	 * Retrieves the data using the data callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments to pass to the data callback.
	 *
	 * @return array|\WP_Error List of data objects or WP_Error if there was a problem.
	 */
	public function get_page_count( $args ) {
		if ( ! is_callable( $this->page_count_callback ) ) {
			return new \WP_Error( 'invalid_callback',
				sprintf( 'The %s table count_callback is invalid.', $this->name ),
				$args
			);
		}

		return call_user_func( $this->page_count_callback, $args );
	}

	/**
	 * @inheritDoc
	 */
	public function build_sets( $args ) {
		return $this->build_rows( $args );
	}

	/**
	 * Builds table rows from datasets.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The arguments to pass to the table's callback.
	 * @return array List of rows structured and rendered by the table schema.
	 */
	public function build_rows( $args ) {
		$result = array();

		if ( ! is_array( $args ) ) {
			$args = array( $args );
		}

		$data = $this->get_data( $args );

		foreach ( $data as $datum ) {
			$result[] = $this->build_row( $datum );
		}

		if ( $this->has_errors() ) {
			$this->log_errors( "{$this->name}_schema" );
		}

		return $result;
	}

	/**
	 * Constructs a single row based on the provided row data and table schema.
	 *
	 * @since 1.0.0
	 *
	 * @param array|object $data The data for this row.
	 *
	 * @return array The structured row.
	 */
	protected function build_row( $data ) {
		$row = array();

		$schema = $this->get_schema();

		// If the schema is empty, bail.
		if ( empty( $schema ) ) {
			return array();
		}

		foreach ( array_keys( $schema ) as $column ) {
			$row[ $column ] = $this->render_data( $column, $data );
		}

		return $row;
	}

}
