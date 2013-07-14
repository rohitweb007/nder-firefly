google.load('visualization', '1.0', {'packages': ['controls', 'corechart', 'table']});
google.setOnLoadCallback(drawCharts);

var beneficiaryDashboard;
var beneficiaryControl;
var beneficiaryChart;
var start = new Date();
var end = new Date();
end.setMonth(end.getMonth() - 1);


$(document).ready(function() {
  $('.deleteBeneficiary').on('click', deleteBeneficiary);
});


function deleteBeneficiary(ev) {
  var target = $(ev.target);
  if (target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();

  }
  $('#delBeneficiaryName').text($('td:nth-child(1) a', row).text())

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action', '/home/beneficiary/delete/' + ID);
  $('#modal').modal();
}


function drawCharts() {
  if ($('#beneficiaryDashboard').length > 0) {
    drawBeneficiary();
//    getSummary();
    updateHeader();
//    drawBudget();
//    drawCategory();
//    drawMoves();
//    drawTransactions();
//    drawBeneficiaries();
  }
}

function updateHeader() {
  var state = beneficiaryControl.getState();
  var months = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
  var start = months[state.range.start.getMonth()] + ' ' + state.range.start.getDate() + ', ' + state.range.start.getFullYear();
  var end = months[state.range.end.getMonth()] + ' ' + state.range.end.getDate() + ', ' + state.range.end.getFullYear();

  $('#date').text('(between ' + start + ' and ' + end + ')');
}

function drawBeneficiary() {
  beneficiaryDashboard = new google.visualization.Dashboard(document.getElementById('beneficiaryDashboard'));
  beneficiaryControl = new google.visualization.ControlWrapper({
    'controlType': 'ChartRangeFilter',
    'containerId': 'control',
    'options': {
      // Filter by the date axis.
      'filterColumnIndex': 0,
      'ui': {
        'chartType': 'LineChart',
        'chartOptions': {
          'chartArea': {'width': '90%', height: 75},
          'hAxis': {'baselineColor': 'none'}
        },
        // Display a single series that shows the closing value of the stock.
        // Thus, this view has two columns: the date (axis) and the stock value (line series).
        'chartView': {
          'columns': [0, 1]
        },
        // 1 day in milliseconds = 24 * 60 * 60 * 1000 = 86,400,000
        'minRangeSize': 86400000
      }
    },
    // Initial range: 2012-02-09 to 2012-03-20.
    'state': {'range': {'start': end, 'end': start}}
  });

  beneficiaryChart = new google.visualization.ChartWrapper({
    'chartType': 'ColumnChart',
    'containerId': 'chart',
    'options': {
      // Use the same chart area width as the control for axis alignment.
      'chartArea': {'height': '80%', 'width': '90%'},
      'hAxis': {'slantedText': false},
      'legend': {'position': 'none'}

    }
  });

  var jsondata = $.ajax({url: "/home/beneficiary/chart/" + ID, dataType: "json", async: false}).responseText;
  var data = new google.visualization.DataTable(jsondata);
//  google.visualization.events.addListener(accountControl, 'statechange', drawBudget);
//  google.visualization.events.addListener(accountControl, 'statechange', drawCategory);
//  google.visualization.events.addListener(accountControl, 'statechange', drawMoves);
  google.visualization.events.addListener(beneficiaryControl, 'statechange', updateHeader);
//  google.visualization.events.addListener(accountControl, 'statechange', drawTransactions);
//  google.visualization.events.addListener(accountControl, 'statechange', drawBeneficiaries);
//  google.visualization.events.addListener(accountControl, 'statechange', getSummary);
  var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
  for (i = 1; i < data.getNumberOfColumns(); i++) {
    money.format(data, i);
  }


  beneficiaryDashboard.bind(beneficiaryControl, beneficiaryChart);
  beneficiaryDashboard.draw(data);

}