/**
 * Created by Frank on 10/19/2017.
 */
window.onload = function () {
    timer();
};

/* Filter array*/

function filterArrayOfNumbers(newArray) {

    newArray.slice(0, 200); //Keeping only 200 numbers

    //Keeping only new numbers
    var currentNumbers = JSON.parse(Cookies.get("numbers_list"));
    newArray = $.grep(newArray, function (el, index) {

        return ($.inArray(parseInt(el), currentNumbers) !== -1);
    }, true);

    return newArray;
}

/* Adding numbers to confirmation list*/
function addNumbersToConfirm(array) {

    var numbersList = $("#confirmationNumbers");
    numbersList.html("");
    var toAppend = '';
    $.each(array, function (index, value) {
        toAppend = '<div class="chip">' + value + '</div>';
        numbersList.append(toAppend);
    })
}

/* Listeners  */
$(function () {

    var arrayOfNumbers = new Array(0);

    /* Field listener */
    $("#checkButtonField").on('click', function () {
        $('#modal1').modal('open');

        var numbersArea = $("#numbersArea").val();
        arrayOfNumbers = numbersArea.match(/\d+/g);
        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);
        addNumbersToConfirm(arrayOfNumbers);

    });

    /* Sequence listener */
    $("#checkButtonSequence").on('click', function () {
        $('#modal1').modal('open');

        var startNumber = parseInt($("#startSequence").val());
        var endNumber = parseInt($("#endSequence").val());

        for (var i = startNumber; i <= endNumber; i++) {
            arrayOfNumbers.push(i);
        }

        arrayOfNumbers = filterArrayOfNumbers(arrayOfNumbers);
        addNumbersToConfirm(arrayOfNumbers);
    });

    /* Betting */
    $("#playButton").on('click', function () {
        bet(arrayOfNumbers);
    });
});


/* Web sockets */
var conn = new ab.Session('ws://localhost:8080',
    function () {
        conn.subscribe('all', function (topic, data) {

            var jackpotNumber = document.getElementById('jackpotNumber');

            if (data.reload === 0) {
                jackpotNumber.innerHTML = data.jackpot;
                console.log('Jackpot updated: ' + data.jackpot);
            }


            if (data.reload === 1)
            {
                $("#jackpotNumber").html(data.jackpot);
                $("#gameNumberLast").html(data.last_game_number);
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
                    gameHistoryTable.append('<tr><td>' + value['game_id'] + '</td><td>' +
                        value['amount'] + ' bits</td><td><div class="chip">' + value['winner_number'] + '</div></td><td>' +
                        value['timedate'] + '</td></tr>');

                });

                updateBalanceAndNumbers();
            }
                console.log(data);
        });
    },
    function () {
        console.warn('WebSocket connection closed');
    },
    {'skipSubprotocolCheck': true}
);

/* Ajax request to update numbers and balance */
function updateBalanceAndNumbers() {
    $.ajax({url: "balance_numbers_ajax.php", success: function(result){
        var response = JSON.parse(result);
        $("#balanceNumber").html(response['balance']);
        var numbersList = $("#numbersList");
        numbersList.empty();

        $.each(response['numbers'], function (index, value) {

            numbersList.append('<div class="chip">' + value + '</div>');
        });
    }, type: 'GET'});
}

/*
* Bet for Text Area
* */
function bet(arrayOfNumbers) {

    var jsonSend = JSON.stringify(arrayOfNumbers);
    $.ajax({url: "play.php", success: function (result) {
        var response = JSON.parse(result);
        $("#balanceNumber").html(response['balance']);
        var numbersList = $("#numbersList");
        numbersList.empty();

        if (response['count'] > 1)
            $("#count").html("&nbsp;&nbsp;&nbsp;&nbsp;" + response['count'] + " numbers");
        else
            $("#count").html("&nbsp;&nbsp;&nbsp;&nbsp;" + response['count'] + " number");

        $.each(response['numbers'], function (index, value) {

            numbersList.append('<div class="chip">' + value + '</div>');
        });
    }, data: {
        numbers: jsonSend
    }
     , type: 'POST'});

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