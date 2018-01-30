/**
 * Created by Frank on 10/19/2017.
 */
window.onload = function () {
    timer();
};

/* Filter array */
function filterArrayOfNumbers(newArray) {

    var set = new Set(newArray); //Removing duplicates
    newArray = Array.from(set); //Returning the values to the array
    newArray = newArray.slice(0, 200); //Keeping only 200 numbers

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
function addNumbersToConfirm(array) {

    var numbersList = $("#confirmationNumbers");
    numbersList.empty();
    var toAppend = '';
    var countConfirm = $("#countConfirm");
    var count = array.length;

    if (count === 1)
        countConfirm.html("&nbsp;&nbsp;&nbsp;&nbsp;" + count + " number selected (" + (count * 50) + " bits)");
    else
        countConfirm.html("&nbsp;&nbsp;&nbsp;&nbsp;" + count + " numbers selected (" + (count * 50) + " bits)");

    $.each(array, function (index, value) {
        toAppend = '<div class="chip">' + value + '</div>';
        numbersList.append(toAppend);
    });

    var balance = parseInt($("#balanceNumber").html());
    var insufficientText = $("#insufficientText");
    var playButton = $("#playButton");

    if ((count * 50) > balance) {
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

    /* Field listener */
    $("#checkButtonField").on('click', function () {

        arrayOfNumbers = [];
        var numbersArea = $("#numbersArea").val();
        arrayOfNumbers = numbersArea.match(/\d+/g);
        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);
        addNumbersToConfirm(arrayOfNumbers);

        $('#modal1').modal('open');

    });

    /* Random listener */
    $("#checkButtonRandom").on('click', function () {
        arrayOfNumbers = [];

        var startNumber = parseInt($("#start").val());
        var endNumber = parseInt($("#end").val());
        var numbers = parseInt($("#numberOfNumbers").val());

        arrayOfNumbers = arrayOfNumbers = generateArray(startNumber, endNumber, numbers);
        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);


        addNumbersToConfirm(arrayOfNumbers);
        $('#modal1').modal('open');
    });

    /* Sequence listener */
    $("#checkButtonSequence").on('click', function () {
        arrayOfNumbers = [];

        var startNumber = parseInt($("#startSequence").val());
        var endNumber = parseInt($("#endSequence").val());

        for (var i = startNumber; i <= endNumber; i++) {
            arrayOfNumbers.push(i);
        }

        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);
        addNumbersToConfirm(arrayOfNumbers);

        $('#modal1').modal('open');
    });

    /* Betting */
    $("#playButton").on('click', function () {
        arrayOfNumbers = converToArrayOfInt(arrayOfNumbers);
        bet(arrayOfNumbers);
    });
});


/* Web sockets */
var conn = new ab.Session('ws://localhost:8080',
    function () {
        conn.subscribe('all', function (topic, data) {


            if (data.option === 1) {
                var jackpotNumber = document.getElementById('jackpotNumber');
                jackpotNumber.innerHTML = 155000;
            }


            if (data.option === 2) {

                $("#jackpotNumber").html(data.jackpot);
                $("#gameNumberLast").html(data.last_game_number);
                $("#gameLink").attr("href", "game_info.php?game_id=" + data.last_game_number);
                $("#winnerNumberLast").html(data.last_winner_number);
                $("#jackpotLast").html(data.last_jackpot);

                var lastGameTable = $("#lastGameTable").find("tbody");
                lastGameTable.empty();

                $.each(data.winners, function (index, value) {

                    var toAppend = '';
                    if (value['profit'] > 0)
                        toAppend = '<tr class="win"><td>' + value['username'] + '</td><td>' +
                            value['bet'] + ' bits</td><td><span class="win-text">+' + value['profit'] + ' bits</span></td></tr>';
                    else if (value['profit'] === 0)
                        toAppend = '<tr class="win"><td>' + value['username'] + '</td><td>' +
                            value['bet'] + ' bits</td><td><span class="neutral-text">' + value['profit'] + ' bits</span></td></tr>';
                    else
                        toAppend = '<tr class="win"><td>' + value['username'] + '</td><td>' +
                            value['bet'] + ' bits</td><td><span class="lose-text">' + value['profit'] + ' bits</span></td></tr>';

                    lastGameTable.append(toAppend);
                });

                $.each(data.losers, function (index, value) {

                    lastGameTable.append('<tr class="lose"><td>' + value['username'] + '</td><td>' +
                        value['profit'] + ' bits</td><td><span class="lose-text">-' + value['profit'] + ' bits</span></td></tr>');
                });

                var gameHistoryTable = $("#gameHistoryTable").find("tbody");
                gameHistoryTable.empty();

                $.each(data.games, function (index, value) {
                    gameHistoryTable.append('<tr><td><a href="game_info.php?game_id=' + value['game_id'] + '" target="_blank">' + value['game_id'] + '</a></td><td>' +
                        value['amount'] + ' bits</td><td><div class="chip">' + value['winner_number'] + '</div></td><td>' +
                        value['timedate'] + '</td></tr>');

                });

                updateBalanceAndNumbers();
            }

            if (data.option === 3) {
                var chat_list = $("#chat-messages");
                var time = new Date();
                var hour = time.getHours();
                var minute = time.getMinutes();
                if (minute < 10) {
                    minute = "0" + minute;
                }

                var to_append = "<li><b>" + data.user + "(" + hour + ":" + minute + "): </b>" + data.chat_message + "</li>";
                chat_list.append(to_append);

                chat_list.animate({ scrollTop: chat_list.height() }, 0);
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
        url: "php_ajax/balance_numbers_ajax.php", success: function (result) {
            var response = JSON.parse(result);
            $("#balanceNumber").html(response['balance']);
            var numbersList = $("#numbersList");
            numbersList.empty();

            numbersGlobal = [];
            $.each(response['numbers'], function (index, value) {
                numbersGlobal.push(parseInt(value));
                numbersList.append('<div class="chip small-chip">' + value + '</div>');
            });

            var numbers_card = $("#numbers_card");

            if (response['count'] > 1) {
                $("#count").html("<b>My " + response['count'] + " numbers</b>");
                numbers_card.removeClass('scale-out');
                numbers_card.addClass('scale-in');
            }
            else if (response['count'] === 1) {
                $("#count").html("<b>My number</b>");
                numbers_card.removeClass('scale-out');
                numbers_card.addClass('scale-in');
            }
            else {
                numbers_card.removeClass('scale-in');
                numbers_card.addClass('scale-out');
            }

        }, type: 'GET'
    });
}

/*
* Bet (AJAX)
* */
function bet(arrayOfNumbers) {

    var jsonSend = JSON.stringify(arrayOfNumbers);
    $.ajax({
        url: "php_ajax/play.php", success: function (result) {

            var response = JSON.parse(result);
            $("#balanceNumber").html(response['balance']);
            var numbersList = $("#numbersList");
            numbersList.empty();

            var numbers_card = $("#numbers_card");

            if (response['count'] > 1) {
                $("#count").html("<b>My " + response['count'] + " numbers</b>");
                numbers_card.removeClass('scale-out');
                numbers_card.addClass('scale-in');
            }
            else if (response['count'] === 1) {
                $("#count").html("<b>My number</b>");
                numbers_card.removeClass('scale-out');
                numbers_card.addClass('scale-in');
            }

            numbersGlobal = [];
            $.each(response['numbers'], function (index, value) {
                numbersGlobal.push(parseInt(value));
                numbersList.append('<div class="chip small-chip">' + value + '</div>');
            });


        }, data: {
            numbers: jsonSend
        }
        , type: 'POST'
    });

}

function timer() {

    var value = document.getElementById("time").innerHTML;
    var distance = 300 - value;

    var x = setInterval(function () {

        // Get todays date and time
        distance--;

        if (distance === 0) {
            distance = 300;
            updateBalanceAndNumbers();
        }

        var minutes = Math.floor(distance / 60);
        var seconds = Math.floor(distance % 60);

        // Display the result in the element with id="demo"
        document.getElementById("time").innerHTML = minutes + "m " + seconds + "s ";

    }, 1000);
}