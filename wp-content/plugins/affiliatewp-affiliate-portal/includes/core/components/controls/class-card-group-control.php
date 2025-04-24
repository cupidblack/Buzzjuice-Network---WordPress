<?php
/**
 * Controls: Card Group Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Controls_Registry;
use function AffiliateWP_Affiliate_Portal\html;

#[\AllowDynamicProperties]

/**
 * Implements a card group control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Card_Group_Control extends Base_Control {

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
	 *         @type array  $cards       List of cards arguments for display. See html::card for list of card args
	 *         @type int    $columns     Max number of items per row. Leave blank for single row. Default 0
	 *         @type string $title       Heading title.
	 *         @type string $card_layout Card layout to use if card-level layout is not already set. Accepts 'info'
	 *                                   or 'stat'. Default 'stat'.
	 *         @type bool   $show_empty  Whether to show empty cards with no value. Default true (show).
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * }
	 * @param bool   $validate Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true;
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'card_control';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'cards', 'columns', 'title', 'card_layout', 'show_empty', );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * Retrieves a given argument if set, otherwise a default value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $argument Argument key.
	 * @param mixed  $default  Default argument value.
	 * @return mixed Argument value.
	 */
	public function get_argument( $argument, $default = '' ) {
		$value = parent::get_argument( $argument, $default );

		if ( 'cards' === $argument ) {
			$controls_registry = Controls_Registry::instance();

			$controls = $controls_registry->query( array(
				'view_id' => $this->get_view_id(),
				'type'    => 'card',
			) );

			$children = array();

			if ( ! empty( $controls ) ) {
				$children = wp_list_filter( $controls, array( 'parent' => $this->get_id() ) );
			}

			if ( ! empty( $children ) ) {
				$value = array_merge( $value, $children );
			}
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$cards       = $this->get_argument( 'cards', array() );
		$columns     = $this->get_argument( 'columns', 0 );
		$title       = $this->get_argument( 'title' );
		$card_layout = $this->get_argument( 'card_layout', 'stat' );
		$show_empty  = $this->get_argument( 'show_empty', true );

		$classes = $this->get_attribute( 'class', array() );

		$id_base = $this->get_id_base();

		if ( empty( $cards ) ) {
			$this->add_error( 'missing_cards',
				sprintf( 'No cards were defined for the \'%1$s\' card group control for the \'%2$s\' view.',
					$this->get_id(),
					$this->get_view_id()
				),
				$this->get_arguments()
			);

			?>

			<p><?php esc_html_e( 'No creatives found.', 'affiliate-wp' ); ?></p>

			<?php
		}

		if ( $this->has_errors() ) {

			$this->log_errors();

			return;
		}

		$columns = $columns <= 0 ? count( $cards ) : $columns;

		$wrapper_classes = array( 'grid', 'gap-5', 'grid-cols-1' );

		// Only set mb-10 if a bottom margin class isn't already set.
		if ( ! $found = preg_grep( '/^(([a-z]+:)?mb-\d+)$/', $classes ) ) {
			$wrapper_classes[] = 'mb-10';
		}

		$wrapper_classes  = array_merge( $wrapper_classes, $classes );
		$col_span_classes = html()->get_col_span_classes( $columns, 'grid' );
		$wrapper_classes  = array_merge( $wrapper_classes, $col_span_classes );

		$output = '';

		// Group wrapper start
		$output .= html()->div_start( array(), false );

		if ( ! empty( $title ) ) {
			$heading = new Heading_Control( array(
				'id'   => "{$id_base}-head",
				'args' => array(
					'level' => 3,
					'text'  => $title,
				),
			) );

			if ( ! $heading->has_errors() ) {
				$output .= $heading->render( false );
			} else {
				$heading->log_errors( $this->get_view_id() );
			}
		}

		// Card wrapper start
		$output .= html()->div_start( array( 'class' => $wrapper_classes ), false );

		$i = 0;

		foreach ( $cards as $card ) {
			$number = $i++;

			if ( ! $card instanceof Card_Control && ! $card instanceof Creative_Card_Control ) {
				$card = new Card_Control( array(
					'id'      => "{$id_base}-card-{$number}",
					'view_id' => $this->get_view_id(),
					'section' => $this->get_prop( 'section' ),
					'args'    => $card,
				) );
			}

			if ( false === $card->get_argument( 'layout', false ) ) {
				$card->set_argument( 'layout', $card_layout );
			}

			$card->set_argument( 'show_empty', $show_empty );

			if ( true !== $card->can_render() ) {
				continue;
			} elseif ( true === $card->has_errors() ) {
				$card->log_errors();
			} else {
				$output .= $card->render( false );
			}
		}

		// Card wrapper end
		$output .= html()->div_end( false );

		// Group wrapper end
		$output .= html()->div_end( false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

}
