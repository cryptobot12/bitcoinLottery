$(document).ready(function () {
    M.AutoInit();
});

function togglePasswordResetButton() {

    var new_password_input = $("#new_password");
    var confirm_password_input = $("#confirm_new_password");
    var password_reset_button = $("#password_reset_button");

    if (new_password_input.hasClass('valid') && confirm_password_input.hasClass('valid')) {
        password_reset_button.prop("disabled", false);
        password_reset_button.removeClass("disabled");
    } else {
        password_reset_button.prop("disabled", true);
        password_reset_button.addClass("disabled");
    }
}

$(function () {

    var new_password_input = $("#new_password");
    var confirm_password_input = $("#confirm_new_password");

    new_password_input.on('keyup input', function () {
        new_password_input.removeClass('invalid');
        new_password_input.removeClass('valid');
        confirm_password_input.removeClass('invalid');
        confirm_password_input.removeClass('valid');

        var confirm_password_value = confirm_password_input.val();
        var new_password_value = new_password_input.val();

        if (new_password_value.length < 8)
            new_password_input.addClass('invalid');
        else
            new_password_input.addClass('valid');

        if (confirm_password_value.length > 0)
            if (new_password_value !== confirm_password_value)
                confirm_password_input.addClass('invalid');
            else
                confirm_password_input.addClass('valid');

        togglePasswordResetButton();
    });

    confirm_password_input.on('keyup input', function () {
        confirm_password_input.removeClass('invalid');
        confirm_password_input.removeClass('valid');

        var confirm_password_value = confirm_password_input.val();
        var new_password_value = new_password_input.val();


        if (confirm_password_value.length > 0)
            if (new_password_value !== confirm_password_value)
                confirm_password_input.addClass('invalid');
            else
                confirm_password_input.addClass('valid');

        togglePasswordResetButton();
    });
});