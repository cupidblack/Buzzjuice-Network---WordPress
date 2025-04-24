<?php
/**
 * Multi Tier Commissions Admin Settings File
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
 * Handles rendering the Multi-Tier Commissions settings screen.
 *
 * @since 1.0
 */
class Settings {

	/**
	 * The default rate type.
	 *
	 * @since 1.2.0
	 */
	const DEFAULT_RATE_TYPE = 'percentage';

	/**
	 * Stores the site currency.
	 *
	 * @var string
	 */
	private string $site_currency;

	/**
	 * Construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Cache the site currency.
		$this->site_currency = strtoupper( affwp_get_currency() );

		// Use the new section system.
		add_filter( 'affwp_settings_commissions', array( $this, 'register_settings' ) );
		add_filter( 'affiliatewp_register_section_addon_multi_tier_commissions', array( $this, 'update_section_settings' ) );

		// Adds additional data to the mtc object.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Updates the mtc object in our JS namespace with additional data to render the Tiers repeater component.
	 *
	 * @since 1.0.0
	 * @since 1.4.4 Updated some of the language to include periods as the rest of the settings do.
	 */
	public function enqueue_assets() {

		$data = wp_json_encode(
			array(
				'maxReferralTiers' => affiliate_wp_mtc()->get_max_tiers(),
				'tiersRowHtml'     => $this->render_template(
					$this->get_tier_row_html(),
					[
						'flat_type_options' => $this->render_rate_type_options( self::DEFAULT_RATE_TYPE ),
					]
				),
			)
		);

		wp_add_inline_script( 'affiliatewp-mtc-admin', "affiliatewp.parseArgs( {$data}, affiliatewp.mtc.data );", 'after' );
	}

	/**
	 * Return the list of settings.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Added Forced Matrix settings.
	 * @since 1.4.4 Updated some string to use periods to be consistent, see
	 *              https://github.com/awesomemotive/affiliate-wp/pull/5358.
	 *
	 * @return array
	 */
	private function get_settings() : array {

		return array(
			'multi_tier_commissions_tier_compensation_plan' => [
				'name'       => __( 'Compensation Plan', 'affiliatewp-multi-tier-commissions' ),
				'type'       => 'radio',
				'options'    => [
					'unilevel'      => __( 'Unilevel', 'affiliatewp-multi-tier-commissions' ),
					'forced_matrix' => __( 'Forced Matrix', 'affiliatewp-multi-tier-commissions' ),
				],
				'std'        => 'unilevel',
				'visibility' => [
					'required_field' => 'multi_tier_commissions',
					'value'          => true,
				],
				'tooltip'    => sprintf(
					'<p><strong>%1$s</strong> %2$s</p><p><strong>%3$s</strong> %4$s</p><a href="%5$s" target="_blank" rel="noopener">%6$s %7$s</a>',
					esc_html__( 'Unilevel:', 'affiliatewp-multi-tier-commissions' ),
					esc_html__( 'Allows unlimited direct recruits per affiliate, offering flexibility in network width and depth.', 'affiliatewp-multi-tier-commissions' ),
					esc_html__( 'Forced Matrix:', 'affiliatewp-multi-tier-commissions' ),
					esc_html__( 'Limits the number of direct recruits each affiliate can have, creating a fixed structure that promotes balanced team growth.', 'affiliatewp-multi-tier-commissions' ),
					esc_url( 'https://affiliatewp.com/docs/multi-tier-commissions-guide/#compensation-plans' ),
					esc_html__( 'Learn More', 'affiliatewp-multi-tier-commissions' ),
					sprintf(
						'<span class="screen-reader-text"> %s</span><span aria-hidden="true" class="dashicons dashicons-external"></span>',
						/* translators: Hidden accessibility text. */
						__( '(opens in a new tab)', 'affiliatewp-multi-tier-commissions' )
					)
				),
			],
			'multi_tier_commissions_tier_width' => [
				'name'       => __( 'Width', 'affiliatewp-multi-tier-commissions' ),
				'type'       => 'select',
				'options'    => ( function() {
					$range = range( 2, 10 );

					return array_combine( $range, $range );
				} )(),
				'visibility' => [
					[
						'required_field' => 'multi_tier_commissions_tier_compensation_plan',
						'value'          => 'forced_matrix',
					],
					[
						'required_field' => 'multi_tier_commissions',
						'value'          => true,
					]
				],
				'select2'    => [
					'width' => '65px',
				],
				'desc'       => esc_html__( 'Maximum number of direct affiliates each affiliate can have in the forced matrix structure.', 'affiliatewp-multi-tier-commissions' ),
			],
			'multi_tier_commissions_tier_depth' => [
				'name'       => __( 'Depth', 'affiliatewp-multi-tier-commissions' ),
				'type'       => 'select',
				'options'    => ( function() {
					$range = range( 2, 12 );

					return array_combine( $range, $range );
				} )(),
				'visibility' => [
					[
						'required_field' => 'multi_tier_commissions_tier_compensation_plan',
						'value'          => 'forced_matrix',
					],
					[
						'required_field' => 'multi_tier_commissions',
						'value'          => true,
					]
				],
				'select2'    => [
					'width' => '65px',
				],
				'desc'       => esc_html__( 'Maximum depth of the forced matrix structure.', 'affiliatewp-multi-tier-commissions' ),
			],
			'tiers'                             => array(
				'name'       => __( 'Tiers', 'affiliatewp-multi-tier-commissions' ),
				'desc'       => '',
				'type'       => '',
				'callback'   => array( $this, 'tiers' ),
				'visibility' => array(
					'required_field' => 'multi_tier_commissions',
					'value'          => true,
				),
				'tooltip'    => sprintf(
					'<p>%1$s</p>%2$s',
					esc_html__( 'Define commission rates for each tier. Tier 1 affiliates earn commissions on their own sales. Affiliates in Tiers 2 and above earn from sales made by their directly recruited affiliates. Choose either a percentage or a flat rate for each tier\'s commission.', 'affiliatewp-multi-tier-commissions' ),
					sprintf(
						/* translators: 1: Link to the doc page on tiers. 2: Additional link attributes. 3: Accessibility text. */
						__( '<a href="%1$s" %2$s>Learn more about tiers%3$s</a>', 'affiliatewp-multi-tier-commissions' ),
						esc_url( 'https://affiliatewp.com/docs/multi-tier-commissions-guide/#setting-up-and-managing-affiliate-tiers' ),
						'target="_blank" rel="noopener"',
						sprintf(
							'<span class="screen-reader-text"> %s</span><span aria-hidden="true" class="dashicons dashicons-external"></span>',
							/* translators: Hidden accessibility text. */
							esc_html__( '(opens in a new tab)', 'affiliatewp-multi-tier-commissions' )
						)
					)
				),
			),
			'multi_tier_commissions_tier_calculation_method' => array(
				'name'             => __( 'Tier Calculation Method', 'affiliatewp-multi-tier-commissions' ),
				'type'             => 'radio',
				'options'          => array(
					'tier_based_commission' => __( "Calculate subsequent tiers' commissions based on tier 1's earnings.", 'affiliatewp-multi-tier-commissions' ),
					'sale_based_commission' => __( "Calculate all tiers' commissions based on the total sale amount.", 'affiliatewp-multi-tier-commissions' ),
				),
				'std'              => 'tier_based_commission',
				'visibility'       => array(
					'required_field' => 'multi_tier_commissions',
					'value'          => true,
				),
				'options_tooltips' => array(
					'tier_based_commission' => __( 'This method calculates commissions for Tiers 2 and above as a percentage of what Tier 1 earns. Applicable only when tiers are set to Percentage (%).', 'affiliatewp-multi-tier-commissions' ),
					'sale_based_commission' => __( 'This method calculates commissions for each tier directly from the total sale amount, independent of other tiers\' earnings. Use this when tiers are set to Percentage (%).', 'affiliatewp-multi-tier-commissions' ),
				),
			),
		);
	}


	/**
	 * Returns an updated array of settings to use in the MTC section.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings The array of settings.
	 *
	 * @return array
	 */
	public function update_section_settings( array $settings ) : array {

		return array_merge(
			$settings,
			array_keys( $this->get_settings() )
		);
	}

	/**
	 * Register the settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings The array of settings.
	 *
	 * @return array
	 */
	public function register_settings( array $settings ) : array {

		return array_merge_recursive(
			$settings,
			$this->get_settings()
		);
	}

	/**
	 * Retrieve the key/pair values to interact with dropdown options.
	 *
	 * @since 1.2.0
	 *
	 * @return array The key/pair values to interact as dropdown options.
	 */
	private function get_tier_rate_types() : array {
		return [
			'percentage'       => __( 'Percentage (%)', 'affiliatewp-multi-tier-commissions' ),
			'flat_per_product' => sprintf(
				/* translators: the currency. */
				__( 'Flat %s Per Product', 'affiliatewp-multi-tier-commissions' ),
				$this->site_currency
			),
			'flat_per_order'   => sprintf(
				/* translators: the currency. */
				__( 'Flat %s Per Order', 'affiliatewp-multi-tier-commissions' ),
				$this->site_currency
			),
		];
	}

	/**
	 * Generates the HTML to render a row for the repeatable tiers component.
	 *
	 * @since 1.0.0
	 *
	 * @return string The template to use with the tiers.
	 */
	private function get_tier_row_html() : string {

		ob_start();

		?>

		<div class="affwp-tier-row">
			<label for="affwp_settings[tiers][{{index}}][rate]" class="affwp-tier-label"></label>

			<div>
				<input
					id="affwp_settings[tiers][{{index}}][rate]"
					name="affwp_settings[tiers][{{index}}][rate]"
					type="number"
					required
					min="0"
					max="999999999"
					step="0.01"
					value="{{rate}}"
					class="small-text"
				>
			</div>

			<div>
				<select
					name="affwp_settings[tiers][{{index}}][rate_type]"
					class="small-text"
					data-select2-settings="<?php echo esc_attr( wp_json_encode( [ 'width' => '170px' ] ) ); ?>"
				>{{flat_type_options}}</select>
			</div>

			<a
				href="#"
				class="affwp-remove-tier"
				title="<?php esc_html_e( 'Remove Tier', 'affiliatewp-multi-tier-commissions' ); ?>"
				style="display:none"
			>
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
					<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
				</svg>
			</a>
		</div>

		<?php

		return ob_get_clean();
	}

	/**
	 * Returns HTML template string replacing {{var}} with the data provided.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template The HTML template.
	 * @param array  $data Key pair values to replace within the template.
	 *
	 * @return string The template with strings replaced.
	 */
	private function render_template( string $template, array $data = array() ) : string {
		if ( empty( $data ) ) {
			return $template;
		}

		foreach ( $data as $key => $value ) {
			$template = str_replace( "{{{$key}}}", $value, $template );
		}

		return $template;
	}

	/**
	 * Render the rate type dropdown options.
	 *
	 * @since 1.2.0
	 *
	 * @param string $current The current option.
	 *
	 * @return string Options HTML.
	 */
	private function render_rate_type_options( string $current ) : string {

		ob_start();

		foreach ( $this->get_tier_rate_types() as $option => $label ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $option ),
				selected( $option, $current, false ),
				esc_html( $label )
			);
		}

		return ob_get_clean();
	}

	/**
	 * Render the tiers repeatable divs.
	 *
	 * @since 1.0.0
	 */
	public function tiers() {

		$tiers    = affiliate_wp_mtc()->network->get_tiers_rates();
		$row_html = $this->get_tier_row_html();

		?>

		<script>
			document.addEventListener( 'DOMContentLoaded', function() {

				if ( ! affiliatewp.has( 'mtc' ) ) {

					console.error( 'Missing MTC scripts.' );
					return;
				}

				affiliatewp.mtc.initTiersRepeater();
			} );
		</script>

		<form id="affwp-tiers-form">

			<div id="affwp-tiers">
				<div class="affwp-grid-header">
					<?php esc_html_e( 'Tier', 'affiliatewp-multi-tier-commissions' ); ?>
				</div>
				<div class="affwp-grid-header">
					<?php esc_html_e( 'Commission', 'affiliatewp-multi-tier-commissions' ); ?>
				</div>

				<div id="affwp-tier-rows">

				<?php foreach ( $tiers as $key => $tier ) : ?>

					<?php

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is escaped at this point.
					echo $this->render_template(
						$row_html,
						array(
							'index'             => esc_html( $key ),
							'rate'              => esc_html( $tier['rate'] ),
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is escaped at this point.
							'flat_type_options' => $this->render_rate_type_options( $tier['rate_type'] ?? self::DEFAULT_RATE_TYPE ),
						)
					);

					?>

				<?php endforeach; ?>

				</div>
			</div>

			<button
				id="affwp-new-tier"
				name="affwp-new-tier"
				class="button"
				<?php

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is escaped at this point.
				echo affiliatewp_tag_attr(
					'disabled',
					count( $tiers ) >= affiliate_wp_mtc()->get_max_tiers()
						? 'disabled'
						: ''
				);

				?>
			><?php esc_html_e( 'Add Tier', 'affiliatewp-multi-tier-commissions' ); ?></button>

		</form>

		<?php
	}
}

new Settings();
