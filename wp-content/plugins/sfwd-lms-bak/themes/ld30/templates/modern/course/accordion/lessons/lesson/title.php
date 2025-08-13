<?php
/**
 * View: Course Accordion Lesson - Title.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var bool   $has_access Whether the user has access to the course or not.
 * @var Lesson $lesson Lesson model object.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Lesson;

?>
<a
	class="ld-accordion__item-title ld-accordion__item-title--lesson"
	<?php if ( ! $has_access && ! $lesson->is_sample() ) : ?>
		data-ld-tooltip-text="<?php esc_html_e( "You don't currently have access to this content", 'learndash' ); ?>"
	<?php endif; ?>
	href="<?php echo esc_url( $lesson->get_permalink() ); ?>"
>
	<?php echo wp_kses_post( $lesson->get_title() ); ?>
</a>
