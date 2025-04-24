<?php
/**
 * Plugin Name: AffiliateWP - Lifetime Commissions
 * Plugin URI: http://affiliatewp.com/addons/lifetime-commissions/
 * Description: Allow your affiliates to receive a commission on all future purchases by the customer
 * Author: Sandhills Development, LLC
 * Author URI: https://sandhillsdev.com
 * Version: 1.6.2
 * Text Domain: affiliate-wp-lifetime-commissions
 * Domain Path: languages
 *
 * AffiliateWP is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * AffiliateWP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AffiliateWP. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package AffiliateWP Lifetime Commissions
 * @category Core
 * @author Andrew Munro
 * @version 1.6.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AffiliateWP_Requirements_Check_v1_1' ) ) {
	require_once dirname( __FILE__ ) . '/includes/lib/affwp/class-affiliatewp-requirements-check-v1-1.php';
}

/**
 * Class used to check requirements for and bootstrap the plugin.
 *
 * @since 1.5
 *
 * @see Affiliate_WP_Requirements_Check
 */
class AffiliateWP_LC_Requirements_Check extends AffiliateWP_Requirements_Check_v1_1 {

	/**
	 * Plugin slug.
	 *
	 * @since 1.5
	 * @var   string
	 */
	protected $slug = 'affiliate-wp-lifetime-commissions';

	/**
	 * Add-on requirements.
	 *
	 * @since 1.5
	 * @var   array[]
	 */
	protected $addon_requirements = array(
		// AffiliateWP.
		'affwp' => array(
			'minimum' => '2.6.1',
			'name'    => 'AffiliateWP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		),
	);

	/**
	 * Bootstrap everything.
	 *
	 * @since 1.5
	 */
	public function bootstrap() {
		if ( ! class_exists( 'Affiliate_WP' ) ) {

			if ( ! class_exists( 'AffiliateWP_Activation' ) ) {
				require_once 'includes/lib/affwp/class-affiliatewp-activation.php';
			}

			// AffiliateWP activation
			if ( ! class_exists( 'Affiliate_WP' ) ) {
				$activation = new AffiliateWP_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
				$activation = $activation->run();
			}
		} else {
			\AffiliateWP_Lifetime_Commissions::instance( __FILE__ );
		}
	}

	/**
	 * Loads the add-on.
	 *
	 * @since 1.5
	 */
	protected function load() {
		// Maybe include the bundled bootstrapper.
		if ( ! class_exists( 'AffiliateWP_Lifetime_Commissions' ) ) {
			require_once dirname( __FILE__ ) . '/includes/class-affiliatewp-lifetime-commissions.php';
		}

		// Maybe hook-in the bootstrapper.
		if ( class_exists( 'AffiliateWP_Lifetime_Commissions' ) ) {

			$affwp_version = get_option( 'affwp_version' );

			if ( version_compare( $affwp_version, '2.7', '<' ) ) {
				add_action( 'plugins_loaded', array( $this, 'bootstrap' ), 100 );
			} else {
				add_action( 'affwp_plugins_loaded', array( $this, 'bootstrap' ), 100 );
			}

			// Register the activation hook.
			register_activation_hook( __FILE__, array( $this, 'install' ) );
		}
	}

	/**
	 * Install, usually on an activation hook.
	 *
	 * @since 1.5
	 */
	public function install() {
		// Bootstrap to include all of the necessary files
		$this->bootstrap();

		affiliate_wp_lifetime_commissions()->lifetime_customers->create_table();

		if ( true === version_compare( AFFILIATEWP_VERSION, '2.0.2', '>=' ) ) {
			@affiliate_wp()->utils->log( 'Upgrade: Lifetime Commissions: The lifetime customers table has been created.' );
		}

		$affiliate_wp_settings = affiliate_wp()->settings->get_all();

		if ( ! isset( $affiliate_wp_settings['lifetime_commissions'] ) ) {
			update_option( 'affwp_lc_is_installed', '1' );
			update_option( 'affwp_lc_migrate_customers', '1' );
			update_option( 'affwp_lc_migrate_meta', '1' );
		}

		if ( defined( 'AFFWP_LC_VERSION' ) ) {
			update_option( 'affwp_lc_version', AFFWP_LC_VERSION );
		}
	}

	/**
	 * Plugin-specific aria label text to describe the requirements link.
	 *
	 * @since 1.5
	 *
	 * @return string Aria label text.
	 */
	protected function unmet_requirements_label() {
		return esc_html__( 'AffiliateWP - Lifetime Commissions Requirements', 'affiliate-wp-lifetime-commissions' );
	}

	/**
	 * Plugin-specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 1.5
	 *
	 * @return string CSS selector.
	 */
	protected function unmet_requirements_name() {
		return 'affiliate-wp-lifetime-commissions-requirements';
	}

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 1.5
	 *
	 * @return string Unmet requirements URL.
	 */
	protected function unmet_requirements_url() {
		return 'https://docs.affiliatewp.com/article/2361-minimum-requirements-roadmaps';
	}

}

$requirements = new AffiliateWP_LC_Requirements_Check( __FILE__ );

$requirements->maybe_load();
