<?php
/**
 * Controls: Div With Copy Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Traits\Data_Getter;

use function AffiliateWP_Affiliate_Portal\html;

#[\AllowDynamicProperties]

/**
 * Implements a dvi with copy button control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Div_With_Copy_Control extends Base_Control {

	use Data_Getter;

	/**
	 * Sets up the control.
	 *
	 * @param array $metadata  {
	 *     Metadata for setting up the current control. Arguments are optional unless otherwise stated.
	 *
	 *     @type string $id       Required. Globally-unique ID for the current control.
	 *     @type string $view_id  Required unless `$section` is also omitted. View ID to associate a registered
	 *                            control with.
	 *     @type string $section  Required unless `$view_id` is also omitted. Section to associate a registered
	 *                            control with.
	 *     @type int    $priority Priority within the section to display the control. Default 25.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $args     {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type string   $content            Control content.
	 *         @type string   $desc               Control description.
	 *         @type array    $desc_class         Classes for the control description.
	 *         @type string   $label              Control label.
	 *         @type string   $icon_name          Registered name for the icon to use. Default 'link'.
	 *         @type array    $label_class        Classes for the control label.
	 *         @type array    $copy_button_alpine Alpine directives for copy button.
	 *         @type callable $get_callback       Callback to return a value to display via the `$content` arg. Ignored
	 *                                            if `$arg` is defined. Callback will be passed the current affiliate ID
	 *                                            Signature: `( $affiliate_id ) : string`. Default unused.
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * }
	 * @param bool   $validate Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'div_with_copy';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'desc', 'desc_class', 'label', 'label_class', 'content', 'copy_button_alpine', 'icon_name' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$desc               = $this->get_argument( 'desc' );
		$desc_class         = $this->get_argument( 'desc_class', array() );
		$label              = $this->get_argument( 'label' );
		$label_class        = $this->get_argument( 'label_class', array() );
		$icon_name          = $this->get_argument( 'icon_name', 'link' );
		$copy_button_alpine = $this->get_argument( 'copy_button_alpine', array() );

		$output = '';

		$id_base = $this->get_id_base();

		if ( ! empty( $label ) ) {
			$label_control = new Label_Control( array(
				'id'      => "{$id_base}-label",
				'view_id' => $this->get_view_id(),
				'section' => $this->get_prop( 'section' ),
				'args'    => array(
					'value' => $label,
				),
				'atts'    => array(
					'class' => $label_class,
				),
			) );

			if ( ! $label_control->has_errors() ) {
				$output .= $label_control->render( false );
			} else {
				$label_control->log_errors();
			}
		}

		if ( ! empty( $desc ) ) {
			$desc_control = new Paragraph_Control( array(
				'id'      => "{$id_base}-desc",
				'view_id' => $this->get_view_id(),
				'section' => $this->get_prop( 'section' ),
				'atts'    => array(
					'class' => array_merge( array( 'mb-2', 'text-sm', 'leading-5', 'text-gray-500' ), $desc_class ),
				),
				'args'    => array(
					'text'  => esc_html( $desc ),
				),
			) );

			if ( ! $desc_control->has_errors() ) {
				$output .= $desc_control->render( false );
			} else {
				$desc_control->log_errors();
			}
		}

		$output .= html()->div_start( array(
			'class' => array( 'border', 'border-gray-200', 'rounded-md' ),
		), false );

		$output .= html()->div_start( array(
			'class' => array( 'pl-3', 'pr-4', 'py-3', 'flex', 'items-center', 'justify-between', 'text-sm', 'leading-5' ),
		), false );

		$output .= html()->div_start( array(
			'class' => array( 'w-0', 'flex-1', 'flex', 'items-center', 'flex-wrap' ),
		), false );

		$copy_icon = new Icon_Control( array(
			'id'      => "{$id_base}-icon",
			'view_id' => $this->get_view_id(),
			'section' => $this->get_prop( 'section' ),
			'args'    => array(
				'name'  => $icon_name,
				'size'  => 5,
			),
			'atts'    => array(
				'class' => array( 'flex-shrink-0', 'text-gray-400' ),
			),
		) );

		if ( ! $copy_icon->has_errors() ) {
			$output .= $copy_icon->render( false );
		}

		$output .= html()->element_start( 'span', array(
			'class'      => array( 'ml-2', 'flex-1', 'w-0', 'truncate', 'break-words' ),
			'directives' => $this->get_alpine_directives(),
		), false );

		$content = $this->get_argument( 'content', $this->get_control_data() );
		$output .= $content;

		$output .= html()->element_end( 'span', false );

		if ( is_ssl() ) {
			$copy_button = new Copy_Button_Control( array(
				'id'      => "{$id_base}-copy",
				'view_id' => $this->get_view_id(),
				'section' => $this->get_prop( 'section' ),
				'args'    => array(
					'content' => $content,
				),
				'alpine'  => $copy_button_alpine,
			) );

			if ( ! $copy_button->has_errors() ) {
				$output .= $copy_button->render( false );
			} else {
				$copy_button->log_errors();
			}
		}

		$output .= '</div>';

		$output .= '</div>';

		$output .= '</div>';

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Retrieves the control data, in this case the content argument.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id Current affiliate ID.
	 * @return string Value of the content argument.
	 */
	public function get_data( $affiliate_id ) {
		return $this->get_argument( 'content' );
	}

}
