

$(document).ready(function() {
  $('.deleteTransfer').on('click',deleteTransfer);
});


function deleteTransfer(ev) {
  var target = $(ev.target);
  if(target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();

  }
  $('#delTransferName').text($('td:nth-child(2) a',row).text())

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action','/home/transfer/delete/' + ID);
  $('#modal').modal();


}
