<?php
/**
 * Templates: Payouts Service Payout Method
 *
 * This template is used to display the payout method for the affiliate on the Payouts Service.
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Templates
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

use AffiliateWP_Affiliate_Portal\Core\Components\Portal;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use function AffiliateWP_Affiliate_Portal\html;

$affiliate_id       = affwp_get_affiliate_id();
$payout_method_meta = affwp_get_affiliate_meta( $affiliate_id, 'payouts_service_payout_method', true );
$payout_method      = $payout_method_meta['payout_method'];
$current_page_url   = Portal::get_page_url( 'settings' );

$query_args = array(
	'affwp_action'     => 'payouts_service_change_payout_method',
	'current_page_url' => urlencode($current_page_url),
);

$change_payout_method_url = remove_query_arg(array('affwp_notice', 'email'));
$change_payout_method_url = wp_nonce_url(add_query_arg($query_args, $change_payout_method_url), 'payouts_service_change_payout_method', 'payouts_service_change_payout_method_nonce');

$payouts_service_errors = affiliate_wp()->affiliates->payouts->service_register->get_errors();

if ( ! empty( $payouts_service_errors ) ) {
	foreach ( $payouts_service_errors as $payouts_service_error ) {
		html()->notice( array(
			'message' => $payouts_service_error,
			'type'    => 'error',
		) );
	}
}

if ( ! empty( $_REQUEST['affwp_notice'] ) && 'change-payout-method' === $_REQUEST['affwp_notice'] ) :
	$email = ! empty( $_REQUEST['email'] ) ? sanitize_text_field( $_REQUEST['email'] ) : '';

	html()->notice( array(
		/* translators: Payment email address */
		'message' => sprintf( __( 'An email has been sent to %s with a link to change the payout method', 'affiliatewp-affiliate-portal' ),
			$email
		),
	) );

endif;

html()->div_start( array(
	'class' => array(
		'md:grid',
		'md:grid-cols-3',
		'md:gap-6',
	),
) );

html()->div_start( array(
	'class' => 'md:col-span-1',
) );

html()->div_start();

$ps_heading = new Controls\Heading_Control( array(
	'id'   => 'payouts-service-heading',
	'args' => array(
		'text'  => __( 'Payout settings', 'affiliatewp-affiliate-portal' ),
		'level' => 3,
	),
) );

if ( ! $ps_heading->has_errors() ) {
	$ps_heading->render();
} else {
	$ps_heading->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_end();

html()->div_start( array(
	'class' => array(
		'mt-5',
		'md:mt-0',
		'md:col-span-2',
	),
) );

html()->div_start( array(
	'class' => array(
		'shadow',
		'overflow-hidden',
		'sm:rounded-md',
	),
) );

html()->div_start( array(
	'class' => array(
		'px-4',
		'py-5',
		'bg-white',
		'sm:p-6',
	),
) );

if ( 'bank_account' === $payout_method ) {

	$ps_account_desc = new Controls\Paragraph_Control( array(
		'id'   => 'payouts-service-payout-method-account',
		'atts' => array(
			'class' => array(
				'mb-5',
				'text-sm',
				'leading-5',
				'text-gray-600',
			),
		),
		'args' => array(
			'text' => __('Your earnings will be paid into the following account.', 'affiliatewp-affiliate-portal'),
		),
	) );

	if ( ! $ps_account_desc->has_errors() ) {
		$ps_account_desc->render();
	} else {
		$ps_account_desc->log_errors( 'payouts-service-payout-method' );
	}

	html()->element_start( 'dl' );

	html()->div_start( array(
		'class' => array(
			'sm:grid',
			'sm:grid-cols-3',
			'sm:gap-4',
			'sm:py-5',
			'sm:pt-0',
		),
	) );

	html()->dt( array(
		'text' => __('Bank name', 'affiliatewp-affiliate-portal'),
		'class' => array(
			'text-sm',
			'leading-5',
			'font-medium',
			'text-gray-500',
		),
	));

	html()->dd( array(
		'text' => $payout_method_meta['bank_name'],
		'class' => array(
			'mt-1',
			'text-sm',
			'leading-5',
			'text-gray-900',
			'sm:mt-0',
			'sm:col-span-2',
		),
	));

	html()->div_end();

	html()->div_start( array(
		'class' => array(
			'mt-8',
			'sm:mt-0',
			'sm:grid',
			'sm:grid-cols-3',
			'sm:gap-4',
			'sm:border-t',
			'sm:border-gray-200',
			'sm:py-5',
		),
	) );

	html()->dt( array(
		'text' => __('Account holder name', 'affiliatewp-affiliate-portal'),
		'class' => array(
			'text-sm',
			'leading-5',
			'font-medium',
			'text-gray-500',
		),
	));

	html()->dd( array(
		'text' => $payout_method_meta['account_name'],
		'class' => array(
			'mt-1',
			'text-sm',
			'leading-5',
			'text-gray-900',
			'sm:mt-0',
			'sm:col-span-2',
		),
	));

	html()->div_end();

	html()->div_start( array(
		'class' => array(
			'mt-8',
			'sm:mt-0',
			'sm:grid',
			'sm:grid-cols-3',
			'sm:gap-4',
			'sm:border-t',
			'sm:border-gray-200',
			'sm:py-5',
			'sm:pb-0',
		),
	) );

	html()->dt( array(
		'text' => __('Account number', 'affiliatewp-affiliate-portal'),
		'class' => array(
			'text-sm',
			'leading-5',
			'font-medium',
			'text-gray-500',
		),
	));

	html()->dd( array(
		'text' => $payout_method_meta['account_no'],
		'class' => array(
			'mt-1',
			'text-sm',
			'leading-5',
			'text-gray-900',
			'sm:mt-0',
			'sm:col-span-2',
		),
	));

	html()->div_end();

	html()->element_end( 'dl' );

} else {

	$ps_card_desc = new Controls\Paragraph_Control( array(
		'id'   => 'payout-method-card',
		'atts' => array(
			'class' => array(
				'mb-5',
				'text-sm',
				'leading-5',
				'text-gray-600',
			),
		),
		'args' => array(
			'text' => __('Your earnings will be paid to the following card.', 'affiliatewp-affiliate-portal'),
		),
	) );

	if ( ! $ps_card_desc->has_errors() ) {
		$ps_card_desc->render();
	} else {
		$ps_card_desc->log_errors( 'payouts-service-register' );
	}

	html()->element_start( 'dl' );

	html()->div_start( array(
		'class' => array(
			'sm:grid',
			'sm:grid-cols-3',
			'sm:gap-4',
			'sm:py-5',
			'sm:pt-0',
		),
	) );

	html()->dt( array(
		'text' => __('Card', 'affiliatewp-affiliate-portal'),
		'class' => array(
			'text-sm',
			'leading-5',
			'font-medium',
			'text-gray-500',
		),
	));

	html()->dd( array(
		'text' => $payout_method_meta['card'],
		'class' => array(
			'mt-1',
			'text-sm',
			'leading-5',
			'text-gray-900',
			'sm:mt-0',
			'sm:col-span-2',
		),
	));

	html()->div_end();

	html()->div_start( array(
		'class' => array(
			'mt-8',
			'sm:mt-0',
			'sm:grid',
			'sm:grid-cols-3',
			'sm:gap-4',
			'sm:border-t',
			'sm:border-gray-200',
			'sm:py-5',
		),
	) );

	html()->dt( array(
		'text' => __('Expiry', 'affiliatewp-affiliate-portal'),
		'class' => array(
			'text-sm',
			'leading-5',
			'font-medium',
			'text-gray-500',
		),
	));

	html()->dd( array(
		'text' => $payout_method_meta['expiry'],
		'class' => array(
			'mt-1',
			'text-sm',
			'leading-5',
			'text-gray-900',
			'sm:mt-0',
			'sm:col-span-2',
		),
	));

	html()->div_end();

	html()->element_end( 'dl' );

}

html()->div_end();

html()->div_start( array(
	'class' => array(
		'px-4',
		'py-3',
		'bg-gray-50',
		'sm:px-6',
	),
) );

$ps_change_payout_method = new Controls\Link_Control( array(
	'id'   => 'payouts-service-change-payout-method-link',
	'atts' => array(
		'href'  => esc_url( $change_payout_method_url ),
		'class' => array(
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
	'args' => array(
		'label' => __( 'Change payout method', 'affiliatewp-affiliate-portal' ),
	),
) );

if ( ! $ps_change_payout_method->has_errors() ) {
	$ps_change_payout_method->render();
} else {
	$ps_change_payout_method->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_end();

html()->div_end();

html()->div_end();
