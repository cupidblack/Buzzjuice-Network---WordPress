<?php
/**
 * Plugin Name: AffiliateWP - Multi-Tier Commissions
 * Plugin URI: https://affiliatewp.com/addons/multi-tier-commissions/
 * Description: Enables your affiliates to grow their networks and boost earnings through recruit sales, improving overall sales and benefiting both your business and your affiliates.
 * Author: AffiliateWP
 * Author URI: https://affiliatewp.com/
 * Version: 1.4.4
 * Text Domain: affiliatewp-multi-tier-commissions
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
 * @package AffiliateWP Multi-Tier Commissions
 * @category Core
 * @author Darvin da Silveira
 * @version 1.4.3
 */

namespace AffiliateWP\MTC;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
class Requirements_Check extends \AffiliateWP_Requirements_Check_v1_1 {

	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $slug = 'affiliatewp-multi-tier-commissions';

	/**
	 * Add-on requirements.
	 *
	 * @since 1.0.0
	 * @var   array[]
	 */
	protected $addon_requirements = array(
		// AffiliateWP.
		'affwp' => array(
			'minimum' => '2.25.3',
			'name'    => 'AffiliateWP',
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

			// AffiliateWP activation.
			if ( ! class_exists( 'Affiliate_WP' ) ) {
				$activation = new \AffiliateWP_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
				$activation->run();
			}
		} else {
			Multi_Tier_Commissions::instance( __FILE__ );
		}
	}

	/**
	 * Loads the add-on.
	 *
	 * @since 1.0.0
	 */
	protected function load() {

		// Maybe include the bundled bootstrapper.
		if ( ! class_exists( 'AffiliateWP\MTC\Multi_Tier_Commissions' ) ) {
			require_once dirname( __FILE__ ) . '/includes/class-multi-tier-commissions.php';
		}

		// Maybe hook-in the bootstrapper.
		if ( class_exists( 'AffiliateWP\MTC\Multi_Tier_Commissions' ) ) {

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
	 * @since 1.0.0
	 */
	public function install() {

		// Bootstrap to include all the necessary files.
		$this->bootstrap();

		if ( defined( 'AFFWP_MTC_VERSION' ) ) {
			update_option( 'affwp_mtc_version', AFFWP_MTC_VERSION );
		}
	}

	/**
	 * Plugin-specific aria label text to describe the requirements link.
	 *
	 * @since 1.0.0
	 *
	 * @return string Aria label text.
	 */
	protected function unmet_requirements_label() : string {
		return esc_html__( 'AffiliateWP - Multi-Tier Commissions Requirements', 'affiliatewp-multi-tier-commissions' );
	}

	/**
	 * Plugin-specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 1.0.0
	 *
	 * @return string CSS selector.
	 */
	protected function unmet_requirements_name() : string {
		return 'affiliatewp-multi-tier-commissions-requirements';
	}

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unmet requirements URL.
	 */
	protected function unmet_requirements_url() : string {
		return 'https://affiliatewp.com/docs/minimum-requirements-roadmap/';
	}

}

$requirements = new Requirements_Check( __FILE__ );

$requirements->maybe_load();
