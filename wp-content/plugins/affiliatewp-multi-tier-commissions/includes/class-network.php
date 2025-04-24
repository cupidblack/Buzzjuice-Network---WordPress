<?php
/**
 * Multi Tier Commissions Network
 *
 * Utilities to work with the Affiliate's network.
 *
 * @package     AffiliateWP
 * @subpackage  AffiliateWP\MTC
 * @since       1.0.0
 * @copyright   Copyright (c) 2024, Awesome Motive, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP\MTC;

use AffiliateWP\Utils\Icons;

/**
 * Series of methods to handle the Affiliate Network.
 *
 * @since 1.0.0
 */
class Network {

	/**
	 * Retrieves the initial tier rate for referrals.
	 *
	 * This function checks the referral rate type in the Affiliate WP settings. If the type is not set to 'percentage',
	 * it returns a default rate of 20. Otherwise, it retrieves the referral rate from the settings.
	 *
	 * @since 1.0.0
	 *
	 * @return float The initial tier rate for referrals.
	 */
	private function get_initial_tier_rate() : float {

		$default_rate = 20;

		// If is not percentage, return the system default rate of 20%.
		if ( affiliate_wp()->settings->get( 'referral_rate_type' ) !== 'percentage' ) {
			return $default_rate;
		}

		return (float) affiliate_wp()->settings->get( 'referral_rate', $default_rate );
	}

	/**
	 * Retrieve the tiers with rates with data sanitized.
	 *
	 * This function fetches the tiers using the `get_tiers` method.
	 * If no tiers are found, it provides a default array with the initial tier rate
	 * and a zero rate. Each tier's rate is cast to a float to ensure consistency
	 * in the type of the 'rate' value.
	 * It also set a rate_type in case it doesn't exist.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 added default value for rate_type.
	 *
	 * @return array An array of tiers where each 'rate' is a float. If no tiers are
	 *               found, returns an array with the initial tier rate and a zero rate.
	 */
	public function get_tiers_rates() : array {

		$tiers = affiliate_wp_mtc()->get_tiers();

		// Check if tiers are empty and return default rates if so.
		if ( empty( $tiers ) ) {
			$tiers = [
				[
					'rate' => $this->get_initial_tier_rate(),
				],
				[
					'rate' => 0,
				],
			];
		}

		return array_map(
			function( $tier ) {
				return array_merge(
					$tier,
					[
						'rate'      => is_null( $tier['rate'] ) ? '' : floatval( $tier['rate'] ),
						'rate_type' => $tier['rate_type'] ?? 'percentage',
					]
				);
			},
			$tiers
		);
	}

	/**
	 * Turn a multidimensional array into a flat array containing both keys and values.
	 *
	 * @since 1.0.0
	 *
	 * @param array $array The array to convert.
	 *
	 * @return array
	 */
	private function flatten_array( array $array ) : array {

		$result = array();

		foreach ( $array as $key => $value ) {

			if ( is_array( $value ) ) {

				if ( $this->is_assoc_array( $value ) ) {
					$result[] = $key;
				}

				$result = array_merge( $result, $this->flatten_array( $value ) );
			} else {
				$result[] = $value;
			}
		}

		return $result;
	}

	/**
	 * Check if is a multidimensional array.
	 *
	 * @since 1.0.0
	 *
	 * @param array|mixed $array The array to check.
	 *
	 * @return bool
	 */
	private function is_assoc_array( $array ) : bool {
		return is_array( $array ) && array_keys( $array ) !== range( 0, count( $array ) - 1 );
	}

	/**
	 * Retrieve the flat list of sub-affiliates from a given affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The affiliate ID.
	 *
	 * @return array
	 */
	public function get_affiliate_flat_list( int $affiliate_id ) : array {
		return array_unique( $this->flatten_array( $this->get_affiliate_tree( $affiliate_id ) ) );
	}

	/**
	 * Retrieve the full tree for a given affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $affiliate_id The affiliate ID.
	 * @param bool $expanded_tree If true, will return any affiliates, independently if they were recruited by the initial affiliate or not.
	 * @param bool $ignore_cache Ignore cache and execute the query again.
	 *
	 * @return array
	 */
	public function get_affiliate_tree( int $affiliate_id, bool $expanded_tree = true, bool $ignore_cache = true ) : array {

		$cache_key = "affwp_mtc_affiliate_network_tree_for_{$affiliate_id}";

		if ( ! $ignore_cache ) {

			$network = get_transient( $cache_key );

			if ( is_array( $network ) ) {
				return $network;
			}
		}

		$network = array();

		$this->build_tree_recursive( $affiliate_id, $expanded_tree, $network, true );

		if ( ! $ignore_cache ) {
			set_transient( $cache_key, $network, DAY_IN_SECONDS );
		}

		return $network;
	}

	/**
	 * Recursively build the entire tree for a given affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $affiliate_id  The affiliate to build the tree.
	 * @param bool  $expanded_tree If true, will return any affiliates, independently if they were recruited by the initial affiliate or not.
	 * @param array $network       The final array with the entire network.
	 * @param bool  $is_root       Used to identify if we are looping through the root affiliate.
	 * @param array $visited       Array to keep track of visited affiliates to prevent infinite loops.
	 *
	 * @return void
	 */
	private function build_tree_recursive(
		int $affiliate_id,
		bool $expanded_tree = true,
		array &$network = [],
		bool $is_root = false,
		array &$visited = []
	) : void {
		/**
		 * Store the initial Affiliate ID, so we can compare against the sponsor ID.
		 */
		static $parent_affiliate_id;

		/**
		 * Store the affiliates already interacted by this method, avoiding infinite loops.
		 */
		$visited = [];

		if ( $is_root ) {
			$parent_affiliate_id      = $affiliate_id;
			$network[ $affiliate_id ] = [];
		}

		// Avoid infinite loops by checking if an affiliate has been visited already.
		if ( in_array( $affiliate_id, $visited, true ) ) {
			// Remove it from the network, as it is already in the tree.
			unset( $network[ $affiliate_id ] );

			return; // Continuing from here would just recreate the entire tree again, so we stop here.
		}

		$visited[] = $affiliate_id;

		$tiered_affiliates = $this->get_sub_affiliates( $affiliate_id );

		foreach ( $tiered_affiliates as $tiered_affiliate_id ) {
			if (
				! $expanded_tree &&
				$this->get_affiliate_sponsor_id( $tiered_affiliate_id ) !== $parent_affiliate_id
			) {
				continue;
			}

			$network[ $affiliate_id ][ $tiered_affiliate_id ] = [];
			$this->build_tree_recursive(
				$tiered_affiliate_id,
				$expanded_tree,
				$network[ $affiliate_id ],
				false,
				$visited
			);
		}
	}

	/**
	 * Retrieve all tiered affiliates (downline) for a given affiliate.
	 * Commonly used to build the affiliate's tree.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $affiliate_id The affiliate ID.
	 * @param bool $ignore_cache Ignore cache and execute the query again.
	 *
	 * @return array
	 */
	public function get_sub_affiliates( int $affiliate_id, bool $ignore_cache = true ) : array {

		$cache_key = "affwp_mtc_tiered_affiliates_for_{$affiliate_id}";

		if ( ! $ignore_cache ) {

			$ids = get_transient( $cache_key );

			if ( is_array( $ids ) ) {
				return $ids;
			}
		}

		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- The SQL is safe.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				str_replace(
					'{table}',
					sprintf( '%s', affiliate_wp()->affiliate_meta->table_name ),
					'SELECT affiliate_id FROM {table} WHERE meta_key = %s AND meta_value = %d ORDER BY meta_id ASC'
				),
				'parent_affiliate_id',
				$affiliate_id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		if ( ! $ignore_cache ) {
			set_transient( $cache_key, $ids, DAY_IN_SECONDS );
		}

		return array_map( 'absint', $ids );
	}

	/**
	 * Retrieves the ancestors of an affiliate up to a specified maximum number of tiers.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The ID of the affiliate whose ancestors are being retrieved.
	 * @param int $max_number_of_tiers The maximum number of tiers to retrieve ancestors for (default: 999).
	 *
	 * @return array An array containing the IDs of the affiliate's ancestors.
	 */
	public function get_affiliate_ancestors( int $affiliate_id, int $max_number_of_tiers = 999 ) : array {

		$affiliate = affwp_get_affiliate( $affiliate_id );

		if ( ! is_a( $affiliate, '\AffWP\Affiliate' ) ) {
			return array();
		}

		// The list of ancestors.
		$ancestors = array();

		// The current tier index.
		$current_tier = 0;

		// Keep track of the original affiliate ID.
		$original_affiliate_id = $affiliate_id;

		// Traverse the original affiliate network.
		while ( $current_tier < $max_number_of_tiers ) {

			// Save the last affiliate_id interacted.
			$affiliate_id = affwp_get_affiliate_meta( $affiliate_id, 'parent_affiliate_id', true );

			if ( empty( $affiliate_id ) ) {
				break; // Affiliate's network ended. This affiliate doesn't have a parent.
			}

			// This will only happen if we find a loop situation, so we don't want to continue.
			if ( in_array( $affiliate_id, $ancestors, true ) ) {
				break;
			}

			$affiliate = affwp_get_affiliate( $affiliate_id );

			if ( ! is_a( $affiliate, '\AffWP\Affiliate' ) ) {
				continue; // Not an Affiliate object. Skip and try the next one.
			}

			if ( 'active' !== $affiliate->status ) {
				continue; // If it is not an active affiliate, skip to the next.
			}

			// Include the affiliate as ancestor.
			$ancestors[] = $affiliate_id;

			// Update tier index.
			$current_tier++;
		}

		// Return unique IDs and ensure the original one is not present in the final array.
		return array_unique(
			array_diff(
				$ancestors,
				array( $original_affiliate_id )
			)
		);
	}

	/**
	 * Convert a multidimensional array of affiliates in a nested unordered list.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tree The affiliate's tree.
	 * @param int   $level The current level that is being interacted.
	 *
	 * @return string The final markup.
	 */
	public function convert_tree_to_html( array $tree, int $level = 0 ) : string {

		$level++;

		$html = sprintf( '<ul data-level="%d">', $level );

		foreach ( $tree as $affiliate_id => $sub_affiliates ) {

			$html .= sprintf( '<li>%s', $this->get_affiliate_card_template( $affiliate_id, $level ) );

			if ( ! empty( $sub_affiliates ) ) {
				$html = sprintf( "$html %s", $this->convert_tree_to_html( $sub_affiliates, $level ) );
			}

			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Retrieve the affiliate status necessary to render the card correctly.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The affiliate ID.
	 *
	 * @return string The status string: deleted, active, inactive or pending.
	 */
	private function get_card_affiliate_status( int $affiliate_id ) : string {

		$user_id = affwp_get_affiliate_user_id( $affiliate_id );

		return empty( $user_id ) || false === get_user_by( 'id', $user_id )
			? 'deleted'
			: affwp_get_affiliate_status( $affiliate_id );
	}

	/**
	 * Retrieve the affiliate's name string based on the affiliate's status.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The affiliate ID.
	 *
	 * @return string The status string: deleted, active, inactive or pending.
	 */
	private function get_card_affiliate_name( int $affiliate_id ) : string {

		$status = $this->get_card_affiliate_status( $affiliate_id );

		if ( 'deleted' === $status ) {
			return esc_html( __( 'User deleted', 'affiliatewp-multi-tier-commissions' ) );
		}

		return affwp_get_affiliate_full_name_or_display_name( $affiliate_id );
	}

	/**
	 * Retrieve the affiliate's tooltip content.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The affiliate ID.
	 *
	 * @return string The tooltip HTML.
	 */
	public function get_affiliate_card_tooltip( int $affiliate_id ) : string {

		// Affiliate stats.
		$affiliate_paid_earnings    = affwp_get_affiliate_earnings( $affiliate_id, false );
		$affiliate_unpaid_earnings  = affwp_get_affiliate_unpaid_earnings( $affiliate_id, false );
		$affiliate_total_earnings   = $affiliate_paid_earnings + $affiliate_unpaid_earnings;
		$affiliate_paid_referrals   = affwp_count_referrals( $affiliate_id, 'paid' );
		$affiliate_unpaid_referrals = affwp_count_referrals( $affiliate_id, 'unpaid' );
		$affiliate_total_referrals  = $affiliate_paid_referrals + $affiliate_unpaid_referrals;
		$affiliate_visits           = affwp_get_affiliate_visit_count( $affiliate_id ) ?? 0;
		$affiliate_conversion_rate  = affwp_get_affiliate_conversion_rate( $affiliate_id );

		ob_start();

		?>

		<div class="affiliate-tooltip__item">
			<span class="affiliate-tooltip__label">
				<?php esc_html_e( 'Visits', 'affiliatewp-multi-tier-commissions' ); ?>
			</span>
			<span class="affiliate-tooltip__value">
				<?php echo intval( $affiliate_visits ); ?>
			</span>
		</div>

		<div class="affiliate-tooltip__item">
			<span class="affiliate-tooltip__label">
				<?php esc_html_e( 'Total Referrals', 'affiliatewp-multi-tier-commissions' ); ?>
			</span>
			<span class="affiliate-tooltip__value">
				<span>
					<?php echo intval( $affiliate_total_referrals ); ?>
				</span>
				<span class="affiliate-tooltip__subtext">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: Paid referrals, 2: Unpaid referrals. */
							__( '%1$s paid, %2$s unpaid', 'affiliatewp-multi-tier-commissions' ),
							absint( $affiliate_paid_referrals ),
							absint( $affiliate_unpaid_referrals )
						)
					);
					?>
				</span>
			</span>
		</div>

		<div class="affiliate-tooltip__item">
			<span class="affiliate-tooltip__label">
				<?php esc_html_e( 'Conversion Rate', 'affiliatewp-multi-tier-commissions' ); ?>
			</span>
			<span class="affiliate-tooltip__value">
				<?php echo esc_html( $affiliate_conversion_rate ); ?>
			</span>
		</div>

		<div class="affiliate-tooltip__item">
			<span class="affiliate-tooltip__label">
				<?php esc_html_e( 'Total Earnings', 'affiliatewp-multi-tier-commissions' ); ?>
			</span>
			<span class="affiliate-tooltip__value">
				<span>
					<?php echo wp_kses_post( affwp_currency_filter( affwp_format_amount( $affiliate_total_earnings ) ) ); ?>
				</span>
				<span class="affiliate-tooltip__subtext">
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: 1: Paid earnings, 2: Unpaid earnings. */
						__( '%1$s paid, %2$s unpaid', 'affiliatewp-multi-tier-commissions' ),
						affwp_currency_filter( affwp_format_amount( $affiliate_paid_earnings ) ),
						affwp_currency_filter( affwp_format_amount( $affiliate_unpaid_earnings ) )
					)
				);
				?>
				</span>
			</span>
		</div>

		<?php

		return ob_get_clean();
	}

	/**
	 * Helper method to generate the JS necessary to use the tooltips for the network cards.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The affiliate ID to query sub-affiiates.
	 */
	public function render_network_tooltips_js( int $affiliate_id ) : void {

		$affiliate_ids = $this->get_affiliate_flat_list( $affiliate_id );

		$js = '';

		foreach ( $affiliate_ids as $_affiliate_id ) {

			$status = $this->get_card_affiliate_status( $_affiliate_id );

			if ( 'active' !== $status ) {
				continue;
			}

			$card_id          = "affwp-affiliate-card-{$_affiliate_id}";
			$tooltip          = $this->get_affiliate_card_tooltip( $_affiliate_id );
			$tooltip_settings = array(
				'trigger'   => 'mouseenter focus',
				'allowHTML' => true,
				'theme'     => 'affiliate-network',
				'placement' => 'top',
			);

			// phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired -- We want double quotes here.
			$js .= "affiliatewp.tooltip.show('#{$card_id}', `{$tooltip}`, " . wp_json_encode( $tooltip_settings ) . ");";
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content was escaped at this point.
		echo $js;
	}

	/**
	 * Retrieve the Sponsor ID.
	 *
	 * @since 1.4.0
	 *
	 * @param int $affiliate_id The affiliate ID.
	 *
	 * @return int The sponsor ID.
	 */
	public function get_affiliate_sponsor_id( int $affiliate_id ) : int {
		$sponsor_id = affwp_get_affiliate_meta( $affiliate_id, 'sponsor_id', true );

		return intval( empty( $sponsor_id ) ? $affiliate_id : $sponsor_id );
	}

	/**
	 * Retrieve the affiliate card HTML to be used in the network tree.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The affiliate ID to retrieve.
	 * @param int $level The level to be used to render the card. The first one has different adornments.
	 *
	 * @return string The card markup.
	 */
	public function get_affiliate_card_template( int $affiliate_id, int $level = 0 ) : string {

		$card_id    = "affwp-affiliate-card-{$affiliate_id}";
		$status     = $this->get_card_affiliate_status( $affiliate_id );
		$card_name  = $this->get_card_affiliate_name( $affiliate_id );
		$card_image = affwp_get_affiliate_gravatar( $affiliate_id, 40 );

		// Affiliate stats.
		$affiliate_paid_earnings    = affwp_get_affiliate_earnings( $affiliate_id, false );
		$affiliate_unpaid_earnings  = affwp_get_affiliate_unpaid_earnings( $affiliate_id, false );
		$affiliate_total_earnings   = $affiliate_paid_earnings + $affiliate_unpaid_earnings;
		$affiliate_paid_referrals   = affwp_count_referrals( $affiliate_id, 'paid' );
		$affiliate_unpaid_referrals = affwp_count_referrals( $affiliate_id, 'unpaid' );
		$affiliate_total_referrals  = $affiliate_paid_referrals + $affiliate_unpaid_referrals;

		$css_classes = ( $this->get_affiliate_sponsor_id( $affiliate_id ) !== intval( affwp_get_affiliate_id() ) )
			? 'affwp-card affwp-card--compact affwp-card--affiliate affwp-card--indirect-member'
			: 'affwp-card affwp-card--compact affwp-card--affiliate';

		ob_start();

		?>

		<div
			id="<?php echo esc_attr( $card_id ); ?>"
			data-status="<?php echo esc_attr( $status ); ?>"
			class="<?php echo esc_attr( $css_classes ); ?>"
		>
			<div class="affwp-card__info">

				<?php if ( $card_image ) : ?>
				<div class="affwp-card__image">
					<?php echo $card_image; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Image is coming from a safe source. ?>

					<?php if ( 1 === $level ) : ?>
						<span><?php Icons::render( 'top-affiliate' ); ?></span>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<div class="affwp-card__details">
					<p><?php echo wp_kses( 1 === $level ? __( 'You', 'affiliatewp-multi-tier-commissions' ) : $card_name, array( 'span' => array() ) ); ?></p>

					<?php if ( 'pending' === $status || 'inactive' === $status ) : ?>
					<em class="affwp-card__status"><?php echo esc_html( ucwords( $status ) ); ?></em>
					<?php endif; ?>

					<?php if ( 'active' === $status ) : ?>
					<div class="affwp-card__stats">
						<div class="affwp-card__referral-count">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: Referral count. */
									__( 'Referrals <span>%1$s</span> ', 'affiliatewp-multi-tier-commissions' ),
									$affiliate_total_referrals
								),
								affwp_kses()
							);
							?>
						</div>
						<div class="affwp-card__referral-total">
							<?php
							echo wp_kses(
								sprintf(
								/* translators: Referral totals. */
									__( 'Earnings <span>%1$s</span> ', 'affiliatewp-multi-tier-commissions' ),
									affwp_currency_filter( affwp_format_amount( $affiliate_total_earnings ) )
								),
								affwp_kses()
							);
							?>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php

		return ob_get_clean();
	}

	/**
	 * Retrieve the network URL for the current affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id Optional affiliate ID.
	 *
	 * @return string The affiliate's network URL.
	 */
	public function get_invitation_url( int $affiliate_id = 0 ) : string {

		return affwp_get_affiliate_referral_url(
			array_filter(
				array(
					/**
					 * Make possible to change the network link base URL.
					 *
					 * @since 1.1.0
					 *
					 * @param string $affiliate_area_page_url The Affiliate Area URL
					 * @param int    $affiliate_id The affiliate ID.
					 */
					'base_url'     => apply_filters(
						'affiliatewp_mtc_invite_url',
						get_permalink(
							affiliatewp_get_affiliate_registration_page_id()
						),
						$affiliate_id
					),
					'format'       => affwp_get_referral_format(),
					'affiliate_id' => $affiliate_id,
				)
			)
		);
	}

	/**
	 * Connects an affiliate to a referrer.
	 *
	 * This method establishes a connection between an affiliate and a referrer by updating
	 * the affiliate's parent_affiliate_id meta value with the referrer's ID.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Added compatibility with the Forced Matrix compensation plan.
	 *
	 * @param int  $affiliate_id The ID of the affiliate to be connected.
	 * @param int  $referrer_id  The ID of the referrer affiliate.
	 * @param bool $reassign     Whether to allow overwriting the previous parent.
	 *
	 * @return bool True on success, false on failure.
	 *
	 * @throws \Exception Throws an exception if:
	 *   - Attempting to connect an affiliate to itself.
	 *   - The affiliate with the given ID cannot be found.
	 *   - The affiliate was already referred.
	 *   - The referrer affiliate with the given ID cannot be found.
	 *   - The referrer is a direct ancestor of the affiliate.
	 *   - When a Forced Matrix reaches its limits.
	 */
	public function connect_to_referrer( int $affiliate_id, int $referrer_id, bool $reassign = false ) : bool {

		if ( $affiliate_id === $referrer_id ) {
			throw new \Exception( 'Can\'t connect an affiliate to itself.' );
		}

		$affiliate = affwp_get_affiliate( $affiliate_id );

		if ( ! is_a( $affiliate, '\AffWP\Affiliate' ) ) {
			throw new \Exception( sprintf( 'Can\'t find an affiliate with ID %s', $affiliate_id ) );
		}

		if ( false === $reassign && ! empty( affwp_get_affiliate_meta( $affiliate_id, 'parent_affiliate_id' ) ) ) {
			throw new \Exception( 'Affiliate was already referred.' );
		}

		$referrer = affwp_get_affiliate( $referrer_id );

		if ( ! is_a( $referrer, '\AffWP\Affiliate' ) ) {
			throw new \Exception( sprintf( 'Can\'t find the referrer affiliate with ID %s', $referrer_id ) );
		}

		// Get the direct sub affiliates from the affiliate you want to create the connection.
		$sub_affiliates = $this->get_sub_affiliates( $affiliate_id );

		if ( in_array( $referrer_id, $sub_affiliates, true ) ) {
			throw new \Exception( 'Can not assign a direct sub affiliate as a parent.' );
		}

		if ( 'forced_matrix' === affiliate_wp_mtc()->get_compensation_plan() ) {
			$matrix = $this->get_matrix_size();

			$next_referrers_team_member = $this->find_next_available_position(
				$referrer_id,
				$matrix['width'],
				$matrix['depth']
			);

			if ( is_null( $next_referrers_team_member ) ) {
				throw new \Exception( 'Cannot add affiliate due to width or depth limits.' );
			}

			// Save the ID of the affiliate that originally recruited this new affiliate.
			affwp_update_affiliate_meta( $affiliate_id, 'sponsor_id', absint( $referrer_id ) );

			// Because of the spillover, the referrer is the next available affiliate in the referrer's team.
			$referrer_id = $next_referrers_team_member;
		}

		// Create the connection between the affiliates.
		return affwp_update_affiliate_meta( $affiliate_id, 'parent_affiliate_id', absint( $referrer_id ) );
	}

	/**
	 * Finds the next available position for an affiliate considering width and depth limits.
	 *
	 * This method uses a breadth-first search (BFS) to traverse the affiliate tree and find the next
	 * available position where a new affiliate can be placed without exceeding the specified width and depth limits.
	 *
	 * @since 1.4.0
	 *
	 * @param int $parent_affiliate_id ID of the parent affiliate.
	 * @param int $width               Width limit.
	 * @param int $depth               Depth limit.
	 *
	 * @return int|null ID of the next available parent affiliate or null if none found.
	 */
	public function find_next_available_position( int $parent_affiliate_id, int $width, int $depth ) : ?int {
		// Initialize the queue with the starting parent affiliate at depth 1.
		$queue = [
			[
				'affiliate_id' => $parent_affiliate_id,
				'depth'        => 1,
			],
		];

		// Continue processing the queue until it's empty.
		while ( ! empty( $queue ) ) {
			// Dequeue the next affiliate in the queue.
			$current = array_shift( $queue );

			// If the current depth exceeds the maximum allowed depth, skip this branch.
			if ( $current['depth'] > $depth ) {
				continue;
			}

			// Get the list of direct sub-affiliates (width) for the current affiliate.
			$sub_affiliates = $this->get_sub_affiliates( $current['affiliate_id'] );

			// If the number of sub-affiliates is less than the width limit, we found an available position.
			if ( count( $sub_affiliates ) < $width ) {
				return $current['affiliate_id'];
			}

			// Enqueue each sub-affiliate to continue the BFS, ensuring we prioritize affiliates at the current depth.
			foreach ( $sub_affiliates as $sub_affiliate_id ) {
				$queue[] = [
					'affiliate_id' => $sub_affiliate_id,
					'depth'        => $current['depth'] + 1,
				];
			}
		}

		// If no available position is found within the limits, return null.
		return null;
	}

	/**
	 * Retrieves the matrix width and depth.
	 *
	 * @since 1.4.0
	 *
	 * @return array The matrix width and depth.
	 */
	public function get_matrix_size() : array {
		return [
			'width' => intval( affiliate_wp()->settings->get( 'multi_tier_commissions_tier_width', 2 ) ),
			'depth' => intval( affiliate_wp()->settings->get( 'multi_tier_commissions_tier_depth', 2 ) ),
		];
	}

	/**
	 * Display notices regarding the recruitment status.
	 *
	 * Will show an engaging message when the network starts to spillover or a congratulations message when complete.
	 *
	 * @since 1.4.0
	 * @since 1.4.2 Added a condition to check if it is not Unilevel before returning the message.
	 *
	 * @param int|false $affiliate_id The current affiliate ID.
	 *
	 * @return string The notice.
	 */
	public function get_forced_matrix_notices( $affiliate_id ) : string {
		if ( affiliate_wp_mtc()->get_compensation_plan() === 'unilevel' ) {
			return '';
		}

		$affiliate_id = intval( $affiliate_id );
		$matrix       = affiliate_wp_mtc()->network->get_matrix_size();

		$next_affiliate_id = $this->find_next_available_position(
			$affiliate_id,
			$matrix['width'],
			$matrix['depth']
		);

		if ( is_null( $next_affiliate_id ) ) {
			return sprintf(
				'<p class="affwp-notice">%s</p>',
				esc_html__( 'Great job! Your network is fully complete. Focus on supporting your network to maximize earnings.', 'affiliatewp-multi-tier-commissions' )
			);
		}

		if ( $next_affiliate_id !== $affiliate_id ) {
			return sprintf(
				'<p class="affwp-notice">%s</p>',
				esc_html__( 'Great job! You have reached your network\'s limit. New recruits will now be assigned to your downline affiliates.', 'affiliatewp-multi-tier-commissions' )
			);
		}

		return '';
	}
}

// Make Network object public available.
affiliate_wp_mtc()->network = new Network();
