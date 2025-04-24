<?php
/**
 * Scripts and Styles Bootstrap
 *
 * @package     AffiliateWP
 * @subpackage  Core
 * @copyright   Copyright (c) 2014, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

use AffWP\Components\Notifications\REST\v1\Notifications_Endpoints;

/**
 * Determines whether the current admin page is an AffiliateWP admin page.
 *
 * Only works after the `wp_loaded` hook, & most effective
 * starting on `admin_menu` hook.
 *
 * @since 1.0
 *
 * @param string $page Optional. Specific admin page to check for. Default empty (any).
 * @return bool True if AffiliateWP admin page.
 */
function affwp_is_admin_page( $page = '' ) {

	if ( ! is_admin() || ! did_action( 'wp_loaded' ) ) {
		$ret = false;
	}

	if ( empty( $page ) && isset( $_GET['page'] ) ) {
		$page = sanitize_text_field( $_GET['page'] );
	} else {
		$ret = false;
	}

	$pages = array(
		'affiliate-wp',
		'affiliate-wp-affiliates',
		'affiliate-wp-referrals',
		'affiliate-wp-customers',
		'affiliate-wp-payouts',
		'affiliate-wp-visits',
		'affiliate-wp-creatives',
		'affiliate-wp-reports',
		'affiliate-wp-tools',
		'affiliate-wp-settings',
		'affwp-getting-started',
		'affwp-what-is-new',
		'affwp-credits',
		'affiliate-wp-add-ons',
		'affiliate-wp-wizard',
		'affiliate-wp-setup-screen',
		'affiliate-wp-about',
	);

	if ( ! empty( $page ) && in_array( $page, $pages ) ) {
		$ret = true;
	} else {
		$ret = in_array( $page, $pages );
	}

	/**
	 * Filters whether the current page is an AffiliateWP admin page.
	 *
	 * @since 1.0
	 *
	 * @param bool $ret Whether the current page is either a given admin page
	 *                  or any whitelisted admin page.
	 */
	return apply_filters( 'affwp_is_admin_page', $ret );
}

/**
 *  Load the admin scripts
 *
 *  @since 1.0
 *  @return void
 */
function affwp_admin_scripts() {

	if ( 'dashboard' === get_current_screen()->id ) {
		wp_register_script( 'affwp-dashboard-ajax', AFFILIATEWP_PLUGIN_URL . 'assets/js/dashboard-ajax.js', array( 'jquery' ), AFFILIATEWP_VERSION );
	}

	if( ! affwp_is_admin_page() ) {
		return;
	}

	affwp_enqueue_admin_js();

	// only enqueue for settings and creatives page
	if ( 'affiliate-wp-settings' === affwp_get_current_screen() || ( isset( $_GET['action'] ) && ( $_GET['action'] == 'add_creative' || $_GET['action'] == 'edit_creative' ) ) ) {
		wp_enqueue_media();
	}

	// Enqueue Select2 for Setting Screens.
	if ( affwp_is_admin_page() ) {
		affwp_enqueue_style( 'affwp-select2' );
		affwp_enqueue_script( 'affwp-select2' );
	}

	wp_enqueue_script( 'jquery-ui-datepicker' );

	// Enqueue postbox for core meta boxes.
	wp_enqueue_script( 'postbox' );
}
add_action( 'admin_enqueue_scripts', 'affwp_admin_scripts' );

/**
 * Enqueue scripts on the Add New User (user-new.php) Page.
 *
 * @since  2.9.6
 * @return void Early bail when not the `user-new.php` screen.
 */
function affwp_register_dependant_fields() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script( 'affiliate-wp-dependant-fields', AFFILIATEWP_PLUGIN_URL . "assets/js/admin-dependant-fields{$suffix }.js", array( 'jquery' ), AFFILIATEWP_VERSION, true );

	if ( ! affwp_is_add_user_screen() ) {
		return;
	}

	wp_enqueue_script( 'affiliate-wp-dependant-fields' );
}
add_action( 'admin_enqueue_scripts', 'affwp_register_dependant_fields' );

/**
 * Is this the add user screen in the admin?
 *
 * @since 2.9.6
 *
 * @return bool
 */
function affwp_is_add_user_screen() {

	if ( ! is_admin() ) {
		return false;
	}

	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	$screen = get_current_screen();

	return 'add' === $screen->action && 'user' === $screen->base;
}

/**
 * Add `defer` to the script tag.
 *
 * @since 2.9.5
 */
function affwp_add_defer( $url ) {
	// Add `defer` to the AlpineJS script tag.
	return ( false !== strpos( $url, AFFILIATEWP_PLUGIN_URL . 'assets/js/alpine.min.js' ) )
		? str_replace( ' src', ' defer src', $url )
		: $url;
}
add_filter( 'script_loader_tag', 'affwp_add_defer' );

/**
 *  Load the admin styles
 *
 *  @since 1.0
 *  @return void
 */
function affwp_admin_styles() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Dashicons and our main admin CSS need to be on all pages for the menu icon
	wp_enqueue_style( 'affwp-admin', AFFILIATEWP_PLUGIN_URL . 'assets/css/admin' . $suffix . '.css', array( 'dashicons' ), AFFILIATEWP_VERSION );

	if( ! affwp_is_admin_page() ) {
		return;
	}

	// jQuery UI styles are loaded on our admin pages only
	$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
	wp_enqueue_style( 'jquery-ui-css', AFFILIATEWP_PLUGIN_URL . 'assets/css/jquery-ui-' . $ui_style . '.min.css' );

	// In-plugin notifications.
	wp_enqueue_script( 'affwp-admin-notifications' );
	wp_localize_script( 'affwp-admin-notifications', 'affwp_notifications_vars', array(
		'restBase'  => rest_url( ( new Notifications_Endpoints )->namespace ),
		'restNonce' => wp_create_nonce( 'wp_rest' ),
	) );

	// Addons page style.
	wp_register_style( 'affwp_admin_addons', AFFILIATEWP_PLUGIN_URL . "assets/css/admin-addons{$suffix}.css", array(), AFFILIATEWP_VERSION );

	// Setup Screen style.
	wp_register_style( 'affiliate-wp-setup-screen', AFFILIATEWP_PLUGIN_URL . "assets/css/setup-screen{$suffix}.css", array(), AFFILIATEWP_VERSION );
}
add_action( 'admin_enqueue_scripts', 'affwp_admin_styles' );

/**
 * Enqueues and localizes admin.js.
 *
 * This is separated so it can be selectively executed outside of affwp admin pages.
 *
 * @since 2.0
 * @since 2.17.0 Conditionally loads QR Code library if on a Creative page.
 */
function affwp_enqueue_admin_js() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	wp_register_script( 'affwp-tooltips', AFFILIATEWP_PLUGIN_URL . 'assets/js/tooltips' . $suffix . '.js', array( 'jquery' ), AFFILIATEWP_VERSION );

	// Batch processing.
	wp_register_script( 'affwp-batch', AFFILIATEWP_PLUGIN_URL . 'assets/js/batch' . $suffix . '.js', array( 'jquery-form' ), AFFILIATEWP_VERSION );

	wp_localize_script( 'affwp-batch', 'affwp_batch_vars', array(
		'unsupported_browser'   => __( 'We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'affiliate-wp' ),
		'import_field_required' => __( 'This field must be mapped for the import to proceed.', 'affiliate-wp' ),
	) );

	$admin_deps = array( 'jquery', 'jquery-ui-autocomplete', 'affwp-batch', 'wp-util' );

	wp_enqueue_script( 'affwp-admin', AFFILIATEWP_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js', $admin_deps, AFFILIATEWP_VERSION );
	wp_localize_script( 'affwp-admin', 'affwp_vars', array(
		'post_id'                   => isset( $post->ID ) ? $post->ID : null,
		'affwp_version'             => AFFILIATEWP_VERSION,
		'currency_sign'             => affwp_currency_filter(''),
		'currency_pos'              => affiliate_wp()->settings->get( 'currency_position', 'before' ),
		'confirm_delete_referral'   => __( 'Are you sure you want to delete this referral?', 'affiliate-wp' ),
		'holdingPeriodModal'        => [
			'rowActionsTitle'  => __( 'This referral is currently within the commission holding period.', 'affiliate-wp' ),
			'bulkActionsTitle' => __( 'A selected referral is currently within the commission holding period.', 'affiliate-wp' ),
			'content'          => __( 'Are you sure you want to pay this now?', 'affiliate-wp' ),
			'confirm'          => __( 'Yes, pay now', 'affiliate-wp' ),
			'cancel'           => __( 'Cancel', 'affiliate-wp' ),
		],
		'no_user_found'             => __( 'The user you entered does not exist. To create a new user and affiliate, continue filling out the form and click Add User & Affiliate.', 'affiliate-wp' ),
		'no_user_email_found'       => __( 'No user account is associated with this email address. To create a new user and affiliate, continue filling out the form and click Add User & Affiliate.', 'affiliate-wp' ),
		'user_and_affiliate_input'  => __( 'Add User & Affiliate', 'affiliate-wp' ),
		'valid_user_selected'       => __( 'You have selected a valid user account and may continue adding this user as an affiliate.', 'affiliate-wp' ),
		'existing_affiliate'        => __( 'An affiliate already exists for this username.', 'affiliate-wp' ),
		/* translators: Affiliate username */
		'user_email_exists'         => __( 'A user already exists for this email address, however they are not currently an affiliate. Their username is %s', 'affiliate-wp' ),
		'view_affiliate'            => __( 'View Affiliate', 'affiliate-wp' ),
		'creativeDefaultName'       => __( 'Creative', 'affiliate-wp' ),
		'creativeUpgradeNoticeNo'   => __( 'Review & Rename Creatives', 'affiliate-wp' ),
		'creativeUpgradeNoticeYes'  => __( 'Make Creative Names Visible', 'affiliate-wp' ),
		'creativeUpdateNameConfirm' => __( 'This creative’s name has not been updated and affiliates will see it as “Creative” on your website.', 'affiliate-wp' ),
		'creativeQRCodeFeatUpgReq'  => __( 'Please upgrade to PRO to use the QR Code type feature.', 'affiliate-wp' ),
		'proFeatureOnly'            => __( 'Upgrade to Pro to access this feature.', 'affiliate-wp' ),
		'proFeatureOnlyUnlicensed'  => __( 'Enter your license key to access this Pro feature.', 'affiliate-wp' ),
		'license'                   => array(
			'isPro'        => affwp_is_upgrade_required( 'pro' ) !== true,
			'hasProAccess' => affwp_can_access_pro_features(),
		),
	) );

	// Alpine and in-plugin notifcations.
	wp_register_script( 'alpinejs', AFFILIATEWP_PLUGIN_URL . 'assets/js/alpine.min.js', array(), '3.4.2', false );
	wp_register_script( 'affwp-admin-notifications', AFFILIATEWP_PLUGIN_URL . 'assets/js/admin-notifications.js', array( 'alpinejs' ), AFFILIATEWP_VERSION, false );

	// Addons page.
	wp_register_script( 'affwp_admin_addons', AFFILIATEWP_PLUGIN_URL . "assets/js/admin-addons{$suffix}.js", array( 'jquery' ), AFFILIATEWP_VERSION );

	// Setup Screen page.
	wp_register_script( 'affiliate-wp-setup-screen', AFFILIATEWP_PLUGIN_URL . "assets/js/setup-screen{$suffix}.js", array( 'jquery' ), AFFILIATEWP_VERSION, true );

	$screen = get_current_screen();

	if ( ! $screen || 'affiliates_page_affiliate-wp-creatives' !== $screen->id ) {
		return; // Bail if isn't the Creatives admin page.
	}

	// Loads the QR Code script to the Creative pages.
	affiliatewp_load_qrcode_admin_scripts();
}

/**
 * Load resources for QR Code features for the admin screens.
 *
 * @since 2.17.0
 *
 * @return void
 */
function affiliatewp_load_qrcode_admin_scripts() {

	// Enqueue the QR Code scripts.
	affiliate_wp()->scripts->enqueue( 'affiliatewp-qrcode' );

	$action = filter_input( INPUT_GET, 'action' );

	if ( ! in_array( $action, array( 'add_creative', 'edit_creative' ), true ) ) {
		return; // We don't need to load any other resources.
	}

	// Enqueue the color picker.
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	$creative_url = '';

	if ( 'edit_creative' === $action ) {

		$creative     = affwp_get_creative( absint( $_GET['creative_id'] ?? 0 ) );
		$creative_url = $creative->url ?? '';
	}

	$urls = wp_json_encode(
		array(
			'creativeUrl' => ! empty( $creative_url )
				? esc_js( $creative_url )
				: get_site_url(),
			'siteUrl'     => get_site_url(),
		)
	);

	wp_add_inline_script(
		'affwp-admin',
		"const affwpCreativeUrls = {$urls};",
		'before'
	);
}

/**
 * Global scripts.
 *
 * These scripts will always be registered for both
 * frontend and backend use.
 *
 * @since  2.12.0
 */
function affwp_register_global_scripts() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Register select2 CSS.
	wp_register_style( 'affwp-select2', AFFILIATEWP_PLUGIN_URL . 'assets/css/select2' . $suffix . '.css', array(), AFFILIATEWP_VERSION );

	// Register select2 JS lib.
	wp_register_script( 'affwp-select2', AFFILIATEWP_PLUGIN_URL . 'assets/js/select2' . $suffix . '.js', array( 'jquery' ), AFFILIATEWP_VERSION, false );
}
add_action( 'wp_enqueue_scripts', 'affwp_register_global_scripts' );
add_action( 'admin_enqueue_scripts', 'affwp_register_global_scripts' );

/**
 *  Load the frontend scripts and styles
 *
 *  @since 1.0
 *  @return void
 */
function affwp_frontend_scripts_and_styles() {

	global $post;

	if ( ! is_object( $post ) ) {
		return;
	}

	$script_deps = array( 'jquery' );
	$style_deps  = array();

	if ( 'graphs' === affwp_get_active_affiliate_area_tab() || isset( $_REQUEST['tab'] ) && 'graphs' === sanitize_key( $_REQUEST['tab'] ) ) {
		$script_deps[] = 'jquery-ui-datepicker';
		$style_deps[]  = 'jquery-ui-css';
	}

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	wp_register_style( 'affwp-forms', AFFILIATEWP_PLUGIN_URL . 'assets/css/forms' . $suffix . '.css', $style_deps, AFFILIATEWP_VERSION );

	wp_register_style( 'jquery-ui-css', AFFILIATEWP_PLUGIN_URL . 'assets/css/jquery-ui-fresh.min.css' );

	wp_register_script( 'affwp-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), AFFILIATEWP_VERSION );

	wp_register_script( 'affwp-frontend', AFFILIATEWP_PLUGIN_URL . 'assets/js/frontend' . $suffix . '.js', $script_deps, AFFILIATEWP_VERSION );

	wp_localize_script( 'affwp-frontend', 'affwp_vars', array(
		'affwp_version'                  => AFFILIATEWP_VERSION,
		'permalinks'                     => get_option( 'permalink_structure' ),
		'pretty_affiliate_urls'          => affwp_is_pretty_referral_urls(),
		'currency_sign'                  => affwp_currency_filter( '' ),
		'currency_pos'                   => affiliate_wp()->settings->get( 'currency_position', 'before' ),
		'invalid_url'                    => __( 'Please enter a valid URL for this site', 'affiliate-wp' ),
		'personal_account_country_label' => __( 'Your Country of Residence', 'affiliate-wp' ),
		'business_account_country_label' => __( 'Country Where The Business Is Legally Established', 'affiliate-wp' ),
	) );

	/**
	 * Filters whether to force frontend scripts to be enqueued.
	 *
	 * @since 1.0
	 *
	 * @param bool $force Whether to force frontend scripts. Default false.
	 */
	if ( true === apply_filters( 'affwp_force_frontend_scripts', false ) ) {
		affwp_enqueue_script( 'affwp-frontend', 'force_frontend_scripts' );
	}

	// Always enqueue the 'affwp-forms' stylesheet.
	affwp_enqueue_style( 'affwp-forms' );

}
add_action( 'wp_enqueue_scripts', 'affwp_frontend_scripts_and_styles' );

/**
 * Filters whether to enqueue reCAPTCHA via AffiliateWP to maintain GravityForms compatibility.
 *
 * @since 1.9.8
 *
 * @param bool   $enqueue Whether to enqueue the script. Default true.
 * @return bool Whether to enqueue the script.
 */
function affwp_enqueue_recaptcha_gravityforms_compat( $enqueue ) {
	if ( wp_script_is( 'gform-recaptcha', 'enqueued' ) ) {
		$enqueue = false;
	}
	return $enqueue;
}
add_filter( 'affwp_enqueue_script_affwp-recaptcha', 'affwp_enqueue_recaptcha_gravityforms_compat' );
