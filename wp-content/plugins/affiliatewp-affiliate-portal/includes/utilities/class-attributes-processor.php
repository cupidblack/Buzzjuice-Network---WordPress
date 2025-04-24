<?php
/**
 * Utilities: Attributes Processor
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Utilities
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Utilities;

/**
 * Implements a processor for a wide variety of attributes.
 *
 * @since 1.0.0
 */
class Attributes_Processor {

	/**
	 * Sanitizes and processes a given group of attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $attributes   All supplied attributes.
	 * @param string $control_type Optional. Control type. Default null (unused).
	 * @return array Supplied arguments with sanitized attributes according to whitelists.
	 */
	public static function process( $attributes, $control_type = null ) {
		// Loop through attributes.
		foreach ( $attributes as $key => $value ) {
			$attributes[ $key ] = self::process_single( $key, $value, $control_type );
		}

		return $attributes;
	}

	/**
	 * Processes a single attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key          Attribute key.
	 * @param mixed  $value        Attribute value.
	 * @param string $control_type Optional. Control type. Default null (unused).
	 * @return mixed Attribute value.
	 */
	public static function process_single( $key, $value, $control_type = null ) {

		if ( empty( $value ) && false !== $value ) {
			return false;
		}

		switch ( $key ) {

			case 'autocomplete':
				if ( ! in_array( $value, array( 'on', 'off' ), true ) ) {
					$value = 'on';
				}

				$value = self::sanitize_key( $value );
				break;

			case 'class':
			case 'label_class':
			case 'label_href_class':
				if ( ! is_array( $value ) ) {
					$value = array( $value );
				}

				$value = array_map( array( __CLASS__, 'sanitize_html_class' ), $value );
				break;

			case 'context':
				if ( ! in_array( $value, array( 'edit', 'add' ) ) ) {
					$value = 'edit';
				}
				break;

			case 'options':
				if ( is_array( $value ) ) {
					foreach ( $value as $value_key => $sub_value ) {
						$value_key = self::sanitize_key( $value_key );

						$value[ $value_key ] = $sub_value;
					}
				}
				break;

			case 'min':
			case 'max':
			case 'product_id':
			case 'width':
			case 'height':
				$value = intval( $value );
				break;

			case 'placeholder':
			case 'label_href':
			case 'srcset':
				$value = sanitize_text_field( $value );
				break;

			case 'href':
			case 'url':
			case 'src':
				$value = esc_url( $value );
				break;

			// Attributes to leave alone.
			case 'editor_args':
			case 'label':
			case 'title':
			case 'store':
			case 'text':
			case 'level':
			case 'desc':
			case 'value':
				$value = $value;
				break;

			default:
				$value = self::sanitize_key( $value );
				break;
		}

		return $value;
	}

	/**
	 * Converts key/value attribute pairs into strings for display.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   Attribute key.
	 * @param mixed  $value Attribute value.
	 * @return string Stringified attribute.
	 */
	public static function stringify( $key, $value ) {
		if ( empty( $value ) && false !== $value ) {
			return false;
		}

		switch ( $key ) {

			case 'class':
			case 'label_class':
			case 'label_href_class':
				if ( ! empty( $value ) ) {
					$value = implode( ' ', $value );
					$value = self::attribute_to_string( $key, $value );
				}
				break;

			case 'aria':
			case 'data':
			case 'stroke':
				if ( 'stroke' === $key && ! is_array( $value ) ) {
					$value = self::attribute_to_string( $key, $value );
				} else {
					$value = self::build_hyphenated_atts( $key, $value );
				}
				break;

			case 'disabled':
			case 'readonly':
				if ( false !== $value ) {
					$value = self::special_to_string( $key, $value );
				}
				break;

			case 'error':
				if ( ! is_array( $value ) ) {
					$value = self::attribute_to_string( $key, $value );
				}
				break;

			case 'autocomplete':
			case 'min':
			case 'max':
			case 'placeholder':
			case 'href':
			case 'label_href':
			case 'value':
			default:
				$value = self::attribute_to_string( $key, $value );
				break;
		}

		return $value;
	}

	/**
	 * Builds and optionally prepares attributes for display.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Attributes.
	 * @param array $directives Optional. Directives to merge with attributes.
	 * @return string Display-ready attributes.
	 */
	public static function prepare( array $attributes, $directives = array() ) {
		$output = '';

		if ( ! empty( $directives ) ) {
			$attributes = array_merge( $attributes, $directives );
		}

		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $key => $value ) {
				$attributes[ $key ] = self::stringify( $key, $value );
			}

			$output = ' ' . implode( ' ', $attributes );
		}

		return $output;
	}

	/**
	 * Sanitizes a string key for use in an attribute.
	 *
	 * Keys are used as internal identifiers. Alphanumeric characters, dashes,
	 * underscores, stops, colons, square brackets, and slashes are allowed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Key to sanitize.
	 * @return string Sanitized key.
	 */
	private static function sanitize_key( $key ) {
		return preg_replace( '/[^][a-zA-Z0-9_\-\.\:\/@]/', '', $key );
	}

	/**
	 * Sanitizes an HTML classname to ensure it only contains valid characters.
	 *
	 * @param string $class    The classname to be sanitized
	 * @param string $fallback Optional. The value to return if the sanitization ends up as an empty string.
	 *                         Default empty.
	 * @return string The sanitized value
	 */
	private static function sanitize_html_class( $class, $fallback = '' ) {
		// Strip out any %-encoded octets.
		$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class );

		// Limit to A-Z, a-z, 0-9, ':', '_', '-', '/'.
		$sanitized = preg_replace( '/[^A-Za-z0-9\/:_-]/', '', $sanitized );

		if ( '' === $sanitized && $fallback ) {
			return self::sanitize_html_class( $fallback );
		}

		return $sanitized;
	}

	/**
	 * Converts a key and value pair into an HTML attribute string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   Attribute key.
	 * @param mixed  $value Attribute value.
	 * @return string Attribute string.
	 */
	private static function attribute_to_string( $key, $value ) {
		return sprintf( '%1$s="%2$s"', $key, esc_attr( $value ) );
	}

	/**
	 * Helper for outputting a readonly attribute.
	 *
	 * @since 1.0.0
	 *
	 * @see __checked_selected_helper()
	 *
	 * @param mixed  $helper  One of the values to compare
	 * @param mixed  $current (true) The other value to compare if not just true
	 * @param bool   $echo    Whether to echo or just return the string
	 * @return string|void HTML attribute or empty string if `$echo` is false, otherwise void (echo).
	 */
	private static function readonly( $helper, $current, $echo ) {
		if ( true === $echo ) {
			__checked_selected_helper( $helper, $current, $echo, 'readonly' );
		} else {
			return __checked_selected_helper( $helper, $current, $echo, 'readonly' );
		}
	}

	/**
	 * Converts a special attribute into a string based on the key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Attribute key.
	 * @param mixed  $value
	 * @return string String representation of the attribute.
	 */
	private static function special_to_string( $key, $value ) {
		switch( $key ) {

			case 'disabled':
				return disabled( true, $value, false );
				break;

			case 'readonly':
				return self::readonly( true, $value, false );
				break;

			default: break;
		}
	}

	/**
	 * General helper to build hyphenated attribute groups.
	 *
	 * @since 1.0.0
	 *
	 * @param string $group Attribute group.
	 * @param array  $atts  Attribute key/value pairs.
	 * @return string String representation of the attribute(s).
	 */
	private static function build_hyphenated_atts( $group, $atts ) {
		$attributes = array();

		foreach ( $atts as $key => $value ) {
			$attributes[] = sprintf( '%1$s-%2$s="%3$s"',
				$group,
				self::sanitize_key( $key ),
				esc_attr( $value )
			);
		}

		if ( ! empty( $attributes ) ) {
			$value = implode( ' ', $attributes );
		} else {
			$value = '';
		}

		return $value;
	}

}
