<?php
/**
 * Components: Assets Loader
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core/Components
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use AffiliateWP_Affiliate_Portal\Core\Views_Registry;

#[\AllowDynamicProperties]

/**
 * Manages loading of various script and style assets and other needed build tools.
 *
 * @since 1.0.0
 */
class Assets {

	/**
	 * View script handles and files.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $view_scripts = array();

	/**
	 * Sets up the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( is_admin() ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_styles'  ) );

		} else {

			if ( ! ( affwp_is_affiliate() && affwp_is_active_affiliate() ) ) {
				return;
			}

			$this->view_scripts = array(
				/*
				 * 'affwp-portal-vendor' is registered separately inside load_scripts(),
				 * but included here to ensure external consuming functions receive it.
				 */
				'affwp-portal-vendor'    => 'vendor.js',
				'url-generator'          => 'urlGenerator.js',
				'affwp-portal-table'     => 'table.js',
				'affwp-portal-form'      => 'form.js',
				'affwp-portal-creatives' => 'creatives.js',
				'affwp-portal-chart'     => 'chart.js',
				'sharing-links'          => 'sharingLinks.js',
			);

			// Do not enqueue affiliate area scripts on portal pages.
			if ( affwp_is_affiliate_portal() ) {
				remove_action( 'wp_enqueue_scripts', 'affwp_frontend_scripts_and_styles' );
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_styles' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'deregister_theme_styles' ),  9999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'deregister_theme_scripts' ), 9999 );
		}
	}

	/**
	 * Retrieves the list of view script handles and filenames.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of view script handles and filenames as key/value pairs.
	 */
	public function get_view_scripts() {
		return $this->view_scripts;
	}

	/**
	 * Retrieves generated asset file from webpack. Includes sane fallbacks to prevent errors and warnings if build files
	 * do not exist.
	 *
	 * @param string $package The package name.
	 *
	 * @return array|object|string
	 */
	private function get_assets( $package ) {
		$dir = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'dev' : 'prod';

		$asset_file = sprintf(
			'%s/build/%s/%s.asset.php',
			untrailingslashit( AFFWP_PORTAL_PLUGIN_DIR ),
			$dir,
			$package
		);

		if ( file_exists( $asset_file ) ) {
			$assets = (array) require( $asset_file );
		} else {
			$assets = array();
		}

		return wp_parse_args( $assets, array( 'dependencies' => array(), 'version' => '' ) );
	}

	/**
	 * Loads the frontend scripts and styles.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_scripts() {

		global $post;

		if ( ! is_object( $post ) ) {
			return;
		}

		$controls_registry = Controls_Registry::instance();

		$style_deps  = array();

		$dir = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'dev' : 'prod';

		$lang_dir = AFFWP_PORTAL_PLUGIN_DIR . 'languages';

		wp_register_style( 'affwp-forms', AFFILIATEWP_PLUGIN_URL . 'assets/css/forms.min.css', $style_deps, AFFILIATEWP_VERSION );
		wp_register_style( 'jquery-ui-css', AFFILIATEWP_PLUGIN_URL . 'assets/css/jquery-ui-fresh.min.css' );

		wp_register_script( 'affwp-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), AFFILIATEWP_VERSION );

		$packages = array(
			'alpine-chart',
			'alpine-form',
			'alpine-table',
			'cas-settings',
			'chart',
			// 'clipboard-helpers',
			'core',
			'creatives',
			// 'date-helpers',
			'direct-link',
			// 'dom-helpers',
			'form',
			// 'helpers',
			'portal-form',
			'sdk',
			'sharing-links',
			'storage',
			'table',
			'toggle',
			'url-generator',
			// 'url-helpers'
		);

		foreach ( $packages as $package ) {
			$asset_file = $this->get_assets( $package );

			wp_register_script(
				'affwp-portal-' . $package,
				sprintf(
					'%s/build/%s/%s.js',
					untrailingslashit( AFFWP_PORTAL_PLUGIN_URL ),
					$dir,
					$package
				),
				$asset_file['dependencies'],
				$asset_file['version']
			);

			wp_set_script_translations(
				'affwp-portal-' . $package,
				'affiliatewp-affiliate-portal',
				$lang_dir
			);
		}

		wp_localize_script( 'affwp-portal-core', 'affwp_portal_vars', array(
			'rest_url'     => get_rest_url(),
			'affiliate_id' => affwp_get_affiliate_id(),
			'nonce'        => wp_create_nonce( 'wp_rest' ),
		) );

		if ( affwp_is_affiliate_portal() || affwp_is_affiliate_area() ) {
			affwp_enqueue_script( 'affwp-portal-core', 'affiliate_portal' );

			// Setup preload middleware.
			$this->preload_endpoints();
		}

		if ( affwp_is_affiliate_portal( 'urls' ) ) {
			affwp_enqueue_script( 'affwp-portal-url-generator' );
			affwp_enqueue_script( 'affwp-portal-sharing-links' );
		}

		if ( affwp_is_affiliate_portal( 'graphs' ) ) {
			affwp_enqueue_script( 'affwp-portal-chart' );
		}

		if ( affwp_is_affiliate_portal( 'creatives' ) ) {
			affwp_enqueue_script( 'affwp-portal-creatives' );
		}

		$view_table_controls = $controls_registry->query( array(
			'view_id' => Portal::get_current_view_slug(),
			'type'    => 'table',
		) );

		// Enqueue the Table JS on any view with a registered table control.
		if ( ! empty( $view_table_controls ) ) {
			affwp_enqueue_script( 'affwp-portal-table' );
		}

		$view_form_controls = $controls_registry->query( array(
			'view_id'     => Portal::get_current_view_slug(),
			'formControl' => 1,
		) );

		// Enqueue the Form JS on any view with a registered form control.
		if ( ! empty( $view_form_controls ) ) {
			affwp_enqueue_script( 'affwp-portal-form' );
		}
	}

	/**
	 * Preloads REST endpoints specified inside controls, views, and sections.
	 *
	 * @since 1.0.4
	 */
	public function preload_endpoints() {
		$controls_registry = Controls_Registry::instance();
		$preload_routes    = array();

		$views_registry = Views_Registry::instance();
		$view           = $views_registry->get( Portal::get_current_view_slug() );

		// Preload views.
		if ( ! empty( $view['preload_routes'] ) ) {
			$preload_routes = array_merge( $preload_routes, (array) $view['preload_routes'] );
		}

		// Preload sections.
		$preload_sections = isset( $view['sections'] ) ? $view['sections'] : array();

		foreach ( $preload_sections as $section ) {
			if ( ! empty( $section['preload_routes'] ) ) {
				$preload_routes = array_merge( $preload_routes, (array) $section['preload_routes'] );
			}
		}

		// Preload controls.
		$preload_controls = $controls_registry->query( array(
			'view_id' => Portal::get_current_view_slug(),
			'preload' => true,
		) );

		foreach ( $preload_controls as $preload_control ) {
			$preload_routes = array_merge( $preload_routes, (array) $preload_control->get_preload_routes() );
		}

		// Fetch.
		$preloaded = array_reduce( array_unique( $preload_routes ), 'rest_preload_api_request', array() );

		// Create the preloading middleware.
		wp_add_inline_script( 'affwp-portal-core',
			sprintf( 'AFFWP.portal.core.fetch.use( AFFWP.portal.core.fetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preloaded ) )
		);
	}


	/**
	 * Loads the footer styles.
	 *
	 * @since 1.0.0
	 *
	 * @todo Bundle inter with plugin.
	 *
	 * @return void
	 */
	public function load_styles() {
		$dir = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'dev' : 'prod';

		wp_register_style( 'inter-font', 'https://rsms.me/inter/inter.css' );
		wp_register_style( 'affwp-affiliate-portal', AFFWP_PORTAL_PLUGIN_URL . 'build/' . $dir . '/style-portal.css' );

		if ( affwp_is_affiliate_portal() ) {
			wp_enqueue_style( 'inter-font' );
			wp_enqueue_style( 'affwp-affiliate-portal' );
		}
	}

	/**
	 * Gets an array of any scripts which should be removed from the theme when the affiliate portal is loaded.
	 *
	 * @since 1.0.0
	 */
	public function theme_script_handles() {

		global $wp_scripts;

		$stylesheet_uri = get_stylesheet_directory_uri();

		$handles = array();

		foreach( $wp_scripts->queue as $handle ) {

			$obj        = $wp_scripts->registered[$handle];
			$obj_handle = $obj->handle;
			$obj_uri    = $obj->src;

			// 0 if found, false otherwise.
			if (strpos($obj_uri, $stylesheet_uri) === 0) {
				// Found, put handle into handles array.
				$handles[] = $obj_handle;
			}

		}

		return $handles;
	}

	/**
	 * Deregisters the theme's scripts.
	 *
	 * @since 1.0.0
	 */
	public function deregister_theme_scripts() {
		if ( ! affwp_is_affiliate_portal() ) {
			return;
		}

		// Deregister scripts that the theme loads.
		foreach ( $this->theme_script_handles() as $handle ) {
			wp_deregister_script( $handle );
		}

	}

	/**
	 * Gets an array of any styles which should be removed from theme when the affiliate portal is loaded.
	 *
	 * @since 1.0.0
	 * @since 1.3.1 Added affiliatewp-mtc to the exclude list.
	 */
	public function theme_style_handles() {
		global $wp_styles;

		$handles = array();

		// Whitelist
		$exclude = array(
			'affwp-custom-affiliate-area',
			'dashicons',
			'inter-font',
			'affwp-affiliate-portal',
			'affiliatewp-mtc',
		);

		foreach ( $wp_styles->queue as $handle ) {
			if ( in_array( $handle, $exclude ) ) {
				continue;
			}

			$handles[] = $handle;
		}

		return $handles;
	}

	/**
	 * Deregisters the theme's styles.
	 *
	 * @since 1.0.0
	 */
	public function deregister_theme_styles() {

		if ( ! affwp_is_affiliate_portal() ) {
			return;
		}

		/**
		 * Set up whitelist of only the CSS files needed.
		 * We do this because we don't want the following CSS files being loaded
		 *
		 * 1. AffiliateWP's own forms.css
		 * 2. The active theme's CSS. This will make the custom area look bad.
		 * 3. Gutenberg's block CSS files
		 */
		foreach ( $this->theme_style_handles() as $handle ) {
			wp_deregister_style( $handle );
		}
	}

	/**
	 * Loads admin scripts.
	 *
	 * @since 1.0.8
	 */
	public function load_admin_scripts() {
		$dir = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'dev' : 'prod';

		wp_register_script(
			'affwp-portal-admin',
			AFFWP_PORTAL_PLUGIN_URL . 'src/admin.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			AFFWP_PORTAL_VERSION
		);

		wp_localize_script( 'affwp-portal-admin', 'affwp_portal_admin_vars', array(
			'new_link_heading' => __( 'New Custom Link', 'affiliatewp-affiliate-portal' ),
			'ays'              => __( 'Are you sure you want to delete this link?', 'affiliatewp-affiliate-portal' ),
		) );

		if ( true === affwp_is_admin_page( 'affiliate-wp-settings' ) && ( isset( $_GET['tab'] ) && 'affiliate_portal' === $_GET['tab'] ) ) {
			wp_enqueue_script( 'affwp-portal-admin' );
		}
	}

	/**
	 * Loads admin styles.
	 *
	 * @since 1.0.8
	 */
	public function load_admin_styles() {
		$dir = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'dev' : 'prod';

		wp_register_style(
			'affwp-portal-admin-styles',
			AFFWP_PORTAL_PLUGIN_URL . 'src/admin.css',
			array( 'dashicons' ),
			AFFWP_PORTAL_VERSION
		);

		if ( true === affwp_is_admin_page( 'affiliate-wp-settings' ) && ( isset( $_GET['tab'] ) && 'affiliate_portal' === $_GET['tab'] ) ) {
			wp_enqueue_style( 'affwp-portal-admin-styles' );
		}
	}

}
