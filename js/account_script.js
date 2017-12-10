function isEmail(email) {
    var regex = /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/;
    return regex.test(email);
}

function activatePasswordButton() {

    var newPassword = $("#new_password");
    var confirmNewPassword = $("#confirm_new_password");
    var updatePasswordButton = $("#update_password_button");

    if (newPassword.hasClass("valid") && confirmNewPassword.hasClass("valid"))
        updatePasswordButton.removeClass("disabled");
    else
        updatePasswordButton.addClass("disabled");
}

function activateUpdateEmailButton() {

    var newEmail = $("#new-email");
    var confirmEmail = $("#confirm-email");
    var updateEmailButton = $("#updateEmailButton");

    //Activate button
    if (confirmEmail.hasClass("valid") && newEmail.hasClass("valid")) {
        updateEmailButton.removeClass("disabled");
    }
    else {
        updateEmailButton.addClass("disabled");
    }
}

function verifyEmailUniqueness() {

    var newEmail = $("#new-email");
    var labelNewEmail = $("#newEmailLabel");
    var confirmEmail = $("#confirm-email");
    var updateEmailButton = $("#updateEmailButton");
    var labelConfirmEmail = $("#confirmEmailLabel");

    var conEmailVal = confirmEmail.val();
    var newEmailVal = newEmail.val();

    if (isEmail(newEmail.val())) {

        $.ajax('php_ajax/check_email_uniqueness.php', {
            success: function (result) {
                console.log(result);
                var response = JSON.parse(result);

                if (response['taken'] === true) {
                    newEmail.removeClass("valid");
                    newEmail.addClass("invalid");

                    labelNewEmail.attr("data-error", "Email is already taken");
                }
                else {
                    newEmail.removeClass("invalid");
                    newEmail.addClass("valid");

                    if (isEmail(conEmailVal) || conEmailVal !== "") {
                        if (conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {
                            confirmEmail.removeClass("invalid");
                            confirmEmail.addClass("valid");
                        }
                        else {
                            updateEmailButton.addClass("disabled");
                            confirmEmail.removeClass("valid");
                            confirmEmail.addClass("invalid");

                            labelConfirmEmail.attr("data-error", "Emails do not match");
                        }
                    }

                    activateUpdateEmailButton();
                }
            },
            data: {
                email: newEmailVal
            },
            error: function () {
                console.log("error");
            },

            method: "POST"
        });
    }
    else {
        newEmail.removeClass("valid");
        newEmail.addClass("invalid");

        labelNewEmail.attr("data-error", "Invalid email");
    }


}

$(function () {

    var checkAvailabilityButton = $("#checkAvailability");

    checkAvailabilityButton.on('click', function () {
        verifyEmailUniqueness();
    });
});

$(function () {
    var newEmail = $("#new-email");
    var confirmEmail = $("#confirm-email");
    var labelNewEmail = $("#newEmailLabel");
    var labelConfirmEmail = $("#confirmEmailLabel");

    var updateEmailButton = $("#updateEmailButton");


    confirmEmail.on('keyup click input change', function () {
        var conEmailVal = confirmEmail.val();
        var newEmailVal = newEmail.val();

        if (isEmail(conEmailVal)) {
            if (conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {
                confirmEmail.removeClass("invalid");
                confirmEmail.addClass("valid");
            }
            else {
                updateEmailButton.addClass("disabled");
                confirmEmail.removeClass("valid");
                confirmEmail.addClass("invalid");

                labelConfirmEmail.attr("data-error", "Emails do not match");
            }
        }
        else {
            updateEmailButton.addClass("disabled");
            confirmEmail.removeClass("valid");
            confirmEmail.addClass("invalid");

            labelConfirmEmail.attr("data-error", "Invalid email");
        }

        activateUpdateEmailButton();
    });

    newEmail.on('keyup change input', function () {
        var conEmailVal = confirmEmail.val();
        var newEmailVal = newEmail.val();

        if (isEmail(newEmailVal)) {
            newEmail.removeClass("invalid");
            newEmail.removeClass("valid");
            if (conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {
                confirmEmail.removeClass("invalid");
                confirmEmail.addClass("valid");

            }
            else {
                updateEmailButton.addClass("disabled");
                confirmEmail.removeClass("valid");
                confirmEmail.addClass("invalid");

                labelConfirmEmail.attr("data-error", "Emails do not match");
            }
        }
        else {
            newEmail.removeClass("valid");
            newEmail.addClass("invalid");

            labelNewEmail.attr("data-error", "Invalid email");
        }

        activateUpdateEmailButton();
    });

    var newPassword = $("#new_password");
    var confirmNewPassword = $("#confirm_new_password");
    //Labels
    var newPasswordLabel = $("#new_password-label");

    newPassword.on('keyup blur input change', function () {
        var newPasswordVal = newPassword.val();

        confirmNewPassword.removeClass("valid");
        confirmNewPassword.removeClass("invalid");
        if (newPasswordVal.length < 8) {
            newPassword.removeClass("valid");
            newPassword.addClass("invalid");
            newPasswordLabel.attr("data-error", "Password must be at least 8 characters long");
        }
        else {
            newPassword.removeClass("invalid");
            newPassword.addClass("valid");
        }

        activatePasswordButton();
    });

    confirmNewPassword.on('click keyup blur input change', function () {
        var confirmNewPasswordVal = confirmNewPassword.val();
        var newPasswordVal = newPassword.val();

        if (confirmNewPasswordVal !== newPasswordVal) {
            confirmNewPassword.removeClass("valid");
            confirmNewPassword.addClass("invalid");
            confirmNewPassword.attr("data-error", "Passwords do not match");
        }
        else {
            confirmNewPassword.removeClass("invalid");
            confirmNewPassword.addClass("valid");
        }

        activatePasswordButton();
    });


});

$(function () {

    var codeInput = $("#code");
    var regex = /\W/;
    var submitButton = $("#updateEmailCodeButton");

    codeInput.on('keyup click blur input change', function () {

        if (codeInput.val().length === 4 && !regex.test(codeInput.val())) {
            submitButton.removeClass('disabled');
        }
        else {
            submitButton.addClass('disabled');
        }


    });
});