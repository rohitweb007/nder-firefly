google.load('visualization', '1.0', {'packages': ['controls', 'corechart', 'table']});
google.setOnLoadCallback(drawCharts);

var accountDashboard;
var accountControl;
var accountChart;
var start = new Date();
var end = new Date();
end.setMonth(end.getMonth() - 1);
var charts = new Array();

var pieChartOpt = {
  legend: {position: 'none'},
  animation: {
    duration: 1000,
    easing: 'out',
  }
};


function drawCharts() {
  if ($('#accountDashboard').length > 0) {
    drawAccount();
  }
  if ($('#allChart').length > 0) {
    drawAllChart();

  }
}


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
  if ($('td:nth-child(1) a', row).text().length > 0) {
    $('#delAccountName').text($('td:nth-child(1) a', row).text())
  } else {
    $('#delAccountName').text(Name);
  }

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action', '/home/account/delete/' + ID);
  $('#modal').modal();
}


function drawAllChart() {
  var opt = {
    areaOpacity: 0.1,
    legend: {position: 'bottom'},
    lineWidth: 1

  };

  $.getJSON('/home/accounts/chart', function(data) {
    var chart = new google.visualization.AreaChart(document.getElementById('allChart'));
    var gdata = new google.visualization.DataTable(data);
    var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
    for (i = 1; i < gdata.getNumberOfColumns(); i++) {
      money.format(gdata, i);
    }
    chart.draw(gdata, opt);
  }).fail(function() {
    $('#allChart').removeClass('loading').addClass('load_error');
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

  var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
  for (i = 1; i < data.getNumberOfColumns(); i++) {
    money.format(data, i);
  }


  accountDashboard.bind(accountControl, accountChart);
  accountDashboard.draw(data);

  google.visualization.events.addListener(accountControl, 'statechange', updateHeader);
  google.visualization.events.addListener(accountControl, 'statechange', drawPieCharts);

  updateHeader();
  drawPieCharts();
}


function updateHeader() {
  var state = accountControl.getState();
  var months = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
  // fix start date.
  startdate = state.range.start;
  startdate.setDate(startdate.getDate()+1);
  var start = months[startdate.getMonth()] + ' ' + (startdate.getDate()) + ', ' + startdate.getFullYear();
  var end = months[state.range.end.getMonth()] + ' ' + state.range.end.getDate() + ', ' + state.range.end.getFullYear();
  $('#date').text('(between ' + start + ' and ' + end + ')');
}

function drawPie(chart, type) {
  url = '/home/account/pie/';
  var state = accountControl.getState();

  // draw it!
  $.getJSON(url, {
    id: ID,
    type: type,
    object: chart,
    start: state.range.start.toDateString(),
    end: state.range.end.toDateString()
  }, function(data) {
    var key = type + chart;
    if(!charts[key]) {
      console.log('new chart!');
      charts[key] = new google.visualization.PieChart(document.getElementById(chart + type));
    }
    var gdata = new google.visualization.DataTable(data);
    var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
    for (i = 1; i < gdata.getNumberOfColumns(); i++) {
      money.format(gdata, i);
    }
    charts[key].draw(gdata, pieChartOpt);

  }).fail(function() {
    $('#' + chart + type).removeClass('loading').addClass('load_error');
  });


}

function drawPieCharts(opt) {

  if (opt === undefined || (opt != undefined && opt.inProgress === false)) {

    drawPie('budgets', 'income');
    drawPie('budgets', 'expenses');

    drawPie('beneficiaries', 'income');
    drawPie('beneficiaries', 'expenses');

    drawPie('categories', 'income');
    drawPie('categories', 'expenses');
  }

}