/* EMAIL CHECKER */
function isEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

$(document).ready(function () {
    M.AutoInit();

    $('input#support_subject, textarea#support_content').characterCounter();
});

/* AJAX EMAIL UNIQUENESS */
function verifyEmailUniqueness() {

    var newEmail = $("#new-email");
    var helperNewEmail = $("#new_email_helper");
    var confirmEmail = $("#confirm-email");
    var newEmailVal = newEmail.val();


    $.ajax('ajax/check-email-uniqueness', {
        success: function (result) {
            var response = JSON.parse(result);

            if (response['taken'] === true) {
                newEmail.addClass("invalid");

                helperNewEmail.attr("data-error", "Email is already taken");
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
    var helperNewEmail = $("#new_email_helper");
    var helperConfirmEmail = $("#confirm_email_helper");

    var delayNewEmail = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    //DOCUMENT READY////////////////////////////////////
    var newEmailVal = newEmail.val();
    var conEmailVal = confirmEmail.val();

    if (newEmailVal.length > 0) {
        if (isEmail(newEmailVal)) {

            verifyEmailUniqueness();

        }
        else {
            newEmail.addClass("invalid");

            helperNewEmail.attr("data-error", "Invalid email");
        }
    }

    if (conEmailVal.length > 0) {
        if (conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {

            confirmEmail.addClass("valid");
        }
        else {
            confirmEmail.addClass("invalid");

            helperConfirmEmail.attr("data-error", "Emails do not match");
        }
    }

    if (newEmail.hasClass('valid') && confirmEmail.hasClass('valid'))
        toggleEmailButton(true);

    /////////////////////////////////////////////////
    newEmail.on('keyup input', function () {
        newEmail.removeClass('valid');
        newEmail.removeClass('invalid');
        confirmEmail.removeClass("valid");
        confirmEmail.removeClass("invalid");

        toggleEmailButton(false);

        delayNewEmail(function () {
            var newEmailVal = newEmail.val();
            var conEmailVal = confirmEmail.val();

            if (isEmail(newEmailVal)) {

                verifyEmailUniqueness();

            }
            else {
                newEmail.addClass("invalid");

                helperNewEmail.attr("data-error", "Invalid email");
            }

            if (conEmailVal.length > 0) {
                if (conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {

                    confirmEmail.addClass("valid");
                }
                else {
                    confirmEmail.addClass("invalid");

                    helperConfirmEmail.attr("data-error", "Emails do not match");
                }
            }

            if (newEmail.hasClass('valid') && confirmEmail.hasClass('valid'))
                toggleEmailButton(true);

        }, 2000);


    });

    confirmEmail.on('keyup input', function () {
        confirmEmail.removeClass("valid");
        confirmEmail.removeClass("invalid");

        toggleEmailButton(false);


        var conEmailVal = confirmEmail.val();
        var newEmailVal = newEmail.val();

        if (conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {

            confirmEmail.addClass("valid");
        }
        else {
            confirmEmail.addClass("invalid");

            helperConfirmEmail.attr("data-error", "Emails do not match");
        }

        if (newEmail.hasClass('valid') && confirmEmail.hasClass('valid'))
            toggleEmailButton(true);

    });

    /* PASSWORD */
    var newPassword = $("#new_password");
    var confirmNewPassword = $("#confirm_new_password");

    /////DOCUMENT READY ///////////////////////////

    var newPasswordVal = newPassword.val();

    if (newPasswordVal.length > 0) {
        if (newPasswordVal.length < 8)
            newPassword.addClass("invalid");
        else
            newPassword.addClass("valid");
    }


    var confirmNewPasswordVal = confirmNewPassword.val();


    if (confirmNewPasswordVal.length > 0) {
        if (confirmNewPasswordVal !== newPasswordVal)
            confirmNewPassword.addClass("invalid");
        else
            confirmNewPassword.addClass("valid");

    }

    if (newPassword.hasClass("valid") && confirmNewPassword.hasClass("valid"))
        togglePasswordButton(true);


    /////////////////////////////////////////////

    newPassword.on('keyup input', function () {
        newPassword.removeClass("valid");
        newPassword.removeClass("invalid");
        confirmNewPassword.removeClass("invalid");
        confirmNewPassword.removeClass("valid");

        togglePasswordButton(false);

        var confirmNewPasswordVal = confirmNewPassword.val();
        var newPasswordVal = newPassword.val();

        if (newPasswordVal.length < 8)
            newPassword.addClass("invalid");
        else
            newPassword.addClass("valid");

        if (confirmNewPasswordVal.length > 0) {
            if (confirmNewPasswordVal !== newPasswordVal)
                confirmNewPassword.addClass("invalid");
            else
                confirmNewPassword.addClass("valid");
        }


        if (newPassword.hasClass("valid") && confirmNewPassword.hasClass("valid"))
            togglePasswordButton(true);


    });

    confirmNewPassword.on('keyup input', function () {
        confirmNewPassword.removeClass("invalid");
        confirmNewPassword.removeClass("valid");

        togglePasswordButton(false);


        var confirmNewPasswordVal = confirmNewPassword.val();
        var newPasswordVal = newPassword.val();


        if (confirmNewPasswordVal !== newPasswordVal)
            confirmNewPassword.addClass("invalid");
        else
            confirmNewPassword.addClass("valid");


        if (newPassword.hasClass("valid") && confirmNewPassword.hasClass("valid"))
            togglePasswordButton(true);


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
    var transferUserHelper = $("#transfer_user_helper");
    var transferAmountHelper = $("#transfer_amount_helper");

    //ONLOAD
    var amount = parseFloat(transferAmountInput.val());

    //Not empty inputs
    if (transferAmountInput.val().length > 0) {
        if (!Number.isInteger(amount)) {
            transferAmountHelper.attr('data-error', "Amount must be an integer number");
            transferAmountInput.addClass('invalid');
        }
        else if (amount <= 100) {
            transferAmountHelper.attr('data-error', "Amount must be greater than 100");
            transferAmountInput.addClass('invalid');
        } else
            transferAmountInput.addClass('valid');
    }

    var username = transferUserInput.val();

    if (username.length > 0) {
        $.ajax('ajax/check-username-uniqueness', {
            success: function (result) {
                var response = JSON.parse(result);

                if (response['same'] === true) {
                    transferUserInput.addClass('invalid');
                    transferUserHelper.attr('data-error', 'User cannot be yourself');
                }
                else if (response['exists'] === false) {
                    transferUserInput.addClass('invalid');
                    transferUserHelper.attr('data-error', 'User does not exist');
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

        delayTransferUser(function () {
            var username = transferUserInput.val();

            if (username.length > 0) {
                $.ajax('ajax/check-username-uniqueness', {
                    success: function (result) {
                        var response = JSON.parse(result);

                        if (response['same'] === true) {
                            transferUserInput.addClass('invalid');
                            transferUserHelper.attr('data-error', 'User cannot be yourself');
                        }
                        else if (response['exists'] === false) {
                            transferUserInput.addClass('invalid');
                            transferUserHelper.attr('data-error', 'User does not exist');
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


        var amount = parseFloat(transferAmountInput.val());

        //Not empty inputs
        if (transferAmountInput.val().length > 0) {
            if (!Number.isInteger(amount)) {
                transferAmountHelper.attr('data-error', "Amount must be an integer number");
                transferAmountInput.addClass('invalid');
            }
            else if (amount <= 100) {
                transferAmountHelper.attr('data-error', "Amount must be greater than 100");
                transferAmountInput.addClass('invalid');
            } else
                transferAmountInput.addClass('valid');
        }

        if (transferUserInput.hasClass('valid') && transferAmountInput.hasClass('valid'))
            toggleTransferButton(true);


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
    var withdrawalAmountInput = $("#withdraw_amount");

    var withdrawalAmountHelper = $("#withdraw_amount_helper");

    var amount = parseFloat(withdrawalAmountInput.val());

    //ONLOAD
    if (amount > 0) {
        if (!Number.isInteger(amount)) {
            withdrawalAmountHelper.attr('data-error', "Amount must be an integer number");
            withdrawalAmountInput.addClass('invalid');
        }
        else if (amount <= 100) {
            withdrawalAmountHelper.attr('data-error', "Amount must be greater than 100");
            withdrawalAmountInput.addClass('invalid');
        } else
            withdrawalAmountInput.addClass('valid');

        if (withdrawalAmountInput.hasClass('valid'))
            toggleWithdrawButton(true);
    }

    withdrawalAmountInput.on('input keyup', function () {
        withdrawalAmountInput.removeClass('invalid');
        withdrawalAmountInput.removeClass('valid');

        toggleWithdrawButton(false);


        var amount = parseFloat(withdrawalAmountInput.val());

        if (!Number.isInteger(amount)) {
            withdrawalAmountHelper.attr('data-error', "Amount must be an integer number");
            withdrawalAmountInput.addClass('invalid');
        }
        else if (amount <= 100) {
            withdrawalAmountHelper.attr('data-error', "Amount must be greater than 100");
            withdrawalAmountInput.addClass('invalid');
        } else
            withdrawalAmountInput.addClass('valid');

        if (withdrawalAmountInput.hasClass('valid'))
            toggleWithdrawButton(true);


    });

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

    var ticket_content_input = $("#support_content");
    var ticket_content_helper = $("#support_content_helper");

    var ticket_subject_input = $("#support_subject");
    var ticket_subject_helper = $("#support_subject_helper");

    M.textareaAutoResize(ticket_content_input);

    //ONLOAD

    if (ticket_content_input.val().length > 0) {
        if (ticket_content_input.val().length < 60) {

            ticket_content_helper.attr('data-error', "Message must have at least 60 characters");
            ticket_content_input.addClass('invalid');

        } else if (ticket_content_input.val().length > 2000) {
            ticket_content_helper.attr('data-error', "Message is too long");
            ticket_content_input.addClass('invalid');

        }
        else {
            ticket_content_input.addClass('valid');
        }
    }

    if (ticket_subject_input.val().length > 0) {
        if (ticket_subject_input.val().length > 78) {
            ticket_subject_helper.attr('data-error', "Subject is too long");
            ticket_subject_input.addClass('invalid');

        }
        else {
            ticket_subject_input.addClass('valid');
        }

    }
    if (ticket_content_input.hasClass('valid') && ticket_subject_input.hasClass('valid')) {
        toggleTicketButton(true);
    }

    ticket_content_input.on('keyup input', function () {

        toggleTicketButton(false);

        ticket_content_input.removeClass('invalid');
        ticket_content_input.removeClass('valid');


        if (ticket_content_input.val().length < 60) {

            ticket_content_helper.attr('data-error', "Message must have at least 60 characters");
            ticket_content_input.addClass('invalid');

        } else if (ticket_content_input.val().length > 2000) {
            ticket_content_helper.attr('data-error', "Message is too long");
            ticket_content_input.addClass('invalid');

        }
        else {
            ticket_content_input.addClass('valid');
        }

        if (ticket_content_input.hasClass('valid') && ticket_subject_input.hasClass('valid')) {
            toggleTicketButton(true);
        }
    });

    ticket_subject_input.on('keyup input', function () {

        toggleTicketButton(false);

        ticket_subject_input.removeClass('invalid');
        ticket_subject_input.removeClass('valid');


        if (ticket_subject_input.val().length > 78) {
            ticket_subject_helper.attr('data-error', "Subject is too long");
            ticket_subject_input.addClass('invalid');

        }
        else {
            ticket_subject_input.addClass('valid');
        }

        if (ticket_subject_input.hasClass('valid') && ticket_content_input.hasClass('valid')) {
            toggleTicketButton(true);
        }

    });


});