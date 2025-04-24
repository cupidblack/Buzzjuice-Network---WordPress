<?php
/**
 * Core: Chart_Schema
 *
 * @package     AffiliateWP Affiliate Dashboard
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
 * Defines the axes for a chart, as well as how to retrieve the data.
 *
 * @since 1.0.0
 */
class Chart_Schema extends Data_Schema {

	/**
	 * Data control to use with this schema.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $data_control = '\AffiliateWP_Affiliate_Portal\Core\Components\Controls\Chart_Data_Control';

	/**
	 * Key for the x-axis label in charts.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $x_label_key;

	/**
	 * @inheritDoc
	 */
	public function __construct( $control_id, $args = array(), $defaults = array() ) {
		parent::__construct( $control_id, $args );

		$this->x_label_key = $this->parsed_args['x_label_key'];
	}

	/**
	 * @inheritDoc
	 */
	public function get_defaults() {
		return array(
			'x_label_key' => '',
		);
	}

	/**
	 * @inheritDoc
	 */
	public function build_sets( $args ) {
		$sets = array();

		// Loop through each dataset and get data.
		foreach ( $this->get_schema() as $schema_control ) {
			$schema_data = $schema_control->get_dataset_data( $args );

			$sets[] = array(
				'title' => $schema_control->get_argument( 'title' ),
				'data'  => $schema_data,
				'color' => $schema_control->get_argument( 'color' ),
			);
		}

		return $sets;
	}

}
