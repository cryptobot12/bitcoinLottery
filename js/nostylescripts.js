/**
 * Created by Frank on 10/19/2017.
 */
window.onload = function () {
    timer();
};

$(function () {
    $("#playButtonField").on('click', function () {
        betField();
    });
});

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

function betField() {

    var arrayOfNumbers;

    var numbersArea = $("#numbersArea").val();
    arrayOfNumbers = numbersArea.match(/\d+/g);
    arrayOfNumbers = arrayOfNumbers.slice(0, 200); //Just in case


    var jsonSend = JSON.stringify(arrayOfNumbers);
    $.ajax({url: "play.php", success: function (result) {
        var response = JSON.parse(result);
        $("#balanceNumber").html(response['balance']);
        var numbersList = $("#numbersList");
        numbersList.empty();

        $.each(response['numbers'], function (index, value) {

            numbersList.append('<div class="chip">' + value + '</div>');
        });

        $.each(response['inserted'], function (index, value) {
            console.log(value);

        })
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