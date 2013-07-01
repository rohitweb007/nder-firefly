google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(drawChartBudget);

function drawChartBudget() {
  if ($('#budgetGraph').length == 1) {
    // do async data grab for all graphs:
    $.getJSON('/home/budget/overviewGraph/' + ID, function(data) {
      var chart = new google.visualization.AreaChart(document.getElementById('budgetGraph'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }
      chart.draw(gdata, {lineWidth: 1, height: 300,
        chartArea: {left: 40, width: '100%'}, legend: {position: 'none'}});
    });
  }
}

$(document).ready(function() {
  $('.deleteCategory').on('click',deleteCategory);
});


function deleteCategory(ev) {
  var target = $(ev.target);
  if(target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();

  }
  $('#delCategoryName').text($('td:nth-child(1) a',row).text())

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action','/home/category/delete/' + ID);
  $('#modal').modal();


}