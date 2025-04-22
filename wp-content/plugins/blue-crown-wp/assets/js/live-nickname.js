jQuery(document).ready(function($) {
    var emailField = $('#signup_email');
    var nicknameField = $('#field_3');

    // Event listener for input on email field
    emailField.on('input', function() {
        var emailValue = $(this).val();
        var atSymbolIndex = emailValue.indexOf('@');

        // Only update nickname if '@' hasn't been typed yet
        if (atSymbolIndex === -1) {
            var emailAlias = emailValue.split('@')[0];
            nicknameField.val(emailAlias); // Set the nickname field value
        } else {
            // Stop updating once '@' is typed
            nicknameField.val(emailValue.substring(0, atSymbolIndex));
        }
    });
});
