/**
 * Affiliate Portal menu links admin functionality.
 *
 * Implements Javascript functionality for managing the custom menu links.
 *
 * @since  1.0.8
 */

jQuery(document).ready(function ($) {
    
	// Expand/Collapse tabs
	$( document.body ).on( 'click', '.portal-hide-show-menu-links', function(e) {

		e.preventDefault();

		var tabs = $( '.portal-menu-links-repeatable-row-standard-fields' );
		var el = $(this);

		// Change text.
		el.text() == el.data("text-swap") ? el.text( el.data("text-original") ) : el.text( el.data("text-swap") );

		// Show/hide tabs.
		if ( el.text() === el.data("text-swap") ) {
			tabs.show();
		} else if (  el.text() === el.data("text-original") ) {
			tabs.hide();
		}

	});

	/**
	 * Affiliate Portal Menu Links Configuration
	 */
	var APML_Configuration = {

		/**
		 * Initializes Affiliate Portal Menu Links javascript functions.
		 *
		 * @since  1.0.8
		 *
		 * @return void.
		 */
		init : function() {
			this.add();
			this.edit();
			this.move();
			this.remove();
		},

		/**
		 * Clones a menu link row on the DOM.
		 *
		 * @since  1.0.8
		 *
		 * @param {jQuery} row Row to clone.
		 * @return {jQuery} Cloned row.
		 */
		clone_repeatable : function(row) {

			// Retrieve the highest current key
			var key = highest = 0;

			row.parent().find( '.portal_menu_links_repeatable_row' ).each(function() {
				var current = $(this).data( 'key' );
				if( parseInt( current ) > highest ) {
						highest = current;
				}
			});

			key = highest += 1;

			clone = row.clone();

			// Manually update any select box values.
			clone.find( 'select' ).each(function() {
				$( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
			});

			// Update the data-key.
			clone.attr( 'data-key', key );

			// Update any input or select menu's name and ID attribute.
			clone.find( 'input, select' ).val( '' ).each(function() {
				var name = $( this ).attr( 'name' );
				var id   = $( this ).attr( 'id' );

				if ( name ) {
						name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
						$( this ).attr( 'name', name );
				}

				$( this ).attr( 'data-key', key );

				if ( typeof id != 'undefined' ) {
						id = id.replace( /(\d+)/, parseInt( key ) );
						$( this ).attr( 'id', id );
				}
			});

			// Update the label "for" attribute.
			clone.find( 'label' ).val( '' ).each(function() {
				var labelFor = $( this ).attr( 'for' );

				if ( typeof labelFor != 'undefined' ) {
						labelFor = labelFor.replace( /(\d+)/, parseInt( key ) );
						$( this ).attr( 'for', labelFor );
				}
			});

			// Change the tab's title when the last one is cloned.
			clone.find( '.portal-menu-links-title' ).each(function() {
				$( this ).html( affwp_portal_admin_vars.new_link_heading );
			});

			// Remove the "(Default AffiliateWP tab)" text if a custom tab is inserted after a default tab.
			clone.find( '.portal-menu-links-link-default' ).remove();

			// Increase the tab number key.
			clone.find( '.portal-menu-links-link-number-key' ).each(function() {
				$( this ).text( parseInt( key ) );
			});

			// Show the the tab title and content for custom tabs.
			clone.find( '.portal-menu-links-link-title, .portal-menu-links-link-content').show();

			return clone;
		},

		/**
		 * Adds a new menu link to the DOM.
		 *
		 * @since  1.0.8
		 *
		 * @return void.
		 */
		add : function() {
			$( document.body ).on( 'click', '.portal-menu-links-add-repeatable', function(e) {

				e.preventDefault();

				var button = $( this ),
						row    = button.parent().prev( '.portal_menu_links_repeatable_row' ),
						clone  = APML_Configuration.clone_repeatable(row);

				clone.insertAfter( row );
				clone.show();
				clone.find( '.portal-menu-links-repeatable-row-standard-fields' ).show();
				clone.find('input, select').filter(':visible').eq(0).focus();
				button.removeClass( 'no-menu-links' );

			});
		},

		/**
		 * Edits a menu link by toggling the menu link divs.
		 *
		 * @since  1.0.8
		 *
		 * @return void.
		 */
		edit : function() {
			// Open settings for each tab.
			$( document.body ).on( 'click', '.portal-menu-links-repeatable-row-title', function(e) {
				e.preventDefault();

				$(this).next( '.portal-menu-links-repeatable-row-standard-fields' ).toggle();
				$(this).find( '.portal-menu-links-edit .dashicons' ).toggleClass( 'dashicons-arrow-down dashicons-arrow-up' );
			});
		},

		/**
		 * Enables sortable functionality on menu links.
		 *
		 * @since  1.0.8
		 *
		 * @return void.
		 */
		move : function() {

			$(".portal_menu_links_repeatable_table .portal-menu-links-repeatables-wrap").sortable({
				handle: '.portal-menu-links-draghandle-anchor',
				items: $( '.portal_menu_links_repeatable_row' ).not( '.no-sort' ),
				opacity: 0.6,
				cursor: 'move',
				axis: 'y',

				/**
				 * Implements sortable update callback.
				 *
				 * @since  1.0.8
				 *
				 * @return void.
				 */
				update: function() {

					var key  = 1;

					$(this).find( '.portal_menu_links_repeatable_row' ).each(function() {

						// Update the data-key attribute.
						$( this ).attr( 'data-key', key );

						// Update the tab number key. Example (Tab 5)
						$(this).find( '.portal-menu-links-link-number-key' ).text( parseInt( key ) );

						// Update any input or select menu's name and ID attribute.
						$(this).find( 'input, select' ).each(function() {
							var name = $( this ).attr( 'name' );
							var id   = $( this ).attr( 'id' );

							if ( name ) {
								name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
								$( this ).attr( 'name', name );
							}

							$( this ).attr( 'data-key', key );

							if ( typeof id != 'undefined' ) {
								id = id.replace( /(\d+)/, parseInt( key ) );
								$( this ).attr( 'id', id );
							}
						});

						// Update the label "for" attribute.
						$(this).find( 'label' ).val( '' ).each(function() {
							var labelFor = $( this ).attr( 'for' );

							if ( typeof labelFor != 'undefined' ) {
								labelFor = labelFor.replace( /(\d+)/, parseInt( key ) );
								$( this ).attr( 'for', labelFor );
							}
						});

						key++;

					});
				}

			});
		},

		/**
		 * Removes a menu link from the DOM.
		 *
		 * @since  1.0.8
		 *
		 * @return void.
		 */
		remove : function() {

			$( document.body ).on( 'click', '.portal_menu_links_remove_repeatable', function(e) {
				e.preventDefault();

				// Confirm that the user wants to delete the tab.
				var hasConfirmed = confirm( affwp_portal_admin_vars.ays );

				if ( ! hasConfirmed ) {
					return;
				}

				var row   = $(this).parents( '.portal_menu_links_repeatable_row' ),
						count = row.parent().find( '.portal_menu_links_repeatable_row' ).length,
						focusElement,
						focusable,
						firstFocusable;

				// Set focus on next element if removing the first row. Otherwise set focus on previous element.
				if ( $(this).is( '.ui-sortable .portal_menu_links_repeatable_row:first-child .portal_menu_links_remove_repeatable' ) ) {
					focusElement  = row.next( '.portal_menu_links_repeatable_row' );
				} else {
					focusElement  = row.prev( '.portal_menu_links_repeatable_row' );
				}

				focusable  = focusElement.find( 'select, input, textarea, button' ).filter( ':visible' );
				firstFocusable = focusable.eq(0);

				$( 'input, select', row ).val( '' );
				row.remove();
				firstFocusable.focus();

				// Re-index after deleting.

				var key  = 1;
				var rows = $( '.portal-menu-links-repeatables-wrap' ).find( '.portal_menu_links_repeatable_row' );

				rows.each(function() {

					// Update the data-key attribute.
					$( this ).attr( 'data-key', key );

					// Update the tab number key. Example (Tab 5)
					$(this).find( '.portal-menu-links-link-number-key' ).text( parseInt( key ) );

					// Update any input or select menu's name and ID attribute.
					$(this).find( 'input, select' ).each(function() {
						var name = $( this ).attr( 'name' );
						var id   = $( this ).attr( 'id' );

						if ( name ) {
								name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
								$( this ).attr( 'name', name );
						}

						$( this ).attr( 'data-key', key );

						if ( typeof id != 'undefined' ) {
								id = id.replace( /(\d+)/, parseInt( key ) );
								$( this ).attr( 'id', id );
						}
					});

					// Update the label "for" attribute.
					$(this).find( 'label' ).val( '' ).each(function() {
						var labelFor = $( this ).attr( 'for' );

						if ( typeof labelFor != 'undefined' ) {
								labelFor = labelFor.replace( /(\d+)/, parseInt( key ) );
								$( this ).attr( 'for', labelFor );
						}
					});

					key++;

				});

				// If only 1 row, then align the "Add New Link" button with the other settings.
				if ( rows.length === 1 ) {
					$( '.button-secondary.portal-menu-links-add-repeatable' ).addClass( 'no-menu-links' );
				}

			});
		},

	};

	APML_Configuration.init();

});
