google.load('visualization', '1.0', {'packages': ['controls', 'corechart', 'table']});
google.setOnLoadCallback(drawCharts);

var accountDashboard;
var accountControl;
var accountChart;
var budgetDashboard;
var start = new Date();
var end = new Date();
end.setMonth(end.getMonth() - 1);


$(document).ready(function() {
  $('.deleteAccount').on('click', deleteAccount);
  $('#tabs').tab();
});

function deleteAccount(ev) {
  var target = $(ev.target);
  if (target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();

  }
  if($('td:nth-child(1) a', row).text().length > 0) {
  $('#delAccountName').text($('td:nth-child(1) a', row).text())
  } else {
    $('#delAccountName').text(Name);
  }

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action', '/home/account/delete/' + ID);
  $('#modal').modal();
}


function drawCharts() {
  if ($('#accountDashboard').length > 0) {
    drawAccount();
    getSummary();
    updateHeader();
    drawBudget();
    drawCategory();
    drawMoves();
    drawTransactions();
    drawBeneficiaries();

  }
  if ($('#allChart').length > 0) {
    drawAllChart();
  }
}

function drawAllChart() {
  $.getJSON('/home/accounts/chart', function(data) {
    var chart = new google.visualization.AreaChart(document.getElementById('allChart'));
    var gdata = new google.visualization.DataTable(data);
    var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
    for (i = 1; i < gdata.getNumberOfColumns(); i++) {
      money.format(gdata, i);
    }
    chart.draw(gdata);
  });
}


function drawAccount() {
  accountDashboard = new google.visualization.Dashboard(document.getElementById('accountDashboard'));
  accountControl = new google.visualization.ControlWrapper({
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

  accountChart = new google.visualization.ChartWrapper({
    'chartType': 'LineChart',
    'containerId': 'chart',
    'options': {
      // Use the same chart area width as the control for axis alignment.
      'chartArea': {'height': '80%', 'width': '90%'},
      'hAxis': {'slantedText': false},
      'legend': {'position': 'none'}

    }
  });

  var jsondata = $.ajax({url: "/home/account/chart/" + ID, dataType: "json", async: false}).responseText;
  var data = new google.visualization.DataTable(jsondata);
  google.visualization.events.addListener(accountControl, 'statechange', drawBudget);
  google.visualization.events.addListener(accountControl, 'statechange', drawCategory);
  google.visualization.events.addListener(accountControl, 'statechange', drawMoves);
  google.visualization.events.addListener(accountControl, 'statechange', updateHeader);
  google.visualization.events.addListener(accountControl, 'statechange', drawTransactions);
  google.visualization.events.addListener(accountControl, 'statechange', drawBeneficiaries);
  google.visualization.events.addListener(accountControl, 'statechange', getSummary);
  var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
  for (i = 1; i < data.getNumberOfColumns(); i++) {
    money.format(data, i);
  }


  accountDashboard.bind(accountControl, accountChart);
  accountDashboard.draw(data);


}

function updateHeader() {
  var state = accountControl.getState();
  var months = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
  var start = months[state.range.start.getMonth()] + ' ' + state.range.start.getDate() + ', ' + state.range.start.getFullYear();
  var end = months[state.range.end.getMonth()] + ' ' + state.range.end.getDate() + ', ' + state.range.end.getFullYear();

  $('#date').text('(between ' + start + ' and ' + end + ')');
}

var workingBudget = false;
function drawBudget(opt) {

  if (workingBudget === false || (workingBudget === false && opt && opt.inProgress === false)) {
    workingBudget = true;

    var state = accountControl.getState();

    $.getJSON('/home/chart/bba/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
      var chart = new google.visualization.Table(document.getElementById('budgetTable'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});

      var colours_spent = new google.visualization.ColorFormat();
      colours_spent.addRange(0.01, null, "#b94a48");
      colours_spent.addRange(0, 0.009, "#ddd");
      colours_spent.format(gdata, 1);

      var colours_earned = new google.visualization.ColorFormat();
      colours_earned.addRange(0.01, null, "#468847");
      colours_earned.addRange(0, 0.009, "#ddd");
      colours_earned.format(gdata, 2);

      var colours_moved = new google.visualization.ColorFormat();
      colours_moved.addRange(0.01, null, "#c09853");
      colours_moved.addRange(null, 0, "#c09853");
      colours_moved.addRange(0, 0.009, "#ddd");
      colours_moved.format(gdata, 3);


      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);

      }
      chart.draw(gdata, {sortAscending: false, allowHtml: true, sortColumn: 1, width: 400});
      workingBudget = false;
    });
  }
}

var workingCategory = false;
function drawCategory(opt) {

  if (workingCategory === false || (workingCategory === false && opt && opt.inProgress === false)) {
    workingCategory = true;

    var state = accountControl.getState();

    $.getJSON('/home/chart/cba/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
      var chart = new google.visualization.Table(document.getElementById('categoryTable'));
      var gdata = new google.visualization.DataTable(data);


      var colours_spent = new google.visualization.ColorFormat();
      colours_spent.addRange(0.01, null, "#b94a48");
      colours_spent.addRange(0, 0.009, "#ddd");
      colours_spent.format(gdata, 1);

      var colours_earned = new google.visualization.ColorFormat();
      colours_earned.addRange(0.01, null, "#468847");
      colours_earned.addRange(0, 0.009, "#ddd");
      colours_earned.format(gdata, 2);

      var colours_moved = new google.visualization.ColorFormat();
      colours_moved.addRange(0.01, null, "#c09853");
      colours_moved.addRange(null, 0, "#c09853");
      colours_moved.addRange(0, 0.009, "#ddd");
      colours_moved.format(gdata, 3);

      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }

      chart.draw(gdata, {sortAscending: false, sortColumn: 1, allowHtml: true, width: 400});
      workingCategory = false;
    });
  }
}

var workingMoves = false;
function drawMoves(opt) {

  if (workingMoves === false || (workingMoves === false && opt && opt.inProgress === false)) {
    workingMoves = true;

    var state = accountControl.getState();

    $.getJSON('/home/chart/mba/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
      var chart = new google.visualization.Table(document.getElementById('moveTable'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }

      chart.draw(gdata, {sortAscending: false, sortColumn: 1, allowHtml: true, width: 400});
      workingMoves = false;
    });
  }
}

var workingTransactions = false;
function drawTransactions(opt) {

  if (workingTransactions === false || (workingTransactions === false && opt && opt.inProgress === false)) {
    workingTransactions = true;

    var state = accountControl.getState();

    $.getJSON('/home/chart/transba/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
      var chart = new google.visualization.Table(document.getElementById('transactionsTable'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      money.format(gdata, 2);

      chart.draw(gdata);
      workingTransactions = false;
    });
  }
}

var workingBeneficiaries = false;
function drawBeneficiaries(opt) {

  if (workingBeneficiaries === false || (workingBeneficiaries === false && opt && opt.inProgress === false)) {
    workingBeneficiaries = true;

    var state = accountControl.getState();

    $.getJSON('/home/chart/benba/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
      var chart = new google.visualization.Table(document.getElementById('beneficiaryTable'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});


      var colours_spent = new google.visualization.ColorFormat();
      colours_spent.addRange(0.01, null, "#b94a48");
      colours_spent.addRange(0, 0.009, "#ddd");
      colours_spent.format(gdata, 1);

      var colours_earned = new google.visualization.ColorFormat();
      colours_earned.addRange(0.01, null, "#468847");
      colours_earned.addRange(0, 0.009, "#ddd");
      colours_earned.format(gdata, 2);

      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }

      chart.draw(gdata, {sortAscending: false, sortColumn: 1, allowHtml: true, width: 400});
      workingBeneficiaries = false;
    });
  }

}

function getSummary(opt) {
  var state = accountControl.getState();
  $.getJSON('/home/account/summary/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
    $('#summaryText').html(data);
  });
}