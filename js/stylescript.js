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
