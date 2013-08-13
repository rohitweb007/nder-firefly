$(document).ready(function() {
  // initialize some tooltips:
  $('.tt').tooltip();
  $('.popover').popover();

  $('input [name="tags"]').tagsinput({
    typeahead: {
      source: function(query) {
        return ["Amsterdam", "London", "Paris", "Washington", "New York", "Los Angeles", "Sydney", "Melbourne", "Canberra"];
      },
      freeInput: true
    }
  });



});





//
//    var chartHolder = $(v);
//    var ID = chartHolder.attr('data-value');
//    // grab data for this chart:
//    var data = $.ajax({url: "/home/budget/overviewChart/" + ID, dataType: "json", async: false}).responseText;
//    // make chart for the data:
//    var chart = new google.visualization.LineChart(document.getElementById('accountOverviewChart' + ID));
//    // make GData:
//    var gdata = new google.visualization.DataTable(data);
//
//    // format money
//    var formatter = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
//    for (i = 1; i < gdata.getNumberOfColumns(); i++) {
//      formatter.format(gdata, i);
//    }
//    chart.draw(gdata, {lineWidth: 1, legend: {position: 'none'}, chartArea: {left: 40, width: '100%', colors: ['blue', 'red']}});
//
//
//  });
//}