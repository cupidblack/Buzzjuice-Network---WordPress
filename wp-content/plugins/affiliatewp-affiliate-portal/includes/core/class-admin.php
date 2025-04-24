<?php
/**
 * Core: Admin
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core;

use AffiliateWP_Affiliate_Portal\Core\Menu_Links;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class.
 *
 * @since 1.0.0
 */
class Admin {

	/**
	 * Admin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Settings tab.
		add_filter( 'affwp_settings_tabs', array( $this, 'setting_tab' ) );

		// Settings.
		add_filter( 'affwp_settings', array( $this, 'register_settings' ) );

		// Menu links saving.
		add_filter( 'pre_update_option_affwp_settings', array( $this, 'pre_update_option' ), 10, 2 );

		// Sanitization.
		add_filter( 'affwp_settings_sanitize', array( $this, 'sanitize_creatives_per_page' ), 10, 2 );
		add_filter( 'affwp_settings_sanitize', array( $this, 'sanitize_items_per_page'     ), 10, 2 );
	}

	/**
	 * Registers the new settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Setting tabs.
	 * @return array Array of settings tabs.
	 */
	public function setting_tab( $tabs ) {
		$tabs['affiliate_portal'] = __( 'Affiliate Portal', 'affiliatewp-affiliate-portal' );
		return $tabs;
	}

	/**
	 * Registers the settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings Affiliate Portal settings.
	 * @return array Array of settings.
	 */
	public function register_settings( $settings ) {

		$settings['affiliate_portal'] = array(

			'portal_enabled'                  => array(
				'name' => __( 'Enable the Affiliate Portal', 'affiliatewp-affiliate-portal' ),
				'desc' => __( 'Check this box to enable the Affiliate Portal.', 'affiliatewp-affiliate-portal' ),
				'type' => 'checkbox',
			),
			'portal_allow_affiliate_feedback' => array(
				'name' => __( 'Affiliate Feedback', 'affiliatewp-affiliate-portal' ),
				'desc' => __( 'Allow affiliate feedback.<p class="description">Enabling this option will allow your affiliates to submit anonymous feedback directly to us from within the affiliate portal. Any feedback collected will be used to help improve the add-on and overall portal experience which all your affiliates will benefit from. No personal data will be collected.</p>', 'affiliatewp-affiliate-portal' ),
				'type' => 'checkbox',
			),
			'portal_logo' => array(
				'name' => __( 'Logo', 'affiliate-wp' ),
				'desc' => __( 'Upload or choose a logo to be displayed at the top of Affiliate Portal.', 'affiliatewp-affiliate-portal' ),
				'type' => 'upload',
			),
			'portal_sharing_header'           => array(
				'name' => __( 'Referral Link Sharing', 'affiliatewp-affiliate-portal' ),
				'type' => 'header',
			),
			'portal_sharing_options'          => array(
				'name'    => __( 'Sharing Options', 'affiliatewp-affiliate-portal' ),
				'type'    => 'multicheck',
				'options' => array(
					'twitter'  => __( 'Twitter', 'affiliatewp-affiliate-portal' ),
					'facebook' => __( 'Facebook', 'affiliatewp-affiliate-portal' ),
					'email'    => __( 'Email', 'affiliatewp-affiliate-portal' ),
				),
			),
			'portal_sharing_twitter_text'     => array(
				'name' => __( 'Twitter Text', 'affiliatewp-affiliate-portal' ),
				'desc' => '<p class="description">' . __( 'The default text that will show when an affiliate shares to Twitter. Leave blank to use Site Title.', 'affiliatewp-affiliate-portal' ) . '</p>',
				'type' => 'text',
			),
			'portal_sharing_email_subject'    => array(
				'name' => __( 'Email Sharing Subject', 'affiliatewp-affiliate-portal' ),
				'desc' => '<p class="description">' . __( 'The default text that will show in the email subject line. Leave blank to use Site Title.', 'affiliatewp-affiliate-portal' ) . '</p>',
				'type' => 'text',
			),
			'portal_sharing_email_body'       => array(
				'name' => __( 'Email Sharing Message', 'affiliatewp-affiliate-portal' ),
				'desc' => '<p class="description">' . __( 'The default text that will show in the email message. The affiliate\'s referral URL will be automatically appended to the email.', 'affiliatewp-affiliate-portal' ) . '</p>',
				'type' => 'text',
				'std'  => __( 'I thought you might be interested in this:', 'affiliatewp-affiliate-portal' ),
			),
			'portal_menu_links'               => array(
				'name'     => __( 'Menu Links', 'affiliatewp-affiliate-portal' ) . $this->expand_collapse_menu_links(),
				'desc'     => '',
				'type'     => 'text',
				'callback' => array( $this, 'menu_links_list' ),
			),
			'per_page_header'                 => array(
				'name' => __( 'Per Page Settings', 'affiliatewp-affiliate-portal' ),
				'type' => 'header',
			),
			'portal_creatives_per_page'       => array(
				'name' => __( 'Creatives Per Page', 'affiliatewp-affiliate-portal' ),
				'desc' => __( 'The number of creatives to display.', 'affiliatewp-affiliate-portal' ),
				'type' => 'number',
				'min'  => '1',
				'max'  => '200',
				'std'  => '30',
			),
			'portal_items_per_page'           => array(
				'name' => __( 'Items Per Page', 'affiliatewp-affiliate-portal' ),
				'desc' => __( 'The number of items to show per page in most tables.', 'affiliatewp-affiliate-portal' ),
				'type' => 'number',
				'min'  => '1',
				'max'  => '50',
				'std'  => '20',
			),

		);

		return $settings;
	}

	/**
	 * Adds a link to expand/collapse menu links.
	 *
	 * @since 1.0.8
	 *
	 * @return string HTML output for the expand/collapse controls.
	 */
	private function expand_collapse_menu_links() {
		$menu_links = affiliatewp_affiliate_portal()->menu_links->get_menu_links();

		// Only show if there are menu links to expand/collapse.
		if ( ! empty( $menu_links ) ) {
			ob_start();

			$expand_text   = __( 'Expand all menu links', 'affiliatewp-affiliate-portal' );
			$collapse_text = __( 'Collapse all menu links', 'affiliatewp-affiliate-portal' );

			?>
			<p>
				<a href="#" class="portal-hide-show-menu-links" data-text-swap="<?php echo esc_attr( $collapse_text ); ?>" data-text-original="<?php echo esc_attr( $expand_text ); ?>">
					<?php echo esc_html( $expand_text ); ?>
				</a>
			</p>
			<?php
			return ob_get_clean();
		}
		return;
	}

	/**
	 * Renders the menu links list.
	 *
	 * @since 1.0.8
	 */
	public function menu_links_list() {

		$menu_links = affiliatewp_affiliate_portal()->menu_links->get_menu_links();
		$pages      = affiliatewp_affiliate_portal()->menu_links->get_pages();
		?>
		<div class="widefat portal_menu_links_repeatable_table">

			<div class="portal-menu-links-repeatables-wrap">

			<!-- Hidden row to clone on JS. -->
				<div class="portal_menu_links_repeatable_row" data-key="0" style="display: none;">
					<?php $this->render_menu_link_row( 0, '', '', $pages ); ?>
				</div>

				<!-- Menu Links -->
				<?php foreach ( $menu_links as $link_index => $menu_link ) : ?>
					<div class="portal_menu_links_repeatable_row" data-key="<?php echo esc_attr( $link_index ); ?>">
						<?php $this->render_menu_link_row( $link_index, $menu_link['slug'], $menu_link['label'], $pages ); ?>
					</div>
				<?php endforeach; ?>

				<div class="portal-menu-links-add-repeatable-row">
					<button class="button-secondary portal-menu-links-add-repeatable <?php echo ( empty( $menu_links ) ) ?  'no-menu-links' : '' ?>">
						<?php esc_html_e( 'Add New Link', 'affiliatewp-affiliate-portal' ); ?>
					</button>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Renders an individual menu link row.
	 *
	 * @since 1.0.8
	 *
	 * @param int    $key     Link index.
	 * @param string $slug    Link slug.
	 * @param string $label   Link label.
	 * @param array  $pages   WordPress pages.
	 */
	private function render_menu_link_row( $key, $slug, $label, $pages ) {
		?>
		<div class="portal-menu-links-draghandle-anchor">
			<span class="dashicons dashicons-move" title="<?php esc_attr_e( 'Click and drag to re-order', 'affiliatewp-affiliate-portal' ); ?>"></span>
		</div>

		<div class="portal-menu-links-repeatable-row-header">

			<div class="portal-menu-links-repeatable-row-title">
				<span class="portal-menu-links-title"><?php echo esc_html( $label ); ?></span><span class="portal-menu-links-link-number"> (Link <span class="portal-menu-links-link-number-key"><?php echo esc_html( $key ); ?></span>)</span>
				<span class="portal-menu-links-edit">
					<span class="dashicons dashicons-arrow-down"></span>
				</span>
			</div>

			<div class="portal-menu-links-repeatable-row-standard-fields" style="display: none;">

				<?php // Link title. ?>
				<p class="portal-menu-links-link-title">

					<label for="affwp_settings[affiliate_portal][portal_menu_links][<?php echo esc_attr( $key ); ?>][label]"><strong><?php _e( 'Link Title', 'affiliatewp-affiliate-portal' ); ?></strong></label>
					<span class="description"><?php _e( 'Enter a label for the link.', 'affiliatewp-affiliate-portal' ); ?></span>

					<input id="affwp_settings[affiliate_portal][portal_menu_links][<?php echo esc_attr( $key ); ?>][label]" name="affwp_settings[affiliate_portal][portal_menu_links][<?php echo esc_attr( $key ); ?>][label]" type="text" class="widefat" value="<?php echo esc_attr( $label ); ?>"/>

					<input name="affwp_settings[affiliate_portal][portal_menu_links][<?php echo esc_attr( $key ); ?>][slug]" type="hidden" value="<?php echo esc_attr( $slug ); ?>" />

				</p>

				<?php // Link content. ?>
				<p class="portal-menu-links-link-content">
					<label for="affwp_settings[affiliate_portal][portal_menu_links][<?php echo esc_attr( $key ); ?>][id]"><strong><?php _e( 'Link Content', 'affiliatewp-affiliate-portal' ); ?></strong></label>
					<span class="description"><?php _e( 'Select which page will be used for the link\'s content. This page will be blocked for non-affiliates.', 'affiliatewp-affiliate-portal' ); ?></span>

					<?php $links = affiliatewp_affiliate_portal()->menu_links->get_menu_links(); ?>
					<select id="affwp_settings[affiliate_portal][portal_menu_links][<?php echo esc_attr( $key ); ?>][id]" class="widefat" name="affwp_settings[affiliate_portal][portal_menu_links][<?php echo esc_attr( $key ); ?>][id]">
						<?php
						foreach ( $pages as $id => $title ) :
							$selected = $links && isset( $links[ $key ]['id'] ) ? ' ' . selected( $links[ $key ]['id'], $id, false ) : '';
							?>
							<option value="<?php echo esc_attr( $id ); ?>"<?php echo $selected; ?>><?php echo esc_html( $title ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p><a href="#" class="portal_menu_links_remove_repeatable"><?php esc_html_e( 'Delete link', 'affiliatewp-affiliate-portal' ); ?></a></p>

			</div>
		</div>

		<?php
	}

	/**
	 * Handles saving of the menu links setting.
	 *
	 * @since 1.0.8
	 *
	 * @param array $new_value Array of new values.
	 * @param array $old_value Array of old values.
	 * @return array (Maybe) modified values.
	 */
	public function pre_update_option( $new_value, $old_value ) {

		if ( isset( $new_value['affiliate_portal']['portal_menu_links'] ) ) {
			$new_values = $new_value['affiliate_portal']['portal_menu_links'];

			if ( isset( $old_value['affiliate_portal']['portal_menu_links'] ) ) {
				$old_values = $old_value['affiliate_portal']['portal_menu_links'];
			} else {
				$old_values = array();
			}

			// Loop through links.
			foreach ( $new_values as $key => $link ) {

				// Links must have both a title and id assigned.
				if ( empty( $link['label'] ) || ! isset( $link['id'] ) ) {

					// Unset the link.
					unset( $new_values[ $key ] );

					// Skip to the next link.
					continue;
				}

				// Create an initial link slug.
				if ( empty( $link['slug'] ) ) {

					// Create a slug from the link's title.
					$new_values[ $key ]['slug'] = affiliatewp_affiliate_portal()->menu_links->make_slug( $link['label'] );

				}

				// Force the link ID to be an integer.
				$new_values[ $key ]['id'] = intval( $new_values[ $key ]['id'] );

				$link_pairs = affiliatewp_affiliate_portal()->menu_links->get_link_pairs();

				if ( ! empty( $old_values ) ) {
					/*
					 * Loop through the old values.
					 *
					 * First we check if the link exists in the old values. If so, we then check its title.
					 * If the title changed, we attempt to update its tab slug.
					 */
					foreach ( $old_values as $old_key => $old_link ) {

						// Found the custom slug, must be the same link.
						if ( $old_link['slug'] === $link['slug'] ) {

							// Check to see if the link's title was changed.
							if ( $old_link['label'] !== $link['label'] ) {
								// Create a new slug.
								$new_slug = affiliatewp_affiliate_portal()->menu_links->make_slug( $link['label'] );

								// Check that the slug isn't already in-use.
								if ( ! array_key_exists( $new_slug, $link_pairs ) ) {
									// Slug isn't being used, use the new slug.
									$new_values[ $key ]['slug'] = $new_slug;
								}
							}
						}
					}
				}

				// Unset any link if the page has the [affiliate_area] shortcode on it.
				if ( isset( $link['id'] ) ) {

					$page = get_post( $link['id'] );

					// Bail if there's no page.
					if ( ! $page ) {
						continue;
					}

					$page_content = isset( $page->post_content ) ? $page->post_content : '';

					if ( $page_content && has_shortcode( $page_content, 'affiliate_area' ) ) {
						unset( $new_values[ $key ] );

						continue;
					}
				}
			}

			$new_value['affiliate_portal']['portal_menu_links'] = $new_values;
		}

		return $new_value;

	}

	/**
	 * Sanitizes the Affiliate Portal's creatives per page setting.
	 *
	 * @since 1.0.9
	 *
	 * @param mixed  $value Setting value.
	 * @param string $key   Setting key.
	 * @return mixed Sanitized creatives per page value.
	 */
	public function sanitize_creatives_per_page( $value, $key ) {
		if ( 'portal_creatives_per_page' === $key ) {
			// Should default to 30.
			if ( empty( $value ) ) {
				$value = 30;
			}

			// Max value is 200.
			if ( $value > 200 ) {
				$value = 200;
			}

			// Min value is 1.
			if ( $value < 1 ) {
				$value = 1;
			}
		}

		return $value;
	}

	/**
	 * Sanitizes the Affiliate Portal's items per page setting.
	 *
	 * @since 1.0.9
	 *
	 * @param mixed  $value Setting value.
	 * @param string $key   Setting key.
	 * @return mixed Sanitized items per page value.
	 */
	public function sanitize_items_per_page( $value, $key ) {
		if ( 'portal_items_per_page' === $key ) {
			// Should default to 20.
			if ( empty( $value ) ) {
				$value = 20;
			}

			// Max value is 50.
			if ( $value > 50 ) {
				$value = 50;
			}

			// Min value is 1.
			if ( $value < 1 ) {
				$value = 1;
			}
		}

		return $value;
	}
}
