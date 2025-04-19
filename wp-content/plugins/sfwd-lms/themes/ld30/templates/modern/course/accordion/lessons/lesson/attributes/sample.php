<?php
/**
 * View: Course Accordion Lesson Attribute - Sample.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var Lesson   $lesson Lesson model object.
 * @var Template $this   Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Lesson;
use LearnDash\Core\Template\Template;

if ( ! $lesson->is_sample() ) {
	return;
}

$tooltip = sprintf(
	// translators: %s: Lesson custom label.
	esc_html__( 'Sample %s', 'learndash' ),
	esc_html( learndash_get_custom_label( 'lesson' ) ),
);

?>
<div
	class="ld-accordion__item-attribute ld-accordion__item-attribute--sample ld-accordion__item-attribute--collapsible"
	data-ld-tooltip-text="<?php echo esc_attr( $tooltip ); ?>"
	tabindex="0"
>
	<?php
	$this->template(
		'components/icons/lock',
		[
			'classes'        => [ 'ld-accordion__item-attribute-icon' ],
			'is_aria_hidden' => true,
		]
	);
	?>

	<span class="ld-accordion__item-attribute-label ld-accordion__item-attribute-label--sample ld-accordion__item-attribute-label--collapsible">
		<?php echo esc_html( $tooltip ); ?>
	</span>
</div>
