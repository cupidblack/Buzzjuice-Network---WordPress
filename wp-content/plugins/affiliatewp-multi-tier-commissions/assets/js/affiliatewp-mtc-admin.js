/**
 * AffiliateWP Multi-Tier Commissions admin
 *
 * @since 1.0.0
 */

'use strict';

/* eslint-disable no-console, no-undef */
affiliatewp.attach(
	'mtc',
	/**
	 * MTC Component.
	 *
	 * @since 1.0.0
	 */
	{
		/**
		 * Populated dynamically via wp_add_inline_script().
		 */
		data: {},

		/**
		 * Adds select2 functionality to the Parent Affiliate field.
		 *
		 * @since 1.0.0
		 */
		initParentAffiliateDropdown() {
			const perPage = 10;

			jQuery( '#parent-affiliate-id' ).off().select2( {
				width: '350px',
				allowClear: false,
				ajax: {
					url: affiliatewp.mtc.data.ajaxUrl,
					dataType: 'json',
					delay: 250,
					data: function ( params ) {
						return {
							action: 'affiliatewp_mtc_parent_affiliate_select',
							nonce: affiliatewp.mtc.data.nonce,
							q: params.term,
							page: params.page || 1,
							per_page: perPage,
							affiliate_id: affiliatewp.mtc.data.affiliateID ?? 0
						};
					},
					processResults: function ( data, params ) {
						params.page = params.page || 1;
						return {
							results: data.items,
							pagination: {
								more: ( params.page * perPage ) < data.total
							}
						};
					},
					cache: true
				},
				placeholder: affiliatewp.mtc.data.i18n.select2placeholder,
				minimumInputLength: -1,
			} );

			// Open the select2 dropdown when clicking on the Parent Affiliate label.
			jQuery( '.form-row[data-field="parent-affiliate"] label' ).on( 'click', function() {
				jQuery( this ).closest( 'tr' ).find( 'select' ).select2( 'open' );
			} );
		},

		/**
		 * Toggle the remove button visibility.
		 *
		 * The remove button should be visible only if we have 3 or more tiers on the screen,
		 * since this is a multi tier system, we always need more than one tier configured.
		 *
		 * @since 1.0.0
		 */
		toggleRemoveButtonVisibility() {
			const $removeTier = jQuery( '.affwp-remove-tier' );

			// Determine if the remove tier button should be shown or hidden.
			if ( jQuery( '.affwp-tier-row' ).length >= 3 ) {

				$removeTier.css( 'display', 'block' );
				return;
			}

			$removeTier.css( 'display', 'none' );
		},

		initSelect2( $elements ) {
			$elements.each( function() {
				const $select = jQuery( this );
				$select.select2(
					affiliatewp.parseArgs(
						/*
						 * You can use an object with select2 arguments in your select tag to override
						 * the select2 defaults or add new setting.
						 */
						$select.data( 'select2-settings' ) || {},
						{
							width: '170px',
							minimumResultsForSearch: -1,
						}
					)
				);
			} );
		},

		/**
		 * Initiate the tiers repeater in the Settings screen.
		 *
		 * @since 1.0.0
		 */
		initTiersRepeater() {
			// The table body jQuery object.
			const $root = jQuery( '#affwp-tier-rows' );

			// Add new button jQuery object.
			const $addNewButton = jQuery( '#affwp-new-tier' );

			// Tracks the total number of rows added so far.
			let total = $root.find( '.affwp-tier-row' ).length;

			const getRowTemplate = function( fields ) {

				// Copy the row HTML template.
				let row = affiliatewp.mtc.data.tiersRowHtml;

				// Replace all the {{var}} found in the HTML template.
				Object.entries( fields ).forEach( ( [key, value] ) => {
					row = row.replace( new RegExp(`{{\\b${key}\\b}}`, 'g' ), value )
				} );

				return row;
			};

			$addNewButton.on( 'click', function( e ) {
				e.preventDefault();

				const $row = jQuery( getRowTemplate(
					{
						index: total,
						rate: '',
						flat_type_options: '',
					}
				) );

				$root.append( $row );

				// Update total of rows.
				total = $root.find( '.affwp-tier-row' ).length;

				affiliatewp.mtc.toggleRemoveButtonVisibility();

				affiliatewp.mtc.initSelect2( $row.find( 'select' ) );

				// Focus on the input field in the new row.
				$root.find('.affwp-tier-row').find('input').focus();

				// Cap the number of tiers.
				if ( total >= affiliatewp.mtc.data.maxReferralTiers ) {
					$addNewButton.attr( 'disabled', true );
					$addNewButton.attr( 'aria-disabled', true );
				}
			} );

			// Remove row.
			jQuery( document ).on( 'click', '.affwp-remove-tier', function( e ) {
				e.preventDefault();

				// Ensure the button is always active.
				$addNewButton.attr( 'disabled', false );
				$addNewButton.attr( 'aria-disabled', false );

				// Remove the row.
				jQuery( this ).parent().remove();

				affiliatewp.mtc.toggleRemoveButtonVisibility();
			} );

			affiliatewp.mtc.toggleRemoveButtonVisibility();
		},
	}
);
