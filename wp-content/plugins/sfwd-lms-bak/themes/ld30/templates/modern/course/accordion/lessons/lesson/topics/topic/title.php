<?php
/**
 * View: Course Accordion Topic - Title.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var bool  $has_access Whether the user has access to the course or not.
 * @var Topic $topic Topic model object.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Topic;

?>
<a
	class="ld-accordion__item-title ld-accordion__item-title--topic"
	<?php if ( ! $has_access && ! $topic->is_sample() ) : ?>
		data-ld-tooltip-text="<?php esc_html_e( "You don't currently have access to this content", 'learndash' ); ?>"
	<?php endif; ?>
	href="<?php echo esc_url( $topic->get_permalink() ); ?>"
>
	<?php echo wp_kses_post( $topic->get_title() ); ?>
</a>
