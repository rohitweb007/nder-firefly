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
  $('#tabs').tab();

  $('a[href="#transactions"]').on('show', function(e) {
    drawTransactions();
  });
  $('a[href="#budgets"]').on('show', function(e) {
    drawBudget();
  });
  $('a[href="#transactions"]').on('show', function(e) {
    drawTransactions();
  });
  $('a[href="#categories"]').on('show', function(e) {
    drawCategory();
  });

});


function deleteBeneficiary(ev) {
  var target = $(ev.target);
  if (target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();
  }
  if ($('td:nth-child(1) a', row).text().length > 0) {
    $('#delBeneficiaryName').text($('td:nth-child(1) a', row).text())
  } else {
    $('#delBeneficiaryName').text(Name);
  }

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action', '/home/beneficiary/delete/' + ID);
  $('#modal').modal();
}


function drawCharts() {
  if ($('#beneficiaryDashboard').length > 0) {
    drawBeneficiary();
    getSummary();
    updateHeader();
    drawBudget();
    drawCategory();
    drawTransactions();
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
  google.visualization.events.addListener(beneficiaryControl, 'statechange', updateHeader);
  google.visualization.events.addListener(beneficiaryControl, 'statechange', getSummary);
  google.visualization.events.addListener(beneficiaryControl, 'statechange', drawTransactions);
  google.visualization.events.addListener(beneficiaryControl, 'statechange', drawBudget);
  google.visualization.events.addListener(beneficiaryControl, 'statechange', drawCategory);

  var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
  for (i = 1; i < data.getNumberOfColumns(); i++) {
    money.format(data, i);
  }


  beneficiaryDashboard.bind(beneficiaryControl, beneficiaryChart);
  beneficiaryDashboard.draw(data);

}

function getSummary(opt) {
  var state = beneficiaryControl.getState();
  $.getJSON('/home/beneficiary/summary/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
    $('#summaryText').html(data);
  });
}

var workingTransactions = false;
function drawTransactions(opt) {

  if (workingTransactions === false || (workingTransactions === false && opt && opt.inProgress === false)) {
    workingTransactions = true;

    var state = beneficiaryControl.getState();

    $.getJSON('/home/beneficiary/transactions/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
      var chart = new google.visualization.Table(document.getElementById('transactionsTable'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      money.format(gdata, 2);

      chart.draw(gdata, {width: '100%'});
      workingTransactions = false;
    });
  }

}
var workingBudget = false;
function drawBudget(opt) {

  if (workingBudget === false || (workingBudget === false && opt && opt.inProgress === false)) {
    workingBudget = true;

    var state = beneficiaryControl.getState();

    $.getJSON('/home/beneficiary/budgets/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
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


      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);

      }
      chart.draw(gdata, {sortAscending: false, allowHtml: true, sortColumn: 1, width: 400});
      workingBudget = false;
    });
  }
}

var workingCategories = false;
function drawCategory(opt) {

  if (workingCategories === false || (workingCategories === false && opt && opt.inProgress === false)) {
    workingCategories = true;

    var state = beneficiaryControl.getState();

    $.getJSON('/home/beneficiary/categories/' + ID, {start: state.range.start.toDateString(), end: state.range.end.toDateString()}, function(data) {
      var chart = new google.visualization.Table(document.getElementById('categoryTable'));
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
      chart.draw(gdata, {sortAscending: false, allowHtml: true, sortColumn: 1, width: 400});
      workingCategories = false;
    });
  }
}