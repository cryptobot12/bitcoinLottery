$(function () {
    $('input:radio[name="inputType"]').change(
        function () {
            if (this.value === '1') {
                //Disabling
                $("#start").prop("disabled", true);
                $("#end").prop("disabled", true);
                $("#numberOfNumbers").prop("disabled", true);

                $("#startSequence").prop("disabled", true);
                $("#endSequence").prop("disabled", true);

                //Enabling
                $("#numbersArea").prop("disabled", false);
            }
            else if(this.value === '2') {
                //Disabling
                $("#numbersArea").prop("disabled", true);

                $("#startSequence").prop("disabled", true);
                $("#endSequence").prop("disabled", true);

                //Enabling
                $("#start").prop("disabled", false);
                $("#end").prop("disabled", false);
                $("#numberOfNumbers").prop("disabled", false);
            }
            else if(this.value === '3') {
                //Disabling
                $("#numbersArea").prop("disabled", true);

                $("#start").prop("disabled", true);
                $("#end").prop("disabled", true);
                $("#numberOfNumbers").prop("disabled", true);

                //Enabling
                $("#startSequence").prop("disabled", false);
                $("#endSequence").prop("disabled", false);
            }
        }
    );
});
