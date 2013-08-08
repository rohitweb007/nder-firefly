google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(drawChart);

$(document).ready(function() {
});

// some variables needed (it's for event handling
var ovcatChart,ovcatData;

function drawChart() {

  $.each($('.accountOverviewGraph'), function(i, v) {
    var graphHolder = $(v);
    var ID = graphHolder.attr('data-value');

    // do async data grab for all graphs:
    //var gdata = new google.visualization.DataTable(accountCache[ID]);
    if (!accountCache[ID]) {
      $.getJSON('/home/account/overviewGraph/' + ID, function(data) {
        drawAccountChart(ID, data);
      }).fail(function() {
        $('#accountOverviewGraph' + ID).removeClass('loading').addClass('load_error');
      });
    } else {
      drawAccountChart(ID, accountCache[ID]);
    }
  });

  $.each($('.budgetOverviewGraph'), function(i, v) {
    var graphHolder = $(v);
    var ID = graphHolder.attr('data-value');

    if (!budgetCache[ID]) {
      $.getJSON('/home/budget/overviewGraph/' + ID, function(data) {
        drawBudgetChart(ID, data);
      }).fail(function() {
        $('#budgetOverviewGraph' + ID).removeClass('loading').addClass('load_error');
      });
    } else {
      drawBudgetChart(ID, budgetCache[ID]);
    }

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

  if ($('#ovcat').length === 1) {
    $.getJSON('/home/chart/ovcat', function(data) {
      if (data.rows.length > 0) {



        var opt = {
          width: '100%',
          hAxis: {title: 'Overspent in euros'},
          vAxis: {title: 'Spent so far in euros'},
          bubble: {
            textStyle: {fontSize: 10}
          },
          height: 400,
          colors: ['FFFF99', 'FFCC66', 'FF9933', 'FF6633', 'FF0000', '990000', '660000', '330000', '000']
        };

        ovcatChart = new google.visualization.BubbleChart(document.getElementById('ovcat'));
        ovcatData = new google.visualization.DataTable(data);
        var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
        var pct = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '',suffix: '%'});
        money.format(ovcatData, 1);
        money.format(ovcatData, 2);
        //pct.format(ovcatData, 4);
        money.format(ovcatData, 4);

        google.visualization.events.addListener(ovcatChart, 'select', overspendSelectCategory);





        ovcatChart.draw(ovcatData, opt);
      }
    });

  }

}

function drawAccountChart(ID, data) {

  var opt = {
    vAxis: {textPosition: 'none'},
    lineWidth: 1,
    legend: {position: 'none'},
    hAxis: {textPosition: 'none', gridlines: {count: 2}},
    height: 90,
    trendlines: {0: {}},
    chartArea: {left: 40, width: '100%'}
  };

  var chart = new google.visualization.AreaChart(document.getElementById('accountOverviewGraph' + ID));
  var gdata = new google.visualization.DataTable(data);
  var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
  for (i = 1; i < gdata.getNumberOfColumns(); i++) {
    money.format(gdata, i);
  }
  chart.draw(gdata, opt);
}


function drawBudgetChart(ID, data) {

  var opt = {vAxis: {textPosition: 'none'}, lineWidth: 1, legend: {position: 'none'}, hAxis: {textPosition: 'none', gridlines: {count: 2}}, height: 90, chartArea: {left: 40, width: '100%'}};

  var chart = new google.visualization.AreaChart(document.getElementById('budgetOverviewGraph' + ID));
  var gdata = new google.visualization.DataTable(data);
  var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
  for (i = 1; i < gdata.getNumberOfColumns(); i++) {
    money.format(gdata, i);
  }
  chart.draw(gdata, opt);
}

function overspendSelectCategory(e) {
  // always one selection:
  var bubble = ovcatChart.getSelection()[0].row;
  var categoryName = ovcatData.getFormattedValue(bubble,0);

  // now we can continue and get some data on that category.

  window.location = '/home/category/overspending/' + encodeURI(categoryName);
}