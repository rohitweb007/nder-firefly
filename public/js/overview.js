google.load('visualization', '1.0', {'packages': ['controls', 'corechart', 'table']});
google.setOnLoadCallback(drawObject);

var dashboard;
var control;
var chart;
var charts = new Array();
var gdatas = new Array();
var pieChartOpt = {
  legend: {position: 'none'},
  animation: {
    duration: 1000,
    easing: 'out',
  }
};
var transactionsOpt = {allowHtml: true};

if (!strend) {
  var end = new Date();
} else {
  var end = new Date(strend);
}

if (!strstart) {
  var start = new Date();
  start.setMonth(end.getMonth() - 1);
} else {
  var start = new Date(strstart);
}







$(document).ready(function() {
  $('.deleteObject').on('click', deleteObject);
});

function deleteObject(ev) {
  var target = $(ev.target);
  if (target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();

  }
  if ($('td:nth-child(1) a', row).text().length > 0) {
    $('#delObjectName').text($('td:nth-child(1) a', row).text())
  } else {
    $('#delObjectName').text(Name);
  }

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action', '/home/' + object + '/delete/' + ID);
  $('#modal').modal();
}

function drawObject() {
  dashboard = new google.visualization.Dashboard(document.getElementById('dashboard'));
  // corrective measure.
  start.setDate(start.getDate() - 1);
  control = new google.visualization.ControlWrapper({
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
          'columns': [0, 1, 2]
        },
        // 1 day in milliseconds = 24 * 60 * 60 * 1000 = 86,400,000
        'minRangeSize': 86400000
      }
    },
    // Initial range: 2012-02-09 to 2012-03-20.

    'state': {'range': {'start': start, 'end': end}}
  });

  chart = new google.visualization.ChartWrapper({
    'chartType': 'LineChart',
    'containerId': 'chart',
    'options': {
      // Use the same chart area width as the control for axis alignment.
      'chartArea': {'height': '80%', 'width': '90%'},
      'hAxis': {'slantedText': false},
      'legend': {'position': 'none'}

    }
  });

  var jsondata = $.ajax({url: "/home/" + object + "/chart/" + ID, dataType: "json", async: false}).responseText;
  var data = new google.visualization.DataTable(jsondata);

  var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
  for (i = 1; i < data.getNumberOfColumns(); i++) {
    money.format(data, i);
  }


  dashboard.bind(control, chart);
  dashboard.draw(data);

  google.visualization.events.addListener(control, 'statechange', updateHeader);
  google.visualization.events.addListener(control, 'statechange', drawPieCharts);

  updateHeader();
  drawPieCharts();
}


function updateHeader() {
  var state = control.getState();
  var months = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
  // fix start date.
  startdate = state.range.start;
  startdate.setDate(startdate.getDate() + 1);
  var start = months[startdate.getMonth()] + ' ' + (startdate.getDate()) + ', ' + startdate.getFullYear();
  var end = months[state.range.end.getMonth()] + ' ' + state.range.end.getDate() + ', ' + state.range.end.getFullYear();
  $('#date').text('(between ' + start + ' and ' + end + ')');
}

function drawPie(chart, type) {
  url = '/home/' + object + '/pie/';
  var state = control.getState();
  if ($('#' + chart + type).length > 0) {
    // draw it!
    $.getJSON(url, {
      id: ID,
      type: type,
      chart: chart,
      start: state.range.start.toDateString(),
      end: state.range.end.toDateString()
    }, function(data) {
      var key = type + chart;
      if (!charts[key]) {
        charts[key] = new google.visualization.PieChart(document.getElementById(chart + type));
        google.visualization.events.addListener(charts[key], 'select', function() {
          respondPieClick(key);
        });
      }
      gdatas[key] = new google.visualization.DataTable(data);
      if (gdatas[key].getNumberOfRows() > 0) {
        $('#' + chart + type).prev().show();
        $('#' + chart + type).show();
        var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
        for (i = 1; i < gdatas[key].getNumberOfColumns(); i++) {
          money.format(gdatas[key], i);
        }
        charts[key].draw(gdatas[key], pieChartOpt);
      } else {
        $('#' + chart + type).prev().hide();
        $('#' + chart + type).hide();
      }

    }).fail(function() {
      $('#' + chart + type).removeClass('loading').addClass('load_error');
    });
  }


}

function drawPieCharts(opt) {

  if (opt === undefined || (opt != undefined && opt.inProgress === false)) {

    drawPie('accounts', 'income');
    drawPie('accounts', 'expenses');

    drawPie('budgets', 'income');
    drawPie('budgets', 'expenses');

    drawPie('beneficiaries', 'income');
    drawPie('beneficiaries', 'expenses');

    drawPie('categories', 'income');
    drawPie('categories', 'expenses');
    drawTransactions(null);
  }
}

function drawTransactions(filter) {
  // get the date rage from the control chart
  var state = control.getState();
  var url = '/home/' + object + '/transactions/';
  var key = 'transactions';
  // do some kind of switch on the chart if it is not NULL
  var childType, modifier, childValue;
  if (filter) {
    childType = filter.childType;
    modifier = filter.modifier;
    childValue = filter.childValue;

  }


  $.getJSON(url, {
    id: ID,
    start: state.range.start.toDateString(),
    end: state.range.end.toDateString(),
    childType: childType,
    modifier: modifier,
    childValue: childValue
  }, function(data) {
    var gdata = new google.visualization.DataTable(data);

    var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
    money.format(gdata, 2);

    if (!charts[key]) {
      charts[key] = new google.visualization.Table(document.getElementById(key));
    }
    charts[key].draw(gdata, transactionsOpt);


  }).fail(function() {
    $('#transactions').empty().removeClass('loading').addClass('load_error');
  });
}

/**
 * So we only select the types of children found in the pie,
 * and a modifier (more or leess than zero).

 * @param {type} key
 * @returns {undefined} */
function respondPieClick(key) {
  var chart = charts[key] ? charts[key] : null;
  var gdata = gdatas[key] ? gdatas[key] : null;
  if (chart && gdata) {
    var selection = chart.getSelection();
    if (selection.length > 0) {
      var rowNumber = selection[0].row;
      if (key.indexOf('income') > -1) {
        var modifier = 'income';
      } else {
        var modifier = 'expenses';
      }
      var childType = key.replace(modifier, '');
      var childValue = gdata.getValue(rowNumber, 0);
      // get the row from the chart
      //var row = ;
      var filter = {childType: childType, modifier: modifier, childValue: childValue};
      drawTransactions(filter);


    } else {
      drawTransactions(null);
    }
  }
}