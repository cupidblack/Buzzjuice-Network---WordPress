<?php
/**
 * Controls: Stat Card Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Traits;
use function AffiliateWP_Affiliate_Portal\html;

/**
 * Implements a single statistic card control.
 *
 * @since 1.0.0
 *
 * @see   Base_Control
 */
final class Text_Control extends Base_Control {

	use Traits\Data_Getter;

	/**
	 * Sets up the control.
	 *
	 * @param array $metadata {
	 *     Metadata for setting up the current control. Arguments are optional unless
	 *     otherwise stated.
	 *
	 *     Metadata for setting up the current control. Arguments are optional unless otherwise stated.
	 *
	 *     @type string $id       Required. Globally-unique ID for the current control.
	 *     @type string $view_id  Required unless `$section` is also omitted. View ID to associate a registered
	 *                            control with.
	 *     @type string $section  Required unless `$view_id` is also omitted. Section to associate a registered
	 *                            control with.
	 *     @type int    $priority Priority within the section to display the control. Default 25.
	 *     @type string $parent   Parent control ID for select types of controls. Unused if not set.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $args     {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type string   $text         Text value to display. Default empty string.
	 *         @type callable $get_callback Callback to return a value to display via the `$text` attribute. Ignored
	 *                                      if `$text` is defined. Callback will be passed the current affiliate ID
	 *                                      and any HTML will be stripped by kses before render. Signature:
	 *                                      `( $affiliate_id ) : string`. Default unused.
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * @param bool  $validate Optional. Whether to validate the attributes (and split off any arguments).
	 *                        Default true;
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'text';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'text' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * Static factory for building a simplistic or empty Text_Control instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_id Control ID.
	 * @param mixed  $text       Optional. Control Text. Default empty string.
	 * @return Text_Control Control object.
	 */
	public static function create( $control_id, $text = '' ) {
		return new Text_Control( array(
			'id'   => $control_id,
			'args' => array(
				'text' => $text,
			)
		) );
	}

	/**
	 * Retrieves the saved data for the current control.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id Current affiliate ID.
	 * @return mixed Data for the current control.
	 */
	public function get_data( $affiliate_id ) {
		return $this->get_argument( 'text' );
	}

	/**
	 * Renders the markup for the current control.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo Optional. Whether to echo the rendered control markup. Default true;
	 * @return string|void The rendered control markup if `$echo` is true, otherwise the markup will be output.
	 */
	public function render( $echo = true ) {
		$alpine = $this->get_alpine_directives();

		$class = $this->get_attribute( 'class', array() );
		$text  = $this->get_argument( 'text', $this->get_control_data() );

		// Filter HTML out of the text.
		$text = wp_filter_nohtml_kses( $text );

		if ( empty( $alpine ) && empty( $class ) ) {

			$output = $text;

		} else {

			$output = html()->span( array(
				'directives' => $this->get_alpine_directives(),
				'text'       => $text,
				'class'      => $class,
			), false );

		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

}
