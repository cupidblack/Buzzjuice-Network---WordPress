<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

$rules = array(
	'rules' => array(
		'home' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wcarc_rules_tab',
		),
	),
);

return apply_filters( 'yith_wcarc_panel_rules_tab', $rules );
