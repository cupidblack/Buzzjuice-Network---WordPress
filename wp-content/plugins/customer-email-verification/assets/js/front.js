jQuery(document).ready(function($) {
	// OTP input handling
	jQuery('.otp-input-email').on('input', function() {
	   var $this = jQuery(this);
	   var index = $this.index();
   
	   if ($this.val().length === 1) {
		   // Move focus to the next input
		   if (index < jQuery('.otp-input-email').length - 1) {
			   jQuery('.otp-input-email').eq(index + 1).focus();
		   } else {
			   // If the last input is filled, trigger the verify function
			   jQuery('#pin_verification_button').trigger('click');
		   }
	   }
   });
   
   jQuery('.otp-input-email').on('keydown', function(e) {
	   var $this = $(this);
	   var index = $this.index();
   
	   if (e.keyCode === 8 && $this.val().length === 0) {
		   // Move focus to the previous input
		   if (index > 0) {
			   jQuery('.otp-input-email').eq(index - 1).focus();
		   }
	   }
   });
   
   jQuery('.otp-input-email').on('keypress', function(e) {
	   var charCode = (e.which) ? e.which : e.keyCode;
	   if (charCode < 48 || charCode > 57) {
		   e.preventDefault();
	   }
   });
   });
   jQuery(document).on("submit", ".cev_pin_verification_form", function(){
    var form = jQuery(this);
    var error = false;
    var otpValue = '';

    // Collect and concatenate OTP input values
    form.find('.otp-input-email').each(function() {
        otpValue += jQuery(this).val();
    });

    // Check if OTP value is empty
    if (otpValue.length === 0 || otpValue.length < form.find('.otp-input-email').length) {
        jQuery('.required-filed').html('<span class="cev-error-display">4-digits code*</span>');
        showerror(form.find('.otp-input-email').first());
        error = true;
    } else {
        hideerror(form.find('.otp-input-email').first());
    }

    if (error) {
        return false;
    }

    // Serialize form data
    var formData = form.serialize();

    // Add OTP value to form data
    var combinedData = formData + '&otp_value=' + encodeURIComponent(otpValue);

    jQuery(".cev_pin_verification_form").block({
        message: null,
        overlayCSS: {
            background: "#fff",
            opacity: .6
        }
    });

    jQuery.ajax({
        url: cev_ajax_object.ajax_url,
        data: combinedData, // Send combined data
        type: 'POST',
        dataType: "json",
        success: function(response) {
            if (response.success === 'true') {
                window.location.href = response.url;
            } else {
                jQuery('.cev-pin-verification__failure').show();
            }
            jQuery(".cev_pin_verification_form").unblock();
        },
        error: function(jqXHR, exception) {
            console.log(jqXHR.status);
        }
    });

    return false;
});


jQuery(document).ready(function($) {
	// OTP input handling
	jQuery('.otp-input-login').on('input', function() {
	   var $this = jQuery(this);
	   var index = $this.index();
   
	   if ($this.val().length === 1) {
		   // Move focus to the next input
		   if (index < jQuery('.otp-input-login').length - 1) {
			   jQuery('.otp-input-login').eq(index + 1).focus();
		   } else {
			   // If the last input is filled, trigger the verify function
			   jQuery('#loginSubmitPinButton').trigger('click');
		   }
	   }
   });
   
   jQuery('.otp-input-login').on('keydown', function(e) {
	   var $this = $(this);
	   var index = $this.index();
   
	   if (e.keyCode === 8 && $this.val().length === 0) {
		   // Move focus to the previous input
		   if (index > 0) {
			   jQuery('.otp-input-login').eq(index - 1).focus();
		   }
	   }
   });
   
   jQuery('.otp-input-login').on('keypress', function(e) {
	   var charCode = (e.which) ? e.which : e.keyCode;
	   if (charCode < 48 || charCode > 57) {
		   e.preventDefault();
	   }
   });
   });
   jQuery(document).on("click", "#loginSubmitPinButton", function() {
    var form = jQuery(this);
    var error;
    var cev_pin = '';
    
    // Collect the OTP inputs
    jQuery('.otp-input-login').each(function() {
        cev_pin += jQuery(this).val();
    });

    if (cev_pin.length < 4) {
        jQuery('.required-filed').html('<span class="cev-error-display">4-digits code*</span>');        
        showerror(jQuery('.otp-input-login').first());
        error = true;
    } else {
        hideerror(jQuery('.otp-input-login').first());
    }

    if (error === true) {
        return false;
    }
    jQuery(".cev_login_authentication_form").block({
        message: null,
        overlayCSS: {
            background: "#fff",
            opacity: .6
        }    
    });
    
    jQuery.ajax({
        url: cev_ajax_object.ajax_url,        
        data: {
            action: 'cev_login_auth_with_otp',
            cev_login_otp: cev_pin,
			cev_login_auth_with_otp: cev_ajax_object.cev_login_auth_with_otp_nonce // Nonce added here
        },
        type: 'POST',
        dataType: "json",
        success: function(response) {
            if (response.success === 'true') {
                window.location.href = response.url;
            } else {
                jQuery('.cev-pin-verification__failure').show();
            }
            jQuery(".cev_login_authentication_form").unblock();                
        },
        error: function(jqXHR, exception) {
            console.log(jqXHR.status);                        
        }
    });
    return false;
});

function showerror(element){
	element.css("border-color","red");
}
function hideerror(element){
	element.css("border-color","");
}
function getCodeBoxElement(index) {
  return document.getElementById('cev_pin' + index);
}
function onKeyUpEvent(index, event) {
  const eventCode = event.which || event.keyCode;
  if (getCodeBoxElement(index).value.length === 1) {
	 if (index !== 4) {
		getCodeBoxElement(index+ 1).focus();
	 } else {
		getCodeBoxElement(index).blur();
		// Submit code		
	 }
  }
  if (eventCode === 8 && index !== 1) {
	 getCodeBoxElement(index - 1).focus();
  }
}
function onFocusEvent(index) {
  for (item = 1; item < index; item++) {
	 const currentElement = getCodeBoxElement(item);
	 if (!currentElement.value) {
		  currentElement.focus();
		  break;
	 }
  }
}

/* cev pro */
jQuery(document).on('click', '.cev-send-verification-code', function(e) {
	"use strict";	
	
	var error;	
	var cev_pin_email = jQuery("#cev_pin_email");
	
	if( cev_pin_email.val() === '' ){
		showerror( cev_pin_email );
		error = true;
	} else{
		hideerror(cev_pin_email);
	}
	
	if( !validate_email(cev_pin_email.val()) ){
		showerror( cev_pin_email );
		error = true;
	} else{
		hideerror(cev_pin_email);
	}
	
	if(error == true){
		return false;
	}		
	
	jQuery(".cev-authorization").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	var ajax_data = {
		action: 'checkout_page_send_verification_code',
		email: cev_pin_email.val(),	
		wp_nonce : jQuery(this).attr('wp_nonce')
	};
	
	jQuery.ajax({           
		url : cev_ajax_object.ajax_url,
		type : 'post',
		dataType: "json",
		data : ajax_data,
		success : function( response ) {
			if(response.success == 'true'){				
				jQuery('.cev-show-reg-content .cev-authorization__description span').html(cev_pin_email.val());
				jQuery('.cev-pin-verification').show();
				jQuery('.cev-authorization__heading').hide();
				jQuery('.cev-show-reg-content').show();
				jQuery('.cev-authorization__footer').hide();
				jQuery('.cev-rge-content').show();
				jQuery('.cev-hide-success-message').hide();										
			}
			if(response.success == 'false'){
				if( response.cev_resend_limit_reached == 'true'){
					jQuery('.resend_verification_code_guest_user').css("pointer-events", "none");
					jQuery('.cev-send-verification-code').css({"pointer-events":"none",'cursor': 'not-allowed','background-color': '#bdbdbd'});
					jQuery('.cev_limit_message').show();
					
				}
			}
			if(response.success == 'alredy_verify'){
				location.reload();	
			}
			jQuery(".cev-authorization").unblock();	
		}
	}); 
});

function validate_email(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}
jQuery(document).ready(function($) {
 // OTP input handling
 jQuery('.otp-input-checkout').on('input', function() {
	var $this = jQuery(this);
	var index = $this.index();

	if ($this.val().length === 1) {
		// Move focus to the next input
		if (index < jQuery('.otp-input-checkout').length - 1) {
			jQuery('.otp-input-checkout').eq(index + 1).focus();
		} else {
			// If the last input is filled, trigger the verify function
			jQuery('#cev_submit_pin_button').trigger('click');
		}
	}
});

jQuery('.otp-input-checkout').on('keydown', function(e) {
	var $this = $(this);
	var index = $this.index();

	if (e.keyCode === 8 && $this.val().length === 0) {
		// Move focus to the previous input
		if (index > 0) {
			jQuery('.otp-input-checkout').eq(index - 1).focus();
		}
	}
});

jQuery('.otp-input-checkout').on('keypress', function(e) {
	var charCode = (e.which) ? e.which : e.keyCode;
	if (charCode < 48 || charCode > 57) {
		e.preventDefault();
	}
});
});
jQuery(document).on('click', '#cev_submit_pin_button', function(e) {
	"use strict";		
	var error;	
	var cev_pin_box_code = jQuery("#cev_pin_box_code");
	var cev_pin_email = jQuery("#cev_pin_email");
	var otp = '';
    jQuery('.otp-input-checkout').each(function() {
        otp += jQuery(this).val();
    });
	// if( cev_pin_box_code.val() === '' ){
	// 	showerror( cev_pin_box_code );
	// 	error = true;
	// } else{
	// 	hideerror(cev_pin_box_code);
	// }
	
	if(error == true){
		return false;
	}
	
	jQuery(".cev_pin_verification_form_pro").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	var ajax_data = {
		action: 'checkout_page_verify_code',
		email: cev_pin_email.val(),
		pin: otp,
		security: cev_ajax_object.checkout_verify_code,	
	};
	
	jQuery.ajax({           
		url : cev_ajax_object.ajax_url,
		type : 'post',
		dataType: "json",
		data : ajax_data,
		success : function( response ) {
			if(response.success == 'true'){
				location.reload();	
			} else{
				jQuery('.cev-pin-verification__failure').show();	
			}
			jQuery(".cev_pin_verification_form_pro").unblock();				
		},
		error: function(jqXHR, exception) {
			console.log(jqXHR.status);							
		}
	}); 
	return false;	
});

jQuery(document).on('click', '.verify_account_email_my_account', function(e) {
	"use strict";	
	
	var error;	
	var my_account_email_verification = jQuery("#my_account_email_verification");	
	
	if( my_account_email_verification.val() === '' ){
		showerror( my_account_email_verification );
		error = true;
		jQuery('.cev-pin-verification__failure_code').hide();		 
	} else{
		hideerror(my_account_email_verification);
		
		jQuery(".woocommerce-MyAccount-content").block({
			message: null,
			overlayCSS: {
				background: "#fff",
				opacity: .6
			}	
		});
		
		var ajax_data = {                                                                                                                
			action: 'verify_email_on_edit_account',		
			pin: my_account_email_verification.val(),
			wp_nonce : jQuery(this).attr('wp_nonce')
		};
		
		jQuery.ajax({           
			url : cev_ajax_object.ajax_url,
			type : 'post',
			dataType: "json",
			data : ajax_data,
			success : function( response ) {
				if(response.success == 'true'){
					location.reload();
				} else{
					jQuery('.cev-pin-verification__failure_code').show().css("color","red");
				}
				jQuery(".woocommerce-MyAccount-content").unblock();				
			},
			error: function(jqXHR, exception) {
				console.log(jqXHR.status);							
			}
		}); 
	}
	
	
	return false;	
});

jQuery(document).on('click', '.resend_verification_code_my_account', function(e) {
	"use strict";	
	
	var error;
	var my_account_email_verification = jQuery("#my_account_email_verification");	
	
	
	jQuery(".woocommerce-MyAccount-content").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	var ajax_data = {
		action: 'resend_verification_code_on_edit_account',	
				
	};
		
	jQuery.ajax({           
		url : cev_ajax_object.ajax_url,
		type : 'post',
		dataType: "json",
		data : ajax_data,
		success : function( response ) {
			if(response.success == 'true'){
				if( response.cev_resend_limit_reached == 'true'){
					jQuery('.a_cev_resend').css({"pointer-events":"none",'cursor': 'not-allowed','opacity': '0.5'});
				}
			} else{
				jQuery('.show_cev_resend_yes').show();
			}
			jQuery(".woocommerce-MyAccount-content").unblock();			
		},
		
	}); 
	return false;	
});
jQuery(document).on("click", ".cev_already_verify", function(){	
	"use strict";
	jQuery('.cev-pin-verification').show();
	jQuery('.cev-authorization__heading').hide();
	jQuery('.cev-show-reg-content').show();
	jQuery('.cev-authorization__footer').hide();
	jQuery('.cev-rge-content').show();
	jQuery('.cev-hide-success-message').hide();	
});

function showerror(element){
	element.css("border-color","red");
	
}
function hideerror(element){
	element.css("border-color","");
}


jQuery(document).on('click', '.resend_verification_code_guest_user', function(e) {
	"use strict";	
	
	var cev_pin_email = jQuery("#cev_pin_email");	
	
	jQuery(".resend_verification_code_guest_user").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	var ajax_data = {
		action: 'resend_verification_code_guest_user',
		email: cev_pin_email.val(),
		wp_nonce : jQuery(this).data('nonce'),		
	};
	
	
	jQuery.ajax({           
		url : cev_ajax_object.ajax_url,
		type : 'post',
		dataType: "json",
		data : ajax_data,
		success : function( response ) {
			if(response.success === 'true'){
				if( response.cev_resend_limit_reached ===  'true'){
					jQuery('.resend_verification_code_guest_user').css("pointer-events", "none");
				}
			} else{
				jQuery('.show_cev_resend_yes').show();
			}
			jQuery(".resend_verification_code_guest_user").unblock();				
		},
		
	}); 
	return false;	
});
// Check for the presence of the hidden field after the page loads
document.addEventListener('DOMContentLoaded', function() {
	var validationErrorField = document.querySelector('input[name="validation_error"]');
	if (validationErrorField) {
		// Submit the form programmatically to reload the page
		setTimeout(function() {
			location.reload();
		}, 1000); 
	}
});

if(cev_ajax_object.cev_verification_checkout_dropdown_option == 1) {
jQuery(function($) {
    // Select the email input field by its ID.
    var emailInput = $('#billing_email');
   
    // Function to perform the AJAX call.
    function performAjaxCall(email) {
        jQuery.ajax({
            url: cev_ajax_object.ajax_url, // Use the AJAX URL passed from wp_localize_script.
            method: 'POST',
            dataType: "json",
            data: { action: 'custom_email_verification', email: email, wp_nonce: cev_ajax_object.wc_cev_email_guest_user },
            success: function(response) {
              //console.log(response);
              if(response.success == 'true'){	
                   
                    localStorage.setItem('performActions', 'true');
                    localStorage.setItem('cev_pin_email', email);
            
                    // Reload the page
                    location.reload();			
                }
              
                if(response.success === 'false'){
                    if( response.cev_resend_limit_reached == 'true'){
                        jQuery('.resend_verification_code_guest_user').css("pointer-events", "none");
                        jQuery('.cev-send-verification-code').css({"pointer-events":"none",'cursor': 'not-allowed','background-color': '#bdbdbd'});
                        jQuery('.cev_limit_message').show();
                        
                    }
                }
                if(response.success == 'alredy_verify'){
                    location.reload();	
                }
				if(response.success == 'login_user'){
					console.log(response);
                }
            },
            error: function(xhr, status, error) {
                // Handle any AJAX errors here.
                console.error(error);
            }
        });
    }

    // Listen for changes to the email input field.
    emailInput.on('change', function() {
        var emailValue = emailInput.val();
        // Call the performAjaxCall function when the email input changes.
        performAjaxCall(emailValue);
    });
});

jQuery(document).ready(function() {
    // Check if there are actions to perform after page reload
    var performActions = localStorage.getItem('performActions');
    
    if (performActions === 'true') {
        // Retrieve stored values
        var cev_pin_email = localStorage.getItem('cev_pin_email');
        
        // Perform the desired actions here
        jQuery('.cev-show-reg-content .cev-authorization__description span').html(cev_pin_email);
        jQuery('.cev-pin-verification').show();
        jQuery('.cev-authorization__heading').hide();
        jQuery('.cev-show-reg-content').show();
        jQuery('.cev-authorization__footer').hide();
        jQuery('.cev-rge-content').show();
        jQuery('.cev-hide-success-message').hide();
        
        // Clear the localStorage to prevent these actions from running again
        localStorage.removeItem('performActions');
        localStorage.removeItem('cev_pin_email');
    }
});
} 
