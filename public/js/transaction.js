

$(document).ready(function() {
  $('.deleteTransaction').on('click',deleteTransaction);

   $('.toggle-icon').on('click', function() {
    $('.toggle-icon').toggleClass('icon-plus-sign icon-minus-sign');
    $('#toggle-value').val($('#toggle-value').val() === 'min' ? 'plus' : 'min');

    // change some texts:
    if($('#toggle-value').val() === 'min') {
      $('label[for="inputAccount"]').text('From account');
      $('label[for="inputBudget"]').text('From budget');
      $('label[for="inputBeneficiary"]').text('Beneficiary');
      $('input[name="beneficiary"]').attr('placeholder','Beneficiary');
    } else {
      $('label[for="inputAccount"]').text('Into account');
      $('label[for="inputBudget"]').text('Into budget');
      $('label[for="inputBeneficiary"]').text('Payee');
      $('input[name="beneficiary"]').attr('placeholder','Payee');

    }

  });

});


function deleteTransaction(ev) {
  var target = $(ev.target);
  if(target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();

  }
  $('#delTransactionName').text($('td:nth-child(2) a',row).text())

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action','/home/transaction/delete/' + ID);
  $('#modal').modal();


}
