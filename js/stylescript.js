/* Modal initializer*/
$(document).ready(function(){

    $('.modal').modal();
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

/* Text Area Input validator */
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

/* Sequence Input validator listeners */
$(function () {

    var startSequence = $("#startSequence");
    var endSequence = $("#endSequence");

    startSequence.on('keyup', function() { validateSequence() });
    endSequence.on('keyup', function() { validateSequence() });

    startSequence.on('blur', function() { validateSequence() });
    endSequence.on('blur', function() { validateSequence() });
});

