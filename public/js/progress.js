google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(drawProgressCharts);

var budgetOpt = {
  lineWidth: 1,
  legend: {
    position: 'none'
  },
  colors: ['#00a', '#aaa']
};

function drawProgressCharts() {
  $.each($('.budgetProgressChart'), function(i, v) {
    var box = $(v);
    var budget = box.attr('data-value');

    // get JSON for graph:
    if (cached[budget]) {
      gdata = new google.visualization.DataTable(cached[budget]);
      drawProgressChart(box, gdata);
    } else {
      $.getJSON('/home/chart/progress/budget', {budget: budget}, function(data) {
        // when data is here, create graph object:

        var gdata = new google.visualization.DataTable(data);
        drawProgressChart(box, gdata);
      }).fail(function() {
        box.removeClass('loading').addClass('load_error');
      });
    }

  });
}

function drawProgressChart(box, gdata) {
  var chart = new google.visualization.LineChart(document.getElementById(box.attr('id')));
  var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
  for (i = 1; i < gdata.getNumberOfColumns(); i++) {
    money.format(gdata, i);
  }
  // draw it:
  chart.draw(gdata, budgetOpt);
}