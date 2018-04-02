$(window).resize(function () {
    var modal =  $("#modal1");
    if ($(window).width() > 992)
        modal.css('width', 500);
    else
        modal.css('width', '80%');
});

$(document).ready(function(){
    $('.modal').modal();
});

function showList(number) {
    var modal =  $("#modal1");
    updateModal(number);
    $("#numberSelected").html("<b>" + number + "</b>");
    if ($(window).width() > 992)
        modal.css('width', 500);
    else
        modal.css('width', '80%');
    modal.modal('open');
}

/* Update modal AJAX */
function updateModal(number) {
  $.ajax(base_dir + 'ajax/players-number-ajax', {success: function (result) {
        var response = JSON.parse(result);

        var list = $("#playersList");
        list.empty();

        $.each(response, function (index, value) {
            list.append('<div class="chip">' + value + '</div>')
        });
      },
  data: {
      game: game_id,
      number: number
  }, type: 'GET'});
}