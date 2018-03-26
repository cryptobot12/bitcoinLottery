function toggle_register_button() {
    var username_input_el = $("#username");
    var password_input_el = $("#password");
    var confirm_password_input_el = $("#confirm_password");
    var email_input_el = $("#email");
    var register_button = $("#register_button");

    if (username_input_el.hasClass('valid') && password_input_el.hasClass('valid')
    && confirm_password_input_el.hasClass('valid') && email_input_el.hasClass('valid')){
        register_button.prop('disabled', false);
        register_button.removeClass('disabled');
    }
    else {
        register_button.prop('disabled', true);
        register_button.addClass('disabled');
    }
}

function isEmail(email) {
    var regex = /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/;
    return regex.test(email);
}

$(function () {

    var delay_username = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    var delay_password = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    var delay_confirm_password = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    var delay_email = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    var username_input_el = $("#username");
    var password_input_el = $("#password");
    var confirm_password_input_el = $("#confirm_password");
    var email_input_el = $("#email");

    var username_label_el = $("#username_label");
    var password_label_el = $("#password_label");
    var confirm_password_label_el = $("#confirm_password_label");
    var email_label_el = $("#email_label");

    username_input_el.on('keyup input', function () {
        username_input_el.removeClass('invalid');
        username_input_el.removeClass('valid');

        toggle_register_button();

        delay_username(function () {

            var username_val = username_input_el.val();

            if (username_val.length > 0) {
                if (/^[a-z0-9_-]+$/.test(username_val)) {
                    if (username_val.length >= 3 && username_val.length <= 19) {
                        check_username_uniqueness();
                    }
                    else {
                        username_input_el.addClass('invalid');
                        username_label_el.attr('data-error', "Username length must be between 4 and 19 characters");
                    }
                }
                else {
                    username_input_el.addClass('invalid');
                    username_label_el.attr('data-error', "Only letters, numbers and '_-' are allowed");
                }
            }
            else {
                username_input_el.addClass('invalid');
                username_label_el.attr('data-error', "Username is required");
            }

        }, 1800);
    });

    password_input_el.on('keyup input', function () {
        password_input_el.removeClass('invalid');
        password_input_el.removeClass('valid');

        toggle_register_button();

        delay_password(function () {
            var password_val = password_input_el.val();

            if (password_val.length > 0) {
                if (password_val.length >= 8) {
                    password_input_el.addClass('valid');
                    //password_label_el.attr('data-success', "Password is valid")
                }
                else {
                    password_input_el.addClass('invalid');
                    password_label_el.attr('data-error', "Password must be at least 8 characters long");
                }
            }
            else {
                password_input_el.addClass('invalid');
                password_label_el.attr('data-error', "Password is required");
            }

        }, 1800);

    });

    confirm_password_input_el.on('keyup input', function () {
        confirm_password_input_el.removeClass('invalid');
        confirm_password_input_el.removeClass('valid');

        toggle_register_button();

        delay_confirm_password(function () {
            var confirm_password_val = confirm_password_input_el.val();
            var password_val = password_input_el.val();

            if (confirm_password_val === password_val) {
                confirm_password_input_el.addClass('valid');
            }
            else {
                confirm_password_input_el.addClass('invalid');
                confirm_password_label_el.attr('data-error', "Passwords do not match");
            }

        }, 1800);
    });

    email_input_el.on('keyup input', function () {
        email_input_el.removeClass('invalid');
        email_input_el.removeClass('valid');

        toggle_register_button();

        delay_email(function () {
            var email_val = email_input_el.val();

            if (email_val.length > 0) {
                if (isEmail(email_val)) {
                    check_email_uniqueness();
                } else {
                    email_input_el.addClass('invalid');
                    email_label_el.attr('data-error', "Invalid email");
                }
            }
            else {
                email_input_el.addClass('invalid');
                email_label_el.attr('data-error', "Email is required");
            }
        }, 1800);

    });

});

function check_email_uniqueness() {

    var email_input_el = $("#email");
    var email_label_el = $("#email_label");
    var email_val = email_input_el.val();

    $.ajax('ajax/check-email-uniqueness.php', {
        success: function (result) {
            var response = JSON.parse(result);

            if (response['taken'] === true) {
                email_input_el.addClass("invalid");
                email_label_el.attr("data-error", "Email is already taken");
            }
            else {
                email_input_el.addClass('valid');
                email_label_el.attr("data-success", "Email is available");
            }

            toggle_register_button()
        },
        data: {
            email: email_val
        },
        error: function () {
            console.log("error");
        },

        method: "POST"
    });
}

function check_username_uniqueness() {
    var username_input_el = $("#username");
    var username_label_el = $("#username_label");
    var username_val = username_input_el.val();


    $.ajax('ajax/check-username-uniqueness.php', {
        success: function (result) {
            var response = JSON.parse(result);

            if (response['taken'] === true) {
                username_input_el.addClass('invalid');
                username_label_el.attr('data-error', "Username is already taken");

            }
            else {
                username_input_el.addClass('valid');
                username_label_el.attr('data-success', "Username is available");

                toggle_register_button();
            }
        },
        data: {
            username: username_val
        },
        error: function () {
            console.log("Error: Trying to verify username uniqueness")
        },
        method: "POST"
    });
}