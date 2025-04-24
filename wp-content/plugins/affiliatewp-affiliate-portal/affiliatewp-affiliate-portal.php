<?php
/**
 * Plugin Name: AffiliateWP - Affiliate Portal
 * Plugin URI: https://affiliatewp.com/add-ons/pro/affiliate-portal/
 * Description: Provides an optimized experience for affiliates via a custom portal user interface
 * Author: AffiliateWP
 * Author URI: https://affiliatewp.com/
 * Version: 1.3.7
 * Text Domain: affiliatewp-affiliate-portal
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
 * @package AffiliateWP - Affiliate Portal
 */

if ( ! class_exists( 'AffiliateWP_Requirements_Check_v1_1' ) ) {
	require_once dirname( __FILE__ ) . '/includes/lib/affwp/class-affiliatewp-requirements-check-v1-1.php';
}

/**
 * Class used to check requirements for and bootstrap the plugin.
 *
 * @since 1.0.0
 *
 * @see Affiliate_WP_Requirements_Check
 */
class AffiliateWP_Affiliate_Portal_Requirements_Check extends AffiliateWP_Requirements_Check_v1_1 {

	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $slug = 'affiliatewp-affiliate-portal';

	/**
	 * Add-on requirements.
	 *
	 * @since 1.0.0
	 * @var   array[]
	 */
	protected $addon_requirements = array(

		// AffiliateWP: Creative categories pagination fixes in 2.13.0.
		'affwp' => array(
			'minimum' => '2.13.0',
			'name'    => 'AffiliateWP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false,
		),

		// AffiliateWP moved to PHP 7.4 in January 2023.
		'php'   => array(
			'minimum' => '7.4',
			'name'    => 'PHP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false,
		),
	);

	/**
	 * Bootstrap everything.
	 *
	 * @since 1.0.0
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
			\AffiliateWP_Affiliate_Portal::instance( __FILE__ );
		}
	}

	/**
	 * Tell the rewrite registry to flush permalinks.
	 *
	 * When we activate the plugin we use this option to tell the rewrite
	 * registry to flush rewrite rules when we re-add our rewrite rules.
	 *
	 * Also when the system does a flush on it's own (which the portal, because of the
	 * way it loads, may not get the rewrite rules updated in time), instruct the rewrite
	 * registry to flush rewrite rules.
	 *
	 * @since 1.3.6
	 *
	 * @see https://github.com/awesomemotive/affiliate-wp/issues/5335
	 */
	public function flush_permalinks_after_register_rewrites() : void {
		update_option( 'affiliatewp_portal_flush_rewrite_rules_after_register_rewrites', 'flush_rewrites', true );
	}

	/**
	 * Loads the add-on.
	 *
	 * @since 1.0.0
	 * @since 1.3.6 Added activation hook to tell rewrite registry to flush permalinks
	 *               on activation, see https://github.com/awesomemotive/affiliate-wp/issues/5335.
	 */
	protected function load() {

		// Flush rewrite rules on activation and when the system does a permalinks flush.
		add_filter( 'flush_rewrite_rules_hard', [ $this, 'flush_permalinks_after_register_rewrites' ] );
		register_activation_hook( __FILE__, [ $this, 'flush_permalinks_after_register_rewrites' ] );

		// Maybe include the bundled bootstrapper.
		if ( ! class_exists( 'AffiliateWP_Affiliate_Portal' ) ) {
			require_once dirname( __FILE__ ) . '/includes/class-affiliatewp-affiliate-portal.php';
		}

		// Maybe hook-in the bootstrapper.
		if ( class_exists( 'AffiliateWP_Affiliate_Portal' ) ) {

			// Bootstrap to plugins_loaded.
			$affwp_version = get_option( 'affwp_version' );

			if ( version_compare( $affwp_version, '2.7', '<' ) ) {
				add_action( 'plugins_loaded', array( $this, 'bootstrap' ), 101 );
			} else {
				add_action( 'affwp_plugins_loaded', array( $this, 'bootstrap' ), 101 );
			}


			// Register the activation hook.
			register_activation_hook( __FILE__, array( $this, 'install' ) );
		}
	}

	/**
	 * Install, usually on an activation hook.
	 *
	 * @since 1.0.0
	 */
	public function install() {
		// Bootstrap to include all of the necessary files
		$this->bootstrap();

		if ( defined( 'AFFWP_PORTAL_VERSION' ) ) {
			update_option( 'affwp_ap_version', AFFWP_PORTAL_VERSION );
		}
	}

	/**
	 * Plugin-specific aria label text to describe the requirements link.
	 *
	 * @since 1.0.0
	 *
	 * @return string Aria label text.
	 */
	protected function unmet_requirements_label() {
		return esc_html__( 'AffiliateWP - Affiliate Portal Requirements', 'affiliatewp-affiliate-portal' );
	}

	/**
	 * Plugin-specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 1.0.0
	 *
	 * @return string CSS selector.
	 */
	protected function unmet_requirements_name() {
		return 'affiliatewp-affiliate-portal-requirements';
	}

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unmet requirements URL.
	 */
	protected function unmet_requirements_url() {
		return 'https://docs.affiliatewp.com/article/2361-minimum-requirements-roadmaps';
	}

}

$requirements = new AffiliateWP_Affiliate_Portal_Requirements_Check( __FILE__ );

$requirements->maybe_load();
