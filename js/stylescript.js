$(document).ready(function(){

    $('.modal').modal();
});

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

