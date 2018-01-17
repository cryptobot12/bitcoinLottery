/* EMAIL CHECKER */
function isEmail(email) {
    var regex = /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/;
    return regex.test(email);
}

/* PASSWORD CHANGE BUTTON ACTIVATOR */
function activatePasswordButton() {

    var newPassword = $("#new_password");
    var confirmNewPassword = $("#confirm_new_password");
    var updatePasswordButton = $("#update_password_button");

    if (newPassword.hasClass("valid") && confirmNewPassword.hasClass("valid"))
        updatePasswordButton.removeClass("disabled");
    else
        updatePasswordButton.addClass("disabled");
}

/* EMAIL UPDATE BUTTON ACTIVATOR */
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

//Code input for email update
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

/* AJAX EMAIL UNIQUENESS */
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

//Button to check email uniqueness
$(function () {

    var checkAvailabilityButton = $("#checkAvailability");

    checkAvailabilityButton.on('click', function () {
        verifyEmailUniqueness();
    });
});


//Listeners for email and password
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

    newPassword.on('keyup input', function () {

        newPassword.removeClass("valid");
        newPassword.removeClass("invalid");

        var delay = (function () {
            var timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();

        delay(function () {
            var newPasswordVal = newPassword.val();

            if (newPasswordVal.length < 8) {
                newPassword.addClass("invalid");
                newPasswordLabel.attr("data-error", "Password must be at least 8 characters long");
            }
            else {
                newPassword.addClass("valid");
            }

            activatePasswordButton();
        }, 2000);

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

function toggleTransferButton(enable) {

    var transferButton = $("#transfer_button");

    if (enable) {
        transferButton.removeClass('disabled');
        transferButton.prop("disabled", false);
    }
    else {
        transferButton.addClass('disabled');
        transferButton.prop("disabled", true);
    }

}

/* Listeners for transfer */
$(function () {
    /*Inputs*/
    var transferUserInput = $("#transfer_user");
    var transferAmountInput = $("#transfer_amount");

    /*Labels*/
    var transferUserLabel = $("#transfer_user_label");
    var transferAmountLabel = $("#transfer_amount_label");

    /*Balance*/
    var balance = parseInt($("#balanceNumber").html());

    transferUserInput.on('input keyup', function () {
        transferUserInput.removeClass('invalid');
        transferUserInput.removeClass('valid');

        toggleTransferButton(false);

        var delay = (function () {
            var timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();

        delay(function () {
            var username = transferUserInput.val();

            if (username.length > 0) {
                $.ajax('php_ajax/check_username_uniqueness.php', {
                    success: function (result) {
                        var response = JSON.parse(result);

                        if (response['same'] === true) {
                            transferUserInput.addClass('invalid');
                            transferUserLabel.attr('data-error', 'User cannot be yourself');
                        }
                        else if (response['exists'] === false) {
                            transferUserInput.addClass('invalid');
                            transferUserLabel.attr('data-error', 'User does not exist');
                        }
                        else {
                            transferUserInput.removeClass('invalid');
                            transferUserInput.addClass('valid');
                        }

                        if (transferUserInput.hasClass('valid') && transferAmountInput.hasClass('valid'))
                            toggleTransferButton(true);

                    },
                    data: {
                        username: username
                    },
                    error: function () {
                        console.log("Could not verify if user exists");
                    },
                    method: 'POST'
                })
            }

        }, 1800);
    });

    transferAmountInput.on('input keyup', function () {
        transferAmountInput.removeClass('invalid');
        transferAmountInput.removeClass('valid');

        toggleTransferButton(false);

        var delay = (function () {
            var timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();

        delay(function () {
            var amount = parseFloat(transferAmountInput.val());

            //Not empty inputs
            if (transferAmountInput.val().length > 0) {
                if (!Number.isInteger(amount)) {
                    transferAmountLabel.attr('data-error', "Amount must be an integer number");
                    transferAmountInput.addClass('invalid');
                }
                else if (amount <= 100) {
                    transferAmountLabel.attr('data-error', "Amount must be greater than 100");
                    transferAmountInput.addClass('invalid');
                }
                else if ((amount + 100) > balance) {
                    transferAmountLabel.attr('data-error', "Not enough bits");
                    transferAmountInput.addClass('invalid');
                }
                else
                    transferAmountInput.addClass('valid');
            }

            if (transferUserInput.hasClass('valid') && transferAmountInput.hasClass('valid'))
                toggleTransferButton(true);

        }, 1800);


    });

});

function normalize(s) {
    var x = String(s) || '';
    return x.replace(/^[\s\xa0]+|[\s\xa0]+$/g, '');
}

function checkBitcoinAddress(s) {

    if (s.length < 26 || s.length > 35) {
        return false;
    }

    var re = /^[A-Z0-9]+$/i;
    return re.test(s);
}


/* Withdraw button toggle */
function toggleWithdrawButton(isEnabled) {

    var withdrawButton = $("#withdraw_button");

    if (isEnabled) {
        withdrawButton.removeClass('disabled');
        withdrawButton.prop("disabled", false);
    }
    else {
        withdrawButton.addClass('disabled');
        withdrawButton.prop("disabled", true);
    }
}

/* Listeners for withdrawal */
$(function () {
    /* Inputs */
    var withdrawalAddressInput = $("#withdraw_address");
    var withdrawalAmountInput = $("#withdraw_amount");

    /* Labels */
    var withdrawalAddressLabel = $("#withdraw_address_label");
    var withdrawalAmountLabel = $("#withdraw_amount_label");

    /*Balance*/
    var balance = parseInt($("#balanceNumber").html());

    withdrawalAddressInput.on('input keyup', function () {
        withdrawalAddressInput.removeClass('invalid');
        withdrawalAddressInput.removeClass('valid');

        toggleWithdrawButton(false);

        var delay = (function () {
            var timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();

        delay(function () {
            var bitcoinAddress = withdrawalAddressInput.val();

            var normalizedBitcoinAddress = normalize(bitcoinAddress);

            if (checkBitcoinAddress(normalizedBitcoinAddress)) {
                withdrawalAddressInput.addClass('valid');
            }
            else {
                withdrawalAddressInput.addClass('invalid');
                withdrawalAddressLabel.attr('data-error', 'Invalid address')
            }

            if (withdrawalAddressInput.hasClass('valid') && withdrawalAmountInput.hasClass('valid'))
                toggleWithdrawButton(true);

        }, 2000)

    });

    withdrawalAmountInput.on('input keyup', function () {
        withdrawalAmountInput.removeClass('invalid');
        withdrawalAmountInput.removeClass('valid');

        toggleWithdrawButton(false);

        var delay = (function () {
            var timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();

        delay(function () {

            var amount = parseFloat(withdrawalAmountInput.val());

            if (!Number.isInteger(amount)) {
                withdrawalAmountLabel.attr('data-error', "Amount must be an integer number");
                withdrawalAmountInput.addClass('invalid');
            }
            else if (amount <= 100) {
                withdrawalAmountLabel.attr('data-error', "Amount must be greater than 100");
                withdrawalAmountInput.addClass('invalid');
            }
            else if ((amount + 100) > balance) {
                withdrawalAmountLabel.attr('data-error', "Not enough bits");
                withdrawalAmountInput.addClass('invalid');
            }
            else
                withdrawalAmountInput.addClass('valid');

            if (withdrawalAddressInput.hasClass('valid') && withdrawalAmountInput.hasClass('valid'))
                toggleWithdrawButton(true);

        }, 2000);
    })

});