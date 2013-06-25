$(document).ready(function() {
  // initialize some tooltips:
  $('.tt').tooltip();
  $('.popover').popover();

  $('.toggle-icon').on('click', function() {
    $('.toggle-icon').toggleClass('icon-plus-sign icon-minus-sign');
    $('#toggle-value').val($('#toggle-value').val() === 'min' ? 'plus' : 'min');

  });

});





//
//    var graphHolder = $(v);
//    var ID = graphHolder.attr('data-value');
//    // grab data for this graph:
//    var data = $.ajax({url: "/home/budget/overviewGraph/" + ID, dataType: "json", async: false}).responseText;
//    // make chart for the data:
//    var chart = new google.visualization.LineChart(document.getElementById('accountOverviewGraph' + ID));
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