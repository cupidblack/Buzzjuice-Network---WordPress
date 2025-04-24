<?php
/**
 * Views: Feedback View
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components\Views;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces\View;

#[\AllowDynamicProperties]

/**
 * Sets up the Feedback view.
 *
 * @since 1.0.0
 */
class Feedback_View implements View {

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {
		return array(
			'feedback' => array(
				'priority' => 1,
				'wrapper'  => false,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
			),
		);
	}

	/**
	 * Retrieves the view controls.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_controls() {
		$controls = array();

		// Wrapper div.
		$controls[] = new Controls\Wrapper_Control( array(
			'id'      => 'wrapper',
			'view_id' => 'feedback',
			'section' => 'wrapper',
			'atts'    => array(
				'id' => 'affwp-affiliate-dashboard-feedback',
			),
		) );

		// Copy.
		$controls[] = new Controls\Paragraph_Control( array(
			'id'      => 'affwp-feedback-copy',
			'view_id' => 'feedback',
			'section' => 'feedback',
			'args'    => array(
				'text' => __( 'AffiliateWP (the software company who created this affiliate portal) would love to hear about your experience using this affiliate portal. No personal data will be collected and any feedback provided will only be used to help improve your experience.', 'affiliatewp-affiliate-portal' ),
			),
		) );

		// Submit your feedback.
		$controls[] = new Controls\Link_Control( array(
			'id'      => 'affwp-feedback-submit-feedback',
			'view_id' => 'feedback',
			'section' => 'feedback',
			'args'    => array(
				'label'         => __( 'Submit your feedback', 'affiliatewp-affiliate-portal' ),
				'icon_position' => 'after',
				'icon'          => new Controls\Icon_Control( array(
					'id'   => 'affwp-feedback-submit-feedback-icon',
					'args' => array(
						'name' => 'external-link',
					),
				) ),
			),
			'atts'    => array(
				'href'   => 'https://docs.google.com/forms/d/e/1FAIpQLSfg7NQXsambmJEItX_ok0j3VQG3Mgbo2yM8EjN5Iixfe-fUew/viewform',
				'target' => '_blank',
				'class'  => array(
					'inline-flex',
					'items-center',
					'py-2',
					'px-4',
					'border',
					'border-transparent',
					'text-sm',
					'leading-5',
					'font-medium',
					'rounded-md',
					'text-white',
					'bg-indigo-600',
					'shadow-sm',
					'hover:bg-indigo-500',
					'focus:outline-none',
					'focus:shadow-outline-blue',
					'active:bg-indigo-600',
					'transition',
					'duration-150',
					'ease-in-out',
				),
			),
		) );

		return $controls;
	}

}
