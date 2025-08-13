<?php
/**
 * This class provides the easy way to operate a group.
 *
 * @since 4.6.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Models;

use LDLMS_Post_Types;
use LearnDash\Core\Models\Traits\Has_Materials;
use LearnDash\Core\Utilities\Cast;
use WP_User;
use WP_Query;
use WP_Post;

/**
 * Group model class.
 *
 * @since 4.6.0
 */
class Group extends Post {
	use Has_Materials;

	/**
	 * Returns allowed post types.
	 *
	 * @since 4.6.0
	 *
	 * @return string[]
	 */
	public static function get_allowed_post_types(): array {
		return array(
			LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::GROUP ),
		);
	}

	/**
	 * Returns a product model based on the group.
	 *
	 * @since 4.6.0
	 *
	 * @return Product
	 */
	public function get_product(): Product {
		/**
		 * Filters a group product.
		 *
		 * @since 4.22.0
		 *
		 * @param Product $product Product model.
		 * @param Group   $group   Group model.
		 *
		 * @return Product Product model.
		 */
		return apply_filters(
			'learndash_model_group_product',
			Product::create_from_post( $this->get_post() ),
			$this
		);
	}

	/**
	 * Returns true if a group has awards, otherwise false.
	 *
	 * @since 4.22.0
	 *
	 * @return bool
	 */
	public function has_awards(): bool {
		/**
		 * Filters whether a group has awards.
		 *
		 * @since 4.22.0
		 *
		 * @param bool  $has_awards Whether a group has awards.
		 * @param Group $group      Group model.
		 *
		 * @return bool Whether a group has awards.
		 */
		return apply_filters(
			'learndash_model_group_has_awards',
			$this->get_award_certificate() instanceof Certificate,
			$this
		);
	}

	/**
	 * Returns a certificate award or null if not set.
	 *
	 * @since 4.22.0
	 *
	 * @return Certificate|null
	 */
	public function get_award_certificate(): ?Certificate {
		$certificate_id = Cast::to_int(
			$this->getAttribute( '_ld_certificate' )
		);

		/**
		 * Filters a group certificate award.
		 *
		 * @since 4.22.0
		 *
		 * @param Certificate|null $certificate Certificate model or null if not found.
		 * @param Group            $group       Group model.
		 *
		 * @return Certificate|null Group certificate award.
		 */
		return apply_filters(
			'learndash_model_group_award_certificate',
			Certificate::find( $certificate_id ),
			$this
		);
	}

	/**
	 * Returns a certificate link for a user.
	 *
	 * @since 4.22.0
	 *
	 * @param WP_User|int|null $user The user ID or WP_User. If null or empty, the current user is used.
	 *
	 * @return string
	 */
	public function get_certificate_link( $user = null ): string {
		$user    = $this->map_user( $user );
		$user_id = $user instanceof WP_User ? $user->ID : $user;

		/**
		 * Filters a group certificate link.
		 *
		 * @since 4.22.0
		 *
		 * @param string      $url    Group certificate link.
		 * @param Group       $group  Group model.
		 * @param WP_User|int $user   The WP_User by default or the user ID if a user ID was passed explicitly to the filter's caller.
		 *
		 * @return string Group certificate link.
		 */
		return apply_filters(
			'learndash_model_group_certificate_link',
			learndash_get_group_certificate_link( $this->get_id(), $user_id ),
			$this,
			$user
		);
	}

	/**
	 * Returns related courses models.
	 *
	 * @since 4.22.0
	 *
	 * @param int $limit  Optional. Limit. Default 0.
	 * @param int $offset Optional. Offset. Default 0.
	 *
	 * @return Course[]
	 */
	public function get_courses( int $limit = 0, int $offset = 0 ): array {
		$query_args = array_merge(
			[
				'offset'         => $offset,
				'meta_query'     => [
					[
						'key'     => 'learndash_group_enrolled_' . $this->get_id(),
						'compare' => 'EXISTS',
					],
				],
				'post_type'      => LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::COURSE ),
				'posts_per_page' => $limit > 0 ? $limit : -1,
			],
			learndash_get_group_courses_order( $this->get_id() )
		);

		$query = new WP_Query( $query_args );

		/**
		 * Forcing it as it can also be int[] according to docs, but it's an array of posts here.
		 *
		 * @var WP_Post[] $posts
		 */
		$posts = $query->posts;

		/**
		 * Filters group courses.
		 *
		 * @since 4.22.0
		 *
		 * @param Course[] $courses Courses.
		 * @param Group    $group   Group model.
		 *
		 * @return Course[]
		 */
		return apply_filters(
			'learndash_model_group_courses',
			Course::create_many_from_posts( $posts ),
			$this
		);
	}

	/**
	 * Returns the total number of related courses.
	 *
	 * @since 4.22.0
	 *
	 * @return int
	 */
	public function get_courses_number(): int {
		$query_args = [
			'post_type'      => LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::COURSE ),
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'meta_query'     => [
				[
					'key'     => 'learndash_group_enrolled_' . $this->get_id(),
					'compare' => 'EXISTS',
				],
			],
		];

		$query = new WP_Query( $query_args );

		/**
		 * Filters group courses number.
		 *
		 * @since 4.22.0
		 *
		 * @param int   $number Number of courses.
		 * @param Group $group  Group model.
		 *
		 * @return int Number of courses.
		 */
		return apply_filters(
			'learndash_model_group_courses_number',
			$query->found_posts,
			$this
		);
	}
}
