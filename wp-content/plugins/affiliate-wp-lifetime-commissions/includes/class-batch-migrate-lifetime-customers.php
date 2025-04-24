<?php
namespace AffWP\Utils\Batch_Process;

use AffWP\Utils;
use AffWP\Utils\Batch_Process as Batch;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implements an batch processor for migrating linked customers from the customer meta to lifetime customers table.
 *
 * @see \AffWP\Utils\Batch_Process\Base
 * @see \AffWP\Utils\Batch_Process
 * @package AffWP\Utils\Batch_Process
 */
class Migrate_Lifetime_Commissions_Customers extends Utils\Batch_Process implements Batch\With_PreFetch {

	/**
	 * Batch process ID.
	 *
	 * @since  1.4.1
	 * @var    string
	 */
	public $batch_id = 'migrate-lc-customers';

	/**
	 * Capability needed to perform the current batch process.
	 *
	 * @since  1.4.1
	 * @var    string
	 */
	public $capability = 'manage_customers';

	/**
	 * Number of customers meta to process per step.
	 *
	 * @since  1.4.1
	 * @var    int
	 */
	public $per_step = 1;

	/**
	 * Initializes the batch process.
	 *
	 * @since  1.4.1
	 */
	public function init( $data = null ) {}

	/**
	 * Handles pre-fetching customers meta for customers in migration.
	 *
	 * @since  1.4.1
	 */
	public function pre_fetch() {

		$total_to_process = $this->get_total_count();

		if ( false === $total_to_process ) {

			global $wpdb;

			$table_name = affiliate_wp()->customer_meta->table_name;

			$total_to_migrate = $wpdb->get_var( "SELECT COUNT(meta_id) FROM {$table_name};" );

			$this->set_total_count( absint( $total_to_migrate ) );
		}
	}

	/**
	 * Executes a single step in the batch process.
	 *
	 * @since  1.4.1
	 *
	 * @return int|string|\WP_Error Next step number, 'done', or a WP_Error object.
	 */
	public function process_step() {

		global $wpdb;

		$current_count = $this->get_current_count();

		$table_name = affiliate_wp()->customer_meta->table_name;

		$linked_customers = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table_name} LIMIT %d, %d;",
				$this->get_offset(),
				$this->per_step
			)
		);

		if ( empty( $linked_customers ) ) {
			return 'done';
		}

		$migrated = array();

		foreach ( $linked_customers as $linked_customer ) {

			$customer = affwp_get_customer( $linked_customer->affwp_customer_id );

			if ( $customer ) {

				$linked_affiliate = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affwp_customer_id', $customer->customer_id );

				if ( ! $linked_affiliate ) {

					$args = array(
						'affwp_customer_id' => $customer->customer_id,
						'affiliate_id'      => $linked_customer->meta_value,
						'date_created'      => $customer->date_created,
					);

					affiliate_wp_lifetime_commissions()->lifetime_customers->add( $args );
				}
			}

			$migrated[] = $linked_customer->meta_id;
		}

		$this->set_current_count( absint( $current_count ) + count( $migrated ) );

		return ++ $this->step;
	}

	/**
	 * Retrieves a message based on the given message code.
	 *
	 * @since  1.4.1
	 *
	 * @param string $code Message code.
	 * @return string Message.
	 */
	public function get_message( $code ) {

		switch( $code ) {

			case 'done':
				$final_count = $this->get_current_count();

				$message = sprintf(
					_n(
						'%d customer was updated successfully.',
						'%d customers were updated successfully.',
						$final_count,
						'affiliate-wp-lifetime-commissions'
					), number_format_i18n( $final_count )
				);
				break;

			default:
				$message = '';
				break;
		}

		return $message;
	}

	/**
	 * Defines logic to execute after the batch processing is complete.
	 *
	 * @since  1.4.1
	 *
	 * @param string $batch_id Batch process ID.
	 */
	public function finish( $batch_id ) {

		update_option( 'affwp_lc_migrate_customers', 1 );

		// Clean up.
		parent::finish( $batch_id );
	}
}
