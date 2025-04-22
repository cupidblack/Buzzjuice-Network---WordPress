jQuery(document).on('click', '.resend_verification_code_inline_chekout_user', function(e) {
	"use strict";	
	
	jQuery(".woocommerce-checkout").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	var cev_pro_email_checkout = jQuery("#billing_email");	
	
	var ajax_data = {
		action: 'checkout_page_send_verification_code',
		email: cev_pro_email_checkout.val(),
		wp_nonce : jQuery(this).attr('wp_nonce')		
	};
	
	
	jQuery.ajax({           
		url : cev_ajax_object.ajax_url,
		type : 'post',
		dataType: "json",
		data : ajax_data,
		success : function( response ) {
					if( response.cev_resend_limit_reached === 'true'){
					jQuery('.a_cev_resend_checkout').css({"pointer-events" : "none", "opacity" : "0.5"});
			}
			jQuery(".woocommerce-checkout").unblock();				
		},
		
	}); 
	return false;	
});
	
jQuery(document).on("blur", "#billing_email", _.debounce(function () {
    
        jQuery(".woocommerce-checkout").block(
            {
                message: null,
                overlayCSS: {
                    background: "#fff",
                    opacity: .6
                }    
            }
        );
    
        var error;    
        var cev_pro_email_checkout = jQuery("#billing_email");
		var billing_email_field = jQuery("#billing_email_field");
    
        if(!validate_email(cev_pro_email_checkout.val()) ) {
            error = true;        
        } 
    
        if(error === true) {
            jQuery(".cev_pro_append").remove();
            jQuery(".woocommerce-checkout").unblock();    
        } else {
        
            var ajax_data = {
                action: 'send_email_on_chekout_page',
                email: cev_pro_email_checkout.val(),
				security: cev_ajax_object.checkout_send_verification_email,
            };
        
            jQuery.ajax(
                {           
                    url : cev_ajax_object.ajax_url,
                    type : 'post',
                    dataType: "json",
                    data : ajax_data,
                    success : function ( response ) {
						console.log('response',response);
						if (response.mail_sent == false && response.verify == false ) {
                            
							jQuery(".cev_pro_append").remove();
							
							if ( response.checkoutWC == true ) {
								billing_email_field.parent('.cfw-input-wrap-row').after( "<div class='cev_pro_append row cfw-input-wrap-row'><div class='col-lg-12'><div class='cfw-input-wrap cfw-text-input'><label class=''>"+cev_ajax_object.email_verification_code_label+"</label> <input type='text' class='input-text garlic-auto-save cev_pro_chekout_input' name='cev_billing_email' placeholder='"+cev_ajax_object.email_verification_code_label+"'></input><span class='cev_verification__failure_code_checkout' style='display:none; color:red'>"+cev_ajax_object.verification_code_error_msg+"</span> <div class='cev_pro_chekout_button'> <button type='button' class='woocommerce-Button button verify_account_email_chekout_page' name='verify_account_email' value='verify'>"+cev_ajax_object.verify_button_text+"</button><a href='#' class='a_cev_resend_checkout resend_verification_code_inline_chekout_user' style='padding-left: 10px;' name='resend_verification_code' wp_nonce='"+cev_ajax_object.wc_cev_email_guest_user+"'>"+cev_ajax_object.resend_verification_label+"</a></div></div></div></div>");								
								jQuery('.cev_inline_code_sent').remove();
								jQuery("<small class='cev_inline_code_sent' style='display:none; color:green'>"+cev_ajax_object.verification_send_msg+"</small>").insertAfter('#billing_email');
							} else {
								cev_pro_email_checkout.closest('p').after("<div class='cev_pro_append'><label class='jquery_color_css'>"+cev_ajax_object.email_verification_code_label+"</label> <input type='text' class='input-text cev_pro_chekout_input' name='cev_billing_email'></input> <small class='cev_verification__failure_code_checkout' style='display:none; color:red'>"+cev_ajax_object.verification_code_error_msg+"</small><div class='cev_pro_chekout_button'> <button type='button' class='woocommerce-Button button verify_account_email_chekout_page' name='verify_account_email' value='verify'>"+cev_ajax_object.verify_button_text+"</button> <a href='#' class='a_cev_resend_checkout resend_verification_code_inline_chekout_user' name='resend_verification_code' wp_nonce='"+cev_ajax_object.wc_cev_email_guest_user+"'>"+cev_ajax_object.resend_verification_label+"</a></div></div>");								
								jQuery('.cev_inline_code_sent').remove();
								jQuery("<small class='cev_inline_code_sent' style='display:none; color:green'>"+cev_ajax_object.verification_send_msg+"</small>").insertAfter('#billing_email');
								jQuery("#billing_email").css("margin","0");
							}						                            
                        }
						if(response.success == 'alredy_verify'){
							
								jQuery("#billing_email").css("margin","0");
						}
						
                        jQuery(".woocommerce-checkout").unblock();
                        jQuery(".cev_inline_code_sent").show();
						jQuery(".cev-hide-desc").remove();  
                    }
                }
            );    
        }             
    },500)
);

jQuery(document).on('click', '.verify_account_email_chekout_page', function(e) {
	"use strict";	
	
	var error;
	var checkout_page_billing_email_verify = jQuery(".cev_pro_chekout_input");	
	
	jQuery(".woocommerce-checkout").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	if( checkout_page_billing_email_verify.val() === '' ){
		showerror( checkout_page_billing_email_verify );
		error = true;
		jQuery('.cev_verification__failure_code_checkout').hide();
		jQuery(".woocommerce-checkout").unblock();
			 
	} else{
		hideerror(checkout_page_billing_email_verify);
		
		var ajax_data = {                                                                                                                
			action: 'checkout_page_verify_code',		
			pin: checkout_page_billing_email_verify.val(),				
			security: cev_ajax_object.checkout_verify_code,			
		};
		
		jQuery.ajax({           
			url : cev_ajax_object.ajax_url,
			type : 'post',
			dataType: "json",
			data : ajax_data,
			success : function( response ) { 	
				if(response.success === 'true'){
					jQuery(".cev_pro_append").remove();
					jQuery("#billing_email").css({"border-color": "#ddd", "color": "#333"});
					jQuery(".woocommerce-checkout").unblock();					
					jQuery("<small class= 'cev-hide-desc' style='color: green;'>"+cev_ajax_object.email_verified_msg+"</small>").insertAfter('#billing_email');
					jQuery("#billing_email").css("margin","0");
					jQuery(".cev_inline_code_sent").remove();					
				} else {
					jQuery(".woocommerce-checkout").unblock();
					jQuery('.cev_verification__failure_code_checkout').show().css("color","red");
					jQuery(".cev_pro_chekout_input").css("margin","0");
					jQuery(".cev-hide-desc").remove();
					
				}
			},
			
		}); 	
	}
	return false;	
});