jQuery(document).ready(function($) {
    if (cev_ajax.enableEmailVerification) {
        var otpVerified = false;

        function displayError(message) {
            var $form = $(cev_ajax.cev_Validation_error_position_id);
            $form.prepend('<div class="woocommerce-error">' + message + '</div>');
        }

        function clearErrors() {
            $('.woocommerce-error').remove();
        }

        // Set the value of email_verification hidden field to false
        $('input[name="email_verification"]').val('false');
        
        // Add a class to the register button if email_verification is false
        $('button[name="register"]').addClass('email_verification_popup');

        // Handle the register button click event
        $(document).on('click', '.email_verification_popup', function(e) {
            if (!otpVerified && $(this).hasClass('email_verification_popup')) {
                e.preventDefault();
                clearErrors();
                var $button = $(this);
                $('.cev_loading_overlay').css('display', 'block');
                var $form = $(this).closest('form');
                var email = $form.find('#reg_email').val();
                var password = $form.find('#reg_password').val();
                if (!email) {
                    displayError(cev_ajax.cev_email_validation);
                    $('.cev_loading_overlay').css('display', 'none');
                    return;
                }
                if (cev_ajax.password_setup_link_enabled == "no") {
                    if (!password) {
                        displayError(cev_ajax.cev_password_validation);
                        $('.cev_loading_overlay').css('display', 'none');
                        return;
                    }
                }
                // AJAX request to check if the email is already registered
                $.ajax({
                    url: cev_ajax.ajax_url,
                    type: 'POST',
                    data: $('.woocommerce-form-register').serialize() + '&action=check_email_exists&nonce=' + cev_ajax.nonce,
                    success: function(response) {
                        if (response.data.exists) {
                            displayError(cev_ajax.cev_email_exists_validation);
                            $('.cev_loading_overlay').css('display', 'none');
                        } else if (response.data.not_valid) {
                            displayError(cev_ajax.cev_valid_email_validation);
                            $('.cev_loading_overlay').css('display', 'none');
                        } else if (response.data.already_verify) {
                            $form.find('button[name="register"]').removeClass('email_verification_popup');
                            $form.find('input[name="email_verification"]').val('true');
                            $form.find('button[name="register"]').trigger('click');
                            $('.cev_loading_overlay').css('display', 'none');
                        } else if (response.data.email) {
                            console.log(response.data.email);
                            $('#otp-popup').show();
                            $('.cev_loading_overlay').css('display', 'none');
                        } else if (response.data.validation == false ) {
                            displayError(response.data.message);
                            $('.cev_loading_overlay').css('display', 'none');
                        } else {
                            // console.log('No email found in response.data');
                            $('.cev_loading_overlay').css('display', 'none');
                        }
                    }
                });
            }
        });

        // OTP input handling
        $('.otp-input').on('input', function() {
            var $this = $(this);
            var index = $this.index();

            if ($this.val().length === 1) {
                if (index < $('.otp-input').length - 1) {
                    $('.otp-input').eq(index + 1).focus();
                } else {
                    $('#verify-otp-button').trigger('click');
                }
            }
        });

        $('.otp-input').on('keydown', function(e) {
            var $this = $(this);
            var index = $this.index();

            if (e.keyCode === 8 && $this.val().length === 0) {
                if (index > 0) {
                    $('.otp-input').eq(index - 1).focus();
                }
            }
        });

        $('.otp-input').on('keypress', function(e) {
            var charCode = (e.which) ? e.which : e.keyCode;
            if (charCode < 48 || charCode > 57) {
                e.preventDefault();
            }
        });

        $('#verify-otp-button').on('click', function() {
            function getOtpValue() {
                var otp = '';
                $('.otp-input').each(function() {
                    otp += $(this).val();
                });
                return otp;
            }

            var $form = $('.email_verification_popup').closest('form');
            var email = $form.find('#reg_email').val();
            var otp = getOtpValue();

            // Verify OTP using AJAX
            $.ajax({
                url: cev_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'verify_otp',
                    otp: otp,
                    email: email,
                    nonce: cev_ajax.nonce 
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.verified) {
                            $('#otp-popup').hide();

                            $('<p class="success-message">Your email is verified successfully.</p>').insertBefore($form);

                            $form.find('button[name="register"]').removeClass('email_verification_popup');
                            $form.find('input[name="email_verification"]').val('true');
                            $form.find('button[name="register"]').trigger('click');
                        } else {
                            displayError(response.data.message);
                        }
                    } else {
                        $('.error_mesg').text(response.data.message).css('display', 'block');
                    }
                },
                error: function(xhr, status, error) {
                    displayError('Error: ' + error);
                }
            });
        });

        $('.send_again_link').on('click', function() {
            var $form = $('.woocommerce-form-register__submit').closest('form');
            var email = $form.find('#reg_email').val();
            $.ajax({
                url: cev_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resend_otp',
                    email: email,
                    nonce: cev_ajax.nonce 
                },
                success: function(response) {
                    if (response.data.email) {
                        $('.resend_sucsess').show();
                    } else {
                        console.log('No email found in response.data');
                    }
                },
                error: function(xhr, status, error) {
                    displayError('Error: ' + error);
                }
            });
        });
    }
    $(document).on('click', '.back_btn', function(e) {
        $('#otp-popup').hide();
    });

});
