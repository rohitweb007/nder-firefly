google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(drawChartBudget);

function drawChartBudget() {
    // do async data grab for all graphs:
    $.getJSON('/home/budget/overviewGraph/' + ID, function(data) {
      var chart = new google.visualization.AreaChart(document.getElementById('budgetGraph'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }
      chart.draw(gdata, {lineWidth: 1,height:300,

        chartArea: {left: 40, width: '100%'}, legend: {position: 'none'}});
    });
}