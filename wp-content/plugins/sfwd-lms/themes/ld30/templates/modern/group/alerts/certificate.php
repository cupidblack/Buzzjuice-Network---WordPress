<?php
/**
 * View: Group Header - Certificate Link.
 *
 * @since 4.22.0
 * @version 4.22.0
 *
 * @var Group    $group Group model.
 * @var WP_User  $user  Current user.
 * @var Template $this  Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Group;
use LearnDash\Core\Template\Template;

$certificate_link = $group->get_certificate_link( $user );

if ( empty( $certificate_link ) ) {
	return;
}

$this->template(
	'modules/alert',
	[
		'type'    => 'success ld-alert-certificate',
		'icon'    => 'certificate',
		'message' => __( "You've earned a certificate!", 'learndash' ),
		'button'  => [
			'url'    => $certificate_link,
			'icon'   => 'download',
			'label'  => __( 'Download Certificate', 'learndash' ),
			'target' => '_new',
		],
	]
);
