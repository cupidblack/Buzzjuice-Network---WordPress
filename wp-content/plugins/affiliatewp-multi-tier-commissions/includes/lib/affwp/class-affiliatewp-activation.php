<?php
/**
 * AffiliateWP Add-on Activation Handler
 *
 * For use by AffiliateWP and its add-ons.
 *
 * @package     AffiliateWP
 * @subpackage  Tools
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version     1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AffiliateWP Activation Handler Class
 *
 * @since 1.0.0
 */
class AffiliateWP_Activation {

	/**
	 * Plugin name.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $plugin_name;

	/**
	 * Main plugin file path.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $plugin_path;

	/**
	 * Main plugin filename.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $plugin_file;

	/**
	 * Whether AffiliateWP is installed.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	public $has_affiliatewp;

	/**
	 * Sets up the activation class.
	 *
	 * @param string $plugin_path Main add-on plugin file.
	 * @param string $plugin_file Main add-on plugin file path.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $plugin_path, $plugin_file ) {

		// We need plugin.php!
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$plugins = get_plugins();

		// Set plugin directory.
		$plugin_path       = array_filter( explode( '/', $plugin_path ) );
		$this->plugin_path = end( $plugin_path );

		// Set plugin file.
		$this->plugin_file = $plugin_file;

		// Set plugin name.
		if ( isset( $plugins[ $this->plugin_path . '/' . $this->plugin_file ]['Name'] ) ) {
			$this->plugin_name = $plugins[ $this->plugin_path . '/' . $this->plugin_file ]['Name'];
		} else {
			$this->plugin_name = __( 'This plugin', 'affiliatewp-multi-tier-commissions' );
		}

		// Is AffiliateWP installed?
		foreach ( $plugins as $plugin_path => $plugin ) {

			if ( 'AffiliateWP' === $plugin['Name'] ) {
				$this->has_affiliatewp = true;
				break;
			}
		}
	}

	/**
	 * Displays the missing AffiliateWP notice.
	 *
	 * @since 1.0.0
	 */
	public function run() {

		// Display notice.
		add_action( 'admin_notices', array( $this, 'missing_affiliatewp_notice' ) );
	}

	/**
	 * Displays a notice if AffiliateWP isn't installed.
	 *
	 * @since 1.0.0
	 */
	public function missing_affiliatewp_notice() {

		/* translators: 1: AffiliateWP plugin name */
		$affiliatewp_link = sprintf(
			'<a href="https://affiliatewp.com/" title="%1$s" target="_blank">%1$s</a>',
			esc_html__( 'AffiliateWP', 'affiliatewp-multi-tier-commissions' )
		);

		$notice_message = sprintf(
			/* translators: 1: AffiliateWP link, 2: Action verb */
			esc_html__( 'AffiliateWP Multi-Tier Commissions requires %1$s. Please %2$s to continue.', 'affiliatewp-multi-tier-commissions' ),
			$affiliatewp_link,
			$this->has_affiliatewp ? esc_html__( 'activate it', 'affiliatewp-multi-tier-commissions' ) : esc_html__( 'install it', 'affiliatewp-multi-tier-commissions' )
		);

		echo '<div class="error"><p>' . esc_html( $notice_message ) . '</p></div>';
	}
}
