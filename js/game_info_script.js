function showList(number) {
    updateModal(number);
    $("#numberSelected").html(number);
    $('#modal1').modal('open');
}

/* Update modal AJAX */
function updateModal(number) {
  $.ajax('players_number_ajax.php', {success: function (result) {
        console.log(result);
        var response = JSON.parse(result);

        var list = $("#playersList");
        list.empty();

        $.each(response, function (index, value) {
            list.append('<li>' + value + '</li>')
        });
      },
  data: {
      game: currentGame,
      number: number
  }, type: 'POST'});
}