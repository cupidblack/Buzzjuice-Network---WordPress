<?php
/**
 * Templates: Payouts Service Add Payout Method
 *
 * This template is used to display the link to add a payout method after creating a Payouts Service account.
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

$payouts_service_account_meta = affwp_get_affiliate_meta( affwp_get_affiliate_id(), 'payouts_service_account', true );

$current_page_url = Portal::get_page_url( 'settings' );

$add_payout_method_url = add_query_arg( 'redirect_url', urlencode( $current_page_url ), PAYOUTS_SERVICE_URL . '/account/' . $payouts_service_account_meta['link_id'] );

$payouts_service_errors = affiliate_wp()->affiliates->payouts->service_register->get_errors();

if ( ! empty( $payouts_service_errors ) ) {
	foreach ( $payouts_service_errors as $payouts_service_error ) {
		html()->notice( array(
			'message' => $payouts_service_error,
			'type'    => 'error',
		) );
	}
}

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
	$ps_heading->log_errors( 'add-payout-method' );
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

$ps_add_payout_method_desc = new Controls\Paragraph_Control( array(
	'id'   => 'payouts-service-add-payout-method-desc',
	'atts' => array(
		'class' => array(
			'mb-0',
			'text-sm',
			'leading-5',
			'text-gray-600',
		),
	),
	'args' => array(
		'text' => __( 'To receive your affiliate earnings, add a payout method below.', 'affiliatewp-affiliate-portal' ),
	),
) );

if ( ! $ps_add_payout_method_desc->has_errors() ) {
	$ps_add_payout_method_desc->render();
} else {
	$ps_add_payout_method_desc->log_errors( 'add-payout-method' );
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

$ps_add_payout_method_link = new Controls\Link_Control( array(
	'id'   => 'payouts-service-add-payout-method-link',
	'atts' => array(
		'href'  => esc_url( $add_payout_method_url ),
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
		'label'         => __( 'Add payout method', 'affiliatewp-affiliate-portal' ),
		'icon'          => new Controls\Icon_Control( array(
			'id' => 'payouts-service-add-payout-method-link-icon',
			'args' => array(
				'name' => 'external-link',
			),
		) ),
		'icon_position' => 'after',
	),
) );

if ( ! $ps_add_payout_method_link->has_errors() ) {
	$ps_add_payout_method_link->render();
} else {
	$ps_add_payout_method_link->log_errors( 'add-payout-method' );
}

html()->div_end();

html()->div_end();

html()->div_end();

html()->div_end();