$(function () {
    var message_input = $("#message");
    var review_message = $("#message_preview");

    message_input.on('keyup', function () {
        var message_content = message_input.val();
        review_message.html(message_content);
    });
});