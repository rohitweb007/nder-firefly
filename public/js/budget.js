

$(document).ready(function() {
  $('.deleteBudget').on('click',deleteBudget);
});


function deleteBudget(ev) {
  var target = $(ev.target);
  if(target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();

  }
  $('#delBudgetName').text($('td:nth-child(1) a',row).text())

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action','/home/budget/delete/' + ID);
  $('#modal').modal();
}
