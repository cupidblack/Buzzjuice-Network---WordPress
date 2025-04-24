<?php
/**
 * Multi-Tier Commissions Admin Controller File
 *
 * Adds the settings to the admin.
 *
 * @package     AffiliateWP
 * @subpackage  AffiliateWP\MTC\Admin
 * @since       1.0.0
 * @copyright   Copyright (c) 2024, Awesome Motive, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP\MTC\Admin;

use function AffiliateWP\MTC\affiliate_wp_mtc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles different Multi-Tier Commissions admin functionalities.
 *
 * @since 1.0
 */
class Admin {

	/**
	 * Construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Enqueue styles and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		if ( ! affiliate_wp_mtc()->is_activated() ) {
			return; // Multi-Tier Commissions is disabled.
		}

		// Add the parent_affiliate_id to the new, edit and review screens.
		add_action( 'affwp_new_affiliate_after_status', array( $this, 'display_parent_affiliate_field' ) );
		add_action( 'affwp_edit_affiliate_after_status', array( $this, 'display_parent_affiliate_field' ) );
		add_action( 'affwp_review_affiliate_end', array( $this, 'display_parent_affiliate_on_review' ) );

		// Handle save operations, saving the parent_affiliate_id.
		add_action( 'affwp_add_new_affiliate', array( $this, 'save_parent_affiliate_on_form_submission' ), 10, 2 );
		add_action( 'affwp_update_affiliate', array( $this, 'update_parent_affiliate_on_form_submission' ), 0 );

		// Assign the affiliate network to the next affiliate when deleted.
		add_action( 'affwp_post_delete_affiliate', array( $this, 'reassign_affiliates_on_delete' ) );

		// Handle ajax to populate parent affiliates dropdown.
		add_action( 'wp_ajax_affiliatewp_mtc_parent_affiliate_select', array( $this, 'handle_parent_affiliate_select' ) );
	}

	/**
	 * Register and enqueue necessary assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {

		if ( ! affwp_is_admin_page() ) {
			return; // No need to load these in any other page.
		}

		wp_enqueue_style(
			'affiliatewp-mtc-admin',
			sprintf(
				'%1$sassets/css/affiliatewp-mtc-admin%2$s.css',
				AFFWP_MTC_PLUGIN_URL,
				affiliate_wp()->scripts->get_suffix()
			),
			array(),
			affiliate_wp()->scripts->get_version()
		);

		affiliate_wp()->scripts->enqueue(
			'affiliatewp-mtc-admin',
			array(
				'affwp-select2',
			),
			sprintf(
				'%1$sassets/js/affiliatewp-mtc-admin%2$s.js',
				AFFWP_MTC_PLUGIN_URL,
				affiliate_wp()->scripts->get_suffix()
			)
		);

		$data = wp_json_encode(
			array_filter(
				array(
					'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
					'nonce'       => wp_create_nonce( 'affiliatewp_mtc' ),
					'affiliateID' => absint( filter_input( INPUT_GET, 'affiliate_id' ) ) ?? 0,
					'i18n'        => array(
						'select2placeholder' => esc_html( __( 'Select an affiliate', 'affiliatewp-multi-tier-commissions' ) ),
					),
				)
			)
		);

		wp_add_inline_script( 'affiliatewp-mtc-admin', "affiliatewp.mtc.data={$data};", 'after' );
	}

	/**
	 * Reassigns sub affiliates to another parent affiliate when an affiliate is deleted.
	 *
	 * This method is triggered when an affiliate is deleted. It retrieves the ancestors and sub affiliates
	 * of the deleted affiliate. If there are sub affiliates, it attempts to reassign them to the first ancestor
	 * affiliate found. If there are no ancestor affiliates, it removes the parent_affiliate_id meta from the
	 * sub affiliates, effectively detaching them from any network.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The ID of the affiliate being deleted.
	 */
	public function reassign_affiliates_on_delete( int $affiliate_id ) {

		$ancestors      = affiliate_wp_mtc()->network->get_affiliate_ancestors( $affiliate_id );
		$sub_affiliates = affiliate_wp_mtc()->network->get_sub_affiliates( $affiliate_id );

		if ( empty( $sub_affiliates ) ) {
			return; // No affiliates to reconnect.
		}

		// Get the first affiliate in the list.
		$parent_affiliate_id = reset( $ancestors );

		// There's no affiliate available to attach the sub affiliates.
		if ( false === $parent_affiliate_id ) {

			// These affiliates don't belong to any network anymore.
			foreach ( $sub_affiliates as $sub_affiliate_id ) {
				affwp_delete_affiliate_meta( $sub_affiliate_id, 'parent_affiliate_id' );
			}

			return; // Nothing to do here anymore.
		}

		// Update the sub affiliates to the new parent.
		foreach ( $sub_affiliates as $sub_affiliate_id ) {
			affwp_update_affiliate_meta( $sub_affiliate_id, 'parent_affiliate_id', $parent_affiliate_id );
		}
	}

	/**
	 * Saves the parent affiliate ID when adding new affiliates.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $affiliate_id The ID of the affiliate being processed.
	 * @param array $data The form submission data.
	 */
	public function save_parent_affiliate_on_form_submission( int $affiliate_id, array $data ) {

		if ( empty( $data['parent_affiliate_id'] ) ) {
			return;
		}

		try {
			affiliate_wp_mtc()->network->connect_to_referrer( $affiliate_id, absint( $data['parent_affiliate_id'] ) );
		} catch ( \Exception $error ) {
			affiliate_wp()->utils->log( 'Error when trying to refer an affiliate.', $error->getMessage() );
		}
	}

	/**
	 * Updates the parent affiliate ID when an affiliate is updated.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The form submission data.
	 */
	public function update_parent_affiliate_on_form_submission( array $data ) {

		if ( empty( $data['parent_affiliate_id'] ) ) {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'parent_affiliate_id' );
			return;
		}

		try {
			affiliate_wp_mtc()->network->connect_to_referrer(
				absint( $data['affiliate_id'] ),
				absint( $data['parent_affiliate_id'] ),
				true
			);
		} catch ( \Exception $error ) {
			affiliate_wp()->utils->log( 'Error when trying to refer an affiliate.', $error->getMessage() );
		}
	}

	/**
	 * Handle the dropdown ajax request.
	 *
	 * It fills with all Affiliates (paginated) or based in the term supplied.
	 * Both the current affiliate and its direct sub affiliates are not returned as options.
	 *
	 * @since 1.0.0
	 */
	public function handle_parent_affiliate_select() : array {

		// Nonce check.
		if ( ! wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ), 'affiliatewp_mtc' ) ) {

			wp_send_json(
				array(
					'total' => 0,
					'items' => array(
						array(
							'id'   => 0,
							'text' => esc_html( __( 'Nonce check failed.', 'affiliatewp-multi-tier-commissions' ) ),
						),
					),
				)
			);
			exit;
		}

		$per_page     = filter_input( INPUT_GET, 'per_page', FILTER_SANITIZE_NUMBER_INT ) ?? 10;
		$affiliate_id = filter_input( INPUT_GET, 'affiliate_id', FILTER_SANITIZE_NUMBER_INT );
		$exclude_list = array_merge(
			array( (int) $affiliate_id ),
			affiliate_wp_mtc()->network->get_sub_affiliates( $affiliate_id )
		);

		$affiliates = affiliate_wp()->affiliates->get_affiliates(
			array(
				'search'  => filter_input(
					INPUT_GET,
					'q',
					FILTER_SANITIZE_FULL_SPECIAL_CHARS
				) ?? '',
				'number'  => $per_page,
				'offset'  => (
					( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT ) ?? 1 ) - 1
				) * $per_page,
				'exclude' => $exclude_list,
				'status'  => 'active',
				'order'   => 'ASC',
				'orderby' => 'name',
				'fields'  => 'ids',
			)
		);

		wp_send_json(
			array(
				'exclude' => $exclude_list,
				'total' => count( $affiliates ),
				'items' => array_filter(
					array_merge(
						array(
							array(
								'id'   => 0,
								'text' => esc_html( __( 'None', 'affiliatewp-multi-tier-commissions' ) ),
							),
						),
						array_map(
							function ( $affiliate_id ) use ( $exclude_list ) {

								$affiliate = affwp_get_affiliate( $affiliate_id );

								if (
									! is_a( $affiliate, '\AffWP\Affiliate' ) ||
									in_array( $affiliate_id, $exclude_list, true )
								) {
									return array();
								}

								return array(
									'id'   => $affiliate_id,
									'text' => $this->get_affiliate_dropdown_text( (int) $affiliate_id ),
								);
							},
							$affiliates
						)
					)
				),
			)
		);

		exit;
	}

	/**
	 * Retrieve the text to be used in the Parent Affiliate dropdowns.
	 * Is the combination of the display_name — user_email.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The affiliate ID.
	 * @return string Text to be used in the dropdown.
	 */
	private function get_affiliate_dropdown_text( int $affiliate_id ) : string {

		return sprintf(
			'%1$s — %2$s',
			affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id ),
			affwp_get_affiliate_email( $affiliate_id )
		);
	}

	/**
	 * Displays the Parent Affiliate field in the Affiliate new/edit screens.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $affiliate The affiliate object.
	 */
	public function display_parent_affiliate_field( $affiliate ) {

		$parent_affiliate_id = is_a( $affiliate, '\AffWP\Affiliate' )
			? (int) $affiliate->get_meta( 'parent_affiliate_id', true )
			: 0;
		?>

		<tr class="form-row" data-field="parent-affiliate">
			<th scope="row">
				<label for="parent-affiliate-id"><?php esc_html_e( 'Parent Affiliate', 'affiliatewp-multi-tier-commissions' ); ?></label>
			</th>
			<td>
				<select name="parent_affiliate_id" id="parent-affiliate-id">
					<option value="<?php echo esc_attr( $parent_affiliate_id ); ?>>">
						<?php
						echo empty( $parent_affiliate_id )
							? esc_html_e( 'Select an affiliate', 'affiliatewp-multi-tier-commissions' )
							: esc_html( $this->get_affiliate_dropdown_text( $parent_affiliate_id ) );
						?>
					</option>
				</select>
				<p class="description"><?php esc_html_e( 'Select the parent affiliate for multi-tier commission linkage.', 'affiliatewp-multi-tier-commissions' ); ?></p>
			</td>
		</tr>

		<script>
			document.addEventListener( 'DOMContentLoaded', function() {

				if ( ! affiliatewp.has( 'mtc' ) ) {

					console.error( 'Missing MTC scripts.' );
					return;
				}

				affiliatewp.mtc.initParentAffiliateDropdown();
			} );
		</script>

		<?php
	}

	/**
	 * Displays the Parent Affiliate field in the Affiliate review screen.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $affiliate The affiliate object.
	 */
	public function display_parent_affiliate_on_review( $affiliate ) {

		$parent_affiliate_id = is_a( $affiliate, '\AffWP\Affiliate' )
			? (int) $affiliate->get_meta( 'parent_affiliate_id', true )
			: 0;

		if ( empty( $parent_affiliate_id ) ) {
			return;
		}

		?>

		<tr class="form-row">

			<th scope="row">
				<?php esc_html_e( 'Parent Affiliate', 'affiliatewp-multi-tier-commissions' ); ?>
			</th>

			<td>
				<?php echo esc_html( $this->get_affiliate_dropdown_text( $parent_affiliate_id ) ); ?>
			</td>

		</tr>

		<?php
	}
}

new Admin();
