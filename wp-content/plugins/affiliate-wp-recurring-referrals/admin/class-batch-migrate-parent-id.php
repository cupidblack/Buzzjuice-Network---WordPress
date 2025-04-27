<?php
namespace AffWP\Utils\Batch_Process;

use AffWP\Utils;
use AffWP\Utils\Batch_Process as Batch;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implements an batch processor for adding parent id for all recurring referrals.
 *
 * @see \AffWP\Utils\Batch_Process\Base
 * @see \AffWP\Utils\Batch_Process
 * @package AffWP\Utils\Batch_Process
 */
class AffiliateWP_Recurring_Referrals_Migrate_Parent_ID extends Utils\Batch_Process implements Batch\With_PreFetch {

	/**
	 * Batch process ID.
	 *
	 * @since  1.7
	 * @var    string
	 */
	public $batch_id = 'affwp-rr-migrate-parent-id';

	/**
	 * Capability needed to perform the current batch process.
	 *
	 * @since  1.7
	 * @var    string
	 */
	public $capability = 'manage_affiliates';

	/**
	 * Number of referrals to process per step.
	 *
	 * @since  1.7
	 * @var    int
	 */
	public $per_step = 1;

	/**
	 * Initializes the batch process.
	 *
	 * @since  1.7
	 */
	public function init( $data = null ) {}

	/**
	 * Handles pre-fetching referral IDs for referrals in migration.
	 *
	 * @since  1.7
	 */
	public function pre_fetch() {

		$total_to_process = $this->get_total_count();

		if ( false === $total_to_process ) {

			$total_to_process = affiliate_wp()->referrals->count( array(
				'number' => -1,
			) );

			$this->set_total_count( $total_to_process );
		}
	}

	/**
	 * Executes a single step in the batch process.
	 *
	 * @since  1.7
	 *
	 * @return int|string|\WP_Error Next step number, 'done', or a WP_Error object.
	 */
	public function process_step() {

		$current_count = $this->get_current_count();

		$args = array(
			'number'     => $this->per_step,
			'offset'     => $this->get_offset(),
			'orderby'    => 'referral_id',
			'order'      => 'ASC',
		);

		$referrals = affiliate_wp()->referrals->get_referrals( $args );

		if ( empty( $referrals ) ) {
			return 'done';
		}

		$migrated = array();

		$integrations_to_upgrade = array(
			'edd',
			'gravityforms',
			'lifterlms',
			'memberpress',
			'paypal',
			'pmp',
			'rcp',
			'stripe',
			'woocommerce',
			'zippycourses'
		);

		foreach ( $referrals as $referral ) {

			if ( ! in_array( $referral->context, $integrations_to_upgrade ) ) {
				continue;
			}

			// Checks if the custom field for a RCP referral is a string.
			if ( 'rcp' == $referral->context && ! is_string( $referral->custom ) ) {
				continue;
			}

			// Checks if the custom field for other referrals apart from RCP is an integer.
			if ( 'rcp' != $referral->context && ! is_numeric( $referral->custom )  ) {
				continue;
			}

			$parent_referral = $this->get_parent_referral( $referral );

			if ( ! $parent_referral || empty( $parent_referral ) || ! is_object( $parent_referral ) ) {
				continue;
			}

			affiliate_wp()->referrals->update( $referral->referral_id, array( 'parent_id' => $parent_referral->referral_id ), '', 'referral' );

			$migrated[] = $referral->referral_id;
		}

		$this->set_current_count( absint( $current_count ) + count( $migrated ) );

		return ++ $this->step;
	}

	/**
	 * Retrieves the parent referral object for a recurring referral.
	 *
	 * @since  1.7
	 *
	 * @param \AffWP\Referral $referral Referral Object.
	 *
	 * @return \AffWP\Referral|false    Parent referral object, otherwise false.
	 */
	public function get_parent_referral( $referral ) {

		switch ( $referral->context ) {

			case 'paypal':
				$parent_referral = affiliate_wp()->referrals->get_by( 'referral_id', $referral->custom, $referral->context );
				break;

			case 'stripe':
				$parent_referral = affiliate_wp()->referrals->get_by( 'referral_id', $referral->custom['parent'], $referral->context );
				break;

			case 'zippycourses':
				$parent_referral = affiliate_wp()->referrals->get_by( 'referral_id', $referral->custom['parent'], $referral->context );
				break;

			default:
				$parent_referral = affiliate_wp()->referrals->get_by( 'reference', $referral->custom, $referral->context );
				break;
		}

		return $parent_referral;
	}

	/**
	 * Retrieves a message based on the given message code.
	 *
	 * @since  1.7
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
						'%s referral was updated successfully.',
						'%s referrals were updated successfully.',
						$final_count,
						'affiliate-wp-recurring-referrals'
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
	 * @since  1.7
	 *
	 * @param string $batch_id Batch process ID.
	 */
	public function finish( $batch_id ) {

		update_option( 'affwp_rr_migrate_parent_id', 1 );

		// Clean up.
		parent::finish( $batch_id );
	}
}
