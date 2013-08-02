google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(drawChart);

$(document).ready(function() {
});

function drawChart() {
  $.each($('.accountOverviewGraph'), function(i, v) {
    var graphHolder = $(v);
    var ID = graphHolder.attr('data-value');

    var opt = {
      vAxis: {textPosition: 'none'},
      lineWidth: 1,
      legend: {position: 'none'},
      hAxis: {textPosition: 'none', gridlines: {count: 2}},
      height: 90,
      chartArea: {left: 40, width: '100%'}};

    // do async data grab for all graphs:
    $.getJSON('/home/account/overviewGraph/' + ID, function(data) {
      var chart = new google.visualization.AreaChart(document.getElementById('accountOverviewGraph' + ID));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }
      chart.draw(gdata, opt);
    }).fail(function() {
      $('#accountOverviewGraph' + ID).removeClass('loading').addClass('load_error');
    });
  });

  $.each($('.budgetOverviewGraph'), function(i, v) {
    var graphHolder = $(v);
    var ID = graphHolder.attr('data-value');
    $.getJSON('/home/budget/overviewGraph/' + ID, function(data) {
      var chart = new google.visualization.AreaChart(document.getElementById('budgetOverviewGraph' + ID));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }
      chart.draw(gdata, {vAxis: {textPosition: 'none'}, lineWidth: 1, legend: {position: 'none'}, hAxis: {textPosition: 'none', gridlines: {count: 2}}, height: 90, chartArea: {left: 40, width: '100%'}});
    }).fail(function() {
      $('#budgetOverviewGraph' + ID).removeClass('loading').addClass('load_error');
    });

  });

  $.each($('.targetOverviewGraph'), function(i, v) {
    var graphHolder = $(v);
    var ID = graphHolder.attr('data-value');
    $.getJSON('/home/target/overviewGraph/' + ID, function(data) {
      var chart = new google.visualization.AreaChart(document.getElementById('targetOverviewGraph' + ID));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }
      var opt = {
        vAxis: {textPosition: 'none'},
        lineWidth: 1,
        height: 180,
        hAxis: {
          textPosition: 'none',
          gridlines: {
            count: 2
          }
        }, legend: {position: 'none'}, chartArea: {left: 40, width: '100%'}, colors: ['blue', 'green']}

      chart.draw(gdata, opt);
    }).fail(function() {
      $('#targetOverviewGraph' + ID).removeClass('loading').addClass('load_error');
    });

  });

  if ($('#ovcat').length == 1) {
    $.getJSON('/home/chart/ovcat', function(data) {
      if (data.rows.length > 0) {
        var chart = new google.visualization.BubbleChart(document.getElementById('ovcat'));
        var gdata = new google.visualization.DataTable(data);
        var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
        var pct = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '', suffix: '%'});
        money.format(gdata, 1);
        money.format(gdata, 2);
        money.format(gdata, 4);
        chart.draw(gdata, {
          width: '100%',
          hAxis: {title: 'Overspent in euros'},
          vAxis: {title: 'Spent so far in euros'},
          bubble: {
            textStyle: {fontSize: 10}
          },
          height: 400,
          colors: ['FFFF99', 'FFCC66', 'FF9933', 'FF6633', 'FF0000', '990000', '660000', '330000', '000']
        });
      }
    });

  }

}