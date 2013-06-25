google.load('visualization', '1.0', {'packages': ['controls']});
google.setOnLoadCallback(drawDashboard);


function drawDashboard() {
  var dashboard = new google.visualization.Dashboard(document.getElementById('accountDashboard'));

  var today = new Date();
  var month = new Date();
  month.setMonth(today.getMonth() - 1);


     var control = new google.visualization.ControlWrapper({
    'controlType': 'ChartRangeFilter',
    'containerId': 'control',
    'options': {
      // Filter by the date axis.
      'filterColumnIndex': 0,

      'ui': {
        'chartType': 'LineChart',
        'chartOptions': {
          'chartArea': {'width': '90%',height:75},
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
    'state': {'range': {'start': month, 'end': today}}
  });

  var chart = new google.visualization.ChartWrapper({
    'chartType': 'LineChart',
    'containerId': 'chart',
    'options': {
      // Use the same chart area width as the control for axis alignment.
      'chartArea': {'height': '80%', 'width': '90%'},
      'hAxis': {'slantedText': false},
      //'vAxis': {'viewWindow': {'min': 0, 'max': 2000}},
      'legend': {'position': 'none'}
    }
  });

  var jsondata = $.ajax({url: "/home/account/chart/" + ID, dataType: "json", async: false}).responseText;
  var data = new google.visualization.DataTable(jsondata);
  dashboard.bind(control, chart);
  dashboard.draw(data);


  var listDashboard;
  var transactionDashboard;
}