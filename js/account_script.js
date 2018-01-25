/* EMAIL CHECKER */
function isEmail(email) {
    var regex = /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/;
    return regex.test(email);
}

//Code input for email update
$(function () {

    var codeInput = $("#code");
    var regex = /\W/;
    var submitButton = $("#updateEmailCodeButton");

    var delayEmailCode = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();


    codeInput.on('keyup input', function () {
        codeInput.removeClass('valid');
        codeInput.removeClass('invalid');
        submitButton.addClass('disabled');
        submitButton.prop("disabled", true);

        delayEmailCode(function () {
            if (codeInput.val().length === 4 && !regex.test(codeInput.val())) {
                submitButton.removeClass('disabled');
                submitButton.prop("disabled", false);
            }
            else {
                codeInput.addClass('invalid');
            }
        }, 1500);


    });
});

/* AJAX EMAIL UNIQUENESS */
function verifyEmailUniqueness() {

    var newEmail = $("#new-email");
    var labelNewEmail = $("#newEmailLabel");
    var confirmEmail = $("#confirm-email");
    var newEmailVal = newEmail.val();


    $.ajax('php_ajax/check_email_uniqueness.php', {
        success: function (result) {
            var response = JSON.parse(result);

            if (response['taken'] === true) {
                newEmail.addClass("invalid");

                labelNewEmail.attr("data-error", "Email is already taken");
            }
            else {
                newEmail.addClass('valid');
            }

            if (newEmail.hasClass('valid') && confirmEmail.hasClass('valid'))
                toggleEmailButton(true);
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

/* EMAIL UPDATE BUTTON ACTIVATOR */
function toggleEmailButton(enabled) {

    var updateEmailButton = $("#updateEmailButton");

    //Activate button
    if (enabled) {
        updateEmailButton.removeClass("disabled");
        updateEmailButton.prop("disabled", false);
    }
    else {
        updateEmailButton.addClass("disabled");
        updateEmailButton.prop("disabled", true);
    }
}

/* PASSWORD CHANGE BUTTON ACTIVATOR */
function togglePasswordButton(enable) {

    var updatePasswordButton = $("#update_password_button");

    if (enable) {
        updatePasswordButton.removeClass('disabled');
        updatePasswordButton.prop("disabled", false);
    }
    else {
        updatePasswordButton.addClass('disabled');
        updatePasswordButton.prop("disabled", true);
    }
}

//Listeners for email and password
$(function () {

    /* EMAIL */
    var newEmail = $("#new-email");
    var confirmEmail = $("#confirm-email");
    var labelNewEmail = $("#newEmailLabel");
    var labelConfirmEmail = $("#confirmEmailLabel");

    var delayNewEmail = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    newEmail.on('keyup input', function () {
        newEmail.removeClass('valid');
        newEmail.removeClass('invalid');
        confirmEmail.removeClass("valid");
        confirmEmail.removeClass("invalid");

        toggleEmailButton(false);

        delayNewEmail(function () {
            var newEmailVal = newEmail.val();

            if (isEmail(newEmailVal)) {

                verifyEmailUniqueness();

            }
            else {
                newEmail.addClass("invalid");

                labelNewEmail.attr("data-error", "Invalid email");
            }

            if (newEmail.hasClass('valid') && confirmEmail.hasClass('valid'))
                toggleEmailButton(true);

        }, 2000);


    });

    var delayConfirm = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    confirmEmail.on('keyup input', function () {
        confirmEmail.removeClass("valid");
        confirmEmail.removeClass("invalid");

        toggleEmailButton(false);

        delayConfirm(function () {
            var conEmailVal = confirmEmail.val();
            var newEmailVal = newEmail.val();

            if (conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {

                confirmEmail.addClass("valid");
            }
            else {
                confirmEmail.addClass("invalid");

                labelConfirmEmail.attr("data-error", "Emails do not match");
            }

            if (newEmail.hasClass('valid') && confirmEmail.hasClass('valid'))
                toggleEmailButton(true);
        }, 2000);
    });

    /* PASSWORD */
    var newPassword = $("#new_password");
    var confirmNewPassword = $("#confirm_new_password");
    //Labels
    var newPasswordLabel = $("#new_password-label");

    var delayNewPassword = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    newPassword.on('keyup input', function () {
        newPassword.removeClass("valid");
        newPassword.removeClass("invalid");
        confirmNewPassword.removeClass("invalid");
        confirmNewPassword.removeClass("valid");

        togglePasswordButton(false);

        delayNewPassword(function () {
            var newPasswordVal = newPassword.val();

            if (newPasswordVal.length < 8) {
                newPassword.addClass("invalid");
                newPasswordLabel.attr("data-error", "Password must be at least 8 characters long");
            }
            else {
                newPassword.addClass("valid");
            }

            if (newPassword.hasClass("valid") && confirmNewPassword.hasClass("valid"))
                togglePasswordButton(true);

        }, 2000);

    });

    var delayConfirmNewPassword = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    confirmNewPassword.on('keyup input', function () {
        confirmNewPassword.removeClass("invalid");
        confirmNewPassword.removeClass("valid");

        togglePasswordButton(false);

        delayConfirmNewPassword(function () {
            var confirmNewPasswordVal = confirmNewPassword.val();
            var newPasswordVal = newPassword.val();


            if (confirmNewPasswordVal !== newPasswordVal) {
                confirmNewPassword.addClass("invalid");
                confirmNewPassword.attr("data-error", "Passwords do not match");
            }
            else {
                confirmNewPassword.addClass("valid");
            }

            if (newPassword.hasClass("valid") && confirmNewPassword.hasClass("valid"))
                togglePasswordButton(true);

        }, 2000);

    });

});

/* TRANSFER BUTTON TOGGLE */
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

    var delayTransferUser = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    transferUserInput.on('input keyup', function () {
        transferUserInput.removeClass('invalid');
        transferUserInput.removeClass('valid');

        toggleTransferButton(false);

        delayTransfer(function () {
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

    var delayTransferAmount = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    transferAmountInput.on('input keyup', function () {
        transferAmountInput.removeClass('invalid');
        transferAmountInput.removeClass('valid');

        toggleTransferButton(false);

        delayTransferAmount(function () {
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

    var delayWAddress = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    withdrawalAddressInput.on('input keyup', function () {
        withdrawalAddressInput.removeClass('invalid');
        withdrawalAddressInput.removeClass('valid');

        toggleWithdrawButton(false);

        delayWA(function () {
            var bitcoinAddress = withdrawalAddressInput.val();

            if (checkAddress(normalizedBitcoinAddress)) {
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

    var delayWAmount = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    withdrawalAmountInput.on('input keyup', function () {
        withdrawalAmountInput.removeClass('invalid');
        withdrawalAmountInput.removeClass('valid');

        toggleWithdrawButton(false);

        delayWAmount(function () {

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

function toggleTicketButton(enabled) {

    var ticketButton = $("#ticket_button");

    if (enabled) {
        ticketButton.removeClass('disabled');
        ticketButton.prop("disabled", false);
    }
    else {
        ticketButton.addClass('disabled');
        ticketButton.prop("disabled", true);
    }
}
/* Listener for ticket */
$(function () {

    var delayTicket = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    var ticket_content_input = $("#support_content");
    var ticket_content_label = $("#support_content_label");

    var ticket_subject_input = $("#support_subject");
    var ticket_subject_label = $("#support_subject_label");

    ticket_content_input.on('keyup input', function () {

        toggleTicketButton(false);

        ticket_content_input.removeClass('invalid');
        ticket_content_input.removeClass('valid');

        delayTicket(function () {

            if (ticket_content_input.val().length < 50) {

                ticket_content_label.attr('data-error', "Message must have at least 50 characters");
                ticket_content_input.addClass('invalid');

            } else if (ticket_content_input.val().length > 2000) {
                ticket_content_label.attr('data-error', "Message is too long");
                ticket_content_input.addClass('invalid');

            }
            else {
                ticket_content_input.addClass('valid');
            }

            if (ticket_content_input.hasClass('valid') && ticket_subject_input.hasClass('valid')) {
                toggleTicketButton(true);
            }

        }, 2000);
    });

    ticket_subject_input.on('keyup input', function () {

        toggleTicketButton(false);

        ticket_subject_input.removeClass('invalid');
        ticket_subject_input.removeClass('valid');

        delayTicket(function () {

            if (ticket_subject_input.val().length > 80) {
                ticket_subject_label.attr('data-error', "Subject is too long");
                ticket_subject_input.addClass('invalid');

            }
            else {
                ticket_subject_input.addClass('valid');
            }

            if (ticket_subject_input.hasClass('valid') && ticket_content_input.hasClass('valid')) {
                toggleTicketButton(true);
            }

        }, 2000);
    });


});