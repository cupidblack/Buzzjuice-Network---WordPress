<?php
/**
 * View: Course Accordion Lesson Quiz - Title.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var bool $has_access Whether the user has access to the course or not.
 * @var Quiz $quiz Quiz model object.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Quiz;

?>
<a
	class="ld-accordion__item-title ld-accordion__item-title--lesson-quiz"
	<?php if ( ! $has_access && ! $quiz->is_sample() ) : ?>
		data-ld-tooltip-text="<?php esc_html_e( "You don't currently have access to this content", 'learndash' ); ?>"
	<?php endif; ?>
	href="<?php echo esc_url( $quiz->get_permalink() ); ?>"
>
	<?php echo wp_kses_post( $quiz->get_title() ); ?>
</a>
