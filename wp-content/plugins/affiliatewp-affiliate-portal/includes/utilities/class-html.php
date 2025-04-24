<?php
/**
 * Utilities: HTML elements class
 *
 * This class incorporates work originating from the EDD_HTML_Elements class, bundled
 * with the Easy Digital Downloads plugin, (c) 2015, Pippin Williamson.
 *
 * @package   Core/Utilities
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Utilities;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Icons_Registry;
use function AffiliateWP_Affiliate_Portal\html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A core helper class for outputting common HTML elements, such as for forms and other component views.
 *
 * @since 1.0.0
 */
class HTML {

	/**
	 * Element type, e.g. radio, text, number, etc.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $type = '';

	/**
	 * If the element needs a name attribute.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	private $needs_name_attribute = true;

	/**
	 * Holds stringified attributes to be built into $output.
	 *
	 * @since 1.0.0
	 * @var   string[]
	 */
	private $atts = array();

	/**
	 * Holds stringified alpine directives to be built into $output.
	 *
	 * @since 1.0.0
	 * @var   string[]
	 */
	private $directives = array();

	/**
	 * Renders an HTML section divider.
	 *
	 * @since 1.0.0
	 * @param bool  $echo Optional. Whether to echo the output. Default true (echo).
	 */
	public function section_divider( $echo = true ) {

		$output = '<div class="hidden sm:block">';
		$output .='<div class="py-8">';
		$output .= '<div class="border-t border-gray-200"></div>';
		$output .= '</div>';
		$output .= '</div>';

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders an HTML span element (text).
	 *
	 * @since 1.0.0
	 *
	 * @param array $args  {
	 *     Optional. Arguments for displaying the span element.
	 *
	 *     @type string       $text  The span text.
	 *     @type string|array $class Class or array of classes to use for the span.
	 * }
	 * @param bool  $echo  Optional. Whether to echo the output. Default true (echo).
	 */
	public function span( $args = array(), $echo = true ) {
		$defaults = array(
			'text'  => '',
			'class' => '',
		);

		if ( ! empty( $args['text'] ) ) {
			$text = $args['text'];

			// Skip parsing this argument as an attribute.
			unset( $args['text'] );
		} else {
			$text = '';
		}

		$this->parse_args( $args, $defaults );

		$atts = $this->html_atts();

		$output = sprintf( '<span%1$s>%2$s</span>', $atts, $text );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders an HTML opening <div> element.
	 *
	 * @since 1.0.0
	 *
	 * @param array       $args           {
	 *                                    Optional. Arguments for building the <div> element.
	 *
	 *     @type string       $id             ID attribute for the div. Default empty.
	 *     @type string|array $class          Class attribute for the div. Default empty.
	 *     @type array        $directives     Alpine directives. Default empty
	 * }
	 * @param bool        $echo           Optional. Whether to echo the output. Default true (echo).
	 * @return string|void HTML markup if `$echo` is false, otherwise void (echo).
	 */
	public function div_start( $args = array(), $echo = true ) {
		$defaults = array(
			'id'    => '',
			'class' => '',
		);

		// Don't set a name attribute.
		$this->needs_name_attribute = false;

		$this->parse_args( $args, $defaults );

		$atts = $this->html_atts();

		$output = sprintf( '<div%1$s>', $atts );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders an HTML closing </div> element.
	 *
	 * @since 1.0.0
	 *
	 * @param bool  $echo Optional. Whether to echo the output. Default true (echo).
	 * @return string|void HTML markup if `$echo` is false, otherwise void (echo).
	 */
	public function div_end( $echo = true ) {

		$output = '</div>';

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders a generic HTML opening element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type. The HTML opening element to render. Default section.
	 * @param array $args {
	 *     Optional. Arguments for building the HTML element.
	 *
	 *     @type string       $id         ID attribute for the HTML element. Default empty.
	 *     @type string|array $class      Class attribute for the HTML element. Default empty.
	 *     @type array        $directives Alpine directives. Default empty
	 * }
	 * @param bool  $echo Optional. Whether to echo the output. Default true (echo).
	 * @return string|void HTML markup if `$echo` is false, otherwise void (echo).
	 */
	public function element_start( $type, $args = array(), $echo = true ) {

		$types = array(
			'section',
			'select',
			'option',
			'template',
			'span',
			'table',
			'thead',
			'tbody',
			'tr',
			'th',
			'td',
			'nav',
			'dl',
			'canvas',
			'dd',
			'dt',
		);

		// Don't set a name attribute.
		$this->needs_name_attribute = false;

		if ( ! in_array( $type, $types, true ) ) {
			$this->type = 'section';
		} else {
			$this->type = $type;
		}

		$defaults = array(
			'id'    => '',
			'class' => '',
		);

		$this->parse_args( $args, $defaults );

		$atts = $this->html_atts();

		$output = sprintf( '<%1$s%2$s>', $this->type, $atts );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders a generic HTML closing element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The HTML closing element to render. Default section.
	 * @param bool   $echo Optional. Whether to echo the output. Default true (echo).
	 * @return string|void HTML markup if `$echo` is false, otherwise void (echo).
	 */
	public function element_end( $type, $echo = true ) {

		$types = array(
			'section',
			'template',
			'select',
			'option',
			'span',
			'table',
			'thead',
			'tbody',
			'tr',
			'th',
			'td',
			'nav',
			'dl',
			'canvas',
		);

		if ( ! in_array( $type, $types, true ) ) {
			$this->type = 'section';
		} else {
			$this->type = $type;
		}

		$output = sprintf( '</%s>', $this->type );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders an empty generic HTML element.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $type       The HTML element to render. Default 'section'.
	 * @param array       $args       {
	 *                                Optional. Arguments for building the HTML element.
	 * @type string       $id         ID attribute for the HTML element. Default empty.
	 * @type string|array $class      Class attribute for the HTML element. Default empty.
	 * @type array        $directives Alpine directives. Default empty
	 *                                }
	 * @param bool        $echo       Optional. Whether to echo the output. Default true (echo).
	 * @return string|void HTML markup if `$echo` is false, otherwise void (echo).
	 */
	public function element( $type, $args = array(), $echo = true ) {
		if ( true === $echo ) {
			$this->element_start( $type, $args, $echo );
			$this->element_end( $type, $echo );
		} else {
			return $this->element_start( $type, $args, $echo ) . $this->element_end( $type, $echo );
		}
	}

	/**
	 * Renders an HTML opening <form> element.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Optional. Arguments for building the <form> element.
	 *
	 *     @type string       $id         ID attribute for the div. Default empty.
	 *     @type string|array $class      Class attribute for the div. Default empty.
	 *     @type string       $method     The HTTP method to submit the form with.
	 *     @type array        $directives Alpine directives. Default empty
	 * }
	 * @param bool  $echo Optional. Whether to echo the output. Default true (echo).
	 * @return string|void HTML markup if `$echo` is false, otherwise void (echo).
	 */
	public function form_start( $args = array(), $echo = true ) {

		// Don't set a name attribute.
		$this->needs_name_attribute = false;

		// Define and unset method before parsing to avoid passing it to the input.
		if ( ! empty( $args['method'] ) ) {
			$method = $args['method'];

			unset( $args['method'] );
		} else {
			$method = 'post';
		}

		$methods = array(
			'post',
			'get',
		);

		if ( ! in_array( $method, $methods, true ) ) {
			$method = 'post';
		}

		$defaults = array(
			'id'    => '',
			'class' => '',
		);

		$this->parse_args( $args, $defaults );

		$atts = $this->html_atts();

		$output = sprintf( '<form method="%1$s"%2$s>', $method, $atts );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders an HTML closing </form> element.
	 *
	 * @since 1.0.0
	 *
	 * @param bool  $echo Optional. Whether to echo the output. Default true (echo).
	 * @return string|void HTML markup if `$echo` is false, otherwise void (echo).
	 */
	public function form_end( $echo = true ) {

		$output = sprintf( '</form>' );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders an HTML description term element (dt).
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Optional. Arguments for displaying the description term element.
	 *
	 *     @type string       $text  The paragraph text.
	 *     @type string|array $class Class or array of classes to use for the description term.
	 * }
	 * @param bool  $echo Optional. Whether to echo the output. Default true (echo).
	 */
	public function dt( $args = array(), $echo = true ) {
		$defaults = array(
			'text'  => '',
			'class' => '',
		);

		if ( ! empty( $args['text'] ) ) {
			$text = $args['text'];

			// Skip parsing this argument as an attribute.
			unset( $args['text'] );
		} else {
			$text = '';
		}

		$this->parse_args( $args, $defaults );

		$atts = $this->html_atts();

		$output = sprintf( '<dt%1$s>%2$s</dt>', $atts, $text );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders an HTML description details element (dd).
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Optional. Arguments for displaying the description details element.
	 *
	 *     @type string       $text  The paragraph text.
	 *     @type string|array $class Class or array of classes to use for the description details.
	 * }
	 * @param bool  $echo Optional. Whether to echo the output. Default true (echo).
	 */
	public function dd( $args = array(), $echo = true ) {
		$defaults = array(
			'text'  => '',
			'class' => '',
		);

		if ( ! empty( $args['text'] ) ) {
			$text = $args['text'];

			// Skip parsing this argument as an attribute.
			unset( $args['text'] );
		} else {
			$text = '';
		}

		$this->parse_args( $args, $defaults );

		$atts = $this->html_atts();

		$output = sprintf( '<dd%1$s>%2$s</dd>', $atts, $text );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
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
	public function readonly( $helper, $current, $echo ) {
		if ( true === $echo ) {
			__checked_selected_helper( $helper, $current, $echo, 'readonly' );
		} else {
			return __checked_selected_helper( $helper, $current, $echo, 'readonly' );
		}
	}

	/**
	 * Parses arguments against defaults and sanitizes any attributes.
	 *
	 * @since 1.0.0
	 *
	 * @see wp_parse_args()
	 *
	 * @param string|array|object $args         User-defined arguments to merge with defaults.
	 * @param array               $defaults     Optional. Array that serves as the defaults. Default empty array.
	 * @param bool                $remove_empty Whether to remove empty arguments. Default true.
	 * @return array Sanitized user-defined arguments merged with defaults.
	 */
	private function parse_args( $args, $defaults = array(), $remove_empty = true ) {
		$args = wp_parse_args( $args, $defaults );

		// Ensure the name attribute is always set if the ID is set.
		if ( $this->needs_name_attribute && ! empty( $args['id'] ) && empty( $args['name'] ) ) {
			$args['name'] = $args['id'];
		}

		// Prepare directives
		if ( ! empty( $args['directives'] ) ) {
			$this->build_directives( $args['directives'] );
			unset( $args['directives'] );
		}

		$args = $this->process_attributes( $args );

		if ( true === $remove_empty ) {
			foreach ( $args as $key => $value ) {
				// Never remove vital attributes id, name, or options, regardless of emptiness.
				if ( in_array( $key, array( 'id', 'name', 'options' ), true ) ) {
					continue;
				}

				if ( empty( $value ) && 0 !== $value ) {
					unset( $args[ $key ] );
				}
			}
		}

		return $args;
	}

	/**
	 * Sanitizes a given group of attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args All supplied arguments and attributes.
	 * @return array Supplied arguments with sanitized attributes according to whitelists.
	 */
	private function process_attributes( $args ) {

		// Loop through attributes.
		foreach ( $args as $key => $value ) {

			if ( empty( $value ) && false !== $value ) {
				continue;
			}

			switch ( $key ) {

				case 'autocomplete':
					if ( ! in_array( $value, array( 'on', 'off' ), true ) ) {
						$value = 'on';
					}

					$value = $this->sanitize_key( $value );

					$this->attribute_to_string( $key, $value );
					break;

				case 'class':
				case 'label_class':
				case 'label_href_class':
					if ( ! is_array( $value ) ) {
						$value = array( $value );
					}

					$value = array_map( array( $this, 'sanitize_html_class' ), $value );
					$value = implode( ' ', $value );

					$this->attribute_to_string( $key, $value );
					break;

				case 'context':
					if ( ! in_array( $value, array( 'edit', 'add' ) ) ) {
						$value = 'edit';
					}
					break;

				case 'aria':
				case 'data':
				case 'stroke':
					if ( 'stroke' === $key && ! is_array( $value ) ) {
						$this->attribute_to_string( $key, $value );
					} else {
						$this->build_hyphenated_atts( $key, $value );
					}
					break;

				case 'options':
					foreach ( $value as $value_key => $sub_value ) {
						$value_key = $this->sanitize_key( $value_key );

						$value[ $value_key ] = $sub_value;
					}
					break;

				case 'min':
				case 'max':
					$value = intval( $value );

					$this->attribute_to_string( $key, $value );
					break;

				case 'placeholder':
					$value = sanitize_text_field( $value );

					$this->attribute_to_string( $key, $value );
					break;

				case 'href':
				case 'label_href':
					$value = esc_url( $value );

					$this->attribute_to_string( $key, $value );
					break;

				case 'url':
					// Deliberately skip adding URL to $atts (converted to onclick separately and unset).
					$value = sanitize_text_field( $value );

					break;

				case 'product_id':
					$value = absint( $value );
					break;

				// Attributes to leave alone.
				case 'editor_args':
				case 'label':
				case 'title':
				case 'store':
				case 'text':
				case 'level':
				case 'desc':
					$value = $value;
					break;

				case 'value':
					if ( ! in_array( $this->type, array( 'textarea', 'button' ) ) ) {
						$this->attribute_to_string( $key, $value );
					}
					break;

				case 'disabled':
				case 'readonly':
					if ( false !== $value ) {
						$this->special_to_string( $key, $value );
					}
					break;

				default:
					$value = $this->sanitize_key( $value );

					$this->attribute_to_string( $key, $value );
					break;
			}

			$args[ $key ] = $value;
		}

		return $args;
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
	private function sanitize_key( $key ) {
		return preg_replace( '/[^][a-zA-Z0-9_\-\.\:\/@]/', '', $key );
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
	public function attribute_to_string( $key, $value ) {
		$this->atts[] = sprintf( '%1$s="%2$s"', $key, esc_attr( $value ) );
	}

	/**
	 * Converts a special attribute into a string based on the key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Attribute key.
	 * @param mixed  $value
	 */
	public function special_to_string( $key, $value ) {
		switch( $key ) {

			case 'disabled':
				$this->atts[] = disabled( true, $value, false );
				break;

			case 'readonly':
				$this->atts[] = $this->readonly( true, $value, false );
				break;

			default: break;
		}
	}

	/**
	 * General helper to build Alpine directive string.
	 *
	 * @since 1.0.0
	 *
	 * @param array $directives Alpine directives key/value pairs.
	 */
	public function build_directives( $directives ) {
		foreach ( $directives as $key => $value ) {
			$this->directives[] = sprintf( '%1$s="%2$s"',
				$this->sanitize_key( $key ),
				esc_attr( $value )
			);
		}
	}

	/**
	 * General helper to build hyphenated attribute groups.
	 *
	 * @since 1.0.0
	 *
	 * @param string $group Attribute group.
	 * @param array  $atts  Attribute key/value pairs.
	 */
	public function build_hyphenated_atts( $group, $atts ) {
		foreach ( $atts as $key => $value ) {
			$this->atts[] = sprintf( '%1$s-%2$s="%3$s"',
				$group,
				$this->sanitize_key( $key ),
				esc_attr( $value )
			);
		}
	}

	/**
	 * Sanitizes an HTML classname to ensure it only contains valid characters.
	 *
	 * @param string $class    The classname to be sanitized
	 * @param string $fallback Optional. The value to return if the sanitization ends up as an empty string.
	 *  Defaults to an empty string.
	 * @return string The sanitized value
	 */
	private function sanitize_html_class( $class, $fallback = '' ) {
		// Strip out any %-encoded octets.
		$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class );

		// Limit to A-Z, a-z, 0-9, ':', '_', '-', '/'.
		$sanitized = preg_replace( '/[^A-Za-z0-9\/:_-]/', '', $sanitized );

		if ( '' === $sanitized && $fallback ) {
			return $this->sanitize_html_class( $fallback );
		}

		return $sanitized;
	}

	/**
	 * Builds and optionally prepares attributes for display.
	 *
	 * @since 1.0.0
	 * @param array $attributes Attributes.
	 * @param array $directives Optional. Directives to merge with attributes.
	 * @param bool  $prepare    Optional. Whether to prepare
	 * @return string Display-ready attributes.
	 */
	public static function prepare_atts( array $attributes, $directives = array() ) {
		$output = '';

		if ( ! empty( $directives ) ) {
			$attributes = array_merge( $attributes, $directives );
		}

		if ( ! empty( $attributes ) ) {
			$output = ' ' . implode( ' ', $attributes );
		}

		return $output;
	}

	/**
	 * Retrieves a merged list of attributes and Alpine directives.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $implode Whether to implode and prepare the atts for display. Default true.
	 * @return string|array Attributes display markup if `$implode` is true, otherwise a list
	 *                      of current attributes and directives.
	 */
	public function html_atts( $implode = true, $skip_reset = false ) {
		$atts = array();

		if ( ! empty( $this->atts ) ) {
			$atts = $this->atts;
		}

		if ( ! empty( $this->directives ) ) {
			$atts = array_merge( $atts, $this->directives );
		}

		if ( true === $implode ) {
			$atts = $this->implode_atts( $atts );
		}

		if ( true !== $skip_reset ) {
			$this->reset();
		}

		return $atts;
	}

	/**
	 * Resets atts and directives to the default values.
	 *
	 * @since 1.0.0
	 *
	 */
	public function reset() {
		$this->atts       = array();
		$this->directives = array();
	}

	/**
	 * Prepares the given attributes for display.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Attributes to prepare.
	 * @return string Prepared attributes.
	 */
	public function implode_atts( $atts ) {
		$output = '';

		if ( ! empty( $atts ) ) {
			$output = ' ' . implode( ' ', $atts );
		}

		return $output;
	}

	/**
	 * Renders the HTML opening <div> element for the control section header.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section    Control section.
	 * @param array  $attributes {
	 *     Section attributes.
	 *
	 *     @type string $label   Section label.
	 *     @type string $desc    Optional. Description to display below the section label as a paragraph.
	 *                           HTML not allowed.
	 *     @type bool   $wrapper Whether to style the wrapper around the section content area. Default true.
	 *     @type array  $columns {
	 *         Column specifications for the header and content areas with a combined total of 3 columns.
	 *
	 *         @type int  $header  Header col-span. Accepts 1-3. Default 1.
	 *         @type int  $content Section content col-span. Accepts 1-3. Default 2.
	 *     }
	 * }
	 * @param bool   $echo       Optional. Whether to echo the output. Default true (echo).
	 * @return string|void String if `$echo` is false, otherwise the markup is echoed.
	 */
	public function control_section_header( $section, $attributes, $echo = true ) {

		if ( ! empty( $attributes['columns']['header'] ) ) {
			$columns = (int) $attributes['columns']['header'];

			$colspan = $this->get_col_span_classes( $columns );
		} else {
			$colspan = array( 'md:col-span-1' );
		}

		$output = '';

		if ( ! empty( $attributes['label'] ) ) {
			$output .= html()->div_start( array(
				'id'    => $section,
				'class' => $colspan,
			), false );

			$section_heading = new Controls\Heading_Control( array(
				'id'   => 'section-heading',
				'args' => array(
					'text'  => $attributes['label'],
					'level' => 2,
				),
			) );

			if ( ! $section_heading->has_errors() ) {
				$output .= $section_heading->render( false );
			}

			if ( ! empty( $attributes['desc'] ) ) {
				$section_desc = new Controls\Paragraph_Control( array(
					'id'   => 'section-desc',
					'atts' => array(
						'class' => array( 'mt-2', 'text-sm', 'leading-5', 'text-gray-600' ),
					),
					'args'    => array(
						'text'  => $attributes['desc'],
					),
				) );

				if ( ! $section_desc->has_errors() ) {
					$output .= $section_desc->render( false );
				}
			}

			$output .= html()->div_end( false );
		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders the HTML opening <div> elements for a control section.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes {
	 *     Section attributes.
	 *
	 *     @type string $label   Section label.
	 *     @type string $desc    Optional. Description to display below the section label as a paragraph.
	 *                           HTML not allowed.
	 *     @type bool   $wrapper Whether to style the wrapper around the section content area. Default true.
	 *     @type array  $columns {
	 *         Column specifications for the header and content areas with a combined total of 3 columns.
	 *
	 *         @type int  $header  Header col-span. Accepts 1-3. Default 1.
	 *         @type int  $content Section content col-span. Accepts 1-3. Default 2.
	 *     }
	 * }
	 * @param bool  $echo       Optional. Whether to echo the output. Default true (echo).
	 * @return string|void String if `$echo` is false, otherwise the markup is echoed.
	 */
	public function control_section_start( $attributes, $echo = true ) {
		$classes = array(
			'outer' => array( 'mt-5', 'md:mt-0', 'overflow-hidden', 'sm:rounded-md' ),
			'inner' => array(),
		);

		if ( ! empty( $attributes['columns']['content'] ) ) {
			$columns = (int) $attributes['columns']['content'];

			$outer_colspan = $this->get_col_span_classes( $columns );
		} else {
			$outer_colspan = array( 'md:col-span-2' );
		}

		$classes['outer'] = array_merge( $classes['outer'], $outer_colspan );

		if ( isset( $attributes['wrapper'] ) && true === $attributes['wrapper'] ) {
			$classes['outer'][] = 'shadow';

			$classes['inner'] = array_merge( $classes['inner'], array( 'bg-white', 'p-4', 'sm:p-6' ) );
		}

		$wrapper_args = array( 'class' => $classes['inner'] );

		if ( ! empty( $attributes['args']['wrapper'] ) && is_array( $attributes['args']['wrapper'] ) ) {
			$wrapper_args = array_merge( $wrapper_args, $attributes['args']['wrapper'] );
		}

		$output = '';

		$output .= html()->div_start( array( 'class' => $classes['outer'] ), false );

		$output .= html()->div_start( $wrapper_args, false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders the HTML closing <div> elements for a control section.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo Optional. Whether to echo the output. Default true (echo).
	 * @return string|void String if `$echo` is false, otherwise the markup is echoed.
	 */
	public function control_section_end( $echo = true ) {
		$output = html()->div_end( false );
		$output .= html()->div_end( false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders the HTML for a divider element.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo Optional. Whether to echo the output. Default true (echo).
	 * @return string|void String if `$echo` is false, otherwise the markup is echoed.
	 */
	public function divider( $echo = true ) {
		$output = html()->div_start( array(
			'class' => 'py-8',
		), false );

		$output .= html()->element( 'div', array(
			'class' => array( 'border-t', 'border-gray-200' ),
		), false );

		$output .= html()->div_end( false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Renders the HTML markup for a portal notice.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Arguments for rendering the notice.
	 *
	 *     @type string $type    Notice type. Accepts 'success' or 'error'. Default 'success'.
	 *     @type string $message Notice message.
	 * }
	 * @param bool  $echo Optional. Whether to echo the output. Default true (echo).
	 * @return string|void String if `$echo` is false, otherwise the markup is echoed.
	 */
	public function notice( $args, $echo = true ) {
		$defaults = array(
			'type'    => 'success',
			'message' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! in_array( $args['type'], array( 'success', 'error', 'info' ) ) ) {
			$args['type'] = 'success';
		}

		$output = '';

		if ( ! empty( $args['message'] ) ) {

			$notice_classes  = array( 'rounded-md', 'p-4', 'mb-10' );
			$message_classes = array( 'text-sm', 'leading-5', 'font-medium' );

			switch ( $args['type'] ) {
				case 'error':
					$notice_classes[]  = 'bg-red-100';
					$message_classes[] = 'text-red-800';

					$name       = 'x-circle';
					$icon_color = 'text-red-600';
					break;

				case 'info':
					$notice_classes[]  = 'bg-blue-100';
					$message_classes[] = 'text-blue-800';

					$name       = 'information-circle';
					$icon_color = 'text-blue-600';
					break;

				case 'success':
				default:
					$notice_classes[]  = 'bg-green-100';
					$message_classes[] = 'text-green-800';

					$name       = 'check-circle';
					$icon_color = 'text-green-500';
					break;
			}

			$output .= html()->div_start( array(
				'class' => $notice_classes,
			), false );

			$output .= html()->div_start( array(
				'class' => array( 'flex', 'sm:items-center' ),
			), false );

			$output .= html()->div_start( array(
				'class' => array( 'flex-shrink-0' ),
			), false );

			$icon = new Controls\Icon_Control( array(
				'id'   => 'notice_icon',
				'args' => array(
					'name'  => $name,
					'size'  => 6,
					'type'  => 'solid',
				),
				'atts' => array(
					'class' => array( $icon_color ),
				),
			) );

			if ( ! $icon->has_errors() ) {
				$output .= $icon->render( false );
			}

			$output .= html()->div_end( false );

			$output .= html()->div_start( array(
				'class' => array( 'ml-3' ),
			), false );

			$notice = new Controls\Paragraph_Control( array(
				'id'   => 'notice_message',
				'atts' => array(
					'class' => $message_classes,
				),
				'args' => array(
					'text' => $args['message'],
				),
			) );

			if ( ! $notice->has_errors() ) {
				$output .= $notice->render( false );
			}

			$output .= html()->div_end( false );

			$output .= html()->div_end( false );

			$output .= html()->div_end( false );
		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Retrieves the classes for a col-span of a given type.
	 *
	 * It's important for the benefit of PurgeCSS to have the full class names available
	 * in the source rather than class names built with concatenation.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $columns Number of columns.
	 * @param string $type    Optional. Type of col-span. Accepts 'grid' or 'standard'. Default 'standard'.
	 * @return array Classes.
	 */
	public function get_col_span_classes( $columns, $type = 'standard' ) {
		if ( ! in_array( $type, array( 'grid', 'standard' ) ) ) {
			$type = 'standard';
		}

		if ( $columns > 5 ) {
			$columns = 3;
		}

		if ( $columns < 1 ) {
			$columns = 2;
		}

		$classes = array();

		if ( 'grid' === $type ) {
			switch ( true ) {
				case 1 === $columns:
					$classes[] = 'md:grid-cols-1';
					$classes[] = 'lg:grid-cols-1';
					break;

				case 2 === $columns:
					$classes[] = 'md:grid-cols-2';
					$classes[] = 'lg:grid-cols-2';
					break;

				case 3 === $columns:
					$classes[] = 'md:grid-cols-2';
					$classes[] = 'lg:grid-cols-3';
					break;

				case 4 === $columns:
					$classes[] = 'md:grid-cols-2';
					$classes[] = 'lg:grid-cols-4';
					break;

				case 5 === $columns:
					$classes[] = 'md:grid-cols-5';
					$classes[] = 'lg:grid-cols-5';
					break;
			}
		} elseif ( 'standard' === $type ) {
			switch ( true ) {
				case 1 === $columns:
					$classes[] = 'md:col-span-1';
					$classes[] = 'lg:col-span-1';
					break;

				case 2 === $columns:
					$classes[] = 'md:col-span-2';
					$classes[] = 'lg:col-span-2';
					break;

				case 3 === $columns:
					$classes[] = 'md:col-span-3';
					$classes[] = 'lg:col-span-3';
					break;

				case 4 === $columns:
					$classes[] = 'md:col-span-4';
					$classes[] = 'lg:col-span-4';
					break;

				case 5 === $columns:
					$classes[] = 'md:col-span-5';
					$classes[] = 'lg:col-span-5';
					break;

			}
		}

		return $classes;
	}
}
