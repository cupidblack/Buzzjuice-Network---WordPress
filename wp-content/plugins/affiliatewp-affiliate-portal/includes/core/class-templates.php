<?php
/**
 * Core: Template Management API
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core;

#[\AllowDynamicProperties]

/**
 * Template management class.
 *
 * @since 1.0.0
 */
class Templates {

	/**
	 * Retrieves the path to the AffiliateWP templates directory.
	 *
	 * @since 1.0.0
	 *
	 * @return string Templates directory path.
	 */
	public function get_templates_dir() {
		return AFFWP_PORTAL_PLUGIN_DIR . 'templates';
	}

	/**
	 * Retrieves the URL to the AffiliateWP templates directory.
	 *
	 * @since 1.0.0
	 *
	 * @return string Templates directory URL.
	 */
	public function get_templates_url() {
		return AFFWP_PORTAL_PLUGIN_URL . 'templates';
	}

	/**
	 * Retrieves a template part.
	 *
	 * Taken from bbPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Template slug.
	 * @param string $name Optional. Template name. If specified, a `$slug-$name.php` template
	 *                     will be added to the templates searched. Default null (unused).
	 * @param bool   $load Optional. Whether to load the template if it's found. Default true.
	 * @return string The template filename.
	 */
	public function get_template_part( $slug, $name = null, $load = true ) {

		// Setup possible parts
		$templates = array();

		if ( isset( $name ) ) {
			$templates[] = $slug . '-' . $name . '.php';
		}

		$templates[] = $slug . '.php';

		// Return the part that is found
		return $this->locate_template( $templates, $load, false );
	}

	/**
	 * Retrieves the name of the highest priority template file that exists.
	 *
	 * Taken from bbPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $template_names Template file or files to search for, in order.
	 * @param bool         $load           Optional. Whether the template file will be loaded if it is found.
	 *                                     Default false.
	 * @param bool         $require_once   Optional. Whether to require_once or require. Default true.
	 *   Has no effect if $load is false.
	 * @return string The template filename if one is located.
	 */
	public function locate_template( $template_names, $load = false, $require_once = true ) {
		$located = false;

		// Try to find a template file.
		foreach ( (array) $template_names as $template_name ) {

			if ( empty( $template_name ) ) {
				continue;
			}

			// Trim off any slashes from the template name.
			$template_name = ltrim( $template_name, '/' );

			$template_path = trailingslashit( $this->get_templates_dir() );

			if ( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if ( ( true === $load ) && false !== $located ) {
			load_template( $located, $require_once );
		}

		return $located;
	}

}
