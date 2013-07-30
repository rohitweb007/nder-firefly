google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(drawPredictionChart);

function drawPredictionChart() {
  if ($('#predictionChart').length === 1) {
    // do async data grab for all graphs:
    $.getJSON('/home/chart/predict', function(data) {
      var chart = new google.visualization.LineChart(document.getElementById('predictionChart'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }

      var opt = {
       width: '100%',
  height: 500,
  title: 'Predicted balance',
  chartArea: {
    left: 100,
    top: 50,
    width:'85%'
  },
  legend: {
    position:'bottom'
  },
  animation: {
    duration: 1000,
    easing: 'out',
  },
  series: {
    0: {pointSize: 1,lineWidth: 1},
    1: {pointSize: 0,lineWidth: 1},
  },
  colors: ['darkgreen', 'darkblue']
      };

      chart.draw(gdata,opt);
    });
  }
}