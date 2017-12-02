function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function verifyEmailUniqueness(email) {

    var newEmail = $("#new-email");
    var labelNewEmail = $("#newEmailLabel");

    $.ajax('php_ajax/check_email_uniqueness.php', {
        success: function (result) {
            console.log(result);
            var response = JSON.parse(result);

            if (response['taken'] === true) {
                newEmail.removeClass("valid");
                newEmail.addClass("invalid");

                labelNewEmail.attr("data-error", "Email is already taken");
            }
        },
        data: {
            email: email
        },
        error: function () {
            console.log("error");
        },

        type: "POST"
    });

}

$(function () {
    var newEmail = $("#new-email");
    var confirmEmail = $("#confirm-email");
    var updateEmailButton = $("#updateEmailButton");
    var labelNewEmail = $("#newEmailLabel");
    var labelConfirmEmail = $("#confirmEmailLabel");
    var conEmailVal;
    var newEmailVal;

    confirmEmail.on('keyup click blur input change', function () {
        conEmailVal = confirmEmail.val().replace(/\s/g, '');
        newEmailVal = newEmail.val().replace(/\s/g, '');

        if (isEmail(conEmailVal)) {
            if (conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {
                updateEmailButton.removeClass("disabled");
                newEmail.removeClass("invalid");
                confirmEmail.removeClass("invalid");
                newEmail.addClass("valid");
                confirmEmail.addClass("valid");
            }
            else {
                updateEmailButton.addClass("disabled");
                newEmail.removeClass("valid");
                confirmEmail.removeClass("valid");
                newEmail.addClass("invalid");
                confirmEmail.addClass("invalid");

                labelNewEmail.attr("data-error", "Emails do not match");
                labelConfirmEmail.attr("data-error", "Emails do not match");
            }
        }
        else {
            updateEmailButton.addClass("disabled");
            confirmEmail.removeClass("valid");
            confirmEmail.addClass("invalid");

            labelConfirmEmail.attr("data-error", "Invalid email");
        }

        //Activate button
        if (isEmail(conEmailVal) && isEmail(newEmailVal) &&
            conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {
            updateEmailButton.removeClass("disabled");
        }
    });

    newEmail.on('keyup click input change', function () {
        conEmailVal = confirmEmail.val().replace(/\s/g, '');
        newEmailVal = newEmail.val().replace(/\s/g, '');

        if (isEmail(newEmailVal)) {
            if (conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {
                updateEmailButton.removeClass("disabled");
                newEmail.removeClass("invalid");
                confirmEmail.removeClass("invalid");
                newEmail.addClass("valid");
                confirmEmail.addClass("valid");
            }
            else {
                updateEmailButton.addClass("disabled");
                newEmail.removeClass("valid");
                confirmEmail.removeClass("valid");
                newEmail.addClass("invalid");
                confirmEmail.addClass("invalid");
            }

            labelNewEmail.attr("data-error", "Emails do not match");
            labelConfirmEmail.attr("data-error", "Emails do not match");
        }
        else {
            updateEmailButton.addClass("disabled");
            newEmail.removeClass("valid");
            newEmail.addClass("invalid");
            labelNewEmail.attr("data-error", "Invalid email");
        }

        if (isEmail(conEmailVal) && isEmail(newEmailVal) &&
            conEmailVal === newEmailVal && conEmailVal !== "" && newEmailVal !== "") {
            updateEmailButton.removeClass("disabled");
        }
    });

    newEmail.on('blur', function () {
        newEmailVal = newEmail.val().replace(/\s/g, '');

        if (isEmail(newEmailVal) && newEmailVal !== "")
            verifyEmailUniqueness(newEmailVal);
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