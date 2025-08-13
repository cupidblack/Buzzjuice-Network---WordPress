<?php
/**
 * View: Group Header - Progress.
 *
 * @since 4.22.0
 * @version 4.22.0
 *
 * @var bool     $has_access Whether the user has access to the group or not.
 * @var Group    $group      Group model.
 * @var WP_User  $user       Current user.
 * @var Template $this       Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Group;
use LearnDash\Core\Template\Template;

// Bail if user does not have access to the group. We are showing the progress only to users who have access.
if ( ! $has_access ) {
	return;
}

$this->template(
	'modules/infobar/group',
	[
		'group_id'     => $group->get_id(),
		'group_status' => learndash_get_user_group_status( $group->get_id(), $user->ID, true ),
		'has_access'   => $has_access,
		'post'         => $group->get_post(),
		'user_id'      => $user->ID,
	]
);
