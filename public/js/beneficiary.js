$(document).ready(function() {
  $('.deleteBeneficiary').on('click',deleteBeneficiary);
});


function deleteBeneficiary(ev) {
  var target = $(ev.target);
  if(target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();

  }
  $('#delBeneficiaryName').text($('td:nth-child(1) a',row).text())

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action','/home/beneficiary/delete/' + ID);
  $('#modal').modal();


}