<?php
/**
 * View: Course Accordion Topic Attribute - Virtual.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var Topic    $topic Topic model object.
 * @var Template $this  Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Topic;
use LearnDash\Core\Template\Template;

if (
	! $topic->is_external()
	|| ! $topic->is_virtual()
) {
	return;
}

$tooltip = __( 'Virtual (Optional)', 'learndash' );

if ( $topic->is_attendance_required() ) {
	$tooltip = __( 'Virtual (Required)', 'learndash' );
}

?>
<div
	class="ld-accordion__item-attribute ld-accordion__item-attribute--virtual ld-accordion__item-attribute--collapsed"
	data-ld-tooltip-text="<?php echo esc_attr( $tooltip ); ?>"
	tabindex="0"
>
	<?php
	$this->template(
		'components/icons/computer',
		[
			'classes'        => [ 'ld-accordion__item-attribute-icon' ],
			'is_aria_hidden' => true,
		]
	);
	?>
</div>
