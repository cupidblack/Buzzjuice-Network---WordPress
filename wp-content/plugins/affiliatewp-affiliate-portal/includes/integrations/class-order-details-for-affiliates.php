<?php
/**
 * Integrations: Order Details for Affiliates
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Integrations;

use AffiliateWP_Affiliate_Portal\Core;
use AffiliateWP_Affiliate_Portal\Core\Interfaces;
use AffiliateWP_Affiliate_Portal\Core\Traits;
use AffiliateWP_Affiliate_Portal\Core\Components\Portal;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

/**
 * Class for integrating the Order Details for Affiliates add-on.
 *
 * @since 1.0.0
 */
class Order_Details_for_Affiliates implements Interfaces\Integration {

	use Traits\REST_Support;

	/**
	 * View ID.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $view_id = 'order-details';

	/**
	 * Allowed fields.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $allowed_fields = array();

	/**
	 * Allowed table fields.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $allowed_table_fields = array();

	/**
	 * Sets up props needed for any given instantiation of the helper class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$odfa_allowed_fields = affiliatewp_order_details_for_affiliates()->order_details->allowed();

		foreach ( $odfa_allowed_fields as $field => $status ) {
			if ( true === $status ) {
				$this->allowed_fields[] = $field;
			}
		}

		$this->allowed_table_fields = $this->get_allowed_table_fields();
	}

	/**
	 * @inheritDoc
	 */
	public function init() {
		$this->bootstrap_rest_support();

		// Register the ODFA view and secondary view controls.
		add_action( 'affwp_portal_views_registry_init', array( $this, 'register_view' ) );
		add_action( 'affwp_portal_controls_registry_init', array( $this, 'register_secondary_controls' ) );

		add_action( 'template_redirect', array( $this, 'maybe_redirect_invalid_order_detail' ) );
		add_action( 'template_redirect', array( $this, 'maybe_flush_rewrites' ), 0 );

		add_filter( 'query_vars', array( $this, 'odfa_query_vars' ) );

		add_filter( 'affwp_settings_integrations', array( $this, 'order_details_table_column_settings' ), 11 );

		// Sanitize settings.
		add_filter( 'affwp_settings_sanitize', array( $this, 'sanitize_odfa_items_per_page' ), 10, 2 );
	}

	/**
	 * Sets up query variables used by the ODFA integration.
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars Query vars.
	 * @return array Modified query vars.
	 */
	public function odfa_query_vars( $vars ) {
		$vars[] = 'affwp_odfa_order';

		return $vars;
	}

	/**
	 * Retrieves the sections for the view.
	 *
	 * @since 1.0.0
	 */
	public function get_sections() {
		$sections = array(
			'order-details' => array(
				'priority'            => 5,
				'permission_callback' => function( $section_id, $affiliate_id ) {
					return 'view_order' !== get_query_var( 'affwp_portal_action' );
				},
				'columns'             => array(
					'header'  => 3,
					'content' => 3,
				),
			),
			'order-details-single' => array(
				'priority'            => 5,
				'permission_callback' => function( $section_id, $affiliate_id ) {
					return 'view_order' === get_query_var( 'affwp_portal_action' );
				},
				'columns'             => array(
					'header'  => 3,
					'content' => 3,
				),
			),
		);

		return $sections;
	}

	/**
	 * Retrieves the controls for the primary view.
	 *
	 * @since 1.0.0
	 *
	 * @return Controls\Base_Control[] Controls.
	 */
	public function get_primary_controls() {

		/*
		 * Add a description for how to interact with the table if the detail
		 * view is enabled by virtue of detail fields being allowed.
		 */
		if ( $this->get_allowed_fields( true ) > 0 ) {
			$table_desc = __( 'Each order on this screen is associated with one of your referrals. Click any value below to view more detailed information about an order.', 'affiliatewp-affiliate-portal' );
		} else {
			$table_desc = __( 'Each order on this screen is associated with one of your referrals.', 'affiliatewp-affiliate-portal' );
		}

		$odfa_items_per_page_setting = affiliate_wp()->settings->get( 'odfa_items_per_page' );

		$controls = array(
			new Controls\Wrapper_Control( array(
				'id'      => 'wrapper',
				'view_id' => $this->view_id,
				'section' => 'wrapper',
			) ),
			new Controls\Paragraph_Control( array(
				'id'                  => 'odfa_single_no_details',
				'view_id'             => $this->view_id,
				'section'             => 'order-details',
				'priority'            => 1,
				'permission_callback' => function( $control_id, $affiliate_id ) {
					return 0 === $this->get_allowed_table_fields( true );
				},
				'atts'                => array(
					'class' => array( 'mt-2', 'text-sm', 'leading-5', 'text-gray-600' ),
				),
				'args'                => array(
					'text' => __( 'Looks like there are no order details to display. Contact your affiliate manager to find out more information.', 'affiliatewp-affiliate-portal' ),
				)
			) ),
			new Controls\Table_Control( array(
				'id'                  => 'odfa-table',
				'view_id'             => $this->view_id,
				'section'             => 'order-details',
				'permission_callback' => function( $control_id, $affiliate_id ) {
					return 0 !== $this->get_allowed_table_fields( true );
				},
				'args'                => array(
					'data'   => array(
						'allowSorting'   => false,
						'showPagination' => false,
						'perPage'        => ! empty( $odfa_items_per_page_setting ) ? $odfa_items_per_page_setting : 30,
					),
					'schema' => new ODFA_Table_Schema( 'odfa-table' ),
					'desc'   => array(
						'position' => 'before',
						'text'     => $table_desc,
					),
				),
			) ),
		);

		return $controls;
	}

	/**
	 * Registers the Order Details for Affiliates view.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Views_Registry $registry Views registry instance.
	 */
	public function register_view( $registry ) {

		$registry->register_view( $this->view_id, array(
			'label'               => __( 'Order Details', 'affiliatewp-affiliate-portal' ),
			'icon'                => 'clipboard-list',
			'permission_callback' => array( $this, 'can_affiliate_access_odfa_view' ),
			'sections'            => $this->get_sections(),
			'controls'            => $this->get_primary_controls(),
			'route'               => array(
				'slug'      => 'orders',
				'secondary' => array(
					'pattern' => '/detail/(\d+)/?$',
					'vars'    => array(
						'affwp_portal_action' => 'view_order',
						'affwp_odfa_order'    => '$matches[1]'
					),
				)
			)
		) );
	}

	/**
	 * Registers controls for display in the ODFA secondary view.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Controls_Registry $registry Controls registry.
	 */
	public function register_secondary_controls( $registry ) {

		$fields = array(
			'stats'    => array(
				'referral_amount'  => __( 'Referral Amount', 'affiliatewp-affiliate-portal' ),
				'order_total'      => __( 'Order Total', 'affiliatewp-affiliate-portal' ),
				'order_number'     => __( 'Order Number', 'affiliatewp-affiliate-portal' ),
			),
			'meta'     => array(
				'order_date'  => __( 'Order Date', 'affiliatewp-affiliate-portal' ),
				'coupon_code' => __( 'Coupon Code', 'affiliatewp-affiliate-portal' ),
			),
			'customer' => array(
				'customer_name'             => __( 'Customer Name', 'affiliatewp-affiliate-portal' ),
				'customer_email'            => __( 'Customer Email', 'affiliatewp-affiliate-portal' ),
				'customer_phone'            => __( 'Customer Phone', 'affiliatewp-affiliate-portal' ),
				'customer_shipping_address' => __( 'Customer Shipping Address', 'affiliatewp-affiliate-portal' ),
				'customer_billing_address'  => __( 'Customer Billing Address', 'affiliatewp-affiliate-portal' ),
			),
		);

		$cards = $controls = array();

		foreach ( $fields as $group => $group_fields ) {
			foreach ( $group_fields as $field => $title ) {
				if ( $this->is_field_allowed( $field, 'detail' ) ) {
					$cards[ $group ][] = array(
						'title'    => $title,
						'data_key' => $field,
						'data'     => array( $this, 'get_single_order_data' ),
					);
				}
			}
		}

		$controls[] = new Controls\Link_Control( array(
			'id'       => 'odfa_single_back_link',
			'view_id'  => $this->view_id,
			'section'  => 'order-details-single',
			'priority' => 0,
			'args'     => array(
				'label'         => __( 'Return to all orders', 'affiliatewp-affiliate-portal' ),
				'icon_position' => 'before',
				'get_callback'  => function( $control ) {
					return Portal::get_page_url( $this->view_id );
				},
				'icon'          => new Controls\Icon_Control( array(
					'id'   => 'odfa_single_back_link_icon',
					'args' => array(
						'name' => 'arrow-narrow-left',
					)
				) ),
				'wrapper'       => array(
					'class' => array( 'w-max-content' ),
				),
			)
		) );

		if ( ! empty( $cards['stats'] ) ) {
			$controls[] = new Controls\Card_Group_Control( array(
				'id'       => 'odfa_single_stat_group',
				'view_id'  => $this->view_id,
				'section'  => 'order-details-single',
				'priority' => 5,
				'atts'     => array(
					'class' => 'mb-5',
				),
				'args'     => array(
					'columns'    => 3,
					'cards'      => $cards['stats'],
					'show_empty' => false,
				),
			) );
		}

		if ( ! empty( $cards['meta'] ) ) {
			$controls[] = new Controls\Card_Group_Control( array(
				'id'       => 'odfa_single_meta_group',
				'view_id'  => $this->view_id,
				'section'  => 'order-details-single',
				'priority' => 10,
				'atts'     => array(
					'class' => 'mb-5',
				),
				'args'     => array(
					'columns'    => 2,
					'cards'      => $cards['meta'],
					'show_empty' => false,
				),
			) );
		}

		if ( ! empty( $cards['customer'] ) ) {
			$controls[] = new Controls\Card_Group_Control( array(
				'id'       => 'odfa_single_info_group',
				'view_id'  => $this->view_id,
				'section'  => 'order-details-single',
				'priority' => 15,
				'atts'     => array(
					'class' => 'mb-5',
				),
				'args'     => array(
					'columns'     => 3,
					'cards'       => $cards['customer'],
					'card_layout' => 'info',
					'show_empty'  => false,
				),
			) );
		}

		foreach ( $controls as $control ) {
			$registry->add_control( $control );
		}
	}

	/**
	 * Determines whether the current affiliate is allowed to access ODFA views.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view_id      View ID.
	 * @param int    $affiliate_id Affiliate ID.
	 * @return bool True if the affiliate has access, otherwise false.
	 */
	public function can_affiliate_access_odfa_view( $view_id, $affiliate_id ) {
		$odfa = affiliatewp_order_details_for_affiliates();

		$affiliate_user_id = affwp_get_affiliate_user_id( $affiliate_id );

		$order_referral_id = get_query_var( 'affwp_odfa_order' );
		$referral          = affwp_get_referral( $order_referral_id );

		$affiliate_access = $odfa->can_access_order_details( $affiliate_user_id );
		$global_access    = $odfa->global_order_details_access();

		$allowed = $global_access || $affiliate_access;

		if ( 0 === $this->get_allowed_fields( true ) && $order_referral_id ) {
			$allowed = false;
		}

		if ( $referral && $referral->affiliate_id !== (int) $affiliate_id ) {
			$allowed = false;
		}

		return $allowed;
	}

	/**
	 * (Maybe) redirects away from the order detail screen if the supplied order referral is invalid.
	 *
	 * @since 1.0.0
	 */
	public function maybe_redirect_invalid_order_detail() {
		$action      = get_query_var( 'affwp_portal_action' );
		$referral_id = get_query_var( 'affwp_odfa_order' );

		$referral = affwp_get_referral( $referral_id );

		if ( 'view_order' === $action ) {
			if ( ! $referral
				|| ( $referral && ! affiliatewp_order_details_for_affiliates()->order_details->exists( $referral ) )
			) {
				wp_redirect( Portal::get_page_url( $this->view_id ) );
				exit;
			}
		}
	}

	/**
	 * (Maybe) flushes rewrites for /orders on the first load.
	 *
	 * @since 1.0.0
	 * @since 1.3.6 Fixed redirect so it doesn't always redirect to the Dashboard page, instead it redirects
	 *               to whatever page you were on see https://github.com/awesomemotive/affiliate-wp/issues/5335.
	 */
	public function maybe_flush_rewrites() {
		global $wp;

		$post_name = get_post_field( 'post_name', affwp_get_affiliate_area_page_id() );

		if ( isset( $wp->query_vars['orders'] ) && empty( $wp->query_vars['orders'] )
			&& ( isset( $wp->query_vars['pagename'] ) && $post_name === $wp->query_vars['pagename'] )
		) {
			flush_rewrite_rules( false );

			$dashboard_url = home_url( $_SERVER['REQUEST_URI'] ?? get_permalink( get_post( affwp_get_affiliate_area_page_id() ) ) );

			if ( wp_redirect( $dashboard_url ) ) {
				exit;
			}
		}

	}

	/**
	 * Filters the Integrations settings to add table column-specific settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings Settings array.
	 * @return array Modified settings array.
	 */
	public function order_details_table_column_settings( $settings ) {

		$settings['odfa_enable_portal_table_fields'] = array(
			'name'    => __( 'Enable Details in the Affiliate Portal Table', 'affiliatewp-affiliate-portal' ),
			'desc'    => __( 'Select which details <strong>should</strong> show in the table in the Affiliate Portal.', 'affiliatewp-affiliate-portal' ),
			'type'    => 'multicheck',
			'options' => $this->get_allowed_table_fields_options(),
		);

		$settings['odfa_items_per_page'] = array(
			'name' => __( 'Orders per page', 'affiliatewp-affiliate-portal' ),
			'desc' => __( 'The number of orders to display in the Order Details table beginning with the most recent first.', 'affiliatewp-affiliate-portal' ),
			'type' => 'number',
			'min'  => '1',
			'max'  => '100',
			'std'  => '30',
		);

		return $settings;
	}

	/**
	 * Sanitizes the Affiliate Portal's ODFA items per page setting.
	 *
	 * @since 1.0.9
	 *
	 * @param mixed  $value Setting value.
	 * @param string $key   Setting key.
	 * @return mixed Sanitized items per page value.
	 */
	public function sanitize_odfa_items_per_page( $value, $key ) {
		if ( 'odfa_items_per_page' === $key ) {
			// Should default to 30.
			if ( empty( $value ) ) {
				$value = 30;
			}

			// Max value is 100.
			if ( $value > 100 ) {
				$value = 100;
			}

			// Min value is 1.
			if ( $value < 1 ) {
				$value = 1;
			}
		}

		return $value;
	}

	/**
	 * Retrieves the table fields list with corresponding labels.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $keys_only Optional. Whether to retrieve the keys only. Default false.
	 * @return array Table fields and labels key/value pairs.
	 */
	public function get_allowed_table_fields_options( $keys_only = false ) {
		$fields = array(
			'order_number'    => __( 'Order Number', 'affiliatewp-affiliate-portal' ),
			'customer_name'   => __( 'Customer Name', 'affiliatewp-affiliate-portal' ),
			'order_total'     => __( 'Order Total', 'affiliatewp-affiliate-portal' ),
			'referral_amount' => __( 'Referral Amount', 'affiliatewp-affiliate-portal' ),
			'order_date'      => __( 'Order Date', 'affiliatewp-affiliate-portal' ),
		);

		if ( true === $keys_only ) {
			$fields = array_keys( $fields );
		}

		return $fields;
	}

	//
	// Helpers.
	//

	/**
	 * Retrieves the list of allowed fields.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $count Optional. Whether to return the number of allowed fields. Default false.
	 * @return string[]|int Allowed fields or number of allowed fields if `$count` is true.
	 */
	public function get_allowed_fields( $count = false ) {
		if ( true === $count ) {
			return count( $this->allowed_fields );
		} else {
			return $this->allowed_fields;
		}
	}

	/**
	 * Retrieves the list of allowed table fields according to the ODFA settings.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $count Optional. Whether to return the number of allowed fields. Default false.
	 * @return string[]|int Allowed table fields or number of allowed table fields if `$count` is true.
	 */
	public function get_allowed_table_fields( $count = false ) {

		$allowed_table_fields = affiliate_wp()->settings->get( 'odfa_enable_portal_table_fields' );

		// If the setting isn't set, default to the enable/disable settings for standard details.
		if ( ! $allowed_table_fields ) {
			$standard_allowed_fields = $this->get_allowed_fields();
			$allowed_table_fields    = $this->get_allowed_table_fields_options();

			foreach ( $allowed_table_fields as $field => $label ) {
				if ( ! in_array( $field, $standard_allowed_fields ) ) {
					unset( $allowed_table_fields[ $field ] );
				}
			}

			// Update the settings.
			affiliate_wp()->settings->set( array( 'odfa_enable_portal_table_fields' => $allowed_table_fields ), true );
		}

		$allowed_table_fields = array_unique( array_keys( $allowed_table_fields ) );

		if ( true === $count ) {
			return count( $allowed_table_fields );
		} else {
			return $allowed_table_fields;
		}
	}

	/**
	 * Helper to determine whether a given field is allowed to display.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field Field key.
	 * @param string $type  Field type. Accepts 'table' or 'detail'.
	 * @return bool True if allowed, otherwise false.
	 */
	public function is_field_allowed( $field, $type ) {
		switch ( $type ) {
			case 'table':
				$allowed = in_array( $field, $this->get_allowed_table_fields() );
				break;

			case 'rest-single':
			case 'detail':
				$allowed = in_array( $field, $this->get_allowed_fields() );
				break;

			default:
				$allowed = false;
				break;
		}

		return $allowed;
	}

	/**
	 * Data callback to retrieve a single order detail.
	 *
	 * @since 1.0.0
	 *
	 * @param string $data_key     Data key for identifying the specific piece of data.
	 * @param int    $affiliate_id Affiliate ID.
	 * @return mixed Data value (if set) otherwise empty string.
	 */
	public function get_single_order_data( $data_key, $affiliate_id ) {
		$referral_id = get_query_var( 'affwp_odfa_order' );

		if ( ! $referral_id ) {
			return '';
		}

		$data = affwp_rest_get( '/affwp/v2/portal/integrations/order-details/orders/' . $referral_id, array(
			'affiliate_id' => $affiliate_id,
		) );

		return isset( $data[ $data_key ] ) ? $data[ $data_key ] : '';
	}

	/**
	 * Retrieves the order data for a referral.
	 *
	 * Copied (mostly) verbatim from Affiliate_WP_Order_Details_For_Affiliates_Order_Details::get().
	 *
	 * @since 1.0.0
	 *
	 * @param int|\AffWP\Referral $referral      Referral object.
	 * @param string              $query_context Context under which the data is being queried.
	 * @param bool                $allowed_only  Whether to filter data for allowed fields only. Default true.
	 * @return array|\WP_Error Order data if the referral is valid, otherwise a WP_Error object.
	 */
	public function get_order_data( $referral, $query_context, $allowed_only = true ) {

		// Bail if the referral is invalid.
		if ( ! $referral = affwp_get_referral( $referral ) ) {
			return new \WP_Error(
				'odfa_invalid_referral',
				'The referral used to retrieve order data is invalid.',
				$referral
			);
		}

		// Bail if there's no order.
		if ( 'rest-single' === $query_context
			&& ! affiliatewp_order_details_for_affiliates()->order_details->exists( $referral )
		) {
			return new \WP_Error(
				'odfa_invalid_order',
				sprintf( 'There is no order associated with referral #%s.', $referral->ID ),
				$referral
			);
		}

		$defaults = array(
			'order_number'              => 0,
			'order_date'                => '',
			'order_total'               => 0,
			'coupon_code'               => '',
			'customer_name'             => '',
			'customer_email'            => '',
			'customer_phone'            => '',
			'customer_shipping_address' => '',
			'customer_billing_address'  => '',
			'referral_amount'  => 0,
		);

		$data = array();

		// Retrieve the order data by context.
		switch ( $referral->context ) {

			case 'edd':
				if ( ! function_exists( 'edd_get_payment_meta' ) ) {
					break;
				}

				$payment      = new \EDD_Payment( $referral->reference );
				$payment_meta = edd_get_payment_meta( $referral->reference );
				$user_info    = edd_get_payment_meta_user_info( $referral->reference );

				$data = array(
					'order_number'             => $referral->reference,
					'order_date'               => $payment_meta['date'],
					'order_total'              =>  edd_currency_filter( edd_format_amount( edd_get_payment_amount( $referral->reference ) ) ),
					'coupon_code'              => 'none' !== $payment->discounts ? $payment->discounts : '',
					'customer_name'            => $payment->first_name . ' ' . $payment->last_name,
					'customer_email'           => isset( $user_info['email'] ) ? $user_info['email'] : '',
					'customer_phone'           => '',
					'customer_billing_address' => '',
				);

				$address = '';

				if ( ! empty( $user_info['address'] ) ) {
					$parts = array( 'line1', 'line2', 'city', 'zip', 'state', 'country' );

					foreach ( $parts as $part ) {
						if ( isset( $user_info['address'][ $part ] ) ) {
							$address .= $user_info['address'][ $part ] . "\r\n";
						}
					}
				}

				$data['customer_billing_address'] = $address;
				break;

			case 'woocommerce':
				if ( ! class_exists( 'WC_Order' ) ) {
					break;
				}

				$order = new \WC_Order( $referral->reference );

				if ( version_compare( \WC()->version, '3.7.0', '>=' ) ) {
					$coupons = $order->get_coupon_codes();
				} else {
					$coupons = $order->get_used_coupons();
				}

				$data = array(
					'order_number'              => $order->get_order_number(),
					'order_date'                => $order->get_date_created(),
					'order_total'               => $order->get_formatted_order_total(),
					'coupon_code'               => ! empty( $coupons ) ? implode( ', ', $coupons ) : '',
					'customer_name'             => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
					'customer_email'            => $order->get_billing_email(),
					'customer_phone'            => $order->get_billing_phone(),
					'customer_shipping_address' => $order->get_formatted_shipping_address(),
					'customer_billing_address'  => $order->get_formatted_billing_address(),
				);
				break;

			default: break;
		}

		$data = wp_parse_args( $data, $defaults );

		/*
		 * Unset data from the array if the field is disallowed OR if this specific
		 * request is for the single view + the data is empty.
		 *
		 * The single view condition ensures cards lacking data don't get rendered empty.
		 */
		if ( true === $allowed_only ) {

			foreach ( $data as $key => $value ) {

				if ( ! $this->is_field_allowed( $key, $query_context )
					|| ( 'rest-single' === $query_context && empty( $data[ $key ] ) )
				) {
					unset( $data[ $key ] );
				}

			}

		}

		// Format the date / WooCommerce returns WC_DateTime objects.
		if ( ! empty( $data['order_date'] ) ) {
			if ( $data['order_date'] instanceof \DateTime ) {
				$date = $data['order_date']->format( 'U' );
			} else {
				$date = strtotime( $data['order_date'] );
			}

			$data['order_date'] = affwp_date_i18n( $date, 'date' );
		}

		// Add extra referral data to the response.
		$extra_data = array(
			'referral_amount' => affwp_currency_filter( affwp_format_amount( $referral->amount ) ),
			'referral_id'     => $referral->ID,
			'context'         => $referral->context,
		);

		$data = array_merge( $data, $extra_data );

		return $data;
	}

	/**
	 * Helper to build and retrieve the order URL.
	 *
	 * @since 1.0.0
	 *
	 * @param array $row Row metadata.
	 * @return string Order URL.
	 */
	public function get_order_url( $row ) {
		$url_base = Portal::get_page_url( $this->view_id );

		if ( ! empty( $row['referral_id'] ) ) {
			$order_url = sprintf( '%1$s/detail/%2$s', $url_base, $row['referral_id'] );
		} else {
			$order_url = $url_base;
		}

		return $order_url;
	}

	//
	// REST endpoints.
	//

	/**
	 * Registers REST routes.
	 *
	 * @since 1.0.0
	 *
	 * @see register_rest_route()
	 */
	public function register_rest_routes() {

		$namespace = $this->namespace . '/integrations';

		// affwp/v2/portal/integrations/order-details/orders
		register_rest_route( $namespace, 'order-details/orders', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
				'args'                => $this->get_rest_collection_params( 'orders' ),
				'callback'            => function( \WP_REST_Request $request ) {
					$referral_args = affiliatewp_order_details_for_affiliates()->order_details->referral_args();

					$affiliate_id  = $request->get_param( 'affiliate_id' );
					$query_context = $request->get_param( 'query_context' );

					if ( ! $query_context ) {
						$query_context = 'rest';
					}

					if ( $affiliate_id ) {
						$referral_args['affiliate_id'] = $affiliate_id;

						$referrals = affiliate_wp()->referrals->get_referrals( $referral_args );

						$data = array();

						foreach ( $referrals as $referral ) {
							$data[] = $this->get_order_data( $referral, $query_context, false );
						}
					} else {
						$data = new \WP_Error(
							'odfa_invalid_affiliate_id',
							'A valid affiliate ID is required to retrieve order details.',
							$request->get_params()
						);
					}

					return rest_ensure_response( $data );
				},
			),
			'schema' => array( $this, 'get_order_schema' ),
		) );

		// affwp/v2/portal/integrations/order-details/orders/##
		register_rest_route( $namespace, 'order-details/orders/(?P<referral_id>\d+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
				'args'                => $this->get_rest_collection_params( 'order' ),
				'callback'            => function( \WP_REST_Request $request ) {
					$referral_id   = $request->get_param( 'referral_id' );
					$query_context = $request->get_param( 'query_context' );

					if ( ! $query_context ) {
						$query_context = 'rest-single';
					}

					if ( $referral = affwp_get_referral( $referral_id ) ) {
						$data = $this->get_order_data( $referral, $query_context );
					} else {
						$data = new \WP_Error(
							'odfa_invalid_referral_id',
							'A valid affiliate ID is required to retrieve an single order\'s details.',
							$request->get_params()
						);
					}

					return rest_ensure_response( $data );
				},
			),
			'schema' => array( $this, 'get_order_schema' ),
		) );

	}

	/**
	 * Retrieves the collection parameters.
	 *
	 * @since 1.0.0
	 *
	 * @param string $collection REST route to retrieve collection parameters for.
	 * @return array Collection parameters.
	 */
	public function get_rest_collection_params( $collection ) {

		$params = array(
			'context' => array(
				'default' => 'view'
			),
		);

		switch( $collection ) {
			case 'order':
			case 'orders':
				$params = array_merge( $params, array(
					'affiliate_id' => array(
						'description'       => __( 'Affiliate ID to retrieve a collection of order details for.', 'affiliatewp-affiliate-portal' ),
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_numeric( $param );
						},
					),
				) );
				break;

			default: break;
		}

		return $params;
	}

	/**
	 * Retrieves the schema for single or multiple orders.
	 *
	 * @since 1.0.0
	 *
	 * @return array Order(s) schema.
	 */
	public function get_order_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'affwp_portal_odfa_order',
			'type'       => 'object',
			// Base properties for every control.
			'properties' => array(
				'referral_id'     => array(
					'description' => __( 'Coupon code used with the order (if any).', 'affiliatewp-affiliate-portal' ),
					'type'        => 'string',
				),
				'order_number'     => array(
					'description'  => __( 'The order number.', 'affiliatewp-affiliate-portal' ),
					'type'         => 'integer',
				),
				'order_date'       => array(
					'description'  => __( 'The date the order was made.', 'affiliatewp-affiliate-portal' ),
					'type'         => 'string',
				),
				'order_total'      => array(
					'description'  => __( 'The order total.', 'affiliatewp-affiliate-portal' ),
					'type'         => 'string',
				),
				'coupon_code'      => array(
					'description'  => __( 'Coupon code used with the order (if any).', 'affiliatewp-affiliate-portal' ),
					'type'         => 'string',
				),
				'customer_name'    => array(
					'description'  => __( 'Customer name.', 'affiliatewp-affiliate-portal' ),
					'type'         => 'string',
				),
				'customer_email'   => array(
					'description'  => __( 'Customer email address.', 'affiliatewp-affiliate-portal' ),
					'type'         => 'string',
				),
				'customer_phone'   => array(
					'description'  => __( 'Customer phone number.', 'affiliatewp-affiliate-portal' ),
					'type'         => 'string',
				),
				'customer_shipping_address' => array(
					'description'  => __( 'Customer shipping address.', 'affiliatewp-affiliate-portal' ),
					'type'         => 'string',
				),
				'customer_billing_address'  => array(
					'description'  => __( 'Customer billing address.', 'affiliatewp-affiliate-portal' ),
					'type'         => 'string',
				),
				'referral_amount'  => array(
					'description'  => __( 'Referral amount.', 'affiliatewp-affiliate-portal' ),
					'type'         => 'string',
				),
			),
		);

		return $schema;
	}
}
