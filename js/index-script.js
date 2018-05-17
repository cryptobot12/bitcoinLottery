var last_game_header_height = 0;

//Resize chat input
$(document).ready(function () {
    M.AutoInit();

    //chat
    var chat_send = $("#chat-send");
    var chat_space = $("#chat-space");
    var input_chat = $("#input-chat");
    var chat_input_line = $("#chat-input-line");

    var chat_input_line_width = chat_input_line.width();
    var chat_send_pl = chat_send.css("paddingLeft");
    var chat_send_pr = chat_send.css("paddingRight");

    var new_width_for_input = parseFloat(chat_input_line_width) - parseFloat(chat_send_pl) - parseFloat(chat_send_pr) - 4;


    var nav_top = $("#nav-top");
    var play_med_col = $("#play_med_col");

    var chat_messages = $("#chat-messages");
    var chat_card_content = $("#chat-card-content");


    var new_height_for_chat_messages = window.innerHeight - parseFloat(nav_top.height()) - parseFloat(play_med_col.height()) -
        parseFloat(chat_card_content.css("paddingTop")) * 2 - parseFloat(chat_input_line.height()) - parseFloat(chat_input_line.css("marginTop")) -
        parseFloat(chat_input_line.css("marginBottom"));

    if (new_height_for_chat_messages > 180) {
        chat_messages.height(new_height_for_chat_messages);
        chat_space.height(new_height_for_chat_messages);
    } else {
        chat_messages.height(180);
        chat_space.height(180);
    }
    input_chat.width(new_width_for_input);

    var play_med_card = $("#play_med_card");
    var numbers_card_med = $("#numbers_card_med");

    var play_med_card_height = play_med_card.height();
    numbers_card_med.height(play_med_card_height);

    //NUMBERS RESIZE

    var count_numbers_med = $("#count_numbers_med");
    var number_list_div = $("#numbers_list_med");
    var card_numbers_content = $("#card-numbers-content");

    number_list_div.height(parseFloat(numbers_card_med.height()) - parseFloat(card_numbers_content.css("paddingTop")) -
        parseFloat(card_numbers_content.css("paddingBottom")) - parseFloat(count_numbers_med.height()) - parseFloat(count_numbers_med.css("marginBottom")));

    //NUMBERS TAB

    var textarea_div_med = $("#textarea_div_med");
    var numbers_list_small = $("#numbers_list_small");
    numbers_list_small.height(0.91 * parseFloat(textarea_div_med.height()));

    //TABLES RESIZE

    var jackpot_card = $("#jackpot_card");
    var chat_card = $("#chat-card");

    var height_for_table_large = parseFloat(chat_card.height()) + parseFloat(chat_card.css("marginTop")) +
        parseFloat(play_med_card.height()) + parseFloat(play_med_card.css("marginBottom")) -
        parseFloat(jackpot_card.height()) - parseFloat(jackpot_card.css("marginBottom"));

    var height_for_table_small = parseFloat(chat_card.height()) + parseFloat(chat_card.css("marginTop")) +
        parseFloat(play_med_card.height()) + parseFloat(play_med_card.css("marginBottom"));

    var table = $("#tables");
    var game_info_card_content = $("#game_info_card_content");
    var game_history_med = $("#game_history_med");
    var table_tabs = $("#table-tabs");
    var last_game_med = $("#last_game_med_table_container");
    var last_game_header = $("#last_game_header");

    if (last_game_header.height() !== 0)
        last_game_header_height = last_game_header.height();

    if ($(window).width() >= 993) {
        table.css("max-height", height_for_table_large);
        game_history_med.css("max-height", parseFloat(height_for_table_large) - parseFloat(table_tabs.height()) - parseFloat(game_info_card_content.css("paddingTop")) -
            parseFloat(game_info_card_content.css("paddingBottom")) + "px");

        last_game_med.css("max-height", parseFloat(height_for_table_large) - parseFloat(table_tabs.height()) - parseFloat(game_info_card_content.css("paddingTop")) -
            parseFloat(game_info_card_content.css("paddingBottom")) - last_game_header_height - 20 + "px");

    } else if ($(window).width() >= 601) {
        table.css("max-height", height_for_table_small);
        game_history_med.css("max-height", parseFloat(height_for_table_small) - parseFloat(table_tabs.height()) - parseFloat(game_info_card_content.css("paddingTop")) -
            parseFloat(game_info_card_content.css("paddingBottom")) + "px");

        last_game_med.css("max-height", parseFloat(height_for_table_small) - parseFloat(table_tabs.height()) - parseFloat(game_info_card_content.css("paddingTop")) -
            parseFloat(game_info_card_content.css("paddingBottom")) - last_game_header_height - 20 + "px");
    }


});

$(window).resize(function () {

    //chat
    var chat_send = $("#chat-send");
    var chat_space = $("#chat-space");
    var input_chat = $("#input-chat");
    var chat_input_line = $("#chat-input-line");

    var chat_space_width = chat_space.width();
    var chat_input_line_width = chat_input_line.width();
    var chat_send_margin = chat_send.css("marginLeft");
    var chat_send_pl = chat_send.css("paddingLeft");
    var chat_send_pr = chat_send.css("paddingRight");

    var new_width_for_input = parseFloat(chat_input_line_width) - parseFloat(chat_send_pl) - parseFloat(chat_send_pr) - 4;


    var nav_top = $("#nav-top");
    var play_med_col = $("#play_med_col");

    var chat_messages = $("#chat-messages");
    var chat_card_content = $("#chat-card-content");

    var new_height_for_chat_messages = window.innerHeight - parseFloat(nav_top.height()) - parseFloat(play_med_col.height()) -
        parseFloat(chat_card_content.css("paddingTop")) * 2 - parseFloat(chat_input_line.height()) * 1.7 - parseFloat(chat_input_line.css("marginTop")) -
        parseFloat(chat_input_line.css("marginBottom")) - 15;


    if (new_height_for_chat_messages > 150) {
        chat_messages.height(new_height_for_chat_messages);
        chat_space.height(new_height_for_chat_messages);
        chat_space.position().top = chat_messages.position().top;
    } else {
        chat_messages.height(150);
        chat_space.height(150);
    }

    input_chat.width(new_width_for_input);

    var play_med_card = $("#play_med_card");
    var numbers_card_med = $("#numbers_card_med");

    var play_med_card_height = play_med_card.height();
    numbers_card_med.height(play_med_card_height);

    //NUMBERS RESIZE

    var count_numbers_med = $("#count_numbers_med");
    var number_list_div = $("#numbers_list_med");
    var card_numbers_content = $("#card-numbers-content");

    number_list_div.height(parseFloat(numbers_card_med.height()) - parseFloat(card_numbers_content.css("paddingTop")) -
        parseFloat(card_numbers_content.css("paddingBottom")) - parseFloat(count_numbers_med.height()) - parseFloat(count_numbers_med.css("marginBottom")));


    //NUMBERS TAB

    var textarea_div_med = $("#textarea_div_med");
    var numbers_list_small = $("#numbers_list_small");
    numbers_list_small.height(0.91 * parseFloat(textarea_div_med.height()));

    var textarea_selector = $("#textarea_selector");
    var sequence_selector = $("#sequence_selector");
    var random_selector = $("#random_selector");

    var play_tabs = $('#play_tabs');
    var play_tabs_controller = M.Tabs.getInstance(play_tabs);


    if ($(window).width() >= 993) {
        if (!textarea_selector.hasClass("active") && !sequence_selector.hasClass("active") && !random_selector.hasClass("active")) {
            play_tabs_controller.select("textarea_div_med");
        }
    }

    //TABLES RESIZE

    var jackpot_card = $("#jackpot_card");
    var chat_card = $("#chat-card");

    var height_for_table_large = parseFloat(chat_card.height()) + parseFloat(chat_card.css("marginTop")) +
        parseFloat(play_med_card.height()) + parseFloat(play_med_card.css("marginBottom")) -
        parseFloat(jackpot_card.height()) - parseFloat(jackpot_card.css("marginBottom"));

    var height_for_table_small = parseFloat(chat_card.height()) + parseFloat(chat_card.css("marginTop")) +
        parseFloat(play_med_card.height()) + parseFloat(play_med_card.css("marginBottom"));

    var table = $("#tables");
    var game_info_card_content = $("#game_info_card_content");
    var game_history_med = $("#game_history_med");
    var table_tabs = $("#table-tabs");
    var last_game_med = $("#last_game_med_table_container");
    var last_game_header = $("#last_game_header");

    if (last_game_header.height() !== 0)
        last_game_header_height = last_game_header.height();

    if ($(window).width() >= 993) {
        table.css("max-height", height_for_table_large);
        game_history_med.css("max-height", parseFloat(height_for_table_large) - parseFloat(table_tabs.height()) - parseFloat(game_info_card_content.css("paddingTop")) -
            parseFloat(game_info_card_content.css("paddingBottom")) + "px");

        last_game_med.css("max-height", parseFloat(height_for_table_large) - parseFloat(table_tabs.height()) - parseFloat(game_info_card_content.css("paddingTop")) -
            parseFloat(game_info_card_content.css("paddingBottom")) - last_game_header_height - 20 + "px");

    } else if ($(window).width() >= 601) {
        table.css("max-height", height_for_table_small);
        game_history_med.css("max-height", parseFloat(height_for_table_small) - parseFloat(table_tabs.height()) - parseFloat(game_info_card_content.css("paddingTop")) -
            parseFloat(game_info_card_content.css("paddingBottom")) + "px");

        last_game_med.css("max-height", parseFloat(height_for_table_small) - parseFloat(table_tabs.height()) - parseFloat(game_info_card_content.css("paddingTop")) -
            parseFloat(game_info_card_content.css("paddingBottom")) - last_game_header_height - 20 + "px");
    }

});


/* Array of numbers validator */
function isArrayOfNumbersValid(array) {

    var isValid = true;

    if (array === null)
        isValid = false;
    else {

        if (array.length > 25)
            isValid = false;
        else {
            $.each(array, function (index, value) {
                if (value > 50000 || value < 1) {
                    isValid = false;

                    return isValid;
                }
            });
        }
    }

    return isValid;
}

/* Text Area Input validator listener + function*/
//Med
$(function () {

    var numbers_textarea_input = $("#numbers_textarea_med");
    var textarea_button = $("#textarea_button_med");
    numbers_textarea_input.on('keyup', function () {

        if (numbers_textarea_input.val().length !== 0) {
            var array = numbers_textarea_input.val().match(/[^\d\s]/g);

            if (array !== null) {
                numbers_textarea_input.removeClass("valid");
                numbers_textarea_input.addClass("invalid");
                textarea_button.addClass("disabled");
            }
            else {

                array = numbers_textarea_input.val().match(/\d+/g);

                if (isArrayOfNumbersValid(array)) {
                    numbers_textarea_input.removeClass("invalid");
                    numbers_textarea_input.addClass("valid");
                    textarea_button.removeClass("disabled");
                }
                else {
                    numbers_textarea_input.removeClass("valid");
                    numbers_textarea_input.addClass("invalid");
                    textarea_button.addClass("disabled");
                }

            }
        }
        else {
            //Just disable button
            numbers_textarea_input.removeClass("valid");
            numbers_textarea_input.removeClass("invalid");
            textarea_button.addClass("disabled");
        }

    });
});

$(function () {

    var numbers_textarea_input = $("#numbers_textarea_small");
    var textarea_button = $("#textarea_button_small");
    numbers_textarea_input.on('keyup input', function () {

        if (numbers_textarea_input.val().length !== 0) {
            var array = numbers_textarea_input.val().match(/[^\d\s]/g);

            if (array !== null) {
                numbers_textarea_input.removeClass("valid");
                numbers_textarea_input.addClass("invalid");
                textarea_button.addClass("disabled");
            }
            else {

                array = numbers_textarea_input.val().match(/\d+/g);

                if (isArrayOfNumbersValid(array)) {
                    numbers_textarea_input.removeClass("invalid");
                    numbers_textarea_input.addClass("valid");
                    textarea_button.removeClass("disabled");
                }
                else {
                    numbers_textarea_input.removeClass("valid");
                    numbers_textarea_input.addClass("invalid");
                    textarea_button.addClass("disabled");
                }

            }
        }
        else {
            //Just disable button
            numbers_textarea_input.removeClass("valid");
            numbers_textarea_input.removeClass("invalid");
            textarea_button.addClass("disabled");
        }

    });
});


/* Function validator sequence */
function validate_sequence_med() {

    var startSequence = $("#start_sequence_med");
    var endSequence = $("#end_sequence_med");
    var playButton = $("#sequence_button_med");

    var startNumber = parseInt(startSequence.val());
    var endNumber = parseInt(endSequence.val());

    var verifyStart = startSequence.val();
    var verifyEnd = endSequence.val();

    if (verifyStart !== "" && verifyEnd !== "") {
        if (startNumber < 1 || startNumber > 50000 || (startNumber > endNumber) ||
            ((endNumber - startNumber) > 24) || ((endNumber - startNumber) < 0) ||
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

/* Function validator sequence */
function validate_sequence_small() {

    var startSequence = $("#start_sequence_med");
    var endSequence = $("#end_sequence_med");
    var playButton = $("#sequence_button_small");

    var startNumber = parseInt(startSequence.val());
    var endNumber = parseInt(endSequence.val());

    var verifyStart = startSequence.val();
    var verifyEnd = endSequence.val();

    if (verifyStart !== "" && verifyEnd !== "") {
        if (startNumber < 1 || startNumber > 50000 || (startNumber > endNumber) ||
            ((endNumber - startNumber) > 24) || ((endNumber - startNumber) < 0) ||
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
//Med
$(function () {

    var startSequence = $("#start_sequence_med");
    var endSequence = $("#end_sequence_med");

    startSequence.on('keyup input', function () {
        validate_sequence_med()
    });
    endSequence.on('keyup input', function () {
        validate_sequence_med()
    });

});

// Small
$(function () {

    var startSequence = $("#start_sequence_small");
    var endSequence = $("#end_sequence_small");

    startSequence.on('keyup input', function () {
        validate_sequence_small()
    });
    endSequence.on('keyup input', function () {
        validate_sequence_small()
    });

});

/* Random Input Validator Function */
function validate_random_med() {
    var startRandom = $("#start_random_med");
    var endRandom = $("#end_random_med");
    var numberOfNumbers = $("#how_many_numbers_med");
    var randomButton = $("#random_button_med");

    var start = parseInt(startRandom.val());
    var end = parseInt(endRandom.val());
    var numbersON = parseInt(numberOfNumbers.val());

    var verifyStart = startRandom.val();
    var verifyEnd = endRandom.val();
    var verifyNumbers = numberOfNumbers.val();

    if (verifyStart !== "" && verifyEnd !== "" && verifyNumbers !== "") {
        if ((start <= end) && ((end - start + 1) >= numbersON) && (start > 0) && (end > 0) &&
            (end <= 50000) && (start <= 50000) && (numbersON <= 25) && (numbersON > 0)) {
            startRandom.removeClass('invalid');
            endRandom.removeClass('invalid');
            numberOfNumbers.removeClass('invalid');

            startRandom.addClass('valid');
            endRandom.addClass('valid');
            numberOfNumbers.addClass('valid');

            randomButton.removeClass('disabled');
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

function validate_random_small() {
    var startRandom = $("#start_random_small");
    var endRandom = $("#end_random_small");
    var numberOfNumbers = $("#how_many_numbers_small");
    var randomButton = $("#random_button_small");

    var start = parseInt(startRandom.val());
    var end = parseInt(endRandom.val());
    var numbersON = parseInt(numberOfNumbers.val());

    var verifyStart = startRandom.val();
    var verifyEnd = endRandom.val();
    var verifyNumbers = numberOfNumbers.val();

    if (verifyStart !== "" && verifyEnd !== "" && verifyNumbers !== "") {
        if ((start <= end) && ((end - start + 1) >= numbersON) && (start > 0) && (end > 0) &&
            (end <= 50000) && (start <= 50000) && (numbersON <= 25) && (numbersON > 0)) {
            startRandom.removeClass('invalid');
            endRandom.removeClass('invalid');
            numberOfNumbers.removeClass('invalid');

            startRandom.addClass('valid');
            endRandom.addClass('valid');
            numberOfNumbers.addClass('valid');

            randomButton.removeClass('disabled');
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
//Med
$(function () {
    var startRandom = $("#start_random_med");
    var endRandom = $("#end_random_med");
    var numberOfNumbers = $("#how_many_numbers_med");

    startRandom.on('keyup input', function () {
        validate_random_med()
    });
    endRandom.on('keyup input', function () {
        validate_random_med()
    });
    numberOfNumbers.on('keyup input', function () {
        validate_random_med()
    });
});

//Small
$(function () {
    var startRandom = $("#start_random_small");
    var endRandom = $("#end_random_small");
    var numberOfNumbers = $("#how_many_numbers_small");

    startRandom.on('keyup input', function () {
        validate_random_small()
    });
    endRandom.on('keyup input', function () {
        validate_random_small()
    });
    numberOfNumbers.on('keyup input', function () {
        validate_random_small()
    });
});

function send_message() {

    var chat_input = $("#input-chat");
    var message = chat_input.val();

    var chat_send_button = $("#chat-send");

    chat_send_button.addClass('disabled');
    chat_send_button.prop('disabled', true);


    $.ajax({
        url: 'ajax/send_message',
        success: function (result) {
            if (result === 'tmm') {
                var chat_list = $("#chat-messages");

                var to_append = "<li class='red-text'>You have been sending too many messages. Please wait before using the chat again.</li>";
                chat_list.append(to_append);

                chat_list.animate({scrollTop: chat_list.height()}, 0);
            }
            chat_send_button.removeClass('disabled');
            chat_send_button.prop('disabled', false);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            chat_send_button.removeClass('disabled');
            chat_send_button.prop('disabled', false);
        },
        data: {message: message},
        method: "POST"
    });

    chat_input.val(null);
    chat_input.focus();
}

//Chat
$(function () {

    var chat_input = $("#input-chat");
    var chat_send_button = $("#chat-send");

    chat_input.keydown(function (e) {
        if (e.keyCode === 13) {
            if (chat_input.val().length > 0) {
                send_message();
            }

        }
    });

    chat_send_button.on('click', function () {

        if (chat_input.val().length > 0) {
            send_message();
        }
    });

});

/* Filter array */
function filterArrayOfNumbers(newArray) {

    var set = new Set(newArray); //Removing duplicates
    newArray = Array.from(set); //Returning the values to the array
    newArray = newArray.slice(0, 25); //Keeping only 25 numbers

    //Keeping only new numbers
    var currentNumbers = numbersGlobal;

    newArray = $.grep(newArray, function (el, index) {

        return ($.inArray(parseInt(el), currentNumbers) !== -1);
    }, true);

    return newArray;
}

function converToArrayOfInt(array) {

    var newArray = new Array(0);

    $.each(array, function (index, value) {
        newArray.push(parseInt(value));
    });

    return newArray;
}

/* Adding numbers to confirmation list*/
function add_numbers_to_confirm_med(array) {

    var numbersList = $("#confirmation_numbers_med");
    numbersList.empty();
    var toAppend = '';
    var countConfirm = $("#count_numbers_confirm_med");
    var count = array.length;

    if (count === 1)
        countConfirm.html(count + " number selected (" + (count * 100) + " bits)");
    else
        countConfirm.html(count + " numbers selected (" + (count * 100) + " bits)");

    $.each(array, function (index, value) {
        toAppend = '<div class="chip yellow">' + value + '</div>';
        numbersList.append(toAppend);
    });

    var balance = parseInt($("#my_balance").html());
    var insufficientText = $("#insufficient_balance_med");
    var playButton = $("#play_button_med");

    if ((count * 100) > balance) {
        insufficientText.removeClass("hidden");
        playButton.hide();
    }
    else {
        insufficientText.addClass("hidden");
        playButton.show();
    }

}

function add_numbers_to_confirm_small(array) {

    var numbersList = $("#confirmation_numbers_small");
    numbersList.empty();
    var toAppend = '';
    var countConfirm = $("#count_numbers_confirm_small");
    var count = array.length;

    if (count === 1)
        countConfirm.html("&nbsp;&nbsp;&nbsp;&nbsp;" + count + " number selected (" + (count * 100) + " bits)");
    else
        countConfirm.html("&nbsp;&nbsp;&nbsp;&nbsp;" + count + " numbers selected (" + (count * 100) + " bits)");

    $.each(array, function (index, value) {
        toAppend = '<div class="chip">' + value + '</div>';
        numbersList.append(toAppend);
    });

    var balance = parseInt($("#my_balance").html());
    var insufficientText = $("#insufficient_balance_small");
    var playButton = $("#play_button_small");

    if ((count * 100) > balance) {
        insufficientText.removeClass("hidden");
        playButton.hide();
    }
    else {
        insufficientText.addClass("hidden");
        playButton.show();
    }

}

/* Shuffle array */
function shuffle(array) {
    var currentIndex = array.length, temporaryValue, randomIndex;

    // While there remain elements to shuffle...
    while (0 !== currentIndex) {

        // Pick a remaining element...
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex -= 1;

        // And swap it with the current element.
        temporaryValue = array[currentIndex];
        array[currentIndex] = array[randomIndex];
        array[randomIndex] = temporaryValue;
    }

    return array;
}

/* Generate random array */
function generateArray(ini, end, numbers) {

    var array = new Array(0);

    for (var i = ini; i <= end; i++) {
        array.push(i);
    }

    array = shuffle(array);

    while (array.length > numbers) {
        array.splice(0, 1);
    }

    return array.sort(function (a, b) {
        return a - b;
    });
}

/* Listeners  */
$(function () {

    var arrayOfNumbers = new Array(0);

    /* Text area listener med*/
    $("#textarea_button_med").on('click', function () {

        arrayOfNumbers = [];
        var numbersArea = $("#numbers_textarea_med").val();
        arrayOfNumbers = numbersArea.match(/\d+/g);
        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);
        add_numbers_to_confirm_med(arrayOfNumbers);

        $('#confirm_numbers_modal_med').modal('open');

    });

    /* Text area listener small*/
    $("#textarea_button_small").on('click', function () {

        arrayOfNumbers = [];
        var numbersArea = $("#numbers_textarea_small").val();
        arrayOfNumbers = numbersArea.match(/\d+/g);
        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);
        add_numbers_to_confirm_small(arrayOfNumbers);

        $('#confirm_numbers_modal_small').modal('open');

    });

    /* Random listener med*/
    $("#random_button_med").on('click', function () {
        arrayOfNumbers = [];

        var startNumber = parseInt($("#start_random_med").val());
        var endNumber = parseInt($("#end_random_med").val());
        var numbers = parseInt($("#how_many_numbers_med").val());

        arrayOfNumbers = arrayOfNumbers = generateArray(startNumber, endNumber, numbers);
        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);

        add_numbers_to_confirm_med(arrayOfNumbers);
        $('#confirm_numbers_modal_med').modal('open');
    });

    /* Random listener */
    $("#random_button_small").on('click', function () {
        arrayOfNumbers = [];

        var startNumber = parseInt($("#start_random_small").val());
        var endNumber = parseInt($("#end_random_small").val());
        var numbers = parseInt($("#how_many_numbers_small").val());

        arrayOfNumbers = arrayOfNumbers = generateArray(startNumber, endNumber, numbers);
        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);

        add_numbers_to_confirm_small(arrayOfNumbers);
        $('#confirm_numbers_modal_small').modal('open');
    });

    /* Sequence listener med */
    $("#sequence_button_med").on('click', function () {
        arrayOfNumbers = [];

        var startNumber = parseInt($("#start_sequence_med").val());
        var endNumber = parseInt($("#end_sequence_med").val());

        for (var i = startNumber; i <= endNumber; i++) {
            arrayOfNumbers.push(i);
        }

        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);
        add_numbers_to_confirm_med(arrayOfNumbers);

        $('#confirm_numbers_modal_med').modal('open');
    });

    /* Sequence listener small */
    $("#sequence_button_small").on('click', function () {
        arrayOfNumbers = [];

        var startNumber = parseInt($("#start_sequence_small").val());
        var endNumber = parseInt($("#end_sequence_small").val());

        for (var i = startNumber; i <= endNumber; i++) {
            arrayOfNumbers.push(i);
        }

        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);
        add_numbers_to_confirm_small(arrayOfNumbers);

        $('#confirm_numbers_modal_small').modal('open');
    });

    /* Betting */
    $("#play_button_med").on('click', function () {
        arrayOfNumbers = converToArrayOfInt(arrayOfNumbers);
        bet(arrayOfNumbers);
    });

    /* Betting */
    $("#play_button_small").on('click', function () {
        arrayOfNumbers = converToArrayOfInt(arrayOfNumbers);
        bet(arrayOfNumbers);
    });
});


/* Web sockets */
var conn = new ab.Session('ws://localhost:8080',
    function () {
        conn.subscribe('all', function (topic, data) {

            if (data.option === 1) {
                var jackpotNumber = $("#jackpot_number");
                jackpotNumber.html(data.jackpot);
            }


            if (data.option === 2) {

                $("#jackpot_number").html(data.jackpot);
                $("#last_game_number_med").html(data.last_game_number);
                $("#game_link_med").attr("href", "http://localhost/bitcoinLottery/game_info/" + data.last_game_number);
                $("#last_winner_number_med").html(data.last_winner_number);
                $("#last_jackpot_med").html(data.last_jackpot);

                var lastGameTable = $("#last_game_table_med").find("tbody");
                lastGameTable.empty();

                //Last game
                $.each(data.players, function (index, value) {

                    var toAppend = '';

                    if (value['win'] === 1) {
                        if (value['profit'] > 0)
                            toAppend = '<tr class="win"><td>' + value['username'] + '</td><td>' +
                                value['bet'] + ' bits</td><td><span class="win-text">+' + value['profit'] + ' bits</span></td></tr>';
                        else if (value['profit'] === 0)
                            toAppend = '<tr class="win"><td>' + value['username'] + '</td><td>' +
                                value['bet'] + ' bits</td><td><span class="neutral-text">' + value['profit'] + ' bits</span></td></tr>';
                        else
                            toAppend = '<tr class="win"><td>' + value['username'] + '</td><td>' +
                                value['bet'] + ' bits</td><td><span class="lose-text">' + value['profit'] + ' bits</span></td></tr>';
                    } else {

                        toAppend = '<tr class="lose"><td>' + value['username'] + '</td><td>' +
                            value['bet'] + ' bits</td><td><span class="lose-text">' + value['profit'] + ' bits</span></td></tr>';
                    }


                    lastGameTable.append(toAppend);
                });

                var gameHistoryTableMed = $("#game_history_table_large");
                var gameHistoryTableSmall = $("#game_history_table_small");

                $.each(data.games, function (index, value) {
                    gameHistoryTableMed.append('<tr><td><a href="http://localhost/bitcoinLottery/game_info/' + value['game_id'] + '" target="_blank">' + value['game_id'] + '</a></td><td>' +
                        value['amount'] + ' bits</td><td><div class="chip">' + value['winner_number'] + '</div></td><td>' +
                        value['timedate'] + '</td></tr>');

                    gameHistoryTableSmall.append('<tr><td><a href="http://localhost/bitcoinLottery/game_info/' + value['game_id'] + '" target="_blank">' + value['game_id'] + '</a></td><td>' +
                        value['amount'] + ' bits</td><td><div class="chip">' + value['winner_number'] + '</div></td><td>' +
                        value['timedate'] + '</td></tr>');

                });

                updateBalanceAndNumbers();
            }

            if (data.option === 3) {
                var chat_list = $("#chat-messages");
                var time = new Date(data.sentat);
                time.setMinutes(time.getMinutes() + to);
                var hour = time.getHours();
                var minute = time.getMinutes();
                if (minute < 10) {
                    minute = "0" + minute;
                }

                var to_append = "<li><b>" + data.user + " (" + hour + ":" + minute + "): </b>" + data.chat_message + "</li>";
                chat_list.append(to_append);

                chat_list.animate({scrollTop: chat_list.height()}, 0);
            }
        });
    },
    function () {
        console.warn('WebSocket connection closed');
    },
    {'skipSubprotocolCheck': true}
);

/* Ajax request to update numbers and balance */
function updateBalanceAndNumbers() {
    $.ajax({
        url: "ajax/balance_numbers_ajax", success: function (result) {
            var response = JSON.parse(result);
            $("#my_balance").html(response['balance']);
            var numbers_list_small = $("#numbers_list_small");
            var numbers_list_med = $("#numbers_list_med");

            numbers_list_small.empty();
            numbers_list_med.empty();

            numbersGlobal = [];

            $.each(response['numbers'], function (index, value) {
                numbersGlobal.push(parseInt(value));
                numbers_list_small.append('<div class="chip small-chip">' + value + '</div>');
            });

            $.each(response['numbers'], function (index, value) {
                numbersGlobal.push(parseInt(value));
                numbers_list_med.append('<div class="chip small-chip">' + value + '</div>');
            });

            var numbers_card_small = $("#numbers_card_small");
            var numbers_card_medium = $("#numbers_card_med");

            if (response['count'] > 1) {
                $("#count_numbers_med").html("<b>My " + response['count'] + " numbers</b>");
                $("#count_numbers_small").html("<b>My " + response['count'] + " numbers</b>");
                numbers_card_small.removeClass('scale-out');
                numbers_card_small.addClass('scale-in');
                numbers_card_medium.removeClass('scale-out');
                numbers_card_medium.addClass('scale-in');
            }
            else if (response['count'] === 1) {
                $("#count_numbers_small").html("<b>My number</b>");
                $("#count_numbers_med").html("<b>My number</b>");
                numbers_card_small.removeClass('scale-out');
                numbers_card_small.addClass('scale-in');
                numbers_card_medium.removeClass('scale-out');
                numbers_card_medium.addClass('scale-in');
            }
            else {
                $("#count_numbers_small").html("<b>No numbers yet</b>");
                $("#count_numbers_med").html("<b>No numbers yet</b>");
                numbers_card_small.removeClass('scale-in');
                numbers_card_small.addClass('scale-out');
                numbers_card_medium.removeClass('scale-out');
                numbers_card_medium.addClass('scale-in');
            }

        }, type: 'GET'
    });
}

/*
* Bet (AJAX)
* */
function bet(arrayOfNumbers) {

    var my_numbers = JSON.stringify(arrayOfNumbers);
    $.ajax({
        url: "ajax/play", success: function (result) {
            var response = JSON.parse(result);

            $("#my_balance").html(response['balance']);
            var numbers_list_med = $("#numbers_list_med");
            var numbers_list_small = $("#numbers_list_small");
            numbers_list_med.empty();
            numbers_list_small.empty();

            var numbers_card_med = $("#numbers_card_med");
            var numbers_card_small = $("#numbers_card_small");

            if (response['count'] > 1) {
                $("#count_numbers_med").html("<b>My " + response['count'] + " numbers</b>");
                $("#count_numbers_small").html("<b>My " + response['count'] + " numbers</b>");

                numbers_card_med.removeClass('scale-out');
                numbers_card_med.addClass('scale-in');

                numbers_card_small.removeClass('scale-out');
                numbers_card_small.addClass('scale-in');
            }
            else if (response['count'] === 1) {
                $("#count_numbers_med").html("<b>My number</b>");
                $("#count_numbers_small").html("<b>My number</b>");

                numbers_card_med.removeClass('scale-out');
                numbers_card_med.addClass('scale-in');
            }

            numbersGlobal = [];
            $.each(response['numbers'], function (index, value) {
                numbersGlobal.push(parseInt(value));
                numbers_list_med.append('<div class="chip small-chip yellow"><b>' + value + '</b></div>');
                numbers_list_small.append('<div class="chip small-chip yellow"><b>' + value + '</b></div>');
            });


        }, data: {
            numbers: my_numbers
        }
        , type: 'POST'
    });

}

