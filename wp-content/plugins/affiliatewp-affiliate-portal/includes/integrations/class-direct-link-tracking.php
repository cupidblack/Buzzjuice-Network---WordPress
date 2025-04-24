<?php
/**
 * Integrations: Direct Link Tracking add-on
 *
 * @package     AffiliateWP Affiliate Dashboard
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Integrations;

use AffiliateWP_Affiliate_Portal\Core;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces;

/**
 * Class for integrating the Direct Link Tracking add-on.
 *
 * @since 1.0.0
 */
class Direct_Link_Tracking implements Interfaces\Integration {

	use Core\Traits\REST_Support;

	/**
	 * @inheritDoc
	 */
	public function init() {
		// Enqueue Direct Link script.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		// Register Direct Link view.
		add_action( 'affwp_portal_views_registry_init', array( $this, 'register_view' ) );

		$this->bootstrap_rest_support();
	}

	/**
	 * Registers integration rest routes.
	 *
	 * @since 1.0.0
	 *
	 * @see   register_rest_route()
	 */
	public function register_rest_routes() {
		$namespace = $this->namespace . '/integrations';

		// Get Links.
		register_rest_route( $namespace, 'direct-link-tracking/get-links', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
				'callback'            => array( $this, 'rest_get_direct_link_tracking_links' ),
			),
		) );

		// Save Links.
		register_rest_route( $namespace, 'direct-link-tracking/save-links', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
				'callback'            => array( $this, 'rest_get_direct_link_tracking_save_links' ),
			),
		) );

		// Dismiss Notice.
		register_rest_route( $namespace, 'direct-link-tracking/dismiss-notice', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
				'callback'            => array( $this, 'rest_dismiss_notice' ),
			),
		) );

		// Validate.
		register_rest_route( $namespace, 'direct-link-tracking/validate', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
				'callback'            => array( $this, 'rest_validate' ),
			),
		) );

		// Delete link.
		register_rest_route( $namespace, 'direct-link-tracking/links/(?P<url_id>\d+)', array(
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
				'callback'            => array( $this, 'rest_delete_link' ),
			),
		) );

	}

	/**
	 * Gets direct link tracking links.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Data from the request.
	 * @return array Array with list of links and notices.
	 */
	public function rest_get_direct_link_tracking_links( \WP_REST_Request $request ) {
		// Get affiliate ID.
		$affiliate_id = affwp_get_affiliate_id();

		// Get rejected.
		$rejected = $this->get_direct_link_tracking_rejected( $affiliate_id );

		// Direct Links.
		$direct_links = $this->get_direct_link_tracking_links( $affiliate_id );

		return array(
			'links'    => $direct_links,
			'rejected' => $rejected,
		);
	}

	/**
	 * Saves direct link tracking links.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Data from the request.
	 * @return array Array with list of links and notices.
	 */
	public function rest_get_direct_link_tracking_save_links( \WP_REST_Request $request ) {
		// Get affiliate ID.
		$affiliate_id = affwp_get_affiliate_id();

		// Prepare links for affwp_dlt_update_direct_links.
		$links = $request['links'];
		$args  = array(
			'affiliate_id'                  => $affiliate_id,
			'direct_link_tracking_urls'     => array(),
			'direct_link_tracking_urls_new' => array(),
		);
		foreach ( $links as $link ) {
			if ( empty( $link['url_id'] ) ) {
				$args['direct_link_tracking_urls_new'][] = $link['url'];
			} else {
				$args['direct_link_tracking_urls'][ $link['url_id'] ] = $link['url'];
			}
		}

		// Just for compatibility with DLT Add-on.
		$_POST['direct_link_tracking_urls']     = $args['direct_link_tracking_urls'];
		$_POST['direct_link_tracking_urls_new'] = $args['direct_link_tracking_urls_new'];

		$success = affwp_dlt_update_direct_links( $args );

		// Get rejected.
		$rejected = $this->get_direct_link_tracking_rejected( $affiliate_id );

		// Direct Links.
		$direct_links = $this->get_direct_link_tracking_links( $affiliate_id );

		return array(
			'success'  => $success,
			'links'    => $direct_links,
			'rejected' => $rejected,
		);
	}

	/**
	 * Dismiss direct link tracking notice.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Data from the request.
	 */
	public function rest_dismiss_notice( \WP_REST_Request $request ) {
		$url_id = $request['url_id'];

		if ( ! empty( $url_id ) ) {
			$direct_link = affwp_dlt_get_direct_link( $url_id );
			if ( ! empty( $direct_link->url_old ) ) {
				// Replace link url with old url.
				affwp_dlt_update_direct_link( $url_id, array( 'url' => $direct_link->url_old, 'status' => 'active', 'url_old' => '' ) );
			}
		}

		return true;
	}

	/**
	 * Validates a domain.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Data from the request.
	 */
	public function rest_validate( \WP_REST_Request $request ) {
		$url = $request['url'];

		$validated_url = affwp_dlt_validate_url( $url );

		if ( is_array( $validated_url ) ) {
			return array(
				'success' => false,
				'error'   => $validated_url['reason'],
			);
		} else {
			return array(
				'success' => true,
				'error'   => '',
			);
		}
	}

	/**
	 * Deletes a domain by url ID.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Data from the request.
	 */
	public function rest_delete_link( \WP_REST_Request $request ) {
		$url_id  = $request['url_id'];
		$success = false;

		if ( ! empty( $url_id ) ) {
			$success = affwp_dlt_delete_direct_link( $url_id );
		}

		return $success;
	}

	/**
	 * Gets list of rejected domains for direct link tracking.
	 *
	 * @since 1.0.4
	 *
	 * @param int $affiliate_id Affiliate ID.
	 * @return array List of rejected domains, if any.
	 */
	public function get_direct_link_tracking_rejected( $affiliate_id ) {
		// Get list of rejected direct links.
		$rejected_direct_links = affwp_dlt_get_direct_links( array( 'affiliate_id' => $affiliate_id, 'status' => 'rejected' ) );

		// Get list of domains not approved.
		$rejected = array();
		if ( $rejected_direct_links ) {
			foreach ( $rejected_direct_links as $direct_link ) {
				$rejected[] = sprintf( '%s', $direct_link->url );
			}
		}

		return $rejected;
	}

	/**
	 * Gets list of links for direct link tracking.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id Affiliate ID.
	 * @return array List of links, if any.
	 */
	public function get_direct_link_tracking_links( $affiliate_id ) {
		// Get all direct links for affiliate_id.
		$direct_links = affwp_dlt_get_direct_links( array( 'affiliate_id' => $affiliate_id ) );
		if ( empty( $direct_links ) ) {
			$direct_links = array();
		}

		$direct_links = array_map( function( $link ) {
			// Add errors array.
			$link->errors = array();

			// If rejected and old url exists, return old url instead.
			if ( 'rejected' === $link->status && ! empty( $link->url_old ) ) {
				$link->url = $link->url_old;
			}

			// Add status message for each link.
			ob_start();
			do_action( 'affwp_direct_link_tracking_show_notices', $link );
			$status_message = ob_get_clean();

			$link->status_message = wp_strip_all_tags( $status_message );

			// Replace dismiss notice link with an Alpine link.
			$dismiss_label = __( "Dismiss this notice", "affiliatewp-affiliate-portal" );
			if ( strpos( $link->status_message, $dismiss_label ) !== false ) {
				$dismiss_link = "<br><a href='#' class='underline' @click.prevent='dismiss($link->url_id)'>$dismiss_label</a>";
				$link->status_message = str_replace( $dismiss_label, $dismiss_link, $link->status_message );
			}

			return $link;
		}, $direct_links );

		return $direct_links;
	}

	/**
	 * Register Direct Link Tracking Add-on Scripts.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts() {
		if ( affwp_is_affiliate_portal( 'direct-link-tracking' ) ) {
			affwp_enqueue_script( 'affwp-portal-direct-link', 'affiliate_portal' );
		}
	}

	/**
	 * Registers Direct Link Tracking add-on view.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Views_Registry $registry Views registry.
	 */
	public function register_view( $registry ) {
		// Get current affiliate ID.
		$affiliate_id = (int) affwp_get_affiliate_id();

		if ( ! affwp_dlt_allow_direct_link_tracking( $affiliate_id ) ) {
			// Don't load the Direct Link view.
			return;
		}

		// Get max num of links.
		$max_links = affwp_dlt_get_domain_limit( $affiliate_id );

		// Direct Link Section.
		$sections = array(
			'direct-link-tracking' => array(
				'view_id'       => 'direct-link-tracking',
				'label'         => __( 'Direct link domains', 'affiliatewp-affiliate-portal' ),
				'priority'      => 5,
				'desc'          => __( 'Direct links allow you to link directly to this site, from your own website, without an affiliate link.', 'affiliatewp-affiliate-portal' ),
				'wrapper'       => true,
				'submit_label'  => __( 'Save direct links', 'affiliatewp-affiliate-portal' ),
				'submit_alpine' => array(
					'@click.prevent'  => 'submit()',
					'x-spread'        => '{}',
					'x-bind:disabled' => 'typeof valid !== "undefined" && valid === false',
				),
				'form_alpine'   => array(
					'x-data'   => 'AFFWP.portal.directLink.default()',
					'x-init'   => "maxLinks = ${max_links}; init()",
					'x-spread' => '{}',
				),
				'icon_alpine'   => array(
					'x-show' => 'isLoading',
				)
			),
		);

		// Direct Link Controls.
		$controls = array();

		// Wrapper control.
		$controls[] = new Controls\Wrapper_Control( array(
			'view_id' => 'direct-link-tracking',
			'section' => 'wrapper',
			'atts'    => array(
				'id' => 'direct-link-tracking-wrapper',
			),
		) );

		// Intro paragraph control.
		$intro = _n(
			'Submit your domain or individual domain path below for approval.',
			'Submit your domain or individual domain paths below for approval.',
			$max_links,
			'affiliatewp-affiliate-portal'
		);

		$controls[] = new Controls\Paragraph_Control( array(
			'id'      => 'affwp-direct-link-tracking-note',
			'view_id' => 'direct-link-tracking',
			'section' => 'direct-link-tracking',
			'args'    => array(
				'text' => $intro,
			),
		) );

		// SSL Note control.
		if ( ! is_ssl() ) {
			$non_ssl_note = _n(
				'Note, your domain must be HTTP (not HTTPS) based.',
				'Note, your domains must be HTTP (not HTTPS) based.',
				$max_links,
				'affiliatewp-affiliate-portal'
			);
			$controls[] = new Controls\Paragraph_Control( array(
				'id'      => 'affwp-direct-link-tracking-note-ssl',
				'view_id' => 'direct-link-tracking',
				'section' => 'direct-link-tracking',
				'args'    => array(
					'text' => $non_ssl_note,
				),
			) );
		}

		// Links were updated notice.
		$controls[] = new Controls\Paragraph_Control( array(
			'id'      => 'affwp-direct-link-tracking-update-notice',
			'view_id' => 'direct-link-tracking',
			'section' => 'direct-link-tracking',
			'alpine' => array(
				'x-show' => 'showUpdateNotice',
			),
			'atts' => array(
				'class' => array( 'p-2', 'rounded-sm', 'bg-yellow-50', 'border', 'border-yellow-200', 'text-sm' ),
			),
			'args' => array(
				'text' => __( 'Your direct link(s) have been updated', 'affiliatewp-affiliate-portal' ),
			),
		) );

		// Invalid submission control.
		$controls[] = new Controls\Paragraph_Control( array(
			'id'      => 'direct-link-tracking-error-invalid',
			'view_id' => 'direct-link-tracking',
			'section' => 'direct-link-tracking',
			'atts'    => array(
				'class' => array( 'p-2', 'rounded-sm', 'bg-red-50', 'border', 'border-red-700', 'text-sm' ),
			),
			'args'    => array(
				'text' => __( 'An invalid domain was submitted.', 'affiliatewp-affiliate-portal' ),
			),
			'alpine'  => array(
				'x-show' => 'showInvalidSubmission',
			),
		) );

		// Rejected domains control.
		$controls[] = new Controls\Paragraph_Control( array(
			'id'      => 'direct-link-tracking-error-rejected',
			'view_id' => 'direct-link-tracking',
			'section' => 'direct-link-tracking',
			'atts'    => array(
				'class' => array( 'p-2', 'rounded-sm', 'bg-red-50', 'border', 'border-red-700', 'text-sm' ),
			),
			'alpine'  => array(
				'x-show' => 'rejected.length > 0',
				'x-html' => '`' . __( 'You have domains that were not approved:', 'affiliatewp-affiliate-portal' ) . '<br>${rejected}`',
			),
		) );

		// Template control for listing all links.
		$validation_class = array( 'block', 'text-red-600', 'setting', 'text-control', 'mt-1', 'text-sm' );
		$controls[]       = new Controls\Template_Control( array(
			'id'      => 'affwp-direct-link-tracking-template',
			'view_id' => 'direct-link-tracking',
			'section' => 'direct-link-tracking',
			'alpine'  => array(
				'x-for' => '(link, linkIndex) in links',
				':key'  => 'link.url_id',
			),
			'atts' => array(
				'class' => array( 'mb-10' ),
			),
			'args' => array(
				'controls' => array(
					// Link label.
					new Controls\Label_Control( array(
						'id'     => 'affwp-direct-link-tracking-label',
						'alpine' => array(
							':id'    => '`affwp-direct-link-tracking-label-${linkIndex}`',
							'x-text' => '"' . __( 'Direct link domain', 'affiliatewp-affiliate-portal' ) . '" + (maxLinks > 1 ? " #" + (linkIndex + 1) : "")',
						),
						'atts' => array(
							'class' => array( 'text-sm', 'font-medium', 'leading-5', 'text-gray-700' ),
						),
					) ),

					// Remove link button.
					new Controls\Button_Control( array(
						'id'     => 'affwp-direct-link-tracking-remove',
						'alpine' => array(
							':id'       => '`affwp-direct-link-tracking-remove-${linkIndex}`',
							'@click'    => 'removeLink(linkIndex)',
							'x-text'    => 'link.isRemoving ? "removing..." : "remove"',
							':disabled' => 'link.isRemoving',
							'x-show'    => 'maxLinks > 1',
						),
						'args'   => array(
							'std_colors'  => false,
							'std_classes' => false,
						),
						'atts'   => array(
							'value' => 'remove',
							'class' => array(
								'ml-2',
								'text-xs',
								'font-medium',
								'focus:outline-none',
								'focus:shadow-outline-red',
								'transition duration-150',
								'ease-in-out',
								'text-red-600',
								'hover:text-red-500',
								'active:text-red-600',
							),
						),
					) ),

					// Link input.
					new Controls\Text_Input_Control( array(
						'id'     => 'direct-link-tracking-url',
						'alpine' => array(
							'x-model'       => 'link.url',
							'@input'        => 'validateFrontend(linkIndex)',
							':id'           => '`direct-link-tracking-url-${link.url_id}`',
							':name'         => '`direct_link_tracking_urls${!link.url_id ? "_new" : ""}[${link.url_id}]`',
							':disabled'     => 'link.status === "pending" || link.status === "inactive"',
							':class'        => '{ "bg-gray-100": link.status === "pending" || link.status === "inactive",
																"border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:shadow-outline-red": hasErrors(link) }',
							':aria-invalid' => 'hasErrors(link)',
							'x-spread'      => '',
						),
						'args'     => array(
							'error'         => array(
								'alpine' => array(
									'x-show' => 'hasErrors(link)',
								),
							),
						),
					) ),

					// Validating domain notice.
					new Controls\Text_Control( array(
						'id'     => 'direct-link-tracking-validating-url',
						'atts'   => array(
							'class' => $validation_class,
						),
						'args'  => array(
							'text' => __( 'Validating domain, please wait...', 'affiliatewp-affiliate-portal' ),
						),
						'alpine' => array(
							':id'    => '`direct-link-tracking-validating-url-${linkIndex}`',
							'x-show' => 'link.isValidatingUrl',
						),
					) ),

					// Empty error notice.
					new Controls\Text_Control( array(
						'id'     => 'direct-link-tracking-error-empty',
						'atts'   => array(
							'class' => $validation_class,
						),
						'args'  => array(
							'text' => __( 'Domains cannot be empty.', 'affiliatewp-affiliate-portal' ),
						),
						'alpine' => array(
							':id'    => '`direct-link-tracking-error-empty-${linkIndex}`',
							'x-show' => 'hasError(link, "empty")',
						),
					) ),

					// Duplicated error notice.
					new Controls\Text_Control( array(
						'id'     => 'direct-link-tracking-error-duplicated',
						'atts'   => array(
							'class' => $validation_class,
						),
						'args'  => array(
							'text' => __( 'This domain is duplicated.', 'affiliatewp-affiliate-portal' ),
						),
						'alpine' => array(
							':id'    => '`direct-link-tracking-error-duplicated-${linkIndex}`',
							'x-show' => 'hasError(link, "duplicated")',
						),
					) ),

					// Invalid url error notice.
					new Controls\Text_Control( array(
						'id'     => 'direct-link-tracking-error-invalid',
						'atts'   => array(
							'class' => $validation_class,
						),
						'args'   => array(
							'text' => __( 'Domain was entered incorrectly.', 'affiliatewp-affiliate-portal' ),
						),
						'alpine' => array(
							':id'    => '`direct-link-tracking-error-invalid-${linkIndex}`',
							'x-show' => 'hasError(link, "invalid")',
						),
					) ),

					// Add-on validation error notice.
					new Controls\Text_Control( array(
						'id'     => 'direct-link-tracking-error-addon',
						'atts'   => array(
							'class' => $validation_class,
						),
						'alpine' => array(
							':id'    => '`direct-link-tracking-error-addon-${linkIndex}`',
							'x-show' => 'hasError(link, "addon")',
							'x-text' => 'link.errors.addonReason',
						),
					) ),

					// Link status (approved, rejected, pending, inactive).
					new Controls\Paragraph_Control( array(
						'id'     => 'affwp-direct-link-tracking-status',
						'alpine' => array(
							':id'    => '`affwp-direct-link-tracking-status-${linkIndex}`',
							'x-show' => 'link.status_message',
							'x-html' => 'link.status_message',
							':class' => '{"bg-yellow-50": "pending" === link.status, "border-yellow-200" : "pending" === link.status, "bg-red-50" : "rejected" === link.status, "border-red-700" : "rejected" === link.status}',
						),
						'atts'   => array(
							'class' => array( 'mt-2', 'p-2', 'rounded-sm', 'border', 'text-sm' ),
						),
					) ),
				),
			),
		) );

		// Add link control.
		$controls[] = new Controls\Button_Control( array(
			'id'      => 'affwp-direct-link-tracking-add',
			'view_id' => 'direct-link-tracking',
			'section' => 'direct-link-tracking',
			'atts'    => array(
				'value' => __( 'Add new domain', 'affiliatewp-affiliate-portal' ),
			),
			'alpine'  => array(
				'x-show' => 'links.length < maxLinks && maxLinks > 1',
				'@click' => 'addDomain()',
			),
		) );

		// Register Direct Link view.
		$registry->register_view( 'direct-link-tracking', array(
			'label'    => __( 'Direct Links', 'affiliatewp-affiliate-portal' ),
			'route'    => array(
				'slug' => 'direct-links',
			),
			'icon'     => 'arrow-circle-right',
			'sections' => $sections,
			'controls' => $controls,
		) );
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
		return array();
	}

}
