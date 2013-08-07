google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(drawChart);

$(document).ready(function() {
});

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

        var chart = new google.visualization.BubbleChart(document.getElementById('ovcat'));
        var gdata = new google.visualization.DataTable(data);
        var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
        var pct = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '', suffix: '%'});
        money.format(gdata, 1);
        money.format(gdata, 2);
        money.format(gdata, 4);


        chart.setAction({
          id: 'sample', // An id is mandatory for all actions.
          text: 'See sample book', // The text displayed in the tooltip.
          action: function() {           // When clicked, the following runs.
            selection = chart.getSelection();
            switch (selection[0].row) {
              case 0:
                alert("Ender's Game");
                break;
              case 1:
                alert("Feynman Lectures on Physics");
                break;
              case 2:
                alert("Numerical Recipes in JavaScript");
                break;
              case 3:
                alert("Truman");
                break;
              case 4:
                alert("Freakonomics");
                break;
              case 5:
                alert("The Mezzanine");
                break;
              case 6:
                alert("The Color of Magic");
                break;
              case 7:
                alert("The Law of Superheroes");
                break;
            }
          }
        });


        chart.draw(gdata, opt);
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