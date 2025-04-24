jQuery(document).ready( function($) {

	// Datepicker.
	if( $('.affwp-datepicker').length ) {
		$('.affwp-datepicker').datepicker({dateFormat: 'mm/dd/yy'});
	}

	// Business account type input on the payout service registration form.
	var accountTypeInput       = $( '#affwp-payout-service-account-type' ),
	    businessNameDiv        = $( '.affwp-payout-service-business-name-wrap' ),
	    businessOwnerDiv       = $( '.affwp-payout-service-business-owner-wrap' );

	$( accountTypeInput ).change( function() {

		if ( $( this ).val() === 'company' ) {
			businessNameDiv.show();
			businessOwnerDiv.show();
			$( '#affwp-payout-service-business-name' ).prop( 'required', true );
			$( ".affwp-payout-service-country-wrap label" ).text( affwp_vars.business_account_country_label );

		} else {
			businessNameDiv.hide();
			businessOwnerDiv.hide();
			$( '#affwp-payout-service-business-name' ).prop( 'required', false );
			$( '#affwp-payout-service-business-owner' ).prop( 'checked', false );
			$( ".affwp-payout-service-country-wrap label" ).text( affwp_vars.personal_account_country_label );
		}

	}).change();
});
