<?php
/**
 * Views: Creatives View
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 *
 * phpcs:disable PEAR.Functions.FunctionCallSignature.ContentAfterOpenBracket -- Format OK.
 * phpcs:disable PEAR.Functions.FunctionCallSignature.CloseBracketLine -- Format OK.
 * phpcs:disable PEAR.Functions.FunctionCallSignature.MultipleArguments -- Format OK.
 * phpcs:disable PEAR.Functions.FunctionCallSignature.CloseBracketLine -- Format OK.
 * phpcs:disable Squiz.PHP.DisallowMultipleAssignments.Found -- Used below to make more efficent return.
 * phpcs:disable PEAR.Functions.FunctionCallSignature.EmptyLine -- Empty lines OK for format in this file.
 * phpcs:disable PEAR.Functions.FunctionCallSignature.FirstArgumentPosition -- Formatting OK.
 */

// phpcs:disable PEAR.Functions.FunctionCallSignature.CloseBracketLine -- Spacing okay below.

namespace AffiliateWP_Affiliate_Portal\Core\Components\Views;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces\View;
use AffiliateWP_Affiliate_Portal\Core\Components\Portal;
use \AffiliateWP_Affiliate_Portal\Core\Components\Controls\Base_Control;

#[\AllowDynamicProperties]

/**
 * Sets up the Creatives view.
 *
 * @since 1.0.0
 */
class Creatives_View implements View {

	/**
	 * View slug.
	 *
	 * @since 1.2.2
	 *
	 * @var string
	 */
	public string $slug = 'creatives';

	/**
	 * Construct
	 *
	 * @since 1.2.2
	 */
	public function __construct() {
		$this->register_connectables();
		$this->hooks();
	}

	/**
	 * Create a unique slug from a title.
	 *
	 * Yes, this does NOT use `sanitize_title_with_dashes()` since our
	 * titles are not as restricted as Posts and we may have special
	 * characters.
	 *
	 * @since 1.2.2
	 *
	 * @param string $title The title (of the group here).
	 *
	 * @return string
	 */
	private function create_slug_from_title( string $title ) : string {

		return esc_attr( strtolower(
			preg_replace(
				'/[^A-Za-z0-9]/',
				'-',
				html_entity_decode( $title )
			)
		) );
	}

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {
		return array(
			$this->slug => array(
				'priority' => 1,
				'wrapper'  => false,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
			),
		);
	}

	/**
	 * Get the selected category slug from the URL.
	 *
	 * @since 1.2.2
	 *
	 * @return string
	 */
	private function get_selected_filter_slug() : string {

		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return ''; // Fail gracefully (probably for WP CLI).
		}

		// Match the slug in the URL (taking into account the pagination being present too).
		preg_match( "/\/{$this->slug}\/([A-Za-z0-9\-\_]+)\/?[\d+]?\/?/", $_SERVER['REQUEST_URI'], $matches );

		if ( ! isset( $matches[1] ) ) {
			return ''; // We captured nothing.
		}

		if ( ! in_array( $matches[1], array_keys( $this->get_nonempty_creative_category_options() ), true ) ) {
			return ''; // What we captured isn't one of the selectable options.
		}

		return $matches[1];
	}

	/**
	 * Get all the creative category groups (as options).
	 *
	 * @since  1.2.2
	 *
	 * @return array
	 */
	private function get_nonempty_creative_category_options() : array {

		static $cache = null;

		if ( is_array( $cache ) ) {
			return $cache;
		}

		if ( ! isset( affiliate_wp()->groups ) ) {

			// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- We can't get all the groups.
			return $cache = array();
		}

		$options = array(

			// Add an option that gives all categories.
			'' => __( 'All Categories', 'affiliatewp-affiliate-portal' ),
		);

		foreach ( $this->get_creative_category_groups() as $group ) {

			// Tell the browser where to navigate.
			$options[ $this->create_slug_from_title( $group->get_title() ) ] = esc_html( $group->get_title() );
		}

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- We are just caching the result at runtime.
		return $cache = $options;
	}

	/**
	 * Get the slug of the affiliate area page.
	 *
	 * @since 1.2.2
	 *
	 * @return string
	 */
	private function get_affiliate_area_slug() : string {

		static $cache = '';

		if ( ! empty( $cache ) ) {
			return $cache;
		}

		$affiliate_area_id = affwp_get_affiliate_area_page_id();

		if ( ! is_numeric( $affiliate_area_id ) ) {
			return '';
		}

		// Get the slug for the affiliate area page.
		return $cache = get_page_uri( $affiliate_area_id );
	}

	/**
	 * Get the base URL for this view.
	 *
	 * @since 1.2.2
	 *
	 * @param string $append A string to append to the end of the URL after the last /.
	 *
	 * @return string
	 */
	private function get_base_url( string $append = '' ) : string {

		$append = trim( $append, '/' );

		// Get the slug for the affiliate area page.
		$affiliate_area_page_slug = $this->get_affiliate_area_slug();

		if ( ! is_string( $affiliate_area_page_slug ) || empty( $affiliate_area_page_slug ) ) {

			affiliate_wp()->utils->log( 'Unable to get the affiliate area page slug for the Affiliate Portal (Creatives View), it may not be set.' );

			// This will fail, but if an Affiliate Area is not set, the Portal will not work either.
			return home_url();
		}

		// No filtering slug selected.
		return empty( $append )
			? home_url( "{$affiliate_area_page_slug}/{$this->slug}/" ) // No appending to the base url.
			: home_url( "{$affiliate_area_page_slug}/{$this->slug}/{$append}" );
	}

	/**
	 * Get the per-page setting in the Portal admin.
	 *
	 * @since 1.2.2
	 *
	 * @return int
	 */
	private function get_per_page_setting() :int {

		static $cache = null;

		if ( ! is_null( $cache ) && is_numeric( $cache ) ) {
			return $cache;
		}

		$creatives_per_page_setting = affiliate_wp()->settings->get( 'portal_creatives_per_page' );

		return $cache = (
			! empty( $creatives_per_page_setting ) &&
			is_numeric( $creatives_per_page_setting ) &&
			intval( $creatives_per_page_setting ) > 0
		)
			? intval( $creatives_per_page_setting )
			: 30;
	}

	/**
	 * Get the QR Code of the Creative.
	 *
	 * @since 1.2.4
	 *
	 * @param \AffWP\Creative $creative The Creative.
	 *
	 * @return string
	 */
	private function get_card_qrcode( \AffWP\Creative $creative ) : string {

		if ( 'qr_code' !== $creative->type ?? '' ) {
			return '';
		}

		if ( ! method_exists( $creative, 'get_preview' ) ) {
			return '';
		}

		return $creative->get_preview();
	}

	/**
	 * Get a creative's image.
	 *
	 * @since 1.2.2
	 *
	 * @param mixed $creative Creative object.
	 *
	 * @return string URL to image, or empty string if none.
	 */
	private function get_card_image( $creative ) : string {

		if ( ! isset( $creative->image ) ) {
			return '';
		}

		$image_id = attachment_url_to_postid( $creative->image );

		if ( 0 === $image_id && is_string( $creative->image ) ) {
			return $creative->image;
		}

		$src = wp_get_attachment_image_src( $image_id, 'full' );

		return isset( $src[0] ) ? $src[0] : '';
	}

	/**
	 * Retrieves the view controls.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_controls() {

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return array();
		}

		/**
		 * Filter the query args used to get creatives to show.
		 *
		 * @since 1.2.2
		 *
		 * @param array $args The arguments used for `get_creatives()`.
		 */
		$query_args = apply_filters(
			'affwp_creatives_view_query_args',
			array_merge(

				// Common arguments.
				array(
					'status' => 'active',
				),

				// Maybe add filtering arguments.
				empty( $this->get_selected_filter_slug() )
					? array() // No filtering.

					// Filtering arguments.
					: array(

						// Get creatives that are connected to this group.
						'creative_id' => affiliate_wp()->connections->get_connected(
							'creative',
							'group',
							intval( $this->get_group_id_by_slug( $this->get_selected_filter_slug() ) ),
						),
					),
			)
		);

		$creatives_per_page = $this->get_per_page_setting();

		$total_creatives = affiliate_wp()->creatives->get_creatives(

			// Arguments filtered above.
			$query_args,

			// Get the count only.
			true
		);

		$groups = $this->get_nonempty_creative_category_options();

		return array_merge(

			// Filtering.
			is_array( $groups ) && count( $groups ) >= 2

				// Filter, we have groups...
				? array(

					// Select for filtering.
					new Controls\Select_Control(
						array(
							'priority' => 3,
							'id'       => 'filter',
							'view_id'  => $this->slug,
							'section'  => $this->slug,
							'atts'     => array(
								'class'         => array(
									'form-select',
									'mb-4',
									'w-64',
									'block',
								),
								'data-base-url' => $this->get_base_url(),
							),
							'alpine'   => array(
								'x-spread' => '', // Disable.
								'x-data'   => 'AFFWP.portal.creatives.default()',
								'x-init'   => "\$el.value='" . $this->get_selected_filter_slug() . "';",
							),
							'args'     => array(
								'options' => $groups,
							),
						)
					),

					// Submit button.
					new Controls\Submit_Button_Control(
						array(
							'priority' => 4,
							'id'       => 'filter-submit',
							'view_id'  => $this->slug,
							'section'  => $this->slug,
							'atts'     => array(
								'class' => array(
									'block',
									'h-10',
									'relative',
								),
							),
							'alpine'   => array(
								'x-spread' => '', // Disable.
								'x-data'   => 'AFFWP.portal.creatives.default()',
								'@click'   => 'filter($event)',
							),
							'args'     => array(
								'value_atts' => array(
									'value' => __( 'Filter', 'affiliatewp-affiliate-portal' ),
								),
							),
						)
					),
				)

				// No groups, don't add the filter.
				: array(),

			// Cards & Pagination.
			array_merge(

				// Cards.
				array(

					// Wrapper.
					new Controls\Wrapper_Control( array(
						'id'      => 'wrapper',
						'view_id' => $this->slug,
						'section' => 'wrapper',
						'atts'    => array(
							'id' => 'affwp-affiliate-portal-creatives',
						),
					) ),

					// Cards.
					new Controls\Card_Group_Control( array(
						'id'       => 'creatives_card_group',
						'view_id'  => $this->slug,
						'section'  => $this->slug,
						'priority' => 5,
						'args'     => array(
							'columns' => 4,

							// Cards in the control.
							'cards'   => array_map(
								function( $creative ) {

									// Card.
									return new Controls\Creative_Card_Control( array(
										'id'      => 'creative_card',
										'view_id' => $this->slug,
										'section' => $this->slug,
										'args'    => array(
											'qr_code'     => $this->get_card_qrcode( $creative ),
											'image'       => $this->get_card_image( $creative ),
											'text'        => $creative->text,
											'url'         => $creative->url,
											'description' => $creative->description ? $creative->description : '',
											'creative_id' => $creative->creative_id,
										),
									) );
								},

								// Creatives we'll conver to cards.
								affiliate_wp()->creatives->get_creatives(
									array_merge(

										// Arguments filtered above.
										$query_args,

										// Add our pagination arguments everytime.
										array(
											'number' => $creatives_per_page,
											'offset' => $creatives_per_page * ( $this->get_current_page() - 1 ),
										)
									)
								),
							),
						),
					) ),
				),

				// Pagination.
				is_numeric( $total_creatives ) && intval( $total_creatives ) >= 1 ? array(

					// Pagination Control.
					new Controls\Pagination_Control( array(
						'id'       => 'creatives_pagination',
						'view_id'  => $this->slug,
						'section'  => $this->slug,
						'priority' => 9,
						'args'     => array(
							'base_url'     => untrailingslashit( $this->get_base_url( $this->get_selected_filter_slug() ) ),
							'current_page' => absint( $this->get_current_page() ),
							'per_page'     => absint( $creatives_per_page ),
							'total'        => absint( $total_creatives ),
						),
					) ),
				) : array(), // No creatives, no pagination.
			),
		);
	}

	/**
	 * Get the creative category groups (non-empty).
	 *
	 * @since 1.2.2
	 *
	 * @return array
	 */
	private function get_creative_category_groups() : array {

		$cache = null;

		if ( is_array( $cache ) ) {
			return $cache;
		}

		return $cache = array_filter(
			affiliate_wp()->groups->get_groups(
				array(
					'fields' => 'objects',
					'type'   => 'creative-category',
				)
			),
			function( $group ) {

				if ( ! is_a( $group, '\AffiliateWP\Groups\Group' ) ) {
					return false; // Exclude group.
				}

				$creatives_connected_to_group = affiliate_wp()->connections->get_connected(
					'creative',
					'group',
					intval( $group->group_id )
				);

				return count( $creatives_connected_to_group ) >= 1;
			}
		);
	}

	/**
	 * Get the current page.
	 *
	 * I could not figure out how to get the var current_page from e.g.
	 * `$wp_query` or anything else, so I opted for this method instead
	 * directly from the URL.
	 *
	 * @since 1.2.2
	 *
	 * @return int
	 *
	 * @throws \Exception If `$_SERVER['REQUEST_URI']` is not set.
	 */
	private function get_current_page() : int {

		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return 1;
		}

		// Always at the end of the URL, e.g. /#/?$.
		preg_match( '/\/(\d+)\/?$/', $_SERVER['REQUEST_URI'], $matches );

		if ( ! isset( $matches[1] ) ) {
			return 1;
		}

		return is_numeric( $matches[1] ) && intval( $matches[1] ) > 1
			? intval( $matches[1] )
			: 1;
	}

	/**
	 * Get a group id by slug (only of the groups shown on the creatives page).
	 *
	 * @since 1.2.2
	 *
	 * @param string $slug The slug.
	 *
	 * @return string The group ID for that slug.
	 *
	 * @throws \Exception If you pass a slug that isn't in the results for `self::get_creative_category_groups()`.
	 */
	private function get_group_id_by_slug( string $slug ) : int {

		foreach ( $this->get_creative_category_groups() as $group ) {

			if ( $this->create_slug_from_title( $group->get_title() ) !== $slug ) {
				continue;
			}

			return intval( $group->group_id );
		}

		throw new \Exception( 'You can only ask for a group id by slug that would be in self::get_creative_category_groups().' );
	}

	/**
	 * Hooks
	 *
	 * @since  1.2.2
	 */
	private function hooks() {

		if ( is_admin() ) {
			return; // Don't even add them.
		}

		// We're adding form fields, but we don't want our page to be a full form with a submit button.
		add_filter( 'affwp_portal_has_form_controls_creatives_creatives', '__return_false' );

		add_filter( 'affwp_portal_select_section_classes', array( $this, 'control_classes' ), 10, 3 );
		add_filter( 'affwp_portal_submit_section_classes', array( $this, 'control_classes' ), 10, 3 );
	}

	public function control_classes( array $classes, Base_Control $control ) : array {

		if (
			(
				'filter' !== $control->get_id() &&
				'filter-submit' !== $control->get_id()
			) ||
			'creatives' !== $control->get_view_id()
		) {
			return $classes;
		}

		return array_merge(
			$classes,

			// Both.
			array(
				'inline-block',
				'mt-5',
			),

			// Select.
			'filter' === $control->get_id()
				? array(

				)
				: array(),

			// Submit button.
			'filter-submit' === $control->get_id()
				? array(
					'ml-2',
				)
				: array(),
		);
	}

	/**
	 * Register the creative and group connectable (for the frontend).
	 *
	 * @since 1.2.2
	 */
	private function register_connectables() : void {

		affiliate_wp()->connections->register_connectable(
			array(
				'name'   => 'creative',
				'table'  => affiliate_wp()->creatives->table_name,
				'column' => affiliate_wp()->creatives->primary_key,
			)
		);

		affiliate_wp()->connections->register_connectable(
			array(
				'name'   => 'group',
				'table'  => affiliate_wp()->groups->table_name,
				'column' => affiliate_wp()->groups->primary_key,
			)
		);
	}
}
