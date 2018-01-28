$(function () {

    $('.modal').modal();

});


/* Modal initializer*/
$(document).ready(function(){

    $("#expand").collapsible({
        onOpen: function (el) {
            $("#expand-icon").html("expand_more");
        },
        onClose: function (el) {
            $("#expand-icon").html("expand_less");
        }
    });

});

//Resize chat input
$(document).ready(function() {
    var chat_send = $("#chat-send");
    var jackpot_card = $("#jackpot_card");
    var input_chat = $("#input-chat");

    var jackpot_card_width = jackpot_card.width();
    var chat_send_margin = chat_send.css("marginLeft");
    var chat_send_pl = chat_send.css("paddingLeft");
    var chat_send_pr = chat_send.css("paddingRight");

    var new_width = parseFloat(jackpot_card_width) - parseFloat(chat_send_margin) -
        parseFloat(chat_send_pl) - parseFloat(chat_send_pr) - 5;

    input_chat.width(new_width);

    // var chat_space = $("#chat-space");
    // var chat_input_inline = $("#chat-input-line");
    //
    // chat_input_inline.css("marginTop", parseFloat(chat_space.height()) - parseFloat(chat_input_inline.height()));

});

$(window).resize(function() {
    var chat_send = $("#chat-send");
    var jackpot_card = $("#jackpot_card");
    var input_chat = $("#input-chat");

    var jackpot_card_width = jackpot_card.width();
    var chat_send_width = chat_send.width();
    var chat_send_margin = chat_send.css("marginLeft");
    var chat_send_pl = chat_send.css("paddingLeft");
    var chat_send_pr = chat_send.css("paddingRight");

    var new_width = parseFloat(jackpot_card_width) - parseFloat(chat_send_margin) -
        parseFloat(chat_send_pl) - parseFloat(chat_send_pr) - 5;

    input_chat.width(new_width);
});


/* Array of numbers validator */
function isArrayOfNumbersValid(array) {

    var isValid = true;

    if (array === null)
        isValid = false;
    else {

        if (array.length > 200)
            isValid = false;
        else {
            $.each(array, function (index, value) {
                if (value > 50000 || value < 1)
                    isValid = false;
                return isValid;
            });
        }
    }

    return isValid;
}

/* Text Area Input validator listener + function*/
$(function () {

    var numbersArea = $("#numbersArea");
    var playButton = $("#checkButtonField");
    numbersArea.on('keyup', function () {

        var array = numbersArea.val().match(/[^\d\s]/g);


            if (array !== null) {
                numbersArea.removeClass("valid");
                numbersArea.addClass("invalid");
                playButton.addClass("disabled");
            }
            else {

                array = numbersArea.val().match(/\d+/g);

                if (isArrayOfNumbersValid(array)) {
                    numbersArea.removeClass("invalid");
                    numbersArea.addClass("valid");
                    playButton.removeClass("disabled");
                }
                else {
                    numbersArea.removeClass("valid");
                    numbersArea.addClass("invalid");
                    playButton.addClass("disabled");
                }

            }



    });
});

/* Function validator sequence */
function validateSequence() {

    var startSequence = $("#startSequence");
    var endSequence = $("#endSequence");
    var playButton = $("#checkButtonSequence");
    
    var startNumber = parseInt(startSequence.val());
    var endNumber = parseInt(endSequence.val());

    var verifyStart = startSequence.val();
    var verifyEnd = endSequence.val();

    if (verifyStart !== "" && verifyEnd !== "") {
        if (startNumber < 1 || startNumber > 50000 || (startNumber > endNumber) ||
            ((endNumber - startNumber) > 200) || ((endNumber - startNumber) < 0) ||
            endNumber < 1 || endNumber > 50000) {
            startSequence.removeClass("valid");
            endSequence.removeClass("valid");
            startSequence.addClass("invalid");
            endSequence.addClass("invalid");
            playButton.addClass("disabled");
        }
        else {
            startSequence.removeClass("invalid");
            endSequence.removeClass("invalid");
            startSequence.addClass("valid");
            endSequence.addClass("valid");
            playButton.removeClass("disabled");
        }
    }
    else {
        startSequence.removeClass("valid");
        endSequence.removeClass("valid");
        startSequence.addClass("invalid");
        endSequence.addClass("invalid");
        playButton.addClass("disabled");
    }
}

/* Sequence Input validator listeners */
$(function () {

    var startSequence = $("#startSequence");
    var endSequence = $("#endSequence");

    startSequence.on('keyup', function() { validateSequence() });
    endSequence.on('keyup', function() { validateSequence() });

});

/* Random Input Validator Function */
function validateRandom() {
    var startRandom = $("#start");
    var endRandom = $("#end");
    var numberOfNumbers = $("#numberOfNumbers");
    var randomButton = $("#checkButtonRandom");

    var start = parseInt(startRandom.val());
    var end = parseInt(endRandom.val());
    var numbersON = parseInt(numberOfNumbers.val());

    var verifyStart = startRandom.val();
    var verifyEnd = endRandom.val();
    var verifyNumbers = numberOfNumbers.val();

    if (verifyStart !== "" && verifyEnd !== "" && verifyNumbers !== "")
    {
        if ((start <= end) && ((end - start + 1) >= numbersON) && (start > 0) && (end > 0) &&
            (end <= 50000) && (start <= 50000) && (numbersON <= 200) && (numbersON > 0))
        {
            startRandom.removeClass('invalid');
            endRandom.removeClass('invalid');
            numberOfNumbers.removeClass('invalid');

            startRandom.addClass('valid');
            endRandom.addClass('valid');
            numberOfNumbers.addClass('valid');

            randomButton.removeClass('disabled');
        }
        else
        {
            startRandom.removeClass('valid');
            endRandom.removeClass('valid');
            numberOfNumbers.removeClass('valid');

            startRandom.addClass('invalid');
            endRandom.addClass('invalid');
            numberOfNumbers.addClass('invalid');

            randomButton.addClass('disabled');
        }
    }
    else {
        startRandom.removeClass('valid');
        endRandom.removeClass('valid');
        numberOfNumbers.removeClass('valid');

        startRandom.addClass('invalid');
        endRandom.addClass('invalid');
        numberOfNumbers.addClass('invalid');

        randomButton.addClass('disabled');
    }


}

/* Random Input validator listeners */
$(function () {
   var startRandom = $("#start");
   var endRandom = $("#end");
   var numberOfNumbers = $("#numberOfNumbers");

   startRandom.on('keyup', function () { validateRandom()});
    endRandom.on('keyup', function () {validateRandom()});
    numberOfNumbers.on('keyup', function () {validateRandom()});
});

$(function () {

    var chat_input = $("#input-chat");
    var chat_send = $("#chat-send");
});

