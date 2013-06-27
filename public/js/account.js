google.load('visualization', '1.0', {'packages': ['controls', 'table']});
google.setOnLoadCallback(drawDashboard);

var accountDashboard;
var accountControl;
var accountChart;
var budgetDashboard;
var start = new Date();
var end = new Date();
end.setMonth(end.getMonth() - 1);

function drawDashboard() {
  drawAccount();
  updateHeader();
  drawBudget();
  drawCategory();
  drawMoves();
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

  accountDashboard.bind(accountControl, accountChart);
  accountDashboard.draw(data);


}

function updateHeader() {
  var state = accountControl.getState();

  $('#date').text(state.range.start.toDateString() + ' / ' + state.range.end.toDateString());
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
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }
      chart.draw(gdata, {sortAscending: false, sortColumn: 1});
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
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }

      chart.draw(gdata, {sortAscending: false, sortColumn: 1});
      workingCategory = false;
    });
  }
}

var workingMoves = false;
function drawMoves(opt) {

  if (workingMoves === false || (workingMoves === false && opt && opt.inProgress === false)) {
    workingMoves = true;

    var state = accountControl.getState();

    $.getJSON('/home/chart/tba/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
      var chart = new google.visualization.Table(document.getElementById('moveTable'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }

      chart.draw(gdata, {sortAscending: false, sortColumn: 1});
      workingMoves = false;
    });
  }
}