google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(drawChart);

$(document).ready(function() {
  $.each($('.showTransactions'), function(i, v) {
    $(v).on('click',toggleFolder);
  });
});

function toggleFolder(e) {
  var t = $(e.target);
  var closed = t.hasClass('icon-folder-close');
  var identifier = t.attr('data-value')+'Table';
  if(closed) {
    // do open routine:
    t.removeClass('icon-folder-close');
    t.addClass('icon-folder-open');
    $('#' + identifier).show();
  } else {
    // do close routine:
    t.removeClass('icon-folder-open');
    t.addClass('icon-folder-close');
    $('#' + identifier).hide();
  }

}


function drawChart() {
  $.each($('.accountOverviewGraph'), function(i, v) {
    var graphHolder = $(v);
    var ID = graphHolder.attr('data-value');

    // do async data grab for all graphs:
    $.getJSON('/home/account/overviewGraph/' + ID, function(data) {
      var chart = new google.visualization.AreaChart(document.getElementById('accountOverviewGraph' + ID));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }
      chart.draw(gdata, {lineWidth: 1, legend: {position: 'none'}, chartArea: {left: 40, width: '100%'}, colors: ['blue', 'red']});
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
      chart.draw(gdata, {lineWidth: 1, legend: {position: 'none'}, chartArea: {left: 40, width: '100%', colors: ['blue', 'red']}});
    });

  });
  if ($('#ovcat')) {
    $.getJSON('/home/chart/ovcat', function(data) {
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
    });
  }

}