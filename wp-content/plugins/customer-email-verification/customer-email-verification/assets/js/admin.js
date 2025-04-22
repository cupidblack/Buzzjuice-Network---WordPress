/* zorem_snackbar jquery */
(function( $ ){
	$.fn.zorem_snackbar = function(msg) {
		if ( jQuery('.cev-snackbar-logs').length === 0 ){
			$("body").append("<section class=cev-snackbar-logs></section>");
		}
		var zorem_snackbar = $("<article></article>").addClass('cev-snackbar-log cev-snackbar-log-success cev-snackbar-log-show').text( msg );
		$(".cev-snackbar-logs").append(zorem_snackbar);
		setTimeout(function(){ zorem_snackbar.remove(); }, 3000);
		return this;
	}; 
})( jQuery );

/* zorem_snackbar_warning jquery */
(function( $ ){
	$.fn.zorem_snackbar_warning = function(msg) {
		if ( jQuery('.cev-snackbar-logs').length === 0 ){
			$("body").append("<section class=cev-snackbar-logs></section>");
		}
		var zorem_snackbar_warning = $("<article></article>").addClass( 'cev-snackbar-log cev-snackbar-log-error cev-snackbar-log-show' ).html( msg );
		$(".cev-snackbar-logs").append(zorem_snackbar_warning);
		setTimeout(function(){ zorem_snackbar_warning.remove(); }, 3000);
		return this;
	}; 
})( jQuery );

jQuery(document).on("change", "#cev_enable_email_verification,#cev_enable_email_verification_checkout,#cev_enable_login_authentication", function(){
	
	var accordion = jQuery(this).closest('.accordion');			
	var form = jQuery("#cev_settings_form");	
	var cev_enable_email_verification_checkout = jQuery("#cev_enable_email_verification_checkout").val();	
	
	jQuery.ajax({
		url: ajaxurl,
		data: form.serialize(),
		type: 'POST',
		dataType:"json",	
		success: function() {	
			jQuery("#cev_settings_form").zorem_snackbar( 'Your Settings have been successfully saved.' );
		},
		error: function(response) {
			console.log(response);			
		}
	});

	
});

jQuery(document).on("click", ".cev_settings_save", function(){
	
	var form = jQuery("#cev_settings_form");	
	var accordion = jQuery(this).closest('.accordion');
	accordion.find(".spinner").addClass("active");	
	
	jQuery.ajax({
		url: ajaxurl,
		data: form.serialize(),
		type: 'POST',
		dataType:"json",	
		success: function() {	
			form.find(".spinner").removeClass("active");
			jQuery("#cev_settings_form").zorem_snackbar( 'Your Settings have been successfully saved.' );
			jQuery( '.accordion' ).removeClass( 'active' );
			jQuery( '.accordion' ).find( '.cev_settings_save' ).hide();
			jQuery( '.accordion' ).find( 'span.dashicons' ).addClass( 'dashicons-arrow-right-alt2' );
			jQuery( '.panel' ).slideUp( 'slow' );
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".accordion-label, .accordion-open", function(){
	var accordion = jQuery(this).closest('.accordion');	
	toggle_accordion(accordion);	
});

function toggle_accordion( accordion ){
	if ( accordion.hasClass( 'active' ) ) {				
		accordion.removeClass( 'active' );
		accordion.siblings( '.panel' ).removeClass( 'active' );
		accordion.siblings( '.panel' ).slideUp( 'slow' );
		jQuery( '.accordion' ).find('span.dashicons').addClass('dashicons-arrow-right-alt2');
		jQuery( '.accordion' ).find('.cev_settings_save').hide();		
	} else {		
		jQuery( '.accordion' ).removeClass( 'active' );
		jQuery('.panel').removeClass('active').slideUp('slow');
		jQuery(".accordion").find('.cev_settings_save').hide();
		jQuery(".accordion").find('span.dashicons').addClass('dashicons-arrow-right-alt2');	
		accordion.addClass( 'active' );
		accordion.siblings( '.panel' ).addClass( 'active' );
		accordion.find('span.dashicons').removeClass('dashicons-arrow-right-alt2');
		accordion.find('.cev_settings_save').show();				
		accordion.siblings( '.panel' ).slideDown( 'slow' );		
		/**/		
	}
}

(function( $ ){
	$.fn.isInViewport = function( element ) {
		var win = $(window);
		var viewport = {
			top : win.scrollTop()			
		};
		viewport.bottom = viewport.top + win.height();
		
		var bounds = this.offset();		
		bounds.bottom = bounds.top + this.outerHeight();

		if( bounds.top >= 0 && bounds.bottom <= window.innerHeight) {
			return true;
		} else {
			return false;	
		}		
	};
})( jQuery );

/*( function( $, data, wp, ajaxurl ) {
		
	var $cev_settings_form = $("#cev_settings_form");	
			
	var cev_settings_init = {
		
		init: function() {									
			$cev_settings_form.on( 'click', '.cev_settings_save', this.save_wc_cev_settings_form );			
		},

		save_wc_cev_settings_form: function( event ) {
			
			event.preventDefault();
			
			$cev_settings_form.find(".spinner").addClass("active");				
			var ajax_data = $cev_settings_form.serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
				$cev_settings_form.find(".spinner").removeClass("active");
				jQuery("#cev_settings_form").zorem_snackbar( 'Your Settings have been successfully saved.' );
				jQuery( '.accordion' ).removeClass( 'active' );
				jQuery( '.accordion' ).find( '.cev_settings_save' ).hide();
				jQuery( '.accordion' ).find( 'span.dashicons' ).addClass( 'dashicons-arrow-right-alt2' );
				jQuery( '.panel' ).slideUp( 'slow' );
			});
			return false;
		}, 		
	};
	
	$(window).load(function(e) {
        cev_settings_init.init();
    });	
	
})( jQuery, cev_pro_admin_js, wp, ajaxurl );*/


jQuery( document ).ready(function() {	
	jQuery(".woocommerce-help-tip").tipTip();	
	var cev_verification_checkout_dropdown_option = jQuery('#cev_verification_checkout_dropdown_option').val();
	if ( cev_verification_checkout_dropdown_option  == '2' ) {
		jQuery('#cev_enable_email_verification_cart_page').closest('li').hide();
	}
	console.log(cev_verification_checkout_dropdown_option);
});

jQuery( document ).on( "change", "#cev_verification_checkout_dropdown_option", function() {
	var cev_verification_checkout_dropdown_option = jQuery(this).val();
	if ( cev_verification_checkout_dropdown_option  == '2' ) {
		jQuery('#cev_enable_email_verification_cart_page').closest('li').hide();
	} else{
		jQuery('#cev_enable_email_verification_cart_page').closest('li').show();
	}
});




jQuery( document ).on( "click", "#activity-panel-tab-help", function() {
	jQuery(this).addClass( 'is-active' );
	jQuery( '.woocommerce-layout__activity-panel-wrapper' ).addClass( 'is-open is-switching' );
});

jQuery(document).click(function(event){
	var $trigger = jQuery(".woocommerce-layout__activity-panel");
    if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery('#activity-panel-tab-help').removeClass( 'is-active' );
		jQuery( '.woocommerce-layout__activity-panel-wrapper' ).removeClass( 'is-open is-switching' );
    }   
});

jQuery(document).on('click', '.cev-email-verify-button-tools', function(e) {
	"use strict";	

	jQuery('#cev_content_tools .cev-email-verify-tools-td .spinner').addClass("active");
	jQuery( '#cev_content_tools .cev-email-verify-tools-td .tools_save_msg').hide();
	
	var ajax_data = {
		action: 'bulk_email_verify_from_tools',			
	};
	
	jQuery.ajax({           
		url : ajaxurl,
		type : 'post',
		data : ajax_data,
		success : function( response ) {
			jQuery('#cev_content_tools .cev-email-verify-tools-td .spinner').removeClass("active");
			jQuery("#cev_content_tools .cev-email-verify-tools-td .tools_save_msg").zorem_snackbar( 'Successfully verified '+response+' emails.' );					
		}
	}); 
	return false;
});	

jQuery(document).on('click', '.cev-email-resend-bulk-button-tools', function(e) {
	"use strict";		
	jQuery('#cev_content_tools .cev-email-resend-tools-td .spinner').addClass("active");
	jQuery( '#cev_content_tools .cev-email-resend-tools-td .tools_save_msg').hide();
	var ajax_data = {
		action: 'bulk_email_resend_verify_from_tools',			
	};
	
	jQuery.ajax({           
		url : ajaxurl,
		type : 'post',
		data : ajax_data,
		success : function( response ) {
				jQuery('#cev_content_tools .cev-email-resend-tools-td .spinner').removeClass("active");
				jQuery("#cev_content_tools .cev-email-resend-tools-td .tools_save_msg").zorem_snackbar( 'Successfully sent a verification email to all unverified users.' );
		}
	}); 
	return false;
});	

jQuery(document).on('click', '#cev_enable_tools,#cev_change_texbox_value', function(e) {	
	"use strict";
	var toggle_val = jQuery('#cev_enable_tools').parent().find('#cev_enable_tools').is(":checked");
	var taxbox_val = jQuery('#cev_change_texbox_value').parent().find('#cev_change_texbox_value').val();
	var ajax_data = {
		action: 'toggle_email_delete_from_tools',	
		toggle: toggle_val,
		wp_nonce : jQuery(this).attr('wp_nonce'),
		textbox: taxbox_val,
	};
	
	jQuery.ajax({           
		url : ajaxurl,
		type : 'post',
		data : ajax_data,
		success : function( response ) {
							
		}
	}); 	
});

jQuery(document).on("click", ".delay_account_check", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('.delay_account_check').prop('checked', false);
		jQuery(this).prop('checked', true);		
	}
});

jQuery(document).on("change", "#cev_verification_checkout_dropdown_option", function(){	
	if(jQuery(this).val() == '2'){
		jQuery('.cev_cart_values_hide').hide();
	} else{
		jQuery('.cev_cart_values_hide').show();	
	}
});

/* licence activation */
jQuery(document).on("submit", "#wc_cev_pro_addons_form", function( ){	
	var form = jQuery(this);
	jQuery("#wc_cev_pro_addons_form").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	jQuery('#cev_pro_license_message').hide();
	var action = jQuery('#cev-pro-license-action').val();			
	jQuery.ajax({
		url: ajaxurl,
		data: form.serialize(),
		type: 'POST',
		success: function(data) {
			jQuery("#wc_cev_pro_addons_form").unblock();	
			jQuery('#cev_pro_license_message').show();			
			var btn_value = 'Activate';
			if(data.success == true){
				if(action == 'cev_pro_license_activate'){
					var btn_value = 'Deactivate';					
					jQuery('#cev_pro_license_message').html('<span style="color:green;padding: 25px;">Congratulation, your license successful activated</span>');
					jQuery('.activated').show();
					window.location.reload();
				} else {					
					jQuery('#license_key').val('');					
					jQuery('#cev_pro_license_message').html('<span style="color:green;padding: 25px;">Congratulation, your license successful deactivated</span>');
					jQuery('.activated').hide();
					window.location.reload();				
				}
			} else {
				jQuery('#ptw_pro_license_message').html('<span style="color:red;padding: 25px;">'+data.error+'</span>');
			}
			
			jQuery('#saveS').prop('disabled', false).val(btn_value);
		},
		error: function(jqXHR, exception) {
			console.log(jqXHR.status);			
		}
	});
	return false;
});


jQuery(document).on("change", "#cev_enable_email_verification_checkout", function(){ 
	if (jQuery("#cev_enable_email_verification_checkout").prop('checked') === false) {
		// Disable the checkbox
		jQuery("#cev_enable_email_verification_free_orders").prop('disabled', true).closest('label').addClass('disabled');
	} else {
		// Enable the checkbox
		jQuery("#cev_enable_email_verification_free_orders").prop('disabled', false).closest('label').removeClass('disabled');
	}
});
jQuery(document).on("change", "#cev_verification_type", function(){  
    if(jQuery(this).val() == 'link'){
        // Disable the dropdown and add disabled class to its parent label
        jQuery("#cev_verification_code_length").prop('disabled', true).closest('li').addClass('disabled');
        jQuery("#cev_verification_code_expiration").prop('disabled', true).closest('li').addClass('disabled');
    } else{
        jQuery("#cev_verification_code_expiration").prop('disabled', false).closest('li').removeClass('disabled');
        jQuery("#cev_verification_code_length").prop('disabled', false).closest('li').removeClass('disabled');
    }
});
jQuery(document).on("change", "#cev_enable_login_authentication", function(){  
	if (jQuery("#cev_enable_login_authentication").prop('checked') === false) {
        // Disable the dropdown and add disabled class to its parent label
		jQuery("#enable_email_otp_for_account").prop('disabled', true).closest('label').addClass('disabled');
		jQuery("#enable_email_auth_for_new_device").prop('disabled', true).closest('label').addClass('disabled');
		jQuery("#enable_email_auth_for_new_location").prop('disabled', true).closest('label').addClass('disabled');
		jQuery("#enable_email_auth_for_login_time").prop('disabled', true).closest('label').addClass('disabled');
		jQuery("p#login_auth_desc").addClass('disabled');
		jQuery("#cev_last_login_more_then_time").prop('disabled', true)
    } else{
		jQuery("#enable_email_otp_for_account").prop('disabled', false).closest('label').removeClass('disabled');
		jQuery("#enable_email_auth_for_new_device").prop('disabled', false).closest('label').removeClass('disabled');
		jQuery("#enable_email_auth_for_new_location").prop('disabled', false).closest('label').removeClass('disabled');
		jQuery("#enable_email_auth_for_login_time").prop('disabled', false).closest('label').removeClass('disabled');
		jQuery("#cev_last_login_more_then_time").prop('disabled', false)
		jQuery("p#login_auth_desc").removeClass('disabled');
    }
});


var table; // Define the DataTable variable
jQuery(document).ready(function() {
	var table = jQuery('#userLogTable').DataTable({
		searching: false,
		lengthChange: false,
		pageLength: 50, // Show only five entries per page
		columnDefs: [
			{ 
				orderable: false, 
				targets: [0, 1, 2, 3] // Disable sorting on these columns
			},
			{ 
				width: '20px', 
				targets: 0 // Set width for the first column
			},
			{ 
				className: 'text-right', 
				targets: -1 // Align the last column to the right
			},
			{
				targets: '_all', // Apply to all columns
				createdCell: function (td, cellData, rowData, row, col) {
					if (col === 3) {
						jQuery(td).css('text-align', 'right');
					}
					if (row === 0) { // Target only header cells
						if (col === 0) {
							jQuery(td).css('width', '20px');
						}  else {
							jQuery(td).css('width', '300px');
						}
					}
				}
			}
		],
		rowCallback: function(row, data, index) {
			jQuery(row).hover(
				function() {
					jQuery(this).addClass('hover-row');
				},
				function() {
					jQuery(this).removeClass('hover-row');
				}
			);
		}
	});
	
	
	jQuery(document).on("click", ".cev_tab_input", function(){
		"use strict";
		var tab = jQuery(this).data('tab');
		var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=customer-email-verification-for-woocommerce&tab="+tab;
		window.history.pushState({path:url},'',url);
		
	});

    document.getElementById('select_all').addEventListener('click', function() {
        var checkboxes = document.querySelectorAll('.row_checkbox');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });

	jQuery('.apply_bulk_action').on('click', function() {
        var action = jQuery('#bulk_action').val();
        if (action === 'delete') {
            var selectedIds = [];
            jQuery('.row_checkbox:checked').each(function() {
                selectedIds.push(jQuery(this).val());
            });

            if (selectedIds.length > 0) {
                if (confirm('Are you sure you want to delete the selected users?')) {
                    // AJAX call to delete selected users
                    jQuery.ajax({
                        url: cev_vars.ajax_url,
                        method: 'POST',
                        data: {
                            action: 'delete_users',
                            nonce: cev_vars.delete_user_nonce,
                            ids: selectedIds
                        },
                        success: function(response) {
                            response = JSON.parse(response);
                            if (response.success) {
                                // Remove deleted rows from the table
                                for (var id of selectedIds) {
                                    table.row(jQuery('input[value="' + id + '"]').closest('tr')).remove().draw();
                                }
                                jQuery.fn.zorem_snackbar('Users deleted successfully.');
                            } else {
                                jQuery.fn.zorem_snackbar_warning('Error deleting users.');
                            }
                        },
                        error: function() {
                            jQuery.fn.zorem_snackbar_warning('Error deleting users.');
                        }
                    });
                }
            } else {
                alert('No users selected for deletion');
            }
        }
    });

    jQuery(document).on('click', '.delete_button', function() {
        var button = jQuery(this);
        var userId = button.data('id');
        if (confirm('Are you sure you want to delete this user?')) {
            // AJAX call to delete the user
            jQuery.ajax({
                url: cev_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'delete_user',
                    nonce: cev_vars.delete_user_nonce,
                    id: userId
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        // Remove the row from the table
                        table.row(button.closest('tr')).remove().draw();
                        jQuery.fn.zorem_snackbar('User deleted successfully.');
                    } else {
                        jQuery.fn.zorem_snackbar_warning('Error deleting user.');
                    }
                },
                error: function() {
                    jQuery.fn.zorem_snackbar_warning('Error deleting user.');
                }
            });
        }
    });
	
});
