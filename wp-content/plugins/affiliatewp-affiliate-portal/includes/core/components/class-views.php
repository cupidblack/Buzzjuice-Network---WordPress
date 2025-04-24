<?php
/**
 * Components: Views API
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Components\Views as Core_Views;
use AffiliateWP_Affiliate_Portal\Core\Schemas\Referrals_Chart_Schema;
use AffiliateWP_Affiliate_Portal\Core\Schemas\Referrals_Table_Schema;
use AffiliateWP_Affiliate_Portal\Core\Traits;
use AffiliateWP_Affiliate_Portal\Core\Routes_Registry;
use AffiliateWP_Affiliate_Portal\Core\Views_Registry;
use function AffiliateWP_Affiliate_Portal\html;

#[\AllowDynamicProperties]

/**
 * Views API.
 *
 * @since 1.0.0
 */
class Views {

	use Traits\REST_Support;

	/**
	 * Views registry instance.
	 *
	 * @since 1.0.0
	 * @var   Views_Registry
	 */
	private $registry;

	/**
	 * Bootstraps the Views API.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Set up REST support.
		$this->bootstrap_rest_support();

		add_action( 'affwp_portal_views_registry_init', array( $this, 'register_core_views' ), 0 );
		add_filter( 'doing_it_wrong_trigger_error', [ $this, 'prevent_doing_it_wrong_due_to_early_load_textdomain' ], 10, 3 );

		// Initialize the views registry and fire the hook.
		$views_registry  = Views_Registry::instance();
		$views_registry->init();
	}

	/**
	 * Suppress _load_textdomain_just_in_time was called incorrectly.
	 *
	 * The reason this is here is because we load the views registry before any hook
	 * in WordPress. This is required because it was intentionally designed this way
	 * so all the views can run before WordPress outputs any theme code. But this has
	 * a consiqence in WordPress 6.x because in these views we run __() and other translation
	 * functions before we load the textdomain in wp-content/plugins/affiliatewp-affiliate-portal/includes/lib/affwp/class-affiliatewp-requirements-check-v1-1.php
	 * which is hooked into init. So translations functions are being used before the textdomain is loaded.
	 *
	 * Currently, there is no way around this without re-designing how the Portal works fundementally.
	 *
	 * To fix this issue, and to prevent this notice from being pushed to the screen or in debug.log, we
	 * simply supress the notice for now.
	 *
	 * @since 1.3.7
	 *
	 * @param bool   $show_notice  Setting this to false will supress the notice.
	 * @param string $function     The function isn't used and is always _load_textdomain_just_in_time.
	 * @param string $message      The message that would go to the notice will include the name of
	 *                             our textdomain (affiliatewp-affiliate-portal) so we can use this
	 *                             to detect our addon is being pushed to a notice.
	 *
	 * @return bool Returning `false` will cause WordPress not to show the notice.
	 */
	public function prevent_doing_it_wrong_due_to_early_load_textdomain(
		bool $show_notice,
		string $function,
		string $message
	) {

		return strstr( $message, 'affiliatewp-affiliate-portal' )
			? false // Supress notice.
			: $show_notice; // Not our addon, use default.
	}

	/**
	 * Registers core views.
	 *
	 * @since 1.0.0
	 *
	 * @param Views_Registry $registry Views registry.
	 */
	public function register_core_views( $registry ) {
		$this->registry = $registry;

		$this->register_dashboard_view();
		$this->register_urls_view();
		$this->register_statistics_view();
		$this->register_graphs_view();
		$this->register_referrals_view();
		$this->register_payouts_view();
		$this->register_visits_view();
		$this->register_creatives_view();
		$this->register_settings_view();
		$this->register_coupons_view();
		$this->register_example_controls_view();
		$this->register_feedback_view();
	}

	/**
	 * Registers the core Dashboard/Home view.
	 *
	 * @since 1.0.0
	 */
	public function register_dashboard_view() {

		$home_view = new Core_Views\Home_View();

		$current_user = wp_get_current_user();
		$name         = isset( $current_user->user_firstname ) ? esc_html( $current_user->user_firstname ) : '';

		$this->registry->register_view( 'home', array(
			/* translators: Affiliate name */
			'label'         => sprintf( __( 'Welcome %s', 'affiliatewp-affiliate-portal' ), $name ),
			'menu_label'    => __( 'Dashboard', 'affiliatewp-affiliate-portal' ),
			'priority'      => 0,
			'icon'          => 'home',
			'route_pattern' => 'home',
			'sections'      => $home_view->get_sections(),
			'controls'      => $home_view->get_controls(),
		) );

	}

	/**
	 * Registers the core Affiliate URLs view.
	 *
	 * @since 1.0.0
	 */
	public function register_urls_view() {

		$sections = array(
			'referral-url' => array(
				'label'    => __( 'Referral URL', 'affiliate-wp' ),
				'desc'     => __( 'Share your referral URL with your audience to earn commission.', 'affiliatewp-affiliate-portal' ),
				'priority' => 5,
				'wrapper'  => true,
			),
			'referral-url-generator' => array(
				'label'          => __( 'Referral URL generator', 'affiliatewp-affiliate-portal' ),
				'desc'           => __( 'Use this form to generate a referral link.', 'affiliatewp-affiliate-portal' ),
				'priority'       => 10,
				'wrapper'        => true,
				'preload_routes' => array(  '/affwp/v2/portal/settings', '/affwp/v1/affiliates/' . affwp_get_affiliate_id() ),
				'form_alpine'    => array(
					'x-data'   => 'AFFWP.portal.urlGenerator.default()',
					'x-init'   => 'init()',
					'x-spread' => '{}',
				),
			),
		);


		$controls = array(
			new Controls\Wrapper_Control( array(
				'id'      => 'wrapper',
				'view_id' => 'urls',
				'section' => 'wrapper',
				'atts'    => array(
					'id' => 'affwp-affiliate-portal-urls',
				),
				'alpine'  => array(
					'x-data' => 'AFFWP.portal.urlGenerator.default()',
					'x-init' => 'init()',
				),
			) ),
			new Controls\Div_With_Copy_Control( array(
				'id'       => 'referral-url',
				'view_id'  => 'urls',
				'section'  => 'referral-url',
				'priority' => 5,
				'args'     => array(
					'copy_button_alpine' => array(
						'@click' => "setCopy('referral')",
						'x-text' => "getUrlParam('referral','copyMessage')",
					),
				),
				'alpine'   => array(
					'x-text' => "getUrlParam( 'referral', 'url' ).replace( /%40/g, '@' )",

				),
			) ),
			new Controls\Text_Input_Control( array(
				'id'       => 'affwp-url',
				'view_id'  => 'urls',
				'section'  => 'referral-url-generator',
				'priority' => 5,
				'atts'     => array(
					'aria' => array(
						'describedby' => 'message-error'
					),
				),
				'args'     => array(
					'posts_data' => false,
					'label'      => __( 'Page URL', 'affiliatewp-affiliate-portal' ),
					'desc'       => array(
						'alpine' => array(
							'x-text' => 'pageUrlLabel',
							':class' => "{ 'text-red-600': getUrlParam('generated','isError') === true }"
						),
						'position'   => 'before',
					),
					'error'      => array(
						'alpine' => array(
							'x-show' => "getUrlParam('generated','isError')",
						),
					),
				),
				'alpine'   => array(
					'@input'        => "generateUrl('generated')",
					'x-model'       => 'inputUrl',
					'x-spread'      => '',
					':aria-invalid' => "!getUrlParam('generated','isError')",
					':class'        => "{ 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:shadow-outline-red': getUrlParam('generated','isError') === true,
																    'focus:shadow-outline-blue focus:border-blue-300 focus:outline-none border-gray-300': getUrlParam('generated','isError') !== true  }",
				),
			) ),
			new Controls\Text_Input_Control( array(
				'id'       => 'campaign-name',
				'view_id'  => 'urls',
				'section'  => 'referral-url-generator',
				'priority' => 10,
				'atts'     => array(
					'id' => 'affwp-campaign',
				),
				'args'     => array(
					'posts_data' => false,
					'label'      => __( 'Campaign name', 'affiliatewp-affiliate-portal' ),
					'desc'       => array(
						'text'     => __( 'Enter an optional campaign name to help track performance.', 'affiliatewp-affiliate-portal' ),
						'position' => 'before',
					),
				),
				'alpine'   => array(
					'x-model'       => 'campaign',
					'@input'        => 'generateUrl("generated")',
					':aria-invalid' => '',
					'x-spread'      => '',
					':class'        => '',
				),
			) ),
			new Controls\Div_With_Copy_Control( array(
				'id'       => 'generated-referral-url',
				'view_id'  => 'urls',
				'section'  => 'referral-url-generator',
				'priority' => 15,
				'args'     => array(
					'label'              => __( 'Generated referral URL', 'affiliatewp-affiliate-portal' ),
					'desc'               => __( 'Share this URL with your audience.', 'affiliatewp-affiliate-portal' ),
					'copy_button_alpine' => array(
						'@click' => "setCopy('generated')",
						'x-text' => "getUrlParam('generated','copyMessage')",
					),
				),
				'alpine'   => array(
					'x-show' => "!getUrlParam('generated','isError')",
					'x-text' => "getUrlParam( 'generated', 'url' ).replace( /%40/g, '@' )",
				),
			) ),
		);

		// Referral link sharing - Array of sharing options
		$sharing_options_enabled = affiliate_wp()->settings->get( 'portal_sharing_options' );

		if ( false !== $sharing_options_enabled ){

			// Referral Link Sharing settings.
			$twitter_text     = affiliate_wp()->settings->get( 'portal_sharing_twitter_text' );
			$email_subject    = affiliate_wp()->settings->get( 'portal_sharing_email_subject' );
			$email_body       = affiliate_wp()->settings->get( 'portal_sharing_email_body' );

			$sharing_links_header = new Controls\Heading_Control( array(
				'id'       => 'referral-sharing-heading',
				'view_id'  => 'urls',
				'section'  => 'referral-url-generator',
				'priority' => 15,
				'args'     => array(
					'text' => __( 'Share this URL', 'affiliatewp-affiliate-portal' ),
				),
			) );

			$controls[] = $sharing_links_header;

			// Twitter.
			if ( true === isset( $sharing_options_enabled['twitter'] ) ){

				$twitter_options_text = $twitter_text ? $twitter_text : get_bloginfo( 'name' );
				$twitter_options_text = json_encode( $twitter_options_text );

				$twitter_control = new Controls\Link_Control( array(
					'id'       => 'referral-sharing-twitter',
					'view_id'  => 'urls',
					'section'  => 'referral-url-generator',
					'priority' => 15,
					'args'     => array(
						'label' => __( 'Twitter', 'affiliatewp-affiliate-portal'),
						'icon'  => new Controls\Icon_Control( array(
							'id'   => 'referral-sharing-twitter-icon',
							'args' => array(
								'name' => 'twitter-icon',
								'size' => 8,
								'path' => '<path class="st1" d="M163.4,305.5c88.7,0,137.2-73.5,137.2-137.2c0-2.1,0-4.2-0.1-6.2c9.4-6.8,17.6-15.3,24.1-25 c-8.6,3.8-17.9,6.4-27.7,7.6c10-6,17.6-15.4,21.2-26.7c-9.3,5.5-19.6,9.5-30.6,11.7c-8.8-9.4-21.3-15.2-35.2-15.2 c-26.6,0-48.2,21.6-48.2,48.2c0,3.8,0.4,7.5,1.3,11c-40.1-2-75.6-21.2-99.4-50.4c-4.1,7.1-6.5,15.4-6.5,24.2 c0,16.7,8.5,31.5,21.5,40.1c-7.9-0.2-15.3-2.4-21.8-6c0,0.2,0,0.4,0,0.6c0,23.4,16.6,42.8,38.7,47.3c-4,1.1-8.3,1.7-12.7,1.7 c-3.1,0-6.1-0.3-9.1-0.9c6.1,19.2,23.9,33.1,45,33.5c-16.5,12.9-37.3,20.6-59.9,20.6c-3.9,0-7.7-0.2-11.5-0.7 C110.8,297.5,136.2,305.5,163.4,305.5"/>',
							),
							'atts' => array(
								'viewBox' => array( 0, 0, 400, 400),
								'class'   => array(
									'rounded-full',
									'bg-tw-blue',
									'fill-current',
									'text-white',
								),
							)
						) ),
					),
					'atts'     => array(
						'class' 	=> array(
							'mr-5',
							'cursor-pointer',
						),
					),
					'alpine'   => array(
						'x-data' => 'AFFWP.portal.sharingLinks.default()',
						'x-init' => "text = {$twitter_options_text},
									twitterInit()",
						'@click' => 'twitterReferralLink()',
					)
				) );

				$controls[] = $twitter_control;

			}

			// Facebook.
			if ( true === isset( $sharing_options_enabled['facebook'] ) ){

				$facebook_control = new Controls\Link_Control( array(
					'id'       => 'referral-sharing-facebook',
					'view_id'  => 'urls',
					'section'  => 'referral-url-generator',
					'priority' => 15,
					'args'     => array(
						'label' => __( 'Facebook', 'affiliatewp-affiliate-portal' ),
						'icon'  => new Controls\Icon_Control( array(
							'id'   => 'referral-sharing-facebook-icon',
							'args' => array(
								'name' => 'facebook-icon',
								'size' => 8,
								'path' => '<g transform="translate(0.000000,130.000000) scale(0.100000,-0.100000)" stroke="none"><path d="M525 1234 c-230 -50 -413 -235 -461 -464 -44 -207 32 -437 190 -576 58 -51 192 -122 249 -131 l37 -6 0 207 0 206 -70 0 -70 0 0 90 0 90 69 0 68 0 5 98 c8 145 43 212 138 255 37 17 62 21 138 20 51 -1 97 -5 102 -8 6 -3 10 -42 10 -86 l0 -79 -59 0 c-89 0 -105 -17 -109 -119 l-4 -81 87 0 c77 0 86 -2 81 -17 -3 -10 -8 -40 -12 -68 -13 -93 -15 -95 -89 -95 l-65 0 0 -209 0 -209 52 14 c101 26 179 73 264 158 124 124 174 246 174 421 0 190 -72 345 -217 466 -135 113 -335 161 -508 123z"/></g>',
							),
							'atts' => array(
								'viewBox' => array( 0, 0, 130, 130),
								'class'   => array(
									'rounded-full',
									'text-fb-blue',
									'fill-current',
									'text-white',
								),
							)
						) ),
					),
					'atts'     => array(
						'class' 	=> array(
							'mr-5',
							'cursor-pointer',
						),
					),
					'alpine'   => array(
						'x-data' => 'AFFWP.portal.sharingLinks.default()',
						'x-init' => 'facebookInit()',
						'@click' => 'fbReferralLink()',
					)
				) );

				$controls[] = $facebook_control;
			}

			// Email.
			if ( true === isset( $sharing_options_enabled['email'] ) ){

				$email_options_subject = $email_subject ? $email_subject : get_bloginfo( 'name' );
				$email_options_body    = $email_body ? $email_body : 'I thought you might be interested in this:';

				$email_options_subject = json_encode( $email_options_subject );
				$email_options_body    = json_encode( $email_options_body );

				$email_control = new Controls\Link_Control( array(
					'id'       => 'referral-sharing-email',
					'view_id'  => 'urls',
					'section'  => 'referral-url-generator',
					'priority' => 15,
					'args'     => array(
						'label' => __( 'Email', 'affiliatewp-affiliate-portal'),
						'icon'  => new Controls\Icon_Control( array(
							'id'   => 'referral-sharing-email-icon',
							'args' => array(
								'name'  => 'mail',
								'size'  => 8,
							),
							'atts' => array(
								'class' => array(
									'bg-gray-700',
									'rounded-full',
									'text-white',
									'border-gray-700',
									'border-4',
								),
							)
						) ),
					),
					'atts'     => array(
						'class' => array(
							'cursor-pointer',
						),
					),
					'alpine'   => array(
						'x-data' => 'AFFWP.portal.sharingLinks.default()',
						'x-init' => "subject = {$email_options_subject},
									body = {$email_options_body},
									emailInit()",
						'@click' => 'emailReferralLink($event);',
						)
				) );

				$controls[] = $email_control;
			}

		}

		$this->registry->register_view( 'urls', array(
			'label'    => __( 'Affiliate URLs', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'link',
			'priority' => 1,
			'sections' => $sections,
			'controls' => $controls,
			'loader'   => true,
		) );

	}

	/**
	 * Registers the core Statistics view.
	 *
	 * @since 1.0.0
	 */
	public function register_statistics_view() {

		$view = new Core_Views\Statistics_View;

		$this->registry->register_view( 'stats', array(
			'label'    => __( 'Statistics', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'chart-pie',
			'priority' => 2,
			'sections' => $view->get_sections(),
			'controls' => $view->get_controls(),
		) );
	}

	/**
	 * Registers the core Graphs view.
	 *
	 * @since 1.0.0
	 */
	public function register_graphs_view() {

		$sections = array(
			'referral-graphs' => array(
				'priority' => 5,
				'wrapper'  => false,
				'columns'  => array(
					'content' => 3,
				),
			),
		);

		$controls = array(
			new Controls\Wrapper_Control( array(
				'id'      => 'referral-graphs-wrapper',
				'view_id' => 'graphs',
				'section' => 'wrapper',
			) ),
			new Controls\Chart_Control( array(
				'id'       => 'referral-earnings-chart',
				'view_id'  => 'graphs',
				'section'  => 'referral-graphs',
				'priority' => 5,
				'args'     => array(
					'header' => __( 'Earnings', 'affiliatewp-affiliate-portal' ),
					'schema' => new Referrals_Chart_Schema( 'referrals-chart' ),
				),
			) ),
		);

		$this->registry->register_view( 'graphs', array(
			'label'    => __( 'Graphs', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'chart-bar',
			'priority' => 3,
			'sections' => $sections,
			'controls' => $controls,
		) );
	}

	/**
	 * Registers the core Referrals view.
	 *
	 * @since 1.0.0
	 */
	public function register_referrals_view() {

		$sections = array(
			'referrals-table' => array(
				'wrapper'  => false,
				'priority' => 5,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
			)
		);

		$controls = array(
			new Controls\Wrapper_Control( array(
				'id'      => 'affwp-affiliate-portal-referrals',
				'view_id' => 'referrals',
				'section' => 'wrapper',
			) ),
			new Controls\Table_Control( array(
				'id'      => 'referrals-table',
				'view_id' => 'referrals',
				'section' => 'referrals-table',
				'args'    => array(
					'schema' => new Referrals_Table_Schema( 'referrals-table' ),
				),
			) ),
		);

		$this->registry->register_view( 'referrals', array(
			'label'    => __( 'Referrals', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'credit-card',
			'priority' => 4,
			'sections' => $sections,
			'controls' => $controls,
		) );

	}

	/**
	 * Registers the core Payouts view.
	 *
	 * @since 1.0.0
	 */
	public function register_payouts_view() {

		$sections = array(
			'payouts-table' => array(
				'wrapper'  => false,
				'priority' => 5,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
			)
		);

		$controls = array(
			new Controls\Wrapper_Control( array(
				'id'      => 'affwp-affiliate-portal-payouts',
				'view_id' => 'payouts',
				'section' => 'wrapper',
			) ),
			new Controls\Table_Control( array(
				'id'      => 'payouts-table',
				'view_id' => 'payouts',
				'section' => 'payouts-table',
				'args'    => array(
					'schema' => array(
						'name'          => 'payouts-table',
						'page_count_callback' => function ( $args ) {
							$number = isset( $args['number'] ) ? $args['number'] : 20;

							$count  = affiliate_wp()->affiliates->payouts->count( array(
								'affiliate_id' => $args['affiliate_id'],
								'status'       => array( 'processing', 'paid' ),
							) );

							return absint( ceil( $count / $number ) );
						},
						'data_callback'       => function ( $args ) {
							$args['status'] = array( 'processing', 'paid' );

							return affiliate_wp()->affiliates->payouts->get_payouts( $args );
						},
						'schema'              => array(
							'date'          => array(
								'title'           => __( 'Date', 'affiliatewp-affiliate-portal' ),
								'priority'        => 5,
								'render_callback' => function ( \AffWP\Affiliate\Payout $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_date", $row->date_i18n() );
								},
							),
							'amount'        => array(
								'title'           => __( 'Amount', 'affiliatewp-affiliate-portal' ),
								'priority'        => 10,
								'render_callback' => function ( \AffWP\Affiliate\Payout $row, $table_control_id ) {
									$amount = affwp_currency_filter( affwp_format_amount( $row->amount ) );

									return Controls\Text_Control::create( "{$table_control_id}_amount", $amount );
								},
							),
							'payout_method' => array(
								'title'           => __( 'Payout Method', 'affiliatewp-affiliate-portal' ),
								'priority'        => 15,
								'render_callback' => function( \AffWP\Affiliate\Payout $row, $table_control_id ) {
									$payout_method = affwp_get_payout_method_label( $row->payout_method );

									return Controls\Text_Control::create( "{$table_control_id}_payout_method", $payout_method );
								},
							),
							'status'        => array(
								'title'    => __( 'Status', 'affiliatewp-affiliate-portal' ),
								'priority' => 20,
								'render_callback'     => function ( \AffWP\Affiliate\Payout $row, $table_control_id ) {
									switch ( $row->status ) {
										case 'paid':
											$type = 'approved';
											break;
										case 'failed':
											$type = 'rejected';
											break;
										default:
											$type = 'pending';
									}

									return new Controls\Status_Control( array(
										'id'   => "{$table_control_id}_status",
										'args' => array(
											'type'  => $type,
											'label' => affwp_get_payout_status_label( $row->status ),
										),
									) );
								},
							),
						),
					),
				),
			) ),
		);

		$this->registry->register_view( 'payouts', array(
			'label'    => __( 'Payouts', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'cash',
			'priority' => 5,
			'sections' => $sections,
			'controls' => $controls,
		) );

	}

	/**
	 * Registers the core Visits view.
	 *
	 * @since 1.0.0
	 */
	public function register_visits_view() {

		$view = new Core_Views\Visits_View();

		$this->registry->register_view( 'visits', array(
			'label'    => __( 'Visits', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'cursor-click',
			'priority' => 6,
			'sections' => $view->get_sections(),
			'controls' => $view->get_controls(),
		) );

	}

	/**
	 * Registers the core Creatives view.
	 *
	 * @since 1.0.0
	 */
	public function register_creatives_view() {

		$view = new Core_Views\Creatives_View();

		$this->registry->register_view( $view->slug, array(
			'label'          => __( 'Creatives', 'affiliatewp-affiliate-portal' ),
			'icon'           => 'color-swatch',
			'priority'       => 8,
			'sections'       => $view->get_sections(),
			'controls'       => $view->get_controls(),
			'preload_routes' => true,
			'route'          => array(
				'slug'      => $view->slug,
				'secondary' => array(
					'pattern' => '/(.*)?/?(\d+)?/?$',
					'vars'    => array(

						// @TODO We have to have these here, but the view is registered too soon to obtain them via get_query_var().
						'category'     => '$matches[1]',
						'current_page' => '$matches[2]',
					),
				),
			),
		) );
	}

	/**
	 * Registers the core Settings view.
	 *
	 * @since 1.0.0
	 */
	public function register_settings_view() {

		$view = new Core_Views\Settings_View();

		$this->registry->register_view( 'settings', array(
			'label'        => __( 'Settings', 'affiliatewp-affiliate-portal' ),
			'icon'         => 'cog',
			'sections'     => $view->get_sections(),
			'controls'     => $view->get_controls(),
			'hideFromMenu' => true,
		) );

	}

	/**
	 * Registers the core Coupons view.
	 *
	 * @since 1.0.0
	 */
	public function register_coupons_view() {

		$view = new Core_Views\Coupons_View();

		$affiliate_id = affwp_get_affiliate_id();
		$coupons      = affwp_get_affiliate_coupons( $affiliate_id, false );

		// Hide from menu if there are no coupons associated to this affiliate.
		if ( ! isset( $coupons['dynamic'] ) && ! isset( $coupons['manual'] ) ) {
			$hide_coupons = true;
		} else {
			$hide_coupons = false;
		}

		$this->registry->register_view( 'coupons', array(
			'label'        => __( 'Coupons', 'affiliatewp-affiliate-portal' ),
			'icon'         => 'tag',
			'priority'     => 7,
			'sections'     => $view->get_sections(),
			'controls'     => $view->get_controls(),
			'hideFromMenu' => $hide_coupons,
		) );
	}

	/**
	 * Registers the (hidden) example-contols view.
	 *
	 * @since 1.0.0
	 */
	public function register_example_controls_view() {

		if ( 1 === affiliate_wp()->utils->debug_enabled ) {

			$view = new Core_Views\Example_Controls_View();

			$this->registry->register_view( 'example-controls', array(
				'label'        => __( 'Example Controls', 'affiliatewp-affiliate-portal' ),
				'icon'         => 'collection',
				'priority'     => 100,
				'sections'     => $view->get_sections(),
				'controls'     => $view->get_controls(),
				'hideFromMenu' => true,
			) );
		}

	}

	/**
	 * Registers the core Feedback view.
	 *
	 * @since 1.0.0
	 */
	public function register_feedback_view() {

		$view = new Core_Views\Feedback_View();

		$this->registry->register_view( 'feedback', array(
			'label'        => __( 'Give us your feedback', 'affiliatewp-affiliate-portal' ),
			'icon'         => 'chat-alt',
			'sections'     => $view->get_sections(),
			'controls'     => $view->get_controls(),
			'hideFromMenu' => true,
		) );

	}

	/**
	 * Registers REST routes.
	 *
	 * @since 1.0.0
	 *
	 * @see register_rest_route()
	 */
	public function register_rest_routes() {

		// affwp/v2/portal/views
		register_rest_route( $this->namespace, 'views', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_views' ),
				'args'                => $this->get_rest_collection_params( 'views' ),
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
			),
			'schema' => array( $this, 'get_view_schema' ),
		) );

		// affwp/v2/portal/views/view
		register_rest_route( $this->namespace, 'views/(?P<view>[\w\-_]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_view' ),
				'args'                => $this->get_rest_collection_params( 'view' ),
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
			),
			'schema' => array( $this, 'get_view_schema' ),
		) );
	}

	/**
	 * Retrieves registered views.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error Registered views.
	 */
	public function get_views( $request ) {
		$registry = Views_Registry::instance();

		return $registry->get_rest_items( 'view' );
	}

	/**
	 * Retrieves a registered view.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error Registered views.
	 */
	public function get_view( $request ) {
		$view = $request->get_param( 'view' );

		$registry = Views_Registry::instance();

		return $registry->get_rest_item( 'view', $view );
	}

	/**
	 * Retrieves parameters for the given collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $collection Collection to retrieve parameters for.
	 * @return array Collection parameters (if any), otherwise an empty array.
	 */
	public function get_rest_collection_params( $collection ) {
		$params = array(
			'context' => array(
				'default' => 'view'
			),
		);

		switch ( $collection ) {
			case 'view':
			case 'views':
				$params = array_merge( $params, array(
					'viewId'       => array(
						'description'       => __( 'View ID as stored in the views registry.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_string( $param );
						},
					),
					'label'        => array(
						'description'       => __( 'Label used for the view link.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_string( $param );
						},
					),
					'icon'         => array(
						'description'       => __( 'Icon markup used for the view link.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => '',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_string( $param );
						},
					),
					'hideFromMenu' => array(
						'description'       => __( 'Whether to hide the link from the menu.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => '',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_bool( $param );
						},
					),
				) );
				break;

			default:
				break;
		}

		return $params;
	}

	/**
	 * Retrieves the schema for a single view, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_view_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'affwp_portal_view',
			'type'       => 'object',
			// Base properties for every view.
			'properties' => array(
				'viewId'       => array(
					'description' => __( 'View ID as stored in the views registry.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'string',
				),
				'hideFromMenu' => array(
					'description' => __( 'Whether to hide the link from the menu.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'bool',
				),
				'label'        => array(
					'description' => __( 'Label used for the view link.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'string',
				),
				'icon'         => array(
					'description' => __( 'Icon markup used for the view link.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'string',
				),
			),
		);

		// TODO implement additional fields support.
		return $schema;
	}

}
