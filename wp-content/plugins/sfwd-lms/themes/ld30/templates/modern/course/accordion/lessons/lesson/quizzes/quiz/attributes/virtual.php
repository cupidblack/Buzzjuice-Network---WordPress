<?php
/**
 * View: Course Accordion Lesson Quiz Attribute - Virtual.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var Quiz     $quiz The quiz object.
 * @var Template $this Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Quiz;
use LearnDash\Core\Template\Template;

if (
	! $quiz->is_external()
	|| ! $quiz->is_virtual()
) {
	return;
}

$tooltip = __( 'Virtual (Optional)', 'learndash' );

if ( $quiz->is_attendance_required() ) {
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
