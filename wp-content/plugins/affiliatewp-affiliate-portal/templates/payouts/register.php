<?php
/**
 * Templates: Payouts Service Registration Form
 *
 * This template is used to display the Payouts Service registration form.
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

$affiliate_id     = affwp_get_affiliate_id();
$user_email       = ! empty( $_REQUEST['email'] ) ? sanitize_text_field( $_REQUEST['email'] ) : affwp_get_affiliate_email( $affiliate_id );
$first_name       = ! empty( $_REQUEST['first_name'] ) ? sanitize_text_field( $_REQUEST['first_name'] ) : affwp_get_affiliate_first_name( $affiliate_id );
$last_name        = ! empty( $_REQUEST['last_name'] ) ? sanitize_text_field( $_REQUEST['last_name'] ) : affwp_get_affiliate_last_name( $affiliate_id );
$selected_country = ! empty( $_REQUEST['country'] ) ? sanitize_text_field( $_REQUEST['country'] ) : '';
$account_type     = ! empty( $_REQUEST['account_type'] ) ? sanitize_text_field( $_REQUEST['account_type'] ) : 'individual';
$business_name    = ! empty( $_REQUEST['business_name'] ) ? sanitize_text_field( $_REQUEST['business_name'] ) : '';
$business_owner   = ! empty( $_REQUEST['business_owner'] ) ? sanitize_text_field( $_REQUEST['business_owner'] ) : '';
$day_of_birth     = ! empty( $_REQUEST['day_of_birth'] ) ? sanitize_text_field( $_REQUEST['day_of_birth'] ) : '';
$month_of_birth   = ! empty( $_REQUEST['month_of_birth'] ) ? sanitize_text_field( $_REQUEST['month_of_birth'] ) : '';
$year_of_birth    = ! empty( $_REQUEST['year_of_birth'] ) ? sanitize_text_field( $_REQUEST['year_of_birth'] ) : '';
$errors           = affiliate_wp()->affiliates->payouts->service_register->get_errors();
$error_codes      = affiliate_wp()->affiliates->payouts->service_register->get_error_codes();

$current_page_url = Portal::get_page_url( 'settings' );

$payouts_service_description = affiliate_wp()->settings->get( 'payouts_service_description', '' );

$query_args          = array(
	'affwp_action'     => 'payouts_service_connect_account',
	'current_page_url' => urlencode( $current_page_url ),
);
$connect_account_url = wp_nonce_url( add_query_arg( $query_args ), 'payouts_service_connect_account', 'payouts_service_connect_account_nonce' );

$months = array(
	'1'  => __( 'January', 'affiliatewp-affiliate-portal' ),
	'2'  => __( 'February', 'affiliatewp-affiliate-portal' ),
	'3'  => __( 'March', 'affiliatewp-affiliate-portal' ),
	'4'  => __( 'April', 'affiliatewp-affiliate-portal' ),
	'5'  => __( 'May', 'affiliatewp-affiliate-portal' ),
	'6'  => __( 'June', 'affiliatewp-affiliate-portal' ),
	'7'  => __( 'July', 'affiliatewp-affiliate-portal' ),
	'8'  => __( 'August', 'affiliatewp-affiliate-portal' ),
	'9'  => __( 'September', 'affiliatewp-affiliate-portal' ),
	'10' => __( 'October', 'affiliatewp-affiliate-portal' ),
	'11' => __( 'November', 'affiliatewp-affiliate-portal' ),
	'12' => __( 'December', 'affiliatewp-affiliate-portal' ),
);

$years = array_reverse( range( 1905, date( 'Y' ) ) );

$payouts_service_errors = affiliate_wp()->affiliates->payouts->service_register->get_errors();

if ( ! empty( $errors ) && ! ( in_array( 'service_account_not_created', $error_codes ) || in_array( 'http_request_failed', $error_codes ) ) ) {

	if ( ! in_array( 'empty_country', $error_codes ) ) {
		$selected_country = sanitize_text_field( $_POST['country'] );
	}

	if ( ! in_array( 'empty_account_type', $error_codes ) ) {
		$account_type = sanitize_text_field( $_POST['account_type'] );
	}

	if ( ! in_array( 'empty_business_name', $error_codes ) ) {
		$business_name = sanitize_text_field( $_POST['business_name'] );
	}

	if ( ! in_array( 'empty_first_name', $error_codes ) ) {
		$first_name = sanitize_text_field( $_POST['first_name'] );
	}

	if ( ! in_array( 'empty_last_name', $error_codes ) ) {
		$last_name = sanitize_text_field( $_POST['last_name'] );
	}
	if ( ! in_array( 'empty_day_of_birth', $error_codes ) ) {
		$day_of_birth = sanitize_text_field( $_POST['day_of_birth'] );
	}

	if ( ! in_array( 'empty_month_of_birth', $error_codes ) ) {
		$month_of_birth = sanitize_text_field( $_POST['month_of_birth'] );
	}

	if ( ! in_array( 'empty_year_of_birth', $error_codes ) ) {
		$year_of_birth = sanitize_text_field( $_POST['year_of_birth'] );
	}

}

if ( ! empty( $payouts_service_errors ) ) {
	html()->notice( array(
		'message' => implode( '<br>', $payouts_service_errors ),
		'type'    => 'error',
	) );
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
	$ps_heading->log_errors( 'payouts-service-register' );
}

$ps_connect_account_link = new Controls\Link_Control( array(
	'id'   => 'payouts-service-connect-account-link',
	'atts' => array(
		'class' => array(
			'underline',
			'text-indigo-500',
		),
		'href'  => esc_url( $connect_account_url ),
	),
	'args' => array(
		'label' => __( 'Connect it here', 'affiliatewp-affiliate-portal' ),
	),
) );

if ( ! $ps_connect_account_link->has_errors() ) {
	$link = $ps_connect_account_link->render( false );
} else {
	$link = '';
}

$ps_prompt_description = new Controls\Paragraph_Control( array(
	'id'   => 'payouts-service-prompt',
	'atts' => array(
		'class' => array(
			'mt-1',
			'text-sm',
			'leading-5',
			'text-gray-600',
		),
	),
	'args' => array(
		/* translators: 1: payouts service name, 2: Link HTML */
		'text' => sprintf( __( 'Already have a %1$s account? %2$s', 'affiliatewp-affiliate-portal' ), PAYOUTS_SERVICE_NAME, $link ),
	),
) );

if ( ! $ps_prompt_description->has_errors() ) {
	$ps_prompt_description->render();
} else {
	$ps_prompt_description->log_errors( 'payouts-service-register' );
}

if ( $payouts_service_description ) {
	$ps_description = new Controls\Paragraph_Control( array(
		'id'   => 'payouts-service-desc',
		'atts' => array(
			'class' => array(
				'mt-5',
				'text-sm',
				'leading-5',
				'text-gray-600',
			),
		),
		'args' => array(
			'text' => wp_kses_post( nl2br( $payouts_service_description ) ),
		),
	) );

	if ( ! $ps_description->has_errors() ) {
		$ps_description->render();
	} else {
		$ps_description->log_errors( 'payouts-service-register' );
	}
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

html()->form_start( array(
	'id'     => 'payouts-service-form',
	'class'  => 'mb-0',
	'method' => 'post',
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

html()->div_start( array(
	'class'      => array(
		'grid',
		'grid-cols-6',
		'gap-6',
	),
	'directives' => array(
		'x-data' => "{ accountType: '$account_type' }",
	),
) );

html()->div_start( array(
	'class' => array(
		'col-span-6',
		'sm:col-span-6',
	),
) );

$ps_account_type = new Controls\Select_Control( array(
	'id'     => 'payouts-service-account-type',
	'args'   => array(
		'label'       => __( 'Account type', 'affiliatewp-affiliate-portal' ),
		'options'     => array(
			'individual' => __( 'Personal account', 'affiliatewp-affiliate-portal' ),
			'company'    => __( 'Business account', 'affiliatewp-affiliate-portal' ),
		),
		'selected'    => $account_type,
		'label_class' => array(
			'block',
			'text-sm',
			'font-medium',
			'leading-5',
			'text-gray-700',
		),
	),
	'alpine' => array(
		'x-model'  => 'accountType',
		'@change'  => 'accountType = $event.target.value',
		'x-spread' => '',
	),
	'atts'   => array(
		'name'     => 'account_type',
		'required' => 'required',
		'class'    => array(
			'mt-1',
			'block',
			'form-select',
			'w-full',
			'py-2',
			'px-3',
			'border',
			'border-gray-300',
			'bg-white',
			'rounded-md',
			'shadow-sm',
			'focus:outline-none',
			'focus:shadow-outline-blue',
			'focus:border-blue-300',
			'transition',
			'duration-150',
			'ease-in-out',
			'sm:text-sm',
			'sm:leading-5',
		),
	),
) );

if ( ! $ps_account_type->has_errors() ) {
	$ps_account_type->render();
} else {
	$ps_account_type->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_start( array(
	'class' => array(
		'col-span-6',
		'sm:col-span-6',
		'affwp-payout-service-country-wrap',
	),
) );

$countries = array_merge( array( '' => '' ), affwp_get_payouts_service_country_list() );

$ps_country = new Controls\Select_Control( array(
	'id'     => 'payouts-service-country',
	'args'   => array(
		'label'       => __( 'Country of residence', 'affiliatewp-affiliate-portal' ),
		'options'     => $countries,
		'selected'    => $selected_country,
		'label_class' => array(
			'block',
			'text-sm',
			'font-medium',
			'leading-5',
			'text-gray-700',
		),
	),
	'alpine' => array(
		'x-spread' => '',
	),
	'atts'   => array(
		'name'     => 'country',
		'required' => 'required',
		'class'    => array(
			'mt-1',
			'block',
			'form-select',
			'w-full',
			'py-2',
			'px-3',
			'border',
			'border-gray-300',
			'bg-white',
			'rounded-md',
			'shadow-sm',
			'focus:outline-none',
			'focus:shadow-outline-blue',
			'focus:border-blue-300',
			'transition',
			'duration-150',
			'ease-in-out',
			'sm:text-sm',
			'sm:leading-5',
		),
	),
) );

if ( ! $ps_country->has_errors() ) {
	$ps_country->render();
} else {
	$ps_country->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_start( array(
	'class'      => array(
		'col-span-6',
		'sm:col-span-6',
	),
	'directives' => array(
		'x-show' => "'company' === accountType",
	),
) );

$ps_business_name = new Controls\Text_Input_Control( array(
	'id'     => 'payouts-service-business-name',
	'args'   => array(
		'label'       => __( 'Your business name', 'affiliatewp-affiliate-portal' ),
		'label_class' => array(
			'block',
			'text-sm',
			'font-medium',
			'leading-5',
			'text-gray-700',
		),
	),
	'atts'   => array(
		'name'  => 'business_name',
		'value' => esc_attr( $business_name ),
	),
	'alpine' => array(
		'x-bind:required' => "'company' === accountType",
		'x-spread'        => '',
		':class'          => '{}',
		':aria-invalid'   => '',
	),
) );

if ( ! $ps_business_name->has_errors() ) {
	$ps_business_name->render();
} else {
	$ps_business_name->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_start( array(
	'class'      => array(
		'col-span-6',
		'sm:col-span-6',
		'relative',
		'flex',
		'items-start',
	),
	'directives' => array(
		'x-show' => "'company' === accountType",
	),
) );

$ps_business_owner = new Controls\Checkbox_Control( array(
	'id'     => 'payouts-service-business-owner',
	'args'   => array(
		'label'       => __( 'I am the owner of the business legal entity', 'affiliatewp-affiliate-portal' ),
		'label_class' => array(
			'block',
			'text-sm',
			'font-medium',
			'leading-5',
			'text-gray-700',
		),
	),
	'alpine' => array(
		'x-spread' => '',
	),
	'atts'   => array(
		'checked'     => $business_owner ? true : false,
		'value'       => 1,
		'name'        => 'business_owner',
		'label_class' => array(
			'flex',
			'items-center',
			'text-sm',
			'font-medium',
			'leading-5',
			'text-gray-700',
		),
	),
) );

if ( ! $ps_business_owner->has_errors() ) {
	$ps_business_owner->render();
} else {
	$ps_business_owner->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_start( array(
	'class' => array(
		'col-span-6',
		'sm:col-span-3',
	),
) );

$ps_first_name = new Controls\Text_Input_Control( array(
	'id'     => 'payouts-service-first-name',
	'args'   => array(
		'label'       => __( 'First name', 'affiliatewp-affiliate-portal' ),
		'label_class' => array(
			'block',
			'text-sm',
			'font-medium',
			'leading-5',
			'text-gray-700',
		),
	),
	'alpine' => array(
		'x-spread'        => '',
		':class'          => '{}',
		':aria-invalid'   => '',
	),
	'atts'   => array(
		'name'     => 'first_name',
		'value'    => esc_attr( $first_name ),
		'required' => 'required',
	),
) );

if ( ! $ps_first_name->has_errors() ) {
	$ps_first_name->render();
} else {
	$ps_first_name->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_start( array(
	'class' => array(
		'col-span-6',
		'sm:col-span-3',
	),
) );

$ps_last_name = new Controls\Text_Input_Control( array(
	'id'     => 'payouts-service-last-name',
	'args'   => array(
		'label'       => __( 'Last name', 'affiliatewp-affiliate-portal' ),
		'label_class' => array(
			'block',
			'text-sm',
			'font-medium',
			'leading-5',
			'text-gray-700',
		),
	),
	'alpine' => array(
		'x-spread'        => '',
		':class'          => '{}',
		':aria-invalid'   => '',
	),
	'atts'   => array(
		'name'     => 'last_name',
		'value'    => esc_attr( $last_name ),
		'required' => 'required',
	),
) );

if ( ! $ps_last_name->has_errors() ) {
	$ps_last_name->render();
} else {
	$ps_last_name->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_start( array(
	'class' => array(
		'col-span-6',
		'sm:col-span-6',
	),
) );

$ps_email = new Controls\Email_Control( array(
	'id'     => 'payouts-service-email',
	'args'   => array(
		'label'       => __( 'Email address', 'affiliatewp-affiliate-portal' ),
		'label_class' => array(
			'block',
			'text-sm',
			'font-medium',
			'leading-5',
			'text-gray-700',
		),
	),
	'alpine' => array(
		'x-spread'        => '',
		':class'          => '{}',
		':aria-invalid'   => '',
	),
	'atts'   => array(
		'name'     => 'email',
		'value'    => esc_attr( $user_email ),
		'required' => 'required',
	),
) );

if ( ! $ps_email->has_errors() ) {
	$ps_email->render();
} else {
	$ps_email->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_start( array(
	'class' => array(
		'col-span-6',
		'sm:col-span-6',
	),
) );

$ps_dob_label = new Controls\Label_Control( array(
	'id'   => 'payouts-service-dob-label',
	'atts' => array(
		'for'   => 'day-of-birth',
		'class' => array(
			'block',
			'text-sm',
			'font-medium',
			'leading-5',
			'text-gray-700',
			'row-start-1',
		),
	),
	'args' => array(
		'value' => __( 'Date of birth', 'affiliatewp-affiliate-portal' ),
	),
) );

if ( ! $ps_dob_label->has_errors() ) {
	$ps_dob_label->render();
} else {
	$ps_dob_label->log_errors( 'payouts-service-register' );
}

$ps_day_of_birth = new Controls\Select_Control( array(
	'id'     => 'payouts-service-day-of-birth',
	'args'   => array(
		'options'  => array( '' => 'Day' ) + array_combine( range( 1, 31 ), range( 1, 31 ) ),
		'selected' => $day_of_birth,
	),
	'alpine' => array(
		'x-spread' => '',
	),
	'atts'   => array(
		'name'     => 'day_of_birth',
		'required' => 'required',
		'class'    => array(
			'mt-1',
			'mb-1',
			'sm:mb-0',
			'sm:mr-2',
			'inline-block',
			'form-select',
			'w-full',
			'sm:w-24',
			'py-2',
			'px-3',
			'border',
			'border-gray-300',
			'bg-white',
			'rounded-md',
			'shadow-sm',
			'focus:outline-none',
			'focus:shadow-outline-blue',
			'focus:border-blue-300',
			'transition',
			'duration-150',
			'ease-in-out',
			'sm:text-sm',
			'sm:leading-5',
			'row-start-2',
		),
	),
) );

if ( ! $ps_day_of_birth->has_errors() ) {
	$ps_day_of_birth->render();
} else {
	$ps_day_of_birth->log_errors( 'payouts-service-register' );
}

$ps_month_of_birth = new Controls\Select_Control( array(
	'id'     => 'payouts-service-month-of-birth',
	'args'   => array(
		'options'  => array( '' => 'Month' ) + $months,
		'selected' => $month_of_birth,
	),
	'alpine' => array(
		'x-spread' => '',
	),
	'atts'   => array(
		'name'     => 'month_of_birth',
		'required' => 'required',
		'class'    => array(
			'mt-1',
			'mb-1',
			'sm:mb-0',
			'sm:mr-2',
			'inline-block',
			'form-select',
			'w-full',
			'sm:w-32',
			'py-2',
			'px-3',
			'border',
			'border-gray-300',
			'bg-white',
			'rounded-md',
			'shadow-sm',
			'focus:outline-none',
			'focus:shadow-outline-blue',
			'focus:border-blue-300',
			'transition',
			'duration-150',
			'ease-in-out',
			'sm:text-sm',
			'sm:leading-5',
		),
	),
) );

if ( ! $ps_month_of_birth->has_errors() ) {
	$ps_month_of_birth->render();
} else {
	$ps_month_of_birth->log_errors( 'payouts-service-register' );
}

$ps_year_of_birth = new Controls\Select_Control( array(
	'id'     => 'payouts-service-year-of-birth',
	'args'   => array(
		'options'  => array( '' => 'Year' ) + array_combine( $years, $years ),
		'selected' => $year_of_birth,
	),
	'alpine' => array(
		'x-spread' => '',
	),
	'atts'   => array(
		'name'     => 'year_of_birth',
		'required' => 'required',
		'class'    => array(
			'mt-1',
			'inline-block',
			'form-select',
			'w-full',
			'sm:w-24',
			'py-2b',
			'px-3',
			'border',
			'border-gray-300',
			'bg-white',
			'rounded-md',
			'shadow-sm',
			'focus:outline-none',
			'focus:shadow-outline-blue',
			'focus:border-blue-300',
			'transition',
			'duration-150',
			'ease-in-out',
			'sm:text-sm',
			'sm:leading-5',
			'row-start-2',
		),
	),
) );

if ( ! $ps_year_of_birth->has_errors() ) {
	$ps_year_of_birth->render();
} else {
	$ps_year_of_birth->log_errors( 'payouts-service-register' );
}

html()->div_end();

$terms_of_use = affiliate_wp()->settings->get( 'terms_of_use', '' );

if ( ! empty( $terms_of_use ) ) {

	html()->div_start( array(
		'class' => array(
			'col-span-6',
			'sm:col-span-6',
			'relative',
			'flex',
			'items-start',
			'affwp-payout-tos-wrap',
		),
	) );

	$ps_tos_checkbox = new Controls\Checkbox_Control( array(
		'id'     => 'payouts-service-tos',
		'atts'   => array(
			'name'     => 'tos',
			'required' => 'required',
			'class'    => array(
				'form-checkbox',
				'h-4',
				'w-4',
				'text-indigo-600',
				'transition',
				'duration-150',
				'ease-in-out',
			),
		),
		'alpine' => array(
			'x-spread' => '',
		),
		'args'   => array(
			'label'            => affiliate_wp()->settings->get( 'terms_of_use_label', __( 'Agree to our Terms of Use and Privacy Policy', 'affiliatewp-affiliate-portal' ) ),
			'label_class'      => array(
				'flex',
				'items-center',
				'text-sm',
				'font-medium',
				'leading-5',
				'text-gray-700',
			),
			'label_href'       => esc_url( get_permalink( $terms_of_use ) ),
			'label_href_class' => array(
				'underline',
				'text-indigo-500',
			),
		),
	) );

	if ( ! $ps_tos_checkbox->has_errors() ) {
		$ps_tos_checkbox->render();
	} else {
		$ps_tos_checkbox->log_errors( 'payouts-service-register' );
	}

	html()->div_end();

}

html()->div_end();

html()->div_end();

html()->div_start( array(
	'class' => array(
		'px-4',
		'py-3',
		'bg-gray-50',
		'sm:px-6',

	),
) );

$ps_affwp_action = new Controls\Hidden_Control( array(
	'id'     => 'payouts-service-affwp-action',
	'atts'   => array(
		'name'  => 'affwp_action',
		'value' => 'payouts_service_register',
	),
	'alpine' => array(
		'x-spread'        => '',
		':class'          => '{}',
		':aria-invalid'   => '',
	),
) );

if ( ! $ps_affwp_action->has_errors() ) {
	$ps_affwp_action->render();
} else {
	$ps_affwp_action->log_errors( 'payouts-service-register' );
}

$ps_affiliate_id_input = new Controls\Hidden_Control( array(
	'id'     => 'payouts-service-affiliate-id',
	'atts'   => array(
		'name'  => 'affiliate_id',
		'value' => absint( $affiliate_id ),
	),
	'alpine' => array(
		'x-spread'        => '',
		':class'          => '{}',
		':aria-invalid'   => '',
	),
) );

if ( ! $ps_affiliate_id_input->has_errors() ) {
	$ps_affiliate_id_input->render();
} else {
	$ps_affiliate_id_input->log_errors( 'payouts-service-register' );
}

$ps_current_page_url_input = new Controls\Hidden_Control( array(
	'id'     => 'payouts-service-current-page-url',
	'atts'   => array(
		'name'  => 'current_page_url',
		'value' => esc_url( $current_page_url ),
	),
	'alpine' => array(
		'x-spread'        => '',
		':class'          => '{}',
		':aria-invalid'   => '',
	),
) );

if ( ! $ps_current_page_url_input->has_errors() ) {
	$ps_current_page_url_input->render();
} else {
	$ps_current_page_url_input->log_errors( 'payouts-service-register' );
}

$ps_submit_button = new Controls\Submit_Button_Control( array(
	'id'   => 'payouts-service-submit',
	'atts' => array(
		'value' => __( 'Register for Payouts Service', 'affiliatewp-affiliate-portal' ),
	),
) );

if ( ! $ps_submit_button->has_errors() ) {
	$ps_submit_button->render();
} else {
	$ps_submit_button->log_errors( 'payouts-service-register' );
}

html()->div_end();

html()->div_end();

html()->form_end();

html()->div_end();

html()->div_end();